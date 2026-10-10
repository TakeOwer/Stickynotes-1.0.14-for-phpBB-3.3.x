<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use salvocortesiano\stickynotes\service\note_manager;

/**
 * AJAX endpoint used by the front-end (JSON in, JSON out)
 */
class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var note_manager */
	protected $manager;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\request\request $request,
		\phpbb\language\language $language,
		note_manager $manager
	)
	{
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->language = $language;
		$this->manager = $manager;
	}

	public function handle($action)
	{
		$this->language->add_lang('common', 'salvocortesiano/stickynotes');

		if (empty($this->config['stickynotes_enabled']))
		{
			return $this->error('STICKYNOTES_ERR_DISABLED');
		}

		if ($this->user->data['user_id'] == ANONYMOUS || $this->user->data['is_bot']
			|| !$this->auth->acl_get('u_stickynotes_use') || empty($this->user->data['user_stickynotes_enabled']))
		{
			return $this->error('STICKYNOTES_ERR_NO_AUTH', 403);
		}

		if ($this->request->server('REQUEST_METHOD') !== 'POST' || !check_link_hash($this->request->variable('hash', ''), 'stickynotes'))
		{
			return $this->error('STICKYNOTES_ERR_TOKEN', 400);
		}

		$user_id = (int) $this->user->data['user_id'];

		switch ($action)
		{
			case 'create':
				return $this->action_create($user_id);

			case 'toggle_all':
				$page = $this->manager->sanitize_page_key($this->request->variable('page', ''));
				$this->manager->set_all_hidden($user_id, $page, $this->request->variable('hidden', 0));
				return $this->success(['notes' => $this->manager->get_user_notes($user_id, $page)]);

			case 'dismiss':
				$this->manager->dismiss_popups($user_id, $this->request->variable('ids', [0]));
				return $this->success();

			// A reminder expired while the page was open
			case 'due':
				if (!empty($this->config['stickynotes_allow_reminder']))
				{
					$this->manager->process_due_reminders(20, $user_id);
				}
				$page = $this->manager->sanitize_page_key($this->request->variable('page', ''));
				return $this->success([
					'due'		=> $this->manager->get_due_popups($user_id, (int) $this->user->data['user_stickynotes_notify']),
					'notes'		=> $this->manager->get_user_notes($user_id, $page),
					'unread'	=> $this->manager->get_unread_notifications($user_id),
				]);

			case 'shared_get':
				$note_id = $this->request->variable('id', 0);
				if (!$this->manager->can_view_shared($note_id, $user_id))
				{
					return $this->error('STICKYNOTES_ERR_NOT_FOUND', 404);
				}
				$row = $this->manager->get_note($note_id);
				return $this->success(['note' => $this->manager->to_array($row, false)]);
		}

		// All other actions work on one of the user's own notes
		$row = $this->manager->get_note($this->request->variable('id', 0));
		if (!$row || (int) $row['user_id'] !== $user_id)
		{
			return $this->error('STICKYNOTES_ERR_NOT_FOUND', 404);
		}
		$note_id = (int) $row['note_id'];

		switch ($action)
		{
			case 'update':
				$error = $this->save_content($note_id);
				if ($error)
				{
					return $this->error($error);
				}
			break;

			case 'props':
				$props = [];
				foreach (['x', 'y', 'w', 'h', 'z', 'hidden', 'locked', 'minimized', 'snooze'] as $key)
				{
					if ($this->request->is_set_post($key))
					{
						$props[$key] = $this->request->variable($key, 0);
					}
				}
				foreach (['color', 'scope', 'page'] as $key)
				{
					if ($this->request->is_set_post($key))
					{
						$props[$key] = $this->request->variable($key, '');
					}
				}
				if ($this->request->is_set_post('title'))
				{
					$props['title'] = $this->request->variable('title', '', true);
				}
				$this->manager->update_props($note_id, $props);
			break;

			case 'duplicate':
				if (!$this->manager->can_create($user_id))
				{
					return $this->error($this->limit_message());
				}
				$new_id = $this->manager->duplicate($row);
				return $this->success(['note' => $this->note_payload($new_id)]);

			case 'delete':
				$this->manager->delete([$note_id]);
				return $this->success(['deleted' => $note_id]);

			case 'reminder':
				if (empty($this->config['stickynotes_allow_reminder']) || !$this->auth->acl_get('u_stickynotes_reminder'))
				{
					return $this->error('STICKYNOTES_ERR_NO_AUTH', 403);
				}
				$time = $this->request->variable('time', 0);
				if ($time && $time < time() - 60)
				{
					return $this->error('STICKYNOTES_ERR_PAST_DATE');
				}
				$this->manager->set_reminder($note_id, $time, $this->request->variable('repeat', 0));
			break;

			case 'share':
				if (empty($this->config['stickynotes_allow_share']) || !$this->auth->acl_get('u_stickynotes_share'))
				{
					return $this->error('STICKYNOTES_ERR_NO_AUTH', 403);
				}
				$result = $this->manager->set_shares($row, $this->request->variable('users', '', true), $this->request->variable('groups', [0]));
				$extra = [];
				if (!empty($result['missing']))
				{
					$extra['warning'] = $this->language->lang('STICKYNOTES_SHARE_MISSING', implode(', ', $result['missing']));
				}
				return $this->success(array_merge(['note' => $this->note_payload($note_id)], $extra));

			default:
				return $this->error('STICKYNOTES_ERR_ACTION', 400);
		}

		return $this->success(['note' => $this->note_payload($note_id)]);
	}

	protected function action_create($user_id)
	{
		if (!$this->manager->can_create($user_id))
		{
			return $this->error($this->limit_message());
		}

		$note_id = $this->manager->create($user_id, [
			'color'	=> $this->request->variable('color', ''),
			'scope'	=> $this->request->variable('scope', 'global'),
			'page'	=> $this->request->variable('page', ''),
			'x'		=> $this->request->variable('x', 4000),
			'y'		=> $this->request->variable('y', 2500),
		]);

		if ($this->request->is_set_post('title') || $this->request->is_set_post('text'))
		{
			$error = $this->save_content($note_id);
			if ($error)
			{
				$this->manager->delete([$note_id]);
				return $this->error($error);
			}
		}

		return $this->success(['note' => $this->note_payload($note_id)]);
	}

	/**
	 * @return string|false Language key of the error, or false
	 */
	protected function save_content($note_id)
	{
		$title = $this->request->variable('title', '', true);
		$text = $this->request->variable('text', '', true);
		$bbcode = $this->request->variable('format', 0) == 1 && $this->can_bbcode();

		$max = (int) $this->config['stickynotes_max_chars'];
		if ($max > 0 && utf8_strlen(htmlspecialchars_decode($text, ENT_COMPAT)) > $max)
		{
			return 'STICKYNOTES_ERR_TOO_LONG';
		}

		$warnings = $this->manager->update_content($note_id, $title, $text, $bbcode);
		if (!empty($warnings))
		{
			return implode(' ', $warnings);
		}

		return false;
	}

	protected function can_bbcode()
	{
		return !empty($this->config['stickynotes_allow_bbcode']) && $this->auth->acl_get('u_stickynotes_bbcode');
	}

	protected function limit_message()
	{
		return $this->language->lang('STICKYNOTES_ERR_LIMIT', (int) $this->config['stickynotes_max_notes']);
	}

	protected function note_payload($note_id)
	{
		$row = $this->manager->get_note($note_id);
		$shares = $this->manager->get_shares_for_notes([$note_id]);

		return $this->manager->to_array($row, true, isset($shares[$note_id]) ? $shares[$note_id] : []);
	}

	protected function success(array $data = [])
	{
		return new JsonResponse(array_merge(['success' => true], $data));
	}

	protected function error($message, $status = 200)
	{
		// Status 200 for validation errors so the JS can show the message
		return new JsonResponse([
			'success'	=> false,
			'error'		=> $this->language->lang($message),
		], $status === 200 ? 200 : $status);
	}
}

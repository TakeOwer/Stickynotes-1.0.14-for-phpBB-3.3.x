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

use salvocortesiano\stickynotes\service\note_manager;

class ucp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var note_manager */
	protected $manager;

	protected $root_path;
	protected $php_ext;

	/** @var string */
	protected $u_action;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		note_manager $manager,
		$root_path,
		$php_ext,
		\salvocortesiano\stickynotes\service\webpush $webpush = null
	)
	{
		$this->webpush = $webpush;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->manager = $manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/** @var \salvocortesiano\stickynotes\service\webpush */
	protected $webpush;

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	 * Link that opens the note on its page
	 */
	protected function note_url(array $row)
	{
		if ($row['note_scope'] === 'page' && $row['note_page'] !== '')
		{
			list($script, $query) = array_pad(explode('?', $row['note_page'], 2), 2, '');
			return append_sid($this->root_path . $script, ($query !== '' ? $query . '&amp;' : '') . 'sn_open=' . (int) $row['note_id']);
		}

		return append_sid($this->root_path . 'index.' . $this->php_ext, 'sn_open=' . (int) $row['note_id']);
	}

	/* ------------------------------------------------------------------
	 * My notes
	 * ------------------------------------------------------------------ */
	public function display_notes()
	{
		$user_id = (int) $this->user->data['user_id'];
		$action = $this->request->variable('action', '');

		if ($action === 'delete' || $this->request->is_set_post('delete_marked'))
		{
			$this->delete_notes($user_id);
		}

		$notes_table = $this->manager->get_notes_table();
		$sql = 'SELECT * FROM ' . $notes_table . '
			WHERE user_id = ' . $user_id . '
			ORDER BY note_updated DESC';
		$result = $this->db->sql_query_limit($sql, 500);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$shares = $this->manager->get_shares_for_notes(array_column($rows, 'note_id'));
		$repeat_keys = ['STICKYNOTES_REPEAT_NONE', 'STICKYNOTES_REPEAT_DAILY', 'STICKYNOTES_REPEAT_WEEKLY', 'STICKYNOTES_REPEAT_MONTHLY'];

		foreach ($rows as $row)
		{
			$id = (int) $row['note_id'];
			$share_names = isset($shares[$id]) ? array_column($shares[$id], 'name') : [];
			$excerpt = trim(strip_tags($this->manager->render_text($row)));

			$this->template->assign_block_vars('notes', [
				'ID'			=> $id,
				'TITLE'			=> $row['note_title'] !== '' ? $row['note_title'] : $this->language->lang('STICKYNOTES_UNTITLED'),
				'EXCERPT'		=> utf8_strlen($excerpt) > 120 ? utf8_substr($excerpt, 0, 120) . '…' : $excerpt,
				'COLOR'			=> $row['note_color'],
				'COLOR_NAME'	=> $this->language->lang('STICKYNOTES_COLOR_' . strtoupper($row['note_color'])),
				'SCOPE'			=> $row['note_scope'] === 'page'
					? $this->language->lang('STICKYNOTES_SCOPE_PAGE') . ': ' . $row['note_page']
					: $this->language->lang('STICKYNOTES_SCOPE_GLOBAL'),
				'REMINDER'		=> $row['reminder_time'] ? $this->user->format_date((int) $row['reminder_time']) : '',
				'S_EXPIRED'		=> (int) $row['reminder_time'] > 0 && (int) $row['reminder_repeat'] === 0 && (int) $row['reminder_time'] <= time(),
				'REPEAT'		=> (int) $row['reminder_repeat'] ? $this->language->lang($repeat_keys[(int) $row['reminder_repeat']]) : '',
				'SHARES'		=> htmlspecialchars(implode(', ', $share_names), ENT_COMPAT, 'UTF-8'),
				'UPDATED'		=> $this->user->format_date((int) $row['note_updated']),
				'S_HIDDEN'		=> (bool) $row['note_hidden'],
				'S_LOCKED'		=> (bool) $row['note_locked'],
				'S_MINIMIZED'	=> !empty($row['note_minimized']),
				'SNOOZE'		=> (int) $row['note_snooze'] > time() ? $this->user->format_date((int) $row['note_snooze']) : '',
				'U_VIEW'		=> $this->note_url($row),
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $id,
			]);
		}

		foreach ($this->manager->get_shared_notes($user_id) as $note)
		{
			$this->template->assign_block_vars('shared', [
				'TITLE'		=> $note['title'] !== '' ? htmlspecialchars($note['title'], ENT_COMPAT, 'UTF-8') : $this->language->lang('STICKYNOTES_UNTITLED'),
				'OWNER'		=> htmlspecialchars($note['ownerName'], ENT_COMPAT, 'UTF-8'),
				'COLOR'		=> $note['color'],
				'UPDATED'	=> $this->user->format_date($note['updated']),
				'U_VIEW'	=> append_sid($this->root_path . 'index.' . $this->php_ext, 'sn_open=' . $note['id']),
			]);
		}

		$max = (int) $this->config['stickynotes_max_notes'];
		$this->template->assign_vars([
			'STICKYNOTES_COUNT'		=> count($rows),
			'STICKYNOTES_LIMIT'		=> ($max > 0 && !$this->auth->acl_get('u_stickynotes_nolimit'))
				? $this->language->lang('STICKYNOTES_COUNT_NOTES', count($rows), $max)
				: count($rows),
			'S_STICKYNOTES_ACTIVE'	=> !empty($this->user->data['user_stickynotes_enabled']) && !empty($this->config['stickynotes_enabled']),
			'S_UCP_ACTION'			=> $this->u_action,
		]);
	}

	protected function delete_notes($user_id)
	{
		$ids = $this->request->variable('action', '') === 'delete'
			? [$this->request->variable('id', 0)]
			: $this->request->variable('mark', [0]);

		// Only the user's own notes
		$ids = array_filter(array_map('intval', $ids));
		if (!empty($ids))
		{
			$sql = 'SELECT note_id FROM ' . $this->manager->get_notes_table() . '
				WHERE user_id = ' . (int) $user_id . ' AND ' . $this->db->sql_in_set('note_id', $ids);
			$result = $this->db->sql_query($sql);
			$ids = array_map('intval', array_column($this->db->sql_fetchrowset($result), 'note_id'));
			$this->db->sql_freeresult($result);
		}

		if (empty($ids))
		{
			return;
		}

		if (confirm_box(true))
		{
			$deleted = $this->manager->delete($ids);
			$message = $this->language->lang('STICKYNOTES_UCP_DELETED', $deleted);

			if ($this->request->is_ajax())
			{
				$json = new \phpbb\json_response();
				$json->send([
					'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
					'MESSAGE_TEXT'	=> $message,
					'SUCCESS'		=> true,
				]);
			}

			meta_refresh(3, $this->u_action);
			trigger_error($message . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>'));
		}
		else
		{
			confirm_box(false, $this->language->lang('STICKYNOTES_UCP_DELETE_CONFIRM', count($ids)), build_hidden_fields([
				'action'	=> 'delete_marked',
				'mark'		=> $ids,
				'delete_marked'	=> 1,
			]));

			// Cancelled
			redirect($this->u_action);
		}
	}

	/* ------------------------------------------------------------------
	 * Preferences
	 * ------------------------------------------------------------------ */
	public function display_settings()
	{
		add_form_key('stickynotes_ucp');
		$data = $this->user->data;

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stickynotes_ucp'))
			{
				trigger_error('FORM_INVALID');
			}

			$tray = $this->request->variable('user_stickynotes_tray', '');
			$sql_ary = [
				'user_stickynotes_enabled'	=> $this->request->variable('user_stickynotes_enabled', 1),
				'user_stickynotes_tray'		=> in_array($tray, note_manager::TRAY_POSITIONS, true) ? $tray : '',
				'user_stickynotes_notify'	=> max(0, min(2, $this->request->variable('user_stickynotes_notify', 2))),
			];

			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . (int) $data['user_id'];
			$this->db->sql_query($sql);

			// Browser push notifications (phpbb/webpushnotifications)
			if ($this->webpush && $this->webpush->is_available() && $this->request->is_set_post('stickynotes_push_sent'))
			{
				$this->webpush->set_user_enabled((int) $data['user_id'], (bool) $this->request->variable('user_stickynotes_push', 0));
			}

			meta_refresh(3, $this->u_action);
			trigger_error($this->language->lang('STICKYNOTES_UCP_SAVED') . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>'));
		}

		$this->template->assign_block_vars('tray_positions', [
			'VALUE'		=> '',
			'LABEL'		=> $this->language->lang('STICKYNOTES_TRAY_DEFAULT', $this->language->lang('STICKYNOTES_TRAY_' . strtoupper($this->config['stickynotes_tray_pos']))),
			'S_SELECTED'=> $data['user_stickynotes_tray'] === '',
		]);
		foreach (note_manager::TRAY_POSITIONS as $pos)
		{
			$this->template->assign_block_vars('tray_positions', [
				'VALUE'		=> $pos,
				'LABEL'		=> $this->language->lang('STICKYNOTES_TRAY_' . strtoupper($pos)),
				'S_SELECTED'=> $data['user_stickynotes_tray'] === $pos,
			]);
		}

		$this->template->assign_vars([
			'S_STICKYNOTES_ENABLED'		=> (bool) $data['user_stickynotes_enabled'],
			'S_STICKYNOTES_TRAY_USER'	=> !empty($this->config['stickynotes_tray_user']),
			'S_STICKYNOTES_REMINDERS'	=> !empty($this->config['stickynotes_allow_reminder']) && $this->auth->acl_get('u_stickynotes_reminder'),
			'STICKYNOTES_NOTIFY'		=> (int) $data['user_stickynotes_notify'],
			'S_UCP_ACTION'				=> $this->u_action,
		]);

		// Browser push notifications (phpbb/webpushnotifications)
		if ($this->webpush && $this->webpush->is_available())
		{
			$devices = $this->webpush->count_devices((int) $data['user_id']);
			$this->template->assign_vars([
				'S_STICKYNOTES_WEBPUSH'		=> true,
				'S_STICKYNOTES_PUSH_ON'		=> $this->webpush->is_user_enabled((int) $data['user_id']),
				'STICKYNOTES_PUSH_DEVICES'	=> $devices
					? $this->language->lang('STICKYNOTES_UCP_PUSH_DEVICES', $devices)
					: $this->language->lang('STICKYNOTES_UCP_PUSH_NO_DEVICE'),
				'S_STICKYNOTES_PUSH_DEVICE'	=> $devices > 0,
				'U_STICKYNOTES_PUSH_DEVICE'	=> append_sid($this->root_path . 'ucp.' . $this->php_ext, 'i=ucp_notifications&amp;mode=notification_options'),
			]);
		}
	}
}

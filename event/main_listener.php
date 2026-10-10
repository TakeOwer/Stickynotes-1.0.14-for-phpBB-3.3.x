<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use salvocortesiano\stickynotes\service\note_manager;

class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var note_manager */
	protected $manager;

	/** @var int Topic id of the page being viewed (set by viewtopic) */
	protected $topic_id = 0;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\controller\helper $helper,
		note_manager $manager
	)
	{
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->template = $template;
		$this->language = $language;
		$this->request = $request;
		$this->helper = $helper;
		$this->manager = $manager;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'								=> 'load_language',
			'core.permissions'								=> 'add_permissions',
			'core.viewtopic_assign_template_vars_before'	=> 'remember_topic',
			'core.page_header'								=> 'inject_notes',
			'core.delete_user_after'						=> 'delete_user_notes',
			'core.delete_group_after'						=> 'delete_group_shares',
			'core.get_logs_modify_entry_data'				=> 'translate_log_entry',
		];
	}

	/** @var bool */
	protected $log_lang_loaded = false;

	/**
	 * phpBB writes "Module added » <module name>" while the extension is being
	 * enabled, before its language files are loaded, so the raw key is stored
	 * (e.g. ACP_STICKYNOTES_MAINTENANCE). Translate those keys when the log is shown,
	 * which also fixes the entries already in the log.
	 */
	public function translate_log_entry($event)
	{
		$row = $event['row'];
		if (empty($row['log_data']) || strpos($row['log_data'], 'STICKYNOTES_') === false)
		{
			return;
		}

		$data = @unserialize($row['log_data'], ['allowed_classes' => false]);
		if (!is_array($data))
		{
			return;
		}

		if (!$this->log_lang_loaded)
		{
			$this->language->add_lang(['info_acp_stickynotes', 'info_ucp_stickynotes', 'common'], 'salvocortesiano/stickynotes');
			$this->log_lang_loaded = true;
		}

		$changed = false;
		foreach ($data as $i => $value)
		{
			if (is_string($value) && preg_match('#^(ACP|UCP)_STICKYNOTES_[A-Z0-9_]+$#', $value) && $this->language->is_set($value))
			{
				$data[$i] = $this->language->lang($value);
				$changed = true;
			}
		}

		if ($changed)
		{
			$row['log_data'] = serialize($data);
			$event['row'] = $row;
		}
	}

	/**
	 * Language is needed on every page (notifications dropdown, UCP options)
	 */
	public function load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name'	=> 'salvocortesiano/stickynotes',
			'lang_set'	=> 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_permissions($event)
	{
		$categories = $event['categories'];
		$categories['stickynotes'] = 'ACL_CAT_STICKYNOTES';
		$event['categories'] = $categories;

		$permissions = $event['permissions'];
		$permissions['u_stickynotes_use']		= ['lang' => 'ACL_U_STICKYNOTES_USE', 'cat' => 'stickynotes'];
		$permissions['u_stickynotes_bbcode']	= ['lang' => 'ACL_U_STICKYNOTES_BBCODE', 'cat' => 'stickynotes'];
		$permissions['u_stickynotes_share']		= ['lang' => 'ACL_U_STICKYNOTES_SHARE', 'cat' => 'stickynotes'];
		$permissions['u_stickynotes_reminder']	= ['lang' => 'ACL_U_STICKYNOTES_REMINDER', 'cat' => 'stickynotes'];
		$permissions['u_stickynotes_nolimit']	= ['lang' => 'ACL_U_STICKYNOTES_NOLIMIT', 'cat' => 'stickynotes'];
		$permissions['a_stickynotes']			= ['lang' => 'ACL_A_STICKYNOTES', 'cat' => 'misc'];
		$event['permissions'] = $permissions;
	}

	public function remember_topic($event)
	{
		$this->topic_id = isset($event['topic_data']['topic_id']) ? (int) $event['topic_data']['topic_id'] : 0;
	}

	/**
	 * Key that identifies the current page for "this page only" notes
	 */
	protected function get_page_key()
	{
		$page = isset($this->user->page['page_name']) ? $this->user->page['page_name'] : '';
		$page = preg_replace('#\?.*$#', '', $page);

		if (strpos($page, 'viewtopic.') === 0)
		{
			$topic_id = $this->topic_id ?: $this->request->variable('t', 0);
			return $this->manager->sanitize_page_key($page . ($topic_id ? '?t=' . $topic_id : ''));
		}

		if (strpos($page, 'viewforum.') === 0)
		{
			$forum_id = $this->request->variable('f', 0);
			return $this->manager->sanitize_page_key($page . ($forum_id ? '?f=' . $forum_id : ''));
		}

		return $this->manager->sanitize_page_key($page);
	}

	public function inject_notes($event)
	{
		if (empty($this->config['stickynotes_enabled'])
			|| $this->user->data['user_id'] == ANONYMOUS
			|| !empty($this->user->data['is_bot'])
			|| empty($this->user->data['user_stickynotes_enabled'])
			|| !$this->auth->acl_get('u_stickynotes_use'))
		{
			return;
		}

		// Not in the ACP and not on popup/print-like pages
		if (defined('ADMIN_START') || defined('IN_CRON') || $this->request->variable('view', '') === 'print')
		{
			return;
		}

		$user_id = (int) $this->user->data['user_id'];
		$page_key = $this->get_page_key();

		// Due reminders of this user are handled right now, without waiting for the
		// phpBB cron (it runs one task per page view and can be late, or never run
		// if the style does not print it). core.page_header runs before phpBB counts
		// the notifications, so the bell already shows the new one on this page.
		if (!empty($this->config['stickynotes_allow_reminder']))
		{
			try
			{
				$this->manager->process_due_reminders(20, $user_id);
			}
			catch (\Exception $e)
			{
				// never break the page for a reminder
			}
		}

		$can_share = !empty($this->config['stickynotes_allow_share']) && $this->auth->acl_get('u_stickynotes_share');
		$can_reminder = !empty($this->config['stickynotes_allow_reminder']) && $this->auth->acl_get('u_stickynotes_reminder');
		$can_bbcode = !empty($this->config['stickynotes_allow_bbcode']) && $this->auth->acl_get('u_stickynotes_bbcode');

		$groups = [];
		if ($can_share)
		{
			foreach ($this->manager->get_user_groups($user_id) as $gid => $name)
			{
				$groups[] = ['id' => $gid, 'name' => $name];
			}
		}

		$max = (int) $this->config['stickynotes_max_notes'];

		$data = [
			'page'		=> $page_key,
			'hash'		=> generate_link_hash('stickynotes'),
			'ajax'		=> $this->helper->route('salvocortesiano_stickynotes_ajax', ['action' => '__action__'], false),
			'ucp'		=> append_sid(generate_board_url() . '/ucp.php', 'i=-salvocortesiano-stickynotes-ucp-main_module&mode=notes', false),
			'tray'		=> $this->manager->get_tray_position($this->user->data),
			'style'		=> isset($this->user->style['style_path']) ? preg_replace('#[^a-z0-9_-]#', '', strtolower($this->user->style['style_path'])) : '',
			'mobile'	=> (int) $this->config['stickynotes_mobile_mode'],
			'maxChars'	=> (int) $this->config['stickynotes_max_chars'],
			'maxNotes'	=> $this->auth->acl_get('u_stickynotes_nolimit') ? 0 : $max,
			'total'		=> $this->manager->count_user_notes($user_id),
			'color'		=> $this->manager->get_default_color(),
			'colors'	=> note_manager::COLORS,
			'rotation'	=> (bool) $this->config['stickynotes_rotation'],
			'trash'		=> !empty($this->config['stickynotes_trash']) ? ($this->config['stickynotes_trash_pos'] === 'side' ? 'side' : 'above') : '',
			'can'		=> [
				'bbcode'	=> $can_bbcode,
				'share'		=> $can_share,
				'reminder'	=> $can_reminder,
			],
			'groups'	=> $groups,
			'notes'		=> $this->manager->get_user_notes($user_id, $page_key),
			'shared'	=> $this->manager->get_shared_notes($user_id),
			'due'		=> $this->manager->get_due_popups($user_id, (int) $this->user->data['user_stickynotes_notify']),
			'open'		=> $this->request->variable('sn_open', 0),
			'lang'		=> $this->get_js_language(),
		];

		$this->template->assign_vars([
			'S_STICKYNOTES'			=> true,
			'STICKYNOTES_DATA'		=> json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
		]);
	}

	/**
	 * Every text used by the JavaScript comes from the language files
	 */
	protected function get_js_language()
	{
		$keys = [
			'TITLE', 'TRAY_OPEN', 'TRAY_CLOSE', 'NEW', 'SHOW_ALL', 'HIDE_ALL', 'SEARCH', 'TAB_MINE', 'TAB_SHARED',
			'EMPTY', 'EMPTY_SHARED', 'MANAGE', 'UNTITLED', 'EDIT', 'SAVE', 'CANCEL', 'DELETE', 'DELETE_CONFIRM',
			'HIDE', 'SHOW', 'MENU', 'COLOR', 'COLOR_YELLOW', 'COLOR_PINK', 'COLOR_BLUE', 'COLOR_GREEN', 'COLOR_ORANGE',
			'COLOR_PURPLE', 'COLOR_WHITE', 'SCOPE', 'SCOPE_GLOBAL', 'SCOPE_PAGE', 'SCOPE_GLOBAL_EXPLAIN', 'SCOPE_PAGE_EXPLAIN',
			'REMINDER', 'REMINDER_SET', 'REMINDER_REMOVE', 'REMINDER_DATE', 'REMINDER_REPEAT', 'REPEAT_NONE',
			'REPEAT_DAILY', 'REPEAT_WEEKLY', 'REPEAT_MONTHLY', 'REMINDER_AT', 'REMINDER_POPUP_TITLE', 'REMINDER_EXPIRED',
			'SHARE', 'SHARE_USERS', 'SHARE_USERS_EXPLAIN', 'SHARE_GROUPS', 'SHARE_SAVE', 'SHARED_WITH', 'SHARED_BY',
			'SHARE_NONE', 'DUPLICATE', 'LOCK', 'UNLOCK', 'LOCKED', 'FORMAT', 'FORMAT_PLAIN', 'FORMAT_BBCODE',
			'BB_BOLD', 'BB_ITALIC', 'BB_UNDERLINE', 'BB_STRIKE', 'BB_LIST', 'BB_URL', 'BB_COLOR', 'BB_CODE', 'BB_QUOTE',
			'TITLE_PLACEHOLDER', 'TEXT_PLACEHOLDER', 'CHARS_LEFT', 'ERROR', 'ERR_GENERIC', 'ERR_LIMIT_JS', 'SAVED',
			'READONLY', 'CLOSE', 'COUNT_NOTES', 'SHORTCUT', 'RESIZE', 'DRAG', 'URL_PROMPT', 'FOCUS',
			'SNOOZE', 'SNOOZE_30M', 'SNOOZE_1H', 'SNOOZE_4H', 'SNOOZE_TOMORROW', 'SNOOZE_WEEK', 'SNOOZE_CUSTOM', 'SNOOZE_UNTIL',
			'SNOOZE_UNTIL_LABEL', 'SNOOZE_WAKE', 'SNOOZE_SET', 'MINIMIZE', 'EXPAND', 'BACK', 'ERR_SNOOZE_PAST',
			'TRASH', 'TRASH_DROP',
		];

		$lang = [];
		foreach ($keys as $key)
		{
			$lang[$key] = $this->language->lang('STICKYNOTES_' . $key);
		}

		// Buttons of the phpBB confirm box (core language)
		$lang['YES'] = $this->language->lang('YES');
		$lang['NO'] = $this->language->lang('NO');

		return $lang;
	}

	public function delete_user_notes($event)
	{
		$this->manager->delete_user_notes($event['user_ids']);
	}

	public function delete_group_shares($event)
	{
		$this->manager->delete_group_shares($event['group_id']);
	}
}

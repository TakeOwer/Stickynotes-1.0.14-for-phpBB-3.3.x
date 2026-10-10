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

class acp_controller
{
	const BATCH = 250;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\routing\helper */
	protected $routing_helper;

	/** @var note_manager */
	protected $manager;

	protected $root_path;
	protected $php_ext;
	protected $table_prefix;
	protected $notes_table;
	protected $shares_table;

	/** @var string */
	protected $u_action;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\db\tools\tools_interface $db_tools,
		\phpbb\language\language $language,
		\phpbb\log\log_interface $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\extension\manager $ext_manager,
		\phpbb\routing\helper $routing_helper,
		note_manager $manager,
		$root_path,
		$php_ext,
		$table_prefix,
		$notes_table,
		$shares_table,
		\salvocortesiano\stickynotes\service\webpush $webpush = null
	)
	{
		$this->webpush = $webpush;
		$this->config = $config;
		$this->db = $db;
		$this->db_tools = $db_tools;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->ext_manager = $ext_manager;
		$this->routing_helper = $routing_helper;
		$this->manager = $manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->notes_table = $notes_table;
		$this->shares_table = $shares_table;
	}

	/** @var \salvocortesiano\stickynotes\service\webpush */
	protected $webpush;

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	 * Badges at the top and credits at the bottom of every page
	 */
	public function assign_common()
	{
		$this->language->add_lang('common', 'salvocortesiano/stickynotes');

		$version = isset($this->config['stickynotes_version']) ? $this->config['stickynotes_version'] : '';
		try
		{
			$meta = $this->ext_manager->create_extension_metadata_manager('salvocortesiano/stickynotes')->get_metadata('all');
			if (!empty($meta['version']))
			{
				$version = $meta['version'];
			}
		}
		catch (\Exception $e)
		{
			// Keep the version stored in config
		}

		$this->template->assign_vars([
			'STICKYNOTES_EXT_VERSION'	=> $version,
			'STICKYNOTES_PHP_VERSION'	=> PHP_VERSION,
			'STICKYNOTES_PHPBB_VERSION'	=> $this->config['version'],
			'S_STICKYNOTES_ENABLED'		=> !empty($this->config['stickynotes_enabled']),
			'U_ACTION'					=> $this->u_action,
		]);
	}

	/* ------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------ */
	public function display_settings()
	{
		$this->assign_common();
		add_form_key('stickynotes_acp');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stickynotes_acp'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$tray = $this->request->variable('stickynotes_tray_pos', 'bl');
			$color = $this->request->variable('stickynotes_default_color', 'yellow');

			$this->config->set('stickynotes_enabled', $this->request->variable('stickynotes_enabled', 0));
			$this->config->set('stickynotes_max_notes', max(0, $this->request->variable('stickynotes_max_notes', 30)));
			$this->config->set('stickynotes_max_chars', max(0, min(65000, $this->request->variable('stickynotes_max_chars', 2000))));
			$this->config->set('stickynotes_tray_pos', in_array($tray, note_manager::TRAY_POSITIONS, true) ? $tray : 'bl');
			$this->config->set('stickynotes_tray_user', $this->request->variable('stickynotes_tray_user', 0));
			$this->config->set('stickynotes_allow_bbcode', $this->request->variable('stickynotes_allow_bbcode', 0));
			$this->config->set('stickynotes_allow_share', $this->request->variable('stickynotes_allow_share', 0));
			$this->config->set('stickynotes_allow_reminder', $this->request->variable('stickynotes_allow_reminder', 0));
			$this->config->set('stickynotes_mobile_mode', max(0, min(2, $this->request->variable('stickynotes_mobile_mode', 0))));
			$this->config->set('stickynotes_default_color', in_array($color, note_manager::COLORS, true) ? $color : 'yellow');
			$this->config->set('stickynotes_rotation', $this->request->variable('stickynotes_rotation', 0));
			$this->config->set('stickynotes_trash', $this->request->variable('stickynotes_trash', 0));
			$trash_pos = $this->request->variable('stickynotes_trash_pos', 'above');
			$this->config->set('stickynotes_trash_pos', in_array($trash_pos, ['above', 'side'], true) ? $trash_pos : 'above');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_SETTINGS');

			trigger_error($this->language->lang('ACP_STICKYNOTES_SAVED') . adm_back_link($this->u_action));
		}

		foreach (note_manager::TRAY_POSITIONS as $pos)
		{
			$this->template->assign_block_vars('tray_positions', [
				'VALUE'		=> $pos,
				'LABEL'		=> $this->language->lang('STICKYNOTES_TRAY_' . strtoupper($pos)),
				'S_SELECTED'=> $this->config['stickynotes_tray_pos'] === $pos,
			]);
		}

		foreach (note_manager::COLORS as $color)
		{
			$this->template->assign_block_vars('colors', [
				'VALUE'		=> $color,
				'LABEL'		=> $this->language->lang('STICKYNOTES_COLOR_' . strtoupper($color)),
				'S_SELECTED'=> $this->config['stickynotes_default_color'] === $color,
			]);
		}

		$this->template->assign_vars([
			'STICKYNOTES_ENABLED'		=> (bool) $this->config['stickynotes_enabled'],
			'STICKYNOTES_MAX_NOTES'		=> (int) $this->config['stickynotes_max_notes'],
			'STICKYNOTES_MAX_CHARS'		=> (int) $this->config['stickynotes_max_chars'],
			'STICKYNOTES_TRAY_USER'		=> (bool) $this->config['stickynotes_tray_user'],
			'STICKYNOTES_ALLOW_BBCODE'	=> (bool) $this->config['stickynotes_allow_bbcode'],
			'STICKYNOTES_ALLOW_SHARE'	=> (bool) $this->config['stickynotes_allow_share'],
			'STICKYNOTES_ALLOW_REMINDER'=> (bool) $this->config['stickynotes_allow_reminder'],
			'STICKYNOTES_MOBILE_MODE'	=> (int) $this->config['stickynotes_mobile_mode'],
			'STICKYNOTES_ROTATION'		=> (bool) $this->config['stickynotes_rotation'],
			'STICKYNOTES_TRASH'			=> !empty($this->config['stickynotes_trash']),
			'STICKYNOTES_TRASH_POS'		=> (string) $this->config['stickynotes_trash_pos'],
		]);
	}

	/* ------------------------------------------------------------------
	 * Check-up
	 * ------------------------------------------------------------------ */
	public function display_checkup()
	{
		$this->assign_common();

		$checks = [];
		$add = function ($label, $status, $detail = '') use (&$checks)
		{
			$checks[] = ['label' => $label, 'status' => $status, 'detail' => $detail];
		};
		$l = $this->language;

		// Environment
		$add($l->lang('STICKYNOTES_CHECK_PHP'), version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error', PHP_VERSION);
		$add($l->lang('STICKYNOTES_CHECK_PHPBB'), phpbb_version_compare($this->config['version'], '3.3.0', '>=') ? 'ok' : 'error', $this->config['version']);
		$add($l->lang('STICKYNOTES_CHECK_ENABLED'), !empty($this->config['stickynotes_enabled']) ? 'ok' : 'warn',
			!empty($this->config['stickynotes_enabled']) ? $l->lang('YES') : $l->lang('STICKYNOTES_CHECK_ENABLED_OFF'));

		// Database
		foreach ([$this->notes_table, $this->shares_table] as $table)
		{
			$exists = $this->db_tools->sql_table_exists($table);
			$add($l->lang('STICKYNOTES_CHECK_TABLE', $table), $exists ? 'ok' : 'error', $exists ? '' : $l->lang('STICKYNOTES_CHECK_REINSTALL'));
		}

		$missing = [];
		$columns = [
			$this->notes_table => ['note_id', 'user_id', 'note_title', 'note_text', 'note_format', 'note_color', 'note_scope', 'note_page',
				'pos_x', 'pos_y', 'note_width', 'note_height', 'note_z', 'note_hidden', 'note_locked', 'reminder_time', 'reminder_repeat',
				'reminder_notified', 'reminder_dismissed', 'reminder_anchor', 'note_snooze', 'note_minimized'],
			$this->shares_table => ['share_id', 'note_id', 'share_type', 'share_target'],
			USERS_TABLE => ['user_stickynotes_enabled', 'user_stickynotes_tray', 'user_stickynotes_notify'],
		];
		foreach ($columns as $table => $cols)
		{
			if (!$this->db_tools->sql_table_exists($table))
			{
				continue;
			}
			foreach ($cols as $col)
			{
				if (!$this->db_tools->sql_column_exists($table, $col))
				{
					$missing[] = $table . '.' . $col;
				}
			}
		}
		$add($l->lang('STICKYNOTES_CHECK_COLUMNS'), empty($missing) ? 'ok' : 'error', implode(', ', $missing));

		// Database write / read / delete test
		$add($l->lang('STICKYNOTES_CHECK_DB_RW'), $this->test_db() ? 'ok' : 'error');

		// Configuration
		$missing = [];
		foreach (['stickynotes_enabled', 'stickynotes_max_notes', 'stickynotes_max_chars', 'stickynotes_tray_pos', 'stickynotes_tray_user',
			'stickynotes_allow_bbcode', 'stickynotes_allow_share', 'stickynotes_allow_reminder', 'stickynotes_mobile_mode',
			'stickynotes_default_color', 'stickynotes_rotation', 'stickynotes_cron_last', 'stickynotes_version',
			'stickynotes_auto_expired_days', 'stickynotes_auto_inactive_days', 'stickynotes_auto_last', 'stickynotes_auto_deleted', 'stickynotes_trash', 'stickynotes_trash_pos'] as $key)
		{
			if (!isset($this->config[$key]))
			{
				$missing[] = $key;
			}
		}
		$add($l->lang('STICKYNOTES_CHECK_CONFIG'), empty($missing) ? 'ok' : 'error', implode(', ', $missing));

		// Permissions
		$founders = $this->count_founders();
		foreach (['u_stickynotes_use', 'u_stickynotes_bbcode', 'u_stickynotes_share', 'u_stickynotes_reminder', 'u_stickynotes_nolimit', 'a_stickynotes'] as $option)
		{
			$label = $l->lang('STICKYNOTES_CHECK_PERMISSION', $option);
			$count = $this->count_permission($option);
			$is_admin = strpos($option, 'a_') === 0;

			if ($count === false)
			{
				$add($label, 'error', $l->lang('STICKYNOTES_CHECK_PERMISSION_MISSING'));
			}
			else if ($count['groups'] + $count['users'] > 0)
			{
				$add($label, 'ok', $l->lang('STICKYNOTES_CHECK_PERMISSION_COUNT', $count['groups'], $count['users']));
			}
			else if ($is_admin && $founders > 0)
			{
				// phpBB always gives every admin permission to founders
				$add($label, 'ok', $l->lang('STICKYNOTES_CHECK_PERMISSION_FOUNDERS', $founders));
			}
			else if ($option === 'u_stickynotes_nolimit')
			{
				// Optional permission: nobody having it is perfectly fine
				$add($label, 'ok', $l->lang('STICKYNOTES_CHECK_PERMISSION_OPTIONAL'));
			}
			else
			{
				$add($label, 'warn', $l->lang($is_admin ? 'STICKYNOTES_CHECK_PERMISSION_NONE_A' : 'STICKYNOTES_CHECK_PERMISSION_NONE_U'));
			}
		}

		// Notifications (types still missing are registered now)
		$registered = [];
		foreach ([note_manager::TYPE_REMINDER, note_manager::TYPE_SHARED] as $type)
		{
			$sql = 'SELECT notification_type_id FROM ' . $this->table_prefix . "notification_types
				WHERE notification_type_name = '" . $this->db->sql_escape($type) . "'";
			$result = $this->db->sql_query($sql);
			$exists = (bool) $this->db->sql_fetchfield('notification_type_id');
			$this->db->sql_freeresult($result);
			if (!$exists)
			{
				$registered[$type] = true;
			}
		}
		if (!empty($registered))
		{
			$this->manager->register_notification_types();
		}

		foreach ([note_manager::TYPE_REMINDER, note_manager::TYPE_SHARED] as $type)
		{
			$sql = 'SELECT notification_type_enabled FROM ' . $this->table_prefix . "notification_types
				WHERE notification_type_name = '" . $this->db->sql_escape($type) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				// Registered automatically the first time one is sent
				$add($l->lang('STICKYNOTES_CHECK_NOTIFICATION', $type), 'warn', $l->lang('STICKYNOTES_CHECK_NOTIFICATION_NEW'));
			}
			else
			{
				$add($l->lang('STICKYNOTES_CHECK_NOTIFICATION', $type), $row['notification_type_enabled'] ? 'ok' : 'error',
					$row['notification_type_enabled']
						? (isset($registered[$type]) ? $l->lang('STICKYNOTES_CHECK_NOTIFICATION_REGISTERED') : '')
						: $l->lang('STICKYNOTES_CHECK_NOTIFICATION_OFF'));
			}
		}

		// Cron for reminders
		$last = (int) $this->config['stickynotes_cron_last'];
		$sql = 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . '
			WHERE reminder_time > 0 AND reminder_notified = 0 AND reminder_time < ' . (time() - 900);
		$result = $this->db->sql_query($sql);
		$late = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		$detail = $last ? $l->lang('STICKYNOTES_CHECK_CRON_LAST', $this->user->format_date($last)) : $l->lang('STICKYNOTES_CHECK_CRON_NEVER');
		if ($late)
		{
			$detail .= ' — ' . $l->lang('STICKYNOTES_CHECK_CRON_LATE', $late);
		}
		$add($l->lang('STICKYNOTES_CHECK_CRON'), $late ? 'warn' : 'ok', $detail);

		// Browser push notifications (phpbb/webpushnotifications)
		if ($this->webpush)
		{
			$admin = $this->root_path . 'adm/index.' . $this->php_ext;
			switch ($this->webpush->get_state())
			{
				case \salvocortesiano\stickynotes\service\webpush::STATE_MISSING:
					$add($l->lang('STICKYNOTES_CHECK_WEBPUSH'), 'info', $l->lang('STICKYNOTES_CHECK_WEBPUSH_MISSING', \salvocortesiano\stickynotes\service\webpush::URL_DOWNLOAD));
				break;

				case \salvocortesiano\stickynotes\service\webpush::STATE_DISABLED:
					$add($l->lang('STICKYNOTES_CHECK_WEBPUSH'), 'info', $l->lang('STICKYNOTES_CHECK_WEBPUSH_DISABLED',
						append_sid($admin, 'i=acp_extensions&amp;mode=main&amp;action=enable_pre&amp;ext_name=' . urlencode(\salvocortesiano\stickynotes\service\webpush::EXT))));
				break;

				case \salvocortesiano\stickynotes\service\webpush::STATE_UNCONFIGURED:
					$add($l->lang('STICKYNOTES_CHECK_WEBPUSH'), 'warn', $l->lang('STICKYNOTES_CHECK_WEBPUSH_OFF',
						append_sid($admin, 'i=-phpbb-webpushnotifications-acp-wpn_acp_module&amp;mode=webpush')));
				break;

				default:
					$add($l->lang('STICKYNOTES_CHECK_WEBPUSH'), 'ok', $this->webpush->is_default_on()
						? $l->lang('STICKYNOTES_CHECK_WEBPUSH_DEFAULT')
						: $l->lang('STICKYNOTES_CHECK_WEBPUSH_USERS', $this->webpush->count_users_enabled()));
			}
		}

		// Automatic cleanup
		$auto_on = (int) $this->config['stickynotes_auto_expired_days'] > 0 || (int) $this->config['stickynotes_auto_inactive_days'] > 0;
		$auto_last = (int) $this->config['stickynotes_auto_last'];
		if (!$auto_on)
		{
			$add($l->lang('STICKYNOTES_CHECK_AUTO'), 'ok', $l->lang('ACP_STICKYNOTES_AUTO_OFF'));
		}
		else
		{
			$late = $auto_last && $auto_last < time() - 3 * 86400;
			$add($l->lang('STICKYNOTES_CHECK_AUTO'), $late ? 'warn' : 'ok', $auto_last
				? $l->lang('ACP_STICKYNOTES_AUTO_LAST', $this->user->format_date($auto_last), (int) $this->config['stickynotes_auto_deleted']) . ($late ? ' — ' . $l->lang('STICKYNOTES_CHECK_AUTO_LATE') : '')
				: $l->lang('ACP_STICKYNOTES_AUTO_NEVER'));
		}

		// Route
		try
		{
			$url = $this->routing_helper->route('salvocortesiano_stickynotes_ajax', ['action' => 'create'], false);
			$add($l->lang('STICKYNOTES_CHECK_ROUTE'), 'ok', $url);
		}
		catch (\Exception $e)
		{
			$add($l->lang('STICKYNOTES_CHECK_ROUTE'), 'error', $e->getMessage());
		}

		// BBCode / text parser
		try
		{
			list($text, $uid, $bitfield, $flags) = $this->manager->parse_text('[b]Sticky[/b] test', true);
			$html = $this->manager->render_text([
				'note_text' => $text, 'bbcode_uid' => $uid, 'bbcode_bitfield' => $bitfield, 'note_flags' => $flags,
			]);
			$ok = strpos($html, 'Sticky') !== false && strpos($html, '[b]') === false;
			$add($l->lang('STICKYNOTES_CHECK_PARSER'), $ok ? 'ok' : 'error', $ok ? '' : strip_tags($html));
		}
		catch (\Exception $e)
		{
			$add($l->lang('STICKYNOTES_CHECK_PARSER'), 'error', $e->getMessage());
		}

		// JSON
		$json = json_encode(['t' => 'àèì <script>'], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
		$add($l->lang('STICKYNOTES_CHECK_JSON'), ($json && strpos($json, '<script>') === false) ? 'ok' : 'error');

		// Files
		$ext_path = $this->ext_manager->get_extension_path('salvocortesiano/stickynotes', true);
		$files = [
			'styles/all/theme/stickynotes.css',
			'styles/all/theme/fonts/caveat-latin-400-normal.woff2',
			'styles/all/theme/fonts/caveat-latin-700-normal.woff2',
			'styles/all/template/js/stickynotes.js',
			'styles/all/template/event/overall_footer_after.html',
			'styles/all/template/event/overall_header_head_append.html',
			'adm/style/acp_stickynotes_management.html',
			'adm/style/acp_stickynotes_maintenance.html',
			'adm/style/js/stickynotes_acp.js',
			'language/it/common.php',
			'language/en/common.php',
		];
		$missing = [];
		foreach ($files as $file)
		{
			if (!is_readable($ext_path . $file))
			{
				$missing[] = $file;
			}
		}
		$add($l->lang('STICKYNOTES_CHECK_FILES'), empty($missing) ? 'ok' : 'error', empty($missing) ? $l->lang('STICKYNOTES_CHECK_FILES_COUNT', count($files)) : implode(', ', $missing));

		// Language files: same keys in Italian and English
		$diff = $this->compare_languages($ext_path);
		$add($l->lang('STICKYNOTES_CHECK_LANG'), empty($diff) ? 'ok' : 'warn', empty($diff) ? '' : implode(', ', array_slice($diff, 0, 15)));

		// Data health
		$sql = 'SELECT COUNT(n.note_id) AS cnt FROM ' . $this->notes_table . ' n
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id
			WHERE u.user_id IS NULL';
		$result = $this->db->sql_query($sql);
		$orphans = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);
		$add($l->lang('STICKYNOTES_CHECK_ORPHANS'), $orphans ? 'warn' : 'ok', $orphans ? $l->lang('STICKYNOTES_CHECK_ORPHANS_FOUND', $orphans) : '');

		$errors = $warnings = $infos = 0;
		foreach ($checks as $check)
		{
			$this->template->assign_block_vars('checks', [
				'LABEL'		=> $check['label'],
				'STATUS'	=> $check['status'],
				'STATUS_TXT'=> $l->lang('STICKYNOTES_CHECK_STATUS_' . strtoupper($check['status'])),
				'DETAIL'	=> $check['detail'],
			]);
			$errors += $check['status'] === 'error' ? 1 : 0;
			$warnings += $check['status'] === 'warn' ? 1 : 0;
			$infos += $check['status'] === 'info' ? 1 : 0;
		}

		$this->template->assign_vars([
			'CHECK_TOTAL'		=> count($checks),
			'CHECK_ERRORS'		=> $errors,
			'CHECK_WARNINGS'	=> $warnings,
			'CHECK_SUMMARY'		=> $l->lang('STICKYNOTES_CHECK_SUMMARY', count($checks), $errors, $warnings) . ($infos ? ' · ' . $l->lang('STICKYNOTES_CHECK_SUMMARY_INFO', $infos) : ''),
		]);
	}

	protected function test_db()
	{
		if (!$this->db_tools->sql_table_exists($this->notes_table))
		{
			return false;
		}

		$this->db->sql_transaction('begin');
		$sql_ary = [
			'user_id'		=> (int) $this->user->data['user_id'],
			'note_title'	=> 'checkup',
			'note_text'		=> '',
			'note_created'	=> time(),
			'note_updated'	=> time(),
		];
		$this->db->sql_query('INSERT INTO ' . $this->notes_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		$id = (int) $this->db->sql_nextid();
		$row = $this->manager->get_note($id);
		$this->db->sql_query('DELETE FROM ' . $this->notes_table . ' WHERE note_id = ' . $id);
		$this->db->sql_transaction('commit');

		return $row && $row['note_title'] === 'checkup' && !$this->manager->get_note($id);
	}

	/**
	 * @return int|false Groups (direct or via role) with the option, false if the option is missing
	 */
	protected function count_founders()
	{
		$result = $this->db->sql_query('SELECT COUNT(user_id) AS cnt FROM ' . USERS_TABLE . ' WHERE user_type = ' . USER_FOUNDER);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count;
	}

	/**
	 * Groups and users that have the option set to YES, directly or through a role
	 *
	 * @return array|false ['groups' => int, 'users' => int], false if the option does not exist
	 */
	protected function count_permission($option)
	{
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = '" . $this->db->sql_escape($option) . "'";
		$result = $this->db->sql_query($sql);
		$option_id = (int) $this->db->sql_fetchfield('auth_option_id');
		$this->db->sql_freeresult($result);

		if (!$option_id)
		{
			return false;
		}

		$count = [];
		foreach (['groups' => [ACL_GROUPS_TABLE, 'group_id'], 'users' => [ACL_USERS_TABLE, 'user_id']] as $key => $info)
		{
			list($table, $column) = $info;

			// Direct setting
			$sql = 'SELECT DISTINCT ' . $column . ' AS id FROM ' . $table . '
				WHERE auth_option_id = ' . $option_id . ' AND auth_setting = ' . ACL_YES;
			$result = $this->db->sql_query($sql);
			$ids = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ids[(int) $row['id']] = true;
			}
			$this->db->sql_freeresult($result);

			// Through a role
			$sql = 'SELECT DISTINCT a.' . $column . ' AS id
				FROM ' . $table . ' a, ' . ACL_ROLES_DATA_TABLE . ' r
				WHERE a.auth_role_id > 0
					AND r.role_id = a.auth_role_id
					AND r.auth_option_id = ' . $option_id . '
					AND r.auth_setting = ' . ACL_YES;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ids[(int) $row['id']] = true;
			}
			$this->db->sql_freeresult($result);

			$count[$key] = count($ids);
		}

		return $count;
	}

	/**
	 * Keys present in one language but missing in the other
	 */
	protected function compare_languages($ext_path)
	{
		$files = ['common', 'info_acp_stickynotes', 'info_ucp_stickynotes', 'permissions_stickynotes'];
		$diff = [];

		foreach ($files as $file)
		{
			$it = $this->load_lang_file($ext_path . 'language/it/' . $file . '.' . $this->php_ext);
			$en = $this->load_lang_file($ext_path . 'language/en/' . $file . '.' . $this->php_ext);

			foreach (array_diff(array_keys($it), array_keys($en)) as $key)
			{
				$diff[] = 'en:' . $key;
			}
			foreach (array_diff(array_keys($en), array_keys($it)) as $key)
			{
				$diff[] = 'it:' . $key;
			}
		}

		return $diff;
	}

	protected function load_lang_file($file)
	{
		if (!is_readable($file))
		{
			return [];
		}

		$lang = [];
		include($file);

		return $lang;
	}
}

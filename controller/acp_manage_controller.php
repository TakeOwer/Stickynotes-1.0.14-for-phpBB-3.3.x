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
use salvocortesiano\stickynotes\service\maintenance;

/**
 * ACP: "Management and statistics" (list of every note) and "Maintenance"
 */
class acp_manage_controller
{
	const PER_PAGE = 25;

	const STATUSES = ['all', 'reminder_future', 'reminder_expired', 'reminder_recurring', 'no_reminder',
		'hidden', 'snoozed', 'minimized', 'locked', 'shared', 'empty', 'bbcode'];

	const SORTS = [
		'updated'	=> 'n.note_updated',
		'created'	=> 'n.note_created',
		'reminder'	=> 'n.reminder_time',
		'title'		=> 'n.note_title',
		'user'		=> 'u.username_clean',
	];

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var note_manager */
	protected $manager;

	/** @var maintenance */
	protected $maintenance;

	protected $root_path;
	protected $php_ext;
	protected $notes_table;
	protected $shares_table;

	/** @var string */
	protected $u_action;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\log\log_interface $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\pagination $pagination,
		note_manager $manager,
		maintenance $maintenance,
		$root_path,
		$php_ext,
		$notes_table,
		$shares_table
	)
	{
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->pagination = $pagination;
		$this->manager = $manager;
		$this->maintenance = $maintenance;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->notes_table = $notes_table;
		$this->shares_table = $shares_table;
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	protected function ajax_url()
	{
		return str_replace('&amp;', '&', $this->u_action) . '&hash=' . generate_link_hash('stickynotes_acp');
	}

	protected function json(array $data)
	{
		$json = new \phpbb\json_response();
		$json->send($data);
	}

	protected function check_ajax_hash()
	{
		if (!check_link_hash($this->request->variable('hash', ''), 'stickynotes_acp'))
		{
			$this->json(['success' => false, 'error' => $this->language->lang('FORM_INVALID')]);
		}
	}

	/**
	 * Find a user id by username (exact, case insensitive)
	 *
	 * @return array|false
	 */
	protected function find_user($username)
	{
		$username = trim($username);
		if ($username === '')
		{
			return false;
		}

		$sql = 'SELECT user_id, username FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	/**
	 * Configuration + texts for adm/style/js/stickynotes_acp.js (all from the language files)
	 */
	protected function assign_js_data($page)
	{
		$keys = ['ACP_STICKYNOTES_TITLE', 'YES', 'NO', 'INFORMATION', 'ACP_STICKYNOTES_AJAX_ERROR', 'ACP_STICKYNOTES_AJAX_ERROR_TITLE',
			'ACP_STICKYNOTES_WORKING', 'ACP_STICKYNOTES_COUNTING', 'ACP_STICKYNOTES_FOUND',
			'ACP_STICKYNOTES_CONFIRM_DELETE_ONE', 'ACP_STICKYNOTES_CONFIRM_BULK', 'ACP_STICKYNOTES_NOTHING_SELECTED',
			'ACP_STICKYNOTES_NOTHING_TO_DO', 'ACP_STICKYNOTES_CONFIRM_TASK', 'ACP_STICKYNOTES_CONFIRM_ALL',
			'ACP_STICKYNOTES_RUNNING', 'ACP_STICKYNOTES_TASK_DONE', 'ACP_STICKYNOTES_AUTO', 'ACP_STICKYNOTES_AUTO_SAVED',
			'ACP_STICKYNOTES_AUTO_NOTHING', 'ACP_STICKYNOTES_AUTO_CONFIRM', 'ACP_STICKYNOTES_AUTO_DONE'];

		$lang = [];
		foreach ($keys as $key)
		{
			$lang[$key] = $this->language->lang($key);
		}

		$this->template->assign_var('STICKYNOTES_ACP_DATA', json_encode([
			'page'	=> $page,
			'ajax'	=> $this->ajax_url(),
			'lang'	=> $lang,
		], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Current state of the automatic cleanup, as a sentence
	 */
	protected function auto_status()
	{
		$last = (int) $this->config['stickynotes_auto_last'];
		$auto_on = (int) $this->config['stickynotes_auto_expired_days'] > 0 || (int) $this->config['stickynotes_auto_inactive_days'] > 0;

		return [$auto_on, !$auto_on
			? $this->language->lang('ACP_STICKYNOTES_AUTO_OFF')
			: ($last
				? $this->language->lang('ACP_STICKYNOTES_AUTO_LAST', $this->user->format_date($last), (int) $this->config['stickynotes_auto_deleted'])
				: $this->language->lang('ACP_STICKYNOTES_AUTO_NEVER'))];
	}

	protected function find_username_url($form, $field)
	{
		return append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=' . $form . '&amp;field=' . $field . '&amp;select_single=true');
	}

	/* ==================================================================
	 * MANAGEMENT AND STATISTICS
	 * ================================================================== */

	public function display_management()
	{
		$action = $this->request->variable('action', '');
		if ($action !== '' && $this->request->is_ajax())
		{
			$this->check_ajax_hash();
			$this->management_ajax($action);
			return;
		}

		$this->assign_stats();
		$this->assign_top_users();
		$this->assign_note_list();

		$this->assign_js_data('management');
		$this->template->assign_vars([
			'U_STICKYNOTES_MAINT'	=> str_replace('mode=management', 'mode=maintenance', $this->u_action),
		]);
	}

	protected function assign_stats()
	{
		$stats = $this->manager->get_stats();
		foreach ($stats as $key => $value)
		{
			$this->template->assign_var('STICKYNOTES_STAT_' . strtoupper($key), $value);
		}

		// Colour distribution
		$total = max(1, $stats['total']);
		$sql = 'SELECT note_color, COUNT(note_id) AS cnt FROM ' . $this->notes_table . '
			GROUP BY note_color ORDER BY cnt DESC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('colors', [
				'COLOR'		=> $row['note_color'],
				'NAME'		=> $this->language->lang('STICKYNOTES_COLOR_' . strtoupper($row['note_color'])),
				'COUNT'		=> (int) $row['cnt'],
				'PERCENT'	=> round($row['cnt'] / $total * 100, 1),
			]);
		}
		$this->db->sql_freeresult($result);
	}

	protected function assign_top_users()
	{
		$sql = 'SELECT n.user_id, u.username, u.user_colour, COUNT(n.note_id) AS total, MAX(n.note_updated) AS last_update,
				SUM(CASE WHEN n.reminder_time > 0 AND n.reminder_repeat = 0 AND n.reminder_time <= ' . time() . ' THEN 1 ELSE 0 END) AS expired
			FROM ' . $this->notes_table . ' n
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id
			GROUP BY n.user_id, u.username, u.user_colour
			ORDER BY total DESC';
		$result = $this->db->sql_query_limit($sql, 10);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('top_users', [
				'USERNAME'		=> $row['username'] !== null
					? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])
					: $this->language->lang('STICKYNOTES_ORPHAN_USER', (int) $row['user_id']),
				'TOTAL'			=> (int) $row['total'],
				'EXPIRED'		=> (int) $row['expired'],
				'LAST_UPDATE'	=> $this->user->format_date((int) $row['last_update']),
				'U_FILTER'		=> $row['username'] !== null ? $this->u_action . '&amp;sn_user=' . urlencode($row['username']) . '#sn-list' : '',
			]);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Read and validate the list filters
	 */
	protected function get_filters()
	{
		$f = [
			'user'		=> $this->request->variable('sn_user', '', true),
			'q'			=> $this->request->variable('sn_q', '', true),
			'color'		=> $this->request->variable('sn_color', ''),
			'status'	=> $this->request->variable('sn_status', 'all'),
			'scope'		=> $this->request->variable('sn_scope', ''),
			'sort'		=> $this->request->variable('sn_sort', 'updated'),
			'dir'		=> $this->request->variable('sn_dir', 'd'),
		];

		if (!in_array($f['color'], note_manager::COLORS, true))
		{
			$f['color'] = '';
		}
		if (!in_array($f['status'], self::STATUSES, true))
		{
			$f['status'] = 'all';
		}
		if (!in_array($f['scope'], ['global', 'page'], true))
		{
			$f['scope'] = '';
		}
		if (!isset(self::SORTS[$f['sort']]))
		{
			$f['sort'] = 'updated';
		}
		$f['dir'] = $f['dir'] === 'a' ? 'a' : 'd';

		return $f;
	}

	protected function build_where(array $f)
	{
		$now = time();
		$where = ['1 = 1'];

		if ($f['user'] !== '')
		{
			$where[] = "u.username_clean = '" . $this->db->sql_escape(utf8_clean_string($f['user'])) . "'";
		}
		if ($f['q'] !== '')
		{
			$like = $this->db->sql_like_expression($this->db->get_any_char() . utf8_strtolower($f['q']) . $this->db->get_any_char());
			$where[] = '(LOWER(n.note_title) ' . $like . ' OR LOWER(n.note_text) ' . $like . ')';
		}
		if ($f['color'] !== '')
		{
			$where[] = "n.note_color = '" . $this->db->sql_escape($f['color']) . "'";
		}
		if ($f['scope'] !== '')
		{
			$where[] = "n.note_scope = '" . $this->db->sql_escape($f['scope']) . "'";
		}

		switch ($f['status'])
		{
			case 'reminder_future':
				$where[] = 'n.reminder_time > ' . $now;
			break;
			case 'reminder_expired':
				$where[] = 'n.reminder_time > 0 AND n.reminder_repeat = 0 AND n.reminder_time <= ' . $now;
			break;
			case 'reminder_recurring':
				$where[] = 'n.reminder_time > 0 AND n.reminder_repeat > 0';
			break;
			case 'no_reminder':
				$where[] = 'n.reminder_time = 0';
			break;
			case 'hidden':
				$where[] = 'n.note_hidden = 1';
			break;
			case 'snoozed':
				$where[] = 'n.note_snooze > ' . $now;
			break;
			case 'minimized':
				$where[] = 'n.note_minimized = 1';
			break;
			case 'locked':
				$where[] = 'n.note_locked = 1';
			break;
			case 'shared':
				$where[] = 'n.note_id IN (SELECT s.note_id FROM ' . $this->shares_table . ' s)';
			break;
			case 'empty':
				$where[] = "n.note_title = '' AND n.note_text = ''";
			break;
			case 'bbcode':
				$where[] = 'n.note_format = 1';
			break;
		}

		return implode(' AND ', $where);
	}

	protected function assign_note_list()
	{
		$f = $this->get_filters();
		$where = $this->build_where($f);
		$from = $this->notes_table . ' n LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id';

		$sql = 'SELECT COUNT(n.note_id) AS cnt FROM ' . $from . ' WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		$start = $this->pagination->validate_start($this->request->variable('start', 0), self::PER_PAGE, $total);

		$sql = 'SELECT n.*, u.username, u.user_colour
			FROM ' . $from . '
			WHERE ' . $where . '
			ORDER BY ' . self::SORTS[$f['sort']] . ($f['dir'] === 'a' ? ' ASC' : ' DESC') . ', n.note_id DESC';
		$result = $this->db->sql_query_limit($sql, self::PER_PAGE, $start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$shares = $this->manager->get_shares_for_notes(array_column($rows, 'note_id'));
		$now = time();
		$repeat_keys = ['STICKYNOTES_REPEAT_NONE', 'STICKYNOTES_REPEAT_DAILY', 'STICKYNOTES_REPEAT_WEEKLY', 'STICKYNOTES_REPEAT_MONTHLY'];

		foreach ($rows as $row)
		{
			$id = (int) $row['note_id'];
			$excerpt = trim(preg_replace('#\s+#', ' ', strip_tags($this->manager->render_text($row))));
			$reminder = (int) $row['reminder_time'];

			if (!$reminder)
			{
				$reminder_state = '';
			}
			else if ((int) $row['reminder_repeat'] > 0)
			{
				$reminder_state = 'recurring';
			}
			else
			{
				$reminder_state = $reminder <= $now ? 'expired' : 'future';
			}

			$this->template->assign_block_vars('notes', [
				'ID'				=> $id,
				'TITLE'				=> $row['note_title'] !== '' ? $row['note_title'] : $this->language->lang('STICKYNOTES_UNTITLED'),
				'S_UNTITLED'		=> $row['note_title'] === '',
				'EXCERPT'			=> utf8_strlen($excerpt) > 90 ? utf8_substr($excerpt, 0, 90) . '…' : $excerpt,
				'COLOR'				=> $row['note_color'],
				'COLOR_NAME'		=> $this->language->lang('STICKYNOTES_COLOR_' . strtoupper($row['note_color'])),
				'OWNER'				=> $row['username'] !== null
					? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])
					: $this->language->lang('STICKYNOTES_ORPHAN_USER', (int) $row['user_id']),
				'SCOPE'				=> $row['note_scope'] === 'page'
					? $this->language->lang('STICKYNOTES_SCOPE_PAGE') . ': ' . $row['note_page']
					: $this->language->lang('STICKYNOTES_SCOPE_GLOBAL'),
				'REMINDER'			=> $reminder ? $this->user->format_date($reminder) : '',
				'REMINDER_STATE'	=> $reminder_state,
				'REPEAT'			=> (int) $row['reminder_repeat'] ? $this->language->lang($repeat_keys[(int) $row['reminder_repeat']]) : '',
				'S_HIDDEN'			=> (bool) $row['note_hidden'],
				'S_SNOOZED'			=> (int) $row['note_snooze'] > $now,
				'SNOOZE'			=> (int) $row['note_snooze'] > $now ? $this->user->format_date((int) $row['note_snooze']) : '',
				'S_MINIMIZED'		=> (bool) $row['note_minimized'],
				'S_LOCKED'			=> (bool) $row['note_locked'],
				'S_BBCODE'			=> (int) $row['note_format'] === 1,
				'SHARES'			=> isset($shares[$id]) ? htmlspecialchars(implode(', ', array_column($shares[$id], 'name')), ENT_COMPAT, 'UTF-8') : '',
				'CREATED'			=> $this->user->format_date((int) $row['note_created']),
				'UPDATED'			=> $this->user->format_date((int) $row['note_updated']),
			]);
		}

		// Filters: keep them in the pagination and sort links
		$params = [];
		foreach (['user' => 'sn_user', 'q' => 'sn_q', 'color' => 'sn_color', 'status' => 'sn_status', 'scope' => 'sn_scope', 'sort' => 'sn_sort', 'dir' => 'sn_dir'] as $key => $param)
		{
			if ($f[$key] !== '' && !($key === 'status' && $f[$key] === 'all'))
			{
				$params[] = $param . '=' . urlencode($f[$key]);
			}
		}
		$base_url = $this->u_action . ($params ? '&amp;' . implode('&amp;', $params) : '');
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, self::PER_PAGE, $start);

		foreach (self::STATUSES as $status)
		{
			$this->template->assign_block_vars('filter_status', [
				'VALUE'		=> $status,
				'LABEL'		=> $this->language->lang('ACP_STICKYNOTES_FILTER_' . strtoupper($status)),
				'S_SELECTED'=> $f['status'] === $status,
			]);
		}
		foreach (note_manager::COLORS as $color)
		{
			$this->template->assign_block_vars('filter_colors', [
				'VALUE'		=> $color,
				'LABEL'		=> $this->language->lang('STICKYNOTES_COLOR_' . strtoupper($color)),
				'S_SELECTED'=> $f['color'] === $color,
			]);
		}
		foreach (array_keys(self::SORTS) as $sort)
		{
			$this->template->assign_block_vars('filter_sorts', [
				'VALUE'		=> $sort,
				'LABEL'		=> $this->language->lang('ACP_STICKYNOTES_SORT_' . strtoupper($sort)),
				'S_SELECTED'=> $f['sort'] === $sort,
			]);
		}

		$filtered = $f['user'] !== '' || $f['q'] !== '' || $f['color'] !== '' || $f['scope'] !== '' || $f['status'] !== 'all';

		$this->template->assign_vars([
			'SN_FILTER_USER'		=> $f['user'],
			'SN_FILTER_Q'			=> $f['q'],
			'SN_FILTER_SCOPE'		=> $f['scope'],
			'SN_FILTER_DIR'			=> $f['dir'],
			'S_SN_FILTERED'			=> $filtered,
			'SN_LIST_TOTAL'			=> $this->language->lang('ACP_STICKYNOTES_LIST_TOTAL', $total),
			'S_SN_HIDDEN_FIELDS'	=> build_hidden_fields([
				'i'		=> $this->request->variable('i', ''),
				'mode'	=> 'management',
				'sid'	=> $this->user->session_id,
			]),
			'U_SN_RESET_FILTERS'	=> $this->u_action . '#sn-list',
			'U_SN_FIND_USER'		=> $this->find_username_url('sn_filters', 'sn_user'),
		]);
	}

	protected function management_ajax($action)
	{
		switch ($action)
		{
			case 'view':
				$row = $this->manager->get_note($this->request->variable('id', 0));
				if (!$row)
				{
					$this->json(['success' => false, 'error' => $this->language->lang('STICKYNOTES_ERR_NOT_FOUND')]);
				}

				$owner = $this->get_username((int) $row['user_id']);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_VIEWED', false, [$owner, $row['note_title']]);

				$html = $this->manager->render_text($row);
				$this->json([
					'success'	=> true,
					'title'		=> $row['note_title'] !== '' ? html_entity_decode($row['note_title'], ENT_QUOTES, 'UTF-8') : $this->language->lang('STICKYNOTES_UNTITLED'),
					'html'		=> '<div class="sn-acp-view sn-acp-paper-' . $row['note_color'] . '">'
						. '<div class="sn-acp-view-meta">' . $this->language->lang('ACP_STICKYNOTES_VIEW_META', $owner, $this->user->format_date((int) $row['note_updated'])) . '</div>'
						. '<div class="sn-acp-view-body">' . ($html !== '' ? $html : '<em>' . $this->language->lang('ACP_STICKYNOTES_VIEW_EMPTY') . '</em>') . '</div></div>',
				]);
			break;

			case 'bulk':
				$op = $this->request->variable('op', '');
				$ids = array_filter(array_map('intval', $this->request->variable('ids', [0])));
				if (!in_array($op, ['delete', 'unset_reminder', 'reset', 'wake'], true) || empty($ids))
				{
					$this->json(['success' => false, 'error' => $this->language->lang('ACP_STICKYNOTES_NOTHING_SELECTED')]);
				}

				$affected = $this->maintenance->apply($op, $ids);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_BULK',
					false, [$this->language->lang('ACP_STICKYNOTES_BULK_' . strtoupper($op)), $affected]);

				$this->json([
					'success'	=> true,
					'message'	=> $this->language->lang('ACP_STICKYNOTES_BULK_DONE', $affected),
				]);
			break;
		}

		$this->json(['success' => false, 'error' => $this->language->lang('STICKYNOTES_ERR_ACTION')]);
	}

	protected function get_username($user_id)
	{
		$sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$name = $this->db->sql_fetchfield('username');
		$this->db->sql_freeresult($result);

		return $name !== false ? $name : $this->language->lang('STICKYNOTES_ORPHAN_USER', (int) $user_id);
	}

	/* ==================================================================
	 * MAINTENANCE
	 * ================================================================== */

	public function display_maintenance()
	{
		$action = $this->request->variable('action', '');

		if ($action === 'export')
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'stickynotes_acp'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$this->export_csv();
			return;
		}

		if ($action !== '' && $this->request->is_ajax())
		{
			$this->check_ajax_hash();
			$this->maintenance_ajax($action);
			return;
		}

		add_form_key('stickynotes_auto');
		if ($this->request->is_set_post('submit_auto'))
		{
			if (!check_form_key('stickynotes_auto'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->config->set('stickynotes_auto_expired_days', max(0, min(3650, $this->request->variable('stickynotes_auto_expired_days', 0))));
			$this->config->set('stickynotes_auto_inactive_days', max(0, min(3650, $this->request->variable('stickynotes_auto_inactive_days', 0))));
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_AUTO_SETTINGS');

			trigger_error($this->language->lang('ACP_STICKYNOTES_SAVED') . adm_back_link($this->u_action));
		}

		$stats = $this->manager->get_stats();
		list($auto_on, $auto_status) = $this->auto_status();

		$this->assign_js_data('maintenance');
		$this->template->assign_vars([
			'U_STICKYNOTES_EXPORT'			=> $this->u_action . '&amp;action=export&amp;hash=' . generate_link_hash('stickynotes_acp'),
			'U_SN_FIND_USER'				=> $this->find_username_url('sn_maint', 'sn_maint_user'),
			'STICKYNOTES_AUTO_EXPIRED_DAYS'	=> (int) $this->config['stickynotes_auto_expired_days'],
			'STICKYNOTES_AUTO_INACTIVE_DAYS'=> (int) $this->config['stickynotes_auto_inactive_days'],
			'S_STICKYNOTES_AUTO_ON'			=> $auto_on,
			'STICKYNOTES_AUTO_STATUS'		=> $auto_status,
			'STICKYNOTES_STAT_TOTAL'		=> $stats['total'],
			'STICKYNOTES_STAT_EXPIRED'		=> $stats['expired'],
			'STICKYNOTES_STAT_INACTIVE'		=> $stats['inactive'],
			'STICKYNOTES_STAT_EMPTY'		=> $stats['empty'],
		]);
	}

	/**
	 * Parameters of a maintenance task sent by the page
	 */
	protected function task_params($task)
	{
		$params = ['days' => $this->request->variable('days', 0)];

		if ($task === 'user')
		{
			$user = $this->find_user($this->request->variable('username', '', true));
			if (!$user)
			{
				$this->json(['success' => false, 'error' => $this->language->lang('NO_USER')]);
			}
			$params['user_id'] = (int) $user['user_id'];
			$params['username'] = $user['username'];
		}

		if ($task === 'inactive' && $params['days'] < 1)
		{
			$this->json(['success' => false, 'error' => $this->language->lang('ACP_STICKYNOTES_ERR_DAYS')]);
		}

		return $params;
	}

	protected function maintenance_ajax($action)
	{
		if ($action === 'save_auto' || $action === 'auto_mark')
		{
			if ($action === 'save_auto')
			{
				$this->config->set('stickynotes_auto_expired_days', max(0, min(3650, $this->request->variable('expired_days', 0))));
				$this->config->set('stickynotes_auto_inactive_days', max(0, min(3650, $this->request->variable('inactive_days', 0))));
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_AUTO_SETTINGS');
			}
			else
			{
				// "Run now" finished: record it like the cron does
				$affected = max(0, $this->request->variable('affected', 0));
				$this->config->set('stickynotes_auto_last', time(), false);
				$this->config->set('stickynotes_auto_deleted', $affected, false);
				$this->maintenance->cleanup_shares();
			}

			list($auto_on, $status) = $this->auto_status();
			$this->json(['success' => true, 'auto_on' => $auto_on, 'status' => $status]);
		}

		$task = $this->request->variable('task', '');
		if (!$this->maintenance->is_task($task))
		{
			$this->json(['success' => false, 'error' => $this->language->lang('STICKYNOTES_ERR_ACTION')]);
		}
		$params = $this->task_params($task);

		switch ($action)
		{
			// How many notes would be touched (shown in the confirm box)
			case 'count':
				$this->json([
					'success'	=> true,
					'count'		=> $this->maintenance->count($task, $params),
				]);
			break;

			// One batch: the page calls it until finished, showing the real percentage
			case 'run':
				$total = $this->request->variable('total', 0);
				$step = $this->maintenance->step($task, $params, $this->request->variable('last_id', 0));

				$shares = 0;
				if ($step['finished'])
				{
					$shares = $this->maintenance->cleanup_shares();
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_MAINT', false, [
						$this->language->lang('ACP_STICKYNOTES_TASK_' . strtoupper($task)),
						$this->request->variable('affected', 0) + $step['affected'],
					]);
				}

				$this->json(array_merge(['success' => true, 'total' => $total, 'shares' => $shares], $step));
			break;
		}

		$this->json(['success' => false, 'error' => $this->language->lang('STICKYNOTES_ERR_ACTION')]);
	}

	/**
	 * CSV with every note (UTF-8 with BOM, opens correctly in Excel/LibreOffice)
	 */
	protected function export_csv()
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STICKYNOTES_EXPORT');

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="stickynotes-' . date('Y-m-d') . '.csv"');
		header('Cache-Control: no-store');

		$out = fopen('php://output', 'w');
		fwrite($out, "\xEF\xBB\xBF");

		$l = $this->language;
		fputcsv($out, [
			'ID', $l->lang('USERNAME'), $l->lang('ACP_STICKYNOTES_COL_TITLE'), $l->lang('ACP_STICKYNOTES_COL_TEXT'),
			$l->lang('STICKYNOTES_COLOR'), $l->lang('STICKYNOTES_SCOPE'), $l->lang('ACP_STICKYNOTES_COL_PAGE'),
			$l->lang('STICKYNOTES_REMINDER'), $l->lang('STICKYNOTES_REMINDER_REPEAT'), $l->lang('ACP_STICKYNOTES_COL_SHARED'),
			$l->lang('ACP_STICKYNOTES_COL_CREATED'), $l->lang('ACP_STICKYNOTES_COL_UPDATED'),
		], ';');

		$repeat_keys = ['STICKYNOTES_REPEAT_NONE', 'STICKYNOTES_REPEAT_DAILY', 'STICKYNOTES_REPEAT_WEEKLY', 'STICKYNOTES_REPEAT_MONTHLY'];
		$last_id = 0;

		do
		{
			$sql = 'SELECT n.*, u.username FROM ' . $this->notes_table . ' n
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id
				WHERE n.note_id > ' . $last_id . '
				ORDER BY n.note_id ASC';
			$result = $this->db->sql_query_limit($sql, 500);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			$shares = $this->manager->get_shares_for_notes(array_column($rows, 'note_id'));

			foreach ($rows as $row)
			{
				$id = (int) $row['note_id'];
				$last_id = $id;
				$text = html_entity_decode(trim(strip_tags(str_replace(['<br>', '<br />'], "\n", $this->manager->render_text($row)))), ENT_QUOTES, 'UTF-8');

				fputcsv($out, [
					$id,
					$row['username'] !== null ? html_entity_decode($row['username'], ENT_QUOTES, 'UTF-8') : $l->lang('STICKYNOTES_ORPHAN_USER', (int) $row['user_id']),
					html_entity_decode($row['note_title'], ENT_QUOTES, 'UTF-8'),
					$text,
					$l->lang('STICKYNOTES_COLOR_' . strtoupper($row['note_color'])),
					$l->lang($row['note_scope'] === 'page' ? 'STICKYNOTES_SCOPE_PAGE' : 'STICKYNOTES_SCOPE_GLOBAL'),
					$row['note_scope'] === 'page' ? $row['note_page'] : '',
					(int) $row['reminder_time'] ? date('Y-m-d H:i', (int) $row['reminder_time']) : '',
					$l->lang($repeat_keys[(int) $row['reminder_repeat']] ?? 'STICKYNOTES_REPEAT_NONE'),
					isset($shares[$id]) ? implode(', ', array_column($shares[$id], 'name')) : '',
					date('Y-m-d H:i', (int) $row['note_created']),
					date('Y-m-d H:i', (int) $row['note_updated']),
				], ';');
			}
		}
		while (count($rows) === 500);

		fclose($out);

		garbage_collection();
		exit_handler();
	}
}

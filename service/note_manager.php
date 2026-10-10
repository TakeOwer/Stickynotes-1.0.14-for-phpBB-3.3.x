<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\service;

/**
 * Central service: storage, rendering, sharing and reminders of the notes.
 */
class note_manager
{
	const COLORS = ['yellow', 'pink', 'blue', 'green', 'orange', 'purple', 'white'];
	const TRAY_POSITIONS = ['tl', 'tr', 'bl', 'br'];

	const SHARE_USER = 0;
	const SHARE_GROUP = 1;

	const REPEAT_NONE = 0;
	const REPEAT_DAILY = 1;
	const REPEAT_WEEKLY = 2;
	const REPEAT_MONTHLY = 3;

	const NOTIFY_POPUP = 0;
	const NOTIFY_BELL = 1;
	const NOTIFY_BOTH = 2;

	const TYPE_REMINDER = 'salvocortesiano.stickynotes.notification.type.reminder';
	const TYPE_SHARED = 'salvocortesiano.stickynotes.notification.type.shared';

	const TITLE_MAX = 100;
	const MIN_W = 160;
	const MAX_W = 640;
	const MIN_H = 120;
	const MAX_H = 640;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $notes_table;

	/** @var string */
	protected $shares_table;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\notification\manager $notification_manager,
		$root_path,
		$php_ext,
		$notes_table,
		$shares_table
	)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->notification_manager = $notification_manager;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->notes_table = $notes_table;
		$this->shares_table = $shares_table;
	}

	public function get_notes_table()
	{
		return $this->notes_table;
	}

	public function get_shares_table()
	{
		return $this->shares_table;
	}

	/* ------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Normalise a page key ("viewtopic.php?t=12", "index.php", "app.php/route")
	 */
	public function sanitize_page_key($key)
	{
		$key = trim((string) $key);
		$key = preg_replace('#^\./#', '', $key);

		if ($key === '' || strlen($key) > 255 || strpos($key, '..') !== false || $key[0] === '/' || !preg_match('#^[A-Za-z0-9_./\-]+(\?[ft]=\d+)?$#', $key))
		{
			return 'index.' . $this->php_ext;
		}

		return $key;
	}

	public function sanitize_color($color)
	{
		return in_array($color, self::COLORS, true) ? $color : $this->get_default_color();
	}

	public function get_default_color()
	{
		$color = (string) $this->config['stickynotes_default_color'];
		return in_array($color, self::COLORS, true) ? $color : 'yellow';
	}

	public function clamp($value, $min, $max)
	{
		return max($min, min($max, (int) $value));
	}

	/**
	 * Tray position for a user: personal choice (if allowed) or board default
	 */
	public function get_tray_position(array $user_row)
	{
		$pos = (string) $this->config['stickynotes_tray_pos'];
		if (!empty($this->config['stickynotes_tray_user']) && !empty($user_row['user_stickynotes_tray'])
			&& in_array($user_row['user_stickynotes_tray'], self::TRAY_POSITIONS, true))
		{
			$pos = $user_row['user_stickynotes_tray'];
		}

		return in_array($pos, self::TRAY_POSITIONS, true) ? $pos : 'bl';
	}

	/**
	 * Parse a note text, with or without BBCode
	 *
	 * @return array [text, uid, bitfield, flags]
	 */
	/**
	 * Parse a note text, with or without BBCode
	 *
	 * @return array [text, uid, bitfield, flags, warnings]
	 */
	public function parse_text($text, $bbcode)
	{
		$uid = $bitfield = '';
		$flags = 0;
		$bbcode = (bool) $bbcode;

		// Empty note: nothing to parse. (phpBB's message parser would complain
		// "message too short" using a string of the posting language, which is
		// not loaded in AJAX requests: that printed a PHP warning before the JSON.)
		if (trim($text) === '')
		{
			return ['', '', '', 0, []];
		}

		if (!function_exists('generate_text_for_storage'))
		{
			include($this->root_path . 'includes/functions_content.' . $this->php_ext);
		}

		// Texts used by the message parser for its warnings
		$this->user->add_lang('posting');

		$warnings = generate_text_for_storage($text, $uid, $bitfield, $flags, $bbcode, true, $bbcode);

		return [$text, $uid, $bitfield, $flags, is_array($warnings) ? array_filter($warnings) : []];
	}

	public function render_text(array $row)
	{
		if ($row['note_text'] === '')
		{
			return '';
		}

		if (!function_exists('generate_text_for_display'))
		{
			include($this->root_path . 'includes/functions_content.' . $this->php_ext);
		}

		return generate_text_for_display($row['note_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], (int) $row['note_flags']);
	}

	/**
	 * Short plain-text extract of a note (bell dropdown and browser push notifications)
	 */
	public function excerpt(array $row, $length = 140)
	{
		// Works from the BBCode source: it does not depend on the current user
		// (censor options), so it is safe in the CLI cron as well
		$text = preg_replace('#\[/?(?:[a-z*][a-z0-9_*-]*)(?:=[^\]]*)?(?:\s[^\]]*)?\]#i', '', $this->edit_text($row));
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
		$text = trim(preg_replace('#\s+#u', ' ', $text));

		return utf8_strlen($text) > $length ? rtrim(utf8_substr($text, 0, $length - 1)) . '…' : $text;
	}

	public function edit_text(array $row)
	{
		if ($row['note_text'] === '')
		{
			return '';
		}

		if (!function_exists('generate_text_for_edit'))
		{
			include($this->root_path . 'includes/functions_content.' . $this->php_ext);
		}

		$data = generate_text_for_edit($row['note_text'], $row['bbcode_uid'], (int) $row['note_flags']);
		return html_entity_decode($data['text'], ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Convert a DB row to the array sent to JavaScript
	 */
	public function to_array(array $row, $own = true, array $shares = [])
	{
		return [
			'id'		=> (int) $row['note_id'],
			'owner'		=> (int) $row['user_id'],
			'ownerName'	=> isset($row['username']) ? html_entity_decode($row['username'], ENT_QUOTES, 'UTF-8') : '',
			'own'		=> (bool) $own,
			'title'		=> html_entity_decode($row['note_title'], ENT_QUOTES, 'UTF-8'),
			'html'		=> $this->render_text($row),
			'text'		=> $own ? $this->edit_text($row) : '',
			'format'	=> (int) $row['note_format'],
			'color'		=> $row['note_color'],
			'scope'		=> $row['note_scope'],
			'page'		=> $row['note_page'],
			'x'			=> (int) $row['pos_x'],
			'y'			=> (int) $row['pos_y'],
			'w'			=> (int) $row['note_width'],
			'h'			=> (int) $row['note_height'],
			'z'			=> (int) $row['note_z'],
			'rot'		=> (int) $row['note_rot'],
			'hidden'	=> (bool) $row['note_hidden'],
			'locked'	=> (bool) $row['note_locked'],
			'minimized'	=> !empty($row['note_minimized']),
			'snooze'	=> (isset($row['note_snooze']) && (int) $row['note_snooze'] > time()) ? (int) $row['note_snooze'] : 0,
			'reminder'	=> (int) $row['reminder_time'],
			'repeat'	=> (int) $row['reminder_repeat'],
			'updated'	=> (int) $row['note_updated'],
			'shares'	=> $shares,
		];
	}

	/* ------------------------------------------------------------------
	 * Reading
	 * ------------------------------------------------------------------ */

	public function get_note($note_id)
	{
		$sql = 'SELECT * FROM ' . $this->notes_table . ' WHERE note_id = ' . (int) $note_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function count_user_notes($user_id)
	{
		$sql = 'SELECT COUNT(note_id) AS total FROM ' . $this->notes_table . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function can_create($user_id)
	{
		if ($this->auth->acl_get('u_stickynotes_nolimit'))
		{
			return true;
		}

		$max = (int) $this->config['stickynotes_max_notes'];
		return $max <= 0 || $this->count_user_notes($user_id) < $max;
	}

	/**
	 * Own notes, optionally only those visible on a page (global + that page)
	 */
	public function get_user_notes($user_id, $page_key = null)
	{
		$where = 'user_id = ' . (int) $user_id;
		if ($page_key !== null)
		{
			$where .= " AND (note_scope = 'global' OR note_page = '" . $this->db->sql_escape($page_key) . "')";
		}

		$sql = 'SELECT * FROM ' . $this->notes_table . ' WHERE ' . $where . ' ORDER BY note_z ASC, note_id ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$shares = $this->get_shares_for_notes(array_column($rows, 'note_id'));

		$notes = [];
		foreach ($rows as $row)
		{
			$id = (int) $row['note_id'];
			$notes[] = $this->to_array($row, true, isset($shares[$id]) ? $shares[$id] : []);
		}

		return $notes;
	}

	/**
	 * Notes other users shared with this user (directly or via a group)
	 */
	public function get_shared_notes($user_id)
	{
		$group_ids = array_keys($this->get_user_groups($user_id, false));

		$conditions = ['(s.share_type = ' . self::SHARE_USER . ' AND s.share_target = ' . (int) $user_id . ')'];
		if (!empty($group_ids))
		{
			$conditions[] = '(s.share_type = ' . self::SHARE_GROUP . ' AND ' . $this->db->sql_in_set('s.share_target', $group_ids) . ')';
		}

		$sql = 'SELECT n.*, u.username
			FROM ' . $this->shares_table . ' s
			INNER JOIN ' . $this->notes_table . ' n ON n.note_id = s.note_id
			INNER JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id
			WHERE (' . implode(' OR ', $conditions) . ')
				AND n.user_id <> ' . (int) $user_id . '
			ORDER BY n.note_updated DESC';
		$result = $this->db->sql_query_limit($sql, 100);

		$notes = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notes[(int) $row['note_id']] = $this->to_array($row, false);
		}
		$this->db->sql_freeresult($result);

		return array_values($notes);
	}

	public function can_view_shared($note_id, $user_id)
	{
		foreach ($this->get_shared_notes($user_id) as $note)
		{
			if ($note['id'] === (int) $note_id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array note_id => list of shares [type, id, name]
	 */
	public function get_shares_for_notes(array $note_ids)
	{
		$note_ids = array_map('intval', $note_ids);
		if (empty($note_ids))
		{
			return [];
		}

		$sql = 'SELECT s.note_id, s.share_type, s.share_target, u.username, g.group_name, g.group_type
			FROM ' . $this->shares_table . ' s
			LEFT JOIN ' . USERS_TABLE . ' u ON (s.share_type = ' . self::SHARE_USER . ' AND u.user_id = s.share_target)
			LEFT JOIN ' . GROUPS_TABLE . ' g ON (s.share_type = ' . self::SHARE_GROUP . ' AND g.group_id = s.share_target)
			WHERE ' . $this->db->sql_in_set('s.note_id', $note_ids) . '
			ORDER BY s.share_type ASC, s.share_id ASC';
		$result = $this->db->sql_query($sql);

		$shares = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ((int) $row['share_type'] === self::SHARE_USER)
			{
				$name = (string) $row['username'];
			}
			else
			{
				$name = $this->group_name($row);
			}

			if ($name === '')
			{
				continue;
			}

			$shares[(int) $row['note_id']][] = [
				'type'	=> (int) $row['share_type'],
				'id'	=> (int) $row['share_target'],
				'name'	=> html_entity_decode($name, ENT_QUOTES, 'UTF-8'),
			];
		}
		$this->db->sql_freeresult($result);

		return $shares;
	}

	protected function group_name(array $row)
	{
		if (empty($row['group_name']))
		{
			return '';
		}

		if ((int) $row['group_type'] === GROUP_SPECIAL)
		{
			$key = 'G_' . $row['group_name'];
			return isset($this->user->lang[$key]) ? $this->user->lang[$key] : $row['group_name'];
		}

		return $row['group_name'];
	}

	/**
	 * Groups the user is an approved member of (bots and guests excluded)
	 *
	 * @return array group_id => display name
	 */
	public function get_user_groups($user_id, $with_names = true)
	{
		$sql = 'SELECT g.group_id, g.group_name, g.group_type
			FROM ' . USER_GROUP_TABLE . ' ug
			INNER JOIN ' . GROUPS_TABLE . ' g ON g.group_id = ug.group_id
			WHERE ug.user_id = ' . (int) $user_id . '
				AND ug.user_pending = 0
				AND ' . $this->db->sql_in_set('g.group_name', ['GUESTS', 'BOTS'], true) . '
			ORDER BY g.group_type DESC, g.group_name ASC';
		$result = $this->db->sql_query($sql);

		$groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[(int) $row['group_id']] = $with_names ? $this->group_name($row) : true;
		}
		$this->db->sql_freeresult($result);

		return $groups;
	}

	/* ------------------------------------------------------------------
	 * Writing
	 * ------------------------------------------------------------------ */

	public function create($user_id, array $data)
	{
		$now = time();
		$sql_ary = [
			'user_id'			=> (int) $user_id,
			'note_title'		=> '',
			'note_text'			=> '',
			'bbcode_uid'		=> '',
			'bbcode_bitfield'	=> '',
			'note_flags'		=> 0,
			'note_format'		=> 0,
			'note_color'		=> $this->sanitize_color(isset($data['color']) ? $data['color'] : ''),
			'note_scope'		=> (isset($data['scope']) && $data['scope'] === 'page') ? 'page' : 'global',
			'note_page'			=> $this->sanitize_page_key(isset($data['page']) ? $data['page'] : ''),
			'pos_x'				=> $this->clamp(isset($data['x']) ? $data['x'] : 4000, 0, 9800),
			'pos_y'				=> $this->clamp(isset($data['y']) ? $data['y'] : 2500, 0, 9800),
			'note_width'		=> 220,
			'note_height'		=> 200,
			'note_z'			=> $this->get_max_z($user_id) + 1,
			'note_rot'			=> !empty($this->config['stickynotes_rotation']) ? mt_rand(-3, 3) : 0,
			'note_hidden'		=> 0,
			'note_locked'		=> 0,
			'note_snooze'		=> 0,
			'note_minimized'	=> 0,
			'reminder_time'		=> 0,
			'reminder_repeat'	=> 0,
			'reminder_anchor'	=> 0,
			'reminder_notified'	=> 0,
			'reminder_dismissed'=> 0,
			'note_created'		=> $now,
			'note_updated'		=> $now,
		];

		$sql = 'INSERT INTO ' . $this->notes_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		return (int) $this->db->sql_nextid();
	}

	public function get_max_z($user_id)
	{
		$sql = 'SELECT MAX(note_z) AS max_z FROM ' . $this->notes_table . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$max = (int) $this->db->sql_fetchfield('max_z');
		$this->db->sql_freeresult($result);

		return $max;
	}

	/**
	 * Save title and text
	 */
	public function update_content($note_id, $title, $text, $bbcode)
	{
		$title = utf8_substr(trim($title), 0, self::TITLE_MAX);
		list($text, $uid, $bitfield, $flags, $warnings) = $this->parse_text($text, $bbcode);

		// The parser refused the text (too many links, smilies, …): nothing is saved
		if (!empty($warnings))
		{
			return $warnings;
		}

		$sql_ary = [
			'note_title'		=> $title,
			'note_text'			=> $text,
			'bbcode_uid'		=> $uid,
			'bbcode_bitfield'	=> $bitfield,
			'note_flags'		=> $flags,
			'note_format'		=> $bbcode ? 1 : 0,
			'note_updated'		=> time(),
		];

		$this->update_row($note_id, $sql_ary);

		return [];
	}

	/**
	 * Save layout / state properties (only whitelisted keys)
	 */
	public function update_props($note_id, array $props)
	{
		$sql_ary = [];

		foreach ($props as $key => $value)
		{
			switch ($key)
			{
				case 'title':
					$sql_ary['note_title'] = utf8_substr(trim($value), 0, self::TITLE_MAX);
				break;
				case 'color':
					$sql_ary['note_color'] = $this->sanitize_color($value);
				break;
				case 'scope':
					$sql_ary['note_scope'] = $value === 'page' ? 'page' : 'global';
				break;
				case 'page':
					$sql_ary['note_page'] = $this->sanitize_page_key($value);
				break;
				case 'x':
					$sql_ary['pos_x'] = $this->clamp($value, 0, 9800);
				break;
				case 'y':
					$sql_ary['pos_y'] = $this->clamp($value, 0, 9800);
				break;
				case 'w':
					$sql_ary['note_width'] = $this->clamp($value, self::MIN_W, self::MAX_W);
				break;
				case 'h':
					$sql_ary['note_height'] = $this->clamp($value, self::MIN_H, self::MAX_H);
				break;
				case 'z':
					$sql_ary['note_z'] = $this->clamp($value, 1, 999999);
				break;
				case 'hidden':
					$sql_ary['note_hidden'] = $value ? 1 : 0;
				break;
				case 'locked':
					$sql_ary['note_locked'] = $value ? 1 : 0;
				break;
				case 'minimized':
					$sql_ary['note_minimized'] = $value ? 1 : 0;
				break;
				case 'snooze':
					// 0 = wake up now; otherwise a future time, at most one year ahead
					$value = (int) $value;
					$sql_ary['note_snooze'] = $value > time() ? min($value, time() + 366 * 86400) : 0;
				break;
			}
		}

		if (!empty($sql_ary))
		{
			$this->update_row($note_id, $sql_ary);
		}
	}

	protected function update_row($note_id, array $sql_ary)
	{
		$sql = 'UPDATE ' . $this->notes_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE note_id = ' . (int) $note_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Hide or show every note of a user visible on a page
	 */
	public function set_all_hidden($user_id, $page_key, $hidden)
	{
		$sql = 'UPDATE ' . $this->notes_table . '
			SET note_hidden = ' . ($hidden ? 1 : 0) . ($hidden ? '' : ', note_snooze = 0') . '
			WHERE user_id = ' . (int) $user_id . "
				AND (note_scope = 'global' OR note_page = '" . $this->db->sql_escape($page_key) . "')";
		$this->db->sql_query($sql);
	}

	public function duplicate(array $row)
	{
		$copy = $row;
		unset($copy['note_id']);
		$now = time();
		// Next to the original (right side, or left if there is no room), slightly lower,
		// so it never covers the original and its buttons
		$x = (int) $row['pos_x'];
		$copy['pos_x'] = $x + 2000 <= 8200 ? $x + 2000 : max(0, $x - 2000);
		$copy['pos_y'] = min(9000, (int) $row['pos_y'] + 300);
		$copy['note_z'] = $this->get_max_z($row['user_id']) + 1;
		$copy['note_hidden'] = 0;
		$copy['note_snooze'] = 0;
		$copy['reminder_time'] = 0;
		$copy['reminder_repeat'] = 0;
		$copy['reminder_anchor'] = 0;
		$copy['reminder_notified'] = 0;
		$copy['reminder_dismissed'] = 0;
		$copy['note_created'] = $now;
		$copy['note_updated'] = $now;

		$sql = 'INSERT INTO ' . $this->notes_table . ' ' . $this->db->sql_build_array('INSERT', $copy);
		$this->db->sql_query($sql);

		return (int) $this->db->sql_nextid();
	}

	/**
	 * Delete notes, their shares and their notifications
	 */
	public function delete(array $note_ids)
	{
		$note_ids = array_values(array_filter(array_map('intval', $note_ids)));
		if (empty($note_ids))
		{
			return 0;
		}

		$this->db->sql_query('DELETE FROM ' . $this->shares_table . ' WHERE ' . $this->db->sql_in_set('note_id', $note_ids));
		$this->db->sql_query('DELETE FROM ' . $this->notes_table . ' WHERE ' . $this->db->sql_in_set('note_id', $note_ids));
		$deleted = (int) $this->db->sql_affectedrows();

		$this->delete_notifications(self::TYPE_REMINDER, $note_ids);
		$this->delete_notifications(self::TYPE_SHARED, $note_ids);

		return $deleted;
	}

	public function delete_user_notes(array $user_ids)
	{
		$user_ids = array_map('intval', $user_ids);
		if (empty($user_ids))
		{
			return 0;
		}

		$sql = 'SELECT note_id FROM ' . $this->notes_table . ' WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$result = $this->db->sql_query($sql);
		$ids = array_column($this->db->sql_fetchrowset($result), 'note_id');
		$this->db->sql_freeresult($result);

		$deleted = $this->delete($ids);

		$this->db->sql_query('DELETE FROM ' . $this->shares_table . '
			WHERE share_type = ' . self::SHARE_USER . ' AND ' . $this->db->sql_in_set('share_target', $user_ids));

		return $deleted;
	}

	public function delete_group_shares($group_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->shares_table . '
			WHERE share_type = ' . self::SHARE_GROUP . ' AND share_target = ' . (int) $group_id);
	}

	protected function delete_notifications($type, array $ids)
	{
		try
		{
			$this->notification_manager->delete_notifications($type, $ids);
		}
		catch (\Exception $e)
		{
			// Notification type not (yet) registered: nothing to delete
		}
	}

	/* ------------------------------------------------------------------
	 * Sharing
	 * ------------------------------------------------------------------ */

	/**
	 * Replace the share list of a note.
	 *
	 * @param array  $row        Note row
	 * @param string $usernames  Comma/newline separated usernames
	 * @param array  $group_ids  Group ids (only groups the owner belongs to)
	 * @return array ['added' => int[], 'missing' => string[]]
	 */
	public function set_shares(array $row, $usernames, array $group_ids)
	{
		$owner_id = (int) $row['user_id'];
		$names = array_filter(array_map('trim', preg_split('#[,;\n]+#', (string) $usernames)));
		$names = array_slice(array_unique($names), 0, 50);

		$user_ids = [];
		$missing = [];
		if (!empty($names))
		{
			$clean = array_map('utf8_clean_string', $names);
			$sql = 'SELECT user_id, username_clean FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('username_clean', $clean) . '
					AND ' . $this->db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]);
			$result = $this->db->sql_query($sql);
			$found = [];
			while ($user_row = $this->db->sql_fetchrow($result))
			{
				if ((int) $user_row['user_id'] !== $owner_id)
				{
					$user_ids[] = (int) $user_row['user_id'];
				}
				$found[] = $user_row['username_clean'];
			}
			$this->db->sql_freeresult($result);

			foreach ($names as $i => $name)
			{
				if (!in_array($clean[$i], $found, true))
				{
					$missing[] = $name;
				}
			}
		}

		$allowed_groups = $this->get_user_groups($owner_id, false);
		$group_ids = array_values(array_filter(array_map('intval', $group_ids), function ($gid) use ($allowed_groups) {
			return isset($allowed_groups[$gid]);
		}));

		// Who already received the note (to notify only new recipients)
		$old_recipients = $this->get_recipients((int) $row['note_id']);

		$this->db->sql_query('DELETE FROM ' . $this->shares_table . ' WHERE note_id = ' . (int) $row['note_id']);

		$insert = [];
		foreach (array_unique($user_ids) as $uid)
		{
			$insert[] = ['note_id' => (int) $row['note_id'], 'share_type' => self::SHARE_USER, 'share_target' => $uid];
		}
		foreach (array_unique($group_ids) as $gid)
		{
			$insert[] = ['note_id' => (int) $row['note_id'], 'share_type' => self::SHARE_GROUP, 'share_target' => $gid];
		}
		if (!empty($insert))
		{
			$this->db->sql_multi_insert($this->shares_table, $insert);
		}

		$new_recipients = array_values(array_diff($this->get_recipients((int) $row['note_id']), $old_recipients, [$owner_id]));

		if (!empty($new_recipients))
		{
			$this->delete_notifications(self::TYPE_SHARED, [(int) $row['note_id']]);
			try
			{
				$this->notification_manager->add_notifications(self::TYPE_SHARED, [
					'note_id'		=> (int) $row['note_id'],
					'user_id'		=> $owner_id,
					'note_title'	=> $row['note_title'],
					'note_excerpt'	=> $this->excerpt($row),
					'recipients'	=> $new_recipients,
				]);
			}
			catch (\Exception $e)
			{
				// Notifications are best effort
			}
		}

		return ['added' => $new_recipients, 'missing' => $missing];
	}

	/**
	 * All user ids that can see a note through sharing
	 */
	public function get_recipients($note_id)
	{
		$sql = 'SELECT share_type, share_target FROM ' . $this->shares_table . ' WHERE note_id = ' . (int) $note_id;
		$result = $this->db->sql_query($sql);
		$users = $groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ((int) $row['share_type'] === self::SHARE_USER)
			{
				$users[] = (int) $row['share_target'];
			}
			else
			{
				$groups[] = (int) $row['share_target'];
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($groups))
		{
			$sql = 'SELECT user_id FROM ' . USER_GROUP_TABLE . '
				WHERE user_pending = 0 AND ' . $this->db->sql_in_set('group_id', $groups);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$users[] = (int) $row['user_id'];
			}
			$this->db->sql_freeresult($result);
		}

		return array_values(array_unique($users));
	}

	/* ------------------------------------------------------------------
	 * Reminders
	 * ------------------------------------------------------------------ */

	public function set_reminder($note_id, $time, $repeat)
	{
		$time = max(0, (int) $time);
		$repeat = in_array((int) $repeat, [self::REPEAT_NONE, self::REPEAT_DAILY, self::REPEAT_WEEKLY, self::REPEAT_MONTHLY], true) ? (int) $repeat : 0;

		$this->update_row($note_id, [
			'reminder_time'			=> $time,
			'reminder_anchor'		=> $time,
			'reminder_repeat'		=> $time ? $repeat : 0,
			'reminder_notified'		=> 0,
			'reminder_dismissed'	=> 0,
			'note_updated'			=> time(),
		]);

		$this->delete_notifications(self::TYPE_REMINDER, [(int) $note_id]);
	}

	public function next_occurrence($time, $repeat, $anchor = 0)
	{
		$now = time();
		$guard = 0;
		do
		{
			switch ((int) $repeat)
			{
				case self::REPEAT_DAILY:
					$time += 86400;
				break;
				case self::REPEAT_WEEKLY:
					$time += 604800;
				break;
				case self::REPEAT_MONTHLY:
					$time = $this->add_month($time, $anchor ?: $time);
				break;
				default:
					return 0;
			}
		}
		while ($time <= $now && ++$guard < 1000);

		return (int) $time;
	}

	/**
	 * Same day next month, clamped to the last day (31 Jan -> 28/29 Feb)
	 */
	protected function add_month($time, $anchor)
	{
		$y = (int) date('Y', $time);
		$m = (int) date('n', $time) + 1;
		if ($m > 12)
		{
			$m = 1;
			$y++;
		}
		$d = min((int) date('j', $anchor), (int) date('t', mktime(0, 0, 0, $m, 1, $y)));

		return mktime((int) date('G', $time), (int) date('i', $time), (int) date('s', $time), $m, $d, $y);
	}

	protected function advance_reminder(array $row)
	{
		$next = $this->next_occurrence((int) $row['reminder_time'], (int) $row['reminder_repeat'], (int) $row['reminder_anchor']);
		$this->update_row($row['note_id'], [
			'reminder_time'			=> $next,
			'reminder_repeat'		=> $next ? (int) $row['reminder_repeat'] : 0,
			'reminder_notified'		=> 0,
			'reminder_dismissed'	=> 0,
		]);
	}

	/**
	 * Expired reminders to show as phpBB popup to the current user
	 */
	public function get_due_popups($user_id, $notify_method)
	{
		if (!in_array((int) $notify_method, [self::NOTIFY_POPUP, self::NOTIFY_BOTH], true))
		{
			return [];
		}

		$sql = 'SELECT * FROM ' . $this->notes_table . '
			WHERE user_id = ' . (int) $user_id . '
				AND reminder_time > 0
				AND reminder_time <= ' . time() . '
				AND reminder_dismissed = 0
			ORDER BY reminder_time ASC';
		$result = $this->db->sql_query_limit($sql, 10);

		$due = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$due[] = [
				'id'		=> (int) $row['note_id'],
				'title'		=> html_entity_decode($row['note_title'], ENT_QUOTES, 'UTF-8'),
				'html'		=> $this->render_text($row),
				'time'		=> (int) $row['reminder_time'],
				'color'		=> $row['note_color'],
			];
		}
		$this->db->sql_freeresult($result);

		return $due;
	}

	/**
	 * Mark popups as seen; recurring reminders move to their next date
	 */
	public function dismiss_popups($user_id, array $note_ids)
	{
		$note_ids = array_map('intval', $note_ids);
		if (empty($note_ids))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . $this->notes_table . '
			WHERE user_id = ' . (int) $user_id . '
				AND reminder_time > 0
				AND reminder_time <= ' . time() . '
				AND ' . $this->db->sql_in_set('note_id', $note_ids);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($rows as $row)
		{
			if ((int) $row['reminder_repeat'] > 0)
			{
				$this->advance_reminder($row);
			}
			else
			{
				$this->update_row($row['note_id'], ['reminder_dismissed' => 1]);
			}
		}
	}

	/**
	 * Cron: send bell notifications for expired reminders
	 *
	 * @return int processed reminders
	 */
	public function process_due_reminders($limit = 50, $user_id = 0)
	{
		$sql = 'SELECT n.*, u.user_stickynotes_notify
			FROM ' . $this->notes_table . ' n
			INNER JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id
			WHERE n.reminder_time > 0
				AND n.reminder_time <= ' . time() . '
				AND n.reminder_notified = 0' . ($user_id ? '
				AND n.user_id = ' . (int) $user_id : '') . '
			ORDER BY n.reminder_time ASC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($rows as $row)
		{
			$method = (int) $row['user_stickynotes_notify'];

			if ($method === self::NOTIFY_BELL || $method === self::NOTIFY_BOTH)
			{
				$this->delete_notifications(self::TYPE_REMINDER, [(int) $row['note_id']]);
				try
				{
					$this->notification_manager->add_notifications(self::TYPE_REMINDER, [
						'note_id'		=> (int) $row['note_id'],
						'user_id'		=> (int) $row['user_id'],
						'note_title'	=> $row['note_title'],
						'note_excerpt'	=> $this->excerpt($row),
						'reminder_time'	=> (int) $row['reminder_time'],
						'note_page'		=> $row['note_scope'] === 'page' ? $row['note_page'] : '',
					]);
				}
				catch (\Exception $e)
				{
					// Best effort
				}
			}

			if ($method === self::NOTIFY_BELL && (int) $row['reminder_repeat'] > 0)
			{
				// No popup to wait for: move straight to the next occurrence
				$this->advance_reminder($row);
			}
			else
			{
				$this->update_row($row['note_id'], ['reminder_notified' => 1]);
			}
		}

		return count($rows);
	}

	/**
	 * Make sure both notification types exist in phpBB (they are otherwise
	 * created only when the first notification of that type is sent)
	 *
	 * @return array type => true (now registered) | false (error)
	 */
	public function register_notification_types()
	{
		$done = [];
		foreach ([self::TYPE_REMINDER, self::TYPE_SHARED] as $type)
		{
			try
			{
				$this->notification_manager->get_notification_type_id($type);
				$this->notification_manager->enable_notifications($type);
				$done[$type] = true;
			}
			catch (\Exception $e)
			{
				$done[$type] = false;
			}
		}

		return $done;
	}

	/**
	 * Unread board notifications of a user (to update the bell without reloading)
	 */
	public function get_unread_notifications($user_id)
	{
		try
		{
			$data = $this->notification_manager->load_notifications('notification.method.board', [
				'user_id'		=> (int) $user_id,
				'count_unread'	=> true,
				'limit'			=> 1,
			]);
			return isset($data['unread_count']) ? (int) $data['unread_count'] : 0;
		}
		catch (\Exception $e)
		{
			return -1;
		}
	}

	/* ------------------------------------------------------------------
	 * Statistics / maintenance
	 * ------------------------------------------------------------------ */

	public function get_stats()
	{
		$stats = [];
		$queries = [
			'total'		=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table,
			'users'		=> 'SELECT COUNT(DISTINCT user_id) AS cnt FROM ' . $this->notes_table,
			'reminders'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE reminder_time > ' . time(),
			'hidden'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_hidden = 1',
			'bbcode'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_format = 1',
			'snoozed'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_snooze > ' . time(),
			'expired'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE reminder_time > 0 AND reminder_repeat = 0 AND reminder_time <= ' . time(),
			'recurring'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE reminder_time > 0 AND reminder_repeat > 0',
			'empty'		=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . " WHERE note_title = '' AND note_text = ''",
			'inactive'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_updated < ' . (time() - 30 * 86400),
			'new7'		=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_created >= ' . (time() - 7 * 86400),
			'new30'		=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_created >= ' . (time() - 30 * 86400),
			'locked'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_locked = 1',
			'page'		=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . " WHERE note_scope = 'page'",
			'minimized'	=> 'SELECT COUNT(note_id) AS cnt FROM ' . $this->notes_table . ' WHERE note_minimized = 1',
			'shared'	=> 'SELECT COUNT(DISTINCT note_id) AS cnt FROM ' . $this->shares_table,
		];

		foreach ($queries as $key => $sql)
		{
			$result = $this->db->sql_query($sql);
			$stats[$key] = (int) $this->db->sql_fetchfield('cnt');
			$this->db->sql_freeresult($result);
		}

		return $stats;
	}
}

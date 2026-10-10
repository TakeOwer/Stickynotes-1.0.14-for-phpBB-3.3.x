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
 * Maintenance tasks, run in batches (manual tools with progress bar)
 * or automatically by the cron.
 */
class maintenance
{
	const BATCH = 250;

	/** Tasks that delete notes */
	const DELETE_TASKS = ['expired_delete', 'inactive', 'empty', 'user', 'orphans', 'all'];

	/** Tasks that only change notes */
	const UPDATE_TASKS = ['expired_unset', 'reset'];

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var note_manager */
	protected $manager;

	/** @var string */
	protected $notes_table;

	/** @var string */
	protected $shares_table;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\notification\manager $notification_manager,
		note_manager $manager,
		$notes_table,
		$shares_table
	)
	{
		$this->db = $db;
		$this->config = $config;
		$this->notification_manager = $notification_manager;
		$this->manager = $manager;
		$this->notes_table = $notes_table;
		$this->shares_table = $shares_table;
	}

	public function is_task($task)
	{
		return in_array($task, array_merge(self::DELETE_TASKS, self::UPDATE_TASKS), true);
	}

	/**
	 * WHERE clause (on alias n = notes, u = users) selecting the notes of a task
	 *
	 * @param string $task
	 * @param array  $params ['days' => int, 'user_id' => int]
	 * @return string|false
	 */
	public function get_where($task, array $params = [])
	{
		$days = isset($params['days']) ? max(0, (int) $params['days']) : 0;
		$limit_time = time() - $days * 86400;

		switch ($task)
		{
			case 'expired_delete':
			case 'expired_unset':
				// One-off alerts whose date has passed (recurring ones never expire)
				return 'n.reminder_time > 0 AND n.reminder_repeat = 0 AND n.reminder_time < ' . min(time(), $limit_time);

			case 'inactive':
				return 'n.note_updated < ' . (time() - max(1, $days) * 86400);

			case 'empty':
				return "n.note_title = '' AND n.note_text = ''";

			case 'user':
				$user_id = isset($params['user_id']) ? (int) $params['user_id'] : 0;
				return $user_id ? 'n.user_id = ' . $user_id : false;

			case 'orphans':
				return 'u.user_id IS NULL';

			case 'reset':
			case 'all':
				return 'n.note_id > 0';
		}

		return false;
	}

	protected function from()
	{
		return $this->notes_table . ' n LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = n.user_id';
	}

	/**
	 * How many notes a task would touch
	 */
	public function count($task, array $params = [])
	{
		$where = $this->get_where($task, $params);
		if ($where === false)
		{
			return 0;
		}

		$sql = 'SELECT COUNT(n.note_id) AS cnt FROM ' . $this->from() . ' WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count;
	}

	/**
	 * Process one batch of a task
	 *
	 * @return array ['processed' => int, 'affected' => int, 'last_id' => int, 'finished' => bool]
	 */
	public function step($task, array $params, $last_id, $batch = self::BATCH)
	{
		$where = $this->get_where($task, $params);
		if ($where === false)
		{
			return ['processed' => 0, 'affected' => 0, 'last_id' => (int) $last_id, 'finished' => true];
		}

		$sql = 'SELECT n.note_id FROM ' . $this->from() . '
			WHERE ' . $where . '
				AND n.note_id > ' . (int) $last_id . '
			ORDER BY n.note_id ASC';
		$result = $this->db->sql_query_limit($sql, (int) $batch);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['note_id'];
		}
		$this->db->sql_freeresult($result);

		$affected = $this->apply($task, $ids);

		return [
			'processed'	=> count($ids),
			'affected'	=> $affected,
			'last_id'	=> $ids ? end($ids) : (int) $last_id,
			'finished'	=> count($ids) < (int) $batch,
		];
	}

	/**
	 * Apply a task (or a bulk operation) to a list of notes
	 *
	 * @return int notes affected
	 */
	public function apply($task, array $ids)
	{
		$ids = array_values(array_filter(array_map('intval', $ids)));
		if (empty($ids))
		{
			return 0;
		}

		if (in_array($task, self::DELETE_TASKS, true) || $task === 'delete')
		{
			return $this->manager->delete($ids);
		}

		switch ($task)
		{
			case 'expired_unset':
			case 'unset_reminder':
				$this->db->sql_query('UPDATE ' . $this->notes_table . '
					SET reminder_time = 0, reminder_repeat = 0, reminder_anchor = 0, reminder_notified = 0, reminder_dismissed = 0
					WHERE ' . $this->db->sql_in_set('note_id', $ids));
				$this->delete_reminder_notifications($ids);
				return count($ids);

			case 'wake':
				$this->db->sql_query('UPDATE ' . $this->notes_table . '
					SET note_hidden = 0, note_snooze = 0, note_minimized = 0
					WHERE ' . $this->db->sql_in_set('note_id', $ids));
				return count($ids);

			case 'reset':
				// Puts the notes back in sight, in a tidy grid, at their default size
				foreach ($ids as $i => $id)
				{
					$col = $i % 6;
					$row = (int) floor($i / 6) % 4;
					$this->db->sql_query('UPDATE ' . $this->notes_table . '
						SET pos_x = ' . (600 + $col * 1450) . ',
							pos_y = ' . (1400 + $row * 1900) . ',
							note_width = 220,
							note_height = 200,
							note_minimized = 0,
							note_snooze = 0
						WHERE note_id = ' . $id);
				}
				return count($ids);
		}

		return 0;
	}

	protected function delete_reminder_notifications(array $ids)
	{
		try
		{
			$this->notification_manager->delete_notifications(note_manager::TYPE_REMINDER, $ids);
		}
		catch (\Exception $e)
		{
			// Type not registered: nothing to delete
		}
	}

	/**
	 * Run a whole task now (used by the cron), at most $max notes per call
	 *
	 * @return int notes affected
	 */
	public function run($task, array $params = [], $max = 1000)
	{
		$last_id = 0;
		$affected = 0;
		$done = 0;

		do
		{
			$step = $this->step($task, $params, $last_id);
			$last_id = $step['last_id'];
			$affected += $step['affected'];
			$done += $step['processed'];
		}
		while (!$step['finished'] && $done < $max);

		return $affected;
	}

	/**
	 * Automatic cleanup configured in the ACP
	 *
	 * @return array ['expired' => int, 'inactive' => int]
	 */
	public function auto_cleanup()
	{
		$result = ['expired' => 0, 'inactive' => 0];

		$expired_days = (int) $this->config['stickynotes_auto_expired_days'];
		if ($expired_days > 0)
		{
			$result['expired'] = $this->run('expired_delete', ['days' => $expired_days]);
		}

		$inactive_days = (int) $this->config['stickynotes_auto_inactive_days'];
		if ($inactive_days > 0)
		{
			$result['inactive'] = $this->run('inactive', ['days' => $inactive_days]);
		}

		// Shares pointing to deleted users, groups or notes
		$this->cleanup_shares();

		$this->config->set('stickynotes_auto_last', time(), false);
		$this->config->set('stickynotes_auto_deleted', $result['expired'] + $result['inactive'], false);

		return $result;
	}

	/**
	 * Remove shares pointing to deleted notes, users or groups
	 *
	 * @return int shares removed
	 */
	public function cleanup_shares()
	{
		$ids = [];
		$queries = [
			'SELECT s.share_id FROM ' . $this->shares_table . ' s LEFT JOIN ' . $this->notes_table . ' n ON n.note_id = s.note_id WHERE n.note_id IS NULL',
			'SELECT s.share_id FROM ' . $this->shares_table . ' s LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.share_target WHERE s.share_type = ' . note_manager::SHARE_USER . ' AND u.user_id IS NULL',
			'SELECT s.share_id FROM ' . $this->shares_table . ' s LEFT JOIN ' . GROUPS_TABLE . ' g ON g.group_id = s.share_target WHERE s.share_type = ' . note_manager::SHARE_GROUP . ' AND g.group_id IS NULL',
		];

		foreach ($queries as $sql)
		{
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$ids[] = (int) $row['share_id'];
			}
			$this->db->sql_freeresult($result);
		}

		$ids = array_unique($ids);
		if (!empty($ids))
		{
			$this->db->sql_query('DELETE FROM ' . $this->shares_table . ' WHERE ' . $this->db->sql_in_set('share_id', $ids));
		}

		return count($ids);
	}
}

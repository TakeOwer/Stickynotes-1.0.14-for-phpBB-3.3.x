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
 * Integration with the official extension phpbb/webpushnotifications.
 *
 * Sticky Notes sends standard phpBB notifications: when Web Push is enabled for
 * them, phpBB delivers them to the user's devices even with the browser closed.
 */
class webpush
{
	const EXT = 'phpbb/webpushnotifications';
	const METHOD = 'notification.method.phpbb.wpn.webpush';
	const TYPES = [note_manager::TYPE_REMINDER, note_manager::TYPE_SHARED];

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var string */
	protected $table_prefix;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\extension\manager $ext_manager,
		\phpbb\notification\manager $notification_manager,
		$table_prefix
	)
	{
		$this->config = $config;
		$this->db = $db;
		$this->ext_manager = $ext_manager;
		$this->notification_manager = $notification_manager;
		$this->table_prefix = $table_prefix;
	}

	const URL_DOWNLOAD = 'https://www.phpbb.com/customise/db/extension/webpushnotifications/';

	/** State of the push extension, for the Check-up */
	const STATE_MISSING = 'missing';		// not uploaded to ext/
	const STATE_DISABLED = 'disabled';		// uploaded but not enabled
	const STATE_UNCONFIGURED = 'unconfigured';	// enabled, but switched off or without VAPID keys
	const STATE_ACTIVE = 'active';

	public function get_state()
	{
		if ($this->is_available())
		{
			return self::STATE_ACTIVE;
		}
		if ($this->is_installed())
		{
			return self::STATE_UNCONFIGURED;
		}

		return $this->ext_manager->is_available(self::EXT) ? self::STATE_DISABLED : self::STATE_MISSING;
	}

	/** The extension is installed and enabled */
	public function is_installed()
	{
		return $this->ext_manager->is_enabled(self::EXT);
	}

	/** Installed, switched on in its ACP and with VAPID keys */
	public function is_available()
	{
		return $this->is_installed()
			&& !empty($this->config['wpn_webpush_enable'])
			&& !empty($this->config['wpn_webpush_vapid_public'])
			&& !empty($this->config['wpn_webpush_vapid_private']);
	}

	/** Web Push enabled by default for every notification type (its ACP option) */
	public function is_default_on()
	{
		return !empty($this->config['wpn_webpush_method_enabled']);
	}

	/**
	 * Is Web Push active for the Sticky Notes notifications of this user?
	 * (explicit choice in the UCP or the board default)
	 */
	public function is_user_enabled($user_id)
	{
		try
		{
			$subscriptions = $this->notification_manager->get_global_subscriptions((int) $user_id);
		}
		catch (\Exception $e)
		{
			return false;
		}

		foreach (self::TYPES as $type)
		{
			if (empty($subscriptions[$type]) || !in_array(self::METHOD, (array) $subscriptions[$type], true))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Switch Web Push on/off for both Sticky Notes notification types
	 * (same setting as UCP → Board preferences → Edit notification options)
	 */
	public function set_user_enabled($user_id, $enabled)
	{
		foreach (self::TYPES as $type)
		{
			if ($enabled)
			{
				$this->notification_manager->add_subscription($type, 0, self::METHOD, (int) $user_id);
			}
			else
			{
				$this->notification_manager->delete_subscription($type, 0, self::METHOD, (int) $user_id);
			}
		}
	}

	/**
	 * Browsers/devices of the user registered for push notifications
	 */
	public function count_devices($user_id)
	{
		if (!$this->is_installed())
		{
			return 0;
		}

		try
		{
			$sql = 'SELECT COUNT(subscription_id) AS cnt
				FROM ' . $this->table_prefix . 'wpn_push_subscriptions
				WHERE user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$count = (int) $this->db->sql_fetchfield('cnt');
			$this->db->sql_freeresult($result);
		}
		catch (\Exception $e)
		{
			$count = 0;
		}

		return $count;
	}

	/**
	 * Users who explicitly switched Web Push on for the "reminder due" notification
	 */
	public function count_users_enabled()
	{
		$sql = 'SELECT COUNT(DISTINCT user_id) AS cnt
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $this->db->sql_escape(note_manager::TYPE_REMINDER) . "'
				AND item_id = 0
				AND method = '" . $this->db->sql_escape(self::METHOD) . "'
				AND notify = 1";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count;
	}
}

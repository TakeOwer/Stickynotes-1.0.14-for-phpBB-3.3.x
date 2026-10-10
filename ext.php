<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes;

class ext extends \phpbb\extension\base
{
	/** Notification types provided by this extension */
	const NOTIFICATION_TYPES = [
		'salvocortesiano.stickynotes.notification.type.reminder',
		'salvocortesiano.stickynotes.notification.type.shared',
	];

	/**
	 * Requires phpBB 3.3.0+ and PHP 7.4+
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		return phpbb_version_compare($config['version'], '3.3.0', '>=')
			&& phpbb_version_compare($config['version'], '4.0.0-dev', '<')
			&& version_compare(PHP_VERSION, '7.4.0', '>=');
	}

	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$manager = $this->container->get('notification_manager');
			foreach (self::NOTIFICATION_TYPES as $type)
			{
				try
				{
					// Creates the type if missing, so it is ready before the first notification
					$manager->get_notification_type_id($type);
				}
				catch (\Exception $e)
				{
					// Services not loaded yet: the Check-up registers it later
				}
				$manager->enable_notifications($type);
			}
			return 'notifications';
		}

		return parent::enable_step($old_state === 'notifications' ? false : $old_state);
	}

	public function disable_step($old_state)
	{
		if ($old_state === false)
		{
			$manager = $this->container->get('notification_manager');
			foreach (self::NOTIFICATION_TYPES as $type)
			{
				$manager->disable_notifications($type);
			}
			return 'notifications';
		}

		return parent::disable_step($old_state === 'notifications' ? false : $old_state);
	}

	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			$manager = $this->container->get('notification_manager');
			foreach (self::NOTIFICATION_TYPES as $type)
			{
				try
				{
					$manager->purge_notifications($type);
				}
				catch (\phpbb\notification\exception $e)
				{
					// Type already removed: nothing to purge
				}
			}
			return 'notifications';
		}

		return parent::purge_step($old_state === 'notifications' ? false : $old_state);
	}
}

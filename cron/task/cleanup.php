<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\cron\task;

/**
 * Daily automatic cleanup (expired alerts, inactive notes) configured in the ACP
 */
class cleanup extends \phpbb\cron\task\base
{
	const INTERVAL = 86400;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \salvocortesiano\stickynotes\service\maintenance */
	protected $maintenance;

	public function __construct(\phpbb\config\config $config, \phpbb\log\log_interface $log, \salvocortesiano\stickynotes\service\maintenance $maintenance)
	{
		$this->config = $config;
		$this->log = $log;
		$this->maintenance = $maintenance;
	}

	public function run()
	{
		$result = $this->maintenance->auto_cleanup();

		if ($result['expired'] + $result['inactive'] > 0)
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_STICKYNOTES_AUTO_CLEANUP', false, [$result['expired'], $result['inactive']]);
		}
	}

	public function is_runnable()
	{
		return (int) $this->config['stickynotes_auto_expired_days'] > 0 || (int) $this->config['stickynotes_auto_inactive_days'] > 0;
	}

	public function should_run()
	{
		return (int) $this->config['stickynotes_auto_last'] < time() - self::INTERVAL;
	}
}

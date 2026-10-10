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
 * Sends the bell notifications of expired reminders
 */
class reminders extends \phpbb\cron\task\base
{
	/** Run at most once a minute */
	const INTERVAL = 60;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \salvocortesiano\stickynotes\service\note_manager */
	protected $manager;

	public function __construct(\phpbb\config\config $config, \salvocortesiano\stickynotes\service\note_manager $manager)
	{
		$this->config = $config;
		$this->manager = $manager;
	}

	public function run()
	{
		$this->config->set('stickynotes_cron_last', time(), false);
		$this->manager->process_due_reminders(100);
	}

	public function is_runnable()
	{
		return !empty($this->config['stickynotes_enabled']) && !empty($this->config['stickynotes_allow_reminder']);
	}

	public function should_run()
	{
		return (int) $this->config['stickynotes_cron_last'] < time() - self::INTERVAL;
	}
}

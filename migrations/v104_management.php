<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\migrations;

/**
 * 1.0.4: scheda Manutenzione e pulizia automatica
 */
class v104_management extends \phpbb\db\migration\container_aware_migration
{
	public function effectively_installed()
	{
		return isset($this->config['stickynotes_auto_expired_days']);
	}

	public static function depends_on()
	{
		return ['\salvocortesiano\stickynotes\migrations\v103_fix'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'load_module_language']]],

			['config.add', ['stickynotes_auto_expired_days', 0]],
			['config.add', ['stickynotes_auto_inactive_days', 0]],
			['config.add', ['stickynotes_auto_last', 0, true]],
			['config.add', ['stickynotes_auto_deleted', 0, true]],
			['config.update', ['stickynotes_version', '1.0.4']],

			['module.add', ['acp', 'ACP_STICKYNOTES_TITLE', [
				'module_basename'	=> '\salvocortesiano\stickynotes\acp\main_module',
				'modes'				=> ['maintenance'],
			]]],
		];
	}

	/**
	 * Load the module names before phpBB creates/removes the modules,
	 * so the admin log stores "Module added » Manutenzione" instead of the raw key
	 */
	public function load_module_language()
	{
		try
		{
			$this->container->get('language')->add_lang(['info_acp_stickynotes', 'info_ucp_stickynotes'], 'salvocortesiano/stickynotes');
		}
		catch (\Exception $e)
		{
			// Language not available: the log listener translates the entry when shown
		}
	}

	public function revert_data()
	{
		return [
			['custom', [[$this, 'load_module_language']]],
		];
	}
}

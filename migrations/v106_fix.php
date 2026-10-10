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
 * 1.0.6: pulsanti ACP funzionanti (JS caricato dopo jQuery), nessuna modifica al database
 */
class v106_fix extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['stickynotes_version']) && phpbb_version_compare($this->config['stickynotes_version'], '1.0.6', '>=');
	}

	public static function depends_on()
	{
		return ['\salvocortesiano\stickynotes\migrations\v105_fix'];
	}

	public function update_data()
	{
		return [
			['config.update', ['stickynotes_version', '1.0.6']],
		];
	}
}

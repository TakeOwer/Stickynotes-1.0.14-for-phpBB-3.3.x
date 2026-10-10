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
 * 1.0.8: avvisi alla scadenza immediati (pagina aperta e caricamento pagina), nessuna modifica al database
 */
class v108_fix extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['stickynotes_version']) && phpbb_version_compare($this->config['stickynotes_version'], '1.0.8', '>=');
	}

	public static function depends_on()
	{
		return ['\salvocortesiano\stickynotes\migrations\v107_fix'];
	}

	public function update_data()
	{
		return [
			['config.update', ['stickynotes_version', '1.0.8']],
		];
	}
}

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
 * 1.0.13: cestino a scomparsa durante il trascinamento (impostazioni ACP)
 */
class v1013_trash extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['stickynotes_trash']);
	}

	public static function depends_on()
	{
		return ['\salvocortesiano\stickynotes\migrations\v1012_fix'];
	}

	public function update_data()
	{
		return [
			['config.add', ['stickynotes_trash', 1]],
			['config.add', ['stickynotes_trash_pos', 'above']],
			['config.update', ['stickynotes_version', '1.0.13']],
		];
	}
}

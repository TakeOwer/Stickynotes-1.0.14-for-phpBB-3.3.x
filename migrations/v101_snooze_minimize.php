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
 * 1.0.1: posticipa (snooze) e riduci a barretta (minimize) per singolo promemoria
 */
class v101_snooze_minimize extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'stickynotes', 'note_snooze');
	}

	public static function depends_on()
	{
		return ['\salvocortesiano\stickynotes\migrations\v100_install'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'stickynotes' => [
					'note_snooze'		=> ['TIMESTAMP', 0],
					'note_minimized'	=> ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'stickynotes' => [
					'note_snooze',
					'note_minimized',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['stickynotes_version', '1.0.1']],
		];
	}
}

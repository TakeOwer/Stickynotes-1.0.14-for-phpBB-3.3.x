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

class v100_install extends \phpbb\db\migration\container_aware_migration
{
	public function effectively_installed()
	{
		return isset($this->config['stickynotes_version']);
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'stickynotes' => [
					'COLUMNS' => [
						'note_id'				=> ['UINT', null, 'auto_increment'],
						'user_id'				=> ['ULINT', 0],
						'note_title'			=> ['VCHAR_UNI:255', ''],
						'note_text'				=> ['MTEXT_UNI', ''],
						'bbcode_uid'			=> ['VCHAR:8', ''],
						'bbcode_bitfield'		=> ['VCHAR:255', ''],
						'note_flags'			=> ['UINT', 0],
						'note_format'			=> ['TINT:1', 0],
						'note_color'			=> ['VCHAR:20', 'yellow'],
						'note_scope'			=> ['VCHAR:10', 'global'],
						'note_page'				=> ['VCHAR:255', ''],
						'pos_x'					=> ['UINT', 0],
						'pos_y'					=> ['UINT', 0],
						'note_width'			=> ['USINT', 220],
						'note_height'			=> ['USINT', 200],
						'note_z'				=> ['UINT', 1],
						'note_rot'				=> ['TINT:4', 0],
						'note_hidden'			=> ['BOOL', 0],
						'note_locked'			=> ['BOOL', 0],
						'reminder_time'			=> ['TIMESTAMP', 0],
						'reminder_repeat'		=> ['TINT:1', 0],
						'reminder_anchor'		=> ['TIMESTAMP', 0],
						'reminder_notified'		=> ['BOOL', 0],
						'reminder_dismissed'	=> ['BOOL', 0],
						'note_created'			=> ['TIMESTAMP', 0],
						'note_updated'			=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'note_id',
					'KEYS'			=> [
						'user_id'		=> ['INDEX', 'user_id'],
						'reminder'		=> ['INDEX', 'reminder_time'],
					],
				],
				$this->table_prefix . 'stickynotes_shares' => [
					'COLUMNS' => [
						'share_id'		=> ['UINT', null, 'auto_increment'],
						'note_id'		=> ['UINT', 0],
						'share_type'	=> ['TINT:1', 0],
						'share_target'	=> ['ULINT', 0],
					],
					'PRIMARY_KEY'	=> 'share_id',
					'KEYS'			=> [
						'note_id'	=> ['INDEX', 'note_id'],
						'target'	=> ['INDEX', ['share_type', 'share_target']],
					],
				],
			],
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_stickynotes_enabled'	=> ['BOOL', 1],
					'user_stickynotes_tray'		=> ['VCHAR:2', ''],
					'user_stickynotes_notify'	=> ['TINT:1', 2],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'stickynotes',
				$this->table_prefix . 'stickynotes_shares',
			],
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_stickynotes_enabled',
					'user_stickynotes_tray',
					'user_stickynotes_notify',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'load_module_language']]],

			// Configuration
			['config.add', ['stickynotes_version', '1.0.0']],
			['config.add', ['stickynotes_enabled', 1]],
			['config.add', ['stickynotes_max_notes', 30]],
			['config.add', ['stickynotes_max_chars', 2000]],
			['config.add', ['stickynotes_tray_pos', 'bl']],
			['config.add', ['stickynotes_tray_user', 1]],
			['config.add', ['stickynotes_allow_bbcode', 1]],
			['config.add', ['stickynotes_allow_share', 1]],
			['config.add', ['stickynotes_allow_reminder', 1]],
			['config.add', ['stickynotes_mobile_mode', 0]],
			['config.add', ['stickynotes_default_color', 'yellow']],
			['config.add', ['stickynotes_rotation', 1]],
			['config.add', ['stickynotes_cron_last', 0, true]],

			// Permissions
			['permission.add', ['u_stickynotes_use']],
			['permission.add', ['u_stickynotes_bbcode']],
			['permission.add', ['u_stickynotes_share']],
			['permission.add', ['u_stickynotes_reminder']],
			['permission.add', ['u_stickynotes_nolimit']],
			['permission.add', ['a_stickynotes']],

			['if', [
				['permission.role_exists', ['ROLE_USER_FULL']],
				['permission.permission_set', ['ROLE_USER_FULL', ['u_stickynotes_use', 'u_stickynotes_bbcode', 'u_stickynotes_share', 'u_stickynotes_reminder']]],
			]],
			['if', [
				['permission.role_exists', ['ROLE_USER_STANDARD']],
				['permission.permission_set', ['ROLE_USER_STANDARD', ['u_stickynotes_use', 'u_stickynotes_bbcode', 'u_stickynotes_share', 'u_stickynotes_reminder']]],
			]],
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_FULL']],
				['permission.permission_set', ['ROLE_ADMIN_FULL', ['a_stickynotes', 'u_stickynotes_nolimit']]],
			]],
			['if', [
				['permission.role_exists', ['ROLE_ADMIN_STANDARD']],
				['permission.permission_set', ['ROLE_ADMIN_STANDARD', 'a_stickynotes']],
			]],

			// ACP modules
			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_STICKYNOTES_TITLE']],
			['module.add', ['acp', 'ACP_STICKYNOTES_TITLE', [
				'module_basename'	=> '\salvocortesiano\stickynotes\acp\main_module',
				'modes'				=> ['settings', 'management', 'checkup'],
			]]],

			// UCP modules
			['module.add', ['ucp', 0, 'UCP_STICKYNOTES_TITLE']],
			['module.add', ['ucp', 'UCP_STICKYNOTES_TITLE', [
				'module_basename'	=> '\salvocortesiano\stickynotes\ucp\main_module',
				'modes'				=> ['notes', 'settings'],
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

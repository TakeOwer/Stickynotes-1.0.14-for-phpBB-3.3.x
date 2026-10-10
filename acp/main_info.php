<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\salvocortesiano\stickynotes\acp\main_module',
			'title'		=> 'ACP_STICKYNOTES_TITLE',
			'modes'		=> [
				'settings'		=> ['title' => 'ACP_STICKYNOTES_SETTINGS', 'auth' => 'ext_salvocortesiano/stickynotes && acl_a_stickynotes', 'cat' => ['ACP_STICKYNOTES_TITLE']],
				'management'	=> ['title' => 'ACP_STICKYNOTES_MANAGEMENT', 'auth' => 'ext_salvocortesiano/stickynotes && acl_a_stickynotes', 'cat' => ['ACP_STICKYNOTES_TITLE']],
				'maintenance'	=> ['title' => 'ACP_STICKYNOTES_MAINTENANCE', 'auth' => 'ext_salvocortesiano/stickynotes && acl_a_stickynotes', 'cat' => ['ACP_STICKYNOTES_TITLE']],
				'checkup'		=> ['title' => 'ACP_STICKYNOTES_CHECKUP', 'auth' => 'ext_salvocortesiano/stickynotes && acl_a_stickynotes', 'cat' => ['ACP_STICKYNOTES_TITLE']],
			],
		];
	}
}

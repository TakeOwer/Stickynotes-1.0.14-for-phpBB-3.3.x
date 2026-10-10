<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\ucp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\salvocortesiano\stickynotes\ucp\main_module',
			'title'		=> 'UCP_STICKYNOTES_TITLE',
			'modes'		=> [
				'notes'		=> ['title' => 'UCP_STICKYNOTES_NOTES', 'auth' => 'ext_salvocortesiano/stickynotes && acl_u_stickynotes_use', 'cat' => ['UCP_STICKYNOTES_TITLE']],
				'settings'	=> ['title' => 'UCP_STICKYNOTES_SETTINGS', 'auth' => 'ext_salvocortesiano/stickynotes && acl_u_stickynotes_use', 'cat' => ['UCP_STICKYNOTES_TITLE']],
			],
		];
	}
}

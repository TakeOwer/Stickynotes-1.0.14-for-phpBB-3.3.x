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

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \salvocortesiano\stickynotes\controller\ucp_controller $controller */
		$controller = $phpbb_container->get('salvocortesiano.stickynotes.controller.ucp');
		$language = $phpbb_container->get('language');
		$language->add_lang('common', 'salvocortesiano/stickynotes');

		$controller->set_page_url($this->u_action);

		if ($mode === 'settings')
		{
			$this->tpl_name = '@salvocortesiano_stickynotes/ucp_stickynotes_settings';
			$this->page_title = $language->lang('UCP_STICKYNOTES_SETTINGS');
			$controller->display_settings();
		}
		else
		{
			$this->tpl_name = '@salvocortesiano_stickynotes/ucp_stickynotes_notes';
			$this->page_title = $language->lang('UCP_STICKYNOTES_NOTES');
			$controller->display_notes();
		}
	}
}

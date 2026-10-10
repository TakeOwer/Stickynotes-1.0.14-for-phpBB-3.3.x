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

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \salvocortesiano\stickynotes\controller\acp_controller $controller */
		$controller = $phpbb_container->get('salvocortesiano.stickynotes.controller.acp');
		$language = $phpbb_container->get('language');

		$controller->set_page_url($this->u_action);

		/** @var \salvocortesiano\stickynotes\controller\acp_manage_controller $manage */
		$manage = $phpbb_container->get('salvocortesiano.stickynotes.controller.acp_manage');
		$manage->set_page_url($this->u_action);

		switch ($mode)
		{
			case 'management':
				$this->tpl_name = 'acp_stickynotes_management';
				$this->page_title = $language->lang('ACP_STICKYNOTES_MANAGEMENT');
				$controller->assign_common();
				$manage->display_management();
			break;

			case 'maintenance':
				$this->tpl_name = 'acp_stickynotes_maintenance';
				$this->page_title = $language->lang('ACP_STICKYNOTES_MAINTENANCE');
				$controller->assign_common();
				$manage->display_maintenance();
			break;

			case 'checkup':
				$this->tpl_name = 'acp_stickynotes_checkup';
				$this->page_title = $language->lang('ACP_STICKYNOTES_CHECKUP');
				$controller->display_checkup();
			break;

			case 'settings':
			default:
				$this->tpl_name = 'acp_stickynotes_settings';
				$this->page_title = $language->lang('ACP_STICKYNOTES_SETTINGS');
				$controller->display_settings();
			break;
		}
	}
}

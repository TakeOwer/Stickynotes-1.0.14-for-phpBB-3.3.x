<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\stickynotes\notification\type;

/**
 * Bell notification: another user shared a note with you
 */
class shared extends \phpbb\notification\type\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	public static $notification_option = [
		'lang'	=> 'NOTIFICATION_TYPE_STICKYNOTES_SHARED',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function get_type()
	{
		return 'salvocortesiano.stickynotes.notification.type.shared';
	}

	public function is_available()
	{
		return (bool) $this->auth->acl_get('u_stickynotes_use');
	}

	public static function get_item_id($type_data)
	{
		return (int) $type_data['note_id'];
	}

	public static function get_item_parent_id($type_data)
	{
		return (int) $type_data['user_id'];
	}

	public function find_users_for_notification($type_data, $options = [])
	{
		$options = array_merge(['ignore_users' => []], $options);
		$recipients = array_map('intval', isset($type_data['recipients']) ? (array) $type_data['recipients'] : []);
		$recipients = array_diff($recipients, [(int) $type_data['user_id']]);

		if (empty($recipients))
		{
			return [];
		}

		return $this->check_user_notification_options(array_values($recipients), $options);
	}

	public function users_to_query()
	{
		return [(int) $this->get_data('owner_id')];
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar((int) $this->get_data('owner_id'), false, true);
	}

	public function get_title()
	{
		$title = $this->get_data('note_title');
		if ($title === '' || $title === null)
		{
			$title = $this->language->lang('STICKYNOTES_UNTITLED');
		}

		$username = $this->user_loader->get_username((int) $this->get_data('owner_id'), 'no_profile');

		return $this->language->lang('STICKYNOTES_NOTIFICATION_SHARED', $username, $title);
	}

	public function get_reference()
	{
		$excerpt = (string) $this->get_data('note_excerpt');

		return $excerpt !== '' ? $this->language->lang('NOTIFICATION_REFERENCE', htmlspecialchars($excerpt, ENT_COMPAT, 'UTF-8')) : '';
	}

	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'sn_open=' . (int) $this->item_id);
	}

	public function get_email_template()
	{
		return false;
	}

	public function get_email_template_variables()
	{
		return [];
	}

	public function create_insert_array($type_data, $pre_create_data = [])
	{
		$this->set_data('note_title', isset($type_data['note_title']) ? $type_data['note_title'] : '');
		$this->set_data('note_excerpt', isset($type_data['note_excerpt']) ? (string) $type_data['note_excerpt'] : '');
		$this->set_data('owner_id', (int) $type_data['user_id']);

		parent::create_insert_array($type_data, $pre_create_data);
	}
}

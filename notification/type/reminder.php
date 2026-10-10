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
 * Bell notification: a reminder has reached its date
 */
class reminder extends \phpbb\notification\type\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	public static $notification_option = [
		'lang'	=> 'NOTIFICATION_TYPE_STICKYNOTES_REMINDER',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function get_type()
	{
		return 'salvocortesiano.stickynotes.notification.type.reminder';
	}

	public function is_available()
	{
		return (bool) $this->auth->acl_get('u_stickynotes_reminder');
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

		return $this->check_user_notification_options([(int) $type_data['user_id']], $options);
	}

	public function users_to_query()
	{
		return [];
	}

	public function get_title()
	{
		$title = $this->get_data('note_title');
		if ($title === '' || $title === null)
		{
			$title = $this->language->lang('STICKYNOTES_UNTITLED');
		}

		return $this->language->lang('STICKYNOTES_NOTIFICATION_REMINDER', $title);
	}

	/**
	 * Line under the title (bell dropdown, browser push): the text of the note,
	 * or the due date when the note has no text
	 */
	public function get_reference()
	{
		$excerpt = (string) $this->get_data('note_excerpt');
		if ($excerpt !== '')
		{
			return $this->language->lang('NOTIFICATION_REFERENCE', htmlspecialchars($excerpt, ENT_COMPAT, 'UTF-8'));
		}

		$time = (int) $this->get_data('reminder_time');

		return $time ? $this->language->lang('STICKYNOTES_NOTIFICATION_REMINDER_AT', $this->user->format_date($time)) : '';
	}

	public function get_url()
	{
		// Notes valid only on one page open on that page
		$page = (string) $this->get_data('note_page');
		if ($page !== '' && strpos($page, '..') === false && $page[0] !== '/' && preg_match('#^[A-Za-z0-9_./\-]+(\?[ft]=\d+)?$#', $page))
		{
			list($script, $query) = array_pad(explode('?', $page, 2), 2, '');
			return append_sid($this->phpbb_root_path . $script, ($query !== '' ? $query . '&amp;' : '') . 'sn_open=' . (int) $this->item_id);
		}

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
		$this->set_data('reminder_time', isset($type_data['reminder_time']) ? (int) $type_data['reminder_time'] : 0);
		$this->set_data('note_page', isset($type_data['note_page']) ? (string) $type_data['note_page'] : '');

		parent::create_insert_array($type_data, $pre_create_data);
	}
}

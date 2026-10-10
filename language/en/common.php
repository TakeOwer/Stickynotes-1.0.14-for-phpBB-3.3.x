<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 * [English]
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	// Tray and notes
	'STICKYNOTES_TITLE'					=> 'Sticky notes',
	'STICKYNOTES_TRAY_OPEN'				=> 'Open the sticky notes bar',
	'STICKYNOTES_TRAY_CLOSE'			=> 'Close the sticky notes bar',
	'STICKYNOTES_NEW'					=> 'New',
	'STICKYNOTES_SHOW_ALL'				=> 'Show all on the board',
	'STICKYNOTES_HIDE_ALL'				=> 'Hide all in the bar',
	'STICKYNOTES_SEARCH'				=> 'Search notes…',
	'STICKYNOTES_TAB_MINE'				=> 'Mine',
	'STICKYNOTES_TAB_SHARED'			=> 'Shared with me',
	'STICKYNOTES_EMPTY'					=> 'No notes yet. Press “New” to create one.',
	'STICKYNOTES_EMPTY_SHARED'			=> 'Nobody has shared a note with you.',
	'STICKYNOTES_MANAGE'				=> 'Manage all',
	'STICKYNOTES_UNTITLED'				=> 'Untitled',
	'STICKYNOTES_EDIT'					=> 'Edit',
	'STICKYNOTES_SAVE'					=> 'Save',
	'STICKYNOTES_CANCEL'				=> 'Cancel',
	'STICKYNOTES_DELETE'				=> 'Delete',
	'STICKYNOTES_DELETE_CONFIRM'		=> 'Do you really want to delete this note? This cannot be undone.',
	'STICKYNOTES_HIDE'					=> 'Hide in the bar',
	'STICKYNOTES_SHOW'					=> 'Show on the board',
	'STICKYNOTES_MENU'					=> 'More options',
	'STICKYNOTES_CLOSE'					=> 'Close',
	'STICKYNOTES_FOCUS'					=> 'Show this note',
	'STICKYNOTES_DRAG'					=> 'Drag to move · double click the title to rename',
	'STICKYNOTES_RESIZE'				=> 'Drag to resize',
	'STICKYNOTES_SHORTCUT'				=> 'Alt+Shift+N',
	'STICKYNOTES_COUNT_NOTES'			=> '%1$d of %2$d notes',
	'STICKYNOTES_READONLY'				=> 'Read only',
	'STICKYNOTES_SAVED'					=> 'Saved',

	// Colours
	'STICKYNOTES_COLOR'					=> 'Colour',
	'STICKYNOTES_COLOR_YELLOW'			=> 'Yellow',
	'STICKYNOTES_COLOR_PINK'			=> 'Pink',
	'STICKYNOTES_COLOR_BLUE'			=> 'Blue',
	'STICKYNOTES_COLOR_GREEN'			=> 'Green',
	'STICKYNOTES_COLOR_ORANGE'			=> 'Orange',
	'STICKYNOTES_COLOR_PURPLE'			=> 'Lilac',
	'STICKYNOTES_COLOR_WHITE'			=> 'White',

	// Scope
	'STICKYNOTES_SCOPE'					=> 'Where it appears',
	'STICKYNOTES_SCOPE_GLOBAL'			=> 'On every page',
	'STICKYNOTES_SCOPE_PAGE'			=> 'Only on this page',
	'STICKYNOTES_SCOPE_GLOBAL_EXPLAIN'	=> 'The note will be visible on every page of the board.',
	'STICKYNOTES_SCOPE_PAGE_EXPLAIN'	=> 'The note will be visible only on this page (topic, forum or page).',

	// Reminders
	'STICKYNOTES_REMINDER'				=> 'Due-date alert',
	'STICKYNOTES_REMINDER_SET'			=> 'Set alert',
	'STICKYNOTES_REMINDER_REMOVE'		=> 'Remove alert',
	'STICKYNOTES_REMINDER_DATE'			=> 'Date and time',
	'STICKYNOTES_REMINDER_REPEAT'		=> 'Repeat',
	'STICKYNOTES_REPEAT_NONE'			=> 'Never',
	'STICKYNOTES_REPEAT_DAILY'			=> 'Every day',
	'STICKYNOTES_REPEAT_WEEKLY'			=> 'Every week',
	'STICKYNOTES_REPEAT_MONTHLY'		=> 'Every month',
	'STICKYNOTES_REMINDER_AT'			=> 'Alert: %s',
	'STICKYNOTES_REMINDER_POPUP_TITLE'	=> 'Notes due',
	'STICKYNOTES_REMINDER_EXPIRED'		=> 'expired',

	// Sharing
	'STICKYNOTES_SHARE'					=> 'Share',
	'STICKYNOTES_SHARE_USERS'			=> 'Users',
	'STICKYNOTES_SHARE_USERS_EXPLAIN'	=> 'Usernames separated by commas',
	'STICKYNOTES_SHARE_GROUPS'			=> 'Groups you belong to',
	'STICKYNOTES_SHARE_SAVE'			=> 'Save sharing',
	'STICKYNOTES_SHARE_NONE'			=> 'Not shared',
	'STICKYNOTES_SHARE_MISSING'			=> 'These users were not found: %s',
	'STICKYNOTES_SHARED_WITH'			=> 'Shared with',
	'STICKYNOTES_SHARED_BY'				=> 'Shared by %s',

	// Other actions
	'STICKYNOTES_DUPLICATE'				=> 'Duplicate',
	'STICKYNOTES_LOCK'					=> 'Lock position',
	'STICKYNOTES_UNLOCK'				=> 'Unlock position',
	'STICKYNOTES_LOCKED'				=> 'Position locked',

	// Editor
	'STICKYNOTES_FORMAT'				=> 'Text format',
	'STICKYNOTES_FORMAT_PLAIN'			=> 'Plain text',
	'STICKYNOTES_FORMAT_BBCODE'			=> 'BBCode',
	'STICKYNOTES_BB_BOLD'				=> 'Bold',
	'STICKYNOTES_BB_ITALIC'				=> 'Italic',
	'STICKYNOTES_BB_UNDERLINE'			=> 'Underline',
	'STICKYNOTES_BB_STRIKE'				=> 'Strikethrough',
	'STICKYNOTES_BB_LIST'				=> 'Bulleted list',
	'STICKYNOTES_BB_URL'				=> 'Link',
	'STICKYNOTES_BB_COLOR'				=> 'Text colour',
	'STICKYNOTES_BB_CODE'				=> 'Code',
	'STICKYNOTES_BB_QUOTE'				=> 'Quote',
	'STICKYNOTES_URL_PROMPT'			=> 'Link address',
	'STICKYNOTES_TITLE_PLACEHOLDER'		=> 'Note title',
	'STICKYNOTES_TEXT_PLACEHOLDER'		=> 'Write your note here…',
	'STICKYNOTES_CHARS_LEFT'			=> 'Characters left: %d',

	// Errors
	'STICKYNOTES_ERROR'					=> 'Sticky notes',
	'STICKYNOTES_ERR_GENERIC'			=> 'Something went wrong. Please try again in a moment.',
	'STICKYNOTES_ERR_DISABLED'			=> 'Sticky notes have been disabled by the administrator.',
	'STICKYNOTES_ERR_NO_AUTH'			=> 'You are not allowed to do this.',
	'STICKYNOTES_ERR_TOKEN'				=> 'Your session has expired. Reload the page and try again.',
	'STICKYNOTES_ERR_NOT_FOUND'			=> 'This note no longer exists.',
	'STICKYNOTES_ERR_TOO_LONG'			=> 'The text is too long.',
	'STICKYNOTES_ERR_LIMIT'				=> 'You reached the maximum number of notes (%d). Delete some to create new ones.',
	'STICKYNOTES_ERR_LIMIT_JS'			=> 'You reached the maximum number of notes (%d). Delete some to create new ones.',
	'STICKYNOTES_ERR_PAST_DATE'			=> 'The alert date is already in the past.',
	'STICKYNOTES_ERR_ACTION'			=> 'Invalid action.',

	// Tray positions
	'STICKYNOTES_TRAY_TL'				=> 'Top left',
	'STICKYNOTES_TRAY_TR'				=> 'Top right',
	'STICKYNOTES_TRAY_BL'				=> 'Bottom left',
	'STICKYNOTES_TRAY_BR'				=> 'Bottom right',
	'STICKYNOTES_TRAY_DEFAULT'			=> 'Board default (%s)',

	// Notifications (bell)
	'NOTIFICATION_TYPE_STICKYNOTES_REMINDER'	=> 'One of my sticky notes reached its due date',
	'NOTIFICATION_TYPE_STICKYNOTES_SHARED'		=> 'Someone shares a sticky note with me',
	'STICKYNOTES_NOTIFICATION_REMINDER'			=> '<strong>Note due:</strong> %s',
	'STICKYNOTES_NOTIFICATION_REMINDER_AT'		=> 'Alert set for %s',
	'STICKYNOTES_NOTIFICATION_SHARED'			=> '%1$s shared the note <strong>%2$s</strong> with you',

	// UCP
	'STICKYNOTES_UCP_NOTES_EXPLAIN'		=> 'Here you find all your notes, including those visible only on a specific page. Click a title to open it on its page.',
	'STICKYNOTES_UCP_INACTIVE'			=> 'Notes are currently not shown on the board (disabled in your preferences or by the administrator).',
	'STICKYNOTES_UCP_TOTAL'				=> 'Saved notes',
	'STICKYNOTES_UCP_NOTE'				=> 'Note',
	'STICKYNOTES_UCP_UPDATED'			=> 'Last change',
	'STICKYNOTES_UCP_IN_TRAY'			=> 'hidden in the bar',
	'STICKYNOTES_UCP_DELETED'			=> [
		1	=> '%d note deleted.',
		2	=> '%d notes deleted.',
	],
	'STICKYNOTES_UCP_DELETE_CONFIRM'	=> [
		1	=> 'Do you really want to delete the selected note?',
		2	=> 'Do you really want to delete the %d selected notes?',
	],
	'STICKYNOTES_UCP_SAVED'				=> 'Your sticky notes preferences have been saved.',
	'STICKYNOTES_UCP_SETTINGS_EXPLAIN'	=> 'Choose how and where to see your notes.',
	'STICKYNOTES_UCP_ENABLE'			=> 'Show notes on the board',
	'STICKYNOTES_UCP_ENABLE_EXPLAIN'	=> 'When disabled the bar and the notes are not shown. Saved notes are not deleted.',
	'STICKYNOTES_UCP_TRAY'				=> 'Bar position',
	'STICKYNOTES_UCP_TRAY_EXPLAIN'		=> 'The corner of the screen where the pop-up bar button appears.',
	'STICKYNOTES_UCP_NOTIFY'			=> 'How to alert me',
	'STICKYNOTES_UCP_NOTIFY_EXPLAIN'	=> 'Choose how to be alerted when a note reaches its date. The pop-up appears on the open page; the bell also feeds the browser push notifications.',
	'STICKYNOTES_UCP_NOTIFY_POPUP'		=> 'Board pop-up window',
	'STICKYNOTES_UCP_NOTIFY_BELL'		=> 'Notification in the bell',
	'STICKYNOTES_UCP_NOTIFY_BOTH'		=> 'Both',

	// Snooze and minimize (1.0.1)
	'STICKYNOTES_SNOOZE'				=> 'Snooze',
	'STICKYNOTES_SNOOZE_30M'			=> 'For 30 minutes',
	'STICKYNOTES_SNOOZE_1H'				=> 'For 1 hour',
	'STICKYNOTES_SNOOZE_4H'				=> 'For 4 hours',
	'STICKYNOTES_SNOOZE_TOMORROW'		=> 'Until tomorrow morning',
	'STICKYNOTES_SNOOZE_WEEK'			=> 'For one week',
	'STICKYNOTES_SNOOZE_CUSTOM'			=> 'Pick date and time…',
	'STICKYNOTES_SNOOZE_UNTIL'			=> 'Snoozed until %s',
	'STICKYNOTES_SNOOZE_UNTIL_LABEL'	=> 'Hide until',
	'STICKYNOTES_SNOOZE_WAKE'			=> 'Show now',
	'STICKYNOTES_SNOOZE_SET'			=> 'Snooze',
	'STICKYNOTES_MINIMIZE'				=> 'Minimize to a bar',
	'STICKYNOTES_EXPAND'				=> 'Expand',
	'STICKYNOTES_BACK'					=> 'Back',
	'STICKYNOTES_TRASH'						=> 'Drag here to delete',
	'STICKYNOTES_TRASH_DROP'				=> 'Release to delete the note',
	'STICKYNOTES_ERR_SNOOZE_PAST'		=> 'Pick a future date and time.',
	'STICKYNOTES_UCP_MINIMIZED'			=> 'minimized to a bar',

	// Browser push notifications (phpbb/webpushnotifications) (1.0.9)
	'STICKYNOTES_UCP_PUSH'				=> 'Browser push notifications',
	'STICKYNOTES_UCP_PUSH_EXPLAIN'		=> 'Receive note alerts and shares as system notifications (computer or phone), even with the browser closed. Works with “Notification in the bell” or “Both”.',
	'STICKYNOTES_UCP_PUSH_DEVICES'		=> [
		1	=> 'Push notifications active on %d device.',
		2	=> 'Push notifications active on %d devices.',
	],
	'STICKYNOTES_UCP_PUSH_NO_DEVICE'	=> 'No device is registered for push notifications on this account yet.',
	'STICKYNOTES_UCP_PUSH_DEVICE_LINK'	=> 'Enable push notifications on this device',
]);

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
	'ACL_CAT_STICKYNOTES'			=> 'Sticky notes',
	'ACL_U_STICKYNOTES_USE'			=> 'Can use sticky notes',
	'ACL_U_STICKYNOTES_BBCODE'		=> 'Can use BBCode in sticky notes',
	'ACL_U_STICKYNOTES_SHARE'		=> 'Can share sticky notes with other users and groups',
	'ACL_U_STICKYNOTES_REMINDER'	=> 'Can set due-date alerts on sticky notes',
	'ACL_U_STICKYNOTES_NOLIMIT'		=> 'No limit on the number of sticky notes',
	'ACL_A_STICKYNOTES'				=> 'Can manage the Sticky Notes extension',
]);

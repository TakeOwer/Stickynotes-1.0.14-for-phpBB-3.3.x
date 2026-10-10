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
	'UCP_STICKYNOTES_TITLE'		=> 'Sticky notes',
	'UCP_STICKYNOTES_NOTES'		=> 'My notes',
	'UCP_STICKYNOTES_SETTINGS'	=> 'Notes preferences',
]);

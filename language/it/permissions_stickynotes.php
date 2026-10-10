<?php
/**
 *
 * Sticky Notes. An extension for the phpBB Forum Software package.
 * [Italiano]
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
	'ACL_CAT_STICKYNOTES'			=> 'Promemoria',
	'ACL_U_STICKYNOTES_USE'			=> 'Può usare i promemoria (sticky notes)',
	'ACL_U_STICKYNOTES_BBCODE'		=> 'Può usare il BBCode nei promemoria',
	'ACL_U_STICKYNOTES_SHARE'		=> 'Può condividere i promemoria con altri utenti e gruppi',
	'ACL_U_STICKYNOTES_REMINDER'	=> 'Può impostare avvisi a scadenza sui promemoria',
	'ACL_U_STICKYNOTES_NOLIMIT'		=> 'Nessun limite al numero di promemoria',
	'ACL_A_STICKYNOTES'				=> 'Può gestire l’estensione Sticky Notes',
]);

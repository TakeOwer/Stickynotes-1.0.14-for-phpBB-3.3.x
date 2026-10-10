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
	// Barra e note
	'STICKYNOTES_TITLE'					=> 'Promemoria',
	'STICKYNOTES_TRAY_OPEN'				=> 'Apri la barra dei promemoria',
	'STICKYNOTES_TRAY_CLOSE'			=> 'Chiudi la barra dei promemoria',
	'STICKYNOTES_NEW'					=> 'Nuovo',
	'STICKYNOTES_SHOW_ALL'				=> 'Mostra tutti sul forum',
	'STICKYNOTES_HIDE_ALL'				=> 'Nascondi tutti nella barra',
	'STICKYNOTES_SEARCH'				=> 'Cerca nei promemoria…',
	'STICKYNOTES_TAB_MINE'				=> 'I miei',
	'STICKYNOTES_TAB_SHARED'			=> 'Condivisi con me',
	'STICKYNOTES_EMPTY'					=> 'Nessun promemoria. Premi «Nuovo» per crearne uno.',
	'STICKYNOTES_EMPTY_SHARED'			=> 'Nessuno ha condiviso promemoria con te.',
	'STICKYNOTES_MANAGE'				=> 'Gestisci tutti',
	'STICKYNOTES_UNTITLED'				=> 'Senza titolo',
	'STICKYNOTES_EDIT'					=> 'Modifica',
	'STICKYNOTES_SAVE'					=> 'Salva',
	'STICKYNOTES_CANCEL'				=> 'Annulla',
	'STICKYNOTES_DELETE'				=> 'Elimina',
	'STICKYNOTES_DELETE_CONFIRM'		=> 'Vuoi davvero eliminare questo promemoria? L’operazione non può essere annullata.',
	'STICKYNOTES_HIDE'					=> 'Nascondi nella barra',
	'STICKYNOTES_SHOW'					=> 'Mostra sul forum',
	'STICKYNOTES_MENU'					=> 'Altre opzioni',
	'STICKYNOTES_CLOSE'					=> 'Chiudi',
	'STICKYNOTES_FOCUS'					=> 'Mostra questo promemoria',
	'STICKYNOTES_DRAG'					=> 'Trascina per spostare · doppio clic sul titolo per rinominare',
	'STICKYNOTES_RESIZE'				=> 'Trascina per ridimensionare',
	'STICKYNOTES_SHORTCUT'				=> 'Alt+Maiusc+N',
	'STICKYNOTES_COUNT_NOTES'			=> '%1$d di %2$d promemoria',
	'STICKYNOTES_READONLY'				=> 'Sola lettura',
	'STICKYNOTES_SAVED'					=> 'Salvato',

	// Colori
	'STICKYNOTES_COLOR'					=> 'Colore',
	'STICKYNOTES_COLOR_YELLOW'			=> 'Giallo',
	'STICKYNOTES_COLOR_PINK'			=> 'Rosa',
	'STICKYNOTES_COLOR_BLUE'			=> 'Azzurro',
	'STICKYNOTES_COLOR_GREEN'			=> 'Verde',
	'STICKYNOTES_COLOR_ORANGE'			=> 'Arancione',
	'STICKYNOTES_COLOR_PURPLE'			=> 'Lilla',
	'STICKYNOTES_COLOR_WHITE'			=> 'Bianco',

	// Ambito
	'STICKYNOTES_SCOPE'					=> 'Dove appare',
	'STICKYNOTES_SCOPE_GLOBAL'			=> 'Su tutte le pagine',
	'STICKYNOTES_SCOPE_PAGE'			=> 'Solo su questa pagina',
	'STICKYNOTES_SCOPE_GLOBAL_EXPLAIN'	=> 'Il promemoria sarà visibile su tutte le pagine del forum.',
	'STICKYNOTES_SCOPE_PAGE_EXPLAIN'	=> 'Il promemoria sarà visibile solo su questa pagina (argomento, forum o pagina).',

	// Promemoria a scadenza
	'STICKYNOTES_REMINDER'				=> 'Avviso a scadenza',
	'STICKYNOTES_REMINDER_SET'			=> 'Imposta avviso',
	'STICKYNOTES_REMINDER_REMOVE'		=> 'Rimuovi avviso',
	'STICKYNOTES_REMINDER_DATE'			=> 'Data e ora',
	'STICKYNOTES_REMINDER_REPEAT'		=> 'Ripeti',
	'STICKYNOTES_REPEAT_NONE'			=> 'Mai',
	'STICKYNOTES_REPEAT_DAILY'			=> 'Ogni giorno',
	'STICKYNOTES_REPEAT_WEEKLY'			=> 'Ogni settimana',
	'STICKYNOTES_REPEAT_MONTHLY'		=> 'Ogni mese',
	'STICKYNOTES_REMINDER_AT'			=> 'Avviso: %s',
	'STICKYNOTES_REMINDER_POPUP_TITLE'	=> 'Promemoria in scadenza',
	'STICKYNOTES_REMINDER_EXPIRED'		=> 'scaduto',

	// Condivisione
	'STICKYNOTES_SHARE'					=> 'Condividi',
	'STICKYNOTES_SHARE_USERS'			=> 'Utenti',
	'STICKYNOTES_SHARE_USERS_EXPLAIN'	=> 'Nomi utente separati da virgola',
	'STICKYNOTES_SHARE_GROUPS'			=> 'Gruppi di cui fai parte',
	'STICKYNOTES_SHARE_SAVE'			=> 'Salva condivisione',
	'STICKYNOTES_SHARE_NONE'			=> 'Non condiviso',
	'STICKYNOTES_SHARE_MISSING'			=> 'Questi utenti non sono stati trovati: %s',
	'STICKYNOTES_SHARED_WITH'			=> 'Condiviso con',
	'STICKYNOTES_SHARED_BY'				=> 'Condiviso da %s',

	// Altre azioni
	'STICKYNOTES_DUPLICATE'				=> 'Duplica',
	'STICKYNOTES_LOCK'					=> 'Blocca posizione',
	'STICKYNOTES_UNLOCK'				=> 'Sblocca posizione',
	'STICKYNOTES_LOCKED'				=> 'Posizione bloccata',

	// Editor
	'STICKYNOTES_FORMAT'				=> 'Formato del testo',
	'STICKYNOTES_FORMAT_PLAIN'			=> 'Testo semplice',
	'STICKYNOTES_FORMAT_BBCODE'			=> 'BBCode',
	'STICKYNOTES_BB_BOLD'				=> 'Grassetto',
	'STICKYNOTES_BB_ITALIC'				=> 'Corsivo',
	'STICKYNOTES_BB_UNDERLINE'			=> 'Sottolineato',
	'STICKYNOTES_BB_STRIKE'				=> 'Barrato',
	'STICKYNOTES_BB_LIST'				=> 'Elenco puntato',
	'STICKYNOTES_BB_URL'				=> 'Link',
	'STICKYNOTES_BB_COLOR'				=> 'Colore del testo',
	'STICKYNOTES_BB_CODE'				=> 'Codice',
	'STICKYNOTES_BB_QUOTE'				=> 'Citazione',
	'STICKYNOTES_URL_PROMPT'			=> 'Indirizzo del link',
	'STICKYNOTES_TITLE_PLACEHOLDER'		=> 'Titolo del promemoria',
	'STICKYNOTES_TEXT_PLACEHOLDER'		=> 'Scrivi qui il tuo promemoria…',
	'STICKYNOTES_CHARS_LEFT'			=> 'Caratteri rimanenti: %d',

	// Errori
	'STICKYNOTES_ERROR'					=> 'Promemoria',
	'STICKYNOTES_ERR_GENERIC'			=> 'Si è verificato un errore. Riprova tra qualche istante.',
	'STICKYNOTES_ERR_DISABLED'			=> 'I promemoria sono stati disattivati dall’amministratore.',
	'STICKYNOTES_ERR_NO_AUTH'			=> 'Non hai il permesso per eseguire questa operazione.',
	'STICKYNOTES_ERR_TOKEN'				=> 'La sessione è scaduta. Ricarica la pagina e riprova.',
	'STICKYNOTES_ERR_NOT_FOUND'			=> 'Il promemoria non esiste più.',
	'STICKYNOTES_ERR_TOO_LONG'			=> 'Il testo è troppo lungo.',
	'STICKYNOTES_ERR_LIMIT'				=> 'Hai raggiunto il numero massimo di promemoria (%d). Eliminane qualcuno per crearne di nuovi.',
	'STICKYNOTES_ERR_LIMIT_JS'			=> 'Hai raggiunto il numero massimo di promemoria (%d). Eliminane qualcuno per crearne di nuovi.',
	'STICKYNOTES_ERR_PAST_DATE'			=> 'La data dell’avviso è già passata.',
	'STICKYNOTES_ERR_ACTION'			=> 'Azione non valida.',

	// Posizioni della barra
	'STICKYNOTES_TRAY_TL'				=> 'In alto a sinistra',
	'STICKYNOTES_TRAY_TR'				=> 'In alto a destra',
	'STICKYNOTES_TRAY_BL'				=> 'In basso a sinistra',
	'STICKYNOTES_TRAY_BR'				=> 'In basso a destra',
	'STICKYNOTES_TRAY_DEFAULT'			=> 'Predefinita del forum (%s)',

	// Notifiche (campanella)
	'NOTIFICATION_TYPE_STICKYNOTES_REMINDER'	=> 'Un mio promemoria è arrivato alla data di scadenza',
	'NOTIFICATION_TYPE_STICKYNOTES_SHARED'		=> 'Qualcuno condivide un promemoria con me',
	'STICKYNOTES_NOTIFICATION_REMINDER'			=> '<strong>Promemoria in scadenza:</strong> %s',
	'STICKYNOTES_NOTIFICATION_REMINDER_AT'		=> 'Avviso impostato per %s',
	'STICKYNOTES_NOTIFICATION_SHARED'			=> '%1$s ha condiviso con te il promemoria <strong>%2$s</strong>',

	// UCP
	'STICKYNOTES_UCP_NOTES_EXPLAIN'		=> 'Qui trovi tutti i tuoi promemoria, anche quelli visibili solo su una pagina specifica. Clicca sul titolo per aprirlo sulla sua pagina.',
	'STICKYNOTES_UCP_INACTIVE'			=> 'Al momento i promemoria non vengono mostrati sul forum (disattivati nelle tue preferenze o dall’amministratore).',
	'STICKYNOTES_UCP_TOTAL'				=> 'Promemoria salvati',
	'STICKYNOTES_UCP_NOTE'				=> 'Promemoria',
	'STICKYNOTES_UCP_UPDATED'			=> 'Ultima modifica',
	'STICKYNOTES_UCP_IN_TRAY'			=> 'nascosto nella barra',
	'STICKYNOTES_UCP_DELETED'			=> [
		1	=> '%d promemoria eliminato.',
		2	=> '%d promemoria eliminati.',
	],
	'STICKYNOTES_UCP_DELETE_CONFIRM'	=> [
		1	=> 'Vuoi davvero eliminare il promemoria selezionato?',
		2	=> 'Vuoi davvero eliminare i %d promemoria selezionati?',
	],
	'STICKYNOTES_UCP_SAVED'				=> 'Le preferenze dei promemoria sono state salvate.',
	'STICKYNOTES_UCP_SETTINGS_EXPLAIN'	=> 'Scegli come e dove vedere i tuoi promemoria.',
	'STICKYNOTES_UCP_ENABLE'			=> 'Mostra i promemoria sul forum',
	'STICKYNOTES_UCP_ENABLE_EXPLAIN'	=> 'Se disattivi questa opzione la barra e i promemoria non verranno mostrati. I promemoria salvati non vengono cancellati.',
	'STICKYNOTES_UCP_TRAY'				=> 'Posizione della barra',
	'STICKYNOTES_UCP_TRAY_EXPLAIN'		=> 'L’angolo dello schermo in cui compare il pulsante della barra a comparsa.',
	'STICKYNOTES_UCP_NOTIFY'			=> 'Come avvisarmi alla scadenza',
	'STICKYNOTES_UCP_NOTIFY_EXPLAIN'	=> 'Scegli come ricevere l’avviso quando un promemoria arriva alla data impostata. La finestra compare sulla pagina aperta; la campanella vale anche per le notifiche push del browser.',
	'STICKYNOTES_UCP_NOTIFY_POPUP'		=> 'Finestra a comparsa del forum',
	'STICKYNOTES_UCP_NOTIFY_BELL'		=> 'Notifica nella campanella',
	'STICKYNOTES_UCP_NOTIFY_BOTH'		=> 'Entrambe',

	// Posticipa e riduci (1.0.1)
	'STICKYNOTES_SNOOZE'				=> 'Posticipa',
	'STICKYNOTES_SNOOZE_30M'			=> 'Per 30 minuti',
	'STICKYNOTES_SNOOZE_1H'				=> 'Per 1 ora',
	'STICKYNOTES_SNOOZE_4H'				=> 'Per 4 ore',
	'STICKYNOTES_SNOOZE_TOMORROW'		=> 'Fino a domani mattina',
	'STICKYNOTES_SNOOZE_WEEK'			=> 'Per una settimana',
	'STICKYNOTES_SNOOZE_CUSTOM'			=> 'Scegli data e ora…',
	'STICKYNOTES_SNOOZE_UNTIL'			=> 'Posticipato fino a %s',
	'STICKYNOTES_SNOOZE_UNTIL_LABEL'	=> 'Nascondi fino a',
	'STICKYNOTES_SNOOZE_WAKE'			=> 'Mostra adesso',
	'STICKYNOTES_SNOOZE_SET'			=> 'Posticipa',
	'STICKYNOTES_MINIMIZE'				=> 'Riduci a barretta',
	'STICKYNOTES_EXPAND'				=> 'Espandi',
	'STICKYNOTES_BACK'					=> 'Indietro',
	'STICKYNOTES_TRASH'						=> 'Trascina qui per eliminare',
	'STICKYNOTES_TRASH_DROP'				=> 'Rilascia per eliminare il promemoria',
	'STICKYNOTES_ERR_SNOOZE_PAST'		=> 'Scegli una data e un’ora future.',
	'STICKYNOTES_UCP_MINIMIZED'			=> 'ridotto a barretta',

	// Notifiche push del browser (phpbb/webpushnotifications) (1.0.9)
	'STICKYNOTES_UCP_PUSH'				=> 'Notifiche push del browser',
	'STICKYNOTES_UCP_PUSH_EXPLAIN'		=> 'Ricevi gli avvisi dei promemoria e le condivisioni come notifica del sistema (PC o telefono), anche con il browser chiuso. Funziona con «Notifica nella campanella» o «Entrambe».',
	'STICKYNOTES_UCP_PUSH_DEVICES'		=> [
		1	=> 'Notifiche push attive su %d dispositivo.',
		2	=> 'Notifiche push attive su %d dispositivi.',
	],
	'STICKYNOTES_UCP_PUSH_NO_DEVICE'	=> 'Su questo account non è ancora registrato nessun dispositivo per le notifiche push.',
	'STICKYNOTES_UCP_PUSH_DEVICE_LINK'	=> 'Attiva le notifiche push su questo dispositivo',
]);

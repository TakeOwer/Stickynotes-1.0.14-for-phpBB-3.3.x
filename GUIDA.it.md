# Sticky Notes — Guida all'uso

**Estensione:** `salvocortesiano/stickynotes` · **Versione:** 1.0.14
**Requisiti:** phpBB 3.3.x (testata per 3.3.19), PHP 7.4 o superiore
**Licenza:** GPL-2.0 · **Autore:** Salvo Cortesiano — [Le Ombre della Rete 360°](https://netshadows.de/ombra)

Promemoria in stile post-it che ogni utente può creare, scrivere, colorare e trascinare in qualsiasi punto del forum. Quando non servono si nascondono in una barra a comparsa posizionabile in uno dei quattro angoli dello schermo.

---

## 1. Installazione

1. Copia la cartella in `ext/salvocortesiano/stickynotes/` (deve contenere `composer.json`).
2. ACP → **Personalizza** → **Gestione estensioni** → **Sticky Notes** → **Abilita**.
3. ACP → **Estensioni** → **Sticky Notes** → **Check-up**: tutti i controlli devono essere verdi.
4. Svuota la cache (ACP → Generale → Svuota la cache) se i nuovi file di stile non compaiono.

All'installazione i permessi d'uso vengono assegnati in automatico ai ruoli **Funzionalità standard** e **Tutte le funzionalità** degli utenti, mentre il permesso amministrativo va ai ruoli amministratore standard e completo.

### Aggiornamento
Disabilita l'estensione, sostituisci i file e riabilitala. I dati non vengono persi.

### Disinstallazione
**Disabilita** e poi **Elimina i dati**: vengono rimosse le tabelle, le colonne aggiunte alla tabella utenti, la configurazione, i permessi, i moduli ACP/UCP e le notifiche.

---

## 2. Uso per gli utenti

### La barra a comparsa
- In un angolo dello schermo compare il pulsante giallo con la puntina. Il numero rosso indica quanti promemoria **attivi** sono nascosti nella barra: quelli con l'avviso già scaduto non vengono contati (nell'elenco hanno la campanella rossa).
- Un clic apre il pannello con: pulsante **Nuovo**, ricerca, **Mostra tutti** / **Nascondi tutti**, schede **I miei** e **Condivisi con me**, contatore (es. «5 di 30 promemoria») e link **Gestisci tutti** (UCP).
- Cliccando su un promemoria dell'elenco lo si porta in primo piano (se era nascosto viene rimesso sul forum). L'icona a forma di occhio lo nasconde o lo mostra.
- Lo stato aperto/chiuso della barra viene ricordato dal browser.
- Il pannello si chiude anche con il tasto **Esc**.

### Creare e modificare
- **Nuovo** (oppure la scorciatoia **Alt + Maiusc + N**) crea un promemoria e apre subito l'editor.
- Nell'editor si scrivono titolo e testo e si sceglie il formato: **Testo semplice** oppure **BBCode**. Con il BBCode compare una piccola barra: grassetto, corsivo, sottolineato, elenco, link, citazione, codice e colore.
- Il contatore mostra i caratteri rimanenti. Si salva con **Salva** o **Ctrl + Invio**; **Esc** o **Annulla** scartano le modifiche.
- Se durante la scrittura si apre un'altra funzione dal menu ⋮ (avviso a scadenza, condivisione, posticipo, colore…), quanto scritto viene salvato automaticamente e non va perso.
- **Doppio clic sul titolo**: rinomina al volo (Invio salva, Esc annulla).
- **Doppio clic sul testo**: apre l'editor.

### Spostare e ridimensionare
- Si trascina il promemoria afferrandolo dall'intestazione (funziona con mouse, penna e touch).
- L'angolo piegato in basso a destra serve a ridimensionarlo.
- Posizione e dimensioni sono salvate in percentuale dello schermo, quindi il promemoria resta al suo posto anche su monitor di misure diverse e non può mai uscire dallo schermo.
- Il promemoria cliccato viene portato in primo piano rispetto agli altri.

### Cestino durante il trascinamento
- Appena si inizia a trascinare un proprio promemoria compare, con una piccola animazione, un **cestino trasparente** (solo l'icona, senza riquadro) sopra o a fianco del pulsante della barra, ben distanziato da esso (posizione scelta nell'ACP; con la barra in alto il cestino «sopra» compare sotto il pulsante).
- Portando il promemoria sul cestino, il cestino diventa **pieno** (carta appallottolata, alone rosso, scritta «Rilascia per eliminare il promemoria») e il promemoria si rimpicciolisce.
- Rilasciandolo sul cestino compare il riquadro AJAX di conferma di phpBB:
  - **Sì**: il promemoria «vola» dentro il cestino e viene eliminato; il cestino torna vuoto e dopo circa 2,5 secondi sparisce;
  - **No** (oppure Esc o chiusura del riquadro): il promemoria torna con un'animazione esattamente dove si trovava prima del trascinamento, la sua posizione salvata non cambia; il cestino torna vuoto e sparisce dopo circa 2,5 secondi.
- Rilasciando il promemoria in qualsiasi altro punto il cestino sparisce subito e la nuova posizione viene salvata.
- Il cestino non compare per i promemoria condivisi (sola lettura), per quelli bloccati e nella modalità smartphone «solo barra». Funziona con mouse, penna e touch.

### Ridurre a barretta
- Il pulsante **˄** nell'intestazione (oppure la voce **Riduci a barretta** del menu) lascia sul forum solo la barretta con puntina, titolo e pulsanti.
- La barretta resta **trascinabile** come il promemoria intero e lo stato viene ricordato su tutte le pagine finché non la si riespande con **˅**.
- Mentre è ridotto il promemoria non si ridimensiona; larghezza e altezza originali tornano quando lo si espande.

### Posticipare (nascondere per un po')
- Dal menu ⋮ scegli **Posticipa…**: *Per 30 minuti*, *Per 1 ora*, *Per 4 ore*, *Fino a domani mattina* (alle 8:00), *Per una settimana* oppure *Scegli data e ora…*. Accanto a ogni voce è indicato l'orario in cui ricomparirà.
- Il promemoria sparisce dal forum e nella barra compare con l'orologio e l'ora del ritorno (es. 🕑 18:04). Il pulsante della barra lo conta tra quelli nascosti.
- Allo scadere **ricompare da solo**: alla prossima pagina visitata oppure, se la pagina è già aperta, nell'istante esatto, con un breve lampeggio.
- Per farlo tornare prima: occhio nella barra, voce **Mostra adesso** nel sottomenu, oppure **Mostra tutti**.
- Il massimo è un anno; date passate vengono rifiutate.

### Menu ⋮ del promemoria
| Voce | Cosa fa |
|---|---|
| Colori | Giallo, rosa, azzurro, verde, arancione, lilla, bianco |
| Modifica | Apre l'editor |
| Su tutte le pagine / Solo su questa pagina | Sceglie dove appare il promemoria |
| Avviso a scadenza | Data, ora e ripetizione dell'avviso |
| Condividi | Condivisione con utenti e gruppi |
| Posticipa… | Nasconde il promemoria per un tempo scelto |
| Riduci a barretta / Espandi | Lascia visibile solo la barretta del titolo |
| Duplica | Crea una copia spostata di lato |
| Blocca / Sblocca posizione | Impedisce di spostarlo o ridimensionarlo per errore |
| Elimina | Chiede conferma con il riquadro AJAX di phpBB |

Il pulsante **−** nasconde il promemoria nella barra.

### Dove appare un promemoria
- **Su tutte le pagine**: visibile ovunque sul forum.
- **Solo su questa pagina**: visibile solo sulla pagina in cui è stato impostato. La pagina è riconosciuta così: un argomento (`viewtopic.php?t=…`, anche se aperto da un link a un singolo messaggio), un forum (`viewforum.php?f=…`) o qualsiasi altra pagina (indice, membri, pagine delle estensioni…).

### Avvisi a scadenza
- Si impostano dal menu: data e ora (nel fuso orario del proprio dispositivo) e ripetizione **Mai / Ogni giorno / Ogni settimana / Ogni mese**.
- Con la ripetizione mensile il giorno viene mantenuto (un avviso del 31 arriva l'ultimo giorno dei mesi più corti e torna al 31 quando possibile).
- Il promemoria mostra in basso la riga con la data dell'avviso; quando è scaduto la riga diventa rossa.
- Alla scadenza l'avviso arriva **subito**: se la pagina del forum è aperta compare in quel momento, senza ricaricare (il contatore della campanella si aggiorna da solo e il promemoria lampeggia); se l'utente non è sul forum, lo riceve alla prima pagina che apre. Per chi non visita il forum la notifica nella campanella viene creata dal cron di phpBB.
- L'utente viene avvisato nel modo scelto nel proprio UCP:
  - **Finestra a comparsa del forum**: il riquadro AJAX di phpBB alla prima pagina visitata, con titolo e testo dei promemoria scaduti;
  - **Notifica nella campanella**: una notifica phpBB che, cliccata, apre la pagina del promemoria e lo evidenzia;
  - **Entrambe**.
- Gli avvisi ricorrenti passano da soli alla data successiva.
- La notifica nella campanella mostra il titolo del promemoria e l'inizio del suo testo.

### Notifiche push del browser (anche a browser chiuso)
Se sul forum è installata e attiva l'estensione ufficiale **phpBB Browser Push Notifications** (`phpbb/webpushnotifications`) con le chiavi VAPID configurate, gli avvisi arrivano anche come notifica del sistema operativo (Windows, Android, macOS…), **anche con il forum chiuso o il browser chiuso** (se il browser resta attivo in background, come fanno Chrome ed Edge).
- In **UCP → Promemoria → Preferenze promemoria** compare l'opzione **«Notifiche push del browser»**: impostandola su **Sì** l'estensione attiva il metodo push per i tipi di notifica «Promemoria in scadenza» e «Promemoria condiviso con te» (gli stessi interruttori si trovano in UCP → Preferenze notifiche).
- Sotto l'opzione viene indicato su quanti dispositivi sono attive le notifiche push; se nessuno, il link porta alla pagina dove abilitarle per il browser in uso (pulsante dell'estensione push).
- La notifica push contiene il titolo del promemoria e l'inizio del testo; cliccandola si apre il forum con il promemoria evidenziato.
- La modalità di avviso deve essere **Notifica nella campanella** o **Entrambe**: il push viaggia insieme alla notifica della campanella.
- Con il forum chiuso la notifica viene creata dal **cron di phpBB**: con il cron di sistema ogni 5 minuti il push arriva entro 5 minuti dalla scadenza; per l'orario esatto impostare il cron **ogni minuto** (`* * * * *`). Con la pagina del forum aperta l'avviso arriva subito.
- Se l'estensione push non è installata o non è attivata, l'opzione non compare e tutto funziona come prima (campanella e finestra a comparsa); il Check-up dell'ACP consiglia come attivarla.

### Stile ForumUS
Oltre a prosilver, l'estensione è adattata allo stile **ForumUS**:
- aspetto della barra e dei pannelli con i colori e il font Roboto dello stile;
- il contatore della campanella di ForumUS (e l'elenco a discesa delle notifiche) si aggiorna alla scadenza senza ricaricare la pagina;
- promemoria e barra stanno sempre sopra l'intestazione fissa e a ogni altro elemento dello stile;
- il pulsante della barra evita da solo gli elementi fissi dello stile (intestazione fissa, menu del profilo, campanella, logo, pulsante «torna su») in tutte e quattro le posizioni.

### Condivisione
- Dal menu **Condividi** si indicano nomi utente separati da virgola e/o si spuntano i gruppi di cui si fa parte (ospiti e bot esclusi).
- Chi riceve la condivisione trova il promemoria nella scheda **Condivisi con me**, in **sola lettura**, e riceve una notifica nella campanella (solo i nuovi destinatari).
- Se un nome utente non esiste viene mostrato un avviso con l'elenco dei nomi non trovati.

### Pannello di controllo utente (UCP → Promemoria)
- **I miei promemoria**: elenco completo (anche quelli legati ad altre pagine) con colore, anteprima, ambito, avviso, condivisioni e data di modifica. Il titolo apre il promemoria sulla sua pagina. Eliminazione singola (riquadro AJAX) o multipla con le caselle di selezione. Sotto, l'elenco dei promemoria condivisi con te.
- **Preferenze promemoria**: mostra/nascondi i promemoria sul forum, posizione della barra (se consentita dall'amministratore) e modalità di avviso alla scadenza.

### Smartphone
A seconda dell'impostazione dell'ACP: funzionamento completo, **solo barra** (i promemoria si aprono a schermo come finestra, senza trascinamento) oppure disattivato.

---

## 3. Amministrazione (ACP → Estensioni → Sticky Notes)

In cima a ogni pagina ci sono i riquadri con versione dell'estensione, versione PHP, versione phpBB, licenza GPL e stato dell'estensione; in fondo i crediti.

### Impostazioni
| Opzione | Descrizione |
|---|---|
| Attiva i promemoria | Interruttore generale (i dati restano salvati) |
| Numero massimo per utente | 0 = illimitato |
| Lunghezza massima del testo | Caratteri per promemoria, 0 = illimitato |
| Colore predefinito | Colore dei nuovi promemoria |
| Leggera inclinazione casuale | Effetto «bacheca» |
| Posizione predefinita della barra | In alto/basso a sinistra/destra |
| Gli utenti possono scegliere la posizione | Abilita la scelta nell'UCP |
| Cestino durante il trascinamento | Sì/No: mostra il cestino mentre si trascina un promemoria |
| Posizione del cestino | Sopra o a fianco del pulsante della barra |
| Comportamento su smartphone | Completo / Solo barra / Disattivato |
| Consenti il BBCode | Abilita il formato BBCode |
| Consenti gli avvisi a scadenza | Abilita avvisi e notifiche |
| Consenti la condivisione | Abilita la condivisione |

### Gestione e statistiche
- **Statistiche**: promemoria totali, utenti che li usano, avvisi programmati, **avvisi scaduti** (riquadro rosso se ce ne sono), ricorrenti, condivisi, nascosti, posticipati, ridotti, bloccati, con BBCode, solo su una pagina, vuoti, non modificati da 30 giorni, creati negli ultimi 7 e 30 giorni. Ogni riquadro è cliccabile e apre l'elenco già filtrato.
- **Promemoria per colore**: barra di distribuzione con percentuali.
- **Tutti i promemoria**: elenco di ogni promemoria di ogni utente, 25 per pagina, con:
  - filtri per utente (con «Cerca un utente»), testo o titolo, colore, stato (avviso programmato / scaduto / ricorrente / senza avviso, nascosti, posticipati, ridotti, bloccati, condivisi, vuoti, con BBCode) e ambito;
  - ordinamento per ultima modifica, creazione, data dell'avviso, titolo o utente;
  - per ogni promemoria: colore, titolo, anteprima del testo, etichette di stato, proprietario, dove appare, avviso con stato (*scaduto*, *programmato* o ripetizione), date;
  - **Vedi**: mostra il promemoria completo nel riquadro di phpBB (l'accesso viene scritto nel registro amministratore);
  - **Elimina** singolo e **azioni multiple** sui selezionati: elimina, rimuovi l'avviso, rimetti in vista (mostra, sveglia, espandi), riposiziona e ridimensiona. Tutte chiedono conferma con il riquadro AJAX di phpBB.
- **Utenti con più promemoria**: totale, avvisi scaduti, ultima attività e collegamento ai loro promemoria.
- Se ci sono avvisi scaduti o promemoria vuoti compare un avviso con il collegamento alla scheda Manutenzione.

### Manutenzione
Ogni strumento, prima di partire, conta i promemoria coinvolti e lo indica nella conferma («verranno coinvolti 3 promemoria»). Se non c'è nulla da fare lo dice senza eseguire niente. L'esecuzione avviene a blocchi di 250 con **barra di avanzamento a percentuale reale** e viene scritta nel registro amministratore.

| Strumento | Cosa fa |
|---|---|
| Elimina i promemoria con avviso scaduto | Avvisi singoli già passati, opzionalmente scaduti da almeno N giorni (0 = tutti) |
| Rimuovi solo gli avvisi scaduti | Tiene il promemoria, toglie l'avviso e la sua notifica |
| Elimina i promemoria inattivi | Non modificati da almeno N giorni |
| Elimina i promemoria vuoti | Senza titolo e senza testo |
| Elimina i promemoria di un utente | Con «Cerca un utente» |
| Pulizia dei dati orfani | Promemoria di utenti eliminati e condivisioni non valide |
| Riporta in vista tutti i promemoria | Griglia ordinata, dimensioni standard, espansi, posticipi annullati |
| Elimina TUTTI i promemoria | Svuota tutto (esporta prima il CSV) |

> Un avviso **ricorrente** non scade mai: alla scadenza passa da solo alla data successiva, per questo non viene toccato dagli strumenti sugli avvisi scaduti.

Appena premi un pulsante, il pulsante mostra «In corso…» e compare il riquadro di avanzamento («Conteggio dei promemoria coinvolti…»). Dopo la conferma la barra mostra la percentuale reale e alla fine l'esito; la pagina si aggiorna da sola dopo qualche secondo. Se rispondi «No» il riquadro si chiude e non viene modificato nulla.

**Pulizia automatica**: il cron di phpBB, una volta al giorno, può eliminare i promemoria con avviso scaduto da più di N giorni e/o quelli non modificati da N giorni (0 = disattivata). **Salva impostazioni** registra i valori senza ricaricare la pagina e aggiorna subito il riquadro di stato; **Esegui ora** salva, conta i promemoria coinvolti, chiede conferma ed esegue la pulizia subito con la barra di avanzamento. La pagina mostra l'ultima esecuzione e quanti promemoria sono stati eliminati. Il Check-up segnala se il cron non gira da più di 3 giorni.

**Esportazione**: scarica tutti i promemoria in CSV (UTF-8, separatore «;», si apre direttamente con Excel o LibreOffice), utile come backup.

### Check-up
Verifica e segnala con ✔ / ⚠ / ✘:
- versione PHP e phpBB, estensione attivata;
- esistenza di tabelle e colonne, test reale di scrittura/lettura/cancellazione;
- valori di configurazione;
- permessi esistenti e a quanti gruppi e utenti sono assegnati (direttamente o tramite ruolo). Per `a_stickynotes` vengono considerati anche i fondatori, che in phpBB hanno sempre tutti i permessi amministrativi; `u_stickynotes_nolimit` è facoltativo e non genera avvisi;
- estensione Web Push (`phpbb/webpushnotifications`), con 4 casi:
  - **non presente sul server** → ℹ Consiglio, con link per scaricarla da phpBB.com;
  - **caricata ma non attivata** → ℹ Consiglio, con link «Attivala ora» alla gestione estensioni;
  - **attivata ma Web Push spento o senza chiavi VAPID** → ⚠ Avviso, con link alle Impostazioni Web Push;
  - **attiva** → ✔ OK, con il numero di utenti che ricevono gli avvisi via push.
  I consigli (ℹ) non sono errori né avvisi: sono contati a parte nel riepilogo;
- tipi di notifica registrati e attivi (se uno non è ancora registrato, il Check-up lo registra subito);
- ultima esecuzione del cron degli avvisi e avvisi in ritardo;
- indirizzo AJAX, interpretazione del BBCode, codifica JSON sicura;
- presenza di CSS, JavaScript, font, template e file di lingua;
- coerenza tra file di lingua italiano e inglese;
- promemoria orfani.

### Permessi (ACP → Permessi → Permessi gruppi o utenti → Permessi utente → scheda «Promemoria»)
| Permesso | Significato |
|---|---|
| `u_stickynotes_use` | Può usare i promemoria |
| `u_stickynotes_bbcode` | Può usare il BBCode |
| `u_stickynotes_share` | Può condividere |
| `u_stickynotes_reminder` | Può impostare avvisi a scadenza |
| `u_stickynotes_nolimit` | Nessun limite al numero di promemoria |
| `a_stickynotes` | Può gestire l'estensione dall'ACP |

> **Dove si trovano:** i permessi `u_` sono in *Permessi utente → scheda «Promemoria»*; il permesso `a_stickynotes` è un permesso **amministratore** e si trova in *Permessi gruppi → Permessi amministratore → scheda «Misti»*. I fondatori lo hanno sempre, anche se non assegnato.

---

## 4. Dettagli tecnici

### Database
- `phpbb_stickynotes`: un record per promemoria (proprietario, titolo, testo con UID/bitfield/flag BBCode, formato, colore, ambito e pagina, posizione X/Y in centesimi di percentuale, larghezza, altezza, livello, rotazione, nascosto, bloccato, ridotto, posticipato fino a, dati dell'avviso, date).
- `phpbb_stickynotes_shares`: condivisioni (promemoria, tipo utente/gruppo, destinatario).
- Colonne aggiunte a `phpbb_users`: `user_stickynotes_enabled`, `user_stickynotes_tray`, `user_stickynotes_notify`.

### Struttura
```
salvocortesiano/stickynotes/
├── composer.json, ext.php, GUIDA.md
├── config/            services.yml, routing.yml, tables.yml
├── migrations/        v100_install.php … v1014_fix.php
├── service/           note_manager.php (logica centrale), maintenance.php (manutenzione e pulizia), webpush.php (integrazione Web Push)
├── controller/        main_controller.php (AJAX), acp_controller.php, acp_manage_controller.php, ucp_controller.php
├── event/             main_listener.php
├── notification/type/ reminder.php, shared.php
├── cron/task/         reminders.php (avvisi), cleanup.php (pulizia automatica)
├── acp/ ucp/          moduli
├── adm/style/         template ACP, js/stickynotes_acp.js (pulsanti di Gestione e Manutenzione)
├── language/it|en/    common, info_acp, info_ucp, permissions
└── styles/all/        template (eventi, UCP, JS) e theme (CSS, font Caveat OFL)
```

### Funzionamento
- **Caricamento**: all'evento `core.page_header` l'estensione prepara in un unico blocco JSON i promemoria della pagina, i condivisi, gli avvisi scaduti e tutti i testi dell'interfaccia (presi dai file di lingua). Nessuna richiesta AJAX aggiuntiva al caricamento della pagina.
- **Salvataggio**: ogni azione chiama `app.php/stickynotes/ajax/{azione}` in POST con token anti-CSRF (`create`, `update`, `props`, `toggle_all`, `duplicate`, `delete`, `reminder`, `share`, `dismiss`, `shared_get`). Ogni richiesta verifica permessi, proprietà del promemoria e limiti.
- **Testo**: elaborato con le funzioni standard di phpBB (`generate_text_for_storage` / `generate_text_for_display`), quindi BBCode, link e faccine si comportano come nei messaggi e il contenuto è protetto da XSS.
- **Avvisi**: gli avvisi scaduti di un utente vengono elaborati a ogni sua pagina (evento `core.page_header`, prima che phpBB conti le notifiche, così la campanella è già aggiornata) e, con la pagina aperta, da un timer nel browser che alla scadenza chiama il server (azione `due`), mostra il riquadro e aggiorna il contatore della campanella. Il cron di phpBB (al massimo una volta al minuto) serve per gli utenti che in quel momento non sono sul forum.
- **Conferme**: tutte le eliminazioni usano il riquadro AJAX di conferma di phpBB, mai la finestra del browser.
- **Pulizia automatica**: alla cancellazione di un utente vengono eliminati i suoi promemoria e le condivisioni verso di lui; alla cancellazione di un gruppo le condivisioni verso quel gruppo.
- **Stampa**: barra e promemoria non compaiono nella stampa delle pagine.

### Font
Il font in stile manoscritto è **Caveat** (SIL Open Font License, file in `styles/all/theme/fonts/`), ospitato localmente: nessuna richiesta a Google Fonts.

---

## 5. Domande frequenti

**La barra non compare.** Controlla: estensione attivata nelle impostazioni, permesso `u_stickynotes_use` del gruppo, opzione «Mostra i promemoria sul forum» nell'UCP, cache svuotata. Il Check-up segnala la maggior parte dei problemi.

**Gli avvisi nella campanella arrivano in ritardo.** Per chi sta usando il forum arrivano subito (dalla versione 1.0.8). Per chi non è collegato la notifica viene creata dal cron di phpBB, che esegue un'operazione per ogni pagina visitata: se il forum ha poco traffico attiva il cron di sistema (ACP → Impostazioni server → «Esegui le operazioni periodiche dal cron di sistema»). Il Check-up segnala avvisi in ritardo di oltre 15 minuti.

**Le notifiche push del browser non arrivano.** Controlla: estensione «Browser Push Notifications» attiva con chiavi VAPID (il Check-up lo indica), opzione «Notifiche push del browser» su Sì nell'UCP, almeno un dispositivo registrato (indicato sotto l'opzione), modalità di avviso «campanella» o «entrambe», permessi notifiche del browser/sistema non bloccati. A forum chiuso il ritardo massimo è l'intervallo del cron di sistema.

**Ho cambiato pagina e non vedo un promemoria.** Probabilmente è impostato «Solo su questa pagina»: lo trovi in UCP → I miei promemoria, dove il titolo apre la pagina giusta.

---

## Cronologia versioni

### 1.0.14 — 10/10/2026
- Cestino **trasparente**: solo l'icona e la scritta, senza riquadro bianco, con un leggero alone che lo rende leggibile su sfondi chiari e scuri; da pieno l'alone diventa rosso.
- Cestino più distante dal pulsante della barra (34 px, prima 12): non sembra più sovrapposto, nemmeno quando si ingrandisce da pieno.

### 1.0.13 — 10/10/2026
- **Nuovo**: cestino a scomparsa durante il trascinamento. Compare sopra o a fianco del pulsante della barra solo mentre si trascina un promemoria; diventa pieno quando il promemoria è sopra di lui; rilasciandolo chiede conferma con il riquadro AJAX di phpBB. Con «Sì» il promemoria finisce nel cestino e viene eliminato, con «No» torna con un'animazione al suo posto. Il cestino torna vuoto e sparisce dopo circa 2,5 secondi.
- ACP → Impostazioni: nuove opzioni «Cestino durante il trascinamento» (Sì/No) e «Posizione del cestino» (sopra / a fianco).
- Nuova migrazione `v1013_trash` (due nuove impostazioni).

### 1.0.12 — 10/10/2026
- **Corretto**: il numero sul pulsante della barra contava anche i promemoria con l'avviso già scaduto. Ora conta solo quelli attivi e torna a 0 alla scadenza, anche senza ricaricare la pagina. Nell'elenco della barra i promemoria scaduti hanno la campanella rossa.
- **Corretto**: scrivendo titolo e testo di un promemoria e aprendo poi «Avviso a scadenza» (o Condividi, Posticipa, un cambio di colore…) titolo e testo andavano persi. Ora il testo in modifica viene salvato da solo prima di aprire l'altra funzione; solo «Annulla» lo scarta.

### 1.0.11 — 10/10/2026
- **Corretto**: un promemoria trascinato in alto finiva sotto l'intestazione del forum (fissa e con uno z-index più alto di quello dei promemoria) e non si poteva più prendere. Ora promemoria, loro menu e barra stanno sopra a qualsiasi elemento del forum; restano sopra di loro solo i riquadri di conferma/avviso di phpBB.

### 1.0.10 — 10/10/2026
- Check-up: la riga Web Push ora distingue estensione assente, caricata ma non attivata, non configurata e attiva, e per ogni caso indica cosa fare con un link (download da phpBB.com, «Attivala ora», Impostazioni Web Push).
- Nuovo stato **ℹ Consiglio** nel Check-up, contato a parte nel riepilogo.

### 1.0.9 — 10/10/2026
- **Nuovo**: notifiche push del browser tramite l'estensione `phpbb/webpushnotifications`, anche a browser/forum chiuso. Prima non arrivavano perché il metodo push è disattivato di default per i tipi di notifica delle estensioni: ora si attiva con un'opzione in UCP → Preferenze promemoria, che mostra anche i dispositivi registrati.
- La notifica (campanella e push) mostra l'inizio del testo del promemoria invece della sola data.
- Check-up: nuova riga sullo stato dell'estensione Web Push.
- **Stile ForumUS**: aspetto adattato, campanella e relativo elenco aggiornati in tempo reale alla scadenza, pulsante della barra che non copre più logo, menu del profilo, campanella e «torna su» in nessuna delle quattro posizioni.
- Corretto un avviso PHP (`user_options`) quando il cron da riga di comando preparava il testo della notifica.

### 1.0.8 — 10/10/2026
- **Corretto**: allo scadere di un promemoria non compariva nessun avviso, né a video né nella campanella.
  - Con la pagina aperta nessuno controllava la scadenza (il riquadro veniva verificato solo al caricamento della pagina): ora un timer nel browser, all'orario esatto, mostra il riquadro di phpBB, crea la notifica, aggiorna il contatore della campanella senza ricaricare e fa lampeggiare il promemoria.
  - La notifica nella campanella dipendeva solo dal cron di phpBB, che esegue un'operazione per pagina visitata a turno con quelle di phpBB (poteva arrivare in ritardo, o mai se lo stile non stampa il cron o il cron di sistema non gira): ora gli avvisi dell'utente vengono elaborati a ogni sua pagina, prima che phpBB conti le notifiche. Il cron resta per gli utenti non collegati.
- Rispettate le tre scelte dell'UCP (finestra, campanella, entrambe) e gli avvisi ricorrenti.

### 1.0.7 — 10/10/2026
Correzioni trovate collaudando l'estensione in un **phpBB 3.3.19 reale** (installazione completa, debug attivo, due utenti, cron, notifiche, UCP e ACP):
- **Corretto**: salvando un promemoria con il testo vuoto (es. cambiando solo il titolo) compariva «Si è verificato un errore», anche se il salvataggio riusciva. Il parser dei messaggi di phpBB cercava la stringa `TOO_FEW_CHARS` della lingua *posting*, non caricata nelle richieste AJAX, e stampava un warning prima della risposta. Ora un testo vuoto non passa dal parser, la lingua *posting* viene caricata e gli eventuali avvisi del parser (troppi link, faccine…) diventano un messaggio chiaro senza salvare.
- **Corretto**: il doppio clic sul titolo per rinominare non funzionava nei browser veri: il trascinamento «catturava» il puntatore al primo clic. Ora il trascinamento parte solo quando il mouse si sposta davvero.
- **Corretto**: le finestre di conferma e di avviso di phpBB stavano sotto i promemoria; un promemoria nel punto in cui si apre la conferma copriva i pulsanti Sì/No. Ora le finestre di phpBB sono sempre sopra.
- **Corretto**: il menu ⋮ di un promemoria vicino al fondo dello schermo usciva dallo schermo (voci irraggiungibili) e lo stesso accadeva all'editor (pulsante Salva fuori vista). Ora il menu si apre verso l'alto e i moduli spostano temporaneamente il promemoria per restare visibili, senza cambiarne la posizione salvata.
- **Corretto**: il duplicato compariva quasi sopra l'originale, coprendone i pulsanti. Ora viene messo accanto (a destra, a sinistra o sotto, dove c'è spazio).
- Le risposte AJAX vengono lette correttamente anche se un'altra estensione stampa un avviso di debug prima del JSON.

### 1.0.6 — 10/10/2026
- **Corretto**: nelle schede **Manutenzione** e **Gestione e statistiche** i pulsanti (Esegui, Vedi, Elimina, azioni multiple, selezione di tutti) non facevano nulla. L'ACP di phpBB carica jQuery in fondo alla pagina e gli script scritti dentro il template partivano prima, bloccandosi con l'errore «jQuery is not defined». Ora tutto il JavaScript dell'ACP è nel file `adm/style/js/stickynotes_acp.js`, incluso con `INCLUDEJS` e quindi caricato da phpBB dopo jQuery e `core.js`.
- Ogni pulsante risponde subito al click («In corso…») e il riquadro di avanzamento compare già durante il conteggio, poi mostra la percentuale reale e l'esito.
- Pulizia automatica: **Salva impostazioni** senza ricaricare la pagina (con conferma e stato aggiornati) e nuovo pulsante **Esegui ora** con barra di avanzamento. Senza JavaScript il salvataggio funziona comunque con il normale invio del modulo.
- Collaudo end-to-end nell'ACP reale di phpBB 3.3 (header, footer, jQuery 3.7.1, core.js, ajax.js, admin.js originali) con il codice PHP dell'estensione: tutti i pulsanti delle due schede verificati con le modifiche effettive sul database.

### 1.0.5 — 10/10/2026
- **Corretto**: nel registro amministratore i moduli dell'estensione comparivano con la chiave grezza (es. «Modulo aggiunto » ACP_STICKYNOTES_MAINTENANCE»). phpBB scrive quella riga mentre attiva l'estensione, prima che i suoi file di lingua siano caricati.
  - le righe già presenti nel registro ora vengono tradotte al momento della visualizzazione (in italiano o inglese secondo la lingua dell'amministratore);
  - le migrazioni caricano i nomi dei moduli prima di crearli o rimuoverli, quindi anche le installazioni e disinstallazioni future scrivono il nome tradotto.
- Verifica completa di tutte le stringhe: chiavi uguali in italiano e inglese in ogni file, nessuna stringa vuota o non tradotta, stessi segnaposto nelle due lingue, ogni voce del registro con il numero giusto di parametri, nomi dei moduli, permessi, notifiche, chiavi dinamiche e testi JavaScript.

### 1.0.4 — 10/10/2026
- **Gestione e statistiche** riscritta: 16 statistiche cliccabili, distribuzione per colore, elenco completo di tutti i promemoria con filtri, ordinamento, paginazione, anteprima e azioni multiple (elimina, rimuovi avviso, rimetti in vista, riposiziona).
- Nuova scheda **Manutenzione**: 8 strumenti con conteggio preventivo, conferma AJAX e barra di avanzamento reale (avvisi scaduti, solo rimozione avvisi, inattivi, vuoti, per utente, orfani, riporta in vista, elimina tutto).
- **Pulizia automatica** giornaliera tramite cron, configurabile in giorni.
- **Esportazione CSV** di tutti i promemoria.
- UCP: gli avvisi già passati sono indicati come «scaduto».
- Check-up: controllo delle nuove impostazioni, dei nuovi template e della pulizia automatica.
- Tutte le operazioni vengono scritte nel registro amministratore.
- Nuova migrazione `v104_management` (impostazioni della pulizia automatica e modulo Manutenzione).

### 1.0.3 — 10/10/2026
- **Corretto**: il Check-up segnalava `a_stickynotes` come non assegnato anche quando gli amministratori potevano usare l'ACP. Ora conta gruppi **e** utenti (anche tramite ruolo) e considera i fondatori; il messaggio indica in quale scheda dei permessi intervenire.
- **Corretto**: il riepilogo in cima al Check-up era bianco su giallo e illeggibile. Ora ha un riquadro proprio (verde, giallo o rosso) leggibile con qualsiasi stile ACP.
- Il tipo di notifica «condivisione» viene registrato subito (all'attivazione o dal Check-up) invece di attendere la prima condivisione.
- Aggiunto al Check-up il controllo di `u_stickynotes_nolimit` (facoltativo).
- Nuova migrazione `v103_fix` (aggiorna solo il numero di versione).

### 1.0.2 — 10/10/2026
- **Corretto**: l'eliminazione dal menu ⋮ non funzionava perché il riquadro di conferma AJAX di phpBB veniva aperto senza i pulsanti Sì/No. Ora il riquadro ha i pulsanti con le diciture del pacchetto lingua del forum.
- **Corretto**: stesso problema nella scheda ACP Gestione (pulizia dei dati orfani ed eliminazione dei promemoria di un utente).
- **Corretto**: la puntina rossa dei promemoria ridotti a barretta era tagliata a metà.
- Nuova migrazione `v102_fix` (aggiorna solo il numero di versione, nessuna modifica al database).

### 1.0.1 — 09/10/2026
- **Posticipa**: ogni promemoria può essere nascosto per 30 minuti, 1 ora, 4 ore, fino a domani mattina, una settimana o fino a una data scelta; ricompare da solo, anche senza ricaricare la pagina.
- **Riduci a barretta**: il promemoria resta sul forum come sola barretta del titolo, sempre trascinabile; lo stato è salvato.
- Barra: indicazione dell'orario di ritorno dei promemoria posticipati; «Mostra tutti» riporta anche quelli posticipati.
- UCP: etichette «ridotto a barretta» e «posticipato fino a…».
- ACP: due nuove statistiche e controllo delle nuove colonne nel Check-up.
- Nuova migrazione `v101_snooze_minimize` (aggiornamento senza perdita di dati).

### 1.0.0 — 09/10/2026
Prima versione: post-it trascinabili e ridimensionabili, barra a comparsa nei 4 angoli (ACP e UCP), testo semplice o BBCode, 7 colori, ambito globale o per pagina, avvisi a scadenza con ripetizione e scelta tra popup AJAX e campanella, condivisione con utenti e gruppi, duplica, blocco posizione, ricerca, mostra/nascondi tutti, scorciatoia da tastiera, modalità smartphone, UCP completo, ACP con impostazioni, gestione/statistiche, pulizia con barra di avanzamento e Check-up, lingue italiano e inglese.

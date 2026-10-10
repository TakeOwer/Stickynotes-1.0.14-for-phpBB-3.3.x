# Sticky Notes for phpBB

![Version](https://img.shields.io/badge/version-1.0.15-105080)
![phpBB](https://img.shields.io/badge/phpBB-3.3.x-377a33)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-377a33)
![License](https://img.shields.io/badge/license-GPL--2.0--only-7f7f7f)

**Extension:** `salvocortesiano/stickynotes`
**Version:** 1.0.15
**Requirements:** phpBB 3.3.x (tested on 3.3.19), PHP 7.4 or higher
**License:** [GPL-2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
**Author:** Salvo Cortesiano — [Le Ombre della Rete 360°](https://netshadows.de/ombra)

Post-it style notes that every member can create, write, colour and drag anywhere on the board. When they are not needed they hide in a pop-up bar that can sit in any of the four corners of the screen. Notes can have due-date alerts (board pop-up, notification bell and browser push notifications), can be shared with users and groups, and are fully managed from the ACP.

Languages included: **English** and **Italian**.

---

<img width="317" height="254" alt="Screenshot 2026-10-10 135311" src="https://github.com/user-attachments/assets/8040ddd9-f310-42c8-b152-98c35506e4ee" />
---
<img width="832" height="429" alt="Screenshot 2026-10-10 135837" src="https://github.com/user-attachments/assets/a9efdb49-fd20-484f-af44-ab21d70122c5" />
---
<img width="338" height="353" alt="Screenshot 2026-10-10 135843" src="https://github.com/user-attachments/assets/76108fac-9ce6-4b20-8d4d-354fc925928a" />
---
<img width="711" height="500" alt="Screenshot 2026-10-10 135854" src="https://github.com/user-attachments/assets/8371df57-6c86-40c7-b067-394725663b55" />
---
<img width="134" height="102" alt="Screenshot 2026-10-10 135904" src="https://github.com/user-attachments/assets/9bb42430-60a9-4fe3-93c6-28da18ae6b41" />
---
<img width="1833" height="904" alt="Screenshot 2026-10-10 135923" src="https://github.com/user-attachments/assets/d0d8f96b-577a-4cbd-b7bb-daf676f879bc" />
---
<img width="1837" height="679" alt="Screenshot 2026-10-10 135941" src="https://github.com/user-attachments/assets/5465a2b7-29cd-4da1-89e5-f3602e23b385" />
---
<img width="1501" height="62" alt="Screenshot 2026-10-10 140018" src="https://github.com/user-attachments/assets/58b63c5a-ba1f-44b6-9469-488b5cdf588a" />
---
<img width="2292" height="210" alt="Screenshot 2026-10-10 140035" src="https://github.com/user-attachments/assets/24736bf4-09fb-43cd-bee1-377a34b22810" />
---
<img width="2294" height="342" alt="Screenshot 2026-10-10 140047" src="https://github.com/user-attachments/assets/73981f36-28b1-475f-9c7f-16ddd289daa8" />
---
<img width="1549" height="1028" alt="Screenshot 2026-10-10 143120" src="https://github.com/user-attachments/assets/eb0f9bbc-b4bc-499e-9bbb-269daf39ef5c" />
---
<img width="430" height="436" alt="Screenshot 2026-10-10 150258" src="https://github.com/user-attachments/assets/11f5b5af-03a4-4060-85b5-b4f857b1e75c" />
---
<img width="287" height="313" alt="Screenshot 2026-10-10 150307" src="https://github.com/user-attachments/assets/8668a34d-c11e-45cb-a09e-3212467da785" />
---
<img width="317" height="161" alt="Screenshot 2026-10-10 150332" src="https://github.com/user-attachments/assets/07080d12-a35d-461a-9946-ec16ee99566a" />
---
<img width="2294" height="919" alt="Screenshot 2026-10-10 151810" src="https://github.com/user-attachments/assets/1efc31b7-0104-4532-9dc1-e6297d365463" />
---
<img width="2298" height="552" alt="Screenshot 2026-10-10 151819" src="https://github.com/user-attachments/assets/a57f66b8-c7c2-4920-8995-88b9d85b210a" />

---

## Table of contents

1. [Features](#features)
2. [Installation](#installation)
3. [Using the notes](#using-the-notes)
4. [Administration](#administration)
5. [Permissions](#permissions)
6. [Technical details](#technical-details)
7. [FAQ](#faq)
8. [Changelog](#changelog)

---

## Features

- Notes you can drag (mouse, pen and touch), resize, minimize to a title bar and lock in place.
- A **bin** appears while dragging: drop a note on it to delete it (with confirmation). Answer No and the note flies back to its place.
- Pop-up bar in any of the four corners, chosen by the administrator and optionally by each user.
- Plain text or **BBCode**, with a small toolbar: bold, italic, underline, list, link, quote, code, colour.
- 7 colours, optional slight random tilt, handwritten font (Caveat, hosted locally).
- A note can be shown **on every page** or **only on one page** (topic, forum or any other page).
- **Snooze** a note for a while; it comes back by itself.
- **Due-date alerts**, which can repeat daily, weekly or monthly. Each user picks how to be alerted:
  - board pop-up;
  - notification bell;
  - both.
- **Browser push notifications**, even with the browser closed, through the official phpBB extension *Browser Push Notifications*.
- **Sharing** with users and groups; whoever receives a shared note can only read it.
- Search, show all / hide all, duplicate, keyboard shortcut **Alt + Shift + N**.
- Full **UCP**: list of all your notes and preferences.
- Full **ACP**:
  - settings;
  - statistics and the list of every note, with filters and bulk actions;
  - maintenance tools with a real progress bar;
  - automatic cleanup and CSV export;
  - a **Check-up** page that tests the extension.
- Smartphone mode: full, bar only, or disabled.
- All deletions use phpBB's own AJAX confirm box; every text comes from the language files.
- Works with prosilver and with prosilver-based styles. Includes a specific adaptation for the **ForumUS** style.

---

## Installation

1. Download the extension and copy it to `ext/salvocortesiano/stickynotes/` (the folder must contain `composer.json`).
2. **ACP → Customise → Manage extensions → Sticky Notes → Enable**.
3. **ACP → Extensions → Sticky Notes → Check-up**: every check should be green.
4. Purge the cache (**ACP → General → Purge the cache**) if the new style files do not show up.

On installation the user permissions are added automatically to the **Standard Features** and **All Features** user roles. The administrative permission goes to the **Standard Admin** and **Full Admin** roles.

### Updating

1. Disable the extension.
2. Replace the files.
3. Enable it again.
4. Purge the board cache and your browser cache (Ctrl + F5).

No data is lost.

### Uninstalling

**Disable**, then **Delete data**. This removes:
- the tables;
- the columns added to the users table;
- the configuration and permissions;
- the ACP/UCP modules and the notifications.

---

## Using the notes

### The pop-up bar

- A yellow button with a pushpin appears in a corner of the screen.
- Its red number shows how many **active** notes are hidden in the bar. Notes whose alert has already expired are not counted; they get a red bell in the list.
- Clicking the button opens a panel with:
  - **New**, a search field, and **Show all on the board** / **Hide all in the bar**;
  - the **Mine** and **Shared with me** tabs;
  - a counter (e.g. "5 of 30 notes") and the **Manage all** link to the UCP.
- Clicking a note in the list brings it to the front; a hidden note is put back on the board. The eye icon hides or shows it.
- The browser remembers whether the bar is open or closed. **Esc** closes the panel.

### Creating and editing

- **New** (or **Alt + Shift + N**) creates a note and opens the editor at once.
- The editor has a title, the text and the format (**Plain text** or **BBCode**). With BBCode a small toolbar appears.
- A counter shows the characters left.
- **Save** or **Ctrl + Enter** saves. **Esc** or **Cancel** discard the changes.
- If you open another function from the ⋮ menu while writing (alert, sharing, snooze, colour…), what you wrote is saved automatically and never lost.
- **Double click the title** to rename the note in place (Enter saves, Esc cancels).
- **Double click the text** to open the editor.

### Moving and resizing

- Drag a note by its header. This works with mouse, pen and touch.
- The folded corner at the bottom right resizes it.
- Position and size are stored as a percentage of the screen, so the note keeps its place on screens of any size and never leaves the screen.
- Notes and the bar always stay above every element of the board, fixed headers included. Only phpBB's confirm and alert boxes stay above them.

### Bin while dragging

- As soon as you start dragging one of your notes, a **transparent bin** (just the icon, no box) pops up above or beside the bar button, well apart from it. The administrator chooses the position. With the bar at the top, the "above" bin appears below the button.
- When the note is over the bin, the bin turns **full**: crumpled paper, red glow and "Release to delete the note". The note shrinks.
- Releasing the note on the bin opens phpBB's AJAX confirm box:
  - **Yes**: the note flies into the bin and is deleted. The bin is empty again and disappears after about 2.5 seconds.
  - **No** (or Esc, or closing the box): the note flies back exactly where it was before the drag, and its saved position does not change. The bin empties and disappears after about 2.5 seconds.
- Releasing the note anywhere else hides the bin at once and saves the new position.
- The bin is not shown in these cases:
  - shared (read-only) notes;
  - locked notes;
  - the smartphone "bar only" mode.

  It works with mouse, pen and touch.

### Minimize to a bar

- The **˄** button in the header, or **Minimize to a bar** in the menu, leaves only the title bar on the board. It shows the pushpin, the title and the buttons.
- The minimized note can still be dragged, and it stays minimized on every page until you expand it with **˅**.

### Snooze

- From the ⋮ menu choose **Snooze**. The options are:
  - *For 30 minutes*, *For 1 hour*, *For 4 hours*;
  - *Until tomorrow morning* (8:00), *For one week*;
  - *Pick date and time…*.
- Each option shows the time the note will come back.
- The note leaves the board. In the bar it shows a clock with its return time.
- It **comes back by itself**: on the next page you open, or at the exact moment if the page is already open, with a short flash.
- To bring it back earlier, use any of these:
  - the eye icon in the bar;
  - **Show now**;
  - **Show all on the board**.
- Snoozing is limited to one year; past dates are rejected.

### The ⋮ menu

| Item | What it does |
|---|---|
| Colours | Yellow, pink, blue, green, orange, lilac, white |
| Edit | Opens the editor |
| On every page / Only on this page | Where the note appears |
| Due-date alert | Date, time and repetition of the alert |
| Share | Share with users and groups |
| Snooze | Hide the note for a chosen time |
| Minimize to a bar / Expand | Keep only the title bar visible |
| Duplicate | Creates a copy placed beside the original |
| Lock / Unlock position | Prevents moving or resizing by mistake |
| Delete | Asks for confirmation with phpBB's AJAX confirm box |

The **−** button in the header hides the note in the bar.

### Where a note appears

- **On every page**: visible everywhere on the board.
- **Only on this page**: visible only on the page where it was set. The page can be:
  - a topic (`viewtopic.php?t=…`, also when it is opened through a link to a single post);
  - a forum (`viewforum.php?f=…`);
  - any other page (index, member list, extension pages…).

### Due-date alerts

- Set from the menu:
  - date and time, in the time zone of your device;
  - repetition: **Never**, **Every day**, **Every week** or **Every month**.
- A monthly alert keeps its day. An alert on the 31st arrives on the last day of shorter months and goes back to the 31st when possible.
- The note shows a line with the alert date at the bottom. When the alert expires the line turns red.
- The alert arrives **at once**:
  - if a board page is open, it appears at that moment without reloading: the bell counter updates and the note flashes;
  - if the user is not on the board, the alert arrives on the first page they open;
  - for users who do not visit the board, phpBB's cron creates the bell notification.
- Each user chooses in the UCP how to be alerted:
  - **Board pop-up window**: phpBB's AJAX box with the title and text of the expired notes;
  - **Notification in the bell**: a phpBB notification that opens the note's page and highlights it;
  - **Both**.
- Recurring alerts move to their next date by themselves.
- The bell notification shows the note title and the beginning of its text.

### Browser push notifications (even with the browser closed)

If the official extension [phpBB Browser Push Notifications](https://www.phpbb.com/customise/db/extension/webpushnotifications/) (`phpbb/webpushnotifications`) is installed and enabled, with its VAPID keys configured, alerts also arrive as operating-system notifications (Windows, Android, macOS…). They arrive **even with the board or the browser closed**, as long as the browser keeps running in the background, as Chrome and Edge do.

- **UCP → Sticky notes → Notes preferences** gets the option **Browser push notifications**.
  - Setting it to **Yes** enables the push method for the notification types "One of my sticky notes reached its due date" and "Someone shares a sticky note with me".
  - The same switches are also in UCP → Board preferences → Edit notification options.
- Below the option the UCP shows on how many devices push notifications are active. If there are none, a link leads to the page where you can enable them for the current browser.
- The push notification shows the note title and the beginning of its text. Clicking it opens the board with the note highlighted.
- The alert mode must be **Notification in the bell** or **Both**: the push travels with the bell notification.
- With the board closed, phpBB's cron creates the notification:
  - with a system cron every 5 minutes, the push arrives within 5 minutes of the due time;
  - for the exact time, run the cron **every minute** (`* * * * *`);
  - with a board page open, the alert arrives at once.
- If the push extension is not installed or not enabled, the option is not shown and the bell and pop-up keep working. The ACP Check-up tells you how to set it up.

### Sharing

- In **Share** you enter usernames separated by commas and/or tick the groups you belong to (guests and bots excluded).
- Recipients find the note under **Shared with me**, **read only**. New recipients also get a bell notification.
- If a username does not exist, a warning lists the names that were not found.

### User Control Panel (UCP → Sticky notes)

- **My notes**: the full list of your notes, including those bound to other pages. For each note it shows:
  - colour, preview and scope;
  - alert, sharing and last change.

  The title opens the note on its page. You can delete one note (AJAX confirm) or several with the check boxes. The notes shared with you are listed below.
- **Notes preferences**:
  - show or hide notes on the board;
  - bar position, if the administrator allows it;
  - how to be alerted;
  - browser push notifications, when available.

### Smartphones

Depending on the ACP setting:
- full functionality;
- **bar only**: notes open as a window on the screen, without dragging;
- disabled.

### ForumUS style

Besides prosilver, the extension is adapted to the **ForumUS** style:
- the bar and panels use the style's colours and the Roboto font;
- at the due time the ForumUS bell counter and its notification dropdown update without reloading the page;
- notes and the bar always stay above the fixed header;
- the bar button avoids the style's fixed elements in all four positions: sticky header, profile menu, bell, logo and "back to top" button.

---

## Administration

**ACP → Extensions → Sticky Notes.** Every page shows badges at the top (extension version, PHP version, phpBB version, GPL license, extension status) and the credits at the bottom.

### Settings

| Option | Description |
|---|---|
| Enable sticky notes | Master switch (data stays saved) |
| Maximum notes per user | 0 = unlimited |
| Maximum text length | Characters per note, 0 = unlimited |
| Default colour of new notes | Colour of new notes |
| Slight random tilt | "Pin board" effect |
| Default bar position | Top/bottom, left/right |
| Users can choose the position | Enables the choice in the UCP |
| Bin while dragging | Yes/No: show the bin while a note is dragged |
| Bin position | Above or beside the bar button |
| Behaviour on smartphones | Full / Bar only / Disabled |
| Allow BBCode | Enables the BBCode format |
| Allow due-date alerts | Enables alerts and notifications |
| Allow sharing | Enables sharing |

### Management and statistics

- **Statistics**: each box is clickable and opens the list already filtered. They cover:
  - totals: notes, users using them;
  - alerts: scheduled, **expired** (red box when there are any), recurring;
  - status: shared, hidden in the bar, snoozed, minimized, locked;
  - content: using BBCode, on one page only, empty;
  - activity: not changed for 30 days, created in the last 7 and 30 days.
- **Notes by colour**: distribution bar with percentages.
- **All notes**: every note of every user, 25 per page, with:
  - filters by:
    - user, with "Find a member";
    - text or title, colour, scope;
    - status: scheduled, expired, recurring or no alert; hidden, snoozed, minimized, locked, shared, empty, using BBCode;
  - sorting by last change, creation date, alert date, title or user;
  - colour, title, text preview, status tags, owner, where it appears, alert state and dates for every note;
  - **View**: shows the full note in phpBB's dialog. The access is written to the admin log.
  - **Delete** a single note.
  - **Bulk actions** on the selected notes:
    - delete;
    - remove the alert;
    - bring back in sight (show, wake up, expand);
    - reposition and resize.

    All of them ask for confirmation with phpBB's AJAX box.
- **Users with the most notes**: total, expired alerts, last activity and a link to their notes.
- When there are expired alerts or empty notes, a hint links to the Maintenance tab.

### Maintenance

Each tool first counts the notes involved and states the number in the confirmation (e.g. "3 notes will be affected"). When there is nothing to do it says so and does nothing. The work runs in blocks of 250 with a **progress bar showing the real percentage**, and it is written to the admin log.

| Tool | What it does |
|---|---|
| Delete notes with an expired alert | One-time alerts already past, optionally expired for at least N days (0 = all) |
| Remove only the expired alerts | Keeps the note, removes the alert and its notification |
| Delete inactive notes | Not changed for at least N days |
| Delete empty notes | No title and no text |
| Delete the notes of a user | With "Find a member" |
| Clean orphan data | Notes of deleted users and invalid shares |
| Bring every note back in sight | Tidy grid, standard size, expanded, snoozes cancelled |
| Delete ALL notes | Empties everything (export the CSV first) |

> A **recurring** alert never expires: at its due time it moves to the next date. This is why the tools for expired alerts never touch it.

**Automatic cleanup**: once a day phpBB's cron can delete notes whose alert expired more than N days ago, and/or notes not changed for N days (0 = disabled).
- **Save settings** stores the values without reloading the page and updates the status box at once.
- **Run now** saves the values, counts the notes involved, asks for confirmation and runs the cleanup at once with the progress bar.
- The page shows the last run and how many notes it deleted.
- The Check-up warns if the cron has not run for more than 3 days.

**Export**: downloads every note as CSV, useful as a backup. The file is UTF-8 with ";" as separator and opens directly in Excel or LibreOffice.

### Check-up

Checks and reports each item with a status: ✔ OK, ⚠ Warning, ✘ Error or ℹ Tip.

- **Environment**: PHP and phpBB versions; extension enabled.
- **Database**: tables and columns exist; a real write/read/delete test.
- **Configuration**: configuration values.
- **Permissions**:
  - the permissions exist, with how many groups and users have them, directly or through a role;
  - founders count for `a_stickynotes`, since in phpBB they always have every admin permission;
  - `u_stickynotes_nolimit` is optional and never raises a warning.
- **Web Push extension** (`phpbb/webpushnotifications`), four cases:

  | Case | Status | What it offers |
  |---|---|---|
  | Not on the server | ℹ Tip | A link to download it from phpBB.com |
  | Uploaded but not enabled | ℹ Tip | An **Enable it now** link to the extension manager |
  | Enabled, but Web Push switched off or without VAPID keys | ⚠ Warning | A link to the Web Push settings |
  | Active | ✔ OK | The number of users who receive alerts by push |

  Tips are neither errors nor warnings: the summary counts them separately.
- **Notification types**: registered and enabled. A type that is not registered yet is registered at once.
- **Cron**: last run of the alerts cron, and late alerts.
- **Front end**:
  - AJAX route, BBCode parsing, safe JSON encoding;
  - CSS, JavaScript, fonts, templates and language files present.
- **Data quality**: English and Italian language files consistent; orphan notes.

---

## Permissions

ACP → Permissions → Group permissions (or User permissions) → **User permissions** → **Sticky notes** tab.

| Permission | Meaning |
|---|---|
| `u_stickynotes_use` | Can use sticky notes |
| `u_stickynotes_bbcode` | Can use BBCode in sticky notes |
| `u_stickynotes_share` | Can share sticky notes with other users and groups |
| `u_stickynotes_reminder` | Can set due-date alerts on sticky notes |
| `u_stickynotes_nolimit` | No limit on the number of sticky notes |
| `a_stickynotes` | Can manage the Sticky Notes extension |

> `a_stickynotes` is an **administrator** permission: you find it in *Group permissions → Admin permissions → Misc* tab. Founders always have it, even when it is not assigned.

---

## Technical details

### Database

- `phpbb_stickynotes`: one row per note. It stores:
  - owner, title, and text with the BBCode UID, bitfield and flags;
  - format, colour, scope and page;
  - X/Y position in hundredths of a percent, width, height, z-order, tilt;
  - hidden, locked, minimized and snoozed-until flags;
  - alert data and dates.
- `phpbb_stickynotes_shares`: shares (note, user/group type, recipient).
- Columns added to `phpbb_users`: `user_stickynotes_enabled`, `user_stickynotes_tray`, `user_stickynotes_notify`.

### Structure

```
salvocortesiano/stickynotes/
├── composer.json, ext.php, README.md, GUIDA.md
├── config/            services.yml, routing.yml, tables.yml
├── migrations/        v100_install.php … v1014_fix.php
├── service/           note_manager.php (core logic), maintenance.php (maintenance and cleanup), webpush.php (Web Push integration)
├── controller/        main_controller.php (AJAX), acp_controller.php, acp_manage_controller.php, ucp_controller.php
├── event/             main_listener.php
├── notification/type/ reminder.php, shared.php
├── cron/task/         reminders.php (alerts), cleanup.php (automatic cleanup)
├── acp/ ucp/          modules
├── adm/style/         ACP templates, js/stickynotes_acp.js
├── language/en|it/    common, info_acp, info_ucp, permissions
└── styles/all/        template (events, UCP, JS) and theme (CSS, Caveat font, OFL)
```

### How it works

- **Page load**: on `core.page_header` the extension prepares one JSON block with:
  - the notes of the page and the shared notes;
  - the expired alerts;
  - every interface text, taken from the language files.

  No extra AJAX request is made on page load.
- **Saving**: each action calls `app.php/stickynotes/ajax/{action}` by POST with an anti-CSRF token.
  - Actions: `create`, `update`, `props`, `toggle_all`, `duplicate`, `delete`, `reminder`, `share`, `dismiss`, `due`, `shared_get`.
  - Every request checks permissions, note ownership and limits.
- **Text**: processed with phpBB's own functions (`generate_text_for_storage` / `generate_text_for_display`). BBCode, links and smilies behave as in posts, and the content is safe from XSS.
- **Alerts**: they are processed in three places.
  - On each page a user opens, their expired alerts are processed on `core.page_header`, before phpBB counts the notifications, so the bell is already up to date.
  - While a page is open, a browser timer calls the server at the due time (action `due`), shows the dialog and updates the bell.
  - phpBB's cron, at most once a minute, covers users who are not on the board.
- **Web Push**: the extension sends standard phpBB notifications. When the push method is enabled for its two notification types, `phpbb/webpushnotifications` delivers them to the user's devices.
- **Confirmations**: every deletion uses phpBB's AJAX confirm box, never the browser's `confirm()`.
- **Cleanup on delete**: when a user is deleted, their notes and the shares to them are deleted. When a group is deleted, the shares to that group are deleted.
- **Printing**: the bar and the notes do not appear when a page is printed.

### Font

The handwritten font is **Caveat** (SIL Open Font License), shipped in `styles/all/theme/fonts/` and hosted locally: no request is made to Google Fonts.

---

## FAQ

**The bar does not appear.** Check:
- the extension is enabled in its settings;
- the group has the `u_stickynotes_use` permission;
- "Show notes on the board" is enabled in the UCP;
- the cache is purged.

The Check-up reports most problems.

**Bell notifications arrive late.**
- For users who are on the board they arrive at once (since 1.0.8).
- For users who are not online, phpBB's cron creates the notification. The web cron runs one task per page view. On a low-traffic board, enable the system cron (ACP → General → Server configuration → Server settings → "Run periodic tasks from operating system cron").
- The Check-up reports alerts that are more than 15 minutes late.

**Browser push notifications do not arrive.** Check:
- the "Browser Push Notifications" extension is enabled with VAPID keys (the Check-up shows it);
- "Browser push notifications" is set to Yes in the UCP;
- at least one device is registered (shown below the option);
- the alert mode is "Notification in the bell" or "Both";
- notifications are not blocked by the browser or the operating system.

With the board closed, the maximum delay is the interval of the system cron.

**I changed page and a note is gone.** It is probably set to "Only on this page". You find it in UCP → My notes, where the title opens the right page.

**Does it work with other styles?** Yes: the interface is drawn by the extension and works with prosilver and prosilver-based styles. ForumUS has a specific adaptation.

---

## Changelog

### 1.0.14 — 2026-10-10
- The bin is now **transparent**: only the icon and its label, with a soft halo for light and dark backgrounds (red when full).
- The bin sits farther from the bar button (34 px instead of 12), so it no longer looks overlapped, even when it grows while full.

### 1.0.13 — 2026-10-10
- **New**: a bin that appears while a note is dragged.
  - It shows above or beside the bar button, only during the drag, and turns full when the note is over it.
  - Releasing the note on it asks for confirmation with phpBB's AJAX box: Yes drops the note into the bin and deletes it; No flies it back to its place.
  - Afterwards the bin empties and disappears after about 2.5 seconds.
- ACP → Settings: new options "Bin while dragging" (Yes/No) and "Bin position" (above / beside).
- New migration `v1013_trash`.

### 1.0.12 — 2026-10-10
- **Fixed**: the number on the bar button also counted notes whose alert had already expired. It now counts only active notes and goes back to 0 at the due time, even without reloading. Expired notes have a red bell in the bar list.
- **Fixed**: the title and text of a note were lost when another function was opened after writing them: "Due-date alert", Share, Snooze, a colour change…. The text being edited is now saved automatically before the other function opens; only Cancel discards it.

### 1.0.11 — 2026-10-10
- **Fixed**: a note dragged to the top could end up under a fixed header with a higher z-index and could not be grabbed again. Notes, their menus and the bar now stay above every element of the board; only phpBB's confirm/alert boxes stay above them.

### 1.0.10 — 2026-10-10
- Check-up: the Web Push line now tells apart four cases:
  - extension missing;
  - uploaded but not enabled;
  - not configured;
  - active.

  For each case it says what to do, with a link: download from phpBB.com, **Enable it now**, or Web Push settings.
- New **ℹ Tip** status in the Check-up, counted separately in the summary.

### 1.0.9 — 2026-10-10
- **New**: browser push notifications through `phpbb/webpushnotifications`, even with the browser or the board closed. The push method is off by default for extension notification types; a UCP option now enables it and shows the registered devices.
- Notifications (bell and push) show the beginning of the note text instead of the date only.
- Check-up: new line with the state of the Web Push extension.
- **ForumUS style**:
  - adapted look;
  - bell and its dropdown updated live at the due time;
  - the bar button no longer covers logo, profile menu, bell or "back to top" in any of the four positions.
- Fixed a PHP notice (`user_options`) when the command-line cron prepared the notification text.

### 1.0.8 — 2026-10-10
- **Fixed**: no alert appeared at the due time, neither on screen nor in the bell.
  - With a page open nothing checked the due time. A browser timer now fires at the exact time: it shows phpBB's dialog, creates the notification, updates the bell without reloading and makes the note flash.
  - The bell notification depended only on phpBB's cron, which could be late. A user's alerts are now processed on each of their page views, before phpBB counts the notifications. The cron remains for users who are not online.
- The three UCP choices (pop-up, bell, both) and recurring alerts are respected.

### 1.0.7 — 2026-10-10
Fixes found while testing on a real phpBB 3.3.19 board:
- **Fixed**: saving a note with empty text showed an error even though the save worked. An empty text now skips the parser. Parser warnings (too many links, smilies…) become a clear message and the note is not saved.
- **Fixed**: double-click on the title to rename did not work in real browsers. Dragging now starts only when the pointer really moves.
- **Fixed**: phpBB's confirm and alert boxes were below the notes. They are now always on top.
- **Fixed**: the ⋮ menu and the editor of a note near the bottom of the screen went off screen. They now stay visible without changing the saved position.
- **Fixed**: a duplicate appeared almost on top of the original. It is now placed beside it.
- AJAX answers are read correctly even if another extension prints a debug notice before the JSON.

### 1.0.6 — 2026-10-10
- **Fixed**: the buttons in Maintenance and in Management and statistics did nothing, because jQuery is loaded at the end of the ACP page. All ACP JavaScript is now in `adm/style/js/stickynotes_acp.js`, included with `INCLUDEJS`.
- Each button reacts at once, and the progress box appears while counting, then shows the real percentage and the result.
- Automatic cleanup: **Save settings** without reloading, and a new **Run now** button with a progress bar.

### 1.0.5 — 2026-10-10
- **Fixed**: in the admin log the extension modules appeared with their raw language key. Existing log rows are now translated when shown, and the migrations load the module names before adding or removing modules.
- Full check of every language string in English and Italian.

### 1.0.4 — 2026-10-10
- **Management and statistics** rewritten: 16 clickable statistics, distribution by colour, the full list of notes with filters, sorting, pagination, preview and bulk actions.
- New **Maintenance** tab: 8 tools with a preliminary count, AJAX confirmation and a real progress bar.
- Daily **automatic cleanup** through the cron.
- **CSV export**.
- Every operation is written to the admin log.

### 1.0.3 — 2026-10-10
- **Fixed**: the Check-up reported `a_stickynotes` as not assigned. It now counts groups and users, through roles too, and founders.
- **Fixed**: the Check-up summary was hard to read; it now has its own coloured box.
- The "shared" notification type is registered at once.

### 1.0.2 — 2026-10-10
- **Fixed**: deleting from the ⋮ menu did not work (the confirm box had no Yes/No buttons).
- **Fixed**: the same problem in the ACP.
- **Fixed**: the red pushpin of minimized notes was cut in half.

### 1.0.1 — 2026-10-09
- **Snooze** and **Minimize to a bar**, with the snooze return time shown in the bar and the matching labels in the UCP and statistics in the ACP.

### 1.0.0 — 2026-10-09
First release. It includes:
- drag-and-drop, resizable notes, with plain text or BBCode;
- the pop-up bar in 4 corners (ACP and UCP);
- 7 colours; global or per-page scope;
- due-date alerts with repetition (pop-up or bell);
- sharing with users and groups;
- duplicate, lock, search, show/hide all, keyboard shortcut;
- smartphone mode, full UCP and ACP with a Check-up;
- English and Italian.

---

## License

[GNU General Public License v2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

The Caveat font is distributed under the SIL Open Font License 1.1.

Developed by **Salvo Cortesiano** — [netshadows.de/ombra](https://netshadows.de/ombra)

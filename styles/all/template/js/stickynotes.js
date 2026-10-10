/**
 * Sticky Notes for phpBB
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
(function ($, window, document) {
	'use strict';

	var dataEl = document.getElementById('sn-data');
	if (!dataEl || typeof $ === 'undefined') {
		return;
	}

	var D;
	try {
		D = JSON.parse(dataEl.textContent);
	} catch (e) {
		return;
	}

	var L = D.lang || {};
	var isMobile = window.matchMedia && window.matchMedia('(max-width: 700px)').matches;

	if (isMobile && D.mobile === 2) {
		return;
	}

	var trayOnly = isMobile && D.mobile === 1;
	var STORE_KEY = 'sn_tray_open';
	var REPEATS = ['REPEAT_NONE', 'REPEAT_DAILY', 'REPEAT_WEEKLY', 'REPEAT_MONTHLY'];

	var state = {
		notes: {},		// own notes by id
		order: [],		// own note ids
		shared: {},		// shared notes by id
		sharedOpen: {},	// shared notes currently shown
		tab: 'mine',
		search: '',
		maxZ: 1,
		open: false
	};

	/* ------------------------------------------------------------------
	 * Icons (inline SVG, stroke based)
	 * ------------------------------------------------------------------ */
	var ICONS = {
		pin: '<path d="M12 17v5"/><path d="M9 10.76V6h6v4.76l2 2.24v2H7v-2z"/><path d="M8 2h8"/>',
		plus: '<path d="M12 5v14M5 12h14"/>',
		close: '<path d="M18 6 6 18M6 6l12 12"/>',
		edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
		dots: '<circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>',
		minus: '<path d="M5 12h14"/>',
		clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		chevUp: '<path d="m18 15-6-6-6 6"/>',
		chevDown: '<path d="m6 9 6 6 6-6"/>',
		back: '<path d="M19 12H5M12 19l-7-7 7-7"/>',
		eye: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
		eyeoff: '<path d="M17.9 17.9A10.1 10.1 0 0 1 12 20C5 20 1 12 1 12a18.5 18.5 0 0 1 5.1-5.9"/><path d="M9.9 4.2A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.2 3.2"/><path d="M1 1l22 22"/>',
		bell: '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
		share: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
		lock: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
		unlock: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
		page: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
		globe: '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
		copy: '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
		trash: '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
		palette: '<circle cx="13.5" cy="6.5" r="1"/><circle cx="17.5" cy="10.5" r="1"/><circle cx="8.5" cy="7.5" r="1"/><circle cx="6.5" cy="12.5" r="1"/><path d="M12 2a10 10 0 0 0 0 20c1 0 1.5-.8 1.5-1.5 0-.4-.1-.7-.4-1-.3-.3-.4-.6-.4-1 0-.8.7-1.5 1.5-1.5H16a6 6 0 0 0 6-6c0-4.9-4.5-9-10-9z"/>',
		user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		gear: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>'
	};

	function icon(name) {
		return '<svg class="sn-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + (ICONS[name] || '') + '</svg>';
	}

	/* ------------------------------------------------------------------
	 * Small helpers
	 * ------------------------------------------------------------------ */
	function el(tag, cls, html) {
		var node = document.createElement(tag);
		if (cls) {
			node.className = cls;
		}
		if (html !== undefined) {
			node.innerHTML = html;
		}
		return node;
	}

	function btn(cls, iconName, title, label) {
		var b = el('button', 'sn-btn ' + (cls || ''));
		b.type = 'button';
		b.innerHTML = (iconName ? icon(iconName) : '') + (label ? '<span>' + escapeHtml(label) + '</span>' : '');
		if (title) {
			b.title = title;
			b.setAttribute('aria-label', title);
		}
		return b;
	}

	function escapeHtml(str) {
		return String(str === undefined || str === null ? '' : str)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	function lang(key) {
		return L[key] !== undefined ? L[key] : key;
	}

	function langf(key) {
		var str = lang(key), args = Array.prototype.slice.call(arguments, 1), i = 0;
		return str.replace(/%(\d+\$)?[sd]/g, function () {
			return args[i++] !== undefined ? args[i - 1] : '';
		});
	}

	function storeGet(key) {
		try {
			return window.localStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	function storeSet(key, value) {
		try {
			window.localStorage.setItem(key, value);
		} catch (e) {
			// Storage not available: ignore
		}
	}

	function formatDate(ts) {
		if (!ts) {
			return '';
		}
		var d = new Date(ts * 1000);
		try {
			return d.toLocaleString(document.documentElement.lang || undefined, {
				day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
			});
		} catch (e) {
			return d.toLocaleString();
		}
	}

	function toLocalInput(ts) {
		var d = ts ? new Date(ts * 1000) : new Date(Date.now() + 3600000);
		function p(n) { return (n < 10 ? '0' : '') + n; }
		return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
	}

	function showError(msg) {
		if (window.phpbb && phpbb.alert) {
			phpbb.alert(lang('ERROR'), escapeHtml(msg || lang('ERR_GENERIC')));
		}
	}

	/**
	 * phpBB AJAX confirm box. phpbb.confirm() does not create the buttons:
	 * the message must contain them (name="confirm" / name="cancel"),
	 * exactly like phpBB's own confirm_body.html.
	 */
	function confirmBox(message, onYes, onNo) {
		if (!window.phpbb || !phpbb.confirm) {
			if (onNo) {
				onNo();
			}
			return;
		}
		var html = '<h3>' + escapeHtml(lang('TITLE')) + '</h3>' +
			'<p>' + escapeHtml(message) + '</p>' +
			'<fieldset class="submit-buttons">' +
			'<input type="button" name="confirm" value="' + escapeHtml(lang('YES')) + '" class="button2" />&nbsp;' +
			'<input type="button" name="cancel" value="' + escapeHtml(lang('NO')) + '" class="button2" />' +
			'</fieldset>';

		var decided = false, watch = null;
		var $box = phpbb.confirm(html, function (confirmed) {
			decided = true;
			if (watch) {
				clearInterval(watch);
			}
			if (confirmed) {
				onYes();
			} else if (onNo) {
				onNo();
			}
		});
		// closed with the X or by clicking outside: phpBB does not call back
		if (onNo && $box && $box.length) {
			watch = setInterval(function () {
				if (!decided && !$box.is(':visible')) {
					clearInterval(watch);
					decided = true;
					onNo();
				}
			}, 250);
		}
	}

	/**
	 * Read the JSON answer even if the server printed something before it
	 * (e.g. a "[phpBB Debug]" notice of another extension on boards in debug mode)
	 */
	function parseResponse(text) {
		try {
			return JSON.parse(text);
		} catch (e) {
			// something printed before and/or after the JSON (notices of other code)
			var str = String(text), start = str.indexOf('{"success"');
			if (start > -1) {
				var end = str.lastIndexOf('}'), tries = 0;
				while (end > start && tries < 200) {
					try {
						return JSON.parse(str.slice(start, end + 1));
					} catch (e2) {
						end = str.lastIndexOf('}', end - 1);
						tries++;
					}
				}
			}
			return $.Deferred().reject({ responseJSON: null }).promise();
		}
	}

	function ajax(action, data, done, failed) {
		// any other request waits until the text being edited has been saved
		if (action !== 'update' && draftsPending > 0) {
			afterDrafts.push(function () { ajax(action, data, done, failed); });
			return;
		}
		data = data || {};
		data.hash = D.hash;

		$.ajax({
			url: D.ajax.replace('__action__', action),
			type: 'POST',
			data: data,
			dataType: 'text',
			cache: false
		}).then(parseResponse).done(function (r) {
			if (r && r.success) {
				if (done) {
					done(r);
				}
			} else {
				showError(r && r.error);
				if (failed) {
					failed();
				}
			}
		}).fail(function (xhr) {
			showError(xhr && xhr.responseJSON && xhr.responseJSON.error);
			if (failed) {
				failed();
			}
		});
	}

	function isSnoozed(n) {
		return !!(n.snooze && n.snooze * 1000 > Date.now());
	}

	function isVisible(n) {
		return !trayOnly && !n.hidden && !isSnoozed(n);
	}

	function vw() {
		return document.documentElement.clientWidth || window.innerWidth;
	}

	function vh() {
		return document.documentElement.clientHeight || window.innerHeight;
	}

	/* ------------------------------------------------------------------
	 * DOM skeleton: layer for the notes + tray
	 * ------------------------------------------------------------------ */
	var root = el('div', 'sn-root' + (D.style ? ' sn-style-' + D.style : ''));
	root.id = 'sn-root';
	var layer = el('div', 'sn-layer');
	var tray = el('div', 'sn-tray sn-pos-' + D.tray);
	var toggle = btn('sn-tray-toggle', 'pin', lang('TRAY_OPEN'));
	var badge = el('span', 'sn-badge');
	toggle.appendChild(badge);

	var panel = el('div', 'sn-panel');
	panel.setAttribute('role', 'dialog');
	panel.setAttribute('aria-label', lang('TITLE'));

	var head = el('div', 'sn-panel-head');
	head.appendChild(el('span', 'sn-panel-title', icon('pin') + escapeHtml(lang('TITLE'))));
	var newBtn = btn('sn-btn-primary', 'plus', lang('NEW') + ' (' + lang('SHORTCUT') + ')', lang('NEW'));
	var closeBtn = btn('sn-btn-ghost', 'close', lang('TRAY_CLOSE'));
	head.appendChild(newBtn);
	head.appendChild(closeBtn);

	var tools = el('div', 'sn-panel-tools');
	var search = el('input', 'sn-search');
	search.type = 'search';
	search.placeholder = lang('SEARCH');
	var showAllBtn = btn('sn-btn-ghost', 'eye', lang('SHOW_ALL'));
	var hideAllBtn = btn('sn-btn-ghost', 'eyeoff', lang('HIDE_ALL'));
	tools.appendChild(search);
	if (!trayOnly) {
		tools.appendChild(showAllBtn);
		tools.appendChild(hideAllBtn);
	}

	var tabs = el('div', 'sn-tabs');
	var tabMine = btn('sn-tab sn-tab-active', '', '', lang('TAB_MINE'));
	var tabShared = btn('sn-tab', '', '', lang('TAB_SHARED'));
	tabs.appendChild(tabMine);
	tabs.appendChild(tabShared);

	var list = el('ul', 'sn-list');

	var foot = el('div', 'sn-panel-foot');
	var counter = el('span', 'sn-counter');
	var manage = el('a', 'sn-manage', icon('gear') + escapeHtml(lang('MANAGE')));
	manage.href = D.ucp;
	foot.appendChild(counter);
	foot.appendChild(manage);

	panel.appendChild(head);
	panel.appendChild(tools);
	panel.appendChild(tabs);
	panel.appendChild(list);
	panel.appendChild(foot);
	tray.appendChild(toggle);
	tray.appendChild(panel);
	root.appendChild(layer);
	root.appendChild(tray);
	document.body.appendChild(root);

	/* ------------------------------------------------------------------
	 * Tray
	 * ------------------------------------------------------------------ */
	function setTray(open) {
		state.open = open;
		tray.classList.toggle('sn-open', open);
		toggle.title = open ? lang('TRAY_CLOSE') : lang('TRAY_OPEN');
		storeSet(STORE_KEY, open ? '1' : '0');
		if (open) {
			renderList();
		}
	}

	/* ------------------------------------------------------------------
	 * Keep the tray button away from the board's own controls
	 * (header links, bell, profile menu, fixed/sticky navbar, "back to top"...)
	 * Works with any style: it looks at what is really under the button.
	 * ------------------------------------------------------------------ */
	var TRAY_MARGIN = 12;
	var trayVertical = D.tray.charAt(0) === 't' ? 'top' : 'bottom';

	function isOurs(node) {
		return !!(node && node.closest && node.closest('#sn-root'));
	}

	/**
	 * @param {boolean} fixedOnly  true: only fixed/sticky things of the style
	 *                             (sticky header, "back to top", cookie bar…);
	 *                             false: also the clickable header controls
	 */
	function isObstacle(node, fixedOnly) {
		var clickable = false;
		for (var depth = 0; node && node !== document.body && node !== document.documentElement && depth < 8; depth++, node = node.parentElement) {
			var cs = window.getComputedStyle(node);
			if (cs.visibility === 'hidden' || parseFloat(cs.opacity) < 0.1) {
				return false;
			}
			if (cs.position === 'fixed' || cs.position === 'sticky') {
				return true;
			}
			if (!clickable && node.matches && node.matches('a[href], button, input, select, textarea, label, [role="button"], [role="menuitem"], [onclick], .back-to-top, .dropdown-trigger')) {
				clickable = true;
			}
		}
		return !fixedOnly && clickable;
	}

	function trayBlocked(fixedOnly) {
		if (typeof document.elementsFromPoint !== 'function' && typeof document.elementFromPoint !== 'function') {
			return false;
		}
		var r = toggle.getBoundingClientRect();
		var pad = 4;
		var pts = [
			[r.left - pad, r.top - pad], [r.right + pad, r.top - pad], [r.left - pad, r.bottom + pad],
			[r.right + pad, r.bottom + pad], [r.left + r.width / 2, r.top + r.height / 2]
		];
		for (var i = 0; i < pts.length; i++) {
			var x = Math.max(0, Math.min(vw() - 1, pts[i][0])), y = Math.max(0, Math.min(vh() - 1, pts[i][1]));
			var stack = document.elementsFromPoint ? document.elementsFromPoint(x, y) : [document.elementFromPoint(x, y)];
			for (var j = 0; j < stack.length; j++) {
				if (!stack[j] || isOurs(stack[j])) {
					continue;
				}
				if (isObstacle(stack[j], fixedOnly)) {
					return true;
				}
				break; // the first element that is not ours decides
			}
		}
		return false;
	}

	function findOffset(start, fixedOnly) {
		var max = Math.round(vh() * 0.45);
		var offset = start;
		tray.style[trayVertical] = offset + 'px';
		while (trayBlocked(fixedOnly) && offset < max) {
			offset += 8;
			tray.style[trayVertical] = offset + 'px';
		}
		return offset < max ? offset : start;
	}

	// Top corners: clear the header controls (logo, profile, bell) once, at the
	// top of the page, so the button does not jump while scrolling
	var baseOffset = TRAY_MARGIN;
	function computeBaseOffset() {
		if (trayVertical === 'top' && (window.pageYOffset || document.documentElement.scrollTop || 0) < 40) {
			baseOffset = findOffset(TRAY_MARGIN, false);
		}
	}

	function placeTray() {
		// then only fixed/sticky things (sticky header, back to top…) move it further
		var offset = findOffset(baseOffset, true);
		tray.style[trayVertical] = offset + 'px';
	}

	var placeTimer = null;
	function schedulePlaceTray() {
		if (placeTimer) {
			return;
		}
		placeTimer = setTimeout(function () {
			placeTimer = null;
			placeTray();
		}, 150);
	}

	window.addEventListener('scroll', schedulePlaceTray, { passive: true });
	window.addEventListener('resize', function () {
		computeBaseOffset();
		schedulePlaceTray();
	});
	window.addEventListener('load', schedulePlaceTray);

	/** The reminder of the note is past (a recurring one moves on to its next date) */
	function isExpired(n) {
		return !!(n && n.reminder && n.reminder * 1000 <= Date.now());
	}

	function updateCounter() {
		// only the active notes: an expired reminder no longer counts
		var count = 0;
		state.order.forEach(function (id) {
			var n = state.notes[id];
			if (!isExpired(n) && (trayOnly || !isVisible(n))) {
				count++;
			}
		});
		badge.textContent = count > 0 ? String(count) : '';
		badge.style.display = count > 0 ? '' : 'none';

		counter.textContent = D.maxNotes > 0 ? langf('COUNT_NOTES', D.total, D.maxNotes) : String(D.total);
	}

	function matchesSearch(n) {
		if (!state.search) {
			return true;
		}
		var q = state.search.toLowerCase();
		return (n.title || '').toLowerCase().indexOf(q) !== -1 || (n.text || '').toLowerCase().indexOf(q) !== -1 ||
			(n.html || '').replace(/<[^>]+>/g, ' ').toLowerCase().indexOf(q) !== -1;
	}

	function renderList() {
		list.innerHTML = '';
		var items = [];

		if (state.tab === 'mine') {
			state.order.forEach(function (id) {
				var n = state.notes[id];
				if (matchesSearch(n)) {
					items.push(n);
				}
			});
		} else {
			Object.keys(state.shared).forEach(function (id) {
				if (matchesSearch(state.shared[id])) {
					items.push(state.shared[id]);
				}
			});
		}

		if (!items.length) {
			list.appendChild(el('li', 'sn-empty', escapeHtml(state.tab === 'mine' ? lang('EMPTY') : lang('EMPTY_SHARED'))));
			return;
		}

		items.forEach(function (n) {
			var away = n.own && (n.hidden || isSnoozed(n));
			var li = el('li', 'sn-item sn-c-' + n.color + (away ? ' sn-item-hidden' : ''));
			var main = el('button', 'sn-item-main');
			main.type = 'button';
			var meta = '';
			if (n.own) {
				meta += n.scope === 'page' ? icon('page') : icon('globe');
				if (n.reminder) {
					meta += isExpired(n)
						? '<span class="sn-item-expired" title="' + escapeHtml(langf('REMINDER_AT', formatDate(n.reminder)) + ' · ' + lang('REMINDER_EXPIRED')) + '">' + icon('bell') + '</span>'
						: icon('bell');
				}
				if (n.shares && n.shares.length) {
					meta += icon('share');
				}
				if (n.locked) {
					meta += icon('lock');
				}
				if (n.minimized) {
					meta += icon('chevUp');
				}
				if (isSnoozed(n)) {
					meta += '<span class="sn-item-snooze" title="' + escapeHtml(langf('SNOOZE_UNTIL', formatDate(n.snooze))) + '">' +
						icon('clock') + escapeHtml(formatShort(n.snooze)) + '</span>';
				}
			} else {
				meta += icon('user') + '<em>' + escapeHtml(n.ownerName) + '</em>';
			}
			main.innerHTML = '<span class="sn-dot"></span><span class="sn-item-title">' +
				escapeHtml(n.title || lang('UNTITLED')) + '</span><span class="sn-item-meta">' + meta + '</span>';
			main.title = lang('FOCUS');
			main.addEventListener('click', function () {
				if (n.own) {
					openOwn(n.id);
				} else {
					openShared(n.id);
				}
			});
			li.appendChild(main);

			if (n.own && !trayOnly) {
				var eye = btn('sn-btn-ghost sn-item-eye', away ? 'eye' : 'eyeoff',
					isSnoozed(n) ? lang('SNOOZE_WAKE') : (away ? lang('SHOW') : lang('HIDE')));
				eye.addEventListener('click', function (e) {
					e.stopPropagation();
					setHidden(n.id, !away);
				});
				li.appendChild(eye);
			}

			list.appendChild(li);
		});
	}

	toggle.addEventListener('click', function () {
		setTray(!state.open);
	});
	closeBtn.addEventListener('click', function () {
		setTray(false);
	});
	newBtn.addEventListener('click', createNote);
	search.addEventListener('input', function () {
		state.search = search.value.trim();
		renderList();
	});
	tabMine.addEventListener('click', function () {
		state.tab = 'mine';
		tabMine.classList.add('sn-tab-active');
		tabShared.classList.remove('sn-tab-active');
		renderList();
	});
	tabShared.addEventListener('click', function () {
		state.tab = 'shared';
		tabShared.classList.add('sn-tab-active');
		tabMine.classList.remove('sn-tab-active');
		renderList();
	});
	showAllBtn.addEventListener('click', function () {
		toggleAll(false);
	});
	hideAllBtn.addEventListener('click', function () {
		toggleAll(true);
	});

	function toggleAll(hidden) {
		ajax('toggle_all', { page: D.page, hidden: hidden ? 1 : 0 }, function (r) {
			loadNotes(r.notes);
			renderAll();
		});
	}

	/* ------------------------------------------------------------------
	 * Notes data
	 * ------------------------------------------------------------------ */
	function loadNotes(notes) {
		state.notes = {};
		state.order = [];
		(notes || []).forEach(storeNote);
	}

	function storeNote(n) {
		if (!state.notes[n.id]) {
			state.order.push(n.id);
		}
		state.notes[n.id] = n;
		if (n.z > state.maxZ) {
			state.maxZ = n.z;
		}
	}

	function removeNote(id) {
		delete state.notes[id];
		state.order = state.order.filter(function (x) { return x !== id; });
		var node = findNode(id, true);
		if (node) {
			node.parentNode.removeChild(node);
		}
	}

	function findNode(id, own) {
		return layer.querySelector('.sn-note[data-id="' + id + '"][data-own="' + (own ? '1' : '0') + '"]');
	}

	function renderAll() {
		Array.prototype.forEach.call(layer.querySelectorAll('.sn-note'), flushDraft);
		layer.innerHTML = '';
		if (!trayOnly) {
			state.order.forEach(function (id) {
				if (isVisible(state.notes[id])) {
					renderNote(state.notes[id]);
				}
			});
		}
		Object.keys(state.sharedOpen).forEach(function (id) {
			if (state.shared[id]) {
				renderNote(state.shared[id]);
			}
		});
		updateCounter();
		scheduleWake();
		scheduleReminders();
		if (state.open) {
			renderList();
		}
	}

	function refresh(n) {
		storeNote(n);
		var node = findNode(n.id, true);
		var visible = isVisible(n);
		if (node && !visible && !node.classList.contains('sn-modal')) {
			node.parentNode.removeChild(node);
		} else if (node || visible) {
			renderNote(n, node && node.classList.contains('sn-modal'));
		}
		updateCounter();
		scheduleWake();
		scheduleReminders();
		if (state.open) {
			renderList();
		}
	}

	/* ------------------------------------------------------------------
	 * Note element
	 * ------------------------------------------------------------------ */
	function renderNote(n, modal) {
		var existing = findNode(n.id, n.own);
		if (existing) {
			flushDraft(existing);
		}
		var node = el('div', 'sn-note sn-c-' + n.color + (n.own ? '' : ' sn-readonly') + (n.locked ? ' sn-locked' : ''));
		node.setAttribute('data-id', n.id);
		node.setAttribute('data-own', n.own ? '1' : '0');
		node.setAttribute('role', 'note');
		if (modal || trayOnly) {
			node.classList.add('sn-modal');
		} else if (n.own && n.minimized) {
			node.classList.add('sn-minimized');
		}
		node.style.setProperty('--sn-rot', (D.rotation ? n.rot : 0) + 'deg');

		node.appendChild(el('span', 'sn-pushpin'));

		// Header
		var headEl = el('div', 'sn-head');
		headEl.title = n.locked ? lang('LOCKED') : lang('DRAG');
		var titleEl = el('span', 'sn-title');
		titleEl.textContent = n.title || lang('UNTITLED');
		headEl.appendChild(titleEl);

		var badges = el('span', 'sn-badges');
		if (n.own) {
			if (n.scope === 'page') {
				badges.insertAdjacentHTML('beforeend', '<span title="' + escapeHtml(lang('SCOPE_PAGE')) + '">' + icon('page') + '</span>');
			}
			if (n.reminder) {
				badges.insertAdjacentHTML('beforeend', '<span title="' + escapeHtml(langf('REMINDER_AT', formatDate(n.reminder))) + '">' + icon('bell') + '</span>');
			}
			if (n.shares && n.shares.length) {
				badges.insertAdjacentHTML('beforeend', '<span title="' + escapeHtml(lang('SHARED_WITH') + ' ' + n.shares.map(function (s) { return s.name; }).join(', ')) + '">' + icon('share') + '</span>');
			}
			if (n.locked) {
				badges.insertAdjacentHTML('beforeend', '<span title="' + escapeHtml(lang('LOCKED')) + '">' + icon('lock') + '</span>');
			}
		}
		headEl.appendChild(badges);

		var actions = el('span', 'sn-actions');
		if (n.own) {
			var editBtn = btn('', 'edit', lang('EDIT'));
			editBtn.addEventListener('click', function () { openEditor(n.id); });
			actions.appendChild(editBtn);

			if (!node.classList.contains('sn-modal')) {
				var minBtn = btn('sn-min-btn', n.minimized ? 'chevDown' : 'chevUp', n.minimized ? lang('EXPAND') : lang('MINIMIZE'));
				minBtn.addEventListener('click', function () { setMinimized(n.id, !n.minimized); });
				actions.appendChild(minBtn);
			}

			if (!trayOnly) {
				var hideBtn = btn('', 'minus', lang('HIDE'));
				hideBtn.addEventListener('click', function () { setHidden(n.id, true); });
				actions.appendChild(hideBtn);
			}

			var menuBtn = btn('', 'dots', lang('MENU'));
			menuBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				toggleMenu(node, n);
			});
			actions.appendChild(menuBtn);
		}
		if (!n.own || trayOnly || node.classList.contains('sn-modal')) {
			var closeNote = btn('', 'close', lang('CLOSE'));
			closeNote.addEventListener('click', function () {
				if (!n.own) {
					delete state.sharedOpen[n.id];
				}
				node.parentNode.removeChild(node);
			});
			actions.appendChild(closeNote);
		}
		headEl.appendChild(actions);
		node.appendChild(headEl);

		// Body
		var body = el('div', 'sn-body');
		body.innerHTML = n.html || '';
		if (!n.html && n.own) {
			body.innerHTML = '<span class="sn-placeholder">' + escapeHtml(lang('TEXT_PLACEHOLDER')) + '</span>';
		}
		if (!n.own) {
			body.insertAdjacentHTML('afterbegin', '<div class="sn-sharedby">' + icon('user') + escapeHtml(langf('SHARED_BY', n.ownerName)) + '</div>');
		}
		node.appendChild(body);

		if (n.own && n.reminder) {
			var rem = el('div', 'sn-reminder-line' + (n.reminder * 1000 < Date.now() ? ' sn-expired' : ''));
			rem.innerHTML = icon('bell') + escapeHtml(langf('REMINDER_AT', formatDate(n.reminder)) +
				(n.repeat ? ' · ' + lang(REPEATS[n.repeat]) : '') + (n.reminder * 1000 < Date.now() ? ' · ' + lang('REMINDER_EXPIRED') : ''));
			node.appendChild(rem);
		}

		if (n.own) {
			// Double click on the title: rename in place
			titleEl.addEventListener('dblclick', function (e) {
				e.stopPropagation();
				renameInline(n, titleEl);
			});
			body.addEventListener('dblclick', function () {
				openEditor(n.id);
			});
		}

		if (!node.classList.contains('sn-modal')) {
			var grip = el('span', 'sn-resize');
			grip.title = lang('RESIZE');
			node.appendChild(grip);
			if (!n.locked || !n.own) {
				enableResize(node, grip, n);
			}
			enableDrag(node, headEl, n);
		}

		node.addEventListener('pointerdown', function () {
			bringToFront(node, n);
		});

		position(node, n);

		if (existing) {
			layer.replaceChild(node, existing);
		} else {
			layer.appendChild(node);
		}

		return node;
	}

	function position(node, n) {
		if (node.classList.contains('sn-modal')) {
			node.style.zIndex = state.maxZ + 10;
			return;
		}
		var minimized = node.classList.contains('sn-minimized');
		var w = Math.min(minimized ? Math.min(n.w, 260) : n.w, vw() - 16);
		var h = minimized ? 46 : Math.min(n.h, vh() - 16);
		var left = Math.round(n.x / 10000 * vw());
		var top = Math.round(n.y / 10000 * vh());
		left = Math.max(4, Math.min(left, vw() - w - 4));
		top = Math.max(4, Math.min(top, vh() - h - 4));
		node.style.left = left + 'px';
		node.style.top = top + 'px';
		node.style.width = w + 'px';
		node.style.height = minimized ? '' : h + 'px';
		node.style.zIndex = n.z;
	}

	function bringToFront(node, n) {
		if (node.classList.contains('sn-modal') || n.z >= state.maxZ) {
			return;
		}
		state.maxZ++;
		n.z = state.maxZ;
		node.style.zIndex = n.z;
		if (n.own) {
			ajax('props', { id: n.id, z: n.z });
		}
	}

	function flash(node) {
		node.classList.remove('sn-flash');
		void node.offsetWidth;
		node.classList.add('sn-flash');
	}

	/* ------------------------------------------------------------------
	 * Drag & resize (Pointer Events: mouse, pen and touch)
	 * ------------------------------------------------------------------ */
	function enableDrag(node, handle, n) {
		handle.addEventListener('pointerdown', function (e) {
			if (e.button !== 0 || e.target.closest('button, input, select, textarea, a')) {
				return;
			}
			if (n.own && n.locked) {
				return;
			}
			var startX = e.clientX, startY = e.clientY;
			var origLeft = node.offsetLeft, origTop = node.offsetTop;
			var pointerId = e.pointerId;
			var moved = false;
			// the bin: only for your own notes, not in the full-screen (modal) mode
			var useTrash = !!trash && n.own && !node.classList.contains('sn-modal');

			// The pointer is captured only once the note really moves: capturing it on
			// pointerdown would send the double click to the header instead of the title
			// (double click on the title = rename).
			function move(ev) {
				if (ev.pointerId !== pointerId) {
					return;
				}
				var dx = ev.clientX - startX, dy = ev.clientY - startY;
				if (!moved) {
					if (Math.abs(dx) + Math.abs(dy) < 4) {
						return;
					}
					moved = true;
					node.classList.add('sn-dragging');
					if (useTrash) {
						trashShow();
					}
					try {
						handle.setPointerCapture(pointerId);
					} catch (err) {
						// Capture not available: document listeners keep working
					}
				}
				ev.preventDefault();
				var left = Math.max(0, Math.min(origLeft + dx, vw() - node.offsetWidth));
				var top = Math.max(0, Math.min(origTop + dy, vh() - node.offsetHeight));
				node.style.left = left + 'px';
				node.style.top = top + 'px';
				if (useTrash) {
					var over = trashHit(ev.clientX, ev.clientY);
					trashSetFull(over);
					node.classList.toggle('sn-over-trash', over);
				}
			}

			function up(ev) {
				if (ev.pointerId !== pointerId) {
					return;
				}
				document.removeEventListener('pointermove', move);
				document.removeEventListener('pointerup', up);
				document.removeEventListener('pointercancel', up);
				node.classList.remove('sn-dragging');
				if (!moved) {
					return;
				}
				if (useTrash) {
					if (trashHit(ev.clientX, ev.clientY) && ev.type === 'pointerup') {
						dropInTrash(node, n, origLeft, origTop);
						return;
					}
					node.classList.remove('sn-over-trash');
					trashHide(true);
				}
				n.x = Math.round(node.offsetLeft / vw() * 10000);
				n.y = Math.round(node.offsetTop / vh() * 10000);
				if (n.own) {
					ajax('props', { id: n.id, x: n.x, y: n.y, z: n.z });
				}
			}

			document.addEventListener('pointermove', move);
			document.addEventListener('pointerup', up);
			document.addEventListener('pointercancel', up);
		});
	}

	/* ------------------------------------------------------------------
	 * Bin shown while dragging a note (ACP: on/off, above or beside the bar button)
	 * ------------------------------------------------------------------ */
	var TRASH_EMPTY = '<svg viewBox="0 0 64 72" aria-hidden="true" focusable="false">' +
		'<path class="sn-trash-body" d="M10 19h44l-5 46.5a3 3 0 0 1-3 2.5H18a3 3 0 0 1-3-2.5z"/>' +
		'<g class="sn-trash-slots"><path d="M19 27l2.4 33M26 27l1.2 33M32 27v33M38 27l-1.2 33M45 27l-2.4 33"/></g>' +
		'<rect class="sn-trash-band" x="12.5" y="41" width="39" height="4" rx="1"/>' +
		'<rect class="sn-trash-rim" x="7" y="14" width="50" height="7" rx="3.5"/>' +
		'<ellipse class="sn-trash-mouth" cx="32" cy="16.4" rx="21" ry="1.6"/></svg>';
	var TRASH_FULL = '<svg viewBox="0 0 64 72" aria-hidden="true" focusable="false">' +
		'<path class="sn-trash-paper" d="M9 18C7 11 12 5 19 7c1-6 9-9 14-4 4-5 13-4 15 3 6 0 9 6 7 12z"/>' +
		'<path class="sn-trash-paper sn-trash-paper2" d="M22 16c-1-5 4-8 8-6 3-3 9-2 10 2 3 0 5 3 4 5z"/>' +
		'<path class="sn-trash-crease" d="M15 10l5 4M24 5l2 6M34 3l-1 6M43 6l-3 5M50 10l-4 4M28 12l3 3M36 11l-2 4"/>' +
		'<path class="sn-trash-body" d="M10 19h44l-5 46.5a3 3 0 0 1-3 2.5H18a3 3 0 0 1-3-2.5z"/>' +
		'<g class="sn-trash-slots sn-trash-slots-full"><path d="M19 27l2.4 33M26 27l1.2 33M32 27v33M38 27l-1.2 33M45 27l-2.4 33"/></g>' +
		'<rect class="sn-trash-band" x="12.5" y="41" width="39" height="4" rx="1"/>' +
		'<rect class="sn-trash-rim" x="7" y="14" width="50" height="7" rx="3.5"/></svg>';

	var trash = null, trashTip = null, trashTimer = null, trashBusy = false;
	if (D.trash && !trayOnly) {
		trash = el('div', 'sn-trash sn-trash-' + D.trash);
		trash.setAttribute('aria-hidden', 'true');
		trash.innerHTML = '<span class="sn-trash-icon sn-trash-icon-empty">' + TRASH_EMPTY + '</span>' +
			'<span class="sn-trash-icon sn-trash-icon-full">' + TRASH_FULL + '</span>';
		trashTip = el('span', 'sn-trash-tip');
		trashTip.textContent = lang('TRASH');
		trash.appendChild(trashTip);
		root.appendChild(trash);
	}

	function placeTrash() {
		var r = toggle.getBoundingClientRect();
		// well away from the bar button, also when it grows while full
		var w = trash.offsetWidth || 84, h = trash.offsetHeight || 96, gap = 34;
		var left, top;
		if (D.trash === 'side') {
			left = D.tray.charAt(1) === 'l' ? r.right + gap : r.left - w - gap;
			top = r.top + r.height / 2 - h / 2;
		} else {
			left = r.left + r.width / 2 - w / 2;
			top = D.tray.charAt(0) === 't' ? r.bottom + gap : r.top - h - gap;
		}
		trash.style.left = Math.round(Math.max(10, Math.min(left, vw() - w - 10))) + 'px';
		trash.style.top = Math.round(Math.max(10, Math.min(top, vh() - h - 10))) + 'px';
	}

	function trashShow() {
		clearTimeout(trashTimer);
		trashBusy = false;
		trash.classList.remove('sn-trash-full', 'sn-trash-gulp', 'sn-trash-leaving');
		trashTip.textContent = lang('TRASH');
		placeTrash();
		trash.classList.add('sn-trash-visible');
	}

	function trashHide(now) {
		clearTimeout(trashTimer);
		var go = function () {
			trash.classList.remove('sn-trash-visible', 'sn-trash-full', 'sn-trash-gulp');
			trashBusy = false;
		};
		if (now) {
			go();
		} else {
			// stays empty for a moment, then goes away
			trashTimer = setTimeout(go, 2500);
		}
	}

	function trashSetFull(full) {
		if (trashBusy) {
			return;
		}
		trash.classList.toggle('sn-trash-full', full);
		trashTip.textContent = full ? lang('TRASH_DROP') : lang('TRASH');
	}

	function trashHit(x, y) {
		if (!trash || !trash.classList.contains('sn-trash-visible')) {
			return false;
		}
		var r = trash.getBoundingClientRect(), pad = 14;
		return x >= r.left - pad && x <= r.right + pad && y >= r.top - pad && y <= r.bottom + pad;
	}

	function trashEmpty() {
		trash.classList.remove('sn-trash-full');
		trashTip.textContent = lang('TRASH');
	}

	/** The note was released on the bin: confirm, then delete it or put it back */
	function dropInTrash(node, n, origLeft, origTop) {
		trashBusy = true;
		trash.classList.add('sn-trash-full');
		trashTip.textContent = lang('TRASH_DROP');
		node.classList.add('sn-over-trash');

		function putBack() {
			node.classList.remove('sn-over-trash');
			node.classList.add('sn-returning');
			node.style.left = origLeft + 'px';
			node.style.top = origTop + 'px';
			setTimeout(function () { node.classList.remove('sn-returning'); }, 650);
			trashBusy = false;
			trashEmpty();
			trashHide(false);
		}

		confirmBox(lang('DELETE_CONFIRM'), function () {
			// into the bin...
			var tr = trash.getBoundingClientRect(), nr = node.getBoundingClientRect();
			var dx = (tr.left + tr.width / 2) - (nr.left + nr.width / 2);
			var dy = (tr.top + tr.height * 0.35) - (nr.top + nr.height / 2);
			node.style.setProperty('--sn-tx', dx + 'px');
			node.style.setProperty('--sn-ty', dy + 'px');
			node.classList.add('sn-trashing');
			ajax('delete', { id: n.id }, function () {
				setTimeout(function () {
					D.total = Math.max(0, D.total - 1);
					removeNote(n.id);
					updateCounter();
					if (state.open) {
						renderList();
					}
					// gulp, then the bin is empty again and goes away
					trash.classList.add('sn-trash-gulp');
					setTimeout(function () {
						trash.classList.remove('sn-trash-gulp');
						trashBusy = false;
						trashEmpty();
						trashHide(false);
					}, 650);
				}, 420);
			}, function () {
				node.classList.remove('sn-trashing');
				putBack();
			});
		}, putBack);
	}

	function enableResize(node, grip, n) {
		grip.addEventListener('pointerdown', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var startX = e.clientX, startY = e.clientY;
			var w0 = node.offsetWidth, h0 = node.offsetHeight;
			grip.setPointerCapture(e.pointerId);

			function move(ev) {
				var w = Math.max(160, Math.min(640, w0 + ev.clientX - startX, vw() - node.offsetLeft - 4));
				var h = Math.max(120, Math.min(640, h0 + ev.clientY - startY, vh() - node.offsetTop - 4));
				node.style.width = w + 'px';
				node.style.height = h + 'px';
			}

			function up() {
				grip.removeEventListener('pointermove', move);
				grip.removeEventListener('pointerup', up);
				grip.removeEventListener('pointercancel', up);
				n.w = node.offsetWidth;
				n.h = node.offsetHeight;
				if (n.own) {
					ajax('props', { id: n.id, w: n.w, h: n.h });
				}
			}

			grip.addEventListener('pointermove', move);
			grip.addEventListener('pointerup', up);
			grip.addEventListener('pointercancel', up);
		});
	}

	window.addEventListener('resize', function () {
		Array.prototype.forEach.call(layer.querySelectorAll('.sn-note'), function (node) {
			var id = parseInt(node.getAttribute('data-id'), 10);
			var n = node.getAttribute('data-own') === '1' ? state.notes[id] : state.shared[id];
			if (n) {
				position(node, n);
			}
		});
	});

	/* ------------------------------------------------------------------
	 * Actions
	 * ------------------------------------------------------------------ */
	function createNote() {
		if (D.maxNotes > 0 && D.total >= D.maxNotes) {
			showError(langf('ERR_LIMIT_JS', D.maxNotes));
			return;
		}
		ajax('create', {
			color: D.color,
			scope: 'global',
			page: D.page,
			x: Math.round(2500 + Math.random() * 3500),
			y: Math.round(1500 + Math.random() * 3500)
		}, function (r) {
			D.total++;
			refresh(r.note);
			if (trayOnly) {
				renderNote(r.note, true);
			}
			openEditor(r.note.id);
		});
	}

	function openOwn(id) {
		var n = state.notes[id];
		if (!n) {
			return;
		}
		if (trayOnly) {
			flash(renderNote(n, true));
			setTray(false);
			return;
		}
		if (n.hidden || isSnoozed(n)) {
			setHidden(id, false, true);
			return;
		}
		var node = findNode(id, true) || renderNote(n);
		bringToFront(node, n);
		flash(node);
	}

	function openShared(id) {
		var n = state.shared[id];
		if (!n) {
			return;
		}
		if (!n.positioned) {
			n.x = 3500;
			n.y = 2000;
			n.w = n.w || 240;
			n.h = n.h || 220;
			n.z = ++state.maxZ;
			n.positioned = true;
		}
		state.sharedOpen[id] = true;
		var node = renderNote(n, trayOnly);
		flash(node);
		if (trayOnly) {
			setTray(false);
		}
	}

	function setHidden(id, hidden, focus) {
		var data = { id: id, hidden: hidden ? 1 : 0 };
		if (!hidden) {
			data.snooze = 0;	// showing a note also wakes it up
		}
		ajax('props', data, function (r) {
			refresh(r.note);
			if (focus && !hidden) {
				var node = findNode(id, true);
				if (node) {
					bringToFront(node, state.notes[id]);
					flash(node);
				}
			}
		});
	}

	function setMinimized(id, minimized) {
		ajax('props', { id: id, minimized: minimized ? 1 : 0 }, function (r) {
			refresh(r.note);
		});
	}

	/**
	 * Hide a single note until the given time, then it comes back by itself
	 */
	function setSnooze(id, until) {
		ajax('props', { id: id, snooze: until }, function (r) {
			refresh(r.note);
			toggle.classList.remove('sn-pulse');
			void toggle.offsetWidth;
			toggle.classList.add('sn-pulse');
		});
	}

	function snoozeTimes() {
		var now = new Date();
		var tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 8, 0, 0);
		var sec = function (d) { return Math.round(d.getTime() / 1000); };
		return [
			['SNOOZE_30M', sec(new Date(now.getTime() + 30 * 60000))],
			['SNOOZE_1H', sec(new Date(now.getTime() + 3600000))],
			['SNOOZE_4H', sec(new Date(now.getTime() + 4 * 3600000))],
			['SNOOZE_TOMORROW', sec(tomorrow)],
			['SNOOZE_WEEK', sec(new Date(now.getTime() + 7 * 86400000))]
		];
	}

	function formatShort(ts) {
		var d = new Date(ts * 1000), now = new Date();
		function p(x) { return (x < 10 ? '0' : '') + x; }
		var time = p(d.getHours()) + ':' + p(d.getMinutes());
		if (d.toDateString() === now.toDateString()) {
			return time;
		}
		return p(d.getDate()) + '/' + p(d.getMonth() + 1) + ' ' + time;
	}

	var wakeTimer = null;

	/**
	 * When the first snoozed note expires while the page is open, show it again
	 */
	function scheduleWake() {
		if (wakeTimer) {
			clearTimeout(wakeTimer);
			wakeTimer = null;
		}
		var next = 0;
		state.order.forEach(function (id) {
			var n = state.notes[id];
			if (isSnoozed(n) && (!next || n.snooze < next)) {
				next = n.snooze;
			}
		});
		if (!next) {
			return;
		}
		var delay = next * 1000 - Date.now() + 500;
		if (delay > 2147000000) {
			return;
		}
		wakeTimer = setTimeout(function () {
			var woken = state.order.filter(function (id) {
				var n = state.notes[id];
				return n.snooze && !isSnoozed(n) && !n.hidden;
			});
			woken.forEach(function (id) {
				state.notes[id].snooze = 0;
			});
			renderAll();
			woken.forEach(function (id) {
				var node = findNode(id, true);
				if (node) {
					flash(node);
				}
			});
		}, Math.max(0, delay));
	}

	function openSnoozeMenu(node, n) {
		closeMenus();
		var menu = el('div', 'sn-menu sn-menu-snooze');
		menu.setAttribute('role', 'menu');

		var head = el('div', 'sn-menu-head');
		var backBtn = btn('sn-menu-back', 'back', lang('BACK'));
		backBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			closeMenus();
			toggleMenu(node, n);
		});
		head.appendChild(backBtn);
		head.appendChild(el('span', '', icon('clock') + escapeHtml(lang('SNOOZE'))));
		menu.appendChild(head);

		snoozeTimes().forEach(function (opt) {
			var b = btn('sn-menu-item', '', '', lang(opt[0]));
			b.insertAdjacentHTML('beforeend', '<small>' + escapeHtml(formatShort(opt[1])) + '</small>');
			b.addEventListener('click', function (e) {
				e.stopPropagation();
				closeMenus();
				setSnooze(n.id, opt[1]);
			});
			menu.appendChild(b);
		});

		menuItem(menu, 'edit', lang('SNOOZE_CUSTOM'), function () { openSnoozeForm(n.id); });

		if (isSnoozed(n)) {
			menuItem(menu, 'eye', lang('SNOOZE_WAKE'), function () { setSnooze(n.id, 0); });
		}

		node.appendChild(menu);
		fitMenu(menu, node);
	}

	function openSnoozeForm(id) {
		var ctx = notePanel(id, 'sn-form-snooze');
		if (!ctx) {
			return;
		}
		var n = ctx.n, form = ctx.form;

		form.appendChild(el('div', 'sn-form-title', icon('clock') + escapeHtml(lang('SNOOZE'))));
		var label = el('label', 'sn-label', escapeHtml(lang('SNOOZE_UNTIL_LABEL')));
		var when = el('input', 'sn-input');
		when.type = 'datetime-local';
		when.value = toLocalInput(isSnoozed(n) ? n.snooze : Math.round(Date.now() / 1000) + 7200);
		label.appendChild(when);
		form.appendChild(label);

		formButtons(ctx, lang('SNOOZE_SET'), function () {
			var ts = when.value ? Math.round(new Date(when.value).getTime() / 1000) : 0;
			if (!ts || ts * 1000 <= Date.now()) {
				showError(lang('ERR_SNOOZE_PAST'));
				return;
			}
			closePanel(ctx);
			setSnooze(n.id, ts);
		});
		when.focus();
	}

	function deleteNote(id) {
		confirmBox(lang('DELETE_CONFIRM'), function () {
			ajax('delete', { id: id }, function () {
				D.total = Math.max(0, D.total - 1);
				removeNote(id);
				updateCounter();
				if (state.open) {
					renderList();
				}
			});
		});
	}

	function renameInline(n, titleEl) {
		var input = el('input', 'sn-rename');
		input.type = 'text';
		input.maxLength = 100;
		input.value = n.title;
		input.placeholder = lang('TITLE_PLACEHOLDER');
		titleEl.replaceWith(input);
		input.focus();
		input.select();
		var done = false;

		function finish(save) {
			if (done) {
				return;
			}
			done = true;
			if (save && input.value.trim() !== n.title) {
				ajax('props', { id: n.id, title: input.value.trim() }, function (r) {
					refresh(r.note);
				});
			} else {
				input.replaceWith(titleEl);
			}
		}

		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				finish(true);
			} else if (e.key === 'Escape') {
				finish(false);
			}
		});
		input.addEventListener('blur', function () {
			finish(true);
		});
	}

	/* ------------------------------------------------------------------
	 * Context menu
	 * ------------------------------------------------------------------ */
	function closeMenus() {
		Array.prototype.forEach.call(layer.querySelectorAll('.sn-menu'), function (m) {
			m.parentNode.removeChild(m);
		});
	}

	document.addEventListener('pointerdown', function (e) {
		if (!e.target.closest('.sn-menu')) {
			closeMenus();
		}
	});

	function menuItem(menu, iconName, label, handler, cls) {
		var b = btn('sn-menu-item ' + (cls || ''), iconName, '', label);
		b.addEventListener('click', function (e) {
			e.stopPropagation();
			closeMenus();
			handler();
		});
		menu.appendChild(b);
		return b;
	}

	function toggleMenu(node, n) {
		var open = node.querySelector('.sn-menu');
		closeMenus();
		if (open) {
			return;
		}

		var menu = el('div', 'sn-menu');
		menu.setAttribute('role', 'menu');

		// Colours
		var colors = el('div', 'sn-menu-colors');
		colors.setAttribute('aria-label', lang('COLOR'));
		D.colors.forEach(function (c) {
			var sw = btn('sn-swatch sn-c-' + c + (c === n.color ? ' sn-swatch-active' : ''), '', lang('COLOR_' + c.toUpperCase()));
			sw.addEventListener('click', function (e) {
				e.stopPropagation();
				closeMenus();
				ajax('props', { id: n.id, color: c }, function (r) { refresh(r.note); });
			});
			colors.appendChild(sw);
		});
		menu.appendChild(colors);

		menuItem(menu, 'edit', lang('EDIT'), function () { openEditor(n.id); });

		if (n.scope === 'page') {
			menuItem(menu, 'globe', lang('SCOPE_GLOBAL'), function () {
				ajax('props', { id: n.id, scope: 'global' }, function (r) { refresh(r.note); });
			}).title = lang('SCOPE_GLOBAL_EXPLAIN');
		} else {
			menuItem(menu, 'page', lang('SCOPE_PAGE'), function () {
				ajax('props', { id: n.id, scope: 'page', page: D.page }, function (r) { refresh(r.note); });
			}).title = lang('SCOPE_PAGE_EXPLAIN');
		}

		if (D.can.reminder) {
			menuItem(menu, 'bell', lang('REMINDER'), function () { openReminder(n.id); });
		}
		if (D.can.share) {
			menuItem(menu, 'share', lang('SHARE'), function () { openShare(n.id); });
		}

		var snoozeItem = btn('sn-menu-item', 'clock', '', lang('SNOOZE') + '…');
		snoozeItem.addEventListener('click', function (e) {
			e.stopPropagation();
			openSnoozeMenu(node, n);
		});
		menu.appendChild(snoozeItem);

		menuItem(menu, n.minimized ? 'chevDown' : 'chevUp', n.minimized ? lang('EXPAND') : lang('MINIMIZE'), function () {
			setMinimized(n.id, !n.minimized);
		});

		menuItem(menu, 'copy', lang('DUPLICATE'), function () {
			ajax('duplicate', { id: n.id }, function (r) {
				D.total++;
				var copy = r.note;
				var orig = findNode(n.id, true);

				// Put the copy next to the original (right, else left, else below)
				// so it never covers the original and its buttons
				if (orig && !orig.classList.contains('sn-modal')) {
					var gap = 16, w = orig.offsetWidth, h = orig.offsetHeight;
					var left = orig.offsetLeft + w + gap, top = orig.offsetTop;
					if (left + w > vw() - 4) {
						left = orig.offsetLeft - w - gap;
					}
					if (left < 4) {
						left = orig.offsetLeft;
						top = orig.offsetTop + h + gap;
						if (top + h > vh() - 4) {
							top = Math.max(4, orig.offsetTop - h - gap);
						}
					}
					copy.x = Math.round(left / vw() * 10000);
					copy.y = Math.round(top / vh() * 10000);
					copy.w = n.w;
					copy.h = n.h;
					ajax('props', { id: copy.id, x: copy.x, y: copy.y, w: copy.w, h: copy.h });
				}

				refresh(copy);
				var node2 = findNode(copy.id, true);
				if (node2) {
					flash(node2);
				}
			});
		});

		menuItem(menu, n.locked ? 'unlock' : 'lock', n.locked ? lang('UNLOCK') : lang('LOCK'), function () {
			ajax('props', { id: n.id, locked: n.locked ? 0 : 1 }, function (r) { refresh(r.note); });
		});

		menuItem(menu, 'trash', lang('DELETE'), function () { deleteNote(n.id); }, 'sn-danger');

		node.appendChild(menu);
		fitMenu(menu, node);
	}

	/* ------------------------------------------------------------------
	 * Panels inside a note (editor, reminder, share)
	 * ------------------------------------------------------------------ */
	/**
	 * Keep a context menu inside the screen: notes are fixed on the screen and do
	 * not scroll, so a menu opening below a note near the bottom was unreachable.
	 */
	function fitMenu(menu, node) {
		var margin = 8;
		var nr = node.getBoundingClientRect();
		menu.style.maxHeight = (vh() - margin * 2) + 'px';
		menu.style.overflowY = 'auto';

		var mr = menu.getBoundingClientRect();
		if (mr.bottom > vh() - margin) {
			// open upwards / move up just enough
			var top = (vh() - margin - mr.height) - nr.top;
			menu.style.top = Math.max(margin - nr.top, top) + 'px';
		}
		mr = menu.getBoundingClientRect();
		if (mr.left < margin) {
			menu.style.right = 'auto';
			menu.style.left = (margin - nr.left) + 'px';
		} else if (mr.right > vw() - margin) {
			menu.style.right = (nr.right - (vw() - margin)) + 'px';
		}
	}

	/**
	 * While a form is open the note may grow: lift it so the whole form (and its
	 * Save button) stays on screen. The saved position is not changed.
	 */
	function fitPanel(node) {
		var raf = window.requestAnimationFrame || function (f) { return setTimeout(f, 0); };
		raf(function () {
			if (!node.classList.contains('sn-editing') || node.classList.contains('sn-modal')) {
				return;
			}
			var r = node.getBoundingClientRect();
			if (r.bottom > vh() - 4) {
				node.style.top = Math.max(4, node.offsetTop - (r.bottom - (vh() - 4))) + 'px';
			}
			r = node.getBoundingClientRect();
			if (r.right > vw() - 4) {
				node.style.left = Math.max(4, node.offsetLeft - (r.right - (vw() - 4))) + 'px';
			}
		});
	}

	/* ------------------------------------------------------------------
	 * Text being edited is never lost: if the editor is replaced by another
	 * form (alert, share, snooze) or the note is redrawn, it is saved first.
	 * ------------------------------------------------------------------ */
	var draftsPending = 0, afterDrafts = [];

	function whenDraftsSaved(fn) {
		if (draftsPending > 0) {
			afterDrafts.push(fn);
		} else {
			fn();
		}
	}

	function draftDone() {
		draftsPending = Math.max(0, draftsPending - 1);
		if (!draftsPending) {
			var list = afterDrafts;
			afterDrafts = [];
			list.forEach(function (fn) { fn(); });
		}
	}

	function flushDraft(node) {
		var form = node && node.querySelector('.sn-form-edit');
		if (!form || typeof form.snDraft !== 'function') {
			return;
		}
		var data = form.snDraft();
		form.snDraft = null;
		if (!data) {
			return;
		}
		var id = data.id;
		// show it at once, the server answer replaces it
		var n = state.notes[id];
		if (n) {
			n.title = data.title;
			n.text = data.text;
		}
		draftsPending++;
		ajax('update', data, function (r) {
			var live = findNode(id, true);
			if (live && live.classList.contains('sn-editing')) {
				// another form is open on the note: update it without closing the form
				storeNote(r.note);
				var t = live.querySelector('.sn-title');
				if (t) {
					t.textContent = r.note.title || lang('UNTITLED');
				}
				var b = live.querySelector('.sn-body');
				if (b) {
					b.innerHTML = r.note.html || '<span class="sn-placeholder">' + escapeHtml(lang('TEXT_PLACEHOLDER')) + '</span>';
				}
				if (state.open) {
					renderList();
				}
			} else {
				refresh(r.note);
			}
			draftDone();
		}, draftDone);
	}

	function notePanel(id, cls) {
		var n = state.notes[id];
		if (!n) {
			return null;
		}
		var node = findNode(id, true);
		if (!node) {
			node = renderNote(n, trayOnly);
		}
		bringToFront(node, n);

		var old = node.querySelector('.sn-form');
		if (old) {
			flushDraft(node);
			old.parentNode.removeChild(old);
		}
		node.classList.add('sn-editing');

		var form = el('form', 'sn-form ' + cls);
		form.addEventListener('submit', function (e) { e.preventDefault(); });
		node.appendChild(form);
		fitPanel(node);

		return { n: n, node: node, form: form };
	}

	function closePanel(ctx) {
		ctx.node.classList.remove('sn-editing');
		if (ctx.form.parentNode) {
			ctx.form.parentNode.removeChild(ctx.form);
		}
		// back to its saved place
		if (ctx.node.parentNode) {
			position(ctx.node, ctx.n);
		}
	}

	function formButtons(ctx, saveLabel, onSave, extra) {
		var row = el('div', 'sn-form-buttons');
		if (extra) {
			row.appendChild(extra);
		}
		var cancel = btn('sn-btn-ghost', '', '', lang('CANCEL'));
		cancel.addEventListener('click', function () { closePanel(ctx); });
		var save = btn('sn-btn-primary', '', '', saveLabel);
		save.addEventListener('click', onSave);
		row.appendChild(cancel);
		row.appendChild(save);
		ctx.form.appendChild(row);
		return save;
	}

	function openEditor(id) {
		closeMenus();
		var ctx = notePanel(id, 'sn-form-edit');
		if (!ctx) {
			return;
		}
		var n = ctx.n, form = ctx.form;

		var title = el('input', 'sn-input');
		title.type = 'text';
		title.maxLength = 100;
		title.value = n.title;
		title.placeholder = lang('TITLE_PLACEHOLDER');
		form.appendChild(title);

		var format = null, toolbar = null;
		if (D.can.bbcode) {
			var fmtRow = el('div', 'sn-format');
			format = el('select', 'sn-select');
			format.title = lang('FORMAT');
			format.innerHTML = '<option value="0">' + escapeHtml(lang('FORMAT_PLAIN')) + '</option><option value="1">' + escapeHtml(lang('FORMAT_BBCODE')) + '</option>';
			format.value = String(n.format);
			fmtRow.appendChild(format);

			toolbar = el('div', 'sn-bbbar');
			[
				['b', 'BB_BOLD', '<b>B</b>'], ['i', 'BB_ITALIC', '<i>I</i>'], ['u', 'BB_UNDERLINE', '<u>U</u>'],
				['list', 'BB_LIST', '&#8226;'], ['url', 'BB_URL', '&#128279;'], ['quote', 'BB_QUOTE', '&#8220;'],
				['code', 'BB_CODE', '&lt;/&gt;']
			].forEach(function (b) {
				var tb = el('button', 'sn-bb', b[2]);
				tb.type = 'button';
				tb.title = lang(b[1]);
				tb.addEventListener('click', function () { wrap(text, b[0]); });
				toolbar.appendChild(tb);
			});
			var colorIn = el('input', 'sn-bb-color');
			colorIn.type = 'color';
			colorIn.value = '#c0392b';
			colorIn.title = lang('BB_COLOR');
			colorIn.addEventListener('change', function () { wrap(text, 'color', colorIn.value); });
			toolbar.appendChild(colorIn);
			fmtRow.appendChild(toolbar);
			form.appendChild(fmtRow);

			format.addEventListener('change', function () {
				toolbar.style.display = format.value === '1' ? '' : 'none';
			});
			toolbar.style.display = format.value === '1' ? '' : 'none';
		}

		var text = el('textarea', 'sn-textarea');
		text.value = n.text || '';
		text.placeholder = lang('TEXT_PLACEHOLDER');
		form.appendChild(text);

		var left = el('div', 'sn-chars');
		form.appendChild(left);
		function updateChars() {
			if (D.maxChars > 0) {
				var rest = D.maxChars - text.value.length;
				left.textContent = langf('CHARS_LEFT', rest);
				left.classList.toggle('sn-over', rest < 0);
			}
		}
		text.addEventListener('input', updateChars);
		updateChars();

		form.snDraft = function () {
			var data = { id: n.id, title: title.value.trim(), text: text.value, format: format ? format.value : n.format };
			var changed = data.title !== (n.title || '') || data.text !== (n.text || '') || String(data.format) !== String(n.format);
			return changed ? data : null;
		};

		function save() {
			if (D.maxChars > 0 && text.value.length > D.maxChars) {
				return;
			}
			form.snDraft = null;
			ajax('update', {
				id: n.id,
				title: title.value.trim(),
				text: text.value,
				format: format ? format.value : 0
			}, function (r) {
				closePanel(ctx);
				refresh(r.note);
			});
		}

		formButtons(ctx, lang('SAVE'), save);

		form.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
				e.preventDefault();
				save();
			} else if (e.key === 'Escape') {
				closePanel(ctx);
			}
		});

		(n.title ? text : title).focus();
	}

	function wrap(textarea, tag, value) {
		var s = textarea.selectionStart, e = textarea.selectionEnd;
		var sel = textarea.value.substring(s, e);
		var open, close;
		if (tag === 'list') {
			var lines = (sel || '').split(/\n/).map(function (l) { return '[*]' + l; }).join('\n');
			open = '[list]\n';
			sel = lines;
			close = '\n[/list]';
		} else if (tag === 'color') {
			open = '[color=' + value + ']';
			close = '[/color]';
		} else {
			open = '[' + tag + ']';
			close = '[/' + tag + ']';
		}
		textarea.value = textarea.value.substring(0, s) + open + sel + close + textarea.value.substring(e);
		textarea.focus();
		textarea.selectionStart = s + open.length;
		textarea.selectionEnd = s + open.length + sel.length;
		textarea.dispatchEvent(new Event('input'));
	}

	function openReminder(id) {
		var ctx = notePanel(id, 'sn-form-reminder');
		if (!ctx) {
			return;
		}
		var n = ctx.n, form = ctx.form;

		form.appendChild(el('div', 'sn-form-title', icon('bell') + escapeHtml(lang('REMINDER'))));

		var label = el('label', 'sn-label', escapeHtml(lang('REMINDER_DATE')));
		var when = el('input', 'sn-input');
		when.type = 'datetime-local';
		when.value = toLocalInput(n.reminder);
		label.appendChild(when);
		form.appendChild(label);

		var label2 = el('label', 'sn-label', escapeHtml(lang('REMINDER_REPEAT')));
		var repeat = el('select', 'sn-select');
		REPEATS.forEach(function (key, i) {
			var o = el('option', '', escapeHtml(lang(key)));
			o.value = i;
			repeat.appendChild(o);
		});
		repeat.value = String(n.repeat || 0);
		label2.appendChild(repeat);
		form.appendChild(label2);

		var remove = null;
		if (n.reminder) {
			remove = btn('sn-btn-ghost sn-danger', 'trash', '', lang('REMINDER_REMOVE'));
			remove.addEventListener('click', function () {
				ajax('reminder', { id: n.id, time: 0, repeat: 0 }, function (r) {
					closePanel(ctx);
					refresh(r.note);
				});
			});
		}

		formButtons(ctx, lang('REMINDER_SET'), function () {
			var ts = when.value ? Math.round(new Date(when.value).getTime() / 1000) : 0;
			if (!ts) {
				return;
			}
			ajax('reminder', { id: n.id, time: ts, repeat: repeat.value }, function (r) {
				closePanel(ctx);
				refresh(r.note);
			});
		}, remove);
	}

	function openShare(id) {
		var ctx = notePanel(id, 'sn-form-share');
		if (!ctx) {
			return;
		}
		var n = ctx.n, form = ctx.form;
		var shares = n.shares || [];

		form.appendChild(el('div', 'sn-form-title', icon('share') + escapeHtml(lang('SHARE'))));

		var label = el('label', 'sn-label', escapeHtml(lang('SHARE_USERS')));
		var users = el('input', 'sn-input');
		users.type = 'text';
		users.value = shares.filter(function (s) { return s.type === 0; }).map(function (s) { return s.name; }).join(', ');
		users.placeholder = lang('SHARE_USERS_EXPLAIN');
		label.appendChild(users);
		form.appendChild(label);

		var checks = [];
		if (D.groups.length) {
			var gl = el('div', 'sn-label', escapeHtml(lang('SHARE_GROUPS')));
			var box = el('div', 'sn-groups');
			D.groups.forEach(function (g) {
				var lab = el('label', 'sn-check');
				var cb = el('input');
				cb.type = 'checkbox';
				cb.value = g.id;
				cb.checked = shares.some(function (s) { return s.type === 1 && s.id === g.id; });
				lab.appendChild(cb);
				lab.appendChild(document.createTextNode(' ' + g.name));
				box.appendChild(lab);
				checks.push(cb);
			});
			gl.appendChild(box);
			form.appendChild(gl);
		}

		formButtons(ctx, lang('SHARE_SAVE'), function () {
			var groups = checks.filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
			ajax('share', { id: n.id, users: users.value, groups: groups }, function (r) {
				closePanel(ctx);
				refresh(r.note);
				if (r.warning && window.phpbb) {
					phpbb.alert(lang('SHARE'), escapeHtml(r.warning));
				}
			});
		});
		users.focus();
	}

	/* ------------------------------------------------------------------
	 * Reminder popups (phpBB AJAX alert box)
	 * ------------------------------------------------------------------ */
	function showDuePopups(list) {
		list = list || D.due;
		if (!list || !list.length || !window.phpbb || !phpbb.alert) {
			return;
		}
		var html = '<div class="sn-due-list">';
		list.forEach(function (d) {
			html += '<div class="sn-due sn-c-' + escapeHtml(d.color) + '"><strong>' + escapeHtml(d.title || lang('UNTITLED')) + '</strong>' +
				'<div class="sn-due-text">' + (d.html || '') + '</div>' +
				'<small>' + icon('bell') + escapeHtml(langf('REMINDER_AT', formatDate(d.time))) + '</small></div>';
		});
		html += '</div>';
		phpbb.alert(lang('REMINDER_POPUP_TITLE'), html);
		ajax('dismiss', { ids: list.map(function (d) { return d.id; }) });
	}

	/**
	 * Update the phpBB bell counter without reloading (prosilver and derived styles)
	 */
	function setBell(count) {
		if (typeof count !== 'number' || count < 0) {
			return;
		}
		// prosilver and styles based on its navbar
		var badges = document.querySelectorAll('#notification_list_button .badge, #notification-button .badge');
		Array.prototype.forEach.call(badges, function (b) {
			b.textContent = count > 0 ? String(count) : '';
			b.classList.toggle('hidden', count === 0);
		});
		// ForumUS: <div class="header-member--notifications has-unread"><a ...--toggle><small>N</small></a>
		Array.prototype.forEach.call(document.querySelectorAll('.header-member--notifications'), function (box) {
			box.classList.toggle('has-unread', count > 0);
			var toggle = box.querySelector('.header-member-notifications--toggle');
			if (toggle) {
				var small = toggle.querySelector('small');
				if (count > 0) {
					if (!small) {
						small = document.createElement('small');
						toggle.appendChild(small);
					}
					small.textContent = String(count);
				} else if (small) {
					small.parentNode.removeChild(small);
				}
				if (toggle.title) {
					toggle.title = toggle.title.replace(/\s*\(\d+\)$/, '') + (count > 0 ? ' (' + count + ')' : '');
				}
			}
			var headCount = box.querySelector('.dropdown--header h4 .count');
			if (headCount) {
				headCount.textContent = count > 0 ? ' (' + count + ')' : '';
			}
		});
		document.title = document.title.replace(/^\(\d+\)\s*/, '');
		if (count > 0) {
			document.title = '(' + count + ') ' + document.title;
		}
	}

	var bellUnread = -1;

	/**
	 * Reload the content of the notification dropdown (prosilver and ForumUS)
	 * so the new notification is listed without reloading the page
	 */
	function refreshBellList(count) {
		if (typeof count !== 'number' || count === bellUnread) {
			return;
		}
		bellUnread = count;
		var selectors = ['#notification_list .dropdown-contents', '.header-member--notifications .dropdown-menu'];
		if (!document.querySelector(selectors.join(',')) || !window.DOMParser) {
			return;
		}
		$.ajax({ url: window.location.href.split('#')[0], type: 'GET', dataType: 'text' }).done(function (html) {
			var doc;
			try {
				doc = new DOMParser().parseFromString(html, 'text/html');
			} catch (e) {
				return;
			}
			selectors.forEach(function (sel) {
				var fresh = doc.querySelector(sel);
				Array.prototype.forEach.call(document.querySelectorAll(sel), function (box) {
					if (!fresh) {
						return;
					}
					box.innerHTML = fresh.innerHTML;
					// bind phpBB's AJAX links again (mark as read, mark all read)
					if (window.phpbb && phpbb.ajaxify) {
						$(box).find('[data-ajax]').each(function () {
							var $a = $(this), fn = $a.attr('data-ajax');
							if (fn !== 'false') {
								phpbb.ajaxify({ selector: this, refresh: $a.attr('data-refresh') !== undefined, callback: fn !== 'true' ? fn : null });
							}
						});
					}
				});
			});
		});
	}

	var reminderTimer = null;

	/**
	 * While the page is open, wake up when the next reminder expires:
	 * the server sends the bell notification, the popup is shown at once.
	 */
	function scheduleReminders() {
		if (reminderTimer) {
			clearTimeout(reminderTimer);
			reminderTimer = null;
		}
		if (!D.can.reminder) {
			return;
		}
		var now = Date.now(), next = 0;
		state.order.forEach(function (id) {
			var t = (state.notes[id].reminder || 0) * 1000;
			if (t > now && (!next || t < next)) {
				next = t;
			}
		});
		if (!next) {
			return;
		}
		var delay = next - now + 1500;
		if (delay > 2147000000) {
			return;
		}
		reminderTimer = setTimeout(checkDue, delay);
	}

	function checkDue() {
		reminderTimer = null;
		ajax('due', { page: D.page }, function (r) {
			var dueIds = (r.due || []).map(function (d) { return d.id; });
			if (r.notes) {
				loadNotes(r.notes);
				renderAll();
			}
			setBell(r.unread);
			refreshBellList(r.unread);
			if (r.due && r.due.length) {
				showDuePopups(r.due);
			}
			// make the expired notes blink, even with "bell only"
			state.order.forEach(function (id) {
				var n = state.notes[id];
				if (dueIds.indexOf(id) > -1 || (n.reminder && n.reminder * 1000 <= Date.now())) {
					var node = findNode(id, true);
					if (node) {
						flash(node);
					}
				}
			});
			scheduleReminders();
		});
	}

	/* ------------------------------------------------------------------
	 * Keyboard shortcut: Alt + Shift + N
	 * ------------------------------------------------------------------ */
	document.addEventListener('keydown', function (e) {
		if (e.altKey && e.shiftKey && (e.key === 'N' || e.key === 'n' || e.code === 'KeyN')) {
			e.preventDefault();
			createNote();
		} else if (e.key === 'Escape' && state.open && !e.target.closest('.sn-form')) {
			setTray(false);
		}
	});

	/* ------------------------------------------------------------------
	 * Init
	 * ------------------------------------------------------------------ */
	loadNotes(D.notes);
	(D.shared || []).forEach(function (n) {
		state.shared[n.id] = n;
	});
	if (!Object.keys(state.shared).length) {
		tabs.classList.add('sn-tabs-single');
	}
	renderAll();
	computeBaseOffset();
	placeTray();
	setTimeout(function () {	// sticky headers and late widgets
		computeBaseOffset();
		placeTray();
	}, 1200);
	setTray(storeGet(STORE_KEY) === '1');
	showDuePopups();

	if (D.open) {
		if (state.notes[D.open]) {
			openOwn(D.open);
		} else if (state.shared[D.open]) {
			openShared(D.open);
		}
	}
})(window.jQuery, window, document);

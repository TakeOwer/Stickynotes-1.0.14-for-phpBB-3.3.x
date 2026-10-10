/**
 * Sticky Notes - ACP pages (Management, Maintenance)
 *
 * Loaded with INCLUDEJS, so phpBB prints it in {$SCRIPTS} AFTER jQuery and core.js
 * (the ACP loads jQuery at the end of the page: inline scripts in the template
 * would run before it and fail).
 *
 * @copyright (c) 2026, Salvo Cortesiano, https://netshadows.de/ombra
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
(function ($, window, document) {
	'use strict';

	var dataEl = document.getElementById('sn-acp-data');
	if (!dataEl || !$ || !window.phpbb) {
		return;
	}

	var D;
	try {
		D = JSON.parse(dataEl.textContent);
	} catch (e) {
		return;
	}
	var L = D.lang || {};

	/* ------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */
	function lang(key) {
		return L[key] !== undefined ? L[key] : key;
	}

	function esc(s) {
		return $('<div>').text(String(s === undefined || s === null ? '' : s)).html();
	}

	function format(str, args) {
		var i = 0;
		return String(str).replace(/%(\d+\$)?[sd]/g, function (m, pos) {
			var idx = pos ? parseInt(pos, 10) - 1 : i++;
			return args[idx] !== undefined ? args[idx] : '';
		});
	}

	// phpBB AJAX confirm box: the Yes/No buttons must be part of the message
	function confirmBox(messageHtml, onYes, onNo) {
		var html = '<h3>' + esc(lang('ACP_STICKYNOTES_TITLE')) + '</h3><p>' + messageHtml + '</p>' +
			'<fieldset class="submit-buttons">' +
			'<input type="button" name="confirm" value="' + esc(lang('YES')) + '" class="button1" />&nbsp;' +
			'<input type="button" name="cancel" value="' + esc(lang('NO')) + '" class="button2" />' +
			'</fieldset>';
		phpbb.confirm(html, function (ok) {
			if (ok) {
				onYes();
			} else if (onNo) {
				onNo();
			}
		});
	}

	function alertBox(title, html) {
		phpbb.alert(title || lang('INFORMATION'), html);
	}

	function fail(msg) {
		phpbb.alert(lang('ACP_STICKYNOTES_AJAX_ERROR_TITLE'), esc(msg || lang('ACP_STICKYNOTES_AJAX_ERROR')));
	}

	// Reads the JSON answer even if a debug notice was printed before it
	function parseResponse(text) {
		try {
			return JSON.parse(text);
		} catch (e) {
			var start = String(text).indexOf('{"success"');
			if (start > -1) {
				try {
					return JSON.parse(String(text).slice(start));
				} catch (e2) {
					// fall through
				}
			}
			return $.Deferred().reject().promise();
		}
	}

	function post(data) {
		return $.ajax({ url: D.ajax, type: 'POST', data: data, dataType: 'text', cache: false }).then(parseResponse);
	}

	// Immediate feedback on every button: disabled + "Working…"
	function busy($btn, on) {
		if (!$btn || !$btn.length) {
			return;
		}
		if (on) {
			if ($btn.data('sn-label') === undefined) {
				$btn.data('sn-label', $btn.is('input') ? $btn.val() : $btn.text());
			}
			$btn.prop('disabled', true).addClass('sn-acp-busy');
			if ($btn.is('input')) {
				$btn.val(lang('ACP_STICKYNOTES_WORKING'));
			} else {
				$btn.text(lang('ACP_STICKYNOTES_WORKING'));
			}
		} else {
			$btn.prop('disabled', false).removeClass('sn-acp-busy');
			if ($btn.data('sn-label') !== undefined) {
				if ($btn.is('input')) {
					$btn.val($btn.data('sn-label'));
				} else {
					$btn.text($btn.data('sn-label'));
				}
			}
		}
	}

	/* ------------------------------------------------------------------
	 * Progress panel (shown as soon as a button is clicked)
	 * ------------------------------------------------------------------ */
	function Runner($box) {
		this.$box = $box;
		this.$title = $box.find('.sn-acp-runner-title');
		this.$bar = $box.find('.sn-acp-progress');
		this.$fill = this.$bar.children('div');
		this.$status = $box.find('.sn-acp-runner-status');
	}

	Runner.prototype.start = function (title, status) {
		this.$box.removeClass('done error').addClass('active');
		this.$title.text(title);
		this.$bar.addClass('indeterminate');
		this.$fill.css('width', '100%').text('');
		this.$status.text(status || '');
	};

	Runner.prototype.progress = function (pct, status) {
		this.$bar.removeClass('indeterminate');
		this.$fill.css('width', pct + '%').text(pct + '%');
		if (status !== undefined) {
			this.$status.text(status);
		}
	};

	Runner.prototype.done = function (status) {
		this.progress(100, status);
		this.$box.addClass('done');
	};

	Runner.prototype.error = function (status) {
		this.$bar.removeClass('indeterminate');
		this.$box.addClass('error');
		this.$status.text(status || lang('ACP_STICKYNOTES_AJAX_ERROR'));
	};

	Runner.prototype.hide = function () {
		this.$box.removeClass('active done error');
	};

	/**
	 * Run one maintenance task in batches with the real percentage
	 *
	 * @param {object} data      task + params
	 * @param {int}    total     notes counted beforehand
	 * @param {Runner} runner
	 * @param {object} offset    {done, total} to combine several tasks in one bar
	 */
	function runTask(data, total, runner, offset) {
		var deferred = $.Deferred();
		var processed = 0, affected = 0;
		offset = offset || { done: 0, total: total };

		function step(lastId) {
			post($.extend({ action: 'run', last_id: lastId, total: total, affected: affected }, data)).done(function (r) {
				if (!r || !r.success) {
					deferred.reject(r && r.error);
					return;
				}
				processed += r.processed;
				affected += r.affected;
				var doneAll = offset.done + (r.finished ? total : processed);
				var pct = Math.min(r.finished && offset.done + total >= offset.total ? 100 : 99, Math.round(doneAll / Math.max(1, offset.total) * 100));
				runner.progress(pct, format(lang('ACP_STICKYNOTES_RUNNING'), [Math.min(doneAll, offset.total), offset.total]));

				if (r.finished) {
					deferred.resolve({ affected: affected, shares: r.shares });
				} else {
					step(r.last_id);
				}
			}).fail(function () {
				deferred.reject();
			});
		}

		step(0);
		return deferred.promise();
	}

	function reloadSoon(delay) {
		setTimeout(function () { window.location.reload(); }, delay || 2500);
	}

	/* ==================================================================
	 * MANAGEMENT PAGE
	 * ================================================================== */
	if (D.page === 'management') {
		// Preview of a note
		$(document).on('click', '.sn-view', function (e) {
			e.preventDefault();
			var $a = $(this);
			busy($a, true);
			post({ action: 'view', id: $a.data('id') }).done(function (r) {
				if (!r || !r.success) {
					return fail(r && r.error);
				}
				alertBox(esc(r.title), r.html);
			}).fail(function () {
				fail();
			}).always(function () {
				busy($a, false);
			});
		});

		// Delete a single note
		$(document).on('click', '.sn-delete-one', function (e) {
			e.preventDefault();
			var $a = $(this), id = $a.data('id');
			confirmBox(esc(lang('ACP_STICKYNOTES_CONFIRM_DELETE_ONE')), function () {
				busy($a, true);
				post({ action: 'bulk', op: 'delete', ids: [id] }).done(function (r) {
					if (!r || !r.success) {
						busy($a, false);
						return fail(r && r.error);
					}
					$a.closest('tr').fadeOut(300);
					alertBox('', esc(r.message));
					reloadSoon(1500);
				}).fail(function () {
					busy($a, false);
					fail();
				});
			});
		});

		$('#sn-check-all').on('change', function () {
			$('.sn-check').prop('checked', this.checked);
		});

		// Bulk operations
		$('#sn-bulk-run').on('click', function () {
			var $btn = $(this);
			var ids = $('.sn-check:checked').map(function () { return this.value; }).get();
			var $op = $('#sn-bulk-op');
			if (!ids.length) {
				return alertBox('', esc(lang('ACP_STICKYNOTES_NOTHING_SELECTED')));
			}
			confirmBox(format(lang('ACP_STICKYNOTES_CONFIRM_BULK'), ['<strong>' + esc($op.find('option:selected').text()) + '</strong>', ids.length]), function () {
				busy($btn, true);
				post({ action: 'bulk', op: $op.val(), ids: ids }).done(function (r) {
					if (!r || !r.success) {
						busy($btn, false);
						return fail(r && r.error);
					}
					alertBox('', esc(r.message));
					reloadSoon(1500);
				}).fail(function () {
					busy($btn, false);
					fail();
				});
			});
		});
	}

	/* ==================================================================
	 * MAINTENANCE PAGE
	 * ================================================================== */
	if (D.page === 'maintenance') {
		var toolsRunner = new Runner($('#sn-runner'));
		var autoRunner = new Runner($('#sn-auto-runner'));
		var running = false;

		function lockAll(on) {
			running = on;
			$('.sn-run, #sn-auto-save, #sn-auto-run').prop('disabled', on);
		}

		function taskParams($row, task) {
			var data = { task: task };
			$row.find('[data-param]').each(function () {
				data[$(this).data('param')] = $.trim($(this).val());
			});
			return data;
		}

		// Manual tools: count -> confirm -> run with progress
		$('.sn-run').on('click', function () {
			if (running) {
				return;
			}
			var $btn = $(this), $row = $btn.closest('tr');
			var task = $btn.data('task');
			var title = $row.find('.sn-acp-tool-desc strong').text();
			var data = taskParams($row, task);

			lockAll(true);
			busy($btn, true);
			toolsRunner.start(title, lang('ACP_STICKYNOTES_COUNTING'));

			post($.extend({ action: 'count' }, data)).done(function (r) {
				if (!r || !r.success) {
					finish();
					toolsRunner.error(r && r.error);
					return fail(r && r.error);
				}
				if (!r.count) {
					finish();
					toolsRunner.done(lang('ACP_STICKYNOTES_NOTHING_TO_DO'));
					return;
				}

				toolsRunner.$status.text(format(lang('ACP_STICKYNOTES_FOUND'), [r.count]));
				var msg = format(lang(task === 'all' ? 'ACP_STICKYNOTES_CONFIRM_ALL' : 'ACP_STICKYNOTES_CONFIRM_TASK'),
					['<strong>' + esc(title) + '</strong>', '<strong>' + r.count + '</strong>']);

				confirmBox(msg, function () {
					toolsRunner.progress(0, format(lang('ACP_STICKYNOTES_RUNNING'), [0, r.count]));
					runTask(data, r.count, toolsRunner).done(function (res) {
						toolsRunner.done(format(lang('ACP_STICKYNOTES_TASK_DONE'), [res.affected, res.shares]));
						finish();
						reloadSoon(3000);
					}).fail(function (err) {
						toolsRunner.error(err);
						finish();
						fail(err);
					});
				}, function () {
					finish();
					toolsRunner.hide();
				});
			}).fail(function () {
				finish();
				toolsRunner.error();
				fail();
			});

			function finish() {
				lockAll(false);
				busy($btn, false);
			}
		});

		// Automatic cleanup: save without reloading the page
		function autoValues() {
			return {
				expired_days: parseInt($('#stickynotes_auto_expired_days').val(), 10) || 0,
				inactive_days: parseInt($('#stickynotes_auto_inactive_days').val(), 10) || 0
			};
		}

		function saveAuto() {
			var v = autoValues();
			return post({ action: 'save_auto', expired_days: v.expired_days, inactive_days: v.inactive_days });
		}

		function showAutoStatus(r) {
			if (r && r.status) {
				$('#sn-auto-status').removeClass('ok warn').addClass(r.auto_on ? 'ok' : 'warn')
					.find('.sn-acp-summary-text').text(r.status);
				$('#sn-auto-status .sn-acp-summary-icon').html(r.auto_on ? '&#10004;' : '&#9888;');
			}
		}

		$('#sn-auto-save').on('click', function (e) {
			e.preventDefault();
			if (running) {
				return;
			}
			var $btn = $(this);
			busy($btn, true);
			saveAuto().done(function (r) {
				if (!r || !r.success) {
					return fail(r && r.error);
				}
				showAutoStatus(r);
				$('#sn-auto-saved').text(lang('ACP_STICKYNOTES_AUTO_SAVED')).stop(true, true).show().delay(4000).fadeOut(600);
			}).fail(function () {
				fail();
			}).always(function () {
				busy($btn, false);
			});
		});

		// Automatic cleanup: run it now with the current values
		$('#sn-auto-run').on('click', function (e) {
			e.preventDefault();
			if (running) {
				return;
			}
			var $btn = $(this), v = autoValues();
			if (!v.expired_days && !v.inactive_days) {
				return alertBox('', esc(lang('ACP_STICKYNOTES_AUTO_NOTHING')));
			}

			var tasks = [];
			if (v.expired_days) {
				tasks.push({ task: 'expired_delete', days: v.expired_days });
			}
			if (v.inactive_days) {
				tasks.push({ task: 'inactive', days: v.inactive_days });
			}

			lockAll(true);
			busy($btn, true);
			autoRunner.start(lang('ACP_STICKYNOTES_AUTO'), lang('ACP_STICKYNOTES_COUNTING'));

			function finish() {
				lockAll(false);
				busy($btn, false);
			}

			// Save first, then count every task
			saveAuto().then(function (r) {
				if (!r || !r.success) {
					return $.Deferred().reject(r).promise();
				}
				showAutoStatus(r);
				return $.when.apply($, tasks.map(function (t) {
					return post($.extend({ action: 'count' }, t));
				}));
			}).done(function () {
				// post() resolves with the parsed answer: one argument per task
				var results = Array.prototype.slice.call(arguments, 0, tasks.length);
				var total = 0;
				results.forEach(function (res, i) {
					var data = Array.isArray(res) ? res[0] : res;
					if (!data || !data.success) {
						tasks[i].count = 0;
						return;
					}
					tasks[i].count = data.count || 0;
					total += tasks[i].count;
				});

				if (!total) {
					finish();
					autoRunner.done(lang('ACP_STICKYNOTES_NOTHING_TO_DO'));
					return post({ action: 'auto_mark', affected: 0 }).done(showAutoStatus);
				}

				autoRunner.$status.text(format(lang('ACP_STICKYNOTES_FOUND'), [total]));
				confirmBox(format(lang('ACP_STICKYNOTES_AUTO_CONFIRM'), ['<strong>' + total + '</strong>']), function () {
					var done = 0, affected = 0, chain = $.Deferred().resolve().promise();
					autoRunner.progress(0, format(lang('ACP_STICKYNOTES_RUNNING'), [0, total]));

					tasks.forEach(function (t) {
						chain = chain.then(function () {
							if (!t.count) {
								return $.Deferred().resolve().promise();
							}
							return runTask({ task: t.task, days: t.days }, t.count, autoRunner, { done: done, total: total }).done(function (res) {
								done += t.count;
								affected += res.affected;
							});
						});
					});

					chain.then(function () {
						return post({ action: 'auto_mark', affected: affected });
					}).done(function (r) {
						showAutoStatus(r);
						autoRunner.done(format(lang('ACP_STICKYNOTES_AUTO_DONE'), [affected]));
						finish();
						reloadSoon(3000);
					}).fail(function (err) {
						autoRunner.error(err);
						finish();
						fail(err);
					});
				}, function () {
					finish();
					autoRunner.hide();
				});
			}).fail(function (r) {
				finish();
				autoRunner.error();
				fail(r && r.error);
			});
		});

		// Export: feedback while the browser starts the download
		$('#sn-export').on('click', function () {
			var $btn = $(this);
			busy($btn, true);
			setTimeout(function () { busy($btn, false); }, 2500);
		});
	}
})(window.jQuery, window, document);

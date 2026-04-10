/**
 * In-app modals replacing alert() / confirm().
 * Requires partial app-ui-modals markup and app-ui-modals.css.
 */
(function () {
  var zBase = 10050;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function trapFocus(overlay, firstFocus) {
    function onKey(e) {
      if (e.key !== 'Tab') return;
      var focusables = overlay.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
      );
      var list = Array.prototype.slice.call(focusables).filter(function (el) {
        return el.offsetParent !== null;
      });
      if (list.length === 0) return;
      var i = list.indexOf(document.activeElement);
      if (e.shiftKey) {
        if (i <= 0) {
          e.preventDefault();
          list[list.length - 1].focus();
        }
      } else {
        if (i === -1 || i === list.length - 1) {
          e.preventDefault();
          list[0].focus();
        }
      }
    }
    overlay._auiTrap = onKey;
    overlay.addEventListener('keydown', onKey);
    if (firstFocus) setTimeout(function () { firstFocus.focus(); }, 10);
  }

  function releaseTrap(overlay) {
    if (overlay._auiTrap) {
      overlay.removeEventListener('keydown', overlay._auiTrap);
      overlay._auiTrap = null;
    }
  }

  var _prevActive = null;

  function openOverlay(el) {
    _prevActive = document.activeElement;
    el.classList.add('aui-open');
    document.body.style.overflow = 'hidden';
  }

  function closeOverlay(el) {
    el.classList.remove('aui-open');
    document.body.style.overflow = '';
    releaseTrap(el);
    if (_prevActive && typeof _prevActive.focus === 'function') {
      try { _prevActive.focus(); } catch (e) {}
    }
  }

  window.appUiAlert = function (message, opts) {
    opts = opts || {};
    var title = opts.title || 'Notice';
    var ov = document.getElementById('appUiAlertModal');
    if (!ov) {
      window.alert(message);
      return;
    }
    var tEl = document.getElementById('appUiAlertTitle');
    var bEl = document.getElementById('appUiAlertBody');
    if (tEl) tEl.textContent = title;
    if (bEl) bEl.textContent = message;
    var ok = document.getElementById('appUiAlertOk');
    function cleanup() {
      closeOverlay(ov);
      ok.removeEventListener('click', onOk);
      ov.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onEsc);
      if (typeof opts.onClose === 'function') opts.onClose();
    }
    function onOk() { cleanup(); }
    function onBackdrop(e) {
      if (e.target === ov) onOk();
    }
    function onEsc(e) {
      if (e.key === 'Escape') { e.preventDefault(); onOk(); }
    }
    ok.addEventListener('click', onOk);
    ov.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onEsc);
    openOverlay(ov);
    trapFocus(ov, ok);
  };

  window.appUiConfirm = function (message, opts) {
    opts = opts || {};
    var ov = document.getElementById('appUiConfirmModal');
    if (!ov) {
      if (window.confirm(message)) {
        if (opts.onConfirm) opts.onConfirm();
      } else if (opts.onCancel) opts.onCancel();
      return;
    }
    var bEl = document.getElementById('appUiConfirmBody');
    if (bEl) bEl.textContent = message;
    var btnOk = document.getElementById('appUiConfirmOk');
    var btnCancel = document.getElementById('appUiConfirmCancel');
    function cleanup() {
      closeOverlay(ov);
      btnOk.removeEventListener('click', onOk);
      btnCancel.removeEventListener('click', onCancel);
      ov.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onEsc);
    }
    function onOk() {
      cleanup();
      if (opts.onConfirm) opts.onConfirm();
    }
    function onCancel() {
      cleanup();
      if (opts.onCancel) opts.onCancel();
    }
    function onBackdrop(e) {
      if (e.target === ov) onCancel();
    }
    function onEsc(e) {
      if (e.key === 'Escape') { e.preventDefault(); onCancel(); }
    }
    btnOk.addEventListener('click', onOk);
    btnCancel.addEventListener('click', onCancel);
    ov.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onEsc);
    openOverlay(ov);
    trapFocus(ov, btnCancel);
  };

  window.appUiSuccess = function (opts) {
    opts = opts || {};
    var ov = document.getElementById('appUiSuccessModal');
    if (!ov) return;
    var title = opts.title || 'Success';
    var msg = opts.message || 'Report has been submitted successfully!';
    var ticketRaw = opts.ticketId || opts.ticket || '';
    var ticketDisplay = opts.ticketDisplay;
    if (ticketDisplay == null || ticketDisplay === '') {
      ticketDisplay = ticketRaw
        ? (window.appUiFormatTicketDisplay ? window.appUiFormatTicketDisplay(ticketRaw) : ticketRaw)
        : '';
    }

    var h = document.getElementById('appUiSuccessTitle');
    var m = document.getElementById('appUiSuccessMessage');
    var t = document.getElementById('appUiSuccessTicket');
    var x = document.getElementById('appUiSuccessClose');
    var btn = document.getElementById('appUiSuccessDismiss');
    if (h) h.textContent = title;
    if (m) m.textContent = msg;
    if (t) {
      if (ticketDisplay) {
        t.style.display = 'block';
        t.textContent = ticketDisplay;
      } else {
        t.style.display = 'none';
        t.textContent = '';
      }
    }
    function cleanup() {
      closeOverlay(ov);
      x.removeEventListener('click', onDismiss);
      btn.removeEventListener('click', onDismiss);
      ov.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onEsc);
      if (typeof opts.onClose === 'function') opts.onClose();
    }
    function onDismiss() { cleanup(); }
    function onBackdrop(e) {
      if (e.target === ov) onDismiss();
    }
    function onEsc(e) {
      if (e.key === 'Escape') { e.preventDefault(); onDismiss(); }
    }
    x.addEventListener('click', onDismiss);
    btn.addEventListener('click', onDismiss);
    ov.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onEsc);
    openOverlay(ov);
    trapFocus(ov, btn);
  };

  /** Format REF-… id to TIC-… for display (matches Item::display_ticket_id). */
  window.appUiFormatTicketDisplay = function (refId) {
    if (!refId || String(refId).indexOf('REF-') !== 0) return refId || '';
    return 'TIC-' + String(refId).slice(4);
  };
})();

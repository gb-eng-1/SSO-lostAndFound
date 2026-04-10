@php
  $isAdmin = ($role ?? 'admin') === 'admin';
  $recentUrl = $isAdmin ? route('admin.notifications.recent') : route('student.notifications.recent');
  $readAllUrl = $isAdmin ? route('admin.notifications.read-all') : route('student.notifications.read-all');
  $listUrl = $isAdmin ? route('admin.notifications') : route('student.notifications');
  $markReadPrefix = $isAdmin ? url('/admin/notifications') : url('/student/notifications');
  $mapLink = $isAdmin
    ? [
        'item_encoded' => route('admin.found'),
        'item_claimed' => route('admin.history'),
        'claim_submitted' => route('admin.matched'),
        'item_matched' => route('admin.matched'),
        'lost_report_submitted' => route('admin.reports'),
      ]
    : [
        'item_matched' => route('student.dashboard'),
        'claim_approved' => route('student.claim-history'),
        'claim_rejected' => route('student.claim-history'),
      ];
@endphp
<div class="notif-bell-wrap notif-dropdown" id="notifBellWrap">
  <div class="notif-bell-trigger-row">
    <button type="button" class="notif-trigger" id="notifBellBtn" title="Notifications" aria-label="Notifications">
      <i class="fa-regular fa-bell"></i>
    </button>
    <span class="notif-badge" id="notifBadge" style="display:none;" aria-live="polite"></span>
  </div>
  <div id="notifPanel" class="notif-panel">
    <div class="notif-panel-header">
      <span class="notif-panel-title">Notifications</span>
      <div class="notif-panel-header-actions">
        <button type="button" class="notif-mark-all-btn" id="notifMarkAllBtn">Mark all as read</button>
        <a href="{{ $listUrl }}" class="notif-view-all-link">View All</a>
      </div>
    </div>
    <div id="notifList" class="notif-panel-scroll">
      <p class="notif-empty notif-loading-msg">Loading…</p>
    </div>
  </div>
</div>
<script>
(function(){
  var bellBtn = document.getElementById('notifBellBtn');
  var panel = document.getElementById('notifPanel');
  var list = document.getElementById('notifList');
  var badge = document.getElementById('notifBadge');
  var markAllBtn = document.getElementById('notifMarkAllBtn');
  if (!bellBtn || !panel || !list) return;

  var notifUrl = @json($recentUrl);
  var readAllUrl = @json($readAllUrl);
  var markReadPrefix = @json($markReadPrefix);
  var listUrl = @json($listUrl);
  var mapLink = @json($mapLink);
  var isAdminBell = @json($isAdmin);
  var csrf = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrf ? csrf.content : '';

  var loaded = false;

  function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function resolveLink(type) {
    if (mapLink[type]) return mapLink[type];
    return listUrl;
  }

  function markReadUrl(id) {
    return markReadPrefix + '/' + encodeURIComponent(id) + '/read';
  }

  function postJson(url, body) {
    var payload = Object.assign({ _token: csrfToken }, body || {});
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    }).then(function(r) {
      if (!r.ok) return Promise.reject(new Error('HTTP ' + r.status));
      var ct = r.headers.get('content-type') || '';
      if (ct.indexOf('application/json') !== -1) return r.json();
      return { ok: true };
    });
  }

  function renderNotifications(data) {
    if (data.unread_count > 0) {
      badge.textContent = data.unread_count > 99 ? '99+' : String(data.unread_count);
      badge.style.display = 'inline-flex';
      badge.setAttribute('aria-label', data.unread_count + ' unread notifications');
    } else {
      badge.style.display = 'none';
      badge.removeAttribute('aria-label');
    }
    if (!data.notifications || !data.notifications.length) {
      list.innerHTML = '<p class="notif-empty">No notifications yet.</p>';
      return;
    }
    list.innerHTML = data.notifications.map(function(n) {
      var unread = !n.is_read;
      var turl = n.thumbnail_url;
      var thumb = turl
        ? '<img class="notif-thumb-img" src="'+String(turl).replace(/"/g, '')+'" alt="">'
        : '<div class="notif-thumb-placeholder"><i class="fa-regular fa-image"></i></div>';
      var newBadge = unread ? '<span class="notif-item-new-badge">New</span>' : '';
      var timeStr = esc(n.time_relative || '');
      var rid = n.related_id ? String(n.related_id) : '';
      var viewLink = rid
        ? '<a href="#" class="notif-view-link" data-related-id="'+esc(rid)+'">View Details</a>'
        : '<a href="'+esc(resolveLink(n.type))+'" class="notif-view-link notif-view-link--route">View Details</a>';
      return '<div class="notif-card'+(unread ? ' notif-card--unread' : ' notif-card--read')+'" data-notif-id="'+esc(n.id)+'">'
        + '<div class="notif-item-thumb">'+thumb+'</div>'
        + '<div class="notif-item-body">'
          + '<div class="notif-item-top">'
            + '<span class="notif-item-title">'+esc(n.title)+'</span>'
            + newBadge
            + '<span class="notif-item-time">'+timeStr+'</span>'
          + '</div>'
          + '<div class="notif-item-message">'+esc(n.message)+' '
            + viewLink
          + '</div>'
        + '</div>'
        + '<button type="button" class="notif-mark-read-btn" data-mark-read="'+esc(n.id)+'" title="Mark as read" aria-label="Mark as read">'
          + '<i class="fa-solid fa-check"></i>'
        + '</button>'
      + '</div>';
    }).join('');

    list.querySelectorAll('[data-mark-read]').forEach(function(btn) {
      btn.addEventListener('click', function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        var id = btn.getAttribute('data-mark-read');
        postJson(markReadUrl(id)).then(function() { loadNotifications(); }).catch(function() { loadNotifications(); });
      });
    });
  }

  list.addEventListener('click', function(ev) {
    var link = ev.target.closest('a.notif-view-link[data-related-id]');
    if (!link) return;
    ev.preventDefault();
    var rid = link.getAttribute('data-related-id');
    if (!rid) return;
    if (panel) panel.classList.remove('open');
    if (isAdminBell) {
      if (typeof window.showItemDetailsModal === 'function') window.showItemDetailsModal(rid);
      return;
    }
    if (typeof window.openStudentItemFromSearch === 'function') window.openStudentItemFromSearch(rid);
  });

  function loadNotifications() {
    fetch(notifUrl, {
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (!data.ok) return;
        renderNotifications(data);
      })
      .catch(function() {});
  }

  bellBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) {
      if (!loaded) { loaded = true; }
      loadNotifications();
    }
  });

  if (markAllBtn) {
    markAllBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      postJson(readAllUrl).then(function() { loadNotifications(); }).catch(function() { loadNotifications(); });
    });
  }

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#notifBellWrap')) {
      panel.classList.remove('open');
    }
  });

  loadNotifications();
  setInterval(loadNotifications, 60000);
})();
</script>

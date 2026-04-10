@extends('layouts.admin')

@section('title', 'Reports')

@push('styles')
  <link rel="stylesheet" href="{{ asset('ADMIN/AdminReports.css') }}?v=4">
@endpush

@section('content')

<div class="dashboard-header-row">
  <h1 class="page-title">Reports</h1>
</div>

{{-- Two-column grid: main content + Recent Activity sidebar --}}
<div class="reports-2col-grid">

  {{-- Main content --}}
  <div class="reports-main-col">
    <div class="browse-toolbar">
      <div class="report-tabs report-tabs--browse">
        <span class="report-tab active">All Items</span>
      </div>
      <form method="GET" action="{{ route('admin.reports') }}" class="browse-filter-form">
        <div class="browse-filter-filters">
          <label class="sr-only" for="adminReportsCategory">Filter by category</label>
          <select name="category" id="adminReportsCategory" class="found-filter-select browse-filter-select" aria-label="Filter by category" onchange="this.form.submit()">
            <option value="">Filter By Category</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" {{ $categoryFilter === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
        </div>
        @if($search)
          <input type="hidden" name="search" value="{{ $search }}">
        @endif
      </form>
    </div>

    {{-- Unresolved Claimants table --}}
    <div class="inventory-card reports-card">
      <div class="inventory-title">Unresolved Claimants</div>
      <div class="table-wrapper">
        <table class="reports-table">
          <thead>
            <tr>
              <th>Ticket ID</th>
              <th>Category</th>
              <th>Department</th>
              <th>ID</th>
              <th>Contact Number</th>
              <th>Date Lost</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
              <tr data-report-id="{{ $report->id }}">
                <td>{{ $report->display_ticket_id }}</td>
                <td>{{ $report->item_type ?? '—' }}</td>
                <td>{{ $report->parsed_department ?? '—' }}</td>
                <td>{{ $report->parsed_student_number ?? '—' }}</td>
                <td>{{ $report->parsed_contact ?? '—' }}</td>
                <td>{{ $report->date_lost ? $report->date_lost->format('Y-m-d') : '—' }}</td>
                <td class="reports-action-cell">
                  <button type="button" class="reports-btn reports-btn-view btn-view-report" data-id="{{ $report->id }}">View</button>
                  <button type="button" class="reports-btn reports-btn-cancel btn-cancel-report" data-id="{{ $report->id }}" data-ticket="{{ $report->display_ticket_id }}">Cancel</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="table-empty">No unresolved claimants.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div style="margin-top:16px;">
      {{ $reports->withQueryString()->links() }}
    </div>
  </div>

  {{-- Recent Activity sidebar --}}
  <div class="reports-sidebar-col">
    <div class="activity-card">
      <h3 class="activity-title">Recent Activity</h3>
      <div class="activity-list">
        @forelse($recentActivity as $act)
          @php
            $action   = strtolower($act['action'] ?? 'found');
            $itemId   = $act['item_id'] ?? '';
            $itemName = $act['item_name'] ?? '';
            $displayId = str_starts_with($itemId, 'REF-') ? 'TIC-' . substr($itemId, 4) : $itemId;
            $dt       = isset($act['created_at']) && $act['created_at']
                ? \Carbon\Carbon::parse($act['created_at'])->format('M d, Y \a\t g:i A')
                : '';
          @endphp
          <div class="activity-item">
            @if($action === 'matched' || $action === 'match')
              <p class="activity-label">Potential Match!</p>
              <p class="activity-text">
                <a href="#" class="activity-item-link" data-item-id="{{ $itemId }}">{{ $displayId }}{{ $itemName && $itemName !== $itemId ? ' (' . $itemName . ')' : '' }}</a>
                matched a user report.
              </p>
            @elseif($action === 'lost' || $action === 'reported')
              <p class="activity-label">Lost Item!</p>
              <p class="activity-text">
                an Item with a Ticket ID of <a href="#" class="activity-item-link" data-item-id="{{ $itemId }}">{{ $displayId }}</a> has been reported to be lost.
              </p>
            @endif
            @if($dt)
              <p class="activity-time">{{ $dt }}</p>
            @endif
          </div>
        @empty
          <p class="activity-empty">No recent activity.</p>
        @endforelse
      </div>
    </div>
  </div>

</div>{{-- /reports-2col-grid --}}

{{-- Item Details modal --}}
<div id="reportsItemModal" class="reports-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="reportsModalTitle" style="display:none;">
  <div class="reports-modal" onclick="event.stopPropagation()">
    <div class="reports-modal-header">
      <h3 id="reportsModalTitle" class="reports-modal-title">Item Details</h3>
      <button type="button" class="reports-modal-close" aria-label="Close" title="Close">&times;</button>
    </div>
    <div class="reports-modal-content">
      <div class="reports-modal-left">
        <div id="reportsModalImage" class="reports-modal-image"></div>
        <div id="reportsModalTicketId" class="reports-modal-ticket-id"></div>
      </div>
      <div class="reports-modal-right">
        <h4 class="reports-modal-section-title">General Information</h4>
        <div id="reportsModalBody" class="reports-modal-body"></div>
      </div>
    </div>
  </div>
</div>

{{-- Cancel report confirmation --}}
<div class="reports-cancel-overlay" id="reportsCancelConfirmModal" style="display:none;" role="dialog" aria-modal="true" aria-hidden="true"
     onclick="if(event.target===this)window._reportsCloseCancelConfirm&&window._reportsCloseCancelConfirm()">
  <div class="reports-cancel-dialog" onclick="event.stopPropagation()">
    <h3 class="reports-cancel-hdr">Cancel report</h3>
    <div class="reports-cancel-body">
      <p style="margin:0 0 10px;">Cancel lost report <strong id="reportsCancelTicketLabel">—</strong>?</p>
      <p style="margin:0;font-size:13px;color:#6b7280;">This sets status to <strong>Cancelled</strong> and removes it from this list.</p>
    </div>
    <div class="reports-cancel-foot">
      <button type="button" class="reports-cancel-btn reports-cancel-btn--secondary" id="reportsCancelBack">Go back</button>
      <button type="button" class="reports-cancel-btn reports-cancel-btn--danger" id="reportsCancelSubmit">Confirm cancel</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
  var modal = document.getElementById('reportsItemModal');
  var imageEl = document.getElementById('reportsModalImage');
  var ticketEl = document.getElementById('reportsModalTicketId');
  var bodyEl = document.getElementById('reportsModalBody');
  var itemUrl = '{{ route('admin.item') }}';
  var _csrf = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = _csrf ? _csrf.content : '';
  var cancelModal = document.getElementById('reportsCancelConfirmModal');
  var cancelTicketEl = document.getElementById('reportsCancelTicketLabel');
  var _cancelReportId = null;
  var _cancelTriggerBtn = null;

  function esc(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function row(label, value) {
    var v = (!value || value === '-' || value === 'null') ? '—' : value;
    return '<div class="reports-modal-row"><span class="reports-modal-label">' + esc(label) + '</span><span class="reports-modal-value">' + esc(String(v)) + '</span></div>';
  }

  function openModal(itemId) {
    if (!itemId) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    imageEl.innerHTML = '<div class="reports-modal-loading"><i class="fa-solid fa-spinner fa-spin"></i><span>Loading...</span></div>';
    ticketEl.textContent = '';
    bodyEl.innerHTML = '';

    fetch(itemUrl + '?id=' + encodeURIComponent(itemId), {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
      .then(function(r) { return r.json(); })
      .then(function(json) {
        if (!json.ok) {
          bodyEl.innerHTML = '<p class="reports-modal-error">' + esc(json.error || 'Item not found.') + '</p>';
          imageEl.innerHTML = '';
          ticketEl.textContent = '';
          return;
        }
        var item = json.data;
        var parsed = item.parsed || {};
        var displayId = item.display_ticket_id || item.id;

        if (item.image_data) {
          imageEl.innerHTML = '<img src="' + esc(item.image_data) + '" alt="Item">';
        } else {
          imageEl.innerHTML = '<div class="reports-modal-image-placeholder"><i class="fa-solid fa-box-open"></i><span>No image</span></div>';
        }
        ticketEl.textContent = displayId;

        var html = row('Category', item.item_type)
          + row('Full Name', parsed.full_name)
          + row('Contact Number', parsed.contact)
          + row('Department', parsed.department)
          + row('ID', parsed.student_number)
          + row('Item', parsed.item || item.item_type)
          + row('Color', item.color)
          + row('Brand', item.brand)
          + row('Item Description', parsed.clean_description || item.item_description)
          + row('Date Lost', item.date_lost ? (typeof item.date_lost === 'string' ? item.date_lost : item.date_lost.split(' ')[0]) : null);
        bodyEl.innerHTML = html;
      })
      .catch(function() {
        bodyEl.innerHTML = '<p class="reports-modal-error">Could not load item details.</p>';
        imageEl.innerHTML = '';
        ticketEl.textContent = '';
      });
  }

  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.btn-view-report').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var id = this.getAttribute('data-id');
      if (id) openModal(id);
    });
  });

  document.querySelectorAll('.activity-item-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var id = this.getAttribute('data-item-id');
      if (id) openModal(id);
    });
  });

  if (modal) {
    modal.querySelector('.reports-modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
  }
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

  function openCancelConfirm(id, ticketLabel, triggerBtn) {
    if (!cancelModal || !cancelTicketEl) return;
    _cancelReportId = id;
    _cancelTriggerBtn = triggerBtn || null;
    cancelTicketEl.textContent = ticketLabel || id;
    cancelModal.style.display = 'flex';
    cancelModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
  function closeCancelConfirm() {
    if (!cancelModal) return;
    cancelModal.style.display = 'none';
    cancelModal.setAttribute('aria-hidden', 'true');
    if (modal && modal.style.display !== 'flex') document.body.style.overflow = '';
    _cancelReportId = null;
    _cancelTriggerBtn = null;
  }
  window._reportsCloseCancelConfirm = closeCancelConfirm;

  document.querySelectorAll('.btn-cancel-report').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var id = this.getAttribute('data-id');
      var ticket = this.getAttribute('data-ticket') || id;
      if (id) openCancelConfirm(id, ticket, this);
    });
  });

  var cancelBack = document.getElementById('reportsCancelBack');
  var cancelSubmit = document.getElementById('reportsCancelSubmit');
  if (cancelBack) cancelBack.addEventListener('click', closeCancelConfirm);
  if (cancelSubmit) {
    cancelSubmit.addEventListener('click', function() {
      var id = _cancelReportId;
      var btn = this;
      if (!id) return;
      btn.disabled = true;
      var prevText = btn.textContent;
      btn.textContent = 'Cancelling…';
      fetch('{{ url('/admin/reports') }}/' + encodeURIComponent(id) + '/cancel', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
      }).then(function(r) {
        return r.json().then(function(data) {
          if (!r.ok) throw new Error(data.error || ('HTTP ' + r.status));
          return data;
        });
      }).then(function(data) {
        if (data.ok) {
          var trigger = _cancelTriggerBtn;
          closeCancelConfirm();
          if (trigger) {
            var row = trigger.closest('tr');
            if (row) row.remove();
            var tbody = document.querySelector('.reports-table tbody');
            if (tbody && !tbody.querySelector('tr[data-report-id]')) {
              tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No unresolved claimants.</td></tr>';
            }
          } else location.reload();
        }
      }).catch(function(err) {
        var m = (err && err.message) ? err.message : 'Could not cancel report.';
        if (typeof window.appUiAlert === 'function') window.appUiAlert(m); else alert(m);
      }).finally(function() {
        btn.disabled = false;
        btn.textContent = prevText;
      });
    });
  }
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && cancelModal && cancelModal.style.display === 'flex') closeCancelConfirm();
  });
})();
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Reports')

@push('styles')
  <link rel="stylesheet" href="{{ asset('ADMIN/AdminReports.css') }}?v=4">
  <style>
    .reports-encode-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .found-btn-encode-blue{background:#2563eb!important;color:#fff!important;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;}
    .found-btn-encode-blue:hover{background:#1d4ed8!important;}
  </style>
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
      <div class="report-tabs report-tabs--browse reports-encode-toolbar">
        <span class="report-tab active">All Items</span>
        <button type="button" class="found-btn-encode-blue" id="encodeReportBtn">
          <i class="fa-solid fa-file-lines"></i> Encode Report
        </button>
      </div>
      <form method="GET" action="{{ route('admin.reports') }}" class="browse-filter-form">
        <div class="browse-filter-filters">
          <label class="sr-only" for="adminReportsCategory">Filter by category</label>
          <select name="category" id="adminReportsCategory" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by category" onchange="this.form.submit()">
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

@include('partials.admin-encode-report-modal', ['categoriesInternal' => $categoriesInternal])

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
  document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    var arm = document.getElementById('adminEncodeReviewModal');
    if (arm && arm.classList.contains('report-modal-open')) return;
    var enc = document.getElementById('encodeReportModal');
    if (enc && enc.classList.contains('report-modal-open')) {
      if (typeof closeEncodeReportModal === 'function') closeEncodeReportModal();
      return;
    }
    closeModal();
  });

})();
</script>
<script>
(function() {
  var m = document.querySelector('meta[name="csrf-token"]');
  window._CSRF = m ? m.getAttribute('content') : '';
})();
var _encReportPhoto = null;
var _encReportPP = null;

function _reportsAppAlert(msg) {
  if (typeof window.appUiAlert === 'function') window.appUiAlert(msg);
  else alert(msg);
}
function _reportsEscRev(s) {
  return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function _reportsRowRev(label, val) {
  if (val == null || val === '') val = '—';
  return '<div class="report-form-row" style="margin-bottom:8px;"><span class="report-form-label" style="display:inline-block;min-width:170px;">' + _reportsEscRev(label) + '</span><span style="color:#111827;">' + _reportsEscRev(String(val)) + '</span></div>';
}
function buildEncodeReportReviewHtml() {
  var img = '';
  if (typeof _encReportPhoto !== 'undefined' && _encReportPhoto) {
    img = '<div class="report-form-row"><span class="report-form-label">Photo</span><span><img src="' + _reportsEscRev(_encReportPhoto) + '" alt="" style="max-width:200px;border-radius:8px;border:1px solid #e5e7eb;"></span></div>';
  }
  var cat = (document.getElementById('repCategory') || {}).value || '';
  var rows = [
    _reportsRowRev('Student Email', (document.getElementById('repStudentEmail') || {}).value),
    _reportsRowRev('Category', cat),
  ];
  if (cat === 'Document & Identification') {
    rows.push(_reportsRowRev('Document Type', (document.getElementById('repDocType') || {}).value));
  }
  rows.push(
    _reportsRowRev('Full Name', (document.getElementById('repFullName') || {}).value),
    _reportsRowRev('Contact Number', (document.getElementById('repContact') || {}).value),
    _reportsRowRev('Department', (document.getElementById('repDept') || {}).value),
    _reportsRowRev('ID', (document.getElementById('repId') || {}).value),
    _reportsRowRev('Item', (document.getElementById('repItem') || {}).value),
    _reportsRowRev('Item Description', (document.getElementById('repDesc') || {}).value),
    _reportsRowRev('Color', (document.getElementById('repColor') || {}).value),
    _reportsRowRev('Brand', (document.getElementById('repBrand') || {}).value),
    _reportsRowRev('Date Lost', (document.getElementById('repDateLost') || {}).value),
    img
  );
  return rows.join('');
}
function _reportsJsonHeaders() {
  return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': window._CSRF };
}
function _reportsParseFetchResponse(r) {
  return r.text().then(function(text) {
    var data = null;
    if (text) { try { data = JSON.parse(text); } catch (e) {} }
    return { ok: r.ok, status: r.status, data: data };
  });
}
function _reportsLaravelErrMsg(res) {
  var d = res.data;
  if (d && typeof d === 'object') {
    if (d.message) return d.message;
    if (d.errors) { for (var k in d.errors) { var v = d.errors[k]; if (v && v.length) return v[0]; } }
    if (d.error) return d.error;
  }
  if (res.status === 419) return 'Page expired. Refresh and try again.';
  return 'Request failed (' + res.status + ').';
}
function closeEncodeReportModal() {
  var el = document.getElementById('encodeReportModal');
  if (el) el.classList.remove('report-modal-open');
  document.body.style.overflow = '';
}
(function() {
  if (typeof PhotoPicker === 'undefined' || !PhotoPicker.init) return;
  try {
    _encReportPP = PhotoPicker.init({ el: 'encodeReportPhotoPicker', onChange: function(d) { _encReportPhoto = d || null; } });
  } catch (e) { console.warn('encodeReportPhotoPicker', e); }
})();
(function() {
  var repCat = document.getElementById('repCategory');
  if (!repCat) return;
  function syncRepDocRow() {
    var row = document.getElementById('repDocTypeRow');
    if (row) row.style.display = repCat.value === 'Document & Identification' ? 'grid' : 'none';
    var elecHint = document.getElementById('repElecHint');
    if (elecHint) elecHint.style.display = repCat.value === 'Electronics & Gadgets' ? '' : 'none';
  }
  repCat.addEventListener('change', syncRepDocRow);
  syncRepDocRow();
})();
(function() {
  var b = document.getElementById('encodeReportBtn');
  if (!b) return;
  b.addEventListener('click', function() {
    var f = document.getElementById('encodeReportForm');
    if (f) f.reset();
    if (_encReportPP) _encReportPP.clear();
    _encReportPhoto = null;
    var repCat = document.getElementById('repCategory');
    var row = document.getElementById('repDocTypeRow');
    if (repCat && row) row.style.display = repCat.value === 'Document & Identification' ? 'grid' : 'none';
    var elecHint = document.getElementById('repElecHint');
    if (elecHint && repCat) elecHint.style.display = repCat.value === 'Electronics & Gadgets' ? '' : 'none';
    document.getElementById('encodeReportModal').classList.add('report-modal-open');
    document.body.style.overflow = 'hidden';
  });
})();
(function() {
  var sub = document.getElementById('encodeReportSubmitBtn');
  if (!sub) return;
  var lostReportUrl = @json(route('admin.found.lost-report'));
  function runEncodeReportPost(btn) {
    var email = document.getElementById('repStudentEmail').value.trim();
    var contact = document.getElementById('repContact').value.trim();
    var dept = document.getElementById('repDept').value.trim();
    var desc = document.getElementById('repDesc').value.trim();
    var cat = document.getElementById('repCategory').value || '';
    btn.disabled = true;
    btn.textContent = 'Saving…';
    return fetch(lostReportUrl, {
      method: 'POST',
      headers: _reportsJsonHeaders(),
      body: JSON.stringify({
        student_email: email,
        category: cat,
        document_type: (document.getElementById('repDocType') && document.getElementById('repDocType').value) ? document.getElementById('repDocType').value : '',
        full_name: document.getElementById('repFullName').value || '',
        contact_number: contact,
        department: dept,
        id: document.getElementById('repId').value || '',
        item: document.getElementById('repItem').value || '',
        item_description: desc,
        color: document.getElementById('repColor').value || '',
        brand: document.getElementById('repBrand').value || '',
        date_lost: document.getElementById('repDateLost').value || '',
        imageDataUrl: _encReportPhoto || null
      })
    }).then(_reportsParseFetchResponse).then(function(res) {
      btn.disabled = false;
      btn.textContent = 'Next';
      if (res.ok && res.data && res.data.ok) {
        closeEncodeReportModal();
        if (typeof closeAdminEncodeReviewModal === 'function') closeAdminEncodeReviewModal();
        if (typeof window.appUiSuccess === 'function') {
          var id = res.data.id;
          var td = '';
          if (id && String(id).indexOf('REF-') === 0) {
            td = window.appUiFormatTicketDisplay ? window.appUiFormatTicketDisplay(id) : id;
          } else if (id) {
            td = 'Ticket: ' + id;
          }
          window.appUiSuccess({
            title: 'Success',
            message: 'Report has been submitted successfully!',
            ticketId: id,
            ticketDisplay: td,
            onClose: function() { location.reload(); }
          });
        } else {
          alert('Saved: ' + (res.data.id || ''));
          location.reload();
        }
      } else {
        _reportsAppAlert(_reportsLaravelErrMsg(res) || 'Could not save report.');
      }
    }).catch(function() {
      btn.disabled = false;
      btn.textContent = 'Next';
      _reportsAppAlert('Network error. Try again.');
    });
  }
  sub.addEventListener('click', function() {
    var email = document.getElementById('repStudentEmail').value.trim();
    var contact = document.getElementById('repContact').value.trim();
    var dept = document.getElementById('repDept').value.trim();
    var desc = document.getElementById('repDesc').value.trim();
    var cat = document.getElementById('repCategory').value || '';
    if (!email) { document.getElementById('repStudentEmail').focus(); return; }
    if (!contact) { document.getElementById('repContact').focus(); return; }
    if (!dept) { document.getElementById('repDept').focus(); return; }
    if (!desc) { document.getElementById('repDesc').focus(); return; }
    if (cat === 'Document & Identification') {
      var dt = (document.getElementById('repDocType').value || '').trim();
      if (!dt) { document.getElementById('repDocType').focus(); return; }
    }
    var btn = this;
    document.getElementById('adminEncodeReviewTitle').textContent = 'Item Lost Report';
    document.getElementById('adminEncodeReviewSummary').innerHTML = buildEncodeReportReviewHtml();
    window._adminEncodeReview = {
      runSubmit: function() { return runEncodeReportPost(btn); },
      onBack: function() {
        document.getElementById('encodeReportModal').classList.add('report-modal-open');
        document.body.style.overflow = 'hidden';
      }
    };
    closeEncodeReportModal();
    if (typeof openAdminEncodeReviewModal === 'function') openAdminEncodeReviewModal();
  });
})();
</script>
@endpush

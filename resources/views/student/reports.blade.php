@extends('layouts.student')

@section('title', 'Browse Items')

@php
  $queryAll = array_filter(['category' => $categoryFilter ?: null]);
  $queryMatched = array_filter(['filter' => 'matched', 'category' => $categoryFilter ?: null]);
@endphp


@section('content')

  <h1 class="page-title-browse browse-page-title">Browse Items</h1>

  <div class="browse-toolbar">
    <div class="report-tabs report-tabs--browse">
      <a href="{{ route('student.reports', $queryAll) }}"
         class="report-tab {{ $filter !== 'matched' ? 'active' : '' }}">All Reports</a>
      <a href="{{ route('student.reports', $queryMatched) }}"
         class="report-tab {{ $filter === 'matched' ? 'active' : '' }}">Matched Reports</a>
      <button type="button" class="report-tab report-tab-primary" onclick="openReportModal()">
        <i class="fa-solid fa-plus"></i> Report Lost Item
      </button>
    </div>
    <form method="get" action="{{ route('student.reports') }}" class="browse-filter-form">
      @if($filter === 'matched')
        <input type="hidden" name="filter" value="matched">
      @endif
      <div class="browse-filter-filters">
        <label class="sr-only" for="browseCategoryFilter">Filter by category</label>
        <select name="category" id="browseCategoryFilter" class="found-filter-select browse-filter-select" onchange="this.form.submit()">
          <option value="">Filter By Category</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ ($categoryFilter ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>

  @if($filter === 'matched')
    <div class="inventory-card reports-browse-card">
      <div class="inventory-title">Matched Reports</div>
      <div class="table-wrapper">
        <table class="reports-data-table reports-browse-table">
          <thead>
            <tr>
              <th>Barcode ID</th>
              <th>Category</th>
              <th>Found At</th>
              <th>Date Found</th>
              <th>Retention End</th>
              <th>Storage Location</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
              @php
                $found = $report->matched_found_item;
                $pairIdx = null;
                foreach ($matchedPairsPayload as $i => $payload) {
                  if (($payload['lost_id'] ?? null) === $report->id) {
                    $pairIdx = $i;
                    break;
                  }
                }
                $retEnd = $found ? $found->retentionEndDate() : null;
              @endphp
              <tr class="{{ $loop->iteration % 2 === 1 ? 'reports-row-alt' : '' }}">
                <td><strong>{{ $found?->id ?? '—' }}</strong></td>
                <td>{{ $found?->item_type ?? '—' }}</td>
                <td>{{ $found?->found_at ?? '—' }}</td>
                <td>{{ $found && $found->date_encoded ? $found->date_encoded->format('Y-m-d') : '—' }}</td>
                <td>{{ $retEnd ? $retEnd->format('Y-m-d') : '—' }}</td>
                <td>{{ $found?->storage_location ?? '—' }}</td>
                <td class="reports-action-cell">
                  @if($pairIdx !== null)
                    <button type="button" class="reports-btn reports-btn-view" onclick="openStudentCompareModal({{ $pairIdx }})">View</button>
                  @else
                    <span class="reports-muted">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="table-empty">No matched reports yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  @else
    <div class="inventory-card reports-browse-card">
      <div class="inventory-title">My Reports</div>
      <div class="table-wrapper">
        <table class="reports-data-table reports-browse-table">
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
              @php
                $showCancelBtn = false;
                $cancelLocked = false;
                $cancelNotBefore = null;
                if (! in_array($report->status, ['Claimed', 'Resolved', 'Cancelled'], true)) {
                    if ($report->created_at) {
                        $cancelNotBefore = \Carbon\Carbon::parse($report->created_at)->addHours(24);
                        if (now()->lt($cancelNotBefore)) {
                            $cancelLocked = true;
                        } else {
                            $showCancelBtn = true;
                        }
                    } else {
                        $showCancelBtn = true;
                    }
                }
                $matchPairIdx = null;
                foreach ($matchedPairsPayload as $i => $payload) {
                    if (($payload['lost_id'] ?? null) === $report->id) {
                        $matchPairIdx = $i;
                        break;
                    }
                }
              @endphp
              <tr class="{{ $loop->iteration % 2 === 1 ? 'reports-row-alt' : '' }}">
                <td><strong>{{ $report->display_ticket_id }}</strong></td>
                <td>{{ $report->item_type ?? '—' }}</td>
                <td>{{ $report->parsed_department ?? '—' }}</td>
                <td>{{ $report->parsed_student_number ?? '—' }}</td>
                <td>{{ $report->parsed_contact ?? '—' }}</td>
                <td>{{ $report->date_lost ? $report->date_lost->format('Y-m-d') : '—' }}</td>
                <td class="reports-action-cell">
                  <button type="button" class="reports-btn reports-btn-view" onclick="openStudentLostReportView('{{ $report->id }}')">View</button>
                  @if($matchPairIdx !== null)
                    <button type="button" class="reports-btn reports-btn-view" style="background:#059669;" onclick="openStudentCompareModal({{ $matchPairIdx }})">View Match</button>
                  @endif
                  @if($showCancelBtn)
                    <button type="button" class="reports-btn reports-btn-cancel student-report-cancel-btn"
                            data-cancel-id="{{ $report->id }}">Cancel</button>
                  @elseif($cancelLocked)
                    <button type="button" class="reports-btn reports-btn-cancel reports-btn-cancel--locked student-report-cancel-cooldown"
                            data-available-at="{{ $cancelNotBefore?->format('M j, Y g:i A') }}">Cancel</button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="table-empty">No reports yet. Use <strong>Report Lost Item</strong> to file one.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  {{-- Cancel / message modals (replace system popups) --}}
  <div id="studentReportCooldownModal" class="student-report-msg-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">
    <div class="student-report-msg-dialog" onclick="event.stopPropagation()">
      <h3 class="student-report-msg-title">Cancel not available yet</h3>
      <p class="student-report-msg-text">You must wait 24 hours after <strong>submitting</strong> your report before you can cancel it. Date lost does not affect this waiting period.</p>
      <p class="student-report-msg-sub" id="studentReportCooldownSub"></p>
      <div class="student-report-msg-actions">
        <button type="button" class="student-report-msg-btn student-report-msg-btn-primary" onclick="closeStudentReportCooldownModal()">OK</button>
      </div>
    </div>
  </div>
  <div id="studentReportConfirmCancelModal" class="student-report-msg-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">
    <div class="student-report-msg-dialog" onclick="event.stopPropagation()">
      <h3 class="student-report-msg-title">Cancel this report?</h3>
      <p class="student-report-msg-text">This action cannot be undone. Please provide a reason for cancellation.</p>
      <div style="margin:12px 0 4px;">
        <label for="studentCancelReason" style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Reason for cancellation <span style="color:#dc2626;">*</span></label>
        <textarea id="studentCancelReason" rows="3" style="width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-family:Poppins,sans-serif;font-size:13px;resize:vertical;" placeholder="e.g. Item already found, filed duplicate report, etc."></textarea>
        <p id="studentCancelReasonErr" style="display:none;font-size:11px;color:#dc2626;margin-top:3px;">Please enter a reason before cancelling.</p>
      </div>
      <div class="student-report-msg-actions">
        <button type="button" class="student-report-msg-btn student-report-msg-btn-secondary" onclick="closeStudentReportConfirmCancelModal()">Back</button>
        <button type="button" class="student-report-msg-btn student-report-msg-btn-danger" id="studentReportConfirmCancelSubmit">Yes, cancel</button>
      </div>
    </div>
  </div>
  <div id="studentReportMessageModal" class="student-report-msg-overlay" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">
    <div class="student-report-msg-dialog" onclick="event.stopPropagation()">
      <h3 class="student-report-msg-title" id="studentReportMessageTitle">Notice</h3>
      <p class="student-report-msg-text" id="studentReportMessageText"></p>
      <div class="student-report-msg-actions">
        <button type="button" class="student-report-msg-btn student-report-msg-btn-primary" onclick="closeStudentReportMessageModal()">OK</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  @include('partials.student-lost-report-modal')
  @include('partials.student-claim-modal')
  @include('partials.student-lost-report-view-modal')
  @include('partials.student-modals-scripts')
  <script>
  (function(){
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.content : '';
    var cancelBase = @json(url('/student/reports'));
    var _pendingCancelBtn = null;

    function jsonHeaders(){
      return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token };
    }
    function parseRes(r){
      return r.text().then(function(t){
        var d = null;
        if(t){ try { d = JSON.parse(t); } catch(e) {} }
        return { ok: r.ok, status: r.status, data: d };
      });
    }
    function errMsg(res){
      var d = res.data;
      if(d && typeof d === 'object'){
        if(d.message) return d.message;
        if(d.errors){ for(var k in d.errors){ var v = d.errors[k]; if(v && v.length) return v[0]; } }
        if(d.error) return d.error;
      }
      if(res.status === 419) return 'Page expired. Refresh and try again.';
      return 'Request failed (' + res.status + ').';
    }

    window.openStudentReportCooldownModal = function(availableAt){
      var el = document.getElementById('studentReportCooldownModal');
      var sub = document.getElementById('studentReportCooldownSub');
      if(sub) sub.textContent = availableAt ? ('Cancel will be available after ' + availableAt + '.') : '';
      if(el){ el.style.display = 'flex'; el.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; }
    };
    window.closeStudentReportCooldownModal = function(){
      var el = document.getElementById('studentReportCooldownModal');
      if(el){ el.style.display = 'none'; el.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }
    };

    window.openStudentReportConfirmCancelModal = function(btn){
      _pendingCancelBtn = btn;
      var reasonEl = document.getElementById('studentCancelReason');
      var errEl = document.getElementById('studentCancelReasonErr');
      if(reasonEl) reasonEl.value = '';
      if(errEl) errEl.style.display = 'none';
      var el = document.getElementById('studentReportConfirmCancelModal');
      if(el){ el.style.display = 'flex'; el.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; }
    };
    window.closeStudentReportConfirmCancelModal = function(){
      _pendingCancelBtn = null;
      var el = document.getElementById('studentReportConfirmCancelModal');
      if(el){ el.style.display = 'none'; el.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }
    };

    window.openStudentReportMessageModal = function(title, text){
      var t = document.getElementById('studentReportMessageTitle');
      var p = document.getElementById('studentReportMessageText');
      if(t) t.textContent = title || 'Notice';
      if(p) p.textContent = text || '';
      var el = document.getElementById('studentReportMessageModal');
      if(el){ el.style.display = 'flex'; el.setAttribute('aria-hidden','false'); document.body.style.overflow = 'hidden'; }
    };
    window.closeStudentReportMessageModal = function(){
      var el = document.getElementById('studentReportMessageModal');
      if(el){ el.style.display = 'none'; el.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; }
    };

    document.getElementById('studentReportConfirmCancelSubmit').addEventListener('click', function(){
      var reasonEl = document.getElementById('studentCancelReason');
      var errEl = document.getElementById('studentCancelReasonErr');
      var reason = reasonEl ? reasonEl.value.trim() : '';
      if(!reason){
        if(errEl) errEl.style.display = '';
        if(reasonEl) reasonEl.focus();
        return;
      }
      if(errEl) errEl.style.display = 'none';
      var btn = _pendingCancelBtn;
      closeStudentReportConfirmCancelModal();
      if(!btn) return;
      var id = btn.getAttribute('data-cancel-id');
      if(!id) return;
      var prev = btn.textContent;
      btn.disabled = true; btn.textContent = 'Cancelling…';
      fetch(cancelBase + '/' + encodeURIComponent(id) + '/cancel', {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({ reason: reason })
      }).then(parseRes).then(function(res){
        btn.disabled = false; btn.textContent = prev;
        if(res.ok && res.data && res.data.ok){
          var tr = btn.closest('tr');
          if(tr) tr.remove();
          else location.reload();
        } else {
          openStudentReportMessageModal('Could not cancel', errMsg(res));
        }
      }).catch(function(){
        btn.disabled = false; btn.textContent = prev;
        openStudentReportMessageModal('Network error', 'Please try again.');
      });
    });

    document.addEventListener('click', function(e){
      var cool = e.target.closest('.student-report-cancel-cooldown');
      if(cool){
        e.preventDefault();
        var when = cool.getAttribute('data-available-at') || '';
        openStudentReportCooldownModal(when);
        return;
      }
      var btn = e.target.closest('.student-report-cancel-btn');
      if(!btn) return;
      e.preventDefault();
      var id = btn.getAttribute('data-cancel-id');
      if(!id) return;
      openStudentReportConfirmCancelModal(btn);
    });

    document.addEventListener('click', function(e){
      if(e.target.id === 'studentReportCooldownModal') closeStudentReportCooldownModal();
      if(e.target.id === 'studentReportConfirmCancelModal') closeStudentReportConfirmCancelModal();
      if(e.target.id === 'studentReportMessageModal') closeStudentReportMessageModal();
    });
    document.addEventListener('keydown', function(e){
      if(e.key !== 'Escape') return;
      closeStudentReportCooldownModal();
      closeStudentReportConfirmCancelModal();
      closeStudentReportMessageModal();
    });
  })();
  </script>

  <script>
  (function(){
    var summaryUrl = @json(route('student.dashboard.summary'));
    var lastHash = '';

    function hashPayload(pairs) {
      return JSON.stringify((pairs || []).map(function(p){ return p.lost_id + ':' + (p.claimable ? '1' : '0'); }));
    }

    function poll() {
      if (document.visibilityState !== 'visible') return;
      fetch(summaryUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (!data.ok) return;
          var pairs = data.matched_pairs_payload || [];
          window.STUDENT_MATCH_PAIRS = pairs;
          var h = hashPayload(pairs);
          if (lastHash === '') { lastHash = h; return; }
          if (h !== lastHash) { location.reload(); }
        })
        .catch(function(){});
    }

    lastHash = hashPayload(window.STUDENT_MATCH_PAIRS || []);
    setInterval(poll, 15000);
  })();
  </script>
@endpush

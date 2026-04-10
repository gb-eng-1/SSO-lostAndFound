@extends('layouts.student')

@section('title', 'Dashboard')

@push('styles')
  <link rel="stylesheet" href="{{ asset('STUDENT/HelpSupport.css') }}?v=3">
  <style>
    .section-link--btn { background: none; border: none; font: inherit; color: #2563eb; cursor: pointer; padding: 0; }
    .section-link--btn:hover { text-decoration: underline; }
    .reports-ticket-link { background: none; border: none; padding: 0; font: inherit; font-weight: 700; color: #2563eb; text-decoration: underline; cursor: pointer; }
    .activity-view-details-btn { background: none; border: none; padding: 0; font: inherit; color: #2563eb; font-weight: 600; font-size: 13px; cursor: pointer; }
    .activity-view-details-btn:hover { text-decoration: underline; }
  </style>
@endpush

@section('content')

<div class="dashboard-header-row">
  <h1 class="dashboard-header">Dashboard</h1>
  <p class="dashboard-welcome">Welcome to UB Lost and Found System{{ $studentName ? ', ' . $studentName : '' }}!</p>
</div>

<div class="dashboard-grid">
  <div class="dashboard-main">

    <div class="action-cards">
      <button type="button" class="action-card lost" onclick="openReportModal()">
        <span class="action-card-icon">
          <i class="fa-solid fa-umbrella"></i>
          <i class="fa-solid fa-question arrow-icon"></i>
        </span>
        <strong>I LOST an Item</strong>
      </button>
      <button type="button" class="action-card found" onclick="openFoundProtocolModal()">
        <span class="action-card-icon">
          <i class="fa-solid fa-box"></i>
          <i class="fa-solid fa-check check-icon"></i>
        </span>
        <strong>I FOUND an Item</strong>
      </button>
      <button type="button" class="action-card claim" onclick="openClaimProtocolModal()">
        <span class="action-card-icon"><i class="fa-solid fa-box-open"></i></span>
        <strong>CLAIM an Item</strong>
      </button>
    </div>

    <div class="section-card">
      <div class="section-header">
        <h2 class="section-title">Recently Matched Item</h2>
        @if(count($matchedPairsPayload ?? []) > 0)
          <button type="button" class="section-link section-link--btn" id="openAllMatchedBtn">see all</button>
        @endif
      </div>
      @if(count($matchedPairsPayload ?? []) > 0)
        <div class="matched-items">
          @foreach($matchedPairsPayload as $idx => $p)
            @if($loop->iteration > 3)
              @break
            @endif
            <div class="matched-item">
              <div class="matched-item-header">
                <span class="item-icon-wrap"><i class="fa-solid fa-mobile-screen"></i></span>
                <span class="matched-item-name">{{ $p['card_title'] }}</span>
              </div>
              <p class="matched-item-row" style="font-size:12px;color:#4b5563;line-height:1.4;margin:0;">{{ $p['card_description'] }}</p>
              <div class="matched-item-row">
                <i class="fa-solid fa-location-dot row-icon"></i>
                <span>{{ $p['card_location'] }}</span>
              </div>
              <div class="matched-item-row">
                <i class="fa-regular fa-calendar row-icon"></i>
                <span>{{ $p['card_date'] }}</span>
              </div>
              <button type="button" class="btn-view" onclick="openStudentCompareModal({{ $idx }})">View</button>
            </div>
          @endforeach
        </div>
      @else
        <p class="empty-text">No potential matches yet. When the system finds a match for your report, it will appear here.</p>
      @endif
    </div>

    <div class="bottom-grid">
      <div class="section-card">
        <div class="section-header">
          <h2 class="section-title">How to Report Lost Item</h2>
        </div>
        @include('partials.student-how-to-report-lost', ['embedded' => true])
      </div>

      <div class="section-card">
        <div class="section-header">
          <h2 class="section-title">My Reports</h2>
          <a href="{{ route('student.reports') }}" class="section-link">see all</a>
        </div>
        <div class="reports-table-wrapper">
          <table class="reports-table">
            <thead>
              <tr>
                <th>Ticket ID</th>
                <th>Category</th>
                <th>Date Lost</th>
              </tr>
            </thead>
            <tbody>
              @forelse($myReports->filter(fn($r) => !$r->matched_barcode_id || !in_array($r->status, ['For Verification', 'Matched', 'Unresolved Claimants']))->take(5) as $report)
                <tr>
                  <td>
                    <button type="button" class="reports-ticket-link" onclick="openStudentItemFromSearch('{{ $report->id }}')">{{ $report->display_ticket_id }}</button>
                  </td>
                  <td>{{ $report->item_type ?? '—' }}</td>
                  <td>{{ $report->date_lost ? $report->date_lost->format('Y-m-d') : '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="table-empty">No reports yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <aside class="activity-sidebar">
    <div class="activity-card">
      <h2 class="activity-title">Recent Activity</h2>
      @forelse($recentActivity as $n)
        @php
          $rid = $n->related_id;
          $defaultHref = route('student.notifications');
          if (in_array($n->type, ['claim_approved', 'claim_rejected'], true)) {
            $defaultHref = route('student.claim-history');
          }
        @endphp
        <div class="activity-item" style="display:block;padding:12px;background:{{ $n->is_read ? '#fff' : '#fffbeb' }};border:1px solid #fde68a;border-radius:8px;margin-bottom:10px;">
          <p style="margin:0 0 6px;font-size:13px;color:#374151;">{{ $n->title }}</p>
          <p style="margin:0 0 8px;font-size:12px;color:#6b7280;">{{ \Illuminate\Support\Str::limit($n->message, 120) }}</p>
          @if($rid)
            <button type="button" class="activity-view-details-btn" data-student-activity-related-id="{{ e($rid) }}">View Details</button>
          @else
            <a href="{{ $defaultHref }}" style="color:#2563eb;font-weight:600;font-size:13px;">View Details</a>
          @endif
        </div>
      @empty
        <p class="empty-text" style="font-size:13px;">No recent activity.</p>
      @endforelse
    </div>
  </aside>
</div>

{{-- Full list of matched pairs (same data as dashboard cards; indices align with openStudentCompareModal) --}}
<div id="allMatchedItemsModal" class="scm-overlay" role="dialog" aria-modal="true" aria-labelledby="allMatchedTitle"
     onclick="if(event.target===this)closeAllMatchedItemsModal()">
  <div class="scm-modal" style="max-width:min(920px,96vw);" onclick="event.stopPropagation()">
    <div class="scm-header">
      <h2 id="allMatchedTitle">Recently Matched Items</h2>
      <button type="button" class="srm-close" style="background:rgba(255,255,255,.2);" onclick="closeAllMatchedItemsModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="scm-body" id="allMatchedItemsBody" style="max-height:min(70vh,640px);overflow:auto;padding:16px 20px;">
      @if(count($matchedPairsPayload ?? []) > 0)
        <div class="matched-items" style="flex-wrap:wrap;">
          @foreach($matchedPairsPayload as $idx => $p)
            <div class="matched-item">
              <div class="matched-item-header">
                <span class="item-icon-wrap"><i class="fa-solid fa-mobile-screen"></i></span>
                <span class="matched-item-name">{{ $p['card_title'] }}</span>
              </div>
              <p class="matched-item-row" style="font-size:12px;color:#4b5563;line-height:1.4;margin:0;">{{ $p['card_description'] }}</p>
              <div class="matched-item-row">
                <i class="fa-solid fa-location-dot row-icon"></i>
                <span>{{ $p['card_location'] }}</span>
              </div>
              <div class="matched-item-row">
                <i class="fa-regular fa-calendar row-icon"></i>
                <span>{{ $p['card_date'] }}</span>
              </div>
              <button type="button" class="btn-view" onclick="closeAllMatchedItemsModal(); openStudentCompareModal({{ $idx }})">View</button>
            </div>
          @endforeach
        </div>
      @else
        <p class="empty-text" style="text-align:center;padding:28px;">No potential matches yet.</p>
      @endif
    </div>
    <div class="scm-footer">
      <button type="button" class="srm-btn-cancel" onclick="closeAllMatchedItemsModal()">Close</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
  @include('partials.student-lost-report-modal')
  @include('partials.student-claim-modal')
  @include('partials.student-protocol-modals')
  @include('partials.student-lost-report-view-modal')
  @include('partials.student-modals-scripts')
  <script>
  (function(){
    function closeAllMatchedItemsModal(){
      var m = document.getElementById('allMatchedItemsModal');
      if(m){ m.classList.remove('open'); document.body.style.overflow = ''; }
    }
    window.closeAllMatchedItemsModal = closeAllMatchedItemsModal;
    window.openAllMatchedItemsModal = function(){
      var m = document.getElementById('allMatchedItemsModal');
      if(m){ m.classList.add('open'); document.body.style.overflow = 'hidden'; }
    };
    var ob = document.getElementById('openAllMatchedBtn');
    if(ob) ob.addEventListener('click', function(){ window.openAllMatchedItemsModal(); });
    document.addEventListener('keydown', function(e){
      if(e.key !== 'Escape') return;
      var m = document.getElementById('allMatchedItemsModal');
      if(m && m.classList.contains('open')) closeAllMatchedItemsModal();
    });
    document.querySelectorAll('.activity-view-details-btn[data-student-activity-related-id]').forEach(function(btn){
      btn.addEventListener('click', function(){
        var rid = btn.getAttribute('data-student-activity-related-id');
        if(!rid) return;
        if(typeof window.openStudentItemFromSearch === 'function') window.openStudentItemFromSearch(rid);
      });
    });
  })();
  </script>

  <script>
  (function(){
    var summaryUrl = @json(route('student.dashboard.summary'));
    var lastHash = '';

    function hashPayload(pairs) {
      return JSON.stringify((pairs || []).map(function(p){ return p.lost_id + ':' + (p.claimable ? '1' : '0') + ':' + (p.claim_intent_submitted ? '1' : '0'); }));
    }

    function buildCardHtml(p, idx) {
      return '<div class="matched-item">'
        + '<div class="matched-item-header">'
        + '<span class="item-icon-wrap"><i class="fa-solid fa-mobile-screen"></i></span>'
        + '<span class="matched-item-name">' + escH(p.card_title) + '</span>'
        + '</div>'
        + '<p class="matched-item-row" style="font-size:12px;color:#4b5563;line-height:1.4;margin:0;">' + escH(p.card_description) + '</p>'
        + '<div class="matched-item-row"><i class="fa-solid fa-location-dot row-icon"></i><span>' + escH(p.card_location) + '</span></div>'
        + '<div class="matched-item-row"><i class="fa-regular fa-calendar row-icon"></i><span>' + escH(p.card_date) + '</span></div>'
        + '<button type="button" class="btn-view" onclick="openStudentCompareModal(' + idx + ')">View</button>'
        + '</div>';
    }

    function escH(s) {
      var d = document.createElement('div');
      d.appendChild(document.createTextNode(s || ''));
      return d.innerHTML;
    }

    function refreshDashboard(data) {
      var pairs = data.matched_pairs_payload || [];
      window.STUDENT_MATCH_PAIRS = pairs;

      // Main section cards (first 3)
      var mainSection = document.querySelector('.section-card > .matched-items');
      var seeAllBtn = document.getElementById('openAllMatchedBtn');
      var sectionCard = seeAllBtn ? seeAllBtn.closest('.section-card') : (mainSection ? mainSection.closest('.section-card') : null);

      if (sectionCard) {
        var headerDiv = sectionCard.querySelector('.section-header');
        var bodyHtml = '';
        if (pairs.length > 0) {
          if (seeAllBtn) { seeAllBtn.style.display = ''; }
          else if (headerDiv) {
            headerDiv.insertAdjacentHTML('beforeend', '<button type="button" class="section-link section-link--btn" id="openAllMatchedBtn">see all</button>');
            var nb = document.getElementById('openAllMatchedBtn');
            if (nb) nb.addEventListener('click', function(){ window.openAllMatchedItemsModal(); });
          }
          bodyHtml = '<div class="matched-items">';
          for (var i = 0; i < Math.min(pairs.length, 3); i++) {
            bodyHtml += buildCardHtml(pairs[i], i);
          }
          bodyHtml += '</div>';
        } else {
          if (seeAllBtn) seeAllBtn.style.display = 'none';
          bodyHtml = '<p class="empty-text">No potential matches yet. When the system finds a match for your report, it will appear here.</p>';
        }
        var existing = sectionCard.querySelector('.matched-items') || sectionCard.querySelector('.empty-text');
        if (existing) existing.remove();
        headerDiv.insertAdjacentHTML('afterend', bodyHtml);
      }

      // "See all" modal body
      var modalBody = document.getElementById('allMatchedItemsBody');
      if (modalBody) {
        if (pairs.length > 0) {
          var html = '<div class="matched-items" style="flex-wrap:wrap;">';
          for (var j = 0; j < pairs.length; j++) {
            html += buildCardHtml(pairs[j], j).replace('onclick="openStudentCompareModal', 'onclick="closeAllMatchedItemsModal(); openStudentCompareModal');
          }
          html += '</div>';
          modalBody.innerHTML = html;
        } else {
          modalBody.innerHTML = '<p class="empty-text" style="text-align:center;padding:28px;">No potential matches yet.</p>';
        }
      }

      // My Reports preview table
      var preview = data.my_reports_preview || [];
      var tbody = document.querySelector('.reports-table tbody');
      if (tbody) {
        if (preview.length > 0) {
          var rows = '';
          preview.forEach(function(r) {
            rows += '<tr>'
              + '<td><button type="button" class="reports-ticket-link" onclick="openStudentItemFromSearch(\'' + escH(r.id) + '\')">' + escH(r.display_ticket_id) + '</button></td>'
              + '<td>' + escH(r.item_type) + '</td>'
              + '<td>' + escH(r.date_lost) + '</td>'
              + '</tr>';
          });
          tbody.innerHTML = rows;
        } else {
          tbody.innerHTML = '<tr><td colspan="3" class="table-empty">No reports yet.</td></tr>';
        }
      }
    }

    function poll() {
      if (document.visibilityState !== 'visible') return;
      fetch(summaryUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (!data.ok) return;
          var h = hashPayload(data.matched_pairs_payload);
          if (lastHash === '') { lastHash = h; return; }
          if (h !== lastHash) {
            lastHash = h;
            refreshDashboard(data);
          }
        })
        .catch(function(){});
    }

    lastHash = hashPayload(window.STUDENT_MATCH_PAIRS || []);
    setInterval(poll, 15000);
  })();
  </script>
@endpush

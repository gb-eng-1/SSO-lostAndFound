@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/photo-picker.css') }}?v=2">
<style>
/* Barcode duplicate modal (shared partial — same as Found Items inline styles) */
.cancel-confirm-overlay{display:none;position:fixed;inset:0;z-index:1600;align-items:center;justify-content:center;background:rgba(0,0,0,.5);padding:16px;}
.cancel-confirm-overlay.open{display:flex;}
.cancel-confirm-dialog{background:#fff;border-radius:12px;width:min(420px,96vw);box-shadow:0 20px 50px rgba(0,0,0,.2);overflow:hidden;font-family:Poppins,sans-serif;}
.cancel-confirm-hdr{background:#8b0000;color:#fff;padding:14px 18px;font-size:16px;font-weight:700;margin:0;}
.cancel-confirm-body{padding:20px 22px 8px;color:#374151;font-size:14px;line-height:1.5;}
.cancel-confirm-foot{padding:16px 18px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e5e7eb;background:#fafafa;}
.cancel-confirm-btn{padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;}
.cancel-confirm-btn--secondary{border:1px solid #d1d5db;background:#fff;color:#374151;}
.cancel-confirm-btn--secondary:hover{background:#f3f4f6;}
</style>
@endpush

@section('content')

<div class="dashboard-header-row">
  <h1 class="page-title">Dashboard</h1>
</div>

{{-- ── Summary Cards + Action Buttons ────────────────────────────────────── --}}
<div class="dashboard-stats-row">
  <div class="summary-cards-four">
    <a href="{{ route('admin.found') }}" class="summary-card-link">
      <div class="summary-card">
        <div class="summary-card-text">
          <h3 class="summary-title">Recovered Items</h3>
          <p class="summary-sub-title">Internal (non-ID)</p>
          <p class="summary-value" data-summary-stat="internal_recovered">{{ number_format($internalRecovered) }}</p>
        </div>
        <div class="summary-icon-wrap unclaimed"><i class="fa-solid fa-box-archive"></i></div>
        <div class="summary-bg-icon unclaimed"><i class="fa-solid fa-box-archive"></i></div>
      </div>
    </a>
    <a href="{{ route('admin.found') }}" class="summary-card-link">
      <div class="summary-card">
        <div class="summary-card-text">
          <h3 class="summary-title">Recovered IDs</h3>
          <p class="summary-sub-title">External (active)</p>
          <p class="summary-value" data-summary-stat="external_ids">{{ number_format($externalIds) }}</p>
        </div>
        <div class="summary-icon-wrap external"><i class="fa-regular fa-id-card"></i></div>
        <div class="summary-bg-icon external"><i class="fa-regular fa-id-card"></i></div>
      </div>
    </a>
    <a href="{{ route('admin.reports') }}" class="summary-card-link">
      <div class="summary-card">
        <div class="summary-card-text">
          <h3 class="summary-title">Unresolved Claimants</h3>
          <p class="summary-sub-title">Unmatched reports</p>
          <p class="summary-value" data-summary-stat="unresolved">{{ number_format($unresolved) }}</p>
        </div>
        <div class="summary-icon-wrap unresolved"><i class="fa-solid fa-users"></i></div>
        <div class="summary-bg-icon unresolved"><i class="fa-solid fa-users"></i></div>
      </div>
    </a>
    <a href="{{ route('admin.matched') }}" class="summary-card-link">
      <div class="summary-card">
        <div class="summary-card-text">
          <h3 class="summary-title">For Verification</h3>
          <p class="summary-sub-title">&nbsp;</p>
          <p class="summary-value" data-summary-stat="for_verification">{{ number_format($forVerification) }}</p>
        </div>
        <div class="summary-icon-wrap verification"><i class="fa-solid fa-circle-check"></i></div>
        <div class="summary-bg-icon verification"><i class="fa-solid fa-circle-check"></i></div>
      </div>
    </a>
  </div>
  <div class="dashboard-action-btns">
    <a href="#" class="dashboard-btn-encode" onclick="openEncodeModal(event)">
      <i class="fa-solid fa-plus dashboard-action-icon dashboard-action-icon--encode"></i> Encode Item
    </a>
    <a href="{{ route('admin.export.dashboard') }}" class="dashboard-btn-export">
      <i class="fa-solid fa-file-excel dashboard-action-icon dashboard-action-icon--export"></i> Export Report
    </a>
  </div>
</div>

{{-- ── 3-Column Grid ────────────────────────────────────────────────────────── --}}
<div class="dashboard-3col-grid">

  {{-- LEFT: Internal + External tables --}}
  <div class="dashboard-left-col">

    {{-- Recovered Item (Internal) --}}
    <div class="dash-table-card">
      <div class="dash-table-header">
        <span class="dash-table-title">Recovered Item (Internal)</span>
        <a href="{{ route('admin.found') }}" class="dash-see-all">see all</a>
      </div>
      <div class="dash-table-scroll">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Barcode ID</th>
              <th>Category</th>
              <th>Found At</th>
              <th>Date Found</th>
              <th>Retention End</th>
            </tr>
          </thead>
          <tbody id="dash-tbody-recovered-internal">
            @forelse($recoveredInternal as $item)
              <tr>
                <td><a href="#" class="tbl-item-link" data-item-id="{{ $item->id }}">{{ $item->id }}</a></td>
                <td>{{ $item->item_type ?? '—' }}</td>
                <td>{{ $item->found_at ?? '—' }}</td>
                <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
                <td>{{ $item->retention_end ?? 'N/A' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="td-empty">No items yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Recovered IDs (External) --}}
    <div class="dash-table-card">
      <div class="dash-table-header">
        <span class="dash-table-title">Recovered IDs (External)</span>
        <a href="{{ route('admin.found') }}" class="dash-see-all">see all</a>
      </div>
      <div class="dash-table-scroll">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Barcode ID</th>
              <th>Encoded By</th>
              <th>Storage</th>
              <th>Date Surrendered</th>
              <th>Retention End</th>
            </tr>
          </thead>
          <tbody id="dash-tbody-recovered-external">
            @forelse($recoveredExternal as $item)
              <tr>
                <td><a href="#" class="tbl-item-link" data-item-id="{{ $item->id }}">{{ $item->id }}</a></td>
                <td>{{ $item->found_by ?? '—' }}</td>
                <td>{{ $item->storage_location ?? '—' }}</td>
                <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
                <td>{{ $item->retention_end ?? 'N/A' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="td-empty">No items yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>{{-- /dashboard-left-col --}}

  {{-- CENTER: Chart + Unresolved + Verification --}}
  <div class="dashboard-center-col">

    {{-- Chart card --}}
    <div class="dash-chart-card">
      <div class="chart-tabs">
        <button class="chart-tab active" data-chart="pie">Pie Graph</button>
        <button class="chart-tab" data-chart="bar">Bar Graph</button>
      </div>
      <div class="chart-body"><canvas id="statusChart"></canvas></div>
      <p class="chart-caption" id="chartCaption">{{ $pieCaption }}</p>
    </div>

    {{-- Unresolved claimants: student lost reports, unmatched --}}
    <div class="dash-table-card">
      <div class="dash-table-header">
        <span class="dash-table-title">Unresolved claimants</span>
        <a href="{{ route('admin.reports') }}" class="dash-see-all">see all</a>
      </div>
      <div class="dash-table-scroll">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Ticket ID</th>
              <th>Category</th>
              <th>Department</th>
              <th>ID</th>
              <th>Contact Number</th>
            </tr>
          </thead>
          <tbody id="dash-tbody-unresolved">
            @forelse($unresolvedItems as $item)
              <tr>
                <td><a href="#" class="tbl-item-link" data-item-id="{{ $item->id }}">{{ $item->display_ticket_id }}</a></td>
                <td>{{ $item->item_type ?? '—' }}</td>
                <td>{{ $item->parsed_department ?? '—' }}</td>
                <td>{{ $item->parsed_student_number ?? '—' }}</td>
                <td>{{ $item->parsed_contact ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="td-empty">No unresolved claimants.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- For Verification --}}
    <div class="dash-table-card">
      <div class="dash-table-header">
        <span class="dash-table-title">For Verification</span>
        <a href="{{ route('admin.matched') }}" class="dash-see-all">see all</a>
      </div>
      <div class="dash-table-scroll">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Barcode ID</th>
              <th>Category</th>
              <th>Found At</th>
              <th>Retention End</th>
              <th>Storage</th>
              <th>Date Found</th>
            </tr>
          </thead>
          <tbody id="dash-tbody-verification">
            @forelse($verificationItems as $item)
              <tr>
                <td><a href="#" class="tbl-item-link" data-item-id="{{ $item->id }}">{{ $item->id }}</a></td>
                <td>{{ $item->item_type ?? '—' }}</td>
                <td>{{ $item->found_at ?? '—' }}</td>
                <td>{{ $item->retention_end ?? 'N/A' }}</td>
                <td>{{ $item->storage_location ?? '—' }}</td>
                <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="td-empty">No items for verification.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>{{-- /dashboard-center-col --}}

  {{-- RIGHT: Recent Activity --}}
  <div class="dashboard-right-col">
    <div class="activity-card">
      <h3 class="activity-title">Recent Activity</h3>
      <div class="activity-list" id="dash-recent-activity-list">
        @forelse($recentActivity as $act)
          @php
            $action   = strtolower($act['action'] ?? 'found');
            $itemId   = $act['item_id'] ?? '';
            $itemName = $act['item_name'] ?? '';
            $dt       = isset($act['created_at']) && $act['created_at']
                ? \Carbon\Carbon::parse($act['created_at'])->format('M d, Y \a\t g:i A')
                : '';
          @endphp
          <div class="activity-item">
            @if($action === 'matched' || $action === 'match')
              <p class="activity-label">Potential Match!</p>
              <p class="activity-text">
                <a href="#" class="activity-item-link" data-item-id="{{ $itemId }}">{{ $itemId }}{{ $itemName && $itemName !== $itemId ? ' ('.$itemName.')' : '' }}</a>
                matched a user report.
              </p>
            @elseif($action === 'lost' || $action === 'reported')
              <p class="activity-label">Lost Item!</p>
              <p class="activity-text">
                <a href="#" class="activity-item-link" data-item-id="{{ $itemId }}">{{ $itemId }}{{ $itemName && $itemName !== $itemId ? ' ('.$itemName.')' : '' }}</a>
                has been reported to be lost.
              </p>
            @else
              <p class="activity-label">Found Item!</p>
              <p class="activity-text">
                You have recently inputted the item (<a href="#" class="activity-item-link" data-item-id="{{ $itemId }}">{{ $itemId }}</a>).
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
  </div>{{-- /dashboard-right-col --}}

</div>{{-- /dashboard-3col-grid --}}

@include('admin.partials.internal-encode-item-modal', ['campusLocations' => $campusLocations])

{{-- ── Encode Success Dialog ──────────────────────────────────────────────── --}}
<div id="encodeSuccessOverlay" style="display:none;position:fixed;inset:0;z-index:9100;
     background:rgba(0,0,0,0.55);align-items:center;justify-content:center;font-family:Poppins,sans-serif;">
  <div id="encodeSuccessPanel" style="background:#fff;border-radius:16px;padding:40px 44px;max-width:420px;width:100%;
              text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.22);position:relative;">
    <button onclick="cancelEncodeSuccess()" style="position:absolute;top:14px;right:14px;
            background:none;border:none;color:#9ca3af;font-size:20px;cursor:pointer;">
      <i class="fa-regular fa-circle-xmark"></i>
    </button>
    <div style="width:72px;height:72px;background:#22c55e;border-radius:50%;
                display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
      <i class="fa-solid fa-check" style="color:#fff;font-size:32px;"></i>
    </div>
    <h3 id="encodeSuccessHeading" style="margin:0 0 10px;font-size:22px;font-weight:700;color:#111827;">Success</h3>
    <p id="encodeSuccessMsg" style="margin:0 0 6px;font-size:14px;color:#374151;">Item has been encoded successfully!</p>
    <p style="margin:0 0 20px;font-size:14px;color:#111827;font-weight:700;" id="encodeSuccessBid"></p>
    <div id="encodeTicketNotice" style="display:none;background:#fffbeb;border:1px solid #fde68a;
         border-radius:10px;padding:14px 16px;margin-bottom:20px;text-align:left;">
      <p style="margin:0 0 6px;font-size:13px;font-weight:600;color:#92400e;">
        <i class="fa-solid fa-wand-magic-sparkles" style="margin-right:5px;"></i>
        Automatic matching
      </p>
      <div id="encodeAutoMatchBody" style="margin:0;font-size:12px;color:#78350f;line-height:1.55;"></div>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;">
      <button onclick="cancelEncodeSuccess()"
              style="padding:10px 28px;border:1px solid #d1d5db;border-radius:8px;background:#fff;
                     color:#374151;font-family:Poppins,sans-serif;font-size:14px;font-weight:600;cursor:pointer;">
        Close
      </button>
      <button onclick="confirmEncodeSuccess()"
              style="padding:10px 28px;border:none;border-radius:8px;background:#8b0000;
                     color:#fff;font-family:Poppins,sans-serif;font-size:14px;font-weight:600;cursor:pointer;">
        Done
      </button>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
/* ── Icon colour overrides matching original ──────────────────────────────── */
.summary-icon-wrap.unclaimed  i,
.summary-bg-icon.unclaimed    i { color: #F59E0B !important; }
.summary-icon-wrap.external   i,
.summary-bg-icon.external     i { color: #5C5FA8 !important; }
.summary-icon-wrap.unresolved i,
.summary-bg-icon.unresolved   i { color: #E30022 !important; }
.summary-icon-wrap.verification i,
.summary-bg-icon.verification   i { color: #50C878 !important; }

@media (max-width: 900px) {
  .sidebar  { min-height: 0 !important; height: auto !important; }
  .nav-menu { flex: none !important; }
}
</style>
@endpush

@push('scripts')
<script>
/* Chart data from server (mutable for optional summary polling) */
window._pieData = @json($pieData);
window._barData = @json($barData);
window._pieCaption = @json($pieCaption);
window._barCaption = @json($barCaption);
window._DASHBOARD_SUMMARY_URL = @json(route('admin.dashboard.summary'));
</script>

<script>
/* ── Chart (single canvas, tab switching) ─────────────────────────────────── */
(function(){
    var captionEl = document.getElementById('chartCaption');
    var chart;

    function ensureCanvas() {
        var wrap = document.querySelector('.dash-chart-card .chart-body');
        if (!wrap) return null;
        var c = document.getElementById('statusChart');
        if (!c) {
            wrap.innerHTML = '<canvas id="statusChart"></canvas>';
            c = document.getElementById('statusChart');
        }
        return c;
    }

    function makePie() {
        var canvas = ensureCanvas();
        if (!canvas) return;
        if (chart) chart.destroy();
        var pd = window._pieData || [];
        if (!pd.length) {
            canvas.parentElement.innerHTML = '<p style="text-align:center;color:#9ca3af;font-style:italic;padding:20px;">No data.</p>';
            if (captionEl) captionEl.textContent = window._pieCaption || '';
            return;
        }
        canvas = ensureCanvas();
        if (!canvas) return;
        var pctLookup = {};
        pd.forEach(function(d){ pctLookup[d.label] = d.pct; });
        chart = new Chart(canvas.getContext('2d'), {
            type: 'pie',
            data: {
                labels: pd.map(function(d){ return d.label; }),
                datasets: [{ data: pd.map(function(d){ return d.count; }), backgroundColor: pd.map(function(d){ return d.color; }), borderColor: '#fff', borderWidth: 3 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { font: { size: 12, family: 'Poppins' }, padding: 16, boxWidth: 14 } },
                    tooltip: { callbacks: { label: function(c){ var p = pctLookup[c.label] != null ? pctLookup[c.label] : 0; return ' ' + c.label + ': ' + c.raw + ' (' + p + '%)'; } } }
                }
            },
            plugins: [{ afterDatasetsDraw: function(ch) {
                var ctx2=ch.ctx, ds=ch.data.datasets[0], meta=ch.getDatasetMeta(0);
                var total = ds.data.reduce(function(s,v){ return s+(v||0); }, 0);
                ctx2.save();
                meta.data.forEach(function(arc, i) {
                    var val=ds.data[i]; if(!val) return;
                    var pct = total > 0 ? (val/total*100).toFixed(1) : '0';
                    var mid=(arc.startAngle+arc.endAngle)/2;
                    var r=(arc.outerRadius-(arc.innerRadius||0))*0.6+(arc.innerRadius||0);
                    ctx2.font='bold 13px Poppins,sans-serif'; ctx2.fillStyle='#fff';
                    ctx2.shadowColor='rgba(0,0,0,0.4)'; ctx2.shadowBlur=3;
                    ctx2.textAlign='center'; ctx2.textBaseline='middle';
                    ctx2.fillText(pct+'%', arc.x+Math.cos(mid)*r, arc.y+Math.sin(mid)*r);
                });
                ctx2.restore();
            }}]
        });
        if (captionEl) captionEl.textContent = window._pieCaption || '';
    }

    function makeBar() {
        var canvas = ensureCanvas();
        if (!canvas) return;
        if (chart) chart.destroy();
        var bd = window._barData || [];
        if (!bd.length) {
            canvas.parentElement.innerHTML = '<p style="text-align:center;color:#9ca3af;font-style:italic;padding:20px;">No data.</p>';
            if (captionEl) captionEl.textContent = window._barCaption || '';
            return;
        }
        canvas = ensureCanvas();
        if (!canvas) return;
        var data = bd.map(function(d){ return d.count || 0; });
        var maxVal = Math.max.apply(null, data.concat([1]));
        var xMax = Math.ceil(maxVal * 1.25) || 10;
        chart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: bd.map(function(d){ return d.label; }),
                datasets: [{ data: data, backgroundColor: bd.map(function(d){ return d.color; }), borderWidth: 0, borderRadius: 3, barPercentage: 0.5, categoryPercentage: 0.8 }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                layout: { padding: { right: 50 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(c){ var total = c.dataset.data.reduce(function(s,v){return s+(v||0);},0); var pct = total > 0 ? (c.raw/total*100).toFixed(1) : '0'; return ' ' + c.raw + ' items (' + pct + '%)'; } } }
                },
                scales: {
                    x: { beginAtZero: true, max: xMax,
                         ticks: { font: { size: 10, family: 'Poppins' }, color: '#9ca3af', stepSize: 1, precision: 0 },
                         grid: { color: '#f0f0f0' }, border: { display: false } },
                    y: { ticks: { font: { size: 11, family: 'Poppins' }, color: '#374151', padding: 6 },
                         grid: { display: false }, border: { display: false } }
                }
            },
            plugins: [{ afterDatasetsDraw: function(ch) {
                var ctx2=ch.ctx; ctx2.save();
                ctx2.font='600 11px Poppins,sans-serif'; ctx2.fillStyle='#374151';
                ctx2.textAlign='left'; ctx2.textBaseline='middle';
                ch.data.datasets.forEach(function(ds, di) {
                    ch.getDatasetMeta(di).data.forEach(function(bar, idx) {
                        var val=ds.data[idx]; if(val==null) return;
                        ctx2.fillText(val, bar.x+5, bar.y);
                    });
                });
                ctx2.restore();
            }}]
        });
        if (captionEl) captionEl.textContent = window._barCaption || '';
    }

    makePie();

    document.querySelectorAll('.chart-tab').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.chart-tab').forEach(function(b){ b.classList.remove('active'); });
            btn.classList.add('active');
            btn.getAttribute('data-chart') === 'bar' ? makeBar() : makePie();
        });
    });

    window.refreshDashboardCharts = function() {
        var activeBar = document.querySelector('.chart-tab[data-chart="bar"].active');
        if (activeBar) makeBar();
        else makePie();
    };
})();
</script>

<script>
/* ── Summary poll (~15s, tab visible) — cards, charts, tables, activity stay in sync ── */
(function(){
    var url = window._DASHBOARD_SUMMARY_URL;
    if (!url) return;
    function fmt(n){ return Number(n||0).toLocaleString(); }
    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function linkId(id){ return '<a href="#" class="tbl-item-link" data-item-id="'+esc(id)+'">'+esc(id)+'</a>'; }
    function renderRecoveredInternal(rows) {
        var tb = document.getElementById('dash-tbody-recovered-internal');
        if (!tb) return;
        if (!rows || !rows.length) { tb.innerHTML = '<tr><td colspan="5" class="td-empty">No items yet.</td></tr>'; return; }
        tb.innerHTML = rows.map(function(r){
            return '<tr><td>'+linkId(r.id)+'</td><td>'+esc(r.item_type)+'</td><td>'+esc(r.found_at)+'</td><td>'+esc(r.date_encoded)+'</td><td>'+esc(r.retention_end)+'</td></tr>';
        }).join('');
    }
    function renderRecoveredExternal(rows) {
        var tb = document.getElementById('dash-tbody-recovered-external');
        if (!tb) return;
        if (!rows || !rows.length) { tb.innerHTML = '<tr><td colspan="5" class="td-empty">No items yet.</td></tr>'; return; }
        tb.innerHTML = rows.map(function(r){
            return '<tr><td>'+linkId(r.id)+'</td><td>'+esc(r.found_by)+'</td><td>'+esc(r.storage_location)+'</td><td>'+esc(r.date_encoded)+'</td><td>'+esc(r.retention_end)+'</td></tr>';
        }).join('');
    }
    function renderUnresolved(rows) {
        var tb = document.getElementById('dash-tbody-unresolved');
        if (!tb) return;
        if (!rows || !rows.length) { tb.innerHTML = '<tr><td colspan="5" class="td-empty">No unresolved claimants.</td></tr>'; return; }
        tb.innerHTML = rows.map(function(r){
            var ticketLabel = r.display_ticket_id || r.id;
            var ticketLink = '<a href="#" class="tbl-item-link" data-item-id="'+esc(r.id)+'">'+esc(ticketLabel)+'</a>';
            return '<tr><td>'+ticketLink+'</td><td>'+esc(r.item_type)+'</td><td>'+esc(r.parsed_department)+'</td><td>'+esc(r.parsed_student_number)+'</td><td>'+esc(r.parsed_contact)+'</td></tr>';
        }).join('');
    }
    function renderVerification(rows) {
        var tb = document.getElementById('dash-tbody-verification');
        if (!tb) return;
        if (!rows || !rows.length) { tb.innerHTML = '<tr><td colspan="6" class="td-empty">No items for verification.</td></tr>'; return; }
        tb.innerHTML = rows.map(function(r){
            return '<tr><td>'+linkId(r.id)+'</td><td>'+esc(r.item_type)+'</td><td>'+esc(r.found_at)+'</td><td>'+esc(r.retention_end)+'</td><td>'+esc(r.storage_location)+'</td><td>'+esc(r.date_encoded)+'</td></tr>';
        }).join('');
    }
    function fmtActivityTime(iso) {
        if (!iso) return '';
        try {
            var d = new Date(iso);
            return d.toLocaleString(undefined, { month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit' });
        } catch(e){ return ''; }
    }
    function renderRecentActivity(rows) {
        var wrap = document.getElementById('dash-recent-activity-list');
        if (!wrap) return;
        if (!rows || !rows.length) { wrap.innerHTML = '<p class="activity-empty">No recent activity.</p>'; return; }
        wrap.innerHTML = rows.map(function(act){
            var action = String(act.action||'').toLowerCase();
            var itemId = act.item_id || '';
            var itemName = act.item_name || '';
            var dt = fmtActivityTime(act.created_at);
            var namePart = (itemName && itemName !== itemId) ? ' ('+esc(itemName)+')' : '';
            var linkInner = esc(itemId) + namePart;
            var link = '<a href="#" class="activity-item-link" data-item-id="'+esc(itemId)+'">'+linkInner+'</a>';
            var inner = '';
            if (action === 'matched' || action === 'match') {
                inner = '<p class="activity-label">Potential Match!</p><p class="activity-text">'+link+' matched a user report.</p>';
            } else if (action === 'lost' || action === 'reported') {
                inner = '<p class="activity-label">Lost Item!</p><p class="activity-text">'+link+' has been reported to be lost.</p>';
            } else {
                inner = '<p class="activity-label">Found Item!</p><p class="activity-text">You have recently inputted the item (<a href="#" class="activity-item-link" data-item-id="'+esc(itemId)+'">'+esc(itemId)+'</a>).</p>';
            }
            if (dt) inner += '<p class="activity-time">'+esc(dt)+'</p>';
            return '<div class="activity-item">'+inner+'</div>';
        }).join('');
    }
    function apply(json) {
        if (!json || !json.ok) return;
        document.querySelectorAll('[data-summary-stat]').forEach(function(el){
            var k = el.getAttribute('data-summary-stat');
            if (k === 'internal_recovered') el.textContent = fmt(json.internal_recovered);
            if (k === 'external_ids') el.textContent = fmt(json.external_ids);
            if (k === 'unresolved') el.textContent = fmt(json.unresolved);
            if (k === 'for_verification') el.textContent = fmt(json.for_verification);
        });
        window._pieData = json.pie_data || [];
        window._barData = json.bar_data || [];
        window._pieCaption = json.pie_caption || '';
        window._barCaption = json.bar_caption || '';
        if (typeof window.refreshDashboardCharts === 'function') window.refreshDashboardCharts();
        renderRecoveredInternal(json.recovered_internal);
        renderRecoveredExternal(json.recovered_external);
        renderUnresolved(json.unresolved_items);
        renderVerification(json.verification_items);
        renderRecentActivity(json.recent_activity);
    }
    var _summaryWarned = false;
    function tick() {
        if (document.visibilityState !== 'visible') return;
        fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        })
            .then(function(r) {
                var ct = r.headers.get('content-type') || '';
                if (ct.indexOf('application/json') === -1) {
                    return r.text().then(function(t) {
                        throw new Error('Non-JSON response (' + r.status + ')');
                    });
                }
                return r.json();
            })
            .then(function(json) {
                if (!json || !json.ok) {
                    if (!_summaryWarned && window.console && console.warn) {
                        _summaryWarned = true;
                        console.warn('Dashboard summary: invalid or failed JSON payload.');
                    }
                    return;
                }
                apply(json);
            })
            .catch(function(err) {
                if (!_summaryWarned && window.console && console.warn) {
                    _summaryWarned = true;
                    console.warn('Dashboard summary fetch failed:', err && err.message ? err.message : err);
                }
            });
    }
    window.refreshDashboardSummary = tick;
    setInterval(tick, 15000);
    document.addEventListener('visibilitychange', function(){ if (document.visibilityState === 'visible') tick(); });
    setTimeout(tick, 0);
})();
</script>

<script src="{{ asset('assets/photo-picker.js') }}?v=2"></script>
<script>
/* ── Dashboard: Item Recovered Report (shared partial) + success / link tickets ── */
(function(){
    var m = document.querySelector('meta[name="csrf-token"]');
    var _CSRF = m ? m.getAttribute('content') : '';
    var BARCODE_CTX_URL = '{{ route("admin.found.barcode-context") }}';
    var ENCODE_URL = '{{ route("admin.encode") }}';
    var ITEM_LOOKUP = @json(route('admin.item'));

    var encodeModal = document.getElementById('itemLostReportModal');
    var successOverlay = document.getElementById('encodeSuccessOverlay');
    var dupModal = document.getElementById('barcodeDupModal');
    var todayStr = new Date().toISOString().split('T')[0];

    function escRev(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function rowRev(label, val) {
        if (val == null || val === '') val = '—';
        return '<div style="margin-bottom:8px;display:flex;gap:12px;flex-wrap:wrap;"><span style="min-width:170px;font-weight:600;color:#374151;">' + escRev(label) + '</span><span style="color:#111827;">' + escRev(String(val)) + '</span></div>';
    }
    function buildDashboardEncodeItemReviewHtml() {
        var img = '';
        if (_encItemPhoto) {
            img = '<div style="margin-bottom:8px;"><span style="font-weight:600;display:block;margin-bottom:4px;">Photo</span><img src="' + escRev(_encItemPhoto) + '" alt="" style="max-width:200px;border-radius:8px;border:1px solid #e5e7eb;"></div>';
        }
        return [
            rowRev('Barcode ID', (document.getElementById('encBarcodeId') || {}).value),
            rowRev('Category', (document.getElementById('encCategory') || {}).value),
            rowRev('Item', (document.getElementById('encItem') || {}).value),
            rowRev('Color', (document.getElementById('encColor') || {}).value),
            rowRev('Brand', (document.getElementById('encBrand') || {}).value),
            rowRev('Item Description', (document.getElementById('encDescription') || {}).value),
            rowRev('Storage Location', (document.getElementById('encStorage') || {}).value),
            rowRev('Found At', (document.getElementById('encFoundAt') || {}).value),
            rowRev('Found In', (document.getElementById('encFoundIn') || {}).value),
            rowRev('Found By', (document.getElementById('encFoundBy') || {}).value),
            rowRev('Date Found', (document.getElementById('encDateFound') || {}).value),
            img
        ].join('');
    }

    var _encItemPhoto = null;
    var _encItemPP = null;
    if (typeof PhotoPicker !== 'undefined' && PhotoPicker.init) {
        try {
            _encItemPP = PhotoPicker.init({ el: 'encodeItemPhotoPicker', onChange: function(d){ _encItemPhoto = d || null; } });
        } catch (e) { console.warn('encodeItemPhotoPicker', e); }
    }

    (function(){
        var sel = document.getElementById('encFoundAt');
        var row = document.getElementById('encFoundInRow');
        window._syncEncFoundInRow = function(){
            if (!sel || !row) return;
            row.style.display = sel.value ? '' : 'none';
            if (!sel.value) {
                var fin = document.getElementById('encFoundIn');
                if (fin) fin.value = '';
            }
        };
        if (sel) sel.addEventListener('change', window._syncEncFoundInRow);
    })();

    function showEncodeBanner(msg) {
        var el = document.getElementById('encodeModalErrorBanner');
        if (!el) return;
        el.textContent = msg || '';
        el.style.display = msg ? 'block' : 'none';
        if (msg) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function hideEncodeBanner() {
        showEncodeBanner('');
    }

    window.openBarcodeDupModal = function(msg){
        var t = document.getElementById('barcodeDupModalText');
        if (t) t.textContent = msg || '';
        if (dupModal) {
            dupModal.classList.add('open');
            dupModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    };
    window.closeBarcodeDupModal = function(){
        if (dupModal) {
            dupModal.classList.remove('open');
            dupModal.setAttribute('aria-hidden', 'true');
        }
        var enc = document.getElementById('itemLostReportModal');
        if (enc && enc.classList.contains('report-modal-open')) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = '';
    };

    window.openEncodeModal = function(e) {
        if (e) e.preventDefault();
        var form = document.getElementById('encodeItemForm');
        if (form) form.reset();
        if (_encItemPP && typeof _encItemPP.clear === 'function') _encItemPP.clear();
        _encItemPhoto = null;
        hideEncodeBanner();
        if (typeof window._syncEncFoundInRow === 'function') window._syncEncFoundInRow();
        var df = document.getElementById('encDateFound');
        if (df) df.setAttribute('max', todayStr);
        if (encodeModal) {
            encodeModal.classList.add('report-modal-open');
            document.body.style.overflow = 'hidden';
        }
    };
    window.closeEncodeModal = function() {
        if (encodeModal) encodeModal.classList.remove('report-modal-open');
        document.body.style.overflow = '';
        hideEncodeBanner();
    };

    if (encodeModal) {
        encodeModal.addEventListener('click', function(e) {
            if (e.target === encodeModal) closeEncodeModal();
        });
    }
    if (successOverlay) {
        successOverlay.addEventListener('click', function(e) {
            if (e.target === successOverlay) cancelEncodeSuccess();
        });
    }
    var dupOk = document.getElementById('barcodeDupModalOk');
    if (dupOk) dupOk.addEventListener('click', function() { window.closeBarcodeDupModal(); });

    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        var arm = document.getElementById('adminEncodeReviewModal');
        if (arm && arm.classList.contains('report-modal-open')) {
            if (typeof closeAdminEncodeReviewModal === 'function') closeAdminEncodeReviewModal();
            var ob = window._adminEncodeReview && window._adminEncodeReview.onBack;
            if (typeof ob === 'function') ob();
            return;
        }
        if (dupModal && dupModal.classList.contains('open')) { window.closeBarcodeDupModal(); return; }
        if (encodeModal && encodeModal.classList.contains('report-modal-open')) { closeEncodeModal(); return; }
        if (successOverlay && successOverlay.style.display === 'flex') cancelEncodeSuccess();
    });

    var encBarcode = document.getElementById('encBarcodeId');
    if (encBarcode) {
        encBarcode.addEventListener('blur', function() {
            var code = this.value.trim();
            if (!code) return;
            fetch(ITEM_LOOKUP + '?id=' + encodeURIComponent(code))
                .then(function(r) { return r.json(); })
                .then(function(j) { if (j.ok && j.data) autoFillForm(j.data); })
                .catch(function() {});
        });
    }

    function autoFillForm(item) {
        function setVal(id, val) { var el = document.getElementById(id); if (el && val) el.value = val; }
        function parseItemName(d) { var m = d && d.match(/^Item:\s*(.+?)(\n|$)/m); return m ? m[1].trim() : ''; }
        function cleanDesc(d) {
            if (!d) return '';
            return d.replace(/^Item:\s*.+?(\n|$)/m, '').replace(/^Item Type:\s*.+?(\n|$)/m, '').replace(/^Student Number:\s*.+?(\n|$)/m, '').replace(/^Contact:\s*.+?(\n|$)/m, '').replace(/^Department:\s*.+?(\n|$)/m, '').trim();
        }
        setVal('encCategory', item.item_type);
        setVal('encItem', parseItemName(item.item_description) || item.brand);
        setVal('encColor', item.color);
        setVal('encBrand', item.brand);
        setVal('encStorage', item.storage_location);
        setVal('encFoundAt', item.found_at_campus != null && item.found_at_campus !== '' ? item.found_at_campus : '');
        setVal('encFoundIn', item.found_at_detail != null && item.found_at_detail !== '' ? item.found_at_detail : '');
        if (typeof window._syncEncFoundInRow === 'function') window._syncEncFoundInRow();
        setVal('encFoundBy', item.found_by);
        setVal('encDateFound', item.date_encoded ? String(item.date_encoded).split(' ')[0] : '');
        setVal('encDescription', cleanDesc(item.item_description));
    }

    function fetchBarcodeContext(barcode) {
        return fetch(BARCODE_CTX_URL + '?barcode=' + encodeURIComponent(barcode), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) { return r.json(); });
    }

    function runDashboardEncode(btn) {
        var payload = {
            barcode_id: document.getElementById('encBarcodeId').value.trim(),
            category: (document.getElementById('encCategory') || {}).value || '',
            item: document.getElementById('encItem').value.trim(),
            color: (document.getElementById('encColor') || {}).value || '',
            brand: (document.getElementById('encBrand') || {}).value || '',
            found_at: (document.getElementById('encFoundAt') || {}).value || '',
            found_in: (document.getElementById('encFoundIn') || {}).value || '',
            found_by: (document.getElementById('encFoundBy') || {}).value || '',
            date_found: (document.getElementById('encDateFound') || {}).value || '',
            storage_location: (document.getElementById('encStorage') || {}).value || '',
            item_description: document.getElementById('encDescription').value.trim(),
            imageDataUrl: _encItemPhoto || null
        };
        btn.disabled = true;
        btn.textContent = 'Saving…';
        fetch(ENCODE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': _CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json().then(function(j) { return { ok: r.ok, status: r.status, j: j }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.textContent = 'Next';
            if (res.ok && res.j && res.j.ok) {
                closeEncodeModal();
                if (typeof closeAdminEncodeReviewModal === 'function') closeAdminEncodeReviewModal();
                var am = res.j.auto_match || {};
                var matchedUrl = @json(route('admin.matched'));
                var msg = 'Item has been encoded successfully!';
                if (am.linked && am.lost_report_id) {
                    msg += ' Lost report ' + am.lost_report_id + ' was automatically matched (For Verification). Use Matching in the sidebar to complete the workflow.';
                } else {
                    msg += ' No lost report met the automatic match threshold.';
                }
                if (typeof window.appUiSuccess === 'function') {
                    window.appUiSuccess({
                        title: 'Success',
                        message: msg,
                        ticketId: payload.barcode_id,
                        ticketDisplay: 'Barcode ID: ' + payload.barcode_id,
                        onClose: function () { location.reload(); }
                    });
                } else {
                    document.getElementById('encodeSuccessHeading').textContent = 'Success';
                    document.getElementById('encodeSuccessMsg').textContent = 'Item has been encoded successfully!';
                    document.getElementById('encodeSuccessBid').textContent = 'Barcode ID: ' + payload.barcode_id;
                    window._encodedBarcodeId = payload.barcode_id;
                    var notice = document.getElementById('encodeTicketNotice');
                    var autoBody = document.getElementById('encodeAutoMatchBody');
                    if (notice && autoBody) {
                        if (am.linked && am.lost_report_id) {
                            autoBody.innerHTML = 'Lost report <strong>' + escStr(am.lost_report_id) + '</strong> was automatically matched to this item. '
                                + 'Status is <strong>For Verification</strong>. Notifications were sent to the student and admin. '
                                + 'Complete the claim workflow from <a href="' + escStr(matchedUrl) + '" style="color:#92400e;font-weight:600;">Matching</a>.';
                            notice.style.display = 'block';
                        } else {
                            autoBody.textContent = 'No lost report met the automatic match threshold. The item stays unclaimed until a strong match is found.';
                            notice.style.display = 'block';
                        }
                    }
                    successOverlay.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
                if (typeof window.refreshDashboardSummary === 'function') window.refreshDashboardSummary();
            } else {
                var err = (res.j && res.j.error) ? res.j.error : 'Could not encode item. Try again.';
                showEncodeBanner(err);
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = 'Next';
            showEncodeBanner('Network error. Check your connection and try again.');
        });
    }

    var sub = document.getElementById('encodeItemSubmitBtn');
    if (sub) {
        sub.addEventListener('click', function() {
            hideEncodeBanner();
            var barcode = document.getElementById('encBarcodeId').value.trim();
            var item = document.getElementById('encItem').value.trim();
            var col = (document.getElementById('encColor') || {}).value || '';
            var desc = document.getElementById('encDescription').value.trim();
            if (!barcode) { showEncodeBanner('Barcode ID is required.'); document.getElementById('encBarcodeId').focus(); return; }
            if (!item) { showEncodeBanner('Item is required.'); document.getElementById('encItem').focus(); return; }
            if (!col) { showEncodeBanner('Color is required.'); document.getElementById('encColor').focus(); return; }
            if (!desc) { showEncodeBanner('Item Description is required.'); document.getElementById('encDescription').focus(); return; }
            var df = (document.getElementById('encDateFound') || {}).value || '';
            if (df && df > todayStr) { showEncodeBanner('Date Found cannot be in the future.'); return; }

            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Checking…';
            fetchBarcodeContext(barcode)
                .then(function(ctx) {
                    if (!ctx.ok) throw new Error(ctx.error || 'Could not verify barcode.');
                    if (ctx.exists) {
                        btn.disabled = false;
                        btn.textContent = 'Next';
                        var n = ctx.linked_report_count || 0;
                        var msg = n >= 1
                            ? 'This barcode is already registered. It has ' + n + ' linked lost report(s). You cannot register a duplicate found item with the same ID.'
                            : 'This barcode is already in use. You cannot register a duplicate found item.';
                        window.openBarcodeDupModal(msg);
                        return;
                    }
                    btn.disabled = false;
                    btn.textContent = 'Next';
                    document.getElementById('adminEncodeReviewTitle').textContent = 'Item Recovered Report';
                    document.getElementById('adminEncodeReviewSummary').innerHTML = buildDashboardEncodeItemReviewHtml();
                    window._adminEncodeReview = {
                        runSubmit: function () { return runDashboardEncode(btn); },
                        onBack: function () {
                            var enc = document.getElementById('itemLostReportModal');
                            if (enc) enc.classList.add('report-modal-open');
                            document.body.style.overflow = 'hidden';
                        }
                    };
                    closeEncodeModal();
                    if (typeof openAdminEncodeReviewModal === 'function') openAdminEncodeReviewModal();
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.textContent = 'Next';
                    showEncodeBanner((err && err.message) ? err.message : 'Network error. Try again.');
                });
        });
    }

    window.cancelEncodeSuccess = function() {
        successOverlay.style.display = 'none';
        document.body.style.overflow = '';
    };
    window.confirmEncodeSuccess = function() {
        successOverlay.style.display = 'none';
        document.body.style.overflow = '';
        window.location.reload();
    };

    function escStr(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
})();
</script>
@endpush

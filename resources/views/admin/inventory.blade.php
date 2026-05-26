@extends('layouts.admin')

@section('title', 'Inventory')

@push('styles')
<style>
/* ── Item Detail Modal ──────────────────────────────────────────── */
.idm-overlay{display:none;position:fixed;inset:0;z-index:1500;align-items:center;justify-content:center;background:rgba(0,0,0,.5);}
.idm-overlay.open{display:flex;}
.idm-modal{background:#fff;border-radius:12px;width:min(680px,96vw);max-height:92vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.22);display:flex;flex-direction:column;}
.idm-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.idm-header-title{color:#fff;font-size:16px;font-weight:700;margin:0;}
.idm-close-btn{background:none;border:none;color:#fff;font-size:20px;cursor:pointer;padding:2px 6px;border-radius:4px;opacity:.85;transition:opacity .15s;line-height:1;}
.idm-close-btn:hover{opacity:1;}
.idm-body{display:flex;flex:1;min-height:0;}
.idm-left{width:42%;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding:24px 18px 20px;border-right:1px solid #e5e7eb;background:#fafafa;}
.idm-photo{width:160px;height:130px;object-fit:cover;border-radius:8px;border:1px solid #e0e0e0;}
.idm-photo-placeholder{width:160px;height:130px;background:#f3f4f6;border-radius:8px;border:1px solid #e0e0e0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:#9ca3af;}
.idm-photo-placeholder i{font-size:32px;}
.idm-photo-placeholder span{font-size:11px;font-weight:500;}
.idm-barcode{margin:10px 0 0;font-size:13px;color:#374151;font-weight:600;text-align:center;}
.idm-right{flex:1;padding:20px 24px;display:flex;flex-direction:column;overflow-y:auto;}
.idm-section-title{font-size:14px;font-weight:700;color:#111827;margin:0 0 14px;text-align:center;}
.idm-info-row{display:flex;align-items:baseline;gap:8px;padding:6px 0;border-bottom:1px solid #f3f4f6;}
.idm-info-row:last-child{border-bottom:none;}
.idm-info-label{font-size:12px;color:#6b7280;min-width:120px;flex-shrink:0;}
.idm-info-value{font-size:12px;font-weight:700;color:#111827;text-align:left;flex:1;}
.idm-info-card{background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin-bottom:12px;}
.idm-no-claimants{font-size:13px;color:#6b7280;text-align:center;padding:8px 0;}
.idm-footer{display:flex;justify-content:flex-end;gap:10px;padding:14px 24px;border-top:1px solid #e5e7eb;flex-shrink:0;}
.idm-btn-cancel{padding:9px 22px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;}
.idm-btn-cancel:hover{background:#f3f4f6;}
.idm-btn-confirm{padding:9px 22px;border:none;border-radius:7px;background:#8b0000;color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;}
.idm-btn-confirm:hover{opacity:.88;}
.idm-btn-confirm:disabled{opacity:.45;cursor:not-allowed;}
.idm-btn-dispose{padding:9px 22px;border:none;border-radius:7px;background:#dc2626;color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;}
.idm-btn-dispose:hover{opacity:.88;}
.idm-btn-dispose:disabled{opacity:.45;cursor:not-allowed;}
@media(max-width:560px){.idm-body{flex-direction:column;}.idm-left{width:100%;border-right:none;border-bottom:1px solid #e5e7eb;}.idm-claimants-grid{grid-template-columns:1fr;}}

/* ── Success overlay ────────────────────────────────────────────── */
.idm-success-overlay{display:none;position:fixed;inset:0;z-index:1600;align-items:center;justify-content:center;background:rgba(0,0,0,.45);}
.idm-success-overlay.open{display:flex;}
.idm-success-box{background:#fff;border-radius:14px;width:min(380px,94vw);padding:40px 32px 28px;display:flex;flex-direction:column;align-items:center;gap:16px;box-shadow:0 16px 48px rgba(0,0,0,.22);}
.idm-success-icon{width:72px;height:72px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;}
.idm-success-icon i{color:#fff;font-size:34px;}
.idm-success-title{font-size:22px;font-weight:700;color:#111827;margin:0;}
.idm-success-msg{font-size:14px;color:#4b5563;margin:0;text-align:center;}
.idm-success-cancel{padding:9px 28px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;}
.idm-success-cancel:hover{background:#f3f4f6;}
</style>
@endpush

@section('content')

  <div class="dashboard-header-row">
    <h1 class="page-title">Inventory</h1>
  </div>

  {{-- ── Tab navigation ───────────────────────────────────────────────────────── --}}
  <div class="browse-toolbar" style="margin-bottom:12px;">
    <div class="matched-tabs report-tabs--browse">
      <a href="{{ route('admin.matched') }}?tab=all" class="matched-tab-text" style="text-decoration:none;">
        <i class="fa-solid fa-list" style="margin-right:5px;font-size:12px;"></i>All Items
      </a>
      <a href="{{ route('admin.matched') }}?tab=guest" class="matched-tab-text" style="text-decoration:none;">
        <i class="fa-solid fa-id-card" style="margin-right:5px;font-size:12px;"></i>Guest Items
      </a>
      <span class="matched-tab-text matched-tab-active">
        <i class="fa-solid fa-boxes-stacked" style="margin-right:5px;font-size:12px;"></i>Inventory
      </span>
    </div>
  </div>

  {{-- ── Filter bar ─────────────────────────────────────────────────────────── --}}
  <div class="browse-toolbar" style="margin-bottom:16px;">
    <div class="browse-filter-form">
      <div class="browse-filter-filters" style="flex-wrap:wrap;">
        <form method="GET" action="{{ route('admin.inventory') }}" style="display:contents;">
          <label class="sr-only" for="invCategoryFilter">Filter by category</label>
          <select id="invCategoryFilter" name="category" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by category">
            <option value="" {{ !$category ? 'selected' : '' }}>Filter By Category</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
          <label class="sr-only" for="invExpiredFilter">Filter by condition</label>
          <select id="invExpiredFilter" name="expired" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by condition">
            <option value="" {{ !$expired ? 'selected' : '' }}>All Conditions</option>
            <option value="1" {{ $expired ? 'selected' : '' }}>Expired Items</option>
          </select>
          <button type="submit" class="found-btn" style="background:#374151;color:#fff;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">Apply</button>
        </form>
        <label class="sr-only" for="invDateFilter">Filter by date found</label>
        <select id="invDateFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by date found">
          <option value="">Filter By Date Found</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="3months">Last 3 Months</option>
          <option value="year">This Year</option>
        </select>
      </div>
    </div>
  </div>

  {{-- ── Overdue retention alert ─────────────────────────────────────────────── --}}
  @if($overdueCount > 0)
    <div style="display:flex;align-items:center;gap:10px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:11px 14px;margin-bottom:16px;">
      <i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;font-size:16px;flex-shrink:0;"></i>
      <span style="font-size:13px;color:#991b1b;flex:1;">
        There are <strong>{{ $overdueCount }}</strong> item(s) that have exceeded the retention policy.
      </span>
      <a href="{{ route('admin.inventory') }}?expired=1" style="font-size:12px;font-weight:700;color:#dc2626;text-decoration:underline;">Dispose Items</a>
    </div>
  @endif

  {{-- ── Recovered Items (Internal) table ──────────────────────────────────── --}}
  <div class="inventory-card matched-reports-card">
    <div class="inventory-title" style="display:flex;align-items:center;justify-content:space-between;">
      <span>Recovered Items (Internal)</span>
      <a href="{{ route('admin.export.internal') }}" class="found-btn" style="background:#15803d;color:#fff;padding:6px 14px;border-radius:7px;font-size:12px;font-weight:600;text-decoration:none;">+ Export</a>
    </div>
    <div class="table-wrapper">
      <table class="matched-reports-table">
        <thead>
          <tr>
            <th>Reference ID</th>
            <th>Category</th>
            <th>Found At</th>
            <th>Date Found</th>
            <th>Retention End</th>
            <th>Storage Location</th>
            <th>Timestamp</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $item)
            <tr class="{{ ($item->is_overdue ?? false) ? 'matched-row-overdue' : '' }}" data-date-encoded="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}">
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->item_type ?? '—' }}</td>
              <td>{{ $item->found_at ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td class="retention-cell">
                {{ $item->retention_end ?? '—' }}
                @if($item->is_overdue ?? false)
                  <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRED</span>
                @elseif($item->expires_in_30_days ?? false)
                  <span class="matched-pill-expiring">EXPIRING</span>
                @endif
              </td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i') : '—' }}</td>
              <td class="found-action-cell">
                <button type="button"
                        class="found-btn idm-view-btn"
                        style="background:#1976d2;color:#fff;padding:5px 12px;font-size:12px;"
                        data-id="{{ $item->id }}">
                  View
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="table-empty">No items in inventory.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ── School-Year Archive Download ─────────────────────────────────────── --}}
  <div class="inventory-card matched-reports-card" style="margin-top:16px;">
    <div class="inventory-title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
      <span>Archive School Year Records</span>
    </div>
    <p style="font-size:13px;color:#475569;margin-bottom:12px;">
      Download a CSV of all found items and lost reports created during a school year (June 1 – May 31).
      This is useful for end-of-year archiving and record-keeping.
    </p>
    <form method="GET" action="{{ route('admin.export.archive') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      @php
        $currentMonth = now()->month;
        $currentYear  = now()->year;
        $syStartYear  = $currentMonth >= 6 ? $currentYear : $currentYear - 1;
        $options = [];
        for ($y = $syStartYear; $y >= $syStartYear - 4; $y--) {
            $label    = 'SY ' . $y . '–' . ($y + 1);
            $value    = substr($y, 2) . substr($y + 1, 2);
            $options[] = ['label' => $label, 'value' => $value];
        }
      @endphp
      <select name="sy" class="found-filter-select browse-filter-select matched-filter-select" style="min-width:180px;">
        @foreach($options as $opt)
          <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
        @endforeach
      </select>
      <button type="submit" class="found-btn" style="background:#15803d;color:#fff;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;display:flex;align-items:center;gap:6px;">
        <i class="fa-solid fa-download"></i> Download Archive CSV
      </button>
    </form>
  </div>

  {{-- ── Item Detail Modal ───────────────────────────────────────────────────── --}}
  <div id="idmOverlay" class="idm-overlay" role="dialog" aria-modal="true" aria-labelledby="idmTitle">
    <div class="idm-modal">
      <div class="idm-header">
        <h2 class="idm-header-title" id="idmTitle">Item Details</h2>
        <button type="button" class="idm-close-btn" id="idmCloseBtn" aria-label="Close">&times;</button>
      </div>

      {{-- Two-panel body --}}
      <div class="idm-body">
        {{-- Left: photo + barcode --}}
        <div class="idm-left">
          <div id="idmPhotoWrap">
            <div class="idm-photo-placeholder" id="idmPhotoPlaceholder">
              <i class="fa-solid fa-image"></i>
              <span>No Image</span>
            </div>
            <img id="idmPhoto" class="idm-photo" src="" alt="Item photo" style="display:none;">
          </div>
          <p class="idm-barcode" id="idmBarcode">—</p>
        </div>

        {{-- Right: general info --}}
        <div class="idm-right">
          <p class="idm-section-title">General Information</p>
          <div class="idm-info-card">
            <div class="idm-info-row"><span class="idm-info-label">Category</span><span class="idm-info-value" id="idmCategory">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Item</span><span class="idm-info-value" id="idmItem">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Color</span><span class="idm-info-value" id="idmColor">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Brand</span><span class="idm-info-value" id="idmBrand">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Item Description</span><span class="idm-info-value" id="idmDescription">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Storage Location</span><span class="idm-info-value" id="idmStorage">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Found At</span><span class="idm-info-value" id="idmFoundAt">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Found By</span><span class="idm-info-value" id="idmFoundBy">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Encoded By</span><span class="idm-info-value" id="idmEncodedBy">—</span></div>
            <div class="idm-info-row"><span class="idm-info-label">Date Found</span><span class="idm-info-value" id="idmDateFound">—</span></div>
          </div>
        </div>
      </div>

      <div class="idm-footer">
        <button type="button" class="idm-btn-cancel" id="idmCancelBtn">Cancel</button>
        <button type="button" class="idm-btn-dispose" id="idmDisposeBtn" style="display:none;">Dispose Item</button>
      </div>
    </div>
  </div>

  {{-- ── Success overlay ────────────────────────────────────────────────────── --}}
  <div id="idmSuccessOverlay" class="idm-success-overlay" role="status">
    <div class="idm-success-box">
      <div class="idm-success-icon"><i class="fa-solid fa-check"></i></div>
      <p class="idm-success-title">Success</p>
      <p class="idm-success-msg">Item has been notified successfully!</p>
      <button type="button" class="idm-success-cancel" id="idmSuccessClose">Cancel</button>
    </div>
  </div>

@endsection

@push('scripts')
<script>
(function () {
  var overlay      = document.getElementById('idmOverlay');
  var closeBtn     = document.getElementById('idmCloseBtn');
  var cancelBtn    = document.getElementById('idmCancelBtn');
  var disposeBtn   = document.getElementById('idmDisposeBtn');
  var successOv    = document.getElementById('idmSuccessOverlay');
  var successClose = document.getElementById('idmSuccessClose');
  var currentItemId = null;
  var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // ── Open modal ──────────────────────────────────────────────────────────
  document.querySelectorAll('.idm-view-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.dataset.id;
      currentItemId = id;

      // Reset state
      disposeBtn.style.display = 'none';
      disposeBtn.disabled = false;
      disposeBtn.textContent = 'Dispose Item';
      document.getElementById('idmBarcode').textContent = 'Loading…';
      ['idmCategory','idmItem','idmColor','idmBrand','idmDescription','idmStorage','idmFoundAt','idmFoundBy','idmEncodedBy','idmDateFound'].forEach(function(k){ document.getElementById(k).textContent = '—'; });
      document.getElementById('idmPhoto').style.display = 'none';
      document.getElementById('idmPhotoPlaceholder').style.display = '';

      overlay.classList.add('open');

      fetch('{{ url("admin/inventory/item-detail") }}/' + encodeURIComponent(id), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) { alert(data.error || 'Failed to load item.'); closeModal(); return; }

        document.getElementById('idmBarcode').textContent   = 'Reference ID: ' + data.id;
        document.getElementById('idmCategory').textContent  = data.item_type     || '—';
        document.getElementById('idmItem').textContent      = data.item_name     || '—';
        document.getElementById('idmColor').textContent     = data.color         || '—';
        document.getElementById('idmBrand').textContent     = data.brand         || '—';
        document.getElementById('idmDescription').textContent = data.item_description || '—';
        document.getElementById('idmStorage').textContent   = data.storage_location || '—';
        document.getElementById('idmFoundAt').textContent   = data.found_at      || '—';
        document.getElementById('idmFoundBy').textContent   = data.found_by      || '—';
        document.getElementById('idmEncodedBy').textContent = data.encoded_by    || '—';
        document.getElementById('idmDateFound').textContent = data.date_found    || '—';

        if (data.image_data) {
          var img = document.getElementById('idmPhoto');
          img.src = data.image_data;
          img.style.display = '';
          document.getElementById('idmPhotoPlaceholder').style.display = 'none';
        }

        // Show dispose button only for expired items
        disposeBtn.style.display = data.is_overdue ? '' : 'none';
      })
      .catch(function () { alert('Network error loading item details.'); closeModal(); });
    });
  });

  // ── Dispose ─────────────────────────────────────────────────────────────
  disposeBtn.addEventListener('click', function () {
    if (!currentItemId) return;
    if (!confirm('Are you sure you want to dispose of this item? This cannot be undone.')) return;

    disposeBtn.disabled = true;
    disposeBtn.textContent = 'Disposing…';

    fetch('{{ url("admin/inventory/dispose") }}/' + encodeURIComponent(currentItemId), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      disposeBtn.textContent = 'Dispose Item';
      disposeBtn.disabled = false;
      if (data.ok) {
        closeModal();
        document.querySelector('.idm-success-msg').textContent = 'Item has been disposed successfully.';
        successOv.classList.add('open');
        var row = document.querySelector('.idm-view-btn[data-id="' + currentItemId + '"]');
        if (row) row.closest('tr').remove();
      } else {
        alert(data.error || 'Failed to dispose item.');
      }
    })
    .catch(function () {
      disposeBtn.textContent = 'Dispose Item';
      disposeBtn.disabled = false;
      alert('Network error. Please try again.');
    });
  });

  // ── Close helpers ────────────────────────────────────────────────────────
  function closeModal() {
    overlay.classList.remove('open');
    confirmBtn.textContent = 'Confirm';
    disposeBtn.textContent = 'Dispose Item';
    disposeBtn.disabled = false;
  }

  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

  successClose.addEventListener('click', function () { successOv.classList.remove('open'); });
  successOv.addEventListener('click', function (e) { if (e.target === successOv) successOv.classList.remove('open'); });
})();
</script>
<script>
// ── Date filter (client-side) ─────────────────────────────────────────────
(function(){
  var sel = document.getElementById('invDateFilter');
  if(!sel) return;
  function applyDate(){
    var val = sel.value;
    var now = new Date();
    var todayStr  = now.toISOString().slice(0,10);
    var weekStart = new Date(now); weekStart.setDate(now.getDate()-now.getDay());
    var weekStr   = weekStart.toISOString().slice(0,10);
    var monthStr  = new Date(now.getFullYear(),now.getMonth(),1).toISOString().slice(0,10);
    var m3Str     = new Date(now.getFullYear(),now.getMonth()-3,now.getDate()).toISOString().slice(0,10);
    var yearStr   = new Date(now.getFullYear(),0,1).toISOString().slice(0,10);
    document.querySelectorAll('.matched-reports-table tbody tr[data-date-encoded]').forEach(function(r){
      if(r.querySelector('td[colspan]')) return;
      if(!val){ r.style.display=''; return; }
      var d = r.getAttribute('data-date-encoded')||'';
      var show = true;
      if(val==='today')   show = d===todayStr;
      if(val==='week')    show = d>=weekStr && d<=todayStr;
      if(val==='month')   show = d>=monthStr && d<=todayStr;
      if(val==='3months') show = d>=m3Str && d<=todayStr;
      if(val==='year')    show = d>=yearStr && d<=todayStr;
      r.style.display = show?'':'none';
    });
  }
  sel.addEventListener('change', applyDate);
})();
</script>
@endpush

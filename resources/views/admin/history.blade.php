@extends('layouts.admin')

@section('title', 'History')

@push('styles')
<style>
/* ── Tab override: white bg + red underline ────────────────────── */
.matched-tab-text.matched-tab-active{background-color:#fff !important;border-bottom-color:#8b0000 !important;}

/* ── Item Details Modal (History) ──────────────────────────────── */
.hdm-overlay{display:none;position:fixed;inset:0;z-index:1500;align-items:center;justify-content:center;background:rgba(0,0,0,.48);}
.hdm-overlay.open{display:flex;}
.hdm-modal{background:#fff;border-radius:12px;width:min(720px,97vw);max-height:92vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.22);display:flex;flex-direction:column;}
.hdm-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.hdm-header-title{color:#fff;font-size:16px;font-weight:700;margin:0;}
.hdm-close-btn{background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;border-radius:4px;opacity:.85;transition:opacity .15s;line-height:1;}
.hdm-close-btn:hover{opacity:1;}
.hdm-body{display:flex;flex:1;min-height:0;}
.hdm-left{width:40%;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding:24px 18px 20px;border-right:1px solid #e5e7eb;background:#fafafa;}
.hdm-photo{width:160px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e0e0e0;}
.hdm-photo-placeholder{width:160px;height:120px;background:#f3f4f6;border-radius:8px;border:1px solid #e0e0e0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:#9ca3af;font-size:11px;}
.hdm-barcode{margin:10px 0 0;font-size:13px;color:#374151;font-weight:600;text-align:center;}
.hdm-claimant-section{width:100%;margin-top:18px;}
.hdm-claimant-title{font-size:13px;font-weight:700;color:#8b0000;margin:0 0 12px;text-align:center;padding-bottom:6px;border-bottom:1px solid #e5e7eb;}
.hdm-claimant-field{margin-bottom:10px;}
.hdm-claimant-label{display:block;font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;}
.hdm-claimant-box{width:100%;box-sizing:border-box;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:13px;font-weight:600;color:#111827;min-height:34px;}
.hdm-right{flex:1;padding:24px 26px 20px;display:flex;flex-direction:column;}
.hdm-section-title{font-size:15px;font-weight:700;color:#111827;margin:0 0 14px;text-align:center;}
.hdm-info-row{display:flex;align-items:baseline;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6;}
.hdm-info-row:last-child{border-bottom:none;}
.hdm-info-label{font-size:13px;color:#6b7280;min-width:120px;flex-shrink:0;}
.hdm-info-value{font-size:13px;font-weight:700;color:#111827;text-align:right;flex:1;}
.hdm-footer{display:flex;justify-content:flex-end;padding:14px 24px 18px;border-top:1px solid #e5e7eb;flex-shrink:0;}
.hdm-btn-close{padding:9px 24px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;}
.hdm-btn-close:hover{background:#f3f4f6;}
@media(max-width:560px){.hdm-body{flex-direction:column;}.hdm-left{width:100%;border-right:none;border-bottom:1px solid #e5e7eb;}}
</style>
@endpush

@section('content')

  <div class="dashboard-header-row">
    <h1 class="page-title">History</h1>
  </div>

  {{-- ── Tabs (left) | Filters (right) ───────────────────────────────────────── --}}
  <div class="browse-toolbar">
    <div class="matched-tabs report-tabs--browse">
      <span class="matched-tab-text matched-tab-active" id="histAllTab">
        <i class="fa-solid fa-list" style="margin-right:5px;font-size:12px;"></i>All Items
      </span>
      <span class="matched-tab-text" id="histGuestTab">
        <i class="fa-solid fa-id-card" style="margin-right:5px;font-size:12px;"></i>Guest Items
      </span>
    </div>
    <div class="browse-filter-form">
      <div class="browse-filter-filters">
        {{-- All Items filters: Category + Date --}}
        <div id="histFilterAll" style="display:flex;gap:8px;flex-wrap:wrap;">
          <label class="sr-only" for="historyCategoryFilter">Filter by category</label>
          <select id="historyCategoryFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by category">
            <option value="">Filter By Category</option>
            @foreach(['Electronics & Gadgets','Document & Identification','Personal Belongings','Apparel & Accessories','Miscellaneous'] as $cat)
              <option value="{{ $cat }}" {{ ($categoryFilter ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
          <label class="sr-only" for="historyDateFilter">Filter by date</label>
          <select id="historyDateFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by date">
            <option value="">Filter By Date</option>
            <option value="today"   {{ ($dateFilter ?? '') === 'today'   ? 'selected' : '' }}>Today</option>
            <option value="week"    {{ ($dateFilter ?? '') === 'week'    ? 'selected' : '' }}>This Week</option>
            <option value="month"   {{ ($dateFilter ?? '') === 'month'   ? 'selected' : '' }}>This Month</option>
            <option value="3months" {{ ($dateFilter ?? '') === '3months' ? 'selected' : '' }}>Last 3 Months</option>
            <option value="year"    {{ ($dateFilter ?? '') === 'year'    ? 'selected' : '' }}>This Year</option>
          </select>
        </div>
        {{-- Guest Items filter: Date only --}}
        <div id="histFilterGuest" style="display:none;">
          <label class="sr-only" for="historyGuestDateFilter">Filter by date</label>
          <select id="historyGuestDateFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by date">
            <option value="">Filter By Date</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="3months">Last 3 Months</option>
            <option value="year">This Year</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Claimed Items (Internal) ────────────────────────────────────────────── --}}
  <div id="histAllSection" class="inventory-card matched-reports-card">
    <div class="inventory-title">Claimed Items (Internal)</div>
    <div class="table-wrapper">
      <table class="matched-reports-table" id="historyTable">
        <thead>
          <tr>
            <th>Barcode ID</th>
            <th>Category</th>
            <th>Found At</th>
            <th>Date Found</th>
            <th>Date Claimed</th>
            <th>Storage Location</th>
            <th>Timestamp</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($allClaimed as $item)
            @php
              $rawDesc  = $item->item_description ?? '';
              $itemName = '';
              if (preg_match('/^Item(?:\s+Type)?:\s*(.+?)(?:\n|$)/mi', $rawDesc, $dm)) $itemName = trim($dm[1]);
              $claimantName = $claimContact = $dateAccomp = '';
              $claimPos = stripos($rawDesc, '--- Claim Record ---');
              if ($claimPos !== false) {
                $cs = substr($rawDesc, $claimPos);
                if (preg_match('/^Claimed\s+By:\s*(.+?)(?:\n|$)/mi', $cs, $dm))        $claimantName = trim($dm[1]);
                if (preg_match('/^Contact:\s*(.+?)(?:\n|$)/mi', $cs, $dm))             $claimContact = trim($dm[1]);
                if (preg_match('/^Date\s+Accomplished:\s*(.+?)(?:\n|$)/mi', $cs, $dm)) $dateAccomp   = trim($dm[1]);
              }
              $cleanDesc = $claimPos !== false ? substr($rawDesc, 0, $claimPos) : $rawDesc;
              $cleanDesc = preg_replace('/^(Item(?:\s+Type)?|Student Number|Contact|Department):\s*.+\n?/mi', '', $cleanDesc);
              $cleanDesc = trim($cleanDesc);
            @endphp
            <tr class="matched-data-row"
                data-modal-type="internal"
                data-id="{{ $item->id }}"
                data-category="{{ $item->item_type }}"
                data-color="{{ $item->color }}"
                data-brand="{{ $item->brand }}"
                data-found-by="{{ $item->found_by }}"
                data-found-at="{{ $item->found_at }}"
                data-date-encoded="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}"
                data-storage-location="{{ $item->storage_location }}"
                data-item-name="{{ $itemName }}"
                data-claimant-name="{{ $claimantName }}"
                data-contact="{{ $claimContact }}"
                data-date-accomplished="{{ $dateAccomp }}"
                data-item-description="{{ $cleanDesc }}"
                @if($item->image_data) data-image="{{ $item->image_data }}" @endif>
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->item_type ?? '—' }}</td>
              <td>{{ $item->found_at ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td>{{ $item->updated_at ? $item->updated_at->format('Y-m-d') : '—' }}</td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td style="white-space:nowrap;font-size:12px;color:#6b7280;">{{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '—' }}</td>
              <td class="found-action-cell">
                <button type="button" class="found-btn-view hist-view-btn">View</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="table-empty">No claimed items yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ── Claimed IDs (External) ───────────────────────────────────────────────── --}}
  <div id="histGuestSection" style="display:none;margin-top:16px;" class="inventory-card matched-reports-card">
    <div class="inventory-title">Claimed IDs (External)</div>
    <div class="table-wrapper">
      <table class="matched-reports-table" id="historyGuestTable">
        <thead>
          <tr>
            <th>Barcode ID</th>
            <th>Encoded By</th>
            <th>Date Surrendered</th>
            <th>Date Claimed</th>
            <th>Storage Location</th>
            <th>Timestamp</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($guestClaimed as $item)
            @php
              $meta     = $item->parseDescription();
              $rawDesc  = $item->item_description ?? '';
              $claimantName = $claimContact = $dateAccomp = '';
              $claimPos = stripos($rawDesc, '--- Claim Record ---');
              if ($claimPos !== false) {
                $cs = substr($rawDesc, $claimPos);
                if (preg_match('/^Claimed\s+By:\s*(.+?)(?:\n|$)/mi', $cs, $dm))        $claimantName = trim($dm[1]);
                if (preg_match('/^Contact:\s*(.+?)(?:\n|$)/mi', $cs, $dm))             $claimContact = trim($dm[1]);
                if (preg_match('/^Date\s+Accomplished:\s*(.+?)(?:\n|$)/mi', $cs, $dm)) $dateAccomp   = trim($dm[1]);
              }
            @endphp
            <tr class="matched-data-row"
                data-modal-type="external"
                data-id="{{ $item->id }}"
                data-id-type="{{ $meta['ID Type'] ?? '' }}"
                data-fullname="{{ $meta['Owner'] ?? '' }}"
                data-color="{{ $item->color }}"
                data-storage-location="{{ $item->storage_location }}"
                data-encoded-by="{{ $item->found_by }}"
                data-date-surrendered="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}"
                data-claimant-name="{{ $claimantName }}"
                data-contact="{{ $claimContact }}"
                data-date-accomplished="{{ $dateAccomp }}"
                @if($item->image_data) data-image="{{ $item->image_data }}" @endif>
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->found_by ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td>{{ $item->updated_at ? $item->updated_at->format('Y-m-d') : '—' }}</td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td style="white-space:nowrap;font-size:12px;color:#6b7280;">{{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '—' }}</td>
              <td class="found-action-cell">
                <button type="button" class="found-btn-view hist-view-btn">View</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="table-empty">No claimed IDs yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection

@push('scripts')

{{-- ── Item Details Modal ───────────────────────────────────────────────── --}}
<div class="hdm-overlay" id="histViewModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeHdm()">
  <div class="hdm-modal" onclick="event.stopPropagation()">
    <div class="hdm-header">
      <h3 class="hdm-header-title">Item Details</h3>
      <button type="button" class="hdm-close-btn" onclick="closeHdm()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="hdm-body">
      <div class="hdm-left">
        <div id="hdmPhotoPlaceholder" class="hdm-photo-placeholder">
          <i class="fa-solid fa-box-open" style="font-size:28px;"></i>
          <span>No photo</span>
        </div>
        <img id="hdmPhoto" class="hdm-photo" src="" alt="Item photo" style="display:none;">
        <p class="hdm-barcode" id="hdmBarcode">Barcode ID: —</p>
        <div class="hdm-claimant-section">
          <h4 class="hdm-claimant-title">Claimant's Information</h4>
          <div class="hdm-claimant-field">
            <label class="hdm-claimant-label">Name:</label>
            <div class="hdm-claimant-box" id="hdmClaimantName">—</div>
          </div>
          <div class="hdm-claimant-field">
            <label class="hdm-claimant-label">Contact Number:</label>
            <div class="hdm-claimant-box" id="hdmContact">—</div>
          </div>
          <div class="hdm-claimant-field">
            <label class="hdm-claimant-label">Date of Accomplishment:</label>
            <div class="hdm-claimant-box" id="hdmDateAccomp">—</div>
          </div>
        </div>
      </div>
      <div class="hdm-right">
        <h4 class="hdm-section-title">General Information</h4>
        <div id="hdmInfoRows"></div>
      </div>
    </div>
    <div class="hdm-footer">
      <button type="button" class="hdm-btn-close" onclick="closeHdm()">Close</button>
    </div>
  </div>
</div>

<script>
window.closeHdm = function(){
  var m = document.getElementById('histViewModal');
  if(m){ m.classList.remove('open'); document.body.style.overflow=''; }
};

(function(){
  var modal = document.getElementById('histViewModal');
  if(!modal) return;

  function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function infoRow(label, val){
    if(!val||!String(val).trim()) return '';
    return '<div class="hdm-info-row"><span class="hdm-info-label">'+esc(label)+'</span><span class="hdm-info-value">'+esc(String(val))+'</span></div>';
  }

  function openModal(row){
    var type   = row.getAttribute('data-modal-type') || 'internal';
    var ph     = document.getElementById('hdmPhotoPlaceholder');
    var img    = document.getElementById('hdmPhoto');
    var bar    = document.getElementById('hdmBarcode');
    var cn     = document.getElementById('hdmClaimantName');
    var ct     = document.getElementById('hdmContact');
    var da     = document.getElementById('hdmDateAccomp');
    var inf    = document.getElementById('hdmInfoRows');
    function v(a){ return row.getAttribute(a)||'—'; }

    var imgUrl = row.getAttribute('data-image');
    if(imgUrl){ img.src=imgUrl; img.style.display='block'; if(ph) ph.style.display='none'; }
    else       { if(img) img.style.display='none'; if(ph) ph.style.display='flex'; }

    if(bar) bar.textContent = 'Barcode ID: ' + v('data-id');
    if(cn)  cn.textContent  = v('data-claimant-name');
    if(ct)  ct.textContent  = v('data-contact');
    if(da)  da.textContent  = v('data-date-accomplished');

    if(inf){
      if(type === 'external'){
        inf.innerHTML = [
          infoRow('ID Type:',          v('data-id-type')),
          infoRow('Fullname:',         v('data-fullname')),
          infoRow('Color:',            v('data-color')),
          infoRow('Storage Location:', v('data-storage-location')),
          infoRow('Found By:',         '—'),
          infoRow('Encoded By:',       v('data-encoded-by')),
          infoRow('Date Surrendered:', v('data-date-surrendered')),
        ].join('') || '<p style="color:#9ca3af;font-size:13px;">No details available.</p>';
      } else {
        inf.innerHTML = [
          infoRow('Category:',         v('data-category')),
          infoRow('Item:',             v('data-item-name')),
          infoRow('Color:',            v('data-color')),
          infoRow('Brand:',            v('data-brand')),
          infoRow('Item Description:', v('data-item-description')),
          infoRow('Storage Location:', v('data-storage-location')),
          infoRow('Found At:',         v('data-found-at')),
          infoRow('Found By:',         v('data-found-by')),
          infoRow('Encoded By:',       '—'),
          infoRow('Date Found:',       v('data-date-encoded')),
        ].join('') || '<p style="color:#9ca3af;font-size:13px;">No details available.</p>';
      }
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  // Wire all View buttons in both tables
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.hist-view-btn');
    if(!btn) return;
    var row = btn.closest('tr');
    if(row && !row.querySelector('td[colspan]')) openModal(row);
  });

  document.addEventListener('keydown', function(e){ if(e.key==='Escape') window.closeHdm(); });
})();

// ── Tab switching ─────────────────────────────────────────────────────────
(function(){
  var allTab     = document.getElementById('histAllTab');
  var gstTab     = document.getElementById('histGuestTab');
  var allSec     = document.getElementById('histAllSection');
  var gstSec     = document.getElementById('histGuestSection');
  var filterAll  = document.getElementById('histFilterAll');
  var filterGst  = document.getElementById('histFilterGuest');
  if(!allTab||!gstTab||!allSec||!gstSec) return;

  function showAll(){
    allTab.classList.add('matched-tab-active'); gstTab.classList.remove('matched-tab-active');
    allSec.style.display=''; gstSec.style.display='none';
    if(filterAll)  filterAll.style.display='flex';
    if(filterGst)  filterGst.style.display='none';
  }
  function showGuest(){
    gstTab.classList.add('matched-tab-active'); allTab.classList.remove('matched-tab-active');
    gstSec.style.display=''; allSec.style.display='none';
    if(filterAll)  filterAll.style.display='none';
    if(filterGst)  filterGst.style.display='flex';
  }
  allTab.addEventListener('click', showAll);
  gstTab.addEventListener('click', showGuest);
  if(window.location.hash==='#guest') showGuest();
})();

// ── Category filter (All Items tab) ───────────────────────────────────────
document.getElementById('historyCategoryFilter').addEventListener('change', function(){
  var val = (this.value||'').trim();
  document.querySelectorAll('#historyTable .matched-data-row').forEach(function(r){
    var cat = (r.getAttribute('data-category')||'').trim();
    r.style.display = (!val||cat===val) ? '' : 'none';
  });
});

// ── Date filter helpers ────────────────────────────────────────────────────
function applyDateFilter(tableId, selectVal){
  var now  = new Date();
  var todayStr  = now.toISOString().slice(0,10);
  var weekStart = new Date(now); weekStart.setDate(now.getDate() - now.getDay());
  var weekStr   = weekStart.toISOString().slice(0,10);
  var monthStr  = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
  var m3Str     = new Date(now.getFullYear(), now.getMonth()-3, now.getDate()).toISOString().slice(0,10);
  var yearStr   = new Date(now.getFullYear(), 0, 1).toISOString().slice(0,10);

  document.querySelectorAll('#'+tableId+' .matched-data-row').forEach(function(r){
    // Use updated_at as stored in date-accomplished; fallback to date-encoded
    // We'll derive from the "Date Claimed" cell (index varies) — simpler to use data-date-accomplished
    // Actually the cells are ordered; easiest: read 5th cell (Date Claimed, index 4 for internal / 3 for guest)
    var cells = r.querySelectorAll('td');
    var claimedDateTd = (tableId === 'historyTable') ? cells[4] : cells[3];
    var d = claimedDateTd ? (claimedDateTd.textContent.trim()) : '';
    var show = true;
    if(selectVal==='today')   show = d === todayStr;
    if(selectVal==='week')    show = d >= weekStr && d <= todayStr;
    if(selectVal==='month')   show = d >= monthStr && d <= todayStr;
    if(selectVal==='3months') show = d >= m3Str && d <= todayStr;
    if(selectVal==='year')    show = d >= yearStr && d <= todayStr;
    r.style.display = show ? '' : 'none';
  });
}

document.getElementById('historyDateFilter').addEventListener('change', function(){
  applyDateFilter('historyTable', this.value);
});
document.getElementById('historyGuestDateFilter').addEventListener('change', function(){
  applyDateFilter('historyGuestTable', this.value);
});
</script>
@endpush

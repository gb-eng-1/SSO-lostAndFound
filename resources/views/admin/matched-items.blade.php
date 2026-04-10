@extends('layouts.admin')

@section('title', 'Matched Items')

@push('styles')
<style>
/* ── Guest Item Details Modal (gdm) ────────────────────────────── */
.gdm-overlay{display:none;position:fixed;inset:0;z-index:1500;align-items:center;justify-content:center;background:rgba(0,0,0,.5);}
.gdm-overlay.open{display:flex;}
.gdm-modal{background:#fff;border-radius:12px;width:min(640px,96vw);max-height:90vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.22);display:flex;flex-direction:column;}
.gdm-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.gdm-header-title{color:#fff;font-size:16px;font-weight:700;margin:0;}
.gdm-close-btn{background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;border-radius:4px;opacity:.85;transition:opacity .15s;line-height:1;}
.gdm-close-btn:hover{opacity:1;}
.gdm-body{display:flex;flex:1;flex-direction:column;}
.gdm-right{flex:1;padding:28px 28px 20px;display:flex;flex-direction:column;width:100%;}
.gdm-section-title{font-size:15px;font-weight:700;color:#111827;margin:0 0 16px;text-align:center;}
.gdm-info-row{display:flex;align-items:baseline;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6;}
.gdm-info-row:last-child{border-bottom:none;}
.gdm-info-label{font-size:13px;color:#6b7280;min-width:130px;flex-shrink:0;}
.gdm-info-value{font-size:13px;font-weight:700;color:#111827;text-align:right;flex:1;}
.gdm-footer{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #e5e7eb;}
.gdm-btn-cancel{padding:9px 22px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;}
.gdm-btn-cancel:hover{background:#f3f4f6;}
.gdm-btn-claim{padding:9px 22px;border:none;border-radius:7px;background:#15803d;color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;}
.gdm-btn-claim:hover{opacity:.88;}

/* ── Confirm Item Claim Modal (ccm) ────────────────────────────── */
.ccm-overlay{display:none;position:fixed;inset:0;z-index:1600;align-items:center;justify-content:center;background:rgba(0,0,0,.5);}
.ccm-overlay.open{display:flex;}
.ccm-modal{background:#fff;border-radius:12px;width:min(520px,96vw);max-height:92vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.25);display:flex;flex-direction:column;}
.ccm-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;}
.ccm-header-title{color:#fff;font-size:15px;font-weight:700;margin:0;}
.ccm-close-btn{background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;border-radius:4px;opacity:.85;transition:opacity .15s;line-height:1;}
.ccm-close-btn:hover{opacity:1;}
.ccm-body{padding:20px 24px 8px;}
.ccm-item-summary{display:flex;align-items:center;gap:14px;padding:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;margin-bottom:18px;}
.ccm-thumb{width:52px;height:52px;border-radius:6px;object-fit:cover;flex-shrink:0;}
.ccm-thumb-placeholder{width:52px;height:52px;border-radius:6px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:20px;flex-shrink:0;}
.ccm-item-info{display:flex;flex-direction:column;gap:3px;}
.ccm-item-name{font-size:14px;font-weight:700;color:#111827;}
.ccm-item-sub{font-size:12px;color:#6b7280;}
.ccm-section-yellow{background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:14px 16px;margin-bottom:14px;}
.ccm-section-title-yellow{font-size:13px;font-weight:700;color:#92400e;margin:0 0 14px;text-align:center;}
.ccm-form-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.ccm-form-row:last-child{margin-bottom:0;}
.ccm-label{font-size:12px;color:#374151;font-weight:500;min-width:118px;flex-shrink:0;}
.ccm-input{flex:1;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-family:Poppins,sans-serif;font-size:12px;color:#111827;background:#fff;outline:none;transition:border-color .15s;}
.ccm-input:focus{border-color:#8b0000;}
.ccm-required{color:#dc2626;font-size:12px;}
.ccm-section-gray{background:#f3f4f6;border-radius:8px;padding:14px 16px;margin-bottom:14px;}
.ccm-section-title-gray{font-size:13px;font-weight:700;color:#374151;margin:0 0 14px;}
.ccm-disclaimer{font-size:11px;color:#6b7280;text-align:center;padding:4px 0 8px;font-style:italic;}
.ccm-footer{display:flex;justify-content:flex-end;gap:10px;padding:14px 24px 20px;}
.ccm-btn-cancel{padding:9px 22px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;}
.ccm-btn-cancel:hover{background:#f3f4f6;}
.ccm-btn-confirm{padding:9px 22px;border:none;border-radius:7px;background:#8b0000;color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;}
.ccm-btn-confirm:hover{opacity:.88;}
.ccm-btn-confirm:disabled{opacity:.5;cursor:not-allowed;}

/* ── Tabs override: use matched-tab styles ──────────────────────── */
.matched-tab-text.matched-tab-active{background-color:#fff !important;border-bottom-color:#8b0000 !important;}

/* Recent action banner */
.recent-action-banner{display:flex;align-items:flex-start;gap:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:11px 14px;margin-bottom:10px;}
.rab-icon{color:#16a34a;font-size:17px;flex-shrink:0;margin-top:1px;}
.rab-text{flex:1;font-size:13px;color:#166534;line-height:1.5;}
.rab-dismiss{background:none;border:none;cursor:pointer;color:#6b7280;font-size:14px;padding:3px 7px;border-radius:4px;line-height:1;}
.rab-dismiss:hover{background:#dcfce7;color:#166534;}

/* ima-guest-view-btn */
.ima-guest-view-btn{display:inline-flex;align-items:center;justify-content:center;padding:6px 16px;border-radius:6px;background:#1976d2;color:#fff;font-size:12px;font-weight:600;font-family:Poppins,sans-serif;border:none;cursor:pointer;transition:opacity .15s;}
.ima-guest-view-btn:hover{opacity:.85;}

/* Admin claim gated until student acknowledges in app */
.found-btn-claim--gated{
  background:#9ca3af !important;
  color:#f3f4f6 !important;
  cursor:not-allowed !important;
  opacity:.92;
}
.found-btn-claim--gated:hover{opacity:.92;}

/* header row */
.matched-page-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:20px;}
</style>
@endpush

@section('content')

  <div class="dashboard-header-row">
    <h1 class="page-title">Matched Items</h1>
    @if($resolvedThisMonth > 0)
      <span style="font-size:13px;color:#6b7280;">{{ $resolvedThisMonth }} resolved this month</span>
    @endif
  </div>

  {{-- Recent action banner --}}
  <div id="recentActionBanner" class="recent-action-banner" style="display:none;" role="status" aria-live="polite">
    <div class="rab-icon"><i class="fa-solid fa-circle-check"></i></div>
    <div class="rab-text" id="recentActionText"></div>
    <button type="button" class="rab-dismiss" id="rabDismiss" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
  </div>

  {{-- ── Tabs + Filter row ──────────────────────────────────────────────────── --}}
  <div class="browse-toolbar">
    <div class="matched-tabs report-tabs--browse">
      <span class="matched-tab-text matched-tab-active" id="allItemsTab">
        <i class="fa-solid fa-list" style="margin-right:5px;font-size:12px;"></i>All Items
      </span>
      <span class="matched-tab-text" id="guestItemsTab">
        <i class="fa-solid fa-id-card" style="margin-right:5px;font-size:12px;"></i>Guest Items
      </span>
    </div>
    <div class="browse-filter-form">
      <div class="browse-filter-filters">
        <label class="sr-only" for="matchedCategoryFilter">Filter by category</label>
        <select id="matchedCategoryFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by category">
          <option value="">Filter By Category</option>
          @foreach(['Electronics & Gadgets','Document & Identification','Personal Belongings','Apparel & Accessories','Miscellaneous'] as $cat)
            <option>{{ $cat }}</option>
          @endforeach
        </select>
        <label class="sr-only" for="matchedDateFilter">Filter by date</label>
        <select id="matchedDateFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by date" style="display:none;">
          <option value="">Filter By Date</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
        </select>
      </div>
    </div>
  </div>

  {{-- ── For Claiming (Internal) ─────────────────────────────────────────────── --}}
  <div id="recoveredSection" class="inventory-card matched-reports-card">
    <div class="inventory-title">For Claiming (Internal)</div>
    <div class="table-wrapper">
      <table class="matched-reports-table" id="matchedReportsTable">
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
          @forelse($foundItems as $item)
            @php
              $parsedMeta = $item->parseDescription();
              $itemName   = $parsedMeta['Item'] ?? $parsedMeta['Item Type'] ?? '';
              $isOverdue  = $item->retention_end && \Carbon\Carbon::parse($item->retention_end)->isPast();
            @endphp
            <tr class="matched-data-row{{ $isOverdue ? ' matched-row-overdue' : '' }}"
                data-id="{{ $item->id }}"
                data-category="{{ $item->item_type }}"
                data-color="{{ $item->color }}"
                data-brand="{{ $item->brand }}"
                data-found-by="{{ $item->found_by }}"
                data-found-at="{{ $item->found_at }}"
                data-date-encoded="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}"
                data-storage-location="{{ $item->storage_location }}"
                data-status="{{ $item->status }}"
                data-item-name="{{ $itemName }}"
                data-is-guest="0"
                @if($item->image_data) data-image="{{ $item->image_data }}" @endif>
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->item_type ?? '—' }}</td>
              <td>{{ $item->found_at ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td class="retention-cell">
                {{ $item->retention_end ?? '—' }}
                @if($isOverdue)
                  <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRED</span>
                @elseif($item->expires_in_30_days ?? false)
                  <span class="matched-pill-expiring">EXPIRING</span>
                @endif
              </td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td class="found-action-cell">
                <button type="button"
                        class="found-btn-claim {{ $isOverdue ? 'btn-claim-expired' : '' }} {{ ($item->admin_claim_gated ?? false) ? 'found-btn-claim--gated' : '' }}"
                        data-admin-claim-gated="{{ ($item->admin_claim_gated ?? false) ? '1' : '0' }}"
                        {{ $isOverdue ? 'disabled title="Retention period exceeded"' : '' }}>Claim</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="table-empty">No matched items pending claiming.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ── For Claiming (External) ─────────────────────────────────────────────── --}}
  <div id="guestSection" style="display:none;" class="inventory-card matched-reports-card">
    <div class="inventory-title found-title-guest">For Claiming (External)</div>
    <div class="table-wrapper">
      <table class="matched-reports-table" id="guestReportsTable">
        <thead>
          <tr>
            <th>Barcode ID</th>
            <th>Encoded By</th>
            <th>Date Surrendered</th>
            <th>Retention End</th>
            <th>Storage Location</th>
            <th>Timestamp</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($guestItems as $item)
            @php
              $meta   = $item->parseDescription();
              $retEnd = $item->date_encoded ? $item->date_encoded->copy()->addYear()->toDateString() : '—';
              $isOvd  = $retEnd !== '—' && \Carbon\Carbon::parse($retEnd)->isPast();
            @endphp
            <tr class="matched-data-row guest-row{{ $isOvd ? ' matched-row-overdue' : '' }}"
                data-id="{{ $item->id }}"
                data-category="{{ $item->item_type }}"
                data-color="{{ $item->color }}"
                data-found-by="{{ $item->found_by }}"
                data-date-encoded="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}"
                data-storage-location="{{ $item->storage_location }}"
                data-id-type="{{ $meta['ID Type'] ?? '' }}"
                data-fullname="{{ $meta['Owner'] ?? '' }}"
                data-is-guest="1"
                @if($item->image_data) data-image="{{ $item->image_data }}" @endif>
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->found_by ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td class="retention-cell">
                {{ $retEnd }}
                @if($isOvd)
                  <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRED</span>
                @endif
              </td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '—' }}</td>
              <td class="found-action-cell">
                <button type="button" class="ima-guest-view-btn">View</button>
                <button type="button" class="found-btn-claim {{ $isOvd ? 'btn-claim-expired' : '' }}"
                        {{ $isOvd ? 'disabled title="Retention period exceeded"' : '' }}>Claim</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="table-empty">No guest items pending claiming.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection

@push('scripts')

{{-- ── Guest Item Details Modal ─────────────────────────────────────────── --}}
<div class="gdm-overlay" id="guestDetailsModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeGuestDetailsModal()">
  <div class="gdm-modal" onclick="event.stopPropagation()">
    <div class="gdm-header">
      <h3 class="gdm-header-title">Item Details</h3>
      <button type="button" class="gdm-close-btn" onclick="closeGuestDetailsModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="gdm-body">
      <div class="gdm-right">
        <h4 class="gdm-section-title">General Information</h4>
        <div class="gdm-info-row"><span class="gdm-info-label">Barcode ID:</span><span class="gdm-info-value" id="gdmBarcode">—</span></div>
        <div class="gdm-info-row"><span class="gdm-info-label">ID Type:</span><span class="gdm-info-value" id="gdmIdType">—</span></div>
        <div class="gdm-info-row"><span class="gdm-info-label">Fullname:</span><span class="gdm-info-value" id="gdmFullname">—</span></div>
        <div class="gdm-info-row"><span class="gdm-info-label">Color:</span><span class="gdm-info-value" id="gdmColor">—</span></div>
        <div class="gdm-info-row"><span class="gdm-info-label">Storage Location:</span><span class="gdm-info-value" id="gdmStorage">—</span></div>
        <div class="gdm-info-row"><span class="gdm-info-label">Encoded By:</span><span class="gdm-info-value" id="gdmEncodedBy">—</span></div>
        <div class="gdm-info-row"><span class="gdm-info-label">Date Surrendered:</span><span class="gdm-info-value" id="gdmDateSurrendered">—</span></div>
      </div>
    </div>
    <div class="gdm-footer">
      <button type="button" class="gdm-btn-cancel" onclick="closeGuestDetailsModal()">Cancel</button>
      <button type="button" class="gdm-btn-claim" id="gdmClaimBtn">Claim</button>
    </div>
  </div>
</div>

{{-- ── Confirm Item Claim Modal ──────────────────────────────────────────── --}}
<div class="ccm-overlay" id="confirmClaimModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeConfirmClaimModal()">
  <div class="ccm-modal" onclick="event.stopPropagation()">
    <div class="ccm-header">
      <h3 class="ccm-header-title">Confirm Item Claim</h3>
      <button type="button" class="ccm-close-btn" onclick="closeConfirmClaimModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="ccm-body">
      <div class="ccm-item-summary">
        <div class="ccm-thumb-placeholder" id="ccmThumbPlaceholder"><i class="fa-solid fa-box"></i></div>
        <img id="ccmThumb" class="ccm-thumb" src="" alt="" style="display:none;">
        <div class="ccm-item-info">
          <span class="ccm-item-name" id="ccmItemName">—</span>
          <span class="ccm-item-sub" id="ccmItemSub">—</span>
        </div>
      </div>
      <div class="ccm-section-yellow">
        <p class="ccm-section-title-yellow">Verification of Claimant's Identity</p>
        <div class="ccm-form-row">
          <label class="ccm-label" for="ccmClaimantName">Claimant's Name: <span class="ccm-required">*</span></label>
          <input type="text" id="ccmClaimantName" class="ccm-input" required>
        </div>
        <div class="ccm-form-row" style="align-items:flex-start;">
          <label class="ccm-label" for="ccmUbMail" style="padding-top:8px;" id="ccmEmailLabel">Email:</label>
          <div style="flex:1;">
            <input type="text" id="ccmUbMail" class="ccm-input" style="width:100%;box-sizing:border-box;" placeholder="e.g. 200981@ub.edu.ph">
            <span id="ccmUbMailError" style="display:none;font-size:11px;color:#dc2626;margin-top:3px;display:block;">
              Must be a valid @ub.edu.ph email address.
            </span>
          </div>
        </div>
        <div class="ccm-form-row">
          <label class="ccm-label" for="ccmContactNumber">Contact Number:</label>
          <input type="text" id="ccmContactNumber" class="ccm-input">
        </div>
        <div class="ccm-form-row" style="align-items:flex-start;">
          <label class="ccm-label" style="padding-top:8px;">Photo: <span class="ccm-required">*</span></label>
          <div style="flex:1;">
            <div class="pp-wrap" id="ccmPhotoPicker">
              <div class="pp-idle">
                <i class="fa-regular fa-image pp-icon"></i>
                <p class="pp-hint">No photo yet</p>
                <div class="pp-btn-row">
                  <button type="button" class="pp-btn pp-btn--cam" data-pp="camera"><i class="fa-solid fa-camera"></i> Camera</button>
                  <button type="button" class="pp-btn pp-btn--upload" data-pp="upload"><i class="fa-solid fa-upload"></i> Upload</button>
                </div>
              </div>
              <div class="pp-preview" style="display:none">
                <img class="pp-preview-img" src="" alt="Photo preview">
                <div class="pp-preview-actions">
                  <button type="button" class="pp-btn pp-btn--sm" data-pp="camera"><i class="fa-solid fa-camera"></i> Retake</button>
                  <button type="button" class="pp-btn pp-btn--sm" data-pp="upload"><i class="fa-solid fa-upload"></i> Change</button>
                  <button type="button" class="pp-btn pp-btn--del" data-pp="remove"><i class="fa-solid fa-xmark"></i></button>
                </div>
              </div>
              <input type="file" class="pp-file" accept="image/*" style="display:none">
            </div>
            <span id="ccmFileError" style="display:none;font-size:11px;color:#dc2626;margin-top:4px;display:block;">A photo is required.</span>
          </div>
        </div>
      </div>
      <div class="ccm-section-gray">
        <p class="ccm-section-title-gray">Action Taken</p>
        <div class="ccm-form-row">
          <label class="ccm-label" for="ccmDateAccomplishment">Date of Accomplishment:</label>
          <input type="date" id="ccmDateAccomplishment" class="ccm-input" style="flex:1;" max="{{ date('Y-m-d') }}">
        </div>
      </div>
      <p class="ccm-disclaimer">Disclaimer: Image Uploaded will be used for the system only.</p>
    </div>
    <div class="ccm-footer">
      <button type="button" class="ccm-btn-cancel" onclick="closeConfirmClaimModal()">Cancel</button>
      <button type="button" class="ccm-btn-confirm" id="ccmConfirmBtn">Confirm</button>
    </div>
  </div>
</div>

<script>
var _CSRF = document.querySelector('meta[name="csrf-token"]').content;
function _mmJsonHeaders(){
  return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': _CSRF };
}
function _mmParseLaravelFetchResponse(r){
  return r.text().then(function(text){
    var data = null;
    if(text){ try { data = JSON.parse(text); } catch(e) {} }
    return { ok: r.ok, status: r.status, data: data };
  });
}
function _mmLaravelErrMsg(res){
  var d = res.data;
  if(d && typeof d === 'object'){
    if(d.message) return d.message;
    if(d.errors){ for(var k in d.errors){ var v = d.errors[k]; if(v && v.length) return v[0]; } }
    if(d.error) return d.error;
  }
  if(res.status === 419) return 'Page expired. Refresh and try again.';
  return 'Request failed (' + res.status + ').';
}
var _ccmRow = null;
var _ccmImg = null;
var _ccmIsGuest = false;

// ── Tab switching ──────────────────────────────────────────────────────────
(function(){
  var allTab   = document.getElementById('allItemsTab');
  var gstTab   = document.getElementById('guestItemsTab');
  var recovSec = document.getElementById('recoveredSection');
  var gstSec   = document.getElementById('guestSection');
  var catFlt   = document.getElementById('matchedCategoryFilter');
  var dateFlt  = document.getElementById('matchedDateFilter');
  if(!allTab||!gstTab) return;
  function showAll(){
    allTab.classList.add('matched-tab-active'); gstTab.classList.remove('matched-tab-active');
    recovSec.style.display=''; gstSec.style.display='none';
    catFlt.style.display=''; dateFlt.style.display='none';
    dateFlt.value='';
  }
  function showGuest(){
    gstTab.classList.add('matched-tab-active'); allTab.classList.remove('matched-tab-active');
    gstSec.style.display=''; recovSec.style.display='none';
    dateFlt.style.display=''; catFlt.style.display='none';
    catFlt.value='';
  }
  allTab.addEventListener('click', showAll);
  gstTab.addEventListener('click', showGuest);
})();

// ── Category filter (All Items tab) ────────────────────────────────────────
document.getElementById('matchedCategoryFilter').addEventListener('change', function(){
  var val = this.value.trim();
  document.querySelectorAll('#recoveredSection .matched-data-row').forEach(function(row){
    var cat = (row.getAttribute('data-category')||'').trim();
    row.style.display = (!val || cat === val) ? '' : 'none';
  });
});

// ── Date filter (Guest Items tab) ─────────────────────────────────────────
document.getElementById('matchedDateFilter').addEventListener('change', function(){
  var val = this.value;
  var now = new Date();
  var todayStr = now.toISOString().slice(0,10);
  var weekStart = new Date(now); weekStart.setDate(now.getDate() - now.getDay());
  var weekStartStr = weekStart.toISOString().slice(0,10);
  var monthStart = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);

  document.querySelectorAll('#guestSection .matched-data-row').forEach(function(row){
    var d = row.getAttribute('data-date-encoded')||'';
    var show = true;
    if(val === 'today')  show = d === todayStr;
    if(val === 'week')   show = d >= weekStartStr && d <= todayStr;
    if(val === 'month')  show = d >= monthStart && d <= todayStr;
    row.style.display = show ? '' : 'none';
  });
});

// ── Photo picker ──────────────────────────────────────────────────────────
var _ccmPP = PhotoPicker.init({
  el: 'ccmPhotoPicker',
  onChange: function(dataUrl){ _ccmImg = dataUrl || null; }
});
function clearCcmFile(){ _ccmImg = null; if(_ccmPP) _ccmPP.clear(); }

// ── Guest Item Details Modal ───────────────────────────────────────────────
function openGuestDetailsModal(row){
  _ccmRow = null;
  var overlayEl = document.getElementById('guestDetailsModal');
  function v(a){ return row.getAttribute(a)||'—'; }
  document.getElementById('gdmBarcode').textContent = v('data-id');
  document.getElementById('gdmIdType').textContent       = v('data-id-type');
  document.getElementById('gdmFullname').textContent     = v('data-fullname');
  document.getElementById('gdmColor').textContent        = v('data-color');
  document.getElementById('gdmStorage').textContent      = v('data-storage-location');
  document.getElementById('gdmEncodedBy').textContent    = v('data-found-by');
  document.getElementById('gdmDateSurrendered').textContent = v('data-date-encoded');
  _ccmRow = row;
  overlayEl.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeGuestDetailsModal(){
  document.getElementById('guestDetailsModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('gdmClaimBtn').addEventListener('click', function(){
  if(!_ccmRow) return;
  closeGuestDetailsModal();
  openConfirmClaimModal(_ccmRow);
});
document.getElementById('guestReportsTable').addEventListener('click', function(e){
  var row = e.target.closest('tr');
  if(!row || row.querySelector('td[colspan]')) return;
  if(e.target.closest('.ima-guest-view-btn')){
    openGuestDetailsModal(row);
  }
  if(e.target.closest('.found-btn-claim:not([disabled])')){
    openConfirmClaimModal(row);
  }
});

// ── Confirm Item Claim Modal ───────────────────────────────────────────────
function openConfirmClaimModal(row){
  _ccmRow    = row;
  _ccmImg    = null;
  _ccmIsGuest = (row.getAttribute('data-is-guest') === '1');

  var overlay = document.getElementById('confirmClaimModal');
  var th   = document.getElementById('ccmThumb');
  var thP  = document.getElementById('ccmThumbPlaceholder');
  var nm   = document.getElementById('ccmItemName');
  var sb   = document.getElementById('ccmItemSub');
  var lbl  = document.getElementById('ccmEmailLabel');
  var err  = document.getElementById('ccmUbMailError');
  function v(a){ return row.getAttribute(a)||''; }
  var imgUrl   = v('data-image');
  if(imgUrl){ th.src=imgUrl; th.style.display='block'; thP.style.display='none'; }
  else       { th.style.display='none'; thP.style.display='flex'; }
  var bid      = v('data-id');
  var color    = v('data-color');
  var storage  = v('data-storage-location');
  var fullname = v('data-fullname');
  var category = v('data-category');
  var itemName = v('data-item-name');
  var displayName;
  if(_ccmIsGuest && fullname) displayName = bid + ': ' + fullname + (color?' ('+color+')':'');
  else if(itemName)            displayName = bid + ': ' + itemName + (color?' — '+color:'');
  else                         displayName = bid + ': ' + (category||'Item');
  if(nm) nm.textContent = displayName;
  if(sb) sb.textContent = storage || '—';

  // Adjust email field placeholder and error message based on context
  var emailInput = document.getElementById('ccmUbMail');
  if(_ccmIsGuest){
    if(lbl) lbl.textContent = 'Email:';
    if(emailInput) emailInput.placeholder = 'e.g. example@email.com';
    if(err) err.textContent = 'Must be a valid email address.';
  } else {
    if(lbl) lbl.textContent = 'Email:';
    if(emailInput) emailInput.placeholder = 'e.g. 200981@ub.edu.ph';
    if(err) err.textContent = 'Must be a valid @ub.edu.ph email address.';
  }

  ['ccmClaimantName','ccmUbMail','ccmContactNumber'].forEach(function(id){
    var el = document.getElementById(id); if(el){ el.value=''; el.style.borderColor=''; }
  });
  if(err) err.style.display = 'none';
  var fileErr = document.getElementById('ccmFileError');
  if(fileErr) fileErr.style.display = 'none';
  clearCcmFile();
  var dt = document.getElementById('ccmDateAccomplishment');
  if(dt){ dt.value = new Date().toISOString().slice(0,10); dt.max = new Date().toISOString().slice(0,10); }
  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeConfirmClaimModal(){
  document.getElementById('confirmClaimModal').classList.remove('open');
  document.body.style.overflow = '';
}

// Email live validation (context-sensitive)
document.getElementById('ccmUbMail').addEventListener('input', function(){
  var v   = this.value.trim();
  var err = document.getElementById('ccmUbMailError');
  var bad;
  if(_ccmIsGuest){
    bad = v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  } else {
    bad = v && !/^[^@]+@ub\.edu\.ph$/i.test(v);
  }
  if(bad){
    err.style.display='block'; this.style.borderColor='#dc2626';
  } else {
    err.style.display='none'; this.style.borderColor='';
  }
});

// Wire internal Claim buttons
document.getElementById('matchedReportsTable').addEventListener('click', function(e){
  var btn = e.target.closest('.found-btn-claim');
  if(!btn || btn.disabled || btn.classList.contains('btn-claim-expired')) return;
  var row = btn.closest('tr');
  if(!row || row.querySelector('td[colspan]')) return;
  if(btn.getAttribute('data-admin-claim-gated') === '1'){
    var msg = 'The person who lost the item has not yet confirmed in the app that they will claim it.';
    if (typeof window.appUiAlert === 'function') window.appUiAlert(msg);
    else alert(msg);
    return;
  }
  openConfirmClaimModal(row);
});

// Confirm submit
document.getElementById('ccmConfirmBtn').addEventListener('click', function(){
  var cName   = document.getElementById('ccmClaimantName');
  var ubMail  = document.getElementById('ccmUbMail');
  var ubError = document.getElementById('ccmUbMailError');
  var fileErr = document.getElementById('ccmFileError');
  var valid   = true;

  if(!cName || !cName.value.trim()){ if(cName){ cName.style.borderColor='#dc2626'; cName.focus(); } valid=false; }
  else if(cName){ cName.style.borderColor=''; }

  var mailVal = ubMail ? ubMail.value.trim() : '';
  if(mailVal){
    var mailBad;
    if(_ccmIsGuest){
      mailBad = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mailVal);
    } else {
      mailBad = !/^[^@]+@ub\.edu\.ph$/i.test(mailVal);
    }
    if(mailBad){
      if(ubError) ubError.style.display='block';
      if(ubMail){ ubMail.style.borderColor='#dc2626'; ubMail.focus(); }
      valid = false;
    } else {
      if(ubError) ubError.style.display='none';
      if(ubMail)  ubMail.style.borderColor='';
    }
  } else {
    if(ubError) ubError.style.display='none';
    if(ubMail)  ubMail.style.borderColor='';
  }

  if(!_ccmImg){
    if(fileErr) fileErr.style.display='block'; valid=false;
  } else {
    if(fileErr) fileErr.style.display='none';
  }
  if(!valid) return;

  var row = _ccmRow;
  if(!row) return;
  var id = row.getAttribute('data-id');
  if(!id) return;

  var claimantNameVal = cName.value.trim();
  var dateAccompVal   = (document.getElementById('ccmDateAccomplishment')||{}).value||'';
  var itemNameVal     = row.getAttribute('data-item-name') || row.getAttribute('data-category') || 'Item';

  var btn = this;
  btn.disabled = true; btn.textContent = 'Saving…';
  fetch('/admin/claim/' + encodeURIComponent(id), {
    method: 'POST',
    headers: _mmJsonHeaders(),
    body: JSON.stringify({
      claimant_name:     claimantNameVal,
      ub_mail:           mailVal,
      contact_number:    (document.getElementById('ccmContactNumber')||{}).value||'',
      date_accomplished: dateAccompVal,
      imageDataUrl:      _ccmImg
    })
  }).then(_mmParseLaravelFetchResponse).then(function(res){
    btn.disabled = false; btn.textContent = 'Confirm';
    if(res.ok && res.data && res.data.ok){
      closeConfirmClaimModal();
      var tbody    = row.parentNode;
      var colCount = row.querySelectorAll('td').length;
      row.remove();
      if(tbody && !tbody.querySelector('tr:not([style*="display: none"])')){
        var empty = document.createElement('tr');
        empty.innerHTML = '<td colspan="'+colCount+'" class="table-empty">No items.</td>';
        tbody.appendChild(empty);
      }
      var banner     = document.getElementById('recentActionBanner');
      var bannerText = document.getElementById('recentActionText');
      if(banner && bannerText){
        bannerText.textContent = itemNameVal + ' ('+id+') claimed by ' + claimantNameVal + (dateAccompVal?' on '+dateAccompVal:'') + '.';
        banner.style.display = 'flex';
        banner.scrollIntoView({ behavior:'smooth', block:'nearest' });
      }
    } else {
      var em = _mmLaravelErrMsg(res) || 'Could not confirm claim.';
      if (typeof window.appUiAlert === 'function') window.appUiAlert(em); else alert(em);
    }
  }).catch(function(){
    btn.disabled = false; btn.textContent = 'Confirm';
    if (typeof window.appUiAlert === 'function') window.appUiAlert('Network error. Try again.'); else alert('Network error. Try again.');
  });
});

document.getElementById('rabDismiss').addEventListener('click', function(){
  document.getElementById('recentActionBanner').style.display = 'none';
});

document.addEventListener('keydown', function(e){
  if(e.key !== 'Escape') return;
  closeGuestDetailsModal(); closeConfirmClaimModal();
});
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Found Items')

@push('styles')
<style>
/* ── Expiry Items Popup ─────────────────────────────────────────── */
.expiry-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.42);z-index:1200;align-items:center;justify-content:center;}
.expiry-overlay.open{display:flex;}
.expiry-popup{background:#fff;border-radius:14px;padding:24px 26px 28px;width:min(720px,96vw);max-height:88vh;overflow-y:auto;box-shadow:0 12px 40px rgba(0,0,0,.2);position:relative;}
.expiry-popup-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.expiry-popup-title{font-size:16px;font-weight:700;color:#111;}
.expiry-popup-close{background:none;border:none;cursor:pointer;font-size:18px;color:#6b7280;padding:2px 7px;border-radius:5px;line-height:1;transition:background .15s;}
.expiry-popup-close:hover{background:#f3f4f6;color:#111;}
.expiry-cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;}
.expiry-card{background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:16px;box-shadow:0 2px 5px rgba(0,0,0,.05);display:flex;flex-direction:column;gap:8px;}
.expiry-card-title{margin:0;font-size:15px;font-weight:700;color:#111;display:flex;align-items:center;gap:9px;}
.expiry-card-meta{display:flex;align-items:center;gap:9px;color:#555;font-size:13px;}
.expiry-card-badge{display:inline-block;background:#fff3cd;color:#856404;border:1px solid #ffc107;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;align-self:flex-start;}
.expiry-card-footer{display:flex;justify-content:flex-end;margin-top:4px;}
.btn-cancel-item{background:#8b0000;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;transition:opacity .15s;}
.btn-cancel-item:hover{opacity:.85;}
.btn-cancel-item:disabled{opacity:.5;cursor:not-allowed;}

/* ── Cancel item confirmation modal ───────────────────────────── */
.cancel-confirm-overlay{display:none;position:fixed;inset:0;z-index:1600;align-items:center;justify-content:center;background:rgba(0,0,0,.5);padding:16px;}
.cancel-confirm-overlay.open{display:flex;}
.cancel-confirm-dialog{background:#fff;border-radius:12px;width:min(420px,96vw);box-shadow:0 20px 50px rgba(0,0,0,.2);overflow:hidden;font-family:Poppins,sans-serif;}
.cancel-confirm-hdr{background:#8b0000;color:#fff;padding:14px 18px;font-size:16px;font-weight:700;margin:0;}
.cancel-confirm-body{padding:20px 22px 8px;color:#374151;font-size:14px;line-height:1.5;}
.cancel-confirm-body strong{color:#111827;}
.cancel-confirm-foot{padding:16px 18px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e5e7eb;background:#fafafa;}
.cancel-confirm-btn{padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;}
.cancel-confirm-btn--secondary{border:1px solid #d1d5db;background:#fff;color:#374151;}
.cancel-confirm-btn--secondary:hover{background:#f3f4f6;}
.cancel-confirm-btn--danger{border:none;background:#8b0000;color:#fff;}
.cancel-confirm-btn--danger:hover{opacity:.92;}
.cancel-confirm-btn:disabled{opacity:.5;cursor:not-allowed;}

/* ── Guest Item Details Modal ──────────────────────────────────── */
.guest-modal-overlay{display:none;position:fixed;inset:0;z-index:1500;align-items:center;justify-content:center;background:rgba(0,0,0,.5);}
.guest-modal-overlay.open{display:flex;}
.guest-modal{background:#fff;border-radius:12px;width:min(640px,96vw);max-height:90vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.22);display:flex;flex-direction:column;}
.guest-modal-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.guest-modal-header-title{color:#fff;font-size:16px;font-weight:700;margin:0;}
.guest-modal-header-close{background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;border-radius:4px;line-height:1;opacity:.85;transition:opacity .15s;}
.guest-modal-header-close:hover{opacity:1;}
.guest-modal-body{display:flex;gap:0;padding:0;flex:1;}
.guest-modal-left{width:35%;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:28px 16px 24px;border-right:1px solid #e5e7eb;background:#fafafa;border-radius:0 0 0 12px;}
.guest-modal-photo{width:140px;height:100px;object-fit:cover;border-radius:6px;border:1px solid #e0e0e0;}
.guest-modal-photo-placeholder{width:140px;height:100px;background:#f3f4f6;border-radius:6px;border:1px solid #e0e0e0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:#9ca3af;font-size:11px;}
.guest-modal-barcode-label{margin-top:10px;font-size:13px;color:#374151;font-weight:500;text-align:center;}
.guest-modal-right{flex:1;padding:28px 28px 24px;display:flex;flex-direction:column;}
.guest-modal-section-title{font-size:15px;font-weight:700;color:#111827;margin:0 0 18px;text-align:center;}
.guest-modal-info-row{display:flex;align-items:baseline;gap:8px;padding:6px 0;border-bottom:1px solid #f3f4f6;}
.guest-modal-info-row:last-child{border-bottom:none;}
.guest-modal-info-label{font-size:13px;color:#6b7280;flex-shrink:0;min-width:120px;}
.guest-modal-info-value{font-size:13px;font-weight:700;color:#111827;text-align:right;flex:1;}
@media(max-width:520px){.guest-modal-body{flex-direction:column;}.guest-modal-left{width:100%;border-right:none;border-bottom:1px solid #e5e7eb;border-radius:0;}}

/* ── Internal View Modal ───────────────────────────────────────── */
.view-modal-overlay{display:none;position:fixed;inset:0;z-index:1500;background:rgba(0,0,0,.5);align-items:center;justify-content:center;}
/* opacity/visibility override FoundAdmin.css base .view-modal-overlay (hidden until open) */
.view-modal-overlay.open{display:flex;opacity:1;visibility:visible;}
.view-modal{background:#fff;border-radius:12px;width:min(640px,96vw);max-height:90vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.22);display:flex;flex-direction:column;}
.view-modal-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.view-modal-title{color:#fff;font-size:16px;font-weight:700;margin:0;}
.view-modal-close{background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;border-radius:4px;opacity:.85;line-height:1;}
.view-modal-close:hover{opacity:1;}
.view-modal-content{display:flex;flex:1;}
.view-modal-left{flex:1;padding:24px;}
.view-modal-section-title{font-size:14px;font-weight:700;color:#111827;margin:0 0 14px;}
.view-modal-body .vm-row{display:flex;gap:8px;padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:13px;}
.view-modal-body .vm-row:last-child{border-bottom:none;}
.vm-label{color:#6b7280;min-width:120px;flex-shrink:0;}
.vm-val{font-weight:700;color:#111827;flex:1;word-break:break-all;}
.view-modal-right{width:40%;flex-shrink:0;padding:24px 16px;border-left:1px solid #e5e7eb;background:#fafafa;display:flex;flex-direction:column;align-items:center;gap:12px;}
.view-modal-image img{width:160px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e0e0e0;}
.view-modal-image-placeholder{width:160px;height:120px;background:#f3f4f6;border-radius:8px;border:1px solid #e0e0e0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#9ca3af;font-size:11px;gap:6px;}
.view-modal-cancel{padding:8px 22px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;margin-top:auto;}
.view-modal-cancel:hover{background:#f3f4f6;}

/* ── Encode / Report Modal ─────────────────────────────────────── */
.report-modal-overlay{display:none;position:fixed;inset:0;z-index:1400;background:rgba(0,0,0,.5);align-items:center;justify-content:center;}
.report-modal-overlay.report-modal-open{display:flex;}
.report-modal{background:#fff;border-radius:12px;width:min(560px,96vw);max-height:92vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.24);display:flex;flex-direction:column;}
.report-modal-header{background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.report-modal-title{color:#fff;font-size:16px;font-weight:700;margin:0;}
.report-modal-close{background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;border-radius:4px;opacity:.85;line-height:1;}
.report-modal-close:hover{opacity:1;}
.report-modal-body{padding:20px 24px 8px;display:flex;flex-direction:column;gap:12px;}
.report-form-row{display:flex;align-items:center;gap:10px;}
.report-form-row-textarea{align-items:flex-start;}
.report-form-label{font-size:13px;color:#374151;font-weight:500;min-width:130px;flex-shrink:0;}
.report-form-field{flex:1;display:flex;align-items:center;gap:4px;}
.report-input{width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-family:Poppins,sans-serif;font-size:12px;color:#111827;outline:none;transition:border-color .15s;box-sizing:border-box;}
.report-input:focus{border-color:#8b0000;}
.report-select{appearance:auto;}
.report-textarea{resize:vertical;min-height:80px;}
.report-required{color:#dc2626;font-size:12px;}
.report-modal-footer{display:flex;justify-content:flex-end;gap:10px;padding:14px 0 6px;}
.report-btn-cancel{padding:8px 20px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;}
.report-btn-cancel:hover{background:#f3f4f6;}
.report-btn-confirm{padding:8px 20px;border:none;border-radius:7px;background:#8b0000;color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;}
.report-btn-confirm:hover{opacity:.88;}
.report-btn-confirm:disabled{opacity:.5;cursor:not-allowed;}
.pp-photo-row{display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;}
.pp-photo-row .report-form-label{padding-top:4px;flex:0 0 130px;max-width:130px;}
.pp-photo-row .report-form-field{flex:1 1 auto;width:100%;min-width:0;align-items:flex-start;}
.pp-photo-label{font-size:13px;color:#374151;font-weight:500;min-width:130px;flex-shrink:0;padding-top:6px;}
.pp-photo-row .pp-wrap{flex:1 1 auto;width:100%;max-width:100%;min-width:0;}
.pp-wrap{min-width:0;}

/* Success modal */
.success-modal-overlay{display:none;position:fixed;inset:0;z-index:1600;background:rgba(0,0,0,.5);align-items:center;justify-content:center;}
.success-modal-overlay.success-modal-open{display:flex;}
.success-modal{background:#fff;border-radius:12px;width:min(380px,92vw);padding:32px 28px 24px;box-shadow:0 16px 48px rgba(0,0,0,.24);text-align:center;position:relative;}
.success-modal-icon{width:56px;height:56px;border-radius:50%;background:#15803d;color:#fff;font-size:24px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;}
.success-modal-title{font-size:18px;font-weight:700;color:#111827;margin:0 0 8px;}
.success-modal-message{font-size:14px;color:#6b7280;margin:0 0 8px;}
.success-modal-barcode{font-size:13px;color:#374151;margin:0 0 18px;}
.success-modal-close{position:absolute;top:12px;right:14px;background:none;border:none;cursor:pointer;font-size:18px;color:#6b7280;}
.success-modal-footer{display:flex;justify-content:center;gap:10px;}

/* Table action cell */
.found-action-cell{display:flex;gap:6px;align-items:center;}
.found-btn-view,.found-btn-view-guest{background:#2563eb!important;color:#fff!important;border:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;}
.found-btn-view:hover,.found-btn-view-guest:hover{background:#1d4ed8!important;opacity:.9;}
.found-btn-cancel{background:#dc2626!important;color:#fff!important;border:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;}
.found-btn-cancel:hover{background:#b91c1c!important;opacity:.9;}

/* Tabs (matched style) */
.matched-tab-text.matched-tab-active{background-color:#fff !important;border-bottom-color:#8b0000 !important;}
.found-btn-encode-orange{background:#f97316!important;color:#111!important;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;}
.found-btn-encode-orange:hover{filter:brightness(.95);}
.found-btn-encode-blue{background:#2563eb!important;color:#fff!important;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;}
.found-btn-encode-blue:hover{background:#1d4ed8!important;}

/* Internal view modal: image left, info right */
.view-modal-inner{display:flex;flex:1;min-height:0;}
.view-modal-left-col{width:42%;flex-shrink:0;padding:24px 18px;border-right:1px solid #e5e7eb;background:#fafafa;display:flex;flex-direction:column;align-items:center;}
.view-modal-right-col{flex:1;padding:24px 26px;}
.view-modal-right-col .view-modal-section-title{text-align:center;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #e5e7eb;}
</style>
@endpush

@section('content')

  <div class="dashboard-header-row">
    <h1 class="page-title">Found Items</h1>
  </div>

  {{-- Retention bar --}}
  <div class="found-retention-bar" style="margin-bottom:10px;padding:8px 14px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;font-size:13px;color:#92400e;display:flex;align-items:center;justify-content:space-between;">
    <span>There are <strong>{{ $overdueCount }}</strong> item{{ $overdueCount !== 1 ? 's' : '' }} that have exceeded the retention policy.</span>
    @if($expiringItems->count() > 0)
      <a href="#" id="expiryTriggerLink" style="font-size:12px;font-weight:600;color:#8b0000;text-decoration:underline;">View more</a>
    @endif
  </div>

  {{-- ── Tabs + Filters + Actions ──────────────────────────────────────────── --}}
  <form method="get" action="{{ route('admin.found') }}" id="foundFilterForm" class="browse-toolbar">
    <div class="matched-tabs report-tabs--browse" style="display:inline-flex;gap:12px;">
      <span class="matched-tab-text matched-tab-active" id="allItemsTab">
        <i class="fa-solid fa-list" style="margin-right:5px;font-size:12px;"></i>All Items
      </span>
      <span class="matched-tab-text" id="guestItemsTab">
        <i class="fa-solid fa-id-card" style="margin-right:5px;font-size:12px;"></i>Guest Items
      </span>
    </div>
    <div id="foundFilterGroup" class="browse-filter-form">
      <div class="browse-filter-filters">
        <label class="sr-only" for="foundCategorySelect">Filter by category</label>
        <select name="category" id="foundCategorySelect" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by category">
          <option value="">Filter By Category</option>
          @foreach($categoriesInternal as $c)
            <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
          @endforeach
        </select>
        <label class="sr-only" for="foundDateFilter">Filter by date</label>
        <select name="date_filter" id="foundDateFilter" class="found-filter-select browse-filter-select matched-filter-select" aria-label="Filter by date">
          <option value="">Filter By Date</option>
          <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
          <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>This Week</option>
          <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>This Month</option>
          <option value="3months" {{ request('date_filter') === '3months' ? 'selected' : '' }}>Last 3 Months</option>
          <option value="year" {{ request('date_filter') === 'year' ? 'selected' : '' }}>This Year</option>
        </select>
        <button type="submit" class="found-btn" style="background:#374151;color:#fff;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;">Apply</button>
      </div>
    </div>
    <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center;" id="allItemsActions">
      <button type="button" class="found-btn-encode-orange" id="encodeNewItemBtn">
        <i class="fa-solid fa-plus"></i> Encode Item
      </button>
      <button type="button" class="found-btn-encode-blue" id="encodeReportBtn">
        <i class="fa-solid fa-file-lines"></i> Encode Report
      </button>
    </div>
    <div id="guestActionsBar" style="display:none;margin-left:auto;">
      <button type="button" class="found-btn-encode-orange" id="encodeGuestItemBtn">
        <i class="fa-solid fa-plus"></i> Encode ID
      </button>
    </div>
  </form>

  {{-- Internal Items Tab --}}
  <div id="tab-internal" class="inventory-card matched-reports-card">
    <div class="inventory-title">Recovered Items (Internal)</div>
    <div class="table-wrapper">
      <table class="found-table">
        <thead>
          <tr>
            <th>Barcode ID</th>
            <th>Category</th>
            <th>Found At</th>
            <th>Date Found</th>
            <th>Retention End</th>
            <th>Storage Location</th>
            <th>Timestamp</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="inventoryTableBody">
          @forelse($internalItems as $item)
            @php
              $parsedMeta = $item->parseDescription();
              $itemName   = $parsedMeta['Item Type'] ?? $parsedMeta['Item'] ?? '';
              $encodedBy  = $parsedMeta['Encoded By'] ?? '';
              $descForView  = preg_replace('/^Encoded By:\s*.+$/m', '', $item->item_description ?? '');
              $descForView  = trim(preg_replace("/\n{2,}/", "\n", $descForView));
              $timestamp  = $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : ($item->date_encoded ? $item->date_encoded->format('Y-m-d') . ' 00:00:00' : '—');
            @endphp
            <tr class="{{ $item->is_overdue ? 'row-overdue' : ($item->expires_in_30_days ? 'row-expiring' : '') }}"
                data-id="{{ $item->id }}"
                data-category="{{ $item->item_type }}"
                data-color="{{ $item->color }}"
                data-brand="{{ $item->brand }}"
                data-found-by="{{ $item->found_by }}"
                data-found-at="{{ $item->found_at }}"
                data-date-encoded="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}"
                data-storage-location="{{ $item->storage_location }}"
                data-item-description="{{ $descForView }}"
                data-item-name="{{ $itemName }}"
                data-encoded-by="{{ $encodedBy }}"
                data-status="{{ $item->status }}"
                @if($item->image_data) data-image="{{ $item->image_data }}" @endif>
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->item_type ?? '—' }}</td>
              <td>{{ $item->found_at ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td>
                {{ $item->retention_end ?? '—' }}
                @if($item->is_overdue)
                  <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRED</span>
                @elseif($item->expires_in_30_days)
                  <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRING</span>
                @endif
              </td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td>{{ $timestamp }}</td>
              <td>
                <div class="found-action-cell">
                  <button type="button" class="found-btn-view internal-view-btn">View</button>
                  <button type="button" class="found-btn-cancel cancel-item-btn" data-cancel-id="{{ $item->id }}">Cancel</button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="table-empty">No internal items. Click <strong>Encode Item</strong> to add one.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Guest Items Tab --}}
  <div id="tab-guest" style="display:none;" class="inventory-card matched-reports-card">
    <div class="inventory-title found-title-guest">Recovered IDs (External)</div>
    <div class="table-wrapper">
      <table class="found-table found-table-guest">
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
        <tbody id="guestTableBody">
          @forelse($guestItems as $item)
            @php
              $meta       = $item->parseDescription();
              $foundByWho = $meta['Found By'] ?? '';
              $timestamp  = $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : ($item->date_encoded ? $item->date_encoded->format('Y-m-d') . ' 00:00:00' : '—');
            @endphp
            <tr class="{{ $item->is_overdue ? 'row-overdue' : ($item->expires_in_30_days ? 'row-expiring' : '') }}"
                data-id="{{ $item->id }}"
                data-color="{{ $item->color }}"
                data-encoded-by-staff="{{ $item->found_by }}"
                data-found-by-line="{{ $foundByWho }}"
                data-date-encoded="{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '' }}"
                data-storage-location="{{ $item->storage_location }}"
                data-id-type="{{ $meta['ID Type'] ?? '' }}"
                data-fullname="{{ $meta['Owner'] ?? '' }}"
                @if($item->image_data) data-image="{{ $item->image_data }}" @endif>
              <td><strong>{{ $item->id }}</strong></td>
              <td>{{ $item->found_by ?? '—' }}</td>
              <td>{{ $item->date_encoded ? $item->date_encoded->format('Y-m-d') : '—' }}</td>
              <td>
                {{ $item->retention_end ?? '—' }}
                @if($item->is_overdue)
                  <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRED</span>
                @elseif($item->expires_in_30_days)
                  <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;white-space:nowrap;vertical-align:middle;">EXPIRING</span>
                @endif
              </td>
              <td>{{ $item->storage_location ?? '—' }}</td>
              <td>{{ $timestamp }}</td>
              <td>
                <div class="found-action-cell">
                  <button type="button" class="found-btn-view-guest guest-view-btn">View</button>
                  <button type="button" class="found-btn-cancel cancel-item-btn" data-cancel-id="{{ $item->id }}">Cancel</button>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="table-empty">No guest items.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection

{{-- ══════════════════════════════════════════════════════════════════════════
     MODALS (outside @section so they render at body level)
══════════════════════════════════════════════════════════════════════════════ --}}

@push('scripts')

{{-- ── Expiry Popup ────────────────────────────────────────────────────── --}}
<div class="expiry-overlay" id="expiryOverlay" role="dialog" aria-modal="true"
     onclick="if(event.target===this)document.getElementById('expiryOverlay').classList.remove('open')">
  <div class="expiry-popup">
    <div class="expiry-popup-hdr">
      <span class="expiry-popup-title">Items with Approaching Retention Dates</span>
      <button type="button" class="expiry-popup-close" id="expiryPopupClose" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="expiry-cards-grid" id="expiryCardsGrid">
      @forelse($expiringItems as $ei)
        <div class="expiry-card">
          <h4 class="expiry-card-title"><i class="fa-regular fa-file-lines"></i> {{ $ei->id ?? $ei->item_type ?? 'Item' }}</h4>
          <div class="expiry-card-meta"><i class="fa-solid fa-location-dot"></i><span>{{ $ei->found_at ?? $ei->storage_location ?? 'N/A' }}</span></div>
          <div class="expiry-card-meta"><i class="fa-regular fa-calendar"></i><span>Expires: {{ $ei->retention_end }}</span></div>
          <span class="expiry-card-badge">Expiring Soon</span>
          <div class="expiry-card-footer">
            <button type="button" class="btn-cancel-item cancel-item-btn" data-cancel-id="{{ $ei->id }}">Cancel</button>
          </div>
        </div>
      @empty
        <p style="color:#9ca3af;font-size:13px;font-style:italic;">No items approaching expiry within 30 days.</p>
      @endforelse
    </div>
  </div>
</div>

{{-- ── Internal Item View Modal ────────────────────────────────────────── --}}
<div class="view-modal-overlay" id="viewModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeViewModal()">
  <div class="view-modal" onclick="event.stopPropagation()" style="max-width:720px;">
    <div class="view-modal-header">
      <h3 class="view-modal-title">Item Details</h3>
      <button type="button" class="view-modal-close" onclick="closeViewModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="view-modal-inner">
      <div class="view-modal-left-col">
        <div class="view-modal-image" id="viewModalImage">
          <div class="view-modal-image-placeholder" id="viewModalImgPlaceholder">
            <i class="fa-solid fa-box-open" style="font-size:28px;"></i>
            <span>No photo</span>
          </div>
          <img id="viewModalImg" src="" alt="Item photo" style="display:none;width:160px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #e0e0e0;">
        </div>
        <p id="viewModalBarcode" style="margin-top:10px;font-size:13px;font-weight:600;color:#374151;text-align:center;">Barcode ID: —</p>
      </div>
      <div class="view-modal-right-col">
        <h4 class="view-modal-section-title">General Information</h4>
        <div id="viewModalBody" class="view-modal-body"></div>
        <div style="margin-top:16px;text-align:right;">
          <button type="button" class="view-modal-cancel" onclick="closeViewModal()">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Guest Item View Modal ────────────────────────────────────────────── --}}
<div class="guest-modal-overlay" id="guestViewModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeGuestModal()">
  <div class="guest-modal" onclick="event.stopPropagation()">
    <div class="guest-modal-header">
      <h3 class="guest-modal-header-title">Item Details</h3>
      <button type="button" class="guest-modal-header-close" onclick="closeGuestModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="guest-modal-body">
      <div class="guest-modal-left">
        <div class="guest-modal-photo-placeholder" id="guestModalPhotoPlaceholder">
          <i class="fa-regular fa-id-card" style="font-size:28px;"></i>
          <span>No photo</span>
        </div>
        <img id="guestModalPhoto" class="guest-modal-photo" src="" alt="ID photo" style="display:none;">
        <p class="guest-modal-barcode-label" id="guestModalBarcodeLabel">Barcode ID: —</p>
      </div>
      <div class="guest-modal-right">
        <h4 class="guest-modal-section-title">General Information</h4>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">ID Type:</span>
          <span class="guest-modal-info-value" id="guestModalIdType">—</span>
        </div>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">Fullname:</span>
          <span class="guest-modal-info-value" id="guestModalFullname">—</span>
        </div>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">Color:</span>
          <span class="guest-modal-info-value" id="guestModalColor">—</span>
        </div>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">Storage Location:</span>
          <span class="guest-modal-info-value" id="guestModalStorageLocation">—</span>
        </div>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">Found By:</span>
          <span class="guest-modal-info-value" id="guestModalFoundBy">—</span>
        </div>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">Encoded By:</span>
          <span class="guest-modal-info-value" id="guestModalEncodedBy">—</span>
        </div>
        <div class="guest-modal-info-row">
          <span class="guest-modal-info-label">Date Surrendered:</span>
          <span class="guest-modal-info-value" id="guestModalDateSurrendered">—</span>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Confirm cancel item (marks status Cancelled) ───────────────────── --}}
<div class="cancel-confirm-overlay" id="cancelItemConfirmModal" role="dialog" aria-modal="true" aria-hidden="true"
     onclick="if(event.target===this)closeCancelItemConfirmModal()">
  <div class="cancel-confirm-dialog" onclick="event.stopPropagation()" role="document">
    <h3 class="cancel-confirm-hdr">Cancel item</h3>
    <div class="cancel-confirm-body">
      <p style="margin:0 0 10px;">Cancel listing for barcode <strong id="cancelItemConfirmId">—</strong>?</p>
      <p style="margin:0;font-size:13px;color:#6b7280;">This removes the item from active lists by setting its status to <strong>Cancelled</strong>.</p>
    </div>
    <div class="cancel-confirm-foot">
      <button type="button" class="cancel-confirm-btn cancel-confirm-btn--secondary" id="cancelItemConfirmBack">Go back</button>
      <button type="button" class="cancel-confirm-btn cancel-confirm-btn--danger" id="cancelItemConfirmSubmit">Confirm cancel</button>
    </div>
  </div>
</div>

@include('admin.partials.internal-encode-item-modal', ['campusLocations' => $campusLocations])

{{-- ── Encode Report (lost report on behalf of student) — fields match student lost-report modal ─────────────────── --}}
<div class="report-modal-overlay" id="encodeReportModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeEncodeReportModal()">
  <div class="report-modal" onclick="event.stopPropagation()">
    <div class="report-modal-header">
      <h2 class="report-modal-title">Item Lost Report</h2>
      <button type="button" class="report-modal-close" onclick="closeEncodeReportModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="encodeReportForm" class="report-modal-body">
      @include('partials.lost-report-form-fields', ['variant' => 'admin', 'categories' => $categoriesInternal])
      <div class="report-modal-footer">
        <button type="button" class="report-btn-cancel" onclick="closeEncodeReportModal()">Cancel</button>
        <button type="button" class="report-btn-confirm" id="encodeReportSubmitBtn">Next</button>
      </div>
    </form>
  </div>
</div>

{{-- ── Encode Guest ID Modal (Lost ID Report) ─────────────── --}}
<div class="report-modal-overlay" id="encodeIdModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeGuestEncodeModal()">
  <div class="report-modal" onclick="event.stopPropagation()">
    <div class="report-modal-header">
      <h2 class="report-modal-title">Lost ID Report</h2>
      <button type="button" class="report-modal-close" onclick="closeGuestEncodeModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="encodeGuestForm" class="report-modal-body">
      <div class="report-form-row">
        <label class="report-form-label" for="guestBarcodeId">Barcode ID:</label>
        <div class="report-form-field">
          <input type="text" id="guestBarcodeId" name="barcode_id" class="report-input" placeholder="Required — e.g. UB1005" required autocomplete="off">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestIdType">ID Type:</label>
        <div class="report-form-field">
          <select id="guestIdType" name="id_type" class="report-input report-select">
            <option value="">— Select ID Type —</option>
            @foreach(['Student ID','Faculty ID','Staff ID','Employee ID','Visitor ID','Driver\'s License','Passport','SSS ID','GSIS ID','PhilHealth ID','Pag-IBIG ID','Postal ID','Voter\'s ID','Senior Citizen ID','PWD ID','National ID (PhilSys)','Other'] as $idType)
              <option>{{ $idType }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestFullname">Fullname: <span class="report-required">*</span></label>
        <div class="report-form-field">
          <input type="text" id="guestFullname" name="fullname" class="report-input" required placeholder="As printed on the ID">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestColor">Color: <span class="report-required">*</span></label>
        <div class="report-form-field">
          <select id="guestColor" name="color" class="report-input report-select" required>
            <option value="">— Select —</option>
            @foreach(['Red','Orange','Yellow','Green','Blue','Violet','Black','White','Brown','Rainbow','Multi','Other'] as $c)
              <option>{{ $c }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestStorage">Storage Location:</label>
        <div class="report-form-field">
          <input type="text" id="guestStorage" name="storage_location" class="report-input" placeholder="e.g. Shelf A-1">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestFoundBy">Found By:</label>
        <div class="report-form-field">
          <input type="text" id="guestFoundBy" name="found_by" class="report-input" placeholder="e.g. juan.delacruz@ub.edu.ph or Juan Dela Cruz">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestEncodedBy">Encoded By:</label>
        <div class="report-form-field">
          <input type="text" id="guestEncodedBy" name="encoded_by" class="report-input" placeholder="e.g. J. Dela Cruz">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="guestDateSurrendered">Date Surrendered:</label>
        <div class="report-form-field">
          <input type="date" id="guestDateSurrendered" name="date_surrendered" class="report-input" max="{{ date('Y-m-d') }}">
        </div>
      </div>
      <div class="report-form-row report-form-row-textarea pp-photo-row">
        <label class="report-form-label">Photo:</label>
        <div class="report-form-field">
        <div class="pp-wrap" id="guestIdPhotoPicker">
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
        </div>
      </div>
      <div class="report-modal-footer">
        <button type="button" class="report-btn-cancel" onclick="closeGuestEncodeModal()">Cancel</button>
        <button type="button" class="report-btn-confirm" id="guestEncodeSubmitBtn">Next</button>
      </div>
    </form>
  </div>
</div>

{{-- ── Success Modal ─────────────────────────────────────────────────────── --}}
<div class="success-modal-overlay" id="encodeSuccessModal">
  <div class="success-modal" onclick="event.stopPropagation()">
    <button type="button" class="success-modal-close" id="encodeSuccessClose" aria-label="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="success-modal-icon"><i class="fa-solid fa-check"></i></div>
    <h3 class="success-modal-title">Success</h3>
    <p class="success-modal-message">Item encoded successfully!</p>
    <p class="success-modal-barcode">Barcode ID: <strong id="encodeSuccessBarcodeText"></strong></p>
    <div class="success-modal-footer">
      <button type="button" class="report-btn-confirm" onclick="closeEncodeSuccessModal()">OK</button>
    </div>
  </div>
</div>

<script>
(function(){
  var m = document.querySelector('meta[name="csrf-token"]');
  window._CSRF = m ? m.getAttribute('content') : '';
})();
var _encItemPhoto = null;
var _encGuestPhoto = null;
var _encReportPhoto = null;
var _encItemPP = null;
var _encGuestPP = null;
var _encReportPP = null;

function _appAlert(msg){ if(typeof window.appUiAlert === 'function') window.appUiAlert(msg); else alert(msg); }

function _escRev(s){
  return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function _rowRev(label, val){
  if(val==null||val==='') val = '—';
  return '<div class="report-form-row" style="margin-bottom:8px;"><span class="report-form-label" style="display:inline-block;min-width:170px;">'+_escRev(label)+'</span><span style="color:#111827;">'+_escRev(String(val))+'</span></div>';
}
function buildEncodeItemReviewHtml(){
  var img = '';
  if(typeof _encItemPhoto !== 'undefined' && _encItemPhoto){
    img = '<div class="report-form-row"><span class="report-form-label">Photo</span><span><img src="'+_escRev(_encItemPhoto)+'" alt="" style="max-width:200px;border-radius:8px;border:1px solid #e5e7eb;"></span></div>';
  }
  return [
    _rowRev('Barcode ID', (document.getElementById('encBarcodeId')||{}).value),
    _rowRev('Category', (document.getElementById('encCategory')||{}).value),
    _rowRev('Item', (document.getElementById('encItem')||{}).value),
    _rowRev('Color', (document.getElementById('encColor')||{}).value),
    _rowRev('Brand', (document.getElementById('encBrand')||{}).value),
    _rowRev('Item Description', (document.getElementById('encDescription')||{}).value),
    _rowRev('Storage Location', (document.getElementById('encStorage')||{}).value),
    _rowRev('Found At', (document.getElementById('encFoundAt')||{}).value),
    _rowRev('Found In', (document.getElementById('encFoundIn')||{}).value),
    _rowRev('Found By', (document.getElementById('encFoundBy')||{}).value),
    _rowRev('Date Found', (document.getElementById('encDateFound')||{}).value),
    img
  ].join('');
}
function buildEncodeReportReviewHtml(){
  var img = '';
  if(typeof _encReportPhoto !== 'undefined' && _encReportPhoto){
    img = '<div class="report-form-row"><span class="report-form-label">Photo</span><span><img src="'+_escRev(_encReportPhoto)+'" alt="" style="max-width:200px;border-radius:8px;border:1px solid #e5e7eb;"></span></div>';
  }
  var cat = (document.getElementById('repCategory')||{}).value || '';
  var rows = [
    _rowRev('Student Email', (document.getElementById('repStudentEmail')||{}).value),
    _rowRev('Category', cat),
  ];
  if(cat === 'Document & Identification'){
    rows.push(_rowRev('Document Type', (document.getElementById('repDocType')||{}).value));
  }
  rows.push(
    _rowRev('Full Name', (document.getElementById('repFullName')||{}).value),
    _rowRev('Contact Number', (document.getElementById('repContact')||{}).value),
    _rowRev('Department', (document.getElementById('repDept')||{}).value),
    _rowRev('ID', (document.getElementById('repId')||{}).value),
    _rowRev('Item', (document.getElementById('repItem')||{}).value),
    _rowRev('Item Description', (document.getElementById('repDesc')||{}).value),
    _rowRev('Color', (document.getElementById('repColor')||{}).value),
    _rowRev('Brand', (document.getElementById('repBrand')||{}).value),
    _rowRev('Date Lost', (document.getElementById('repDateLost')||{}).value),
    img
  );
  return rows.join('');
}
function buildGuestReviewHtml(){
  var img = '';
  if(typeof _encGuestPhoto !== 'undefined' && _encGuestPhoto){
    img = '<div class="report-form-row"><span class="report-form-label">Photo</span><span><img src="'+_escRev(_encGuestPhoto)+'" alt="" style="max-width:200px;border-radius:8px;border:1px solid #e5e7eb;"></span></div>';
  }
  return [
    _rowRev('Barcode ID', (document.getElementById('guestBarcodeId')||{}).value),
    _rowRev('ID Type', (document.getElementById('guestIdType')||{}).value),
    _rowRev('Fullname', (document.getElementById('guestFullname')||{}).value),
    _rowRev('Color', (document.getElementById('guestColor')||{}).value),
    _rowRev('Storage Location', (document.getElementById('guestStorage')||{}).value),
    _rowRev('Found By', (document.getElementById('guestFoundBy')||{}).value),
    _rowRev('Encoded By', (document.getElementById('guestEncodedBy')||{}).value),
    _rowRev('Date Surrendered', (document.getElementById('guestDateSurrendered')||{}).value),
    img
  ].join('');
}

// ── Photo pickers (must not throw — rest of page depends on this) ─────────
(function(){
  if (typeof PhotoPicker === 'undefined' || !PhotoPicker.init) return;
  try {
    _encItemPP  = PhotoPicker.init({ el: 'encodeItemPhotoPicker', onChange: function(d){ _encItemPhoto  = d||null; } });
  } catch(e) { console.warn('encodeItemPhotoPicker', e); }
  try {
    _encGuestPP = PhotoPicker.init({ el: 'guestIdPhotoPicker',    onChange: function(d){ _encGuestPhoto = d||null; } });
  } catch(e) { console.warn('guestIdPhotoPicker', e); }
  try {
    _encReportPP = PhotoPicker.init({ el: 'encodeReportPhotoPicker', onChange: function(d){ _encReportPhoto = d||null; } });
  } catch(e) { console.warn('encodeReportPhotoPicker', e); }
})();
(function(){
  var repCat = document.getElementById('repCategory');
  if(!repCat) return;
  function syncRepDocRow(){
    var row = document.getElementById('repDocTypeRow');
    if(!row) return;
    row.style.display = repCat.value === 'Document & Identification' ? 'grid' : 'none';
  }
  repCat.addEventListener('change', syncRepDocRow);
  syncRepDocRow();
})();
var _CSRF = window._CSRF || '';
var BARCODE_CTX_URL = '{{ route("admin.found.barcode-context") }}';

function _foundJsonHeaders(){
  return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': _CSRF };
}
function _parseLaravelFetchResponse(r){
  return r.text().then(function(text){
    var data = null;
    if(text){ try { data = JSON.parse(text); } catch(e) {} }
    return { ok: r.ok, status: r.status, data: data };
  });
}
function _laravelErrMsg(res){
  var d = res.data;
  if(d && typeof d === 'object'){
    if(d.message) return d.message;
    if(d.errors){ for(var k in d.errors){ var v = d.errors[k]; if(v && v.length) return v[0]; } }
    if(d.error) return d.error;
  }
  if(res.status === 419) return 'Page expired. Refresh and try again.';
  return 'Request failed (' + res.status + ').';
}

function openBarcodeDupModal(msg){
  var m = document.getElementById('barcodeDupModal');
  var t = document.getElementById('barcodeDupModalText');
  if(t) t.textContent = msg || '';
  if(m){
    m.classList.add('open');
    m.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
  }
}
function closeBarcodeDupModal(){
  var m = document.getElementById('barcodeDupModal');
  if(m){
    m.classList.remove('open');
    m.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }
}
(function(){
  var ok = document.getElementById('barcodeDupModalOk');
  if(ok) ok.addEventListener('click', closeBarcodeDupModal);
})();

function fetchBarcodeContext(barcode){
  return fetch(BARCODE_CTX_URL + '?barcode=' + encodeURIComponent(barcode), {
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
  }).then(function(r){ return r.json(); });
}

// ── Tab switching ──────────────────────────────────────────────────────────
(function(){
  var allTab = document.getElementById('allItemsTab');
  var gstTab = document.getElementById('guestItemsTab');
  var allSec = document.getElementById('tab-internal');
  var gstSec = document.getElementById('tab-guest');
  var allAct = document.getElementById('allItemsActions');
  var gstAct = document.getElementById('guestActionsBar');
  var catSel = document.getElementById('foundCategorySelect');
  if(!allTab||!gstTab) return;
  function showAll(){
    allTab.classList.add('matched-tab-active'); gstTab.classList.remove('matched-tab-active');
    allSec.style.display=''; gstSec.style.display='none';
    if(allAct) allAct.style.display=''; if(gstAct) gstAct.style.display='none';
    if(catSel){ catSel.disabled=false; catSel.style.display=''; }
  }
  function showGuest(){
    gstTab.classList.add('matched-tab-active'); allTab.classList.remove('matched-tab-active');
    gstSec.style.display=''; allSec.style.display='none';
    if(allAct) allAct.style.display='none'; if(gstAct) gstAct.style.display='';
    if(catSel){ catSel.disabled=true; catSel.style.display='none'; }
  }
  allTab.addEventListener('click', showAll);
  gstTab.addEventListener('click', showGuest);
  if(window.location.hash==='#guest') showGuest();
})();

// ── Found At / Found In (encode internal) ───────────────────────────────────
(function(){
  var sel = document.getElementById('encFoundAt');
  var row = document.getElementById('encFoundInRow');
  window._syncEncFoundInRow = function(){
    if(!sel || !row) return;
    row.style.display = sel.value ? '' : 'none';
    if(!sel.value){
      var fin = document.getElementById('encFoundIn');
      if(fin) fin.value = '';
    }
  };
  if(sel) sel.addEventListener('change', window._syncEncFoundInRow);
})();

// ── Encode Item (internal) modal ─────────────────────────────────────────────
function openEncodeModal(){
  document.getElementById('encodeItemForm').reset();
  if(_encItemPP) _encItemPP.clear();
  _encItemPhoto = null;
  if(typeof window._syncEncFoundInRow === 'function') window._syncEncFoundInRow();
  document.getElementById('itemLostReportModal').classList.add('report-modal-open');
}
function closeEncodeModal(){
  document.getElementById('itemLostReportModal').classList.remove('report-modal-open');
}
(function(){
  var el = document.getElementById('encodeNewItemBtn');
  if(el) el.addEventListener('click', openEncodeModal);
})();

(function(){
  var sub = document.getElementById('encodeItemSubmitBtn');
  if(!sub) return;
  function postEncodeItem(btn){
    var item = document.getElementById('encItem').value.trim();
    var col  = document.getElementById('encColor').value;
    var desc = document.getElementById('encDescription').value.trim();
    btn.disabled = true; btn.textContent = 'Saving…';
    return fetch('{{ url("/admin/found-items") }}', {
      method: 'POST',
      headers: _foundJsonHeaders(),
      body: JSON.stringify({
        barcode_id:       document.getElementById('encBarcodeId').value.trim(),
        category:         document.getElementById('encCategory').value || '',
        item:             document.getElementById('encItem').value || '',
        color:            col,
        brand:            document.getElementById('encBrand').value || '',
        found_at:         document.getElementById('encFoundAt').value || '',
        found_in:         document.getElementById('encFoundIn').value || '',
        found_by:         document.getElementById('encFoundBy').value || '',
        date_found:       document.getElementById('encDateFound').value || '',
        storage_location: document.getElementById('encStorage').value || '',
        item_description: desc,
        imageDataUrl:     _encItemPhoto || null
      })
    }).then(_parseLaravelFetchResponse).then(function(res){
      btn.disabled = false; btn.textContent = 'Next';
      if(res.ok && res.data && res.data.ok){
        closeEncodeModal();
        closeAdminEncodeReviewModal();
        showEncodeSuccess(res.data.id);
      } else {
        _appAlert(_laravelErrMsg(res) || 'Could not encode item. Try again.');
      }
    }).catch(function(){
      btn.disabled = false; btn.textContent = 'Next';
      _appAlert('Network error. Try again.');
    });
  }
  sub.addEventListener('click', function(){
  var barcode = document.getElementById('encBarcodeId').value.trim();
  var item = document.getElementById('encItem').value.trim();
  var col  = document.getElementById('encColor').value;
  var desc = document.getElementById('encDescription').value.trim();
  if(!barcode){ _appAlert('Barcode ID is required.'); document.getElementById('encBarcodeId').focus(); return; }
  if(!item){ document.getElementById('encItem').focus(); return; }
  if(!col){ document.getElementById('encColor').focus(); return; }
  if(!desc){ document.getElementById('encDescription').focus(); return; }
  var todayStr = new Date().toISOString().split('T')[0];
  var df = (document.getElementById('encDateFound').value || '').trim();
  if(df && df > todayStr){ _appAlert('Date Found cannot be in the future.'); document.getElementById('encDateFound').focus(); return; }

  var btn = this;
  btn.disabled = true; btn.textContent = 'Checking…';
  fetchBarcodeContext(barcode).then(function(ctx){
    if(!ctx.ok){ throw new Error(ctx.error || 'Could not verify barcode.'); }
    if(ctx.exists){
      btn.disabled = false; btn.textContent = 'Next';
      var n = ctx.linked_report_count || 0;
      var msg = n >= 1
        ? 'This barcode is already registered. It has '+n+' linked lost report(s). You cannot register a duplicate found item with the same ID.'
        : 'This barcode is already in use. You cannot register a duplicate found item.';
      openBarcodeDupModal(msg);
      return;
    }
    document.getElementById('adminEncodeReviewTitle').textContent = 'Item Recovered Report';
    document.getElementById('adminEncodeReviewSummary').innerHTML = buildEncodeItemReviewHtml();
    window._adminEncodeReview = {
      runSubmit: function(){ return postEncodeItem(btn); },
      onBack: function(){
        document.getElementById('itemLostReportModal').classList.add('report-modal-open');
        document.body.style.overflow = 'hidden';
      }
    };
    closeEncodeModal();
    openAdminEncodeReviewModal();
    btn.disabled = false;
    btn.textContent = 'Next';
    return;
  }).catch(function(err){
    btn.disabled = false; btn.textContent = 'Next';
    _appAlert((err && err.message) ? err.message : 'Network error. Try again.');
  });
});
})();

function closeEncodeReportModal(){
  document.getElementById('encodeReportModal').classList.remove('report-modal-open');
}
(function(){
  var b = document.getElementById('encodeReportBtn');
  if(b) b.addEventListener('click', function(){
    var f = document.getElementById('encodeReportForm');
    if(f) f.reset();
    if(_encReportPP) _encReportPP.clear();
    _encReportPhoto = null;
    var repCat = document.getElementById('repCategory');
    var row = document.getElementById('repDocTypeRow');
    if(repCat && row) row.style.display = repCat.value === 'Document & Identification' ? 'grid' : 'none';
    document.getElementById('encodeReportModal').classList.add('report-modal-open');
  });
})();

(function(){
  var sub = document.getElementById('encodeReportSubmitBtn');
  if(!sub) return;
  function runEncodeReportPost(btn){
    var email = document.getElementById('repStudentEmail').value.trim();
    var contact = document.getElementById('repContact').value.trim();
    var dept = document.getElementById('repDept').value.trim();
    var desc = document.getElementById('repDesc').value.trim();
    var cat = document.getElementById('repCategory').value || '';
    btn.disabled = true; btn.textContent = 'Saving…';
    return fetch('{{ route("admin.found.lost-report") }}', {
      method: 'POST',
      headers: _foundJsonHeaders(),
      body: JSON.stringify({
        student_email:    email,
        category:         cat,
        document_type:    (document.getElementById('repDocType') && document.getElementById('repDocType').value) ? document.getElementById('repDocType').value : '',
        full_name:        document.getElementById('repFullName').value || '',
        contact_number:   contact,
        department:       dept,
        id:               document.getElementById('repId').value || '',
        item:             document.getElementById('repItem').value || '',
        item_description: desc,
        color:            document.getElementById('repColor').value || '',
        brand:            document.getElementById('repBrand').value || '',
        date_lost:        document.getElementById('repDateLost').value || '',
        imageDataUrl:     _encReportPhoto || null
      })
    }).then(_parseLaravelFetchResponse).then(function(res){
      btn.disabled = false; btn.textContent = 'Next';
      if(res.ok && res.data && res.data.ok){
        closeEncodeReportModal();
        closeAdminEncodeReviewModal();
        showEncodeSuccess(res.data.id);
      } else {
        _appAlert(_laravelErrMsg(res) || 'Could not save report.');
      }
    }).catch(function(){
      btn.disabled = false; btn.textContent = 'Next';
      _appAlert('Network error. Try again.');
    });
  }
  sub.addEventListener('click', function(){
    var email = document.getElementById('repStudentEmail').value.trim();
    var contact = document.getElementById('repContact').value.trim();
    var dept = document.getElementById('repDept').value.trim();
    var desc = document.getElementById('repDesc').value.trim();
    var cat = document.getElementById('repCategory').value || '';
    if(!email){ document.getElementById('repStudentEmail').focus(); return; }
    if(!contact){ document.getElementById('repContact').focus(); return; }
    if(!dept){ document.getElementById('repDept').focus(); return; }
    if(!desc){ document.getElementById('repDesc').focus(); return; }
    if(cat === 'Document & Identification'){
      var dt = (document.getElementById('repDocType').value || '').trim();
      if(!dt){ document.getElementById('repDocType').focus(); return; }
    }
    var btn = this;
    document.getElementById('adminEncodeReviewTitle').textContent = 'Item Lost Report';
    document.getElementById('adminEncodeReviewSummary').innerHTML = buildEncodeReportReviewHtml();
    window._adminEncodeReview = {
      runSubmit: function(){ return runEncodeReportPost(btn); },
      onBack: function(){
        document.getElementById('encodeReportModal').classList.add('report-modal-open');
        document.body.style.overflow = 'hidden';
      }
    };
    closeEncodeReportModal();
    openAdminEncodeReviewModal();
  });
})();

// ── Encode Guest ID modal ─────────────────────────────────────────────────
function openGuestModal(){
  document.getElementById('encodeGuestForm').reset();
  if(_encGuestPP) _encGuestPP.clear();
  _encGuestPhoto = null;
  document.getElementById('encodeIdModal').classList.add('report-modal-open');
}
function closeGuestEncodeModal(){
  document.getElementById('encodeIdModal').classList.remove('report-modal-open');
}
(function(){
  var b = document.getElementById('encodeGuestItemBtn');
  if(b) b.addEventListener('click', openGuestModal);
})();

(function(){
  var sub = document.getElementById('guestEncodeSubmitBtn');
  if(!sub) return;
  function postGuestEncode(btn, fn, col){
    btn.disabled = true; btn.textContent = 'Saving…';
    return fetch('{{ route("admin.found.guest") }}', {
      method: 'POST',
      headers: _foundJsonHeaders(),
      body: JSON.stringify({
        barcode_id:       document.getElementById('guestBarcodeId').value.trim(),
        id_type:          document.getElementById('guestIdType').value || '',
        fullname:         fn,
        color:            col,
        storage_location: document.getElementById('guestStorage').value || '',
        found_by:         document.getElementById('guestFoundBy').value || '',
        encoded_by:       document.getElementById('guestEncodedBy').value || '',
        date_surrendered: document.getElementById('guestDateSurrendered').value || '',
        imageDataUrl:     _encGuestPhoto || null
      })
    }).then(_parseLaravelFetchResponse).then(function(res){
      btn.disabled = false; btn.textContent = 'Next';
      if(res.ok && res.data && res.data.ok){
        closeGuestEncodeModal();
        closeAdminEncodeReviewModal();
        showEncodeSuccess(res.data.id);
      } else {
        _appAlert(_laravelErrMsg(res) || 'Could not encode guest ID. Try again.');
      }
    }).catch(function(){
      btn.disabled = false; btn.textContent = 'Next';
      _appAlert('Network error. Try again.');
    });
  }
  sub.addEventListener('click', function(){
  var barcode = document.getElementById('guestBarcodeId').value.trim();
  var fn  = document.getElementById('guestFullname').value.trim();
  var col = document.getElementById('guestColor').value;
  if(!barcode){ _appAlert('Barcode ID is required.'); document.getElementById('guestBarcodeId').focus(); return; }
  if(!fn){ document.getElementById('guestFullname').focus(); return; }
  if(!col){ document.getElementById('guestColor').focus(); return; }

  var btn = this;
  btn.disabled = true; btn.textContent = 'Checking…';
  fetchBarcodeContext(barcode).then(function(ctx){
    if(!ctx.ok){ throw new Error(ctx.error || 'Could not verify barcode.'); }
    if(ctx.exists){
      btn.disabled = false; btn.textContent = 'Next';
      var n = ctx.linked_report_count || 0;
      var msg = n >= 1
        ? 'This barcode is already registered. It has '+n+' linked lost report(s). You cannot register a duplicate found item with the same ID.'
        : 'This barcode is already in use. You cannot register a duplicate found item.';
      openBarcodeDupModal(msg);
      return;
    }
    document.getElementById('adminEncodeReviewTitle').textContent = 'Lost ID Report';
    document.getElementById('adminEncodeReviewSummary').innerHTML = buildGuestReviewHtml();
    window._adminEncodeReview = {
      runSubmit: function(){ return postGuestEncode(btn, fn, col); },
      onBack: function(){
        document.getElementById('encodeIdModal').classList.add('report-modal-open');
        document.body.style.overflow = 'hidden';
      }
    };
    closeGuestEncodeModal();
    openAdminEncodeReviewModal();
    btn.disabled = false;
    btn.textContent = 'Next';
    return;
  }).catch(function(err){
    btn.disabled = false; btn.textContent = 'Next';
    _appAlert((err && err.message) ? err.message : 'Network error. Try again.');
  });
});
})();

// ── Internal View Modal ───────────────────────────────────────────────────
function closeViewModal(){
  var el = document.getElementById('viewModal');
  if(el) el.classList.remove('open');
  document.body.style.overflow = '';
}
function openViewModal(row){
  function v(a){ return row.getAttribute(a)||'—'; }
  function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function row_(label,val){ if(!val||val==='—') return ''; return '<div class="vm-row"><span class="vm-label">'+esc(label)+'</span><span class="vm-val">'+esc(val)+'</span></div>'; }

  document.getElementById('viewModalBarcode').textContent = 'Barcode ID: ' + v('data-id');
  document.getElementById('viewModalBody').innerHTML = [
    row_('Category:', v('data-category')),
    row_('Item:', v('data-item-name')),
    row_('Color:', v('data-color')),
    row_('Brand:', v('data-brand')),
    row_('Item Description:', v('data-item-description')),
    row_('Storage Location:', v('data-storage-location')),
    row_('Found At:', v('data-found-at')),
    row_('Found By:', v('data-found-by')),
    row_('Encoded By:', v('data-encoded-by')),
    row_('Date Found:', v('data-date-encoded')),
  ].join('') || '<p style="color:#9ca3af;font-size:13px;">No details.</p>';

  var imgUrl = row.getAttribute('data-image');
  var img = document.getElementById('viewModalImg');
  var ph  = document.getElementById('viewModalImgPlaceholder');
  if(imgUrl){ img.src=imgUrl; img.style.display='block'; if(ph) ph.style.display='none'; }
  else { img.style.display='none'; if(ph) ph.style.display='flex'; }

  var vm = document.getElementById('viewModal');
  if(vm) vm.classList.add('open');
  document.body.style.overflow = 'hidden';
}
document.addEventListener('click', function(e){
  var btn = e.target.closest('.internal-view-btn');
  if(!btn) return;
  var row = btn.closest('tr');
  if(row && !row.querySelector('td[colspan]')){
    e.preventDefault();
    e.stopPropagation();
    openViewModal(row);
  }
});

// ── Guest View Modal ──────────────────────────────────────────────────────
function closeGuestModal(){
  var el = document.getElementById('guestViewModal');
  if(el) el.classList.remove('open');
  document.body.style.overflow = '';
}
function openGuestViewModal(row){
  function v(a){ return row.getAttribute(a)||'—'; }
  var imgUrl = row.getAttribute('data-image');
  var img = document.getElementById('guestModalPhoto');
  var ph  = document.getElementById('guestModalPhotoPlaceholder');
  if(imgUrl){ img.src=imgUrl; img.style.display='block'; if(ph) ph.style.display='none'; }
  else { img.style.display='none'; if(ph) ph.style.display='flex'; }
  document.getElementById('guestModalBarcodeLabel').textContent = 'Barcode ID: ' + v('data-id');
  document.getElementById('guestModalIdType').textContent       = v('data-id-type');
  document.getElementById('guestModalFullname').textContent     = v('data-fullname');
  document.getElementById('guestModalColor').textContent        = v('data-color');
  document.getElementById('guestModalStorageLocation').textContent = v('data-storage-location');
  document.getElementById('guestModalFoundBy').textContent      = v('data-found-by-line') || '—';
  document.getElementById('guestModalEncodedBy').textContent    = v('data-encoded-by-staff');
  document.getElementById('guestModalDateSurrendered').textContent = v('data-date-encoded');
  var gvm = document.getElementById('guestViewModal');
  if(gvm) gvm.classList.add('open');
  document.body.style.overflow = 'hidden';
}
document.addEventListener('click', function(e){
  var btn = e.target.closest('.guest-view-btn');
  if(!btn) return;
  var row = btn.closest('tr');
  if(row && !row.querySelector('td[colspan]')){
    e.preventDefault();
    e.stopPropagation();
    openGuestViewModal(row);
  }
});

// ── Cancel item (confirm modal) ───────────────────────────────────────────
var _cancelItemPendingBtn = null;
function openCancelItemConfirmModal(id, triggerBtn){
  var modal = document.getElementById('cancelItemConfirmModal');
  var idEl = document.getElementById('cancelItemConfirmId');
  if(!modal || !idEl) return;
  _cancelItemPendingBtn = triggerBtn || null;
  idEl.textContent = id;
  modal.setAttribute('aria-hidden','false');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  var confirmBtn = document.getElementById('cancelItemConfirmSubmit');
  if(confirmBtn){ confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm cancel'; }
}
function closeCancelItemConfirmModal(){
  var modal = document.getElementById('cancelItemConfirmModal');
  if(!modal) return;
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden','true');
  document.body.style.overflow = '';
  _cancelItemPendingBtn = null;
}
(function(){
  var backBtn = document.getElementById('cancelItemConfirmBack');
  var submitBtn = document.getElementById('cancelItemConfirmSubmit');
  if(backBtn) backBtn.addEventListener('click', closeCancelItemConfirmModal);
  if(!submitBtn) return;
  submitBtn.addEventListener('click', function(){
  var btn = this;
  var trigger = _cancelItemPendingBtn;
  var idEl = document.getElementById('cancelItemConfirmId');
  var id = idEl ? idEl.textContent.trim() : '';
  if(!id) return;
  btn.disabled = true;
  btn.textContent = 'Cancelling…';
  var cancelUrl = '{{ url("/admin/found-items") }}/' + encodeURIComponent(id) + '/cancel';
  fetch(cancelUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'X-CSRF-TOKEN': _CSRF,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({})
  }).then(function(r){
    return r.json().then(function(data){
      if(!r.ok) throw new Error(data.error || ('HTTP ' + r.status));
      return data;
    });
  }).then(function(data){
    if(data.ok){
      closeCancelItemConfirmModal();
      if(trigger){
        var row = trigger.closest('tr');
        var card = trigger.closest('.expiry-card');
        if(row) row.remove();
        if(card) card.remove();
      }
    } else {
      _appAlert(data.error || 'Could not cancel item.');
      btn.disabled = false;
      btn.textContent = 'Confirm cancel';
    }
  }).catch(function(err){
    _appAlert((err && err.message) ? err.message : 'Network error.');
    btn.disabled = false;
    btn.textContent = 'Confirm cancel';
  });
});
})();

document.addEventListener('click', function(e){
  var btn = e.target.closest('.cancel-item-btn');
  if(!btn) return;
  var id = btn.getAttribute('data-cancel-id');
  if(!id) return;
  e.preventDefault();
  openCancelItemConfirmModal(id, btn);
});
document.addEventListener('keydown', function(e){
  if(e.key==='Escape') closeCancelItemConfirmModal();
});

// ── Expiry popup ───────────────────────────────────────────────────────────
(function(){
  var overlay  = document.getElementById('expiryOverlay');
  var closeBtn = document.getElementById('expiryPopupClose');
  var trigger  = document.getElementById('expiryTriggerLink');
  if(!overlay) return;
  if(trigger) trigger.addEventListener('click', function(e){ e.preventDefault(); overlay.classList.add('open'); });
  if(closeBtn) closeBtn.addEventListener('click', function(){ overlay.classList.remove('open'); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') overlay.classList.remove('open'); });
})();

// ── Success (app UI or legacy) ────────────────────────────────────────────
function showEncodeSuccess(id){
  if(typeof window.appUiSuccess === 'function'){
    var td = '';
    if(id && String(id).indexOf('REF-') === 0){
      td = window.appUiFormatTicketDisplay ? window.appUiFormatTicketDisplay(id) : id;
    } else if(id){
      td = 'Barcode ID: ' + id;
    }
    window.appUiSuccess({
      title: 'Success',
      message: 'Report has been submitted successfully!',
      ticketId: id,
      ticketDisplay: td,
      onClose: function(){ location.reload(); }
    });
    return;
  }
  document.getElementById('encodeSuccessBarcodeText').textContent = id || '';
  document.getElementById('encodeSuccessModal').classList.add('success-modal-open');
}
function closeEncodeSuccessModal(){
  document.getElementById('encodeSuccessModal').classList.remove('success-modal-open');
}
(function(){
  var el = document.getElementById('encodeSuccessClose');
  if(el) el.addEventListener('click', closeEncodeSuccessModal);
})();

// ── ESC key ───────────────────────────────────────────────────────────────
document.addEventListener('keydown', function(e){
  if(e.key !== 'Escape') return;
  var arm = document.getElementById('adminEncodeReviewModal');
  if(arm && arm.classList.contains('report-modal-open')){
    if(typeof closeAdminEncodeReviewModal === 'function') closeAdminEncodeReviewModal();
    var ob = window._adminEncodeReview && window._adminEncodeReview.onBack;
    if(typeof ob === 'function') ob();
    return;
  }
  closeEncodeModal(); closeGuestEncodeModal(); closeEncodeReportModal(); closeViewModal(); closeGuestModal();
});

// ── Auto-open encode modal if ?encode=1 ───────────────────────────────────
if(window.location.search.indexOf('encode=1') !== -1){ openEncodeModal(); }
if(window.location.search.indexOf('guest=1') !== -1){ openGuestModal(); }
</script>
@endpush

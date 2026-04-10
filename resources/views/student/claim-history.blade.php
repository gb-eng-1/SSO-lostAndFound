@extends('layouts.student')

@section('title', 'Claim History')

@section('content')

  <h1 class="page-title-browse browse-page-title">Claim History</h1>

  <div class="browse-toolbar">
    <div class="report-tabs report-tabs--browse">
      <span class="report-tab active">All Items</span>
    </div>
    <form method="get" action="{{ route('student.claim-history') }}" class="browse-filter-form">
      <div class="browse-filter-filters">
        <label class="sr-only" for="claimStatusSelect">Status</label>
        <select name="claim_status" id="claimStatusSelect" class="found-filter-select browse-filter-select" onchange="this.form.submit()">
          <option value="">Filter By Status</option>
          <option value="claimed" {{ ($claimStatus ?? '') === 'claimed' ? 'selected' : '' }}>Claimed</option>
          <option value="pending" {{ ($claimStatus ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        <label class="sr-only" for="claimCategorySelect">Category</label>
        <select name="claim_category" id="claimCategorySelect" class="found-filter-select browse-filter-select" onchange="this.form.submit()">
          <option value="">Filter By Category</option>
          @foreach($categories as $idx => $cat)
            <option value="{{ $idx }}" {{ ($claimCategoryIdx ?? '') === (string) $idx ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>

  <div class="inventory-card reports-browse-card">
    <div class="inventory-title">Claimed Items</div>
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
            <th>Date Claimed</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tableRows as $row)
            <tr class="{{ $loop->iteration % 2 === 1 ? 'reports-row-alt' : '' }}">
              <td><strong>{{ $row['ticket_id'] }}</strong></td>
              <td>{{ $row['category'] }}</td>
              <td>{{ $row['department'] }}</td>
              <td>{{ $row['student_id'] }}</td>
              <td>{{ $row['contact'] }}</td>
              <td>{{ $row['date_lost'] }}</td>
              <td>{{ $row['date_claimed'] }}</td>
              <td>
                <span class="ch-pill {{ $row['status_class'] }}">{{ $row['ui_status'] }}</span>
              </td>
              <td class="reports-action-cell">
                <button type="button"
                        class="reports-btn reports-btn-view ch-open-detail"
                        data-reference-id="{{ $row['reference_id'] }}">View</button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="table-empty">
                <i class="fa-regular fa-clock" style="font-size:28px;display:block;margin-bottom:10px;color:#d1d5db;"></i>
                No claims yet. Submit a claim from
                <a href="{{ route('student.reports', ['filter' => 'matched']) }}" style="color:#8b0000;font-weight:600;">Matched Reports</a>.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection

@push('scripts')
<div id="chidOverlay" class="chid-overlay" role="dialog" aria-modal="true" aria-labelledby="chidTitle" onclick="if(event.target===this)window.closeChid()">
  <div class="chid-modal" onclick="event.stopPropagation()">
    <div class="chid-header">
      <h2 id="chidTitle" class="chid-title">Item Details</h2>
      <button type="button" class="chid-close" onclick="window.closeChid()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div id="chidBody" class="chid-body">
      <div id="chidLoading" class="chid-loading">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <p>Loading…</p>
      </div>
      <div id="chidError" class="chid-error" style="display:none;"></div>
      <div id="chidContent" class="chid-content" style="display:none;">
        <div class="chid-columns">
          <div class="chid-col chid-col-left">
            <div class="chid-img-wrap">
              <img id="chidImg" class="chid-img" src="" alt="Item" style="display:none;">
              <div id="chidImgPh" class="chid-img-ph"><i class="fa-solid fa-box-open"></i><span>No photo</span></div>
            </div>
            <p class="chid-idline" id="chidBarcodeLine">Barcode ID: —</p>
            <p class="chid-idline" id="chidTicketLine">Ticket ID: —</p>
            <p class="chid-subhead">Claimant’s Information</p>
            <label class="chid-field-label">Name</label>
            <input type="text" id="chidClaimName" class="chid-input" readonly value="">
            <label class="chid-field-label">Contact Number</label>
            <input type="text" id="chidClaimContact" class="chid-input" readonly value="">
            <label class="chid-field-label">Date of Accomplishment</label>
            <input type="text" id="chidClaimDate" class="chid-input" readonly value="">
          </div>
          <div class="chid-col chid-col-right">
            <p class="chid-gen-title">General Information</p>
            <div id="chidGenRows"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="chid-footer">
      <button type="button" id="chidStatusBtn" class="chid-foot-btn chid-foot-claimed">Claimed</button>
    </div>
  </div>
</div>

<script>
(function(){
  var DETAIL_URL = @json(route('student.claim-detail'));

  function esc(s){
    return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function parseItemName(d){
    if(!d) return '';
    var m = String(d).match(/^Item:\s*(.+?)(?:\n|$)/m);
    return m ? m[1].trim() : '';
  }
  function cleanDesc(d){
    if(!d) return '—';
    var t = String(d);
    t = t.replace(/^Item:\s*.+?(\n|$)/m,'').replace(/^Item Type:\s*.+?(\n|$)/m,'')
      .replace(/^Student Number:\s*.+?(\n|$)/m,'').replace(/^Contact:\s*.+?(\n|$)/m,'')
      .replace(/^Department:\s*.+?(\n|$)/m,'').replace(/^Encoded By:\s*.+?(\n|$)/m,'')
      .replace(/\n*--- Claim Record ---[\s\S]*$/m,'').trim();
    return t || '—';
  }
  function fmtDate(val){
    if(val==null||val==='') return '—';
    var s = String(val);
    if(s.indexOf('T')>0) return s.slice(0,10);
    if(s.length>=10) return s.slice(0,10);
    return s;
  }
  function row(l,v){
    v = (v==null||v===''||v==='null') ? '—' : v;
    return '<div class="chid-row"><span class="chid-lbl">'+esc(l)+'</span><span class="chid-val">'+esc(String(v))+'</span></div>';
  }

  window.closeChid = function(){
    var o = document.getElementById('chidOverlay');
    if(o){ o.classList.remove('open'); document.body.style.overflow=''; }
  };

  function openChid(){
    document.getElementById('chidOverlay').classList.add('open');
    document.body.style.overflow='hidden';
  }

  function showLoading(){
    document.getElementById('chidLoading').style.display='flex';
    document.getElementById('chidError').style.display='none';
    document.getElementById('chidContent').style.display='none';
  }
  function showError(msg){
    document.getElementById('chidLoading').style.display='none';
    var e = document.getElementById('chidError');
    e.textContent = msg;
    e.style.display='block';
    document.getElementById('chidContent').style.display='none';
  }
  function renderDetail(payload){
    document.getElementById('chidLoading').style.display='none';
    document.getElementById('chidError').style.display='none';
    document.getElementById('chidContent').style.display='block';

    var item = payload.item;
    var lost = payload.lost_report_summary;
    var ui = payload.ui_status;
    var cl = payload.claimant;

    var img = document.getElementById('chidImg');
    var ph = document.getElementById('chidImgPh');
    if (img) { img.style.display = 'none'; img.removeAttribute('src'); }
    if (ph) { ph.style.display = 'flex'; }

    document.getElementById('chidBarcodeLine').textContent = 'Barcode ID: ' + (item.id||'—');
    document.getElementById('chidTicketLine').textContent = 'Ticket ID: ' + (lost && lost.display_ticket_id ? lost.display_ticket_id : '—');

    var nameIn = document.getElementById('chidClaimName');
    var contactIn = document.getElementById('chidClaimContact');
    var dateIn = document.getElementById('chidClaimDate');
    if(ui === 'claimed' && cl){
      nameIn.value = cl.name || '';
      contactIn.value = cl.contact || '';
      dateIn.value = (cl.date_accomplished && cl.date_accomplished.length>=10) ? cl.date_accomplished.slice(0,10) : (cl.date_accomplished||'');
    } else {
      nameIn.value = '';
      contactIn.value = '';
      dateIn.value = '';
    }

    var encBy = item.encoded_by_parsed != null && item.encoded_by_parsed !== '' ? item.encoded_by_parsed : '—';
    var itemLine = parseItemName(item.item_description) || item.brand || '—';
    var gen = document.getElementById('chidGenRows');
    gen.innerHTML =
      row('Category', item.item_type)
      + row('Item', itemLine)
      + row('Color', item.color)
      + row('Brand', item.brand)
      + row('Item Description', cleanDesc(item.item_description))
      + row('Storage Location', item.storage_location)
      + row('Found At', item.found_at)
      + row('Found By', item.found_by)
      + row('Encoded By', encBy)
      + row('Date Found', fmtDate(item.date_encoded));

    var btn = document.getElementById('chidStatusBtn');
    btn.classList.remove('chid-foot-claimed','chid-foot-pending');
    if(ui === 'claimed'){
      btn.textContent = 'Claimed';
      btn.classList.add('chid-foot-claimed');
    } else {
      btn.textContent = 'Pending';
      btn.classList.add('chid-foot-pending');
    }
  }

  document.querySelectorAll('.ch-open-detail').forEach(function(btn){
    btn.addEventListener('click', function(){
      var ref = this.getAttribute('data-reference-id');
      if(!ref) return;
      openChid();
      showLoading();
      fetch(DETAIL_URL + '?reference_id=' + encodeURIComponent(ref), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function(r){ return r.json().then(function(j){ return { ok: r.ok, j: j }; }); })
        .then(function(res){
          if(!res.j.ok){ throw new Error(res.j.error || 'Could not load details.'); }
          renderDetail(res.j.data);
        })
        .catch(function(err){ showError((err && err.message) ? err.message : 'Could not load details.'); });
    });
  });

  document.addEventListener('keydown', function(e){
    if(e.key==='Escape') window.closeChid();
  });
})();
</script>
@endpush

{{-- Search / global item details — REF- = lost report two-column; UB = found item two-column (mockup) --}}
<div id="studentGlobalItemOverlay" class="item-details-overlay" style="z-index:1600;"
     onclick="if(event.target===this)closeStudentGlobalDetail()">
  <div class="item-details-dialog" style="max-width:min(720px,96vw);" onclick="event.stopPropagation()">
    <div class="item-details-header">
      <h3 class="item-details-title">Item Details</h3>
      <button type="button" class="item-details-close" onclick="closeStudentGlobalDetail()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div id="sgBody" class="item-details-body"></div>
    <div class="item-details-footer" id="sgFooter">
      <button type="button" class="item-details-btn item-details-btn-cancel" onclick="closeStudentGlobalDetail()">Close</button>
      <button type="button" id="sgClaimBtn" class="item-details-btn" style="display:none;background:#2563eb;color:#fff;font-weight:600;" onclick="window._sgOpenMatchedPair && window._sgOpenMatchedPair()">View Match &amp; Claim</button>
    </div>
  </div>
</div>

<script>
(function(){
  var STUDENT_ITEM_URL = @json(route('student.item'));

  window.closeStudentGlobalDetail = function(){
    var o = document.getElementById('studentGlobalItemOverlay');
    if (o) {
      o.classList.remove('open');
    }
    document.body.style.overflow = '';
  };

  function esc(s){
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function infoRow(label, val){
    val = (val==null || val==='') ? '—' : String(val);
    return '<div class="item-details-info-row"><dt>'+esc(label)+'</dt><dd>'+esc(val)+'</dd></div>';
  }
  function formatDisplayDate(v){
    if (v == null || v === '') return '—';
    var s = String(v);
    var mdy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/);
    if (mdy) {
      var mo = parseInt(mdy[1], 10), da = parseInt(mdy[2], 10), yr = mdy[3];
      yr = yr.length === 4 ? yr.slice(-2) : yr;
      return mo + '/' + da + '/' + yr;
    }
    var iso = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    var d;
    if (iso) {
      d = new Date(+iso[1], +iso[2] - 1, +iso[3]);
    } else {
      d = new Date(s);
    }
    if (isNaN(d.getTime())) return s;
    return (d.getMonth()+1) + '/' + d.getDate() + '/' + String(d.getFullYear()).slice(-2);
  }

  function itemImageSrc(raw){
    if (!raw || typeof raw !== 'string') return '';
    if (/^data:image\//i.test(raw)) return raw;
    if (/^https?:\/\//i.test(raw)) return raw;
    return '';
  }

  function resetClaimBtn() {
    var btn = document.getElementById('sgClaimBtn');
    if (btn) btn.style.display = 'none';
    window._sgOpenMatchedPair = null;
  }

  window.openStudentItemFromSearch = function(id) {
    if (!id) return;
    var body = document.getElementById('sgBody');
    if (body) body.innerHTML = '<p style="margin:0;padding:24px;text-align:center;color:#6b7280;"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</p>';
    resetClaimBtn();
    var overlay = document.getElementById('studentGlobalItemOverlay');
    if (overlay) {
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    fetch(STUDENT_ITEM_URL + '?id=' + encodeURIComponent(id), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function(r){ return r.json(); })
      .then(function(json){
        if (!json.ok) {
          if (body) body.innerHTML = '<p style="padding:24px;color:#b91c1c;">'+esc(json.error || 'Could not load item.')+'</p>';
          return;
        }
        var d = json.data;
        if (d.view_preset === 'matched_pair' && d.matched_pair && typeof window.openStudentMatchedCompareModal === 'function') {
          closeStudentGlobalDetail();
          window.openStudentMatchedCompareModal(d.matched_pair);
          return;
        }

        if (d.matched_pair && d.matched_pair.claimable) {
          var claimBtn = document.getElementById('sgClaimBtn');
          if (claimBtn) {
            claimBtn.style.display = '';
            window._sgOpenMatchedPair = function() {
              closeStudentGlobalDetail();
              if (typeof window.openStudentMatchedCompareModal === 'function') {
                window.openStudentMatchedCompareModal(d.matched_pair);
              }
            };
          }
        }
        var isRef = String(d.id || '').indexOf('REF-') === 0;
        var p = d.parsed || {};

        if (isRef) {
          body.className = 'item-details-body';
          var tid = d.display_ticket_id || d.id;
          var imgSrc = itemImageSrc(d.image_data);
          var leftCol = '<div class="item-details-left">';
          if (imgSrc) {
            leftCol += '<div class="item-details-image-wrap"><img class="item-details-image" src="'+imgSrc+'" alt=""></div>';
          } else {
            leftCol += '<div class="item-details-image-wrap item-details-image-placeholder"><i class="fa-regular fa-image"></i><span>No photo</span></div>';
          }
          leftCol += '<p class="item-details-barcode-id">'+esc(tid)+'</p></div>';

          var dl = '<dl class="item-details-info-list">'
            + infoRow('Category', d.item_type)
            + infoRow('Full Name', p.full_name)
            + infoRow('Contact Number', p.contact)
            + infoRow('Department', p.department)
            + infoRow('ID', p.student_number)
            + infoRow('Item', p.item)
            + infoRow('Color', d.color)
            + infoRow('Brand', d.brand)
            + infoRow('Item Description', p.clean_description)
            + infoRow('Date Lost', formatDisplayDate(d.date_lost))
            + '</dl>';

          var rightCol = '<div class="item-details-right">'
            + '<div class="item-details-section-head">'
            + '<hr class="item-details-divider" />'
            + '<h4 class="item-details-info-title">General Information</h4>'
            + '<hr class="item-details-divider" />'
            + '</div>'
            + dl
            + '</div>';

          if (body) body.innerHTML = leftCol + rightCol;
        } else {
          body.className = 'item-details-body';
          var bid = d.display_ticket_id || d.id;
          var imgSrc2 = itemImageSrc(d.image_data);
          var leftFound = '<div class="item-details-left">';
          if (imgSrc2) {
            leftFound += '<div class="item-details-image-wrap"><img class="item-details-image" src="'+imgSrc2+'" alt=""></div>';
          } else {
            leftFound += '<div class="item-details-image-wrap item-details-image-placeholder"><i class="fa-regular fa-image"></i><span>No photo</span></div>';
          }
          leftFound += '<p class="item-details-barcode-id">Barcode ID: '+esc(bid)+'</p></div>';

          var pf = d.parsed || {};
          var itemDesc = (pf.clean_description != null && pf.clean_description !== '') ? pf.clean_description : (d.item_description || '');
          var dlFound = '<dl class="item-details-info-list">'
            + infoRow('Category', d.item_type)
            + infoRow('Item', pf.item)
            + infoRow('Color', d.color)
            + infoRow('Brand', d.brand)
            + infoRow('Item Description', itemDesc)
            + infoRow('Storage Location', d.storage_location)
            + infoRow('Found At', d.found_at)
            + infoRow('Found By', d.found_by)
            + infoRow('Encoded By', d.encoded_by)
            + infoRow('Date Found', formatDisplayDate(d.date_found))
            + '</dl>';

          var rightFound = '<div class="item-details-right">'
            + '<div class="item-details-section-head">'
            + '<hr class="item-details-divider" />'
            + '<h4 class="item-details-info-title">General Information</h4>'
            + '<hr class="item-details-divider" />'
            + '</div>'
            + dlFound
            + '</div>';

          if (body) body.innerHTML = leftFound + rightFound;
        }
      })
      .catch(function(){
        if (body) body.innerHTML = '<p style="padding:24px;color:#b91c1c;">Could not load item.</p>';
      });
  };

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeStudentGlobalDetail();
  });
})();
</script>

{{-- Lost report details — two-column layout (matches Item Details design) --}}
<div id="studentLostReportViewModal" class="item-details-overlay" role="dialog" aria-modal="true" aria-hidden="true"
     onclick="if(event.target===this)closeStudentLostReportView()">
  <div class="item-details-dialog" onclick="event.stopPropagation()">
    <div class="item-details-header">
      <h3 class="item-details-title">Item Details</h3>
      <button type="button" class="item-details-close" onclick="closeStudentLostReportView()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="item-details-body" id="studentLostReportViewBody">
      <p class="slrv-loading" style="margin:0;padding:24px;text-align:center;color:#6b7280;"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</p>
    </div>
    <div class="item-details-footer">
      <button type="button" class="item-details-btn item-details-btn-cancel" onclick="closeStudentLostReportView()">Close</button>
    </div>
  </div>
</div>

<script>
(function(){
  var url = @json(route('student.item'));
  window.openStudentLostReportView = function(reportId){
    if(!reportId) return;
    var modal = document.getElementById('studentLostReportViewModal');
    var body = document.getElementById('studentLostReportViewBody');
    if(!modal || !body) return;
    body.innerHTML = '<p class="slrv-loading" style="margin:0;padding:24px;text-align:center;color:#6b7280;"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</p>';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
    fetch(url + '?id=' + encodeURIComponent(reportId), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r){ return r.json(); })
    .then(function(json){
      if(!json.ok || !json.data){
        body.innerHTML = '<p class="slrv-err" style="padding:24px;color:#b91c1c;">' + esc(json.error || 'Could not load details.') + '</p>';
        return;
      }
      var d = json.data;
      var p = d.parsed || {};
      var tid = d.display_ticket_id || d.id;
      var imgSrc = itemImageSrc(d.image_data);

      var leftCol = '<div class="item-details-left">';
      if (imgSrc) {
        leftCol += '<div class="item-details-image-wrap"><img class="item-details-image" src="'+imgSrc+'" alt=""></div>';
      } else {
        leftCol += '<div class="item-details-image-wrap item-details-image-placeholder"><i class="fa-regular fa-image"></i><span>No photo</span></div>';
      }
      leftCol += '<p class="item-details-barcode-id">'+esc(tid)+'</p></div>';

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
        var dt;
        if (iso) {
          dt = new Date(+iso[1], +iso[2] - 1, +iso[3]);
        } else {
          dt = new Date(s);
        }
        if (isNaN(dt.getTime())) return s;
        return (dt.getMonth()+1) + '/' + dt.getDate() + '/' + String(dt.getFullYear()).slice(-2);
      }
      function itemImageSrc(raw){
        if (!raw || typeof raw !== 'string') return '';
        if (/^data:image\//i.test(raw)) return raw;
        if (/^https?:\/\//i.test(raw)) return raw;
        return '';
      }

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

      body.innerHTML = leftCol + rightCol;
    })
    .catch(function(){
      body.innerHTML = '<p class="slrv-err" style="padding:24px;color:#b91c1c;">Could not load details.</p>';
    });
  };
  window.closeStudentLostReportView = function(){
    var modal = document.getElementById('studentLostReportViewModal');
    if(modal){
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden','true');
    }
    document.body.style.overflow = '';
  };
  function esc(s){
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape') closeStudentLostReportView();
  });
})();
</script>

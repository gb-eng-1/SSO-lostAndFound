{{-- Shared compare modal rendering (loaded on all student pages with layout) --}}
<script>
(function(){
  function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function rowScm(label, val){
    val = (val==null||val==='') ? '—' : val;
    return '<div class="scm-row"><span class="scm-label">'+esc(label)+'</span><span class="scm-val">'+esc(String(val))+'</span></div>';
  }
  window.renderComparePanelsFromPair = function(pair){
    var f = pair.found, l = pair.lost;
    if (!f || !l) return '<p style="padding:16px;color:#6b7280;">No match details.</p>';
    var top = '<div class="scm-panel"><p class="scm-panel-title">General Information</p>'
      + rowScm('Category', f.category)
      + rowScm('Item', f.item)
      + rowScm('Color', f.color)
      + rowScm('Brand', f.brand)
      + rowScm(f.date_key || 'Date Found', f.date)
      + '</div>';
    var bot = '<div class="scm-panel"><p class="scm-panel-title">General Information</p>'
      + rowScm('Category', l.category)
      + rowScm('Item', l.item)
      + rowScm('Color', l.color)
      + rowScm('Brand', l.brand)
      + rowScm(l.date_key || 'Date Lost', l.date)
      + '</div>';
    return top + bot;
  };
  window.closeStudentCompareModal = function(){
    var modal = document.getElementById('studentCompareModal');
    if (modal) { modal.classList.remove('open'); document.body.style.overflow = ''; }
  };
  window.openStudentMatchedCompareModal = function(pair){
    if (!pair) return;
    var body = document.getElementById('studentCompareBody');
    var modal = document.getElementById('studentCompareModal');
    var h2 = modal && modal.querySelector('.scm-header h2');
    if (h2) h2.textContent = 'Matched item';
    if (body && typeof window.renderComparePanelsFromPair === 'function') {
      body.innerHTML = window.renderComparePanelsFromPair(pair);
    }
    if (typeof window.configureScmClaimButtonForPair === 'function') {
      window.configureScmClaimButtonForPair(pair);
    }
    if (modal) { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
})();
</script>

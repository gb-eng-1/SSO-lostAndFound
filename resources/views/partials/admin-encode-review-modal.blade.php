{{-- Shared review + consent step before admin encode POSTs (found-items flows) --}}
<div id="adminEncodeReviewModal" class="report-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="adminEncodeReviewTitle"
     onclick="if(event.target===this&&typeof closeAdminEncodeReviewModal==='function')closeAdminEncodeReviewModal()">
  <div class="report-modal" onclick="event.stopPropagation()" style="max-width:min(560px,96vw);">
    <div class="report-modal-header">
      <h2 class="report-modal-title" id="adminEncodeReviewTitle">Review</h2>
      <button type="button" class="report-modal-close" onclick="closeAdminEncodeReviewModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="report-modal-body">
      <p style="margin:0 0 12px;font-size:13px;color:#6b7280;">Please review your details before submitting.</p>
      <div id="adminEncodeReviewSummary"></div>
      <div class="report-form-row" style="margin-top:16px;padding-top:14px;border-top:1px solid #e5e7eb;">
        <label style="display:flex;gap:10px;align-items:flex-start;cursor:pointer;font-size:14px;line-height:1.45;">
          <input type="checkbox" id="adminEncodeReviewConsent" style="margin-top:3px;width:18px;height:18px;flex-shrink:0;">
          <span>I hereby authorize that the above details are accurate and correct.</span>
        </label>
      </div>
    </div>
    <div class="report-modal-footer">
      <button type="button" class="report-btn-cancel" id="adminEncodeReviewBack">Back</button>
      <button type="button" class="report-btn-confirm" id="adminEncodeReviewSubmit">Submit</button>
    </div>
  </div>
</div>

<script>
(function(){
  window._adminEncodeReview = { runSubmit: null, onBack: null };
  window.closeAdminEncodeReviewModal = function(){
    var el = document.getElementById('adminEncodeReviewModal');
    if(el) el.classList.remove('report-modal-open');
  };
  window.openAdminEncodeReviewModal = function(){
    var el = document.getElementById('adminEncodeReviewModal');
    if(el){
      el.classList.add('report-modal-open');
      document.body.style.overflow = 'hidden';
    }
    var c = document.getElementById('adminEncodeReviewConsent');
    if(c) c.checked = false;
  };
  var sub = document.getElementById('adminEncodeReviewSubmit');
  var back = document.getElementById('adminEncodeReviewBack');
  if(sub) sub.addEventListener('click', function(){
    var c = document.getElementById('adminEncodeReviewConsent');
    if(!c || !c.checked){
      if(typeof window.appUiAlert === 'function') window.appUiAlert('Please confirm that the above details are accurate and correct.');
      else alert('Please confirm that the above details are accurate and correct.');
      return;
    }
    var fn = window._adminEncodeReview && window._adminEncodeReview.runSubmit;
    if(typeof fn === 'function') fn();
  });
  if(back) back.addEventListener('click', function(){
    closeAdminEncodeReviewModal();
    var ob = window._adminEncodeReview && window._adminEncodeReview.onBack;
    if(typeof ob === 'function') ob();
  });
})();
</script>

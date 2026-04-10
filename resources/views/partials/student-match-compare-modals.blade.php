{{-- Compare matched items (Found top, Lost bottom) — no images --}}
<div id="studentCompareModal" class="scm-overlay" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeStudentCompareModal()">
  <div class="scm-modal" onclick="event.stopPropagation()">
    <div class="scm-header">
      <h2>Matched item</h2>
      <button type="button" class="srm-close" style="background:rgba(255,255,255,.2);" onclick="closeStudentCompareModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="scm-body" id="studentCompareBody"></div>
    <div class="scm-footer">
      <button type="button" class="srm-btn-cancel" onclick="closeStudentCompareModal()">Close</button>
      <button type="button" id="scmClaimBtn" class="scm-btn-claim" style="display:none;" aria-hidden="true">Claim</button>
    </div>
  </div>
</div>

{{-- Claim success --}}
<div id="studentClaimSuccessModal" class="ssm-overlay" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeStudentSuccessModal()">
  <div class="ssm-modal" onclick="event.stopPropagation()">
    <button type="button" class="ssm-close" onclick="closeStudentSuccessModal()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <div class="ssm-icon"><i class="fa-solid fa-check"></i></div>
    <h3>Success</h3>
    <p id="studentSuccessMessage">Your claim request has been submitted.</p>
    <div style="margin-top:18px;text-align:right;">
      <button type="button" class="srm-btn-cancel" onclick="closeStudentSuccessModal()">Close</button>
    </div>
  </div>
</div>

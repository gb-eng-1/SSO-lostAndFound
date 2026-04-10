{{-- Global in-app modals (replaces alert/confirm) — app-ui-modals.js + app-ui-modals.css --}}
<div id="appUiAlertModal" class="aui-overlay" role="alertdialog" aria-modal="true" aria-labelledby="appUiAlertTitle">
  <div class="aui-dialog" onclick="event.stopPropagation()" role="document">
    <div class="aui-dialog-hdr" id="appUiAlertTitle">Notice</div>
    <div class="aui-dialog-body" id="appUiAlertBody"></div>
    <div class="aui-dialog-foot">
      <button type="button" class="aui-btn aui-btn--primary" id="appUiAlertOk">OK</button>
    </div>
  </div>
</div>

<div id="appUiConfirmModal" class="aui-overlay" role="dialog" aria-modal="true" aria-labelledby="appUiConfirmBody">
  <div class="aui-dialog" onclick="event.stopPropagation()">
    <div class="aui-dialog-body" id="appUiConfirmBody" style="padding-top:22px;"></div>
    <div class="aui-dialog-foot">
      <button type="button" class="aui-btn" id="appUiConfirmCancel">Cancel</button>
      <button type="button" class="aui-btn aui-btn--primary" id="appUiConfirmOk">OK</button>
    </div>
  </div>
</div>

<div id="appUiSuccessModal" class="aui-overlay" role="dialog" aria-modal="true" aria-labelledby="appUiSuccessTitle">
  <div class="aui-dialog aui-dialog--success" onclick="event.stopPropagation()">
    <button type="button" class="aui-success-close" id="appUiSuccessClose" aria-label="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="aui-success-icon-wrap" aria-hidden="true"><i class="fa-solid fa-check"></i></div>
    <h2 class="aui-success-title" id="appUiSuccessTitle">Success</h2>
    <p class="aui-success-msg" id="appUiSuccessMessage">Report has been submitted successfully!</p>
    <p class="aui-success-ticket" id="appUiSuccessTicket" style="display:none;"></p>
    <div class="aui-success-foot">
      <button type="button" class="aui-btn" id="appUiSuccessDismiss">Cancel</button>
    </div>
  </div>
</div>

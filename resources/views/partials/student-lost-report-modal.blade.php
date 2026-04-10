{{-- Item Lost Report — maroon header, DB: POST /student/reports --}}
<div id="reportModal" class="srm-overlay" role="dialog" aria-modal="true" aria-labelledby="srmTitle"
     onclick="if(event.target===this)closeReportModal()">
  <div class="srm-modal" onclick="event.stopPropagation()">
    <div class="srm-header">
      <h2 id="srmTitle">Item Lost Report</h2>
      <button type="button" class="srm-close" onclick="closeReportModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="srm-body">
      <form id="reportForm">
        @csrf
        @include('partials.lost-report-form-fields', ['variant' => 'student'])
        <div class="srm-footer" style="border-top:1px solid #f3f4f6;margin:8px -22px 0;padding:14px 22px 0;">
          <button type="button" class="srm-btn-cancel" onclick="closeReportModal()">Cancel</button>
          <button type="submit" class="srm-btn-next" id="reportFormSubmitBtn">Continue</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Review + consent (read-only) before POST --}}
<div id="studentLostReportReviewModal" class="srm-overlay" role="dialog" aria-modal="true" aria-labelledby="studentLostReportReviewTitle"
     onclick="if(event.target===this)closeStudentLostReportReviewModal()">
  <div class="srm-modal" onclick="event.stopPropagation()" style="max-width:min(560px,96vw);">
    <div class="srm-header">
      <h2 id="studentLostReportReviewTitle">Item Lost Report</h2>
      <button type="button" class="srm-close" onclick="closeStudentLostReportReviewModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="srm-body">
      <p style="margin:0 0 14px;font-size:13px;color:#6b7280;">Please review your details before submitting.</p>
      <div id="studentLostReportReviewSummary" class="student-lost-report-review-summary"></div>
      <div class="srm-form-row" style="align-items:flex-start;margin-top:16px;border-top:1px solid #f3f4f6;padding-top:14px;">
        <label style="display:flex;gap:10px;align-items:flex-start;cursor:pointer;font-size:14px;line-height:1.45;">
          <input type="checkbox" id="studentLostReportReviewConsent" style="margin-top:3px;width:18px;height:18px;flex-shrink:0;">
          <span>I hereby authorize that the above details are accurate and correct.</span>
        </label>
      </div>
      <div class="srm-footer" style="border-top:1px solid #f3f4f6;margin:16px -22px 0;padding:14px 22px 0;">
        <button type="button" class="srm-btn-cancel" id="studentLostReportReviewBack">Back</button>
        <button type="button" class="srm-btn-next" id="studentLostReportReviewSubmit">Submit</button>
      </div>
    </div>
  </div>
</div>

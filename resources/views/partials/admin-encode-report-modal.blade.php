{{-- Admin lost report on behalf of student (POST admin.found.lost-report) --}}
<div class="report-modal-overlay" id="encodeReportModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this&&typeof closeEncodeReportModal==='function')closeEncodeReportModal()">
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

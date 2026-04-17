{{-- Barcode already exists / linked reports (preflight) --}}
<div class="cancel-confirm-overlay" id="barcodeDupModal" role="dialog" aria-modal="true" aria-hidden="true"
     onclick="if(event.target===this)closeBarcodeDupModal()">
  <div class="cancel-confirm-dialog" onclick="event.stopPropagation()">
    <h3 class="cancel-confirm-hdr">Cannot use this Barcode ID</h3>
    <div class="cancel-confirm-body">
      <p style="margin:0;font-size:14px;line-height:1.5;" id="barcodeDupModalText"></p>
    </div>
    <div class="cancel-confirm-foot">
      <button type="button" class="cancel-confirm-btn cancel-confirm-btn--secondary" id="barcodeDupModalOk">Go back</button>
    </div>
  </div>
</div>

{{-- Encode Internal Item (Item Recovered Report) — shared Dashboard + Found Items --}}
<div class="report-modal-overlay" id="itemLostReportModal" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeEncodeModal()">
  <div class="report-modal" onclick="event.stopPropagation()">
    <div class="report-modal-header">
      <h2 class="report-modal-title">Item Recovered Report</h2>
      <button type="button" class="report-modal-close" onclick="closeEncodeModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="encodeItemForm" class="report-modal-body">
      <div id="encodeModalErrorBanner" class="encode-modal-error" role="alert" style="display:none;margin:0 0 12px;padding:10px 14px;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;border-radius:8px;font-size:13px;"></div>
      <div class="report-form-row">
        <label class="report-form-label" for="encBarcodeId">Barcode ID (eg: UB1005):</label>
        <div class="report-form-field">
          <input type="text" id="encBarcodeId" name="barcode_id" class="report-input" placeholder="Required — e.g. UB1005" required autocomplete="off">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encCategory">Category:</label>
        <div class="report-form-field">
          <select id="encCategory" name="category" class="report-input report-select">
            <option value="">— Select Category —</option>
            @foreach(['Electronics & Gadgets','Document & Identification','Personal Belongings','Apparel & Accessories','Miscellaneous'] as $cat)
              <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encItem">Item <span class="report-required">*</span></label>
        <div class="report-form-field">
          <input type="text" id="encItem" name="item" class="report-input" placeholder="e.g. Umbrella, Water Bottle" required>
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encColor">Color: <span class="report-required">*</span></label>
        <div class="report-form-field">
          <select id="encColor" name="color" class="report-input report-select" required>
            <option value="">— Select —</option>
            @foreach(['Red','Orange','Yellow','Green','Blue','Violet','Black','White','Brown','Rainbow','Multi','Other'] as $c)
              <option>{{ $c }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encBrand">Brand:</label>
        <div class="report-form-field">
          <input type="text" id="encBrand" name="brand" class="report-input">
        </div>
      </div>
      <div class="report-form-row report-form-row-textarea">
        <label class="report-form-label" for="encDescription">Item Description <span class="report-required">*</span></label>
        <div class="report-form-field">
          <textarea id="encDescription" name="item_description" class="report-input report-textarea" rows="3" placeholder="e.g. item has a small dent" required></textarea>
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encFoundAt">Found At:</label>
        <div class="report-form-field">
          <select id="encFoundAt" name="found_at" class="report-input report-select">
            <option value="">— Select —</option>
            @foreach($campusLocations as $loc)
              <option value="{{ $loc }}">{{ $loc }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="report-form-row" id="encFoundInRow" style="display:none;">
        <label class="report-form-label" for="encFoundIn">Found In:</label>
        <div class="report-form-field">
          <input type="text" id="encFoundIn" name="found_in" class="report-input" placeholder="e.g. Room 201, lobby, parking area" maxlength="160" autocomplete="off">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encFoundBy">Found By:</label>
        <div class="report-form-field">
          <input type="text" id="encFoundBy" name="found_by" class="report-input" placeholder="e.g. juan.delacruz@ub.edu.ph or Juan Dela Cruz">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encDateFound">Date Found:</label>
        <div class="report-form-field">
          <input type="date" id="encDateFound" name="date_found" class="report-input" max="{{ date('Y-m-d') }}">
        </div>
      </div>
      <div class="report-form-row">
        <label class="report-form-label" for="encStorage">Storage Location:</label>
        <div class="report-form-field">
          <input type="text" id="encStorage" name="storage_location" class="report-input" placeholder="e.g. Shelf A-1">
        </div>
      </div>
      <div class="report-form-row pp-photo-row">
        <label class="report-form-label">Photo:</label>
        <div class="report-form-field">
          <div class="pp-wrap" id="encodeItemPhotoPicker">
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
        <button type="button" class="report-btn-cancel" onclick="closeEncodeModal()">Cancel</button>
        <button type="button" class="report-btn-confirm" id="encodeItemSubmitBtn">Next</button>
      </div>
    </form>
  </div>
</div>

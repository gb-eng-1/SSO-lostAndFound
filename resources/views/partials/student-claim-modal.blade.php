{{-- Submit Claim — POST /student/claim --}}
<div id="claimSubmitOverlay" class="scm-claim-overlay"
     style="display:none;position:fixed;inset:0;z-index:1500;background:rgba(0,0,0,.5);align-items:center;justify-content:center;"
     onclick="if(event.target===this)closeClaimModal()">
  <div style="background:#fff;border-radius:12px;width:min(480px,96vw);max-height:90vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.24);display:flex;flex-direction:column;" onclick="event.stopPropagation()">
    <div style="background:#8b0000;border-radius:12px 12px 0 0;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
      <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0;">Submit Claim</h3>
      <button type="button" onclick="closeClaimModal()" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:2px 6px;opacity:.85;line-height:1;"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="padding:20px 24px 8px;">
      <div id="claimItemSummary" style="padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:16px;font-size:13px;color:#374151;"></div>
      <div style="margin-bottom:12px;">
        <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:5px;">Proof / Description <span style="color:#dc2626;">*</span></label>
        <textarea id="claimProofDesc" rows="3" style="width:100%;box-sizing:border-box;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-family:Poppins,sans-serif;font-size:12px;resize:vertical;" placeholder="Describe how you can prove this is your item"></textarea>
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:5px;">Proof Photo <span style="color:#dc2626;">*</span></label>
        <div class="pp-wrap" id="claimPhotoPicker">
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
        <p id="claimPhotoErr" style="display:none;font-size:11px;color:#dc2626;margin-top:4px;">A proof photo is required.</p>
        <p id="claimDescErr" style="display:none;font-size:11px;color:#dc2626;margin-top:2px;">Please provide a proof description.</p>
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 24px 20px;">
      <button type="button" onclick="closeClaimModal()" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:7px;background:#fff;color:#374151;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
      <button type="button" id="claimSubmitBtn" style="padding:8px 20px;border:none;border-radius:7px;background:#8b0000;color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;">Submit Claim</button>
    </div>
  </div>
</div>

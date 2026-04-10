{{-- FOUND ITEM PROTOCOL --}}
<div id="foundProtocolModal" class="spm-overlay" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeFoundProtocolModal()">
  <div class="spm-modal" onclick="event.stopPropagation()">
    <div class="spm-header">
      <h2>FOUND ITEM PROTOCOL</h2>
      <button type="button" class="srm-close" style="background:rgba(255,255,255,.2);" onclick="closeFoundProtocolModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="spm-illus">
      <span style="font-size:64px;color:#ca8a04;" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
      <p style="margin:8px 0 0;font-size:12px;color:#6b7280;">Document the item and bring it to security.</p>
    </div>
    <div class="spm-steps">
      <div class="spm-step">
        <div class="spm-step-icon"><i class="fa-solid fa-box-open"></i></div>
        <div class="spm-step-body">
          <h3>1. DO NOT TAMPER</h3>
          <p>Do not open, inspect, or alter in any way.</p>
        </div>
      </div>
      <div class="spm-step">
        <div class="spm-step-icon"><i class="fa-solid fa-camera"></i></div>
        <div class="spm-step-body">
          <h3>2. BRING TO OFFICE</h3>
          <p>The item should be turned over to the security to be encoded as lost item.</p>
        </div>
      </div>
      <div class="spm-step">
        <div class="spm-step-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div class="spm-step-body">
          <h3>3. SECURITY ENCODES</h3>
          <p>Security will log item details into UB Lost &amp; Found System.</p>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- CLAIM ITEM PROTOCOL --}}
<div id="claimProtocolModal" class="spm-overlay" role="dialog" aria-modal="true"
     onclick="if(event.target===this)closeClaimProtocolModal()">
  <div class="spm-modal" onclick="event.stopPropagation()">
    <div class="spm-header">
      <h2>CLAIM ITEM PROTOCOL</h2>
      <button type="button" class="srm-close" style="background:rgba(255,255,255,.2);" onclick="closeClaimProtocolModal()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="spm-illus">
      <span style="font-size:64px;color:#ca8a04;" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
      <p style="margin:8px 0 0;font-size:12px;color:#6b7280;">Follow these steps to reclaim your property.</p>
    </div>
    <div class="spm-steps">
      <div class="spm-step">
        <div class="spm-step-icon"><i class="fa-solid fa-id-card"></i></div>
        <div class="spm-step-body">
          <h3>1. REPORT LOST ITEM</h3>
          <p>Once you have reported a lost item, a unique Ticket ID will be generated.</p>
        </div>
      </div>
      <div class="spm-step">
        <div class="spm-step-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <div class="spm-step-body">
          <h3>2. BROWSE &amp; LOCATE</h3>
          <p>Visit the Browse Items in UB Lost &amp; Found and check for potential matches.</p>
        </div>
      </div>
      <div class="spm-step">
        <div class="spm-step-icon"><i class="fa-solid fa-clipboard-check"></i></div>
        <div class="spm-step-body">
          <h3>3. RECLAIM &amp; VERIFY</h3>
          <p>Once verified, sign the logbook and retrieve your item.</p>
        </div>
      </div>
    </div>
  </div>
</div>

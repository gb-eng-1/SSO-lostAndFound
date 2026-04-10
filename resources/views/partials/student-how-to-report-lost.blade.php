@php
  $embedded = $embedded ?? false;
@endphp
@if(! $embedded)
<div class="hs-card">
  <h2 class="hs-card-title">How to Report a Lost Item</h2>
@endif
  <ul class="hs-steps{{ $embedded ? ' hs-steps--compact' : '' }}">
    <li class="hs-step">
      <div class="hs-step-icon" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
      <div class="hs-step-body">
        <p><strong>Step 1: Log in to the Dashboard.</strong></p>
        <p>Access the system using your official student credentials.</p>
      </div>
    </li>
    <li class="hs-step">
      <div class="hs-step-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></div>
      <div class="hs-step-body">
        <p><strong>Step 2: Select &quot;Report Lost Item&quot;.</strong></p>
        <p>Click this button to open the reporting form and fill out item details.</p>
      </div>
    </li>
    <li class="hs-step">
      <div class="hs-step-icon" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></div>
      <div class="hs-step-body">
        <p><strong>Step 3: Upload a Photo.</strong></p>
        <p>Attach a picture of your missing item or a similar reference image to help the system&rsquo;s matching process.</p>
      </div>
    </li>
    <li class="hs-step">
      <div class="hs-step-icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></div>
      <div class="hs-step-body">
        <p><strong>Step 4: Submit the Report.</strong></p>
        <p>After verifying all details, click Submit.</p>
      </div>
    </li>
    <li class="hs-step">
      <div class="hs-step-icon" aria-hidden="true"><i class="fa-solid fa-hand"></i></div>
      <div class="hs-step-body">
        <p><strong>Step 5: Receive a Ticket ID.</strong></p>
        <p>The system will generate a unique Ticket ID (e.g., <span class="hs-highlight">TIC-xxxxxxxxxx</span>), which serves as your reference for claiming the item at the Security Office.</p>
      </div>
    </li>
  </ul>
@if(! $embedded)
</div>
@endif

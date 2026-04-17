{{--
  Shared lost-report fields for student modal and admin encode report.
  @param string $variant 'student' | 'admin'
  @param iterable|null $categories Category options (admin passes $categoriesInternal; student uses default list)
  @param bool $showStudentEmail Admin-only row (default true when variant is admin)
--}}
@php
  $isAdmin = ($variant ?? 'student') === 'admin';
  $categoriesList = $categories ?? [
    'Electronics & Gadgets',
    'Document & Identification',
    'Personal Belongings',
    'Apparel & Accessories',
    'Miscellaneous',
  ];
  $idCat = $isAdmin ? 'repCategory' : 'reportCategory';
  $idDocRow = $isAdmin ? 'repDocTypeRow' : 'reportDocTypeRow';
  $idDocType = $isAdmin ? 'repDocType' : 'reportDocType';
  $idFull = $isAdmin ? 'repFullName' : 'srmFullName';
  $idContact = $isAdmin ? 'repContact' : 'srmContact';
  $idDept = $isAdmin ? 'repDept' : 'srmDept';
  $idSid = $isAdmin ? 'repId' : 'srmSid';
  $idItem = $isAdmin ? 'repItem' : 'srmItem';
  $idDesc = $isAdmin ? 'repDesc' : 'srmDesc';
  $idElecHint = $isAdmin ? 'repElecHint' : 'srmElecHint';
  $idColor = $isAdmin ? 'repColor' : 'srmColor';
  $idBrand = $isAdmin ? 'repBrand' : 'srmBrand';
  $idItemRow = $isAdmin ? 'repItemRow' : 'srmItemRow';
  $idBrandRow = $isAdmin ? 'repBrandRow' : 'srmBrandRow';
  $idItemLabel = $isAdmin ? 'repItemLabel' : 'srmItemLabel';
  $idDateLost = $isAdmin ? 'repDateLost' : 'srmDateLost';
  $idPhotoPicker = $isAdmin ? 'encodeReportPhotoPicker' : 'reportPhotoPicker';
  $formControlClass = $isAdmin ? 'report-input' : 'form-control';
  $formRowClass = $isAdmin ? 'report-form-row' : 'srm-form-row';
  $labelClass = $isAdmin ? 'report-form-label' : null;
  $fieldWrapClass = $isAdmin ? 'report-form-field' : null;
  $showEmail = $isAdmin && ($showStudentEmail ?? true);
  $documentTypeOptions = \App\Http\Controllers\Student\LostReportController::DOCUMENT_TYPES;

  $studentIdAutofill = '';
  if (! $isAdmin) {
    $sessEmail = session('student_email');
    if (is_string($sessEmail) && str_contains($sessEmail, '@')) {
      $studentIdAutofill = trim(explode('@', $sessEmail, 2)[0]);
    }
    if ($studentIdAutofill === '' && isset($studentNumber) && $studentNumber !== null && $studentNumber !== '') {
      $studentIdAutofill = (string) $studentNumber;
    }
  }
@endphp

@if($showEmail)
  <div class="report-form-row">
    <label class="report-form-label" for="repStudentEmail">Student Email (UB) <span class="report-required">*</span></label>
    <div class="report-form-field">
      <input type="email" id="repStudentEmail" name="student_email" class="report-input" placeholder="e.g. 200981@ub.edu.ph" required>
    </div>
  </div>
@endif

{{-- Admin: Category is first; Student: Category comes after ID --}}
@if($isAdmin)
<div class="{{ $formRowClass }}">
  <label class="{{ $labelClass }}" for="{{ $idCat }}">Category</label>
  <div class="{{ $fieldWrapClass }}">
    <select id="{{ $idCat }}" name="category" class="report-input report-select">
      <option value="">— Select —</option>
      @foreach($categoriesList as $cat)
        <option value="{{ $cat }}">{{ $cat }}</option>
      @endforeach
    </select>
  </div>
</div>

<div id="{{ $idDocRow }}" style="display:none;" class="{{ $formRowClass }}">
  <label class="{{ $labelClass }}" for="{{ $idDocType }}">Document Type</label>
  <div class="{{ $fieldWrapClass }}">
    <select id="{{ $idDocType }}" name="document_type" class="report-input report-select">
      <option value="">— Select Document Type —</option>
      @foreach($documentTypeOptions as $docType)
        <option value="{{ $docType }}">{{ $docType }}</option>
      @endforeach
    </select>
  </div>
</div>
@endif

<div class="{{ $formRowClass }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idFull }}">Full Name (Last Name, First Name)</label>
    <div class="{{ $fieldWrapClass }}">
      <input type="text" id="{{ $idFull }}" name="full_name" class="report-input" placeholder="Your full name">
    </div>
  @else
    <label for="{{ $idFull }}">Full Name (Last Name, First Name)</label>
    <input type="text" id="{{ $idFull }}" name="full_name" class="{{ $formControlClass }}" placeholder="Your full name" value="{{ $studentName ?? '' }}">
  @endif
</div>

<div class="{{ $formRowClass }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idContact }}">Contact Number (eg: 09*********) <span class="report-required">*</span></label>
    <div class="{{ $fieldWrapClass }}">
      <input type="text" id="{{ $idContact }}" name="contact_number" class="report-input" required placeholder="09*********">
    </div>
  @else
    <label for="{{ $idContact }}">Contact Number (eg: 09*********) <span class="srm-req">*</span></label>
    <input type="text" id="{{ $idContact }}" name="contact_number" class="{{ $formControlClass }}" required placeholder="09*********">
  @endif
</div>

<div class="{{ $formRowClass }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idDept }}">Department <span class="report-required">*</span></label>
    <div class="{{ $fieldWrapClass }}">
      <input type="text" id="{{ $idDept }}" name="department" class="report-input" required>
    </div>
  @else
    <label for="{{ $idDept }}">Department <span class="srm-req">*</span></label>
    <input type="text" id="{{ $idDept }}" name="department" class="{{ $formControlClass }}" required>
  @endif
</div>

<div class="{{ $formRowClass }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idSid }}">ID (eg: 2230653)</label>
    <div class="{{ $fieldWrapClass }}">
      <input type="text" id="{{ $idSid }}" name="id" class="report-input" placeholder="Student ID number">
    </div>
  @else
    <label for="{{ $idSid }}">ID (eg: 2230653)</label>
    <input type="text" id="{{ $idSid }}" name="id" class="{{ $formControlClass }}" placeholder="Student ID" value="{{ $studentIdAutofill }}">
  @endif
</div>

{{-- Student: Category + Document Type come after ID --}}
@if(!$isAdmin)
<hr class="form-section-divider">
<div class="{{ $formRowClass }}">
  <label for="{{ $idCat }}">Category</label>
  <select id="{{ $idCat }}" name="category" class="{{ $formControlClass }}">
    <option value="">Select Category</option>
    @foreach($categoriesList as $cat)
      <option value="{{ $cat }}">{{ $cat }}</option>
    @endforeach
  </select>
</div>

<div id="{{ $idDocRow }}" style="display:none;" class="{{ $formRowClass }}">
  <label for="{{ $idDocType }}">Document Type</label>
  <select id="{{ $idDocType }}" name="document_type" class="{{ $formControlClass }}">
    <option value="">Select Document Type</option>
    @foreach($documentTypeOptions as $docType)
      <option value="{{ $docType }}">{{ $docType }}</option>
    @endforeach
  </select>
</div>
@else
<hr class="form-section-divider">
@endif

<div class="{{ $formRowClass }}" id="{{ $idItemRow }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idItem }}" id="{{ $idItemLabel }}">Item</label>
    <div class="{{ $fieldWrapClass }}">
      <input type="text" id="{{ $idItem }}" name="item" class="report-input" placeholder="Name of the lost object">
    </div>
  @else
    <label for="{{ $idItem }}" id="{{ $idItemLabel }}">Item</label>
    <input type="text" id="{{ $idItem }}" name="item" class="{{ $formControlClass }}" placeholder="Name of the lost object">
  @endif
</div>

<div class="{{ $formRowClass }}" style="align-items: flex-start;">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idDesc }}">Item Description <span class="report-required">*</span></label>
    <div class="{{ $fieldWrapClass }}">
      <textarea id="{{ $idDesc }}" name="item_description" class="report-input report-textarea" rows="3" required placeholder="e.g. item has scratch"></textarea>
    </div>
  @else
    <label for="{{ $idDesc }}" style="padding-top: 8px;">Item Description <span class="srm-req">*</span></label>
    <textarea id="{{ $idDesc }}" name="item_description" class="{{ $formControlClass }}" rows="3" required placeholder="e.g. item has scratch"></textarea>
  @endif
</div>

@if($isAdmin)
<p id="{{ $idElecHint }}" style="display:none;margin:-6px 0 8px;padding:6px 12px;font-size:12px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;line-height:1.4;">
  <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
  Please specify the device model, brand, and any distinguishing features (e.g. case color, stickers, scratches) so similar-looking gadgets can be differentiated.
</p>
@else
<div class="{{ $formRowClass }}" id="{{ $idElecHint }}" style="display:none;margin-top:-6px;margin-bottom:8px;">
  <span></span>
  <p style="margin:0;padding:6px 12px;font-size:12px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;line-height:1.4;">
    <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
    Please specify the device model, brand, and any distinguishing features (e.g. case color, stickers, scratches) so similar-looking gadgets can be differentiated.
  </p>
</div>
@endif

<div class="{{ $formRowClass }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idColor }}">Color</label>
    <div class="{{ $fieldWrapClass }}">
      <select id="{{ $idColor }}" name="color" class="report-input report-select">
        <option value="">— Select —</option>
        @foreach(['Red','Orange','Yellow','Green','Blue','Violet','Black','White','Brown','Rainbow','Multi','Other'] as $color)
          <option value="{{ $color }}">{{ $color }}</option>
        @endforeach
      </select>
    </div>
  @else
    <label for="{{ $idColor }}">Color</label>
    <select id="{{ $idColor }}" name="color" class="{{ $formControlClass }}">
      <option value="">Select Color</option>
      @foreach(['Red','Orange','Yellow','Green','Blue','Violet','Black','White','Brown','Rainbow','Multi','Other'] as $color)
        <option value="{{ $color }}">{{ $color }}</option>
      @endforeach
    </select>
  @endif
</div>

<div class="{{ $formRowClass }}" id="{{ $idBrandRow }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idBrand }}">Brand</label>
    <div class="{{ $fieldWrapClass }}">
      <input type="text" id="{{ $idBrand }}" name="brand" class="report-input">
    </div>
  @else
    <label for="{{ $idBrand }}">Brand</label>
    <input type="text" id="{{ $idBrand }}" name="brand" class="{{ $formControlClass }}">
  @endif
</div>

<div class="{{ $formRowClass }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}" for="{{ $idDateLost }}">Date Lost</label>
    <div class="{{ $fieldWrapClass }}">
      <input type="date" id="{{ $idDateLost }}" name="date_lost" class="report-input" max="{{ date('Y-m-d') }}">
    </div>
  @else
    <label for="{{ $idDateLost }}">Date Lost</label>
    <input type="date" id="{{ $idDateLost }}" name="date_lost" class="{{ $formControlClass }}" max="{{ date('Y-m-d') }}">
  @endif
</div>

<div class="{{ $formRowClass }}{{ $isAdmin ? ' report-form-row-textarea pp-photo-row' : '' }}" style="{{ !$isAdmin ? 'align-items: flex-start;' : '' }}">
  @if($isAdmin)
    <label class="{{ $labelClass }}">Upload Image</label>
    <div class="{{ $fieldWrapClass }}">
  @else
    <label style="padding-top: 8px;">Upload Image</label>
    <div style="flex:1;">
  @endif
    <div class="pp-wrap" id="{{ $idPhotoPicker }}">
      <div class="pp-idle">
        <i class="fa-regular fa-image pp-icon"></i>
        <p class="pp-hint">{{ $isAdmin ? 'No photo yet' : 'Optional — add a photo of the item' }}</p>
        <div class="pp-btn-row">
          @if($isAdmin)
            <button type="button" class="pp-btn pp-btn--cam" data-pp="camera"><i class="fa-solid fa-camera"></i> Camera</button>
          @endif
          <button type="button" class="pp-btn pp-btn--upload" data-pp="upload"><i class="fa-solid fa-upload"></i> Upload</button>
        </div>
      </div>
      <div class="pp-preview" style="display:none">
        <img class="pp-preview-img" src="" alt="Photo preview">
        <div class="pp-preview-actions">
          @if($isAdmin)
            <button type="button" class="pp-btn pp-btn--sm" data-pp="camera"><i class="fa-solid fa-camera"></i> Retake</button>
          @endif
          <button type="button" class="pp-btn pp-btn--sm" data-pp="upload"><i class="fa-solid fa-upload"></i> Change</button>
          <button type="button" class="pp-btn pp-btn--del" data-pp="remove"><i class="fa-solid fa-xmark"></i></button>
        </div>
      </div>
      <input type="file" class="pp-file" accept="image/*" style="display:none">
    </div>
  @if($isAdmin)
    </div>
  @else
    </div>
  @endif
</div>

@php
  $pairsPayload = $matchedPairsPayload ?? [];
@endphp
<script>
(function(){
  window.STUDENT_MATCH_PAIRS = @json($pairsPayload);
  var _CSRF = document.querySelector('meta[name="csrf-token"]').content;
  var _JSON_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': _CSRF
  };
  function parseLaravelFetchResponse(r){
    return r.text().then(function(text){
      var data = null;
      if(text){ try { data = JSON.parse(text); } catch(e) {} }
      return { ok: r.ok, status: r.status, data: data };
    });
  }
  function laravelErrorMessage(res){
    var d = res.data;
    if(d && typeof d === 'object'){
      if(d.message) return d.message;
      if(d.errors){
        for(var k in d.errors){
          var arr = d.errors[k];
          if(arr && arr.length) return arr[0];
        }
      }
      if(d.error) return d.error;
    }
    if(res.status === 419) return 'Page expired. Refresh and try again.';
    return 'Request failed (' + res.status + ').';
  }
  var _claimFoundId  = null;
  var _claimLostId   = null;
  var _claimPhoto    = null;
  var _reportImage   = null;
  var _pendingLostReportPayload = null;
  var _currentPairIndex = null;

  var _claimPP = typeof PhotoPicker !== 'undefined' ? PhotoPicker.init({
    el: 'claimPhotoPicker',
    onChange: function(d){ _claimPhoto = d || null; }
  }) : null;

  var _reportPP = typeof PhotoPicker !== 'undefined' ? PhotoPicker.init({
    el: 'reportPhotoPicker',
    onChange: function(d){ _reportImage = d || null; }
  }) : null;

  window.openReportModal = function(){
    var el = document.getElementById('reportModal');
    if(!el) return;
    var d = document.getElementById('srmDateLost');
    if(d){
      var t = new Date().toISOString().split('T')[0];
      d.setAttribute('max', t);
    }
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
  };
  window.closeReportModal = function(){
    var el = document.getElementById('reportModal');
    if(!el) return;
    el.classList.remove('open');
    document.body.style.overflow = '';
  };

  window.closeStudentLostReportReviewModal = function(){
    var el = document.getElementById('studentLostReportReviewModal');
    if(el) el.classList.remove('open');
  };

  function openStudentLostReportReviewModal(){
    var el = document.getElementById('studentLostReportReviewModal');
    if(el){
      el.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function buildLostReportReviewHtml(payload){
    var rows = [];
    function add(label, key){
      var v = payload[key];
      if(v == null || v === '') v = '—';
      rows.push('<div class="srm-form-row" style="margin-bottom:8px;"><span style="min-width:150px;font-weight:600;color:#374151;">'+esc(label)+'</span><span style="flex:1;color:#111827;">'+esc(String(v))+'</span></div>');
    }
    add('Category', 'category');
    if((payload.category || '') === 'Document & Identification') add('Document Type', 'document_type');
    add('Full Name', 'full_name');
    add('Contact Number', 'contact_number');
    add('Department', 'department');
    add('ID', 'id');
    add('Item', 'item');
    add('Item Description', 'item_description');
    add('Color', 'color');
    add('Brand', 'brand');
    add('Date Lost', 'date_lost');
    if(_reportImage){
      rows.push('<div class="srm-form-row" style="align-items:flex-start;"><span style="min-width:150px;font-weight:600;color:#374151;">Photo</span><span><img src="'+esc(_reportImage)+'" alt="" style="max-width:220px;max-height:160px;border-radius:8px;border:1px solid #e5e7eb;"></span></div>');
    }
    return rows.join('');
  }

  window.openFoundProtocolModal = function(){
    var el = document.getElementById('foundProtocolModal');
    if(el){ el.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
  window.closeFoundProtocolModal = function(){
    var el = document.getElementById('foundProtocolModal');
    if(el){ el.classList.remove('open'); document.body.style.overflow = ''; }
  };
  window.openClaimProtocolModal = function(){
    var el = document.getElementById('claimProtocolModal');
    if(el){ el.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
  window.closeClaimProtocolModal = function(){
    var el = document.getElementById('claimProtocolModal');
    if(el){ el.classList.remove('open'); document.body.style.overflow = ''; }
  };

  function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function rowScm(label, val){
    val = (val==null||val==='') ? '—' : val;
    return '<div class="scm-row"><span class="scm-label">'+esc(label)+'</span><span class="scm-val">'+esc(String(val))+'</span></div>';
  }

  function renderComparePanels(pair){
    if (typeof window.renderComparePanelsFromPair === 'function') {
      return window.renderComparePanelsFromPair(pair);
    }
    var f = pair.found, l = pair.lost;
    var top = '<div class="scm-panel"><p class="scm-panel-title">General Information</p>'
      + rowScm('Category', f.category)
      + rowScm('Item', f.item)
      + rowScm('Color', f.color)
      + rowScm('Brand', f.brand)
      + rowScm(f.date_key || 'Date Found', f.date)
      + '</div>';
    var bot = '<div class="scm-panel"><p class="scm-panel-title">General Information</p>'
      + rowScm('Category', l.category)
      + rowScm('Item', l.item)
      + rowScm('Color', l.color)
      + rowScm('Brand', l.brand)
      + rowScm(l.date_key || 'Date Lost', l.date)
      + '</div>';
    return top + bot;
  }

  function configureScmClaimButton(pair){
    var btn = document.getElementById('scmClaimBtn');
    if(!btn) return;
    btn.onclick = null;
    if(!pair || !pair.claimable){
      btn.style.display = 'none';
      btn.setAttribute('aria-hidden', 'true');
      return;
    }
    btn.style.display = '';
    btn.setAttribute('aria-hidden', 'false');
    if(pair.claim_intent_submitted){
      btn.disabled = true;
      btn.textContent = 'Claim acknowledged';
      btn.classList.add('scm-btn-claim--ack');
      return;
    }
    btn.disabled = false;
    btn.textContent = 'Claim';
    btn.classList.remove('scm-btn-claim--ack');
    btn.onclick = function(){ submitMatchedClaimIntent(pair); };
  }
  window.configureScmClaimButtonForPair = configureScmClaimButton;

  function submitMatchedClaimIntent(pair){
    var btn = document.getElementById('scmClaimBtn');
    if(btn){
      btn.disabled = true;
      btn.textContent = 'Submitting…';
    }
    fetch(@json(route('student.claim-intent')), {
      method: 'POST',
      headers: _JSON_HEADERS,
      body: JSON.stringify({
        found_item_id: pair.found_id,
        lost_report_id: pair.lost_id
      })
    }).then(parseLaravelFetchResponse).then(function(res){
      if(btn){
        btn.disabled = false;
        btn.textContent = 'Claim';
      }
      if(res.ok && res.data && res.data.ok){
        pair.claim_intent_submitted = true;
        closeStudentCompareModal();
        var msg = document.getElementById('studentSuccessMessage');
        if(msg){
          msg.innerHTML = 'Your claim has been acknowledged. Please go to the <strong>security office</strong> (lost and found) to complete the process.';
        }
        var sm = document.getElementById('studentClaimSuccessModal');
        if(sm) sm.classList.add('open');
      } else {
        if(typeof window.appUiAlert === 'function') window.appUiAlert(laravelErrorMessage(res));
        else alert(laravelErrorMessage(res));
        configureScmClaimButton(pair);
      }
    }).catch(function(){
      if(btn){
        btn.disabled = false;
        btn.textContent = 'Claim';
      }
      if(typeof window.appUiAlert === 'function') window.appUiAlert('Network error. Try again.');
      else alert('Network error. Try again.');
      configureScmClaimButton(pair);
    });
  }

  window.openStudentCompareModal = function(index){
    var pairs = window.STUDENT_MATCH_PAIRS || [];
    var pair = pairs[index];
    if(!pair) return;
    _currentPairIndex = index;
    var body = document.getElementById('studentCompareBody');
    var modal = document.getElementById('studentCompareModal');
    var h2 = modal && modal.querySelector('.scm-header h2');
    if (h2) h2.textContent = 'Matched item';
    if(body) body.innerHTML = renderComparePanels(pair);
    configureScmClaimButton(pair);
    if(modal){ modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
  window.closeStudentCompareModal = function(){
    var modal = document.getElementById('studentCompareModal');
    var scmBtn = document.getElementById('scmClaimBtn');
    if(scmBtn){
      scmBtn.style.display = 'none';
      scmBtn.onclick = null;
      scmBtn.setAttribute('aria-hidden', 'true');
    }
    if(modal){ modal.classList.remove('open'); document.body.style.overflow = ''; }
    _currentPairIndex = null;
  };

  window.openClaimModalFromPair = function(){
    var pairs = window.STUDENT_MATCH_PAIRS || [];
    var pair = _currentPairIndex != null ? pairs[_currentPairIndex] : null;
    if(!pair) return;
    closeStudentCompareModal();
    var fakeBtn = {
      getAttribute: function(n){
        if(n==='data-found-id') return pair.found_id;
        if(n==='data-lost-id') return pair.lost_id;
        if(n==='data-item-name') return pair.found && pair.found.item ? pair.found.item : 'Item';
        if(n==='data-color') return pair.found && pair.found.color ? pair.found.color : '';
        if(n==='data-storage') return pair.card_location || '';
        return '';
      }
    };
    openClaimModal(fakeBtn);
  };

  window.openClaimModal = function(btn){
    if(!btn || !btn.getAttribute) return;
    _claimFoundId = btn.getAttribute('data-found-id');
    _claimLostId  = btn.getAttribute('data-lost-id');
    _claimPhoto   = null;
    if(_claimPP) _claimPP.clear();
    var pd = document.getElementById('claimProofDesc');
    if(pd) pd.value = '';
    var pe = document.getElementById('claimPhotoErr'); if(pe) pe.style.display = 'none';
    var de = document.getElementById('claimDescErr'); if(de) de.style.display = 'none';
    var name    = btn.getAttribute('data-item-name') || 'Item';
    var color   = btn.getAttribute('data-color');
    var storage = btn.getAttribute('data-storage');
    var summary = '<strong>'+esc(name)+'</strong>';
    if(color) summary += ' · '+esc(color);
    if(storage) summary += ' · Storage: '+esc(storage);
    var cs = document.getElementById('claimItemSummary');
    if(cs) cs.innerHTML = summary;
    var overlay = document.getElementById('claimSubmitOverlay');
    if(overlay){ overlay.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  };

  window.closeClaimModal = function(){
    var overlay = document.getElementById('claimSubmitOverlay');
    if(overlay){ overlay.style.display = 'none'; document.body.style.overflow = ''; }
  };

  window.closeStudentSuccessModal = function(){
    var m = document.getElementById('studentClaimSuccessModal');
    if(m){ m.classList.remove('open'); }
    window.location.reload();
  };

  var claimBtn = document.getElementById('claimSubmitBtn');
  if(claimBtn){
    claimBtn.addEventListener('click', function(){
      var desc = (document.getElementById('claimProofDesc')||{}).value || '';
      desc = desc.trim();
      var descErr  = document.getElementById('claimDescErr');
      var photoErr = document.getElementById('claimPhotoErr');
      var valid = true;
      if(!desc){ if(descErr) descErr.style.display='block'; valid=false; } else if(descErr) descErr.style.display='none';
      if(!_claimPhoto){ if(photoErr) photoErr.style.display='block'; valid=false; } else if(photoErr) photoErr.style.display='none';
      if(!valid) return;
      var btn = this; btn.disabled=true; btn.textContent='Submitting…';
      fetch(@json(route('student.claim')), {
        method: 'POST',
        headers: _JSON_HEADERS,
        body: JSON.stringify({
          found_item_id:    _claimFoundId,
          lost_report_id:   _claimLostId,
          proof_description: desc,
          imageDataUrl:     _claimPhoto
        })
      }).then(parseLaravelFetchResponse).then(function(res){
        btn.disabled=false; btn.textContent='Submit Claim';
        if(res.ok && res.data && res.data.ok){
          closeClaimModal();
          var ticket = _claimLostId || '';
          var pairs = window.STUDENT_MATCH_PAIRS || [];
          var p = pairs.find(function(x){ return x.lost_id === _claimLostId; });
          if(p && p.lost_ticket_display) ticket = p.lost_ticket_display;
          var msg = document.getElementById('studentSuccessMessage');
          if(msg){
            msg.innerHTML = 'The item should be claimed at the office. Present your ticket ID: <strong>'+esc(ticket)+'</strong>.';
          }
          var sm = document.getElementById('studentClaimSuccessModal');
          if(sm) sm.classList.add('open');
        } else {
          if(typeof window.appUiAlert === 'function') window.appUiAlert('Error: ' + laravelErrorMessage(res));
          else alert('Error: ' + laravelErrorMessage(res));
        }
      }).catch(function(){
        btn.disabled=false; btn.textContent='Submit Claim';
        if(typeof window.appUiAlert === 'function') window.appUiAlert('Network error. Try again.');
        else alert('Network error. Try again.');
      });
    });
  }

  document.querySelectorAll('.open-claim-modal-btn').forEach(function(btn){
    btn.addEventListener('click', function(){ openClaimModal(this); });
  });

  var rc = document.getElementById('reportCategory');
  if(rc) rc.addEventListener('change', function(){
    var row = document.getElementById('reportDocTypeRow');
    if(row) row.style.display = this.value === 'Document & Identification' ? 'grid' : 'none';
  });

  var rf = document.getElementById('reportForm');
  if(rf) rf.addEventListener('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this);
    var payload = Object.fromEntries(fd.entries());
    if((payload.category || '') === 'Document & Identification'){
      var dt = (payload.document_type || '').trim();
      if(!dt){
        if(typeof window.appUiAlert === 'function') window.appUiAlert('Please select the type of identification.');
        else alert('Please select the type of identification.');
        return;
      }
    }
    if(_reportImage) payload.imageDataUrl = _reportImage;
    _pendingLostReportPayload = payload;
    var box = document.getElementById('studentLostReportReviewSummary');
    if(box) box.innerHTML = buildLostReportReviewHtml(payload);
    var consent = document.getElementById('studentLostReportReviewConsent');
    if(consent) consent.checked = false;
    closeReportModal();
    openStudentLostReportReviewModal();
  });

  var reviewBack = document.getElementById('studentLostReportReviewBack');
  if(reviewBack) reviewBack.addEventListener('click', function(){
    closeStudentLostReportReviewModal();
    document.body.style.overflow = 'hidden';
    openReportModal();
  });

  var reviewSubmit = document.getElementById('studentLostReportReviewSubmit');
  if(reviewSubmit) reviewSubmit.addEventListener('click', function(){
    var consent = document.getElementById('studentLostReportReviewConsent');
    if(!consent || !consent.checked){
      if(typeof window.appUiAlert === 'function') window.appUiAlert('Please confirm that the above details are accurate and correct.');
      else alert('Please confirm that the above details are accurate and correct.');
      return;
    }
    var payload = _pendingLostReportPayload;
    if(!payload){
      if(typeof window.appUiAlert === 'function') window.appUiAlert('Session expired. Open the form again.');
      return;
    }
    var btn = this;
    btn.disabled = true;
    btn.textContent = 'Submitting…';
    fetch(@json(route('student.reports')), {
      method: 'POST',
      headers: _JSON_HEADERS,
      body: JSON.stringify(payload),
    })
    .then(parseLaravelFetchResponse)
    .then(function(res){
      btn.disabled = false;
      btn.textContent = 'Submit';
      if(res.ok && res.data && res.data.ok){
        closeStudentLostReportReviewModal();
        document.body.style.overflow = '';
        var tid = res.data.id || '';
        if(typeof window.appUiSuccess === 'function'){
          window.appUiSuccess({
            title: 'Success',
            message: 'Report has been submitted successfully!',
            ticketId: tid,
            onClose: function(){ window.location.reload(); }
          });
        } else {
          closeReportModal();
          window.location.reload();
        }
      } else {
        if(typeof window.appUiAlert === 'function') window.appUiAlert('Error: ' + laravelErrorMessage(res));
        else alert('Error: ' + laravelErrorMessage(res));
      }
    })
    .catch(function(){
      btn.disabled = false;
      btn.textContent = 'Submit';
      if(typeof window.appUiAlert === 'function') window.appUiAlert('Network error. Try again.');
      else alert('Network error. Try again.');
    });
  });

  document.addEventListener('keydown', function(e){
    if(e.key !== 'Escape') return;
    var rev = document.getElementById('studentLostReportReviewModal');
    if(rev && rev.classList.contains('open')){
      e.preventDefault();
      closeStudentLostReportReviewModal();
      document.body.style.overflow = 'hidden';
      openReportModal();
      return;
    }
    closeReportModal();
    closeClaimModal();
    closeFoundProtocolModal();
    closeClaimProtocolModal();
    closeStudentCompareModal();
  });
})();
</script>

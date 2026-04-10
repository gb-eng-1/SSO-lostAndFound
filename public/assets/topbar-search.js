/**
 * Global topbar search: debounced suggestions + role-specific open handler.
 */
(function () {
  function esc(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function debounce(fn, ms) {
    var t;
    return function () {
      var ctx = this, args = arguments;
      clearTimeout(t);
      t = setTimeout(function () { fn.apply(ctx, args); }, ms);
    };
  }

  var wrap = document.querySelector('[data-global-search]');
  if (!wrap) return;

  var role = wrap.getAttribute('data-search-role') || 'admin';
  var endpoint = wrap.getAttribute('data-search-endpoint');
  var input = wrap.querySelector('.global-search-input');
  var dropdown = wrap.querySelector('.global-search-dropdown');
  var form = wrap.querySelector('form');
  if (!input || !dropdown || !endpoint) return;

  var open = false;

  function hide() {
    dropdown.style.display = 'none';
    open = false;
  }

  function renderResults(results) {
    if (!results || !results.length) {
      dropdown.innerHTML = '<div class="sd-no-results">No matches.</div>';
      dropdown.style.display = 'block';
      open = true;
      return;
    }
    dropdown.innerHTML = results.map(function (r) {
      var icon = r.kind === 'report' ? 'fa-regular fa-file-lines' : 'fa-solid fa-box';
      return (
        '<div class="search-dropdown-item" role="option" tabindex="0" data-id="' + esc(r.id) + '" data-kind="' + esc(r.kind) + '">'
        + '<div class="sd-icon"><i class="' + icon + '"></i></div>'
        + '<div class="sd-info">'
        + '<div class="sd-barcode">' + esc(r.id) + '</div>'
        + '<div class="sd-title">' + esc(r.title) + '</div>'
        + '<div class="sd-desc">' + esc(r.subtitle) + '</div>'
        + '<div class="sd-meta"><span class="sd-meta-item"><i class="fa-solid fa-tag"></i> ' + esc(r.meta) + '</span></div>'
        + '</div></div>'
      );
    }).join('');
    dropdown.style.display = 'block';
    open = true;
  }

  function fetchSuggestions() {
    var q = input.value.trim();
    if (q.length < 2) {
      hide();
      return;
    }
    fetch(endpoint + '?q=' + encodeURIComponent(q), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) {
          hide();
          return;
        }
        renderResults(data.results || []);
      })
      .catch(function () { hide(); });
  }

  var debounced = debounce(fetchSuggestions, 280);

  input.addEventListener('input', function () {
    debounced();
  });

  input.addEventListener('focus', function () {
    if (input.value.trim().length >= 2) debounced();
  });

  dropdown.addEventListener('click', function (e) {
    var row = e.target.closest('.search-dropdown-item');
    if (!row) return;
    e.preventDefault();
    var id = row.getAttribute('data-id');
    if (!id) return;
    hide();
    input.blur();

    if (role === 'admin') {
      if (typeof window.showItemDetailsModal === 'function') {
        window.showItemDetailsModal(id);
      }
      return;
    }

    if (typeof window.openStudentItemFromSearch === 'function') {
      window.openStudentItemFromSearch(id);
    }
  });

  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) hide();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hide();
  });

  form.addEventListener('submit', function (e) {
    if (open && dropdown.querySelector('.search-dropdown-item')) {
      var first = dropdown.querySelector('.search-dropdown-item');
      if (first) {
        e.preventDefault();
        first.click();
      }
    }
  });
})();

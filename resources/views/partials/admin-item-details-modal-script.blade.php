<link rel="stylesheet" href="{{ asset('STUDENT/ItemDetailsModal.css') }}?v=12">
<link rel="stylesheet" href="{{ asset('assets/admin-item-details.css') }}?v=1">
<script>
(function() {
    var ADMIN_ITEM_LOOKUP_URL = @json(route('admin.item'));

    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function infoRow(label, val) {
        val = (val == null || val === '') ? '—' : String(val);
        return '<div class="item-details-info-row"><dt>' + esc(label) + '</dt><dd>' + esc(val) + '</dd></div>';
    }

    function sectionHead() {
        return '<div class="item-details-section-head">'
            + '<hr class="item-details-divider" />'
            + '<h4 class="item-details-info-title">General Information</h4>'
            + '<hr class="item-details-divider" />'
            + '</div>';
    }

    function fmtDate(val) {
        if (val == null || val === '') return '—';
        var s = String(val);
        var mdy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/);
        if (mdy) {
            var mo = parseInt(mdy[1], 10), da = parseInt(mdy[2], 10), yr = mdy[3];
            yr = yr.length === 4 ? yr.slice(-2) : yr;
            return mo + '/' + da + '/' + yr;
        }
        var iso = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        var d;
        if (iso) {
            d = new Date(+iso[1], +iso[2] - 1, +iso[3]);
        } else {
            d = new Date(s);
        }
        if (isNaN(d.getTime())) return s;
        return (d.getMonth() + 1) + '/' + d.getDate() + '/' + String(d.getFullYear()).slice(-2);
    }

    function itemImageSrc(raw) {
        if (!raw || typeof raw !== 'string') return '';
        if (/^data:image\//i.test(raw)) return raw;
        if (/^https?:\/\//i.test(raw)) return raw;
        return '';
    }

    function resolvePreset(item) {
        if (item.view_preset) return item.view_preset;
        var id = String(item.id || '');
        if (id.indexOf('REF-') === 0) return 'lost_report';
        return item.item_type === 'ID & Nameplate' ? 'found_external' : 'found_internal';
    }

    function renderLostReport(item) {
        var p = item.parsed || {};
        var tid = item.display_ticket_id || item.id;
        var imgSrc = itemImageSrc(item.image_data);
        var left = '<div class="item-details-left">';
        if (imgSrc) {
            left += '<div class="item-details-image-wrap"><img class="item-details-image" src="' + imgSrc + '" alt=""></div>';
        } else {
            left += '<div class="item-details-image-wrap item-details-image-placeholder"><i class="fa-regular fa-image"></i><span>No photo</span></div>';
        }
        left += '<p class="item-details-barcode-id">' + esc(tid) + '</p></div>';

        var dl = '<dl class="item-details-info-list">'
            + infoRow('Category', item.item_type)
            + infoRow('Full Name', p.full_name)
            + infoRow('Contact Number', p.contact)
            + infoRow('Department', p.department)
            + infoRow('ID', p.student_number)
            + infoRow('Item', p.item)
            + infoRow('Color', item.color)
            + infoRow('Brand', item.brand)
            + infoRow('Item Description', p.clean_description)
            + infoRow('Date Lost', item.date_lost ? fmtDate(item.date_lost) : null)
            + infoRow('Status', item.status);
        if (item.linked_summary && item.linked_summary.matched_found_item_id) {
            dl += infoRow('Matched found item', item.linked_summary.matched_found_item_id);
        }
        dl += '</dl>';

        var right = '<div class="item-details-right">' + sectionHead() + dl + '</div>';
        return '<div class="item-details-body">' + left + right + '</div>';
    }

    function renderFoundItem(item, isExternal) {
        var p = item.parsed || {};
        var bid = item.display_ticket_id || item.id;
        var imgSrc = itemImageSrc(item.image_data);
        var left = '<div class="item-details-left">';
        if (imgSrc) {
            left += '<div class="item-details-image-wrap"><img class="item-details-image" src="' + imgSrc + '" alt=""></div>';
        } else {
            left += '<div class="item-details-image-wrap item-details-image-placeholder"><i class="fa-regular fa-image"></i><span>No photo</span></div>';
        }
        left += '<p class="item-details-barcode-id">Barcode ID: ' + esc(bid) + '</p>';
        if (isExternal) {
            left += '<p class="admin-item-preset-tag" style="margin-top:4px;">External ID (guest)</p>';
        }
        left += '</div>';

        var itemDesc = (p.clean_description != null && p.clean_description !== '') ? p.clean_description : (item.item_description || '');
        var dl = '<dl class="item-details-info-list">'
            + infoRow('Category', item.item_type)
            + infoRow('Item', p.item)
            + infoRow('Color', item.color)
            + infoRow('Brand', item.brand)
            + infoRow('Item Description', itemDesc)
            + infoRow('Storage Location', item.storage_location)
            + infoRow('Found At', item.found_at)
            + infoRow('Found By', item.found_by)
            + infoRow('Encoded By', item.encoded_by_parsed)
            + infoRow('Date Found', item.date_found || fmtDate(item.date_encoded));
        if (item.linked_summary && item.linked_summary.matched_lost_report_id) {
            dl += infoRow('Linked lost report', item.linked_summary.matched_lost_report_id);
        }
        dl += infoRow('Status', item.status) + '</dl>';

        var right = '<div class="item-details-right">' + sectionHead() + dl + '</div>';
        return '<div class="item-details-body">' + left + right + '</div>';
    }

    function buildModalHtml(bodyInner) {
        return '<div class="item-details-dialog" style="max-width:min(720px,96vw);" onclick="event.stopPropagation()">'
            + '<div class="item-details-header">'
            + '<h3 class="item-details-title">Item Details</h3>'
            + '<button type="button" class="item-details-close" onclick="closeAdminItemModal()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>'
            + '</div>'
            + bodyInner
            + '<div class="item-details-footer">'
            + '<button type="button" class="item-details-btn item-details-btn-cancel" onclick="closeAdminItemModal()">Close</button>'
            + '</div>'
            + '</div>';
    }

    window.showItemDetailsModal = function(itemId) {
        if (!itemId) return;
        closeAdminItemModal();
        var overlay = document.createElement('div');
        overlay.id = 'adminItemModal';
        overlay.className = 'item-details-overlay open';
        overlay.setAttribute('style', 'z-index:10050;');
        overlay.innerHTML = buildModalHtml('<div class="item-details-body item-details-body--single" style="padding:40px;text-align:center;"><i class="fa-solid fa-spinner fa-spin" style="font-size:28px;color:#8b0000;"></i><p style="margin-top:12px;color:#6b7280;font-family:Poppins,sans-serif;">Loading…</p></div>');
        document.body.appendChild(overlay);
        overlay.addEventListener('click', function(e) { if (e.target === overlay) closeAdminItemModal(); });

        fetch(ADMIN_ITEM_LOOKUP_URL + '?id=' + encodeURIComponent(itemId), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        })
            .then(function(r) {
                if (!r.ok) {
                    return r.json().then(function(j) { throw new Error(j.error || ('HTTP ' + r.status)); }).catch(function() { throw new Error('HTTP ' + r.status); });
                }
                return r.json();
            })
            .then(function(json) {
                if (!json.ok) { showAdminItemError(json.error || 'Item not found.'); return; }
                var item = json.data;
                var preset = resolvePreset(item);
                var bodyHtml;
                if (preset === 'lost_report') bodyHtml = renderLostReport(item);
                else if (preset === 'found_external') bodyHtml = renderFoundItem(item, true);
                else bodyHtml = renderFoundItem(item, false);

                var o = document.getElementById('adminItemModal');
                if (!o) return;
                o.innerHTML = buildModalHtml(bodyHtml);
            })
            .catch(function(err) { showAdminItemError((err && err.message) ? err.message : 'Could not load item details.'); });
    };

    function showAdminItemError(msg) {
        var o = document.getElementById('adminItemModal');
        if (!o) return;
        o.innerHTML = buildModalHtml(
            '<div class="item-details-body item-details-body--single" style="padding:32px 24px;text-align:center;font-family:Poppins,sans-serif;">'
            + '<i class="fa-solid fa-circle-exclamation" style="font-size:32px;color:#ef4444;margin-bottom:12px;display:block;"></i>'
            + '<p style="color:#111827;font-weight:600;margin-bottom:8px;">Could not load item</p>'
            + '<p style="color:#6b7280;font-size:13px;">' + esc(msg) + '</p>'
            + '</div>'
        );
    }

    window.closeAdminItemModal = function() { var m = document.getElementById('adminItemModal'); if (m) m.remove(); };
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAdminItemModal(); });
})();

document.addEventListener('click', function(e) {
    var link = e.target.closest('[data-item-id]');
    if (!link) return;
    e.preventDefault();
    var id = link.getAttribute('data-item-id');
    if (id && typeof window.showItemDetailsModal === 'function') window.showItemDetailsModal(id);
});
</script>

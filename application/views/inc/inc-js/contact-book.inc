<script>
/* ================================================================
   Contact Book — Client JS
   Loaded by footer.php when $data['js'] = 'contact-book.inc'
   ================================================================ */
var CB_BASE = '<?php echo site_url(); ?>';
var delContactId = null;

// Avatar colors matching PHP
var avatarColors = [
    '#3498db', '#e74c3c', '#2ecc71', '#9b59b6', '#f1c40f', 
    '#1abc9c', '#e67e22', '#34495e', '#16a085', '#27ae60'
];

function getAvatarColor(id) {
    return avatarColors[parseInt(id) % avatarColors.length];
}

function toggleStatusLbl(checked) {
    var lbl = document.getElementById('f-status-lbl');
    if (checked) {
        lbl.textContent = 'Active';
        lbl.style.color = '#2ecc71';
    } else {
        lbl.textContent = 'Inactive';
        lbl.style.color = '#7f8c8d';
    }
}

// ── Save Contact (AJAX) ───────────────────────────────────────────
function saveContact() {
    var id       = document.getElementById('f-id').value;
    var name     = document.getElementById('f-name').value.trim();
    var phone    = document.getElementById('f-phone').value.trim();
    var email    = document.getElementById('f-email').value.trim();
    var company  = document.getElementById('f-company').value.trim();
    var jobTitle = document.getElementById('f-job-title').value.trim();
    var category = document.getElementById('f-category').value;
    var address  = document.getElementById('f-address').value.trim();
    var notes    = document.getElementById('f-notes').value.trim();
    var status   = document.getElementById('f-status').checked ? 1 : 0;

    if (!name) {
        showToast('Full Name is required.', 'error');
        return;
    }

    var $btn = jQuery('#cb-save-btn').prop('disabled', true);
    var originalText = jQuery('#cb-save-label').text();
    jQuery('#cb-save-label').text('Saving...');

    jQuery.post(CB_BASE + 'contact-book/save', {
        id: id,
        name: name,
        phone: phone,
        email: email,
        company: company,
        job_title: jobTitle,
        category: category,
        address: address,
        notes: notes,
        status: status
    }, function(r) {
        $btn.prop('disabled', false);
        jQuery('#cb-save-label').text(originalText);

        if (r.success) {
            showToast(r.message);
            renderContactCard(r.contact, parseInt(id) > 0 ? 'update' : 'insert');
            resetForm();
            recalculateStats();
        } else {
            showToast(r.message, 'error');
        }
    }).fail(function() {
        $btn.prop('disabled', false);
        jQuery('#cb-save-label').text(originalText);
        showToast('Server error. Please try again.', 'error');
    });
}

// ── Render Card in DOM ─────────────────────────────────────────────
function renderContactCard(c, mode) {
    var initials = c.name.split(' ').map(function(n) { return n[0]; }).join('').toUpperCase().substring(0,2);
    var color = getAvatarColor(c.id);
    var catClass = c.category.toLowerCase();
    
    var subtitle = c.company || 'No Company';
    if (c.job_title) {
        subtitle = c.job_title + (c.company ? ' at ' + c.company : '');
    }

    var phoneRow = c.phone ? '<div class="cb-info-row"><i class="fa fa-phone"></i><span title="' + escHtml(c.phone) + '">' + escHtml(c.phone) + '</span></div>' : '';
    var emailRow = c.email ? '<div class="cb-info-row"><i class="fa fa-envelope"></i><span title="' + escHtml(c.email) + '">' + escHtml(c.email) + '</span></div>' : '';
    var addrRow  = c.address ? '<div class="cb-info-row"><i class="fa fa-map-marker"></i><span title="' + escHtml(c.address) + '">' + escHtml(c.address) + '</span></div>' : '';

    var statusDotClass = c.status == 1 ? 'cb-status-active' : 'cb-status-inactive';

    var cardHtml = 
    '<div class="cb-card" data-id="' + c.id + '" ' +
         'data-name="' + escAttr(c.name.toLowerCase()) + '" ' +
         'data-company="' + escAttr((c.company || '').toLowerCase()) + '" ' +
         'data-email="' + escAttr((c.email || '').toLowerCase()) + '" ' +
         'data-phone="' + escAttr((c.phone || '').toLowerCase()) + '" ' +
         'data-category="' + c.category + '" ' +
         'data-status="' + c.status + '">' +
      '<div class="cb-status-indicator ' + statusDotClass + '"></div>' +
      '<div class="cb-card-header">' +
        '<div class="cb-avatar" style="background:' + color + ';">' + escHtml(initials) + '</div>' +
        '<div style="flex:1; overflow:hidden;">' +
          '<h4 class="cb-card-name" title="' + escHtml(c.name) + '">' + escHtml(c.name) + '</h4>' +
          '<p class="cb-card-title-sub">' + escHtml(subtitle) + '</p>' +
        '</div>' +
      '</div>' +
      '<div class="cb-card-body">' + phoneRow + emailRow + addrRow + '</div>' +
      '<div class="cb-card-footer">' +
        '<span class="cb-cat-badge cat-' + catClass + '">' + c.category + '</span>' +
        '<div class="cb-card-actions">' +
          '<button class="cb-act-btn cb-btn-edit-act" title="Edit Contact" onclick="editContact(' + c.id + ')"><i class="fa fa-pencil"></i></button>' +
          '<button class="cb-act-btn cb-btn-del-act" title="Delete Contact" onclick="deleteContact(' + c.id + ', \'' + c.name.replace(/'/g, "\\'") + '\')"><i class="fa fa-trash"></i></button>' +
        '</div>' +
      '</div>' +
    '</div>';

    // If grid container does not exist (empty state), create it
    if (!document.getElementById('cb-grid-container')) {
        document.getElementById('cb-cards-grid').innerHTML = '<div class="cb-grid" id="cb-grid-container"></div>';
    }

    var grid = jQuery('#cb-grid-container');

    if (mode === 'insert') {
        grid.append(cardHtml);
        var $el = grid.find('.cb-card[data-id="' + c.id + '"]');
        $el.css('background', '#fff3e0').animate({ backgroundColor: '#fdfcf9' }, 1200);
    } else {
        var $el = grid.find('.cb-card[data-id="' + c.id + '"]');
        $el.replaceWith(cardHtml);
        grid.find('.cb-card[data-id="' + c.id + '"]').css('background', '#fff3e0').animate({ backgroundColor: '#fdfcf9' }, 1200);
    }

    applyFilters();
}

// ── Populate Form for Edit ───────────────────────────────────────
function editContact(id) {
    jQuery.get(CB_BASE + 'contact-book/get', { id: id }, function(r) {
        if (r.success) {
            var c = r.contact;
            document.getElementById('f-id').value       = c.id;
            document.getElementById('f-name').value     = c.name || '';
            document.getElementById('f-phone').value    = c.phone || '';
            document.getElementById('f-email').value    = c.email || '';
            document.getElementById('f-company').value  = c.company || '';
            document.getElementById('f-job-title').value = c.job_title || '';
            document.getElementById('f-category').value = c.category || 'General';
            document.getElementById('f-address').value  = c.address || '';
            document.getElementById('f-notes').value    = c.notes || '';
            
            var statusChk = document.getElementById('f-status');
            statusChk.checked = parseInt(c.status) === 1;
            toggleStatusLbl(statusChk.checked);

            // Update panel text
            document.getElementById('cb-form-title').innerHTML = '<i class="fa fa-pencil"></i> Edit Contact';
            document.getElementById('cb-form-subtitle').textContent = 'Modify stored details of this contact';
            document.getElementById('cb-save-label').textContent = 'Update Contact';

            // Scroll form into view
            document.querySelector('.cb-left-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            showToast(r.message, 'error');
        }
    });
}

// ── Delete Contact Modal Trigger ───────────────────────────────────
function deleteContact(id, name) {
    delContactId = id;
    document.getElementById('del-contact-name').textContent = name;
    jQuery('#cbDeleteModal').modal('show');
}

// ── Reset Form ────────────────────────────────────────────────────
function resetForm() {
    document.getElementById('f-id').value = '0';
    document.getElementById('f-name').value = '';
    document.getElementById('f-phone').value = '';
    document.getElementById('f-email').value = '';
    document.getElementById('f-company').value = '';
    document.getElementById('f-job-title').value = '';
    document.getElementById('f-category').value = 'General';
    document.getElementById('f-address').value = '';
    document.getElementById('f-notes').value = '';
    
    var statusChk = document.getElementById('f-status');
    statusChk.checked = true;
    toggleStatusLbl(true);

    document.getElementById('cb-form-title').innerHTML = '<i class="fa fa-plus-circle"></i> Add New Contact';
    document.getElementById('cb-form-subtitle').textContent = 'Store a new contact details securely';
    document.getElementById('cb-save-label').textContent = 'Save Contact';
}

// ── Search & Filter functions ──────────────────────────────────────
function filterContacts(q) {
    applyFilters();
}

function applyFilters() {
    var searchVal = jQuery('#cb-search').val().toLowerCase().trim();
    var catVal    = jQuery('#cb-filter-cat').val();
    var statusVal = jQuery('#cb-filter-status').val();

    var count = 0;

    jQuery('.cb-card').each(function() {
        var card = jQuery(this);
        var name    = card.data('name') || '';
        var company = card.data('company') || '';
        var email   = card.data('email') || '';
        var phone   = card.data('phone') || '';
        var category= card.data('category');
        var status  = card.data('status');

        var matchesSearch = !searchVal || 
                            name.indexOf(searchVal) >= 0 || 
                            company.indexOf(searchVal) >= 0 || 
                            email.indexOf(searchVal) >= 0 || 
                            phone.indexOf(searchVal) >= 0;

        var matchesCat    = !catVal || category === catVal;
        var matchesStatus = statusVal === "" || statusVal == status;

        if (matchesSearch && matchesCat && matchesStatus) {
            card.show();
            count++;
        } else {
            card.hide();
        }
    });

    var gridArea = jQuery('#cb-cards-grid');
    if (count === 0) {
        if (!jQuery('#cb-no-results').length) {
            gridArea.append(
                '<div id="cb-no-results" class="cb-empty">' +
                '<div class="cb-empty-icon">🔍</div>' +
                '<h4>No matching contacts found</h4>' +
                '</div>'
            );
        }
        jQuery('#cb-grid-container').hide();
    } else {
        jQuery('#cb-no-results').remove();
        jQuery('#cb-grid-container').show();
    }
}

// ── Recalculate Stats Panel ─────────────────────────────────────────
function recalculateStats() {
    var total  = 0;
    var active = 0;
    var inactive = 0;

    jQuery('.cb-card').each(function() {
        var status = parseInt(jQuery(this).data('status'));
        total++;
        if (status === 1) {
            active++;
        } else {
            inactive++;
        }
    });

    jQuery('#stat-total').text(total);
    jQuery('#stat-active').text(active);
    jQuery('#stat-inactive').text(inactive);

    // If total is 0, show empty state
    if (total === 0) {
        document.getElementById('cb-cards-grid').innerHTML = 
            '<div class="cb-empty" id="cb-empty-state">' +
            '<div class="cb-empty-icon">👥</div>' +
            '<h4>No contacts found</h4>' +
            '<p>Start adding contacts using the form on the left.</p>' +
            '</div>';
    }
}

// ── Toast Notification ─────────────────────────────────────────────
function showToast(msg, type) {
    var $t = jQuery('#cb-toast');
    var ico = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
    var bg  = type === 'error' ? '#c0392b' : '#2c1a11';
    
    $t.css('background', bg).find('i').attr('class', 'fa ' + ico);
    jQuery('#cb-toast-msg').text(msg);
    $t.addClass('show');
    
    clearTimeout(window._cbToastTimer);
    window._cbToastTimer = setTimeout(function() { $t.removeClass('show'); }, 3000);
}

// ── HTML / Attribute Escape Helpers ─────────────────────────────────
function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── DOM Init ───────────────────────────────────────────────────── */
jQuery(function($) {
    
    // Delete confirm button handler
    $('#cb-confirm-del').on('click', function() {
        if (!delContactId) return;
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.post(CB_BASE + 'contact-book/delete', { id: delContactId }, function(r) {
            $('#cbDeleteModal').modal('hide');
            $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
            
            if (r.success) {
                showToast(r.message);
                var $card = $('.cb-card[data-id="' + delContactId + '"]');
                $card.fadeOut(350, function() {
                    $(this).remove();
                    recalculateStats();
                    applyFilters();
                });
            } else {
                showToast(r.message, 'error');
            }
            delContactId = null;
        }).fail(function() {
            $('#cbDeleteModal').modal('hide');
            $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
            showToast('Server error.', 'error');
            delContactId = null;
        });
    });

});
</script>

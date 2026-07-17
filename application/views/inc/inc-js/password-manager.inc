<script>
/* ================================================================
   Password Manager — Page JS
   Loaded by footer.php when $data['js'] = 'password-manager.inc'
   ================================================================ */
jQuery(function ($) {

  var PM_BASE = '<?php echo site_url(); ?>';

  /* ---------------------------------------------------------------
     1. View Toggle: Grid / List
     --------------------------------------------------------------- */
  var savedView = localStorage.getItem('pm_view') || 'grid';
  if (savedView === 'list') {
    $('#pm-card-container').addClass('pm-list-view');
    $('#btn-list').addClass('active');
    $('#btn-grid').removeClass('active');
  }
  $('#btn-grid').on('click', function () {
    $('#pm-card-container').removeClass('pm-list-view');
    $('#btn-grid').addClass('active');
    $('#btn-list').removeClass('active');
    localStorage.setItem('pm_view', 'grid');
  });
  $('#btn-list').on('click', function () {
    $('#pm-card-container').addClass('pm-list-view');
    $('#btn-list').addClass('active');
    $('#btn-grid').removeClass('active');
    localStorage.setItem('pm_view', 'list');
  });

  /* ---------------------------------------------------------------
     2. Live Search Filter (client-side — instant)
     --------------------------------------------------------------- */
  var searchDelay;
  $('#pm-search').on('input', function () {
    clearTimeout(searchDelay);
    var q = $(this).val().toLowerCase().trim();
    searchDelay = setTimeout(function () {
      filterCards(q);
    }, 200);
  });

  function filterCards(q) {
    var count = 0;
    $('.pm-card-col').each(function () {
      var text = ($(this).data('name') || '') + ' ' + $(this).text().toLowerCase();
      var show = !q || text.indexOf(q) >= 0;
      $(this).toggle(show);
      if (show) count++;
    });
    // Show/hide empty message
    if ($('#pm-card-container').length) {
      if (count === 0) {
        if (!$('#pm-no-results').length) {
          $('#pm-card-container').after(
            '<div id="pm-no-results" class="pm-empty" style="padding:40px;text-align:center;">' +
            '<div style="font-size:44px;opacity:0.25;">🔍</div>' +
            '<h4>No results for "' + $('<div>').text(q).html() + '"</h4>' +
            '</div>'
          );
        }
      } else {
        $('#pm-no-results').remove();
      }
    }
  }

  /* ---------------------------------------------------------------
     3. Filter Dropdowns → reload page with query params
     --------------------------------------------------------------- */
  function buildUrl(params) {
    var base = PM_BASE + 'password-manager';
    var parts = [];
    $.each(params, function (k, v) {
      if (v !== '' && v !== null && v !== undefined) {
        parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
      }
    });
    return base + (parts.length ? '?' + parts.join('&') : '');
  }

  function currentParams() {
    var search = new URLSearchParams(window.location.search);
    return {
      search:   search.get('search')   || '',
      status:   search.get('status')   || '',
      category: search.get('category') || '',
      favorite: search.get('favorite') || '',
      owner:    search.get('owner')    || ''
    };
  }

  $('#pm-filter-status').on('change', function () {
    var p = currentParams();
    p.status = $(this).val();
    window.location.href = buildUrl(p);
  });

  $('#pm-filter-cat').on('change', function () {
    var p = currentParams();
    p.category = $(this).val();
    window.location.href = buildUrl(p);
  });

  // Admin-only: owner filter
  $('#pm-filter-owner').on('change', function () {
    var p = currentParams();
    p.owner = $(this).val();
    window.location.href = buildUrl(p);
  });

  /* ---------------------------------------------------------------
     4. Toggle Favorite (AJAX)
     --------------------------------------------------------------- */
  window.toggleFav = function (id, el) {
    var $icon = $(el).find('i');
    var isNowFav = $icon.hasClass('fa-star-o'); // was unfav → going fav
    $.post(PM_BASE + 'pm-toggle-fav', { id: id }, function (r) {
      if (r.success) {
        if (isNowFav) {
          $icon.removeClass('fa-star-o').addClass('fa-star').css('color', '#f1c40f');
        } else {
          $icon.removeClass('fa-star').addClass('fa-star-o').css('color', '#ccc');
        }
        showPMToast(isNowFav ? 'Added to Favorites ⭐' : 'Removed from Favorites');
      } else {
        showPMToast(r.message || 'Error updating favorite', 'error');
      }
    }).fail(function () {
      showPMToast('Network error', 'error');
    });
  };

  /* ---------------------------------------------------------------
     5. Reveal Secret Field (AJAX decrypt)
     --------------------------------------------------------------- */
  var revealCache = {}; // cache decrypted values per {id+field}

  window.revealSecret = function (id, field, spanId) {
    var $span = $('#' + spanId);
    var cacheKey = id + '_' + field;

    if ($span.hasClass('revealed')) {
      // Hide again
      $span.text('••••••••••').removeClass('revealed');
      return;
    }

    if (revealCache[cacheKey]) {
      $span.text(revealCache[cacheKey]).addClass('revealed');
      return;
    }

    $span.html('<i class="fa fa-spinner fa-spin" style="opacity:0.5;"></i>');
    $.post(PM_BASE + 'pm-get-secret', { id: id, field: field }, function (r) {
      if (r.success) {
        revealCache[cacheKey] = r.value;
        $span.text(r.value || '(empty)').addClass('revealed');
      } else {
        $span.text('••••••••••');
        showPMToast(r.message || 'Failed to decrypt', 'error');
      }
    }).fail(function () {
      $span.text('••••••••••');
      showPMToast('Network error', 'error');
    });
  };

  /* ---------------------------------------------------------------
     6. Copy Plain Text (username / visible text)
     --------------------------------------------------------------- */
  window.copyText = function (text) {
    _pmCopyToClipboard(text);
    showPMToast('Copied to clipboard! 📋');
  };

  /* ---------------------------------------------------------------
     7. Copy Secret (decrypt first, then copy)
     --------------------------------------------------------------- */
  window.copySecret = function (id, field) {
    var cacheKey = id + '_' + field;
    if (revealCache[cacheKey]) {
      _pmCopyToClipboard(revealCache[cacheKey]);
      showPMToast('Password copied! 📋');
      return;
    }
    $.post(PM_BASE + 'pm-get-secret', { id: id, field: field }, function (r) {
      if (r.success) {
        revealCache[cacheKey] = r.value;
        _pmCopyToClipboard(r.value);
        showPMToast('Password copied! 📋');
      } else {
        showPMToast(r.message || 'Failed to copy', 'error');
      }
    }).fail(function () {
      showPMToast('Network error', 'error');
    });
  };

  function _pmCopyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text);
    } else {
      var $tmp = $('<textarea style="position:absolute;left:-9999px;">').val(text).appendTo('body');
      $tmp[0].select();
      document.execCommand('copy');
      $tmp.remove();
    }
  }

  /* ---------------------------------------------------------------
     8. Delete Record
     --------------------------------------------------------------- */
  var _delId = null;
  window.deletePMRecord = function (id) {
    _delId = id;
    $('#pm-del-modal').modal('show');
  };

  $('#pm-del-confirm').on('click', function () {
    if (!_delId) return;
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post(PM_BASE + 'pm-delete', { id: _delId }, function (r) {
      $('#pm-del-modal').modal('hide');
      $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
      if (r.success) {
        // Remove card from DOM
        $('.pm-card-col').filter(function () {
          return $(this).find('[onclick*="deletePMRecord(' + _delId + ')"]').length > 0;
        }).fadeOut(350, function () { $(this).remove(); });
        showPMToast('Entry deleted.');
      } else {
        showPMToast(r.message || 'Delete failed', 'error');
      }
      _delId = null;
    }).fail(function () {
      $('#pm-del-modal').modal('hide');
      $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
      showPMToast('Network error', 'error');
      _delId = null;
    });
  });

  /* ---------------------------------------------------------------
     9. Toast Notification
     --------------------------------------------------------------- */
  window.showPMToast = function (msg, type) {
    var $t  = $('#pm-toast');
    if (!$t.length) {
      $t = $('<div id="pm-toast" style="position:fixed;bottom:22px;right:22px;z-index:9999;' +
             'padding:11px 20px;border-radius:10px;font-size:13px;font-weight:600;' +
             'color:#fff;box-shadow:0 4px 18px rgba(0,0,0,.28);display:none;' +
             'align-items:center;gap:9px;max-width:320px;">' +
             '<i id="pm-toast-ico" class="fa fa-check-circle"></i>' +
             '<span id="pm-toast-msg"></span></div>').appendTo('body');
    }
    $t.css('background', type === 'error' ? '#c0392b' : '#2c1a4e');
    $t.find('#pm-toast-ico').attr('class', 'fa ' + (type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'));
    $t.find('#pm-toast-msg').text(msg);
    $t.css('display', 'flex').hide().fadeIn(220);
    clearTimeout(window._pmToastTimer);
    window._pmToastTimer = setTimeout(function () { $t.fadeOut(300); }, 3000);
  };

  /* ---------------------------------------------------------------
    10. Delete Confirm Modal (inject if not present)
     --------------------------------------------------------------- */
  if (!$('#pm-del-modal').length) {
    $('body').append(
      '<div class="modal fade" id="pm-del-modal" tabindex="-1">' +
      '<div class="modal-dialog modal-sm">' +
      '<div class="modal-content" style="border-radius:12px;overflow:hidden;">' +
      '<div class="modal-header" style="background:linear-gradient(135deg,#e74c3c,#c0392b);' +
           'color:#fff;border:none;padding:14px 20px;border-radius:0;">' +
      '<button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;"><span>&times;</span></button>' +
      '<h4 class="modal-title"><i class="fa fa-trash"></i> Delete Entry</h4></div>' +
      '<div class="modal-body" style="padding:20px;text-align:center;">' +
      '<p style="font-size:15px;">Permanently delete this password entry?<br>' +
      '<small style="color:#999;">This action cannot be undone.</small></p></div>' +
      '<div class="modal-footer" style="border:none;padding:10px 20px 20px;text-align:center;">' +
      '<button class="btn btn-default" data-dismiss="modal" style="border-radius:8px;margin-right:8px;">Cancel</button>' +
      '<button class="btn btn-danger" id="pm-del-confirm" style="border-radius:8px;font-weight:600;">' +
      '<i class="fa fa-trash"></i> Delete</button></div></div></div></div>'
    );
  }

  /* ---------------------------------------------------------------
    11. Auto-clear password clipboard after 30s (security)
     --------------------------------------------------------------- */
  var _clipTimer = null;
  var _origCopy = window.copySecret;
  window.copySecret = function (id, field) {
    _origCopy(id, field);
    clearTimeout(_clipTimer);
    _clipTimer = setTimeout(function () {
      if (navigator.clipboard) {
        navigator.clipboard.writeText('');
      }
    }, 30000);
  };

  /* ---------------------------------------------------------------
    12. Admin Banner (show total record count per owner in title)
     --------------------------------------------------------------- */
  <?php if (isset($is_admin) && $is_admin): ?>
  var adminCount = <?php echo (int)$total_count; ?>;
  var adminTitle = document.querySelector('.content-header h1');
  if (adminTitle && adminCount > 0) {
    adminTitle.insertAdjacentHTML('beforeend',
      ' <span style="font-size:12px;background:linear-gradient(135deg,#8e44ad,#6c3483);' +
      'color:#fff;border-radius:20px;padding:2px 10px;vertical-align:middle;font-weight:700;">' +
      'ALL USERS &bull; ' + adminCount + ' entries</span>'
    );
  }
  <?php endif; ?>

});
</script>

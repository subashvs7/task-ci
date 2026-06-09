<script>
jQuery(function ($) {

    // Init select2 on role dropdown inside edit modal
    $('#edit_user_role').select2({ width: '100%', dropdownParent: $('#editUserModal') });

    // ── Edit modal populate ────────────────────────────────────────
    $(document).on('click', '.btn-edit-user', function () {
        var $b = $(this);
        $('#edit_user_id').val($b.data('id'));
        $('#edit_user_name').val($b.data('name'));
        $('#edit_user_email').val($b.data('email'));
        $('#edit_user_role').val($b.data('role')).trigger('change');
        $('#edit_password').val('');
        $('#editUserModal').modal('show');
    });

    // ── Show/hide password toggle ──────────────────────────────────
    $(document).on('click', '.btn-toggle-pw', function () {
        var $input = $($(this).data('target'));
        var isText = $input.attr('type') === 'text';
        $input.attr('type', isText ? 'password' : 'text');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ── Password strength indicator ────────────────────────────────
    $('#add_password').on('input', function () {
        var pw  = $(this).val();
        var str = 0;
        if (pw.length >= 6)            str++;
        if (pw.length >= 10)           str++;
        if (/[A-Z]/.test(pw))          str++;
        if (/[0-9]/.test(pw))          str++;
        if (/[^A-Za-z0-9]/.test(pw))   str++;

        var colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#27ae60', '#1abc9c'];
        var labels = ['', 'Very weak', 'Weak', 'Fair', 'Strong', 'Very strong'];
        $('#pw_strength_bar').css({ width: (str / 5 * 100) + '%', background: colors[str] || '' });
        $('#pw_strength_text').text(pw.length ? labels[str] : '').css('color', colors[str] || '');
    });



    // Helper to load filtered users via AJAX
    function loadFilteredUsers(offset) {
        var search = '';
        var role = $('#filter_user_role').val();
        var status = $('#filter_user_status').val();
        
        // Show loading state in tbody
        $('#user_list_tbody').html('<tr><td colspan="6" class="text-center text-muted" style="padding:30px;"><i class="fa fa-spinner fa-spin"></i> Loading users...</td></tr>');

        $.ajax({
            url: '<?php echo site_url("get-users-ajax"); ?>',
            type: 'GET',
            data: {
                search: search,
                f_role: role,
                f_status: status,
                offset: offset || 0
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#user_list_tbody').html(res.html);
                    $('.box-footer.clearfix').html(res.pagination);
                    bindPaginationClicks();
                } else {
                    $('#user_list_tbody').html('<tr><td colspan="6" class="text-center text-danger" style="padding:30px;">Failed to load users.</td></tr>');
                }
            },
            error: function() {
                $('#user_list_tbody').html('<tr><td colspan="6" class="text-center text-danger" style="padding:30px;">Error occurred while loading users.</td></tr>');
            }
        });
    }

    // Bind AJAX pagination clicks
    function bindPaginationClicks() {
        $('.box-footer.clearfix').find('a.page-link').off('click').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url) {
                var urlObj = new URL(url, window.location.origin);
                var offset = urlObj.searchParams.get('offset') || 0;
                loadFilteredUsers(offset);
            }
        });
    }

    // Initialize values on page load
    (function initFilterDropdowns() {
        var initialRole = window.initialFilterRole;
        var initialStatus = window.initialFilterStatus;

        if (initialRole) {
            $('#filter_user_role').val(initialRole).trigger('change.select2');
        }
        if (initialStatus) {
            $('#filter_user_status').val(initialStatus).trigger('change.select2');
        }

        bindPaginationClicks();
    })();

    // Role and Status change listeners
    $(document).on('change', '#filter_user_role, #filter_user_status', function(e, data) {
        if (data && data.programmatic) {
            return;
        }
        loadFilteredUsers(0);
    });

    // Reset Filters listener
    $(document).on('click', '#btn_reset_filters', function() {
        $('#filter_user_role').val('').trigger('change', [{ programmatic: true }]);
        $('#filter_user_status').val('').trigger('change', [{ programmatic: true }]);
        
        $('#filter_user_role').trigger('change.select2');
        $('#filter_user_status').trigger('change.select2');

        loadFilteredUsers(0);
    });

});
</script>

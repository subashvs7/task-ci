<script>
jQuery(function ($) {

    // ── Open Modal and Populate ─────────────────────────────────────
    $(document).on('click', '.btn-manage-permission', function () {
        var $btn = $(this);
        var role = $btn.data('role');
        var roleName = $btn.data('role-name');

        $('#modal-role-title').text(roleName);
        $('#modal-role-key').val(role);

        // Populate toggles based on button's data attributes
        $('.toggle-permission-switch').each(function () {
            var $switch = $(this);
            var menuKey = $switch.data('menu-key');
            var isAllowed = $btn.attr('data-perm-' + menuKey) == '1';
            
            $switch.prop('checked', isAllowed);

            // Prevent Admin from disabling User Management (lockout prevention)
            if (role === 'admin' && menuKey === 'users') {
                $switch.prop('disabled', true);
            } else {
                $switch.prop('disabled', false);
            }
        });

        $('#permissionModal').modal('show');
    });

    // ── Handle Toggle Switch Changes ─────────────────────────────────
    $(document).on('change', '.toggle-permission-switch', function () {
        var $switch = $(this);
        var role = $('#modal-role-key').val();
        var menuKey = $switch.data('menu-key');
        var hasAccess = $switch.is(':checked') ? 1 : 0;

        $.ajax({
            url: '<?php echo site_url("update-role-permission"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                role: role,
                menu_key: menuKey,
                has_access: hasAccess
            },
            success: function (res) {
                if (res.success) {
                    // Update trigger button data-attribute
                    var $btn = $('.btn-manage-permission[data-role="' + role + '"]');
                    $btn.attr('data-perm-' + menuKey, hasAccess);

                    // Dynamically update allowed modules badge list in table row
                    updateAllowedBadges(role);

                    // Show success toast
                    showToast(res.message || 'Permission updated successfully.', 'success');
                } else {
                    // Revert UI toggle state on failure
                    $switch.prop('checked', !hasAccess);
                    showToast(res.message || 'Failed to update permission.', 'error');
                }
            },
            error: function () {
                // Revert UI toggle state on error
                $switch.prop('checked', !hasAccess);
                showToast('A network connection error occurred.', 'error');
            }
        });
    });

    // Helper: Dynamically rebuild allowed module badges in the table
    function updateAllowedBadges(role) {
        var $btn = $('.btn-manage-permission[data-role="' + role + '"]');
        var modules = {
            'dashboard': 'Dashboard',
            'projects': 'Projects',
            'tasks': 'Tasks',
            'epics': 'Epics',
            'stories': 'User Stories',
            'reports': 'Reports',
            'users': 'User Management'
        };
        var html = '';
        var hasAny = false;

        $.each(modules, function (key, label) {
            var allowed = $btn.attr('data-perm-' + key);
            if (allowed == '1') {
                hasAny = true;
                html += '<span class="label label-success" style="margin-right: 5px; display: inline-block; margin-bottom: 4px;"><i class="fa fa-check"></i> ' + label + '</span>';
            }
        });

        if (!hasAny) {
            html = '<span class="text-muted" style="font-style: italic;">No permissions enabled</span>';
        }

        $('#allowed-modules-' + role).html(html);
    }

    // Helper: Show floating modern toast notification
    function showToast(message, type) {
        var toastClass = (type === 'success') ? 'custom-toast-success' : 'custom-toast-error';
        var iconClass = (type === 'success') ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger';
        
        var $toast = $('<div class="custom-toast ' + toastClass + '">' +
            '<i class="fa ' + iconClass + '" style="font-size: 16px;"></i>' +
            '<span>' + message + '</span>' +
            '</div>');

        $('#toast-container').append($toast);

        // Fade in
        setTimeout(function () {
            $toast.addClass('show');
        }, 50);

        // Auto remove
        setTimeout(function () {
            $toast.removeClass('show');
            setTimeout(function () {
                $toast.remove();
            }, 300);
        }, 3500);
    }

    // ── Handle Sub-Menu Tree Toggle ─────────────────────────────────
    $(document).on('click', '.toggle-sub-menu', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var icon = $(this).find('i');
        $(target).slideToggle(200, function() {
            if ($(this).is(':visible')) {
                icon.removeClass('fa-angle-right').addClass('fa-angle-down');
            } else {
                icon.removeClass('fa-angle-down').addClass('fa-angle-right');
            }
        });
    });

});
</script>

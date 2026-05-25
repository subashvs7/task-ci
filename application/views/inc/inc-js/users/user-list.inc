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



});
</script>

<script>
jQuery(function ($) {
    $('form').on('submit', function () {
        var np = $('#new_password').val();
        var cp = $('#confirm_password').val();
        if (np !== cp) {
            alert('Passwords do not match.');
            return false;
        }
    });
});
</script>

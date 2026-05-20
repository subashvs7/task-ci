<script>
jQuery(function ($) {
    // Animate info-box numbers
    $('.info-box-number').each(function () {
        var $el = $(this);
        var val = parseInt($el.text()) || 0;
        $({ n: 0 }).animate({ n: val }, {
            duration: 800,
            step: function () { $el.text(Math.floor(this.n)); },
            complete: function () { $el.text(val); }
        });
    });
});
</script>

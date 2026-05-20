<script>
jQuery(function ($) {
    // Hover highlight on kanban cards
    $(document).on('mouseenter', '.kanban-card', function () {
        $(this).css('border-color', '#3498db');
    }).on('mouseleave', '.kanban-card', function () {
        $(this).css('border-color', '#e0e0e0');
    });
});
</script>

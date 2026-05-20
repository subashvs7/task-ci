<script>
jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    var moveTaskId = null;

    // Open move modal
    $(document).on('click', '.kanban-status-btn', function () {
        moveTaskId = $(this).data('id');
        $('#move_task_id').val(moveTaskId);
        $('#moveTaskModal').modal('show');
    });

    // Move task to new status
    $(document).on('click', '.move-status-btn', function () {
        var newStatus = $(this).data('status');
        if (!moveTaskId) return;
        $.ajax({
            url: '<?php echo site_url('update-task-status'); ?>',
            type: 'post',
            data: { task_id: moveTaskId, status: newStatus },
            success: function (d) {
                var res = JSON.parse(d);
                if (res.success) {
                    $('#moveTaskModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function () { alert('Failed to update.'); }
        });
    });
});
</script>

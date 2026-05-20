<script>
jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    // Populate Edit Task modal
    $(document).on('click', '.btn-edit-task', function () {
        var $b = $(this);
        $('#edit_task_id').val($b.data('id'));
        $('#edit_title').val($b.data('title'));
        $('#edit_description').val($b.data('description'));
        $('#edit_status').val($b.data('status'));
        $('#edit_priority').val($b.data('priority'));
        $('#edit_type').val($b.data('type'));
        $('#edit_due_date').val($b.data('due'));
        $('#edit_estimate_hours').val($b.data('eh'));
        $('#edit_estimate_minutes').val($b.data('em'));
        $('#edit_pct').val($b.data('pct'));
        $('#edit_project_id').val($b.data('project')).trigger('change');
        $('#edit_assigned_to').val($b.data('assigned')).trigger('change');
        $('#editTaskModal').modal('show');
    });

    // Delete record
    $(document).on('click', '.del_record', function () {
        if (!confirm('Are you sure you want to delete this task?')) return;
        var id  = $(this).val();
        var tbl = $(this).data('tbl');
        var col = $(this).data('col');
        $.ajax({
            url: '<?php echo site_url('delete-record'); ?>',
            type: 'post',
            data: { tbl: tbl, col: col, id: id },
            dataType: 'json',
            success: function (res) {
                alert(res.message);
                if (res.success) location.reload();
            },
            error: function () { alert('Delete failed. Please try again.'); }
        });
    });
});
</script>

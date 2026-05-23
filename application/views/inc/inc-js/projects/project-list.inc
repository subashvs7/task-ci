<script>
jQuery(function ($) {
    // Select2
    $('.select2').select2({ width: '100%' });

    function updateDeadline(startId, endId, wrapperId, daysId) {
        var startVal = $(startId).val();
        var endVal = $(endId).val();
        if (startVal && endVal) {
            var startDate = new Date(startVal);
            var endDate = new Date(endVal);
            var timeDiff = endDate.getTime() - startDate.getTime();
            var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            if (daysDiff >= 0) {
                $(daysId).text(daysDiff);
                $(wrapperId).show();
            } else {
                $(wrapperId).hide();
            }
        } else {
            $(wrapperId).hide();
        }
    }

    // Attach event listeners for real-time calculations
    $('#add_start_date, #add_end_date').on('change', function() {
        updateDeadline('#add_start_date', '#add_end_date', '#add_deadline_wrapper', '#add_deadline_days');
    });

    $('#edit_start_date, #edit_end_date').on('change', function() {
        updateDeadline('#edit_start_date', '#edit_end_date', '#edit_deadline_wrapper', '#edit_deadline_days');
    });

    // Edit project button
    $(document).on('click', '.btn-edit-project', function () {
        var $btn = $(this);
        $('#edit_project_id').val($btn.data('id'));
        $('#edit_name').val($btn.data('name'));
        $('#edit_stacks').val($btn.data('stacks'));
        $('#edit_description').val($btn.data('description'));
        $('#edit_status').val($btn.data('status'));
        $('#edit_priority').val($btn.data('priority'));
        $('#edit_start_date').val($btn.data('start'));
        $('#edit_end_date').val($btn.data('end'));
        $('#edit_owner_id').val($btn.data('owner')).trigger('change');
        
        // Calculate deadline for Edit modal
        updateDeadline('#edit_start_date', '#edit_end_date', '#edit_deadline_wrapper', '#edit_deadline_days');
        
        $('#editProjectModal').modal('show');
    });

    // Delete record
    $(document).on('click', '.del_record', function () {
        if (!confirm('Are you sure you want to delete this project? All related tasks will also be removed.')) return;
        var id  = $(this).val();
        var tbl = $(this).data('tbl');
        var col = $(this).data('col');
        $.ajax({
            url: '<?php echo site_url('delete-record'); ?>',
            type: 'post',
            data: { tbl: tbl, col: col, id: id },
            success: function (d) {
                var res = JSON.parse(d);
                alert(res.message);
                if (res.success) location.reload();
            },
            error: function () { alert('Delete failed. Please try again.'); }
        });
    });
});
</script>

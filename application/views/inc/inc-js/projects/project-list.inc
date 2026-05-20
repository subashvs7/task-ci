<script>
jQuery(function ($) {
    // Select2
    $('.select2').select2({ width: '100%' });

    // Edit project button
    $(document).on('click', '.btn-edit-project', function () {
        var $btn = $(this);
        $('#edit_project_id').val($btn.data('id'));
        $('#edit_name').val($btn.data('name'));
        $('#edit_key_name').val($btn.data('key'));
        $('#edit_description').val($btn.data('description'));
        $('#edit_status').val($btn.data('status'));
        $('#edit_priority').val($btn.data('priority'));
        $('#edit_color').val($btn.data('color') || '#2c3e50');
        $('#edit_start_date').val($btn.data('start'));
        $('#edit_end_date').val($btn.data('end'));
        $('#edit_owner_id').val($btn.data('owner')).trigger('change');
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

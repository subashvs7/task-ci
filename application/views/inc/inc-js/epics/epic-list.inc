<script>
jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    $(document).on('click', '.btn-edit-epic', function () {
        var $b = $(this);
        $('#edit_epic_id').val($b.data('id'));
        $('#edit_epic_name').val($b.data('name'));
        $('#edit_epic_desc').val($b.data('description'));
        $('#edit_epic_status').val($b.data('status'));
        $('#edit_epic_priority').val($b.data('priority'));
        $('#edit_epic_color').val($b.data('color') || '#9b59b6');
        $('#edit_epic_eh').val($b.data('eh'));
        $('#edit_epic_em').val($b.data('em'));
        $('#edit_epic_project').val($b.data('project')).trigger('change');
        $('#editEpicModal').modal('show');
    });

    $(document).on('click', '.del_record', function () {
        if (!confirm('Delete this epic?')) return;
        $.ajax({
            url: '<?php echo site_url('delete-record'); ?>',
            type: 'post',
            data: { tbl: $(this).data('tbl'), col: $(this).data('col'), id: $(this).val() },
            success: function (d) {
                var res = JSON.parse(d);
                alert(res.message);
                if (res.success) location.reload();
            }
        });
    });
});
</script>

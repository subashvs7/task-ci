<script>
jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    $(document).on('click', '.btn-edit-story', function () {
        var $b = $(this);
        $('#edit_story_id').val($b.data('id'));
        $('#edit_story_name').val($b.data('name'));
        $('#edit_story_desc').val($b.data('description'));
        $('#edit_story_status').val($b.data('status'));
        $('#edit_story_priority').val($b.data('priority'));
        $('#edit_story_points').val($b.data('points'));
        $('#edit_story_sprint').val($b.data('sprint'));
        $('#edit_story_assignee').val($b.data('assignee')).trigger('change');
        $('#edit_story_project').val($b.data('project')).trigger('change');
        $('#edit_story_epic').val($b.data('epic')).trigger('change');
        $('#editStoryModal').modal('show');
    });

    $(document).on('click', '.del_record', function () {
        if (!confirm('Delete this user story?')) return;
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

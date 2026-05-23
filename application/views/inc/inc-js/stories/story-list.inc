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
    $('#add_epic_select').on('change', function() {
        var creator = $(this).find(':selected').data('creator');
        if (creator) {
            $('#add_display_assignee').text('Auto-assigning to Epic Creator: ' + creator);
        } else {
            $('#add_display_assignee').text('-- Auto-assigned --');
        }
    });

    $('#edit_story_epic').on('change', function() {
        var creator = $(this).find(':selected').data('creator');
        if (creator) {
            $('#edit_display_assignee').text('Auto-assigning to Epic Creator: ' + creator);
        } else {
            $('#edit_display_assignee').text('-- Auto-assigned --');
        }
    });

    $('.btn-add-subtask').on('click', function(e) {
        e.preventDefault();
        $('#st_story_id').val($(this).data('story'));
        $('#st_project_id').val($(this).data('project'));
        $('#st_epic_id').val($(this).data('epic'));
        
        var epicTime = parseInt($(this).data('epic-time'), 10);
        if (!isNaN(epicTime) && epicTime > 0) {
            var hrs = epicTime / 60;
            // format to 1 or 2 decimal places if needed, e.g., 5.5
            hrs = Math.round(hrs * 100) / 100;
            $('#st_epic_time_display').text(hrs + (hrs === 1 ? ' hr' : ' hrs'));
        } else {
            $('#st_epic_time_display').text('Not Set by TL');
        }
        
        $('#addSubTaskModal').modal('show');
    });
});
</script>

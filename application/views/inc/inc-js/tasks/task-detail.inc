<script>
jQuery(function ($) {
    var taskId = <?php echo $task['task_id']; ?>;
    var baseUrl = '<?php echo site_url(); ?>';

    $('.select2').select2({ width: '100%' });

    // ── Quick status change ────────────────────────────────────────
    $('#quickStatusForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: baseUrl + 'update-task-status',
            type: 'post',
            dataType: 'json',
            data: { task_id: taskId, status: $('#quick_status').val() },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            },
            error: function () { alert('Failed to update status.'); }
        });
    });

    // ── Add comment ────────────────────────────────────────────────
    $('#addCommentForm').on('submit', function (e) {
        e.preventDefault();
        var body = $('#comment_body').val().trim();
        if (!body) return;
        $.ajax({
            url: baseUrl + 'add-comment',
            type: 'post',
            dataType: 'json',
            data: { task_id: taskId, body: body },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            },
            error: function () { alert('Failed to post comment.'); }
        });
    });

    // ── Delete comment ─────────────────────────────────────────────
    $(document).on('click', '.del_comment', function () {
        if (!confirm('Delete this comment?')) return;
        $.ajax({
            url: baseUrl + 'delete-comment',
            type: 'post',
            dataType: 'json',
            data: { comment_id: $(this).data('id') },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            }
        });
    });

    // ── Subtask checkbox toggle ────────────────────────────────────
    $(document).on('change', '.subtask-check', function () {
        $.ajax({
            url: baseUrl + 'update-subtask-status',
            type: 'post',
            dataType: 'json',
            data: { subtask_id: $(this).data('id'), is_done: this.checked ? 1 : 0 },
            success: function (res) {
                if (res.success) location.reload();
            }
        });
    });

    // ── Add sub-task ───────────────────────────────────────────────
    $('#saveSubTask').on('click', function () {
        var title = $('#subtask_title').val().trim();
        if (!title) { alert('Title is required.'); return; }
        $.ajax({
            url: baseUrl + 'add-comment',
            type: 'post',
            dataType: 'json',
            data: { ajax_action: 'add_subtask', task_id: taskId, title: title, assigned_to: $('#subtask_assigned').val() },
            success: function (res) {
                if (res.success) {
                    $('#addSubTaskModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message || 'Failed.');
                }
            },
            error: function () { alert('Request failed.'); }
        });
    });

    // ── Delete sub-task ────────────────────────────────────────────
    $(document).on('click', '.del_subtask', function () {
        if (!confirm('Delete this sub-task?')) return;
        $.ajax({
            url: baseUrl + 'delete-record',
            type: 'post',
            dataType: 'json',
            data: { tbl: 'tm_subtasks', col: 'subtask_id', id: $(this).data('id') },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            }
        });
    });

    // ── Log time ───────────────────────────────────────────────────
    $('#saveTimeLog').on('click', function () {
        var hours = parseInt($('#log_hours').val()) || 0;
        var mins  = parseInt($('#log_minutes').val()) || 0;
        if (hours === 0 && mins === 0) { alert('Please enter time.'); return; }
        $.ajax({
            url: baseUrl + 'add-time-log',
            type: 'post',
            dataType: 'json',
            data: { task_id: taskId, hours: hours, minutes: mins, logged_date: $('#log_date').val(), description: $('#log_description').val() },
            success: function (res) {
                if (res.success) {
                    $('#logTimeModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                }
            }
        });
    });

    // ── Delete time log ────────────────────────────────────────────
    $(document).on('click', '.del_timelog', function () {
        if (!confirm('Delete this time log?')) return;
        $.ajax({
            url: baseUrl + 'delete-time-log',
            type: 'post',
            dataType: 'json',
            data: { time_log_id: $(this).data('id') },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            }
        });
    });

    // ── Upload attachment ──────────────────────────────────────────
    $('#saveAttachment').on('click', function () {
        var fileInput = document.getElementById('attach_file');
        if (!fileInput.files.length) { alert('Please select a file.'); return; }
        var formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('attachment', fileInput.files[0]);
        formData.append('description', $('#attach_desc').val());
        $.ajax({
            url: baseUrl + 'upload-attachment',
            type: 'post',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    $('#uploadAttachModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                }
            }
        });
    });

    // ── Delete attachment ──────────────────────────────────────────
    $(document).on('click', '.del_attachment', function () {
        if (!confirm('Delete this attachment?')) return;
        $.ajax({
            url: baseUrl + 'delete-attachment',
            type: 'post',
            dataType: 'json',
            data: { attachment_id: $(this).data('id') },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            }
        });
    });
    // ── Add Dependency ──────────────────────────────────────────
    $('#addDependencyForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: baseUrl + 'add-dependency',
            type: 'post',
            dataType: 'json',
            data: {
                task_id: <?php echo $task['task_id']; ?>,
                depends_on_task_id: $('#depends_on_task_id').val()
            },
            success: function (res) {
                if (res.success) {
                    $('#addDependencyModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                }
            }
        });
    });

    // ── Delete Dependency ──────────────────────────────────────────
    $(document).on('click', '.del_dependency', function () {
        if (!confirm('Remove this dependency?')) return;
        $.ajax({
            url: baseUrl + 'delete-dependency',
            type: 'post',
            dataType: 'json',
            data: { dependency_id: $(this).data('id') },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.message);
            }
        });
    });
});
</script>

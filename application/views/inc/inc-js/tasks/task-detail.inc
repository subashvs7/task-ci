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

    // ── Preview Attachment ─────────────────────────────────────────
    $(document).on('click', '.btn-preview-attachment', function () {
        var fileUrl = $(this).data('file');
        if (!fileUrl) return;
        
        var ext = fileUrl.split('.').pop().toLowerCase();
        
        if(['jpg','jpeg','png','gif'].indexOf(ext) > -1) {
            var showViewerAttach = function() {
                var $img = $('<img src="' + fileUrl + '" style="display:none;">').appendTo('body');
                var viewer = new Viewer($img[0], {
                    hidden: function () {
                        viewer.destroy();
                        $img.remove();
                    },
                    toolbar: true, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true
                });
                viewer.show();
            };
            if (typeof Viewer !== 'undefined') {
                showViewerAttach();
            } else {
                $.getScript('https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js', function() {
                    $('<link/>', { rel: 'stylesheet', type: 'text/css', href: 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css' }).appendTo('head');
                    showViewerAttach();
                });
            }
        } else if (['pdf', 'txt'].indexOf(ext) > -1) {
            var showViewerDetailPDF = function() {
                var $overlay = $('<div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:999999; display:flex; flex-direction:column;"></div>');
                var $header = $('<div style="padding:10px 20px; text-align:right;"><button style="background:transparent; border:none; color:#fff; font-size:35px; cursor:pointer; line-height:1;" title="Close (Esc)">&times;</button></div>');
                var $iframe = $('<iframe src="' + fileUrl + '" style="flex-grow:1; width:100%; border:none;"></iframe>');
                $overlay.append($header).append($iframe);
                $('body').append($overlay);
                
                var closePdf = function() { $overlay.fadeOut(200, function() { $(this).remove(); $(document).off('keydown.pdf'); }); };
                $header.find('button').on('click', closePdf);
                $(document).on('keydown.pdf', function(e) { if (e.key === 'Escape') closePdf(); });
                $overlay.hide().fadeIn(200);
            };
            showViewerDetailPDF();
        } else {
            window.open(fileUrl, '_blank');
        }
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

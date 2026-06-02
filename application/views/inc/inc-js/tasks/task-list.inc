<script>
jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    // ── Populate Edit Task modal ──────────────────────────────────────────────
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

        var doc = $b.data('document');
        if (doc && doc !== '') {
            $('#edit_document_container').show();
            $('#btn-view-document').off('click').on('click', function() {
                var fileUrl = '<?php echo base_url("uploads/tasks/"); ?>' + doc;
                var ext = doc.split('.').pop().toLowerCase();
                if(['jpg','jpeg','png','gif'].indexOf(ext) > -1) {
                    var showViewer = function() {
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
                        showViewer();
                    } else {
                        $.getScript('https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js', function() {
                            $('<link/>', { rel: 'stylesheet', type: 'text/css', href: 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css' }).appendTo('head');
                            showViewer();
                        });
                    }
                } else if (['pdf', 'txt'].indexOf(ext) > -1) {
                    var showViewerPDF = function() {
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
                    showViewerPDF();
                } else {
                    window.open(fileUrl, '_blank');
                }
            });
        } else {
            $('#edit_document_container').hide();
        }

        $('#editTaskModal').modal('show');
    });

    // ── Delete record ─────────────────────────────────────────────────────────
    $(document).on('click', '.del_record', function () {
        var id  = $(this).val();
        var tbl = $(this).data('tbl');
        var col = $(this).data('col');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This record will be permanently removed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c0392b',
            confirmButtonText: 'Yes, delete it!'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?php echo site_url('delete-record'); ?>',
                type: 'post',
                data: { tbl: tbl, col: col, id: id },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Swal.fire('Deleted!', res.message, 'success').then(function(){ location.reload(); });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function () { Swal.fire('Error', 'Delete failed. Please try again.', 'error'); }
            });
        });
    });

    // ── Preview Document from Table ───────────────────────────────────────────
    $(document).on('click', '.btn-preview-doc', function(e) {
        e.preventDefault();
        var docsStr = $(this).data('document');
        var docs = [];
        
        if (typeof docsStr === 'string') {
            // Check if it's the old single filename format or JSON
            if (docsStr.indexOf('[') === 0) {
                try { docs = JSON.parse(docsStr); } catch(e) { docs = []; }
            } else {
                // It's the old single filename, map it
                var fileUrl = '<?php echo base_url("uploads/tasks/"); ?>' + docsStr;
                docs = [{ version: 'v1', path: fileUrl, name: docsStr, date: '' }];
            }
        }
        
        if (!docs || (Array.isArray(docs) && docs.length === 0)) return;
        if (!Array.isArray(docs)) docs = [docs];
        
        var html = '<style>' +
            '.doc-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; margin-bottom: 10px; border: 1px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.2s ease; background: #fff; }' +
            '.doc-item:hover { border-color: #3498db; box-shadow: 0 4px 8px rgba(52,152,219,0.1); transform: translateY(-1px); }' +
            '.doc-left { display: flex; align-items: center; gap: 12px; }' +
            '.doc-icon { font-size: 24px; color: #e74c3c; margin-right: 12px; }' +
            '.doc-meta { display: flex; flex-direction: column; text-align: left; }' +
            '.doc-title { font-weight: 600; font-size: 14px; color: #2c3e50; }' +
            '.doc-date { font-size: 11px; color: #7f8c8d; margin-top: 2px; }' +
            '.swal2-close { border: 2px solid #e0e0e0 !important; border-radius: 50% !important; width: 32px !important; height: 32px !important; line-height: 28px !important; color: #7f8c8d !important; font-size: 20px !important; transition: all 0.2s !important; margin: 10px 10px 0 0 !important; }' +
            '.swal2-close:hover { border-color: #e74c3c !important; color: #e74c3c !important; background: transparent !important; }' +
            '</style>';
        
        html += '<div style="margin-top:20px;" id="task-docs-container-1">'; // Just a wrapper
        docs.forEach(function(doc, index) {
            var ext = (doc.path || '').split('.').pop().toLowerCase();
            var icon = (['jpg','jpeg','png','gif'].indexOf(ext) > -1) ? 'fa-file-image-o' : (['pdf'].indexOf(ext) > -1 ? 'fa-file-pdf-o' : 'fa-file-o');
            html += '<div class="doc-item">';
            html += '  <a href="javascript:void(0);" class="doc-left btn-preview-task-doc" data-file="' + doc.path + '" style="text-decoration:none; flex-grow:1; display:flex; align-items:center;">';
            html += '    <i class="fa ' + icon + ' doc-icon"></i>';
            html += '    <div class="doc-meta">';
            html += '      <span class="doc-title">' + (doc.version ? doc.version.toUpperCase() + ' - ' : '') + (doc.name || 'Document') + '</span>';
            html += '      <span class="doc-date">' + (doc.date ? '<i class="fa fa-clock-o"></i> ' + doc.date : '') + '</span>';
            html += '    </div>';
            html += '  </a>';
            html += '  <div style="display:flex; align-items:center; gap:10px;">';
            html += '    <a href="javascript:void(0);" class="btn-preview-task-doc" data-file="' + doc.path + '"><i class="fa fa-eye" style="color:#bdc3c7;"></i></a>';
            html += '    <a href="javascript:void(0);" class="btn-delete-single-doc" data-id="' + $(this).data('id') + '" data-type="task" data-index="' + index + '"><i class="fa fa-trash text-danger"></i></a>';
            html += '  </div>';
            html += '</div>';
        }.bind(this));
        html += '</div>';

        html += '<div style="margin-top:20px; text-align:center;">';
        html += '  <button class="btn btn-sm btn-primary btn-upload-more-doc" data-id="' + $(this).data('id') + '" data-type="task"><i class="fa fa-plus"></i> Add More Document</button>';
        html += '  <input type="file" id="hidden-upload-more" style="display:none;" accept="application/pdf,image/*">';
        html += '</div>';

        Swal.fire({
            title: '<span style="color:#2c3e50; font-weight:600;"><i class="fa fa-folder-open-o" style="color:#f39c12; margin-right:8px;"></i> Task Documents</span>',
            html: html,
            showCloseButton: true,
            showConfirmButton: false,
            width: 600,
            padding: '2em',
            customClass: {
                popup: 'swal-modern-popup'
            }
        });
    });

    $(document).on('click', '.btn-preview-task-doc', function(e) {
        e.preventDefault();
        var fileUrl = $(this).data('file');
        var ext = fileUrl.split('.').pop().toLowerCase();
        
        if (['jpg','jpeg','png','gif'].indexOf(ext) > -1) {
            var showViewerTbl = function() {
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
                showViewerTbl();
            } else {
                $.getScript('https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js', function() {
                    $('<link/>', { rel: 'stylesheet', type: 'text/css', href: 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css' }).appendTo('head');
                    showViewerTbl();
                });
            }
        } else if (['pdf', 'txt'].indexOf(ext) > -1) {
            var showViewerPDFTbl = function() {
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
            showViewerPDFTbl();
        } else {
            window.open(fileUrl, '_blank');
        }
    });

    // ── Single Document Deletion ───────────────────────────────────────────
    $(document).on('click', '.btn-delete-single-doc', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var index = $(this).data('index');
        
        Swal.fire({
            title: 'Delete Document?',
            text: "This specific document version will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo site_url("Task/delete_document"); ?>',
                    type: 'POST',
                    data: { id: id, index: index },
                    success: function(res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // ── Add More Document ──────────────────────────────────────────────────
    $(document).on('click', '.btn-upload-more-doc', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#hidden-upload-more').data('id', id).click();
    });

    $(document).on('change', '#hidden-upload-more', function() {
        var id = $(this).data('id');
        var file = this.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('id', id);
        formData.append('document', file);

        Swal.fire({
            title: 'Uploading...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '<?php echo site_url("Task/upload_additional_document"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
        
        $(this).val('');
    });

    // ── Complete Task directly from list ──────────────────────────────────────
    $(document).on('click', '.btn-task-complete', function(e) {
        e.preventDefault();
        var taskId = $(this).data('task');
        Swal.fire({
            title: 'Complete Task?',
            text: "Are you sure you want to mark this task as completed?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#27ae60',
            confirmButtonText: 'Yes, Complete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo site_url("task-complete"); ?>',
                    type: 'POST',
                    data: { task_id: taskId },
                    dataType: 'json',
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Completed!', 'Task has been marked as complete.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', res.message || 'Could not complete task.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Server error. Please try again.', 'error');
                    }
                });
            }
        });
    });

});
</script>

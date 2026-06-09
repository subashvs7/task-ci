<script>
jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    // ── Cascading Dropdowns for Add/Edit Modals ──────────────────────────────────
    function ajaxLoadEpics(projectId, epicSelectSelector, selectedEpicId, storySelectSelector, selectedStoryId) {
        var $epic = $(epicSelectSelector);
        var $story = $(storySelectSelector);
        $epic.html('<option value="">-- Select Epic --</option>');
        $story.html('<option value="">-- Select Story --</option>');
        if (!projectId) return;

        $.ajax({
            url: '<?php echo site_url("get-epics-by-project"); ?>',
            type: 'GET',
            data: { project_id: projectId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">-- Select Epic --</option>';
                    $.each(res.epics, function(i, e) {
                        html += '<option value="' + e.epic_id + '">' + e.name + '</option>';
                    });
                    $epic.html(html);
                    if (selectedEpicId) {
                        $epic.val(selectedEpicId);
                        ajaxLoadStories(selectedEpicId, storySelectSelector, selectedStoryId);
                    }
                }
            }
        });
    }

    function ajaxLoadStories(epicId, storySelectSelector, selectedStoryId) {
        var $story = $(storySelectSelector);
        $story.html('<option value="">-- Select Story --</option>');
        if (!epicId) return;

        $.ajax({
            url: '<?php echo site_url("get-stories-by-epic"); ?>',
            type: 'GET',
            data: { epic_id: epicId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">-- Select Story --</option>';
                    $.each(res.stories, function(i, s) {
                        html += '<option value="' + s.story_id + '">' + s.name + '</option>';
                    });
                    $story.html(html);
                    if (selectedStoryId) {
                        $story.val(selectedStoryId);
                    }
                }
            }
        });
    }

    // Add modal change events
    $(document).on('change', '#add_task_project', function(e, data) {
        if (data && data.programmatic) return;
        ajaxLoadEpics($(this).val(), '#add_task_epic', null, '#add_task_story', null);
    });

    $(document).on('change', '#add_task_epic', function(e, data) {
        if (data && data.programmatic) return;
        ajaxLoadStories($(this).val(), '#add_task_story', null);
    });

    // Edit modal change events
    $(document).on('change', '#edit_project_id', function(e, data) {
        if (data && data.programmatic) return;
        ajaxLoadEpics($(this).val(), '#edit_task_epic', null, '#edit_task_story', null);
    });

    $(document).on('change', '#edit_task_epic', function(e, data) {
        if (data && data.programmatic) return;
        ajaxLoadStories($(this).val(), '#edit_task_story', null);
    });

    // ── Populate Edit Task modal ──────────────────────────────────────────────
    $(document).on('click', '.btn-edit-task', function () {
        var $b = $(this);
        var projectId = $b.data('project');
        var epicId = $b.data('epic');
        var storyId = $b.data('story');

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

        // Trigger project update programmatically
        $('#edit_project_id').val(projectId).trigger('change.select2').trigger('change', [{ programmatic: true }]);
        $('#edit_assigned_to').val($b.data('assigned')).trigger('change.select2');

        // Load cascading epics and stories
        ajaxLoadEpics(projectId, '#edit_task_epic', epicId, '#edit_task_story', storyId);

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
        var docs = $(this).data('document');
        
        if (typeof docs === 'string') {
            if (docs.indexOf('[') === 0) {
                try { docs = JSON.parse(docs); } catch(e) { docs = []; }
            } else {
                var fileUrl = '<?php echo base_url("uploads/tasks/"); ?>' + docs;
                docs = [{ version: 'v1', path: fileUrl, name: docs, date: '' }];
            }
        }
        
        if (!docs) docs = [];
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
            '.doc-gallery { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 15px; justify-content: center; }' +
            '.doc-gallery-item { position: relative; width: 110px; height: 110px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fdfdfd; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.2s ease; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-decoration: none !important; }' +
            '.doc-gallery-item:hover { border-color: #3498db; box-shadow: 0 4px 10px rgba(52,152,219,0.15); transform: translateY(-2px); background: #fff; }' +
            '.doc-gallery-icon { font-size: 36px; color: #e74c3c; margin-bottom: 8px; }' +
            '.doc-gallery-label { font-size: 11px; color: #555; font-weight: 600; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: center; }' +
            '.doc-gallery-actions { position: absolute; top: 5px; right: 5px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s ease; }' +
            '.doc-gallery-item:hover .doc-gallery-actions { opacity: 1; }' +
            '.doc-gallery-btn { background: rgba(255,255,255,0.95); border: 1px solid #ddd; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer; transition: all 0.2s ease; padding: 0; line-height: 1; }' +
            '.doc-gallery-btn:hover { background: #fff; border-color: #3498db; color: #3498db; }' +
            '.doc-gallery-btn.btn-delete { color: #e74c3c; }' +
            '.doc-gallery-btn.btn-delete:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }' +
            '.tooltip-inner { max-width: 320px !important; word-break: break-word !important; white-space: normal !important; text-align: center !important; }' +
            '</style>';
        
        if (docs.length === 0) {
            html += '<div style="margin-top:20px; text-align:center; padding:30px 10px; color:#95a5a6;" id="task-docs-container-1">';
            html += '  <i class="fa fa-folder-open-o" style="font-size:48px; color:#bdc3c7; margin-bottom:15px; display:block;"></i>';
            html += '  <p style="font-size:14px; margin-bottom:0;">No documents uploaded yet.</p>';
            html += '</div>';
        } else if (docs.length <= 3) {
            html += '<div style="margin-top:20px;" id="task-docs-container-1">';
            docs.forEach(function(doc, index) {
                var ext = (doc.path || '').split('.').pop().toLowerCase();
                var icon = (['jpg','jpeg','png','gif'].indexOf(ext) > -1) ? 'fa-file-image-o' : (['pdf'].indexOf(ext) > -1 ? 'fa-file-pdf-o' : 'fa-file-o');
                html += '<div class="doc-item">';
                html += '  <a href="javascript:void(0);" class="doc-left btn-preview-task-doc" data-file="' + doc.path + '" style="text-decoration:none; flex-grow:1; display:flex; align-items:center;">';
                html += '    <i class="fa ' + icon + ' doc-icon"></i>';
                html += '    <div class="doc-meta">';
                html += '      <span class="doc-title">' + (doc.version ? doc.version.toUpperCase() + ' - ' : '') + (doc.name || 'Document') + '</span>';
                html += '      <span class="doc-date">' + (doc.date ? '<i class="fa fa-clock-o"></i> ' + doc.date : '') + (doc.uploaded_by ? ' <span style="margin-left:8px; color:#e67e22; font-weight:600;"><i class="fa fa-user"></i> ' + doc.uploaded_by + '</span>' : '') + '</span>';
                html += '    </div>';
                html += '  </a>';
                html += '  <div style="display:flex; align-items:center; gap:10px;">';
                html += '    <a href="javascript:void(0);" class="btn-preview-task-doc" data-file="' + doc.path + '"><i class="fa fa-eye" style="color:#bdc3c7;"></i></a>';
                html += '    <a href="javascript:void(0);" class="btn-delete-single-doc" data-id="' + $(this).data('id') + '" data-type="task" data-index="' + index + '"><i class="fa fa-trash text-danger"></i></a>';
                html += '  </div>';
                html += '</div>';
            }.bind(this));
            html += '</div>';
        } else {
            html += '<div class="doc-gallery" id="task-docs-container-1">';
            docs.forEach(function(doc, index) {
                var ext = (doc.path || '').split('.').pop().toLowerCase();
                var icon = (['jpg','jpeg','png','gif'].indexOf(ext) > -1) ? 'fa-file-image-o' : (['pdf'].indexOf(ext) > -1 ? 'fa-file-pdf-o' : 'fa-file-o');
                var cleanName = (doc.name || 'Document').replace(/^\d{10}_/, '');
                var uploaderHtml = doc.uploaded_by ? '<br><small style="color:#f39c12; font-weight:600;"><i class="fa fa-user"></i> ' + doc.uploaded_by + '</small>' : '';
                var tooltip = '<strong>' + (doc.version ? doc.version.toUpperCase() + ' - ' : '') + '</strong>' + cleanName + (doc.date ? '<br><small style="color:#bdc3c7;"><i class="fa fa-clock-o"></i> ' + doc.date + '</small>' : '') + uploaderHtml;
                
                html += '<div class="doc-gallery-item btn-preview-task-doc" data-file="' + doc.path + '" data-toggle="tooltip" data-html="true" title="' + tooltip.replace(/"/g, '&quot;') + '">';
                html += '  <i class="fa ' + icon + ' doc-gallery-icon"></i>';
                html += '  <span class="doc-gallery-label">' + (doc.version ? doc.version.toUpperCase() : 'DOC') + '</span>';
                html += '  <div class="doc-gallery-actions">';
                html += '    <button type="button" class="doc-gallery-btn btn-delete btn-delete-single-doc" data-id="' + $(this).data('id') + '" data-type="task" data-index="' + index + '" title="Delete Document">';
                html += '      <i class="fa fa-trash"></i>';
                html += '    </button>';
                html += '  </div>';
                html += '</div>';
            }.bind(this));
            html += '</div>';
        }

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
            },
            didOpen: function() {
                $('.swal2-popup [data-toggle="tooltip"]').tooltip({
                    container: 'body',
                    trigger: 'hover'
                });
            },
            willClose: function() {
                $('.swal2-popup [data-toggle="tooltip"]').tooltip('destroy');
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
        e.stopPropagation();
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

    // ── Logged Time Details Popup ─────────────────────────────────────────────
    $(document).on('click', '.btn-logged-time-details', function(e) {
        e.preventDefault();
        var created = $(this).data('created');
        var logged = $(this).data('logged');
        var estimated = $(this).data('estimated');
        var overdue = $(this).data('overdue') == '1';
        var overtime = $(this).data('overtime');
        var isCompleted = $(this).data('is-completed') == '1';
        var durationStr = $(this).data('duration-str');
        var durationLabel = isCompleted ? 'Overall Time Taken' : 'Task Running For';
        var title = $(this).data('title');

        var html = '<div style="text-align: left; font-size: 14px;">';
        
        html += '<div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed #dcdde1; text-align: center;">';
        html += '<span style="color: #7f8c8d; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Task Name</span><br>';
        html += '<span style="color: #2c3e50; font-weight: 700; font-size: 15px;">' + title + '</span></div>';

        html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f2f6;">';
        html += '<span style="color: #7f8c8d; font-weight: 600;">Created Date</span>';
        html += '<span style="color: #2c3e50; font-weight: 500;">' + created + '</span></div>';
        
        html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f2f6;">';
        html += '<span style="color: #7f8c8d; font-weight: 600;">Logged Time</span>';
        html += '<span style="color: #2980b9; font-weight: 700;">' + logged + '</span></div>';
        
        html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f2f6;">';
        html += '<span style="color: #7f8c8d; font-weight: 600;">Estimated Time</span>';
        html += '<span style="color: #2c3e50; font-weight: 500;">' + (estimated !== '0m' ? estimated : 'Not Set') + '</span></div>';
        
        html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; ' + (!overdue ? '' : 'border-bottom: 1px solid #f1f2f6;') + '">';
        html += '<span style="color: #7f8c8d; font-weight: 600;">' + durationLabel + '</span>';
        html += '<span style="color: #27ae60; font-weight: 700;">' + durationStr + '</span></div>';

        if (overdue) {
            html += '<div style="display: flex; justify-content: space-between; padding: 10px 0; background: #fdf0ed; border-radius: 6px; margin-top: 10px;">';
            html += '<span style="color: #c0392b; font-weight: 600; padding-left: 10px;">Over Estimation</span>';
            html += '<span style="color: #c0392b; font-weight: bold; padding-right: 10px;">' + overtime + '</span></div>';
        }
        html += '</div>';

        Swal.fire({
            title: '<div style="font-size: 18px; font-weight: 600; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 5px;"><i class="fa fa-clock-o text-info" style="margin-right: 8px;"></i>Time Details</div>',
            html: html,
            showCloseButton: true,
            showConfirmButton: false,
            width: 380,
            padding: '1.5em',
            customClass: {
                popup: 'swal-modern-popup'
            },
            didOpen: function() {
                $('.swal2-popup').css('border-radius', '12px');
                $('.swal2-title').css('margin-bottom', '0');
            }
        });
    });

    // ── AJAX Task List Filtering ─────────────────────────────────────────────
    function loadFilteredTasks(offset) {
        var projectId = $('#filter_project_id').val();
        var status = $('#filter_f_status').val();
        var priority = $('#filter_f_priority').val();
        var assignedTo = $('#filter_assigned_to').val();

        // Show spinner inside tbody
        $('#task_list_tbody').html('<tr><td colspan="15" class="text-center text-muted" style="padding:30px;"><i class="fa fa-spinner fa-spin"></i> Loading tasks...</td></tr>');

        $.ajax({
            url: '<?php echo site_url("get-tasks-ajax"); ?>',
            type: 'GET',
            data: {
                project_id: projectId,
                f_status: status,
                f_priority: priority,
                assigned_to: assignedTo,
                offset: offset || 0
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#task_list_tbody').html(res.html);
                    $('.box-footer.clearfix').html(res.pagination);
                    bindPaginationClicks();
                } else {
                    $('#task_list_tbody').html('<tr><td colspan="15" class="text-center text-danger" style="padding:30px;">Failed to load tasks.</td></tr>');
                }
            },
            error: function() {
                $('#task_list_tbody').html('<tr><td colspan="15" class="text-center text-danger" style="padding:30px;">Error loading tasks.</td></tr>');
            }
        });
    }

    // Bind AJAX pagination links click interceptor
    function bindPaginationClicks() {
        $('.box-footer.clearfix').find('a.page-link').off('click').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url) {
                var urlObj = new URL(url, window.location.origin);
                var offset = urlObj.searchParams.get('offset') || 0;
                loadFilteredTasks(offset);
            }
        });
    }

    // Wire up filter triggers
    $(document).on('change', '#filter_project_id, #filter_f_status, #filter_f_priority, #filter_assigned_to', function() {
        loadFilteredTasks(0);
    });

    // Reset filters handler
    $(document).on('click', '#btn_reset_filters', function() {
        $('#filter_project_id').val('').trigger('change.select2');
        $('#filter_f_status').val('');
        $('#filter_f_priority').val('');
        $('#filter_assigned_to').val('').trigger('change.select2');

        loadFilteredTasks(0);
    });

    // Initialize pagination handlers on page load
    bindPaginationClicks();

});
</script>

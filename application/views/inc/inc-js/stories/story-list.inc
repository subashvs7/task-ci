<script>
jQuery(function ($) {
    // ── Select2 Init ──────────────────────────────────────────────────
    // Fix Select2 search inside Bootstrap modals:
    // Footer already initializes all .select2 globally. We must destroy
    // the modal ones first, then reinit with dropdownParent so search works.
    $('.modal').each(function () {
        var $modal = $(this);
        $modal.find('.select2').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                width: '100%',
                dropdownParent: $modal
            });
        });
    });

    // Also re-apply when modal is shown (handles edge cases)
    $(document).on('shown.bs.modal', '.modal', function () {
        var $modal = $(this);
        $modal.find('.select2').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                width: '100%',
                dropdownParent: $modal
            });
        });
    });




    // Helper to load epics for a project in Edit modal
    function loadEditEpics(projectId, selectedEpicId) {
        var $epicSelect = $('#edit_story_epic');
        var $modal = $('#editStoryModal');
        $.ajax({
            url: '<?php echo site_url("get-epics-by-project"); ?>',
            type: 'GET',
            data: { project_id: projectId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">-- No Epic --</option>';
                    $.each(res.epics, function(i, e) {
                        html += '<option value="' + e.epic_id + '" data-creator="' + (e.creator_name || '') + '">' + e.name + '</option>';
                    });
                    $epicSelect.html(html);
                    if (selectedEpicId) {
                        $epicSelect.val(selectedEpicId);
                    }
                    $epicSelect.select2({
                        width: '100%',
                        dropdownParent: $modal
                    }).trigger('change.select2');
                }
            }
        });
    }

    // Edit Project change listener
    $(document).on('change', '#edit_story_project', function(e, data) {
        if (data && data.programmatic) {
            return;
        }
        var projectId = $(this).val();
        var $epicSelect = $('#edit_story_epic');
        $epicSelect.html('<option value="">-- No Epic --</option>').trigger('change.select2');
        if (projectId) {
            loadEditEpics(projectId, null);
        }
    });

    $(document).on('click', '.btn-edit-story', function () {
        var $b = $(this);
        var storyId = $b.data('id');
        var name = $b.data('name');
        var desc = $b.data('description');
        var status = $b.data('status');
        var priority = $b.data('priority');
        var assignee = $b.data('assignee');
        var projectId = $b.data('project');
        var epicId = $b.data('epic');

        $('#edit_story_id').val(storyId);
        $('#edit_story_name').val(name);
        $('#edit_story_desc').val(desc);
        $('#edit_story_status').val(status);
        $('#edit_story_priority').val(priority);
        $('#edit_story_assignee').val(assignee).trigger('change');

        var $projSelect = $('#edit_story_project');
        var $epicSelect = $('#edit_story_epic');

        $projSelect.html('<option value="">-- Select Project --</option>').trigger('change.select2');
        $epicSelect.html('<option value="">-- No Epic --</option>').trigger('change.select2');

        // Load projects via AJAX
        $.ajax({
            url: '<?php echo site_url("get-active-projects"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">-- Select Project --</option>';
                    $.each(res.projects, function(i, p) {
                        html += '<option value="' + p.project_id + '">' + p.name + '</option>';
                    });
                    $projSelect.html(html);
                    $projSelect.val(projectId).trigger('change', [{ programmatic: true }]);
                    $projSelect.select2({
                        width: '100%',
                        dropdownParent: $('#editStoryModal')
                    });

                    if (projectId) {
                        loadEditEpics(projectId, epicId);
                    }
                }
            }
        });

        $('#editStoryModal').modal('show');
    });

    // Add Story Modal AJAX loading
    $(document).on('show.bs.modal', '#addStoryModal', function() {
        var $projSelect = $('#add_story_project');
        var $epicSelect = $('#add_epic_select');

        $projSelect.html('<option value="">-- Select Project --</option>').trigger('change.select2');
        $epicSelect.html('<option value="">-- No Epic --</option>').trigger('change.select2');

        $.ajax({
            url: '<?php echo site_url("get-active-projects"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">-- Select Project --</option>';
                    $.each(res.projects, function(i, p) {
                        html += '<option value="' + p.project_id + '">' + p.name + '</option>';
                    });
                    $projSelect.html(html);
                    $projSelect.select2({
                        width: '100%',
                        dropdownParent: $('#addStoryModal')
                    }).trigger('change.select2');
                }
            }
        });
    });

    // Add Project change listener
    $(document).on('change', '#add_story_project', function() {
        var projectId = $(this).val();
        var $epicSelect = $('#add_epic_select');
        $epicSelect.html('<option value="">-- No Epic --</option>').trigger('change.select2');

        if (projectId) {
            $.ajax({
                url: '<?php echo site_url("get-epics-by-project"); ?>',
                type: 'GET',
                data: { project_id: projectId },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        var html = '<option value="">-- No Epic --</option>';
                        $.each(res.epics, function(i, e) {
                            html += '<option value="' + e.epic_id + '" data-creator="' + (e.creator_name || '') + '">' + e.name + '</option>';
                        });
                        $epicSelect.html(html);
                        $epicSelect.select2({
                            width: '100%',
                            dropdownParent: $('#addStoryModal')
                        }).trigger('change.select2');
                    }
                }
            });
        }
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

    $(document).on('click', '.btn-add-subtask', function(e) {
        e.preventDefault();
        $('#st_story_id').val($(this).data('story'));
        $('#st_project_id').val($(this).data('project'));
        $('#st_epic_id').val($(this).data('epic'));
        
        var epicTime = parseInt($(this).data('epic-time'), 10);
        if (!isNaN(epicTime) && epicTime > 0) {
            var hrs = epicTime / 60;
            hrs = Math.round(hrs * 100) / 100;
            $('#st_epic_time_display').text(hrs + (hrs === 1 ? ' hr' : ' hrs'));
        } else {
            $('#st_epic_time_display').text('Not Set by TL');
        }
        
        $('#addSubTaskModal').modal('show');
    });

    $('#addSubTaskModal form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var storyId = $('#st_story_id').val();
        var $btn = $form.find('button[type="submit"]');
        var originalBtnText = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            success: function(res) {
                $('#addSubTaskModal').modal('hide');
                $form[0].reset();
                $btn.html(originalBtnText).prop('disabled', false);

                window.openAccordionStoryId = storyId;
                loadFilteredStories(0);
            },
            error: function() {
                alert('Error saving task.');
                $btn.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-edit-task', function(e) {
        e.preventDefault();
        var $b = $(this);
        $('#et_task_id').val($b.data('id'));
        $('#et_title').val($b.data('title'));
        $('#et_description').val($b.data('description'));
        $('#et_status').val($b.data('status'));
        $('#et_assigned_to').val($b.data('assigned')).trigger('change');
        $('#et_project_id').val($b.data('project'));
        $('#et_epic_id').val($b.data('epic'));
        $('#et_story_id').val($b.data('story'));
        $('#et_type').val($b.data('type'));
        $('#et_priority').val($b.data('priority'));
        $('#et_due_date').val($b.data('due'));
        $('#et_estimate_hours').val($b.data('eh'));
        $('#et_estimate_minutes').val($b.data('em'));
        
        $('#editSubTaskModal').modal('show');
    });

    // ── Task Proofs / Screenshots ──────────────────────────────────
    var activeProofTaskId = null;
    var maxProofs = 5;

    function renderProofGallery(attachments, currentUserId) {
        var $gallery = $('#proof-gallery');
        $gallery.empty();
        
        if (!attachments || attachments.length === 0) {
            $gallery.html('<div class="col-md-12 text-center text-muted" id="proof-empty-state" style="padding:20px;">No proofs uploaded yet.</div>');
            return;
        }

        $.each(attachments, function(i, a) {
            var ext = (a.file_name || '').split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif'].indexOf(ext) > -1) {
                var deleteBtn = '';
                if (a.user_id == currentUserId) {
                    deleteBtn = '<button type="button" class="btn-delete-proof" data-id="' + a.attachment_id + '" style="position:absolute; top:8px; right:20px; background:rgba(231,76,60,0.9); color:#fff; border:none; border-radius:50%; width:24px; height:24px; font-size:14px; line-height:1; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="Delete Proof">&times;</button>';
                }
                
                var html = '<div class="col-xs-6 col-sm-4 col-md-3" style="margin-bottom:15px; position:relative;">' +
                           '<div class="proof-img-container" style="border:1px solid #ddd; border-radius:4px; padding:4px; background:#fff; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.1);">' +
                           '<img src="' + a.url + '" class="img-responsive proof-img" data-url="' + a.url + '" style="height:120px; width:100%; object-fit:cover; border-radius:2px;" title="Uploaded by: ' + (a.uploader_name || 'Unknown') + '">' +
                           deleteBtn +
                           '</div></div>';
                $gallery.append(html);
            }
        });
    }

    function fetchProofs(taskId) {
        $('#proof-gallery').html('<div class="col-md-12 text-center text-muted" style="padding:20px;">Loading...</div>');
        $.ajax({
            url: '<?php echo site_url("General/get_task_attachments"); ?>',
            type: 'GET',
            data: { task_id: taskId },
            success: function(res) {
                if (res.success) {
                    renderProofGallery(res.attachments, res.current_user_id);
                } else {
                    $('#proof-gallery').html('<div class="col-md-12 text-center text-danger">Failed to load proofs.</div>');
                }
            }
        });
    }

    $(document).on('click', '.btn-proof-modal', function(e) {
        e.preventDefault();
        activeProofTaskId = $(this).data('id');
        var mode = $(this).data('mode');
        
        if (mode === 'upload') {
            $('#proof-upload-section').show();
        } else {
            $('#proof-upload-section').hide();
        }
        
        fetchProofs(activeProofTaskId);
        $('#proofModal').modal('show');
    });

    // Handle Viewer.js for proof images
    $(document).on('click', '.proof-img', function() {
        var fileUrl = $(this).data('url');
        var showViewerProof = function() {
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
            showViewerProof();
        } else {
            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js', function() {
                $('<link/>', { rel: 'stylesheet', type: 'text/css', href: 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css' }).appendTo('head');
                showViewerProof();
            });
        }
    });

    // Handle delete proof
    $(document).on('click', '.btn-delete-proof', function(e) {
        e.stopPropagation(); // prevent opening Viewer
        var attachmentId = $(this).data('id');
        
        Swal.fire({
            title: 'Delete Proof?',
            text: "This image will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo site_url("General/delete_attachment"); ?>',
                    type: 'POST',
                    data: { attachment_id: attachmentId },
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

    // Upload logic
    function uploadProofFile(file) {
        var currentCount = $('#proof-gallery .proof-img-container').length;
        if (currentCount >= maxProofs) {
            Swal.fire('Limit Reached', 'You can only upload up to 5 images per task.', 'warning');
            return;
        }

        if (!file.type.match('image.*')) {
            Swal.fire('Invalid File', 'Please paste or select an image file.', 'error');
            return;
        }

        var filename = file.name;
        if (!filename || filename === 'image.png' || filename === 'image.jpg' || filename === 'blob') {
            filename = 'proof-' + Date.now() + (file.type === 'image/jpeg' ? '.jpg' : '.png');
        }

        var formData = new FormData();
        formData.append('task_id', activeProofTaskId);
        formData.append('attachment', file, filename);
        formData.append('description', 'Proof Screenshot');

        $('#proof-upload-progress').show();
        
        $.ajax({
            url: '<?php echo site_url("General/upload_attachment"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#proof-upload-progress').hide();
                if (res.success) {
                    location.reload();
                } else {
                    Swal.fire('Upload Failed', res.message, 'error');
                }
            },
            error: function() {
                $('#proof-upload-progress').hide();
                Swal.fire('Upload Failed', 'Server error occurred.', 'error');
            }
        });
    }

    // Drag and drop & Click handling
    $('#proof-dropzone').on('click', function(e) {
        if (e.target.id !== 'proof-file-input') {
            $('#proof-file-input').click();
        }
    });

    $('#proof-file-input').on('change', function(e) {
        var files = e.target.files;
        if (files && files.length > 0) {
            for(var i=0; i<files.length; i++){
                uploadProofFile(files[i]);
            }
            $(this).val(''); // Reset
        }
    });
    
    // Drag and drop events
    var $dropzone = $('#proof-dropzone');
    $dropzone.on('dragenter dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#d2d7d3');
    });
    $dropzone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#ecf0f1');
    });
    $dropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css('background', '#ecf0f1');
        var files = e.originalEvent.dataTransfer.files;
        if (files && files.length > 0) {
            for(var i=0; i<files.length; i++){
                uploadProofFile(files[i]);
            }
        }
    });

    // ── Page Filter AJAX loading ─────────────────────────────────────
    function loadFilterEpics(projectId, selectedEpicId, callback) {
        var $epicSelect = $('#filter_story_epic');
        $.ajax({
            url: '<?php echo site_url("get-epics-by-project"); ?>',
            type: 'GET',
            data: { project_id: projectId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">All Epics</option>';
                    $.each(res.epics, function(i, e) {
                        html += '<option value="' + e.epic_id + '">' + e.name + '</option>';
                    });
                    $epicSelect.html(html);
                    if (selectedEpicId) {
                        $epicSelect.val(selectedEpicId);
                    }
                    $epicSelect.trigger('change.select2');
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            }
        });
    }

    // Helper to load filtered stories via AJAX
    function loadFilteredStories(offset) {
        var projectId = $('#filter_story_project').val();
        var epicId = $('#filter_story_epic').val();
        var creatorId = $('#filter_story_creator').val();
        
        // Show loading state in tbody
        $('#story_list_tbody').html('<tr><td colspan="11" class="text-center text-muted" style="padding:30px;"><i class="fa fa-spinner fa-spin"></i> Loading stories...</td></tr>');

        $.ajax({
            url: '<?php echo site_url("get-stories-ajax"); ?>',
            type: 'GET',
            data: {
                project_id: projectId,
                epic_id: epicId,
                creator_id: creatorId,
                offset: offset || 0
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#story_list_tbody').html(res.html);
                    $('.box-footer.clearfix').html(res.pagination);
                    bindPaginationClicks();
                    
                    if (window.openAccordionStoryId) {
                        var $tr = $('.btn-edit-story[data-id="' + window.openAccordionStoryId + '"]').closest('tr');
                        if ($tr.length) {
                            $tr.next('.sub-tasks-row').show();
                        }
                        window.openAccordionStoryId = null;
                    }
                } else {
                    $('#story_list_tbody').html('<tr><td colspan="11" class="text-center text-danger" style="padding:30px;">Failed to load stories.</td></tr>');
                }
            },
            error: function() {
                $('#story_list_tbody').html('<tr><td colspan="11" class="text-center text-danger" style="padding:30px;">Error occurred while loading stories.</td></tr>');
            }
        });
    }

    // Bind AJAX pagination clicks
    function bindPaginationClicks() {
        $('.box-footer.clearfix').find('a.page-link').off('click').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url) {
                var urlObj = new URL(url, window.location.origin);
                var offset = urlObj.searchParams.get('offset') || 0;
                loadFilteredStories(offset);
            }
        });
    }

    // Load active projects on page load
    (function initFilterDropdowns() {
        var $projSelect = $('#filter_story_project');
        var $epicSelect = $('#filter_story_epic');
        
        $projSelect.html('<option value="">All Projects</option>').trigger('change.select2');
        $epicSelect.html('<option value="">All Epics</option>').trigger('change.select2');

        $.ajax({
            url: '<?php echo site_url("get-active-projects"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    var html = '<option value="">All Projects</option>';
                    $.each(res.projects, function(i, p) {
                        html += '<option value="' + p.project_id + '">' + p.name + '</option>';
                    });
                    $projSelect.html(html);

                    var initialProj = window.initialFilterProject;
                    var initialEpic = window.initialFilterEpic;

                    if (initialProj) {
                        $projSelect.val(initialProj).trigger('change', [{ programmatic: true }]);
                        $projSelect.trigger('change.select2');
                        loadFilterEpics(initialProj, initialEpic);
                    } else {
                        $projSelect.trigger('change.select2');
                    }
                }
            }
        });
        bindPaginationClicks();
    })();

    // Filter Project change listener
    $(document).on('change', '#filter_story_project', function(e, data) {
        if (data && data.programmatic) {
            return;
        }
        var projectId = $(this).val();
        var $epicSelect = $('#filter_story_epic');
        $epicSelect.html('<option value="">All Epics</option>').trigger('change.select2');
        
        if (projectId) {
            loadFilterEpics(projectId, null, function() {
                loadFilteredStories(0);
            });
        } else {
            loadFilteredStories(0);
        }
    });

    // Filter Epic change listener
    $(document).on('change', '#filter_story_epic', function(e, data) {
        if (data && data.programmatic) {
            return;
        }
        loadFilteredStories(0);
    });

    // Filter Creator change listener
    $(document).on('change', '#filter_story_creator', function(e, data) {
        if (data && data.programmatic) {
            return;
        }
        loadFilteredStories(0);
    });

    // Reset Filters listener
    $(document).on('click', '#btn_reset_filters', function() {
        $('#filter_story_project').val('').trigger('change', [{ programmatic: true }]);
        $('#filter_story_epic').html('<option value="">All Epics</option>').val('').trigger('change', [{ programmatic: true }]);
        $('#filter_story_creator').val('').trigger('change', [{ programmatic: true }]);
        
        $('#filter_story_project').trigger('change.select2');
        $('#filter_story_epic').trigger('change.select2');
        $('#filter_story_creator').trigger('change.select2');

        loadFilteredStories(0);
    });

});
</script>

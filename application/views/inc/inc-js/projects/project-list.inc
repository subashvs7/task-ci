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
    // Document View (Swal)
    $(document).on('click', '.btn-view-docs', function(e) {
        e.preventDefault();
        var docs = $(this).data('docs');
        if (typeof docs === 'string') {
            try { docs = JSON.parse(docs); } catch(e) { docs = []; }
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
            html += '<div style="margin-top:20px; text-align:center; padding:30px 10px; color:#95a5a6;" id="project-docs-container-1">';
            html += '  <i class="fa fa-folder-open-o" style="font-size:48px; color:#bdc3c7; margin-bottom:15px; display:block;"></i>';
            html += '  <p style="font-size:14px; margin-bottom:0;">No documents uploaded yet.</p>';
            html += '</div>';
        } else if (docs.length <= 3) {
            html += '<div style="margin-top:20px;" id="project-docs-container-1">';
            docs.forEach(function(doc, index) {
                var ext = (doc.path || '').split('.').pop().toLowerCase();
                var icon = (['jpg','jpeg','png','gif'].indexOf(ext) > -1) ? 'fa-file-image-o' : (['pdf'].indexOf(ext) > -1 ? 'fa-file-pdf-o' : 'fa-file-o');
                html += '<div class="doc-item">';
                html += '  <a href="javascript:void(0);" class="doc-left btn-preview-project-doc" data-file="' + doc.path + '" style="text-decoration:none; flex-grow:1; display:flex; align-items:center;">';
                html += '    <i class="fa ' + icon + ' doc-icon"></i>';
                html += '    <div class="doc-meta">';
                html += '      <span class="doc-title">' + (doc.version ? doc.version.toUpperCase() + ' - ' : '') + (doc.name || 'Document') + '</span>';
                html += '      <span class="doc-date">' + (doc.date ? '<i class="fa fa-clock-o"></i> ' + doc.date : '') + (doc.uploaded_by ? ' <span style="margin-left:8px; color:#e67e22; font-weight:600;"><i class="fa fa-user"></i> ' + doc.uploaded_by + '</span>' : '') + '</span>';
                html += '    </div>';
                html += '  </a>';
                html += '  <div style="display:flex; align-items:center; gap:10px;">';
                html += '    <a href="javascript:void(0);" class="btn-preview-project-doc" data-file="' + doc.path + '"><i class="fa fa-eye" style="color:#bdc3c7;"></i></a>';
                html += '    <a href="javascript:void(0);" class="btn-delete-single-doc" data-id="' + $(this).data('id') + '" data-type="project" data-index="' + index + '"><i class="fa fa-trash text-danger"></i></a>';
                html += '  </div>';
                html += '</div>';
            }.bind(this));
            html += '</div>';
        } else {
            html += '<div class="doc-gallery" id="project-docs-container-1">';
            docs.forEach(function(doc, index) {
                var ext = (doc.path || '').split('.').pop().toLowerCase();
                var icon = (['jpg','jpeg','png','gif'].indexOf(ext) > -1) ? 'fa-file-image-o' : (['pdf'].indexOf(ext) > -1 ? 'fa-file-pdf-o' : 'fa-file-o');
                var cleanName = (doc.name || 'Document').replace(/^\d{10}_/, '');
                var uploaderHtml = doc.uploaded_by ? '<br><small style="color:#f39c12; font-weight:600;"><i class="fa fa-user"></i> ' + doc.uploaded_by + '</small>' : '';
                var tooltip = '<strong>' + (doc.version ? doc.version.toUpperCase() + ' - ' : '') + '</strong>' + cleanName + (doc.date ? '<br><small style="color:#bdc3c7;"><i class="fa fa-clock-o"></i> ' + doc.date + '</small>' : '') + uploaderHtml;
                
                html += '<div class="doc-gallery-item btn-preview-project-doc" data-file="' + doc.path + '" data-toggle="tooltip" data-html="true" title="' + tooltip.replace(/"/g, '&quot;') + '">';
                html += '  <i class="fa ' + icon + ' doc-gallery-icon"></i>';
                html += '  <span class="doc-gallery-label">' + (doc.version ? doc.version.toUpperCase() : 'DOC') + '</span>';
                html += '  <div class="doc-gallery-actions">';
                html += '    <button type="button" class="doc-gallery-btn btn-delete btn-delete-single-doc" data-id="' + $(this).data('id') + '" data-type="project" data-index="' + index + '" title="Delete Document">';
                html += '      <i class="fa fa-trash"></i>';
                html += '    </button>';
                html += '  </div>';
                html += '</div>';
            }.bind(this));
            html += '</div>';
        }

        html += '<div style="margin-top:20px; text-align:center;">';
        html += '  <button class="btn btn-sm btn-primary btn-upload-more-doc" data-id="' + $(this).data('id') + '" data-type="project"><i class="fa fa-plus"></i> Add More Document</button>';
        html += '  <input type="file" id="hidden-upload-more" style="display:none;" accept="application/pdf,image/*">';
        html += '</div>';

        Swal.fire({
            title: '<span style="color:#2c3e50; font-weight:600;"><i class="fa fa-folder-open-o" style="color:#f39c12; margin-right:8px;"></i> Project Documents</span>',
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

    $(document).on('click', '.btn-preview-project-doc', function(e) {
        e.preventDefault();
        var fileUrl = $(this).data('file');
        var ext = fileUrl.split('.').pop().toLowerCase();
        
        if (['jpg','jpeg','png','gif'].indexOf(ext) > -1) {
            var showViewerProj = function() {
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
                showViewerProj();
            } else {
                $.getScript('https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js', function() {
                    $('<link/>', { rel: 'stylesheet', type: 'text/css', href: 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css' }).appendTo('head');
                    showViewerProj();
                });
            }
        } else if (['pdf', 'txt'].indexOf(ext) > -1) {
            var showViewerProjPDF = function() {
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
            showViewerProjPDF();
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
                    url: '<?php echo site_url("Project/delete_document"); ?>',
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
            url: '<?php echo site_url("Project/upload_additional_document"); ?>',
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

});
</script>

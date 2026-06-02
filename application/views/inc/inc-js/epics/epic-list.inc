<script>
jQuery(function ($) {
    // Fix Select2 search inside Bootstrap modals
    function initModalSelect2($modal) {
        $modal.find('.select2').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                width: '100%',
                dropdownParent: $modal
            });
        });
    }

    $('.modal').each(function () {
        initModalSelect2($(this));
    });

    $(document).on('shown.bs.modal', '.modal', function () {
        initModalSelect2($(this));
    });


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

    // Document View (Swal)
    $(document).on('click', '.btn-view-docs', function(e) {
        e.preventDefault();
        var docs = $(this).data('docs');
        if (typeof docs === 'string') {
            try { docs = JSON.parse(docs); } catch(e) { docs = []; }
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
        
        html += '<div style="margin-top:20px;" id="epic-docs-container-1">'; // Just a wrapper
        docs.forEach(function(doc, index) {
            var ext = (doc.path || '').split('.').pop().toLowerCase();
            var icon = (['jpg','jpeg','png','gif'].indexOf(ext) > -1) ? 'fa-file-image-o' : (['pdf'].indexOf(ext) > -1 ? 'fa-file-pdf-o' : 'fa-file-o');
            html += '<div class="doc-item">';
            html += '  <a href="javascript:void(0);" class="doc-left btn-preview-epic-doc" data-file="' + doc.path + '" style="text-decoration:none; flex-grow:1; display:flex; align-items:center;">';
            html += '    <i class="fa ' + icon + ' doc-icon"></i>';
            html += '    <div class="doc-meta">';
            html += '      <span class="doc-title">' + (doc.version ? doc.version.toUpperCase() + ' - ' : '') + (doc.name || 'Document') + '</span>';
            html += '      <span class="doc-date">' + (doc.date ? '<i class="fa fa-clock-o"></i> ' + doc.date : '') + '</span>';
            html += '    </div>';
            html += '  </a>';
            html += '  <div style="display:flex; align-items:center; gap:10px;">';
            html += '    <a href="javascript:void(0);" class="btn-preview-epic-doc" data-file="' + doc.path + '"><i class="fa fa-eye" style="color:#bdc3c7;"></i></a>';
            html += '    <a href="javascript:void(0);" class="btn-delete-single-doc" data-id="' + $(this).data('id') + '" data-type="epic" data-index="' + index + '"><i class="fa fa-trash text-danger"></i></a>';
            html += '  </div>';
            html += '</div>';
        }.bind(this));
        html += '</div>';

        html += '<div style="margin-top:20px; text-align:center;">';
        html += '  <button class="btn btn-sm btn-primary btn-upload-more-doc" data-id="' + $(this).data('id') + '" data-type="epic"><i class="fa fa-plus"></i> Add More Document</button>';
        html += '  <input type="file" id="hidden-upload-more" style="display:none;" accept="application/pdf,image/*">';
        html += '</div>';

        Swal.fire({
            title: '<span style="color:#2c3e50; font-weight:600;"><i class="fa fa-folder-open-o" style="color:#f39c12; margin-right:8px;"></i> Epic Documents</span>',
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

    $(document).on('click', '.btn-preview-epic-doc', function(e) {
        e.preventDefault();
        var fileUrl = $(this).data('file');
        var ext = fileUrl.split('.').pop().toLowerCase();
        
        if (['jpg','jpeg','png','gif'].indexOf(ext) > -1) {
            var showViewerEpic = function() {
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
                showViewerEpic();
            } else {
                $.getScript('https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js', function() {
                    $('<link/>', { rel: 'stylesheet', type: 'text/css', href: 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css' }).appendTo('head');
                    showViewerEpic();
                });
            }
        } else if (['pdf', 'txt'].indexOf(ext) > -1) {
            var showViewerEpicPDF = function() {
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
            showViewerEpicPDF();
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
                    url: '<?php echo site_url("Epic/delete_document"); ?>',
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
            url: '<?php echo site_url("Epic/upload_additional_document"); ?>',
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

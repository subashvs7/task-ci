<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    $('.btn-edit-handler').on('click', function() {
        var id = $(this).data('id');
        var project = $(this).data('project');
        var leader = $(this).data('leader');
        var due = $(this).data('due');
        var status = $(this).data('status');
        var notes = $(this).data('notes');

        $('#edit_handler_id').val(id);
        $('#edit_handler_project').val(project).trigger('change');
        $('#edit_handler_leader').val(leader).trigger('change');
        $('#edit_handler_due').val(due);
        $('#edit_handler_status').val(status);
        $('#edit_handler_notes').val(notes);

        $('#editHandlerModal').modal('show');
    });

    $('.del_record').on('click', function() {
        if (!confirm('Are you sure you want to delete this project delegation?')) return;
        var btn = $(this);
        $.ajax({
            url: '<?php echo site_url("delete-record"); ?>',
            type: 'POST',
            data: {
                tbl: btn.data('tbl'),
                col: btn.data('col'),
                id: btn.val()
            },
            success: function(resp) {
                var r = JSON.parse(resp);
                if (r.success) {
                    location.reload();
                } else {
                    alert(r.message);
                }
            }
        });
    });

    // Update project deadline info on dropdown change
    $('select[name="project_id"]').on('change', function() {
        var opt = $(this).find('option:selected');
        var endDate = opt.data('enddate');
        var days = opt.data('days');
        var modal = $(this).closest('.modal');
        var infoDiv = modal.find('.project-deadline-info');
        
        if (endDate) {
            infoDiv.find('.lbl-project-end-date').text(endDate);
            
            // Calculate remaining days instead of just using DB limit if DB limit isn't calculated
            if (endDate) {
                var parts = endDate.split('-'); // dd-mm-yyyy
                var end = new Date(parts[2], parts[1] - 1, parts[0]);
                var today = new Date();
                today.setHours(0,0,0,0);
                var diffTime = end.getTime() - today.getTime();
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                infoDiv.find('.lbl-project-days').text(diffDays > 0 ? diffDays : 0);
            } else {
                infoDiv.find('.lbl-project-days').text('-');
            }
            
            infoDiv.show();
        } else {
            infoDiv.hide();
        }
    });
});
</script>

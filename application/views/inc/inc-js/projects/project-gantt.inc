<!-- DHTMLX Gantt CSS -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<style>
  .gantt_task_progress { background-color: rgba(33, 33, 33, 0.17); }
</style>

<!-- DHTMLX Gantt JS -->
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<script>
$(document).ready(function() {
    
    // Initialize DHTMLX Gantt
    gantt.config.readonly = true; // Make it read-only initially since we manage data elsewhere
    gantt.config.date_format = "%Y-%m-%d %H:%i";
    gantt.config.columns = [
        {name: "text", label: "Task name", width: "*", tree: true},
        {name: "start_date", label: "Start time", align: "center"},
        {name: "duration", label: "Duration", align: "center"}
    ];
    gantt.init("gantt_here");

    // Handle Project Selection
    $('#project_selector').on('change', function() {
        var projectId = $(this).val();
        if (projectId) {
            $('#no_project_msg').hide();
            $('#gantt_here').show();
            
            // Show loading state
            gantt.clearAll();
            
            // Fetch data
            $.ajax({
                url: baseUrl + 'get-gantt-data/' + projectId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    gantt.parse(response);
                },
                error: function() {
                    alert('Failed to load project timeline data.');
                }
            });
        } else {
            $('#gantt_here').hide();
            $('#no_project_msg').show();
            gantt.clearAll();
        }
    });

});
</script>

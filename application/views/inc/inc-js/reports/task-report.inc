<script src="<?php echo base_url('asset/bower_components/chart.js/Chart.js'); ?>"></script>
<script>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    filename = filename?filename+'.xls':'excel_data.xls';
    
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}

jQuery(function ($) {
    $('.select2').select2({ width: '100%' });

    if ($('#project-tree-table').length > 0) {
        $('#project-tree-table').DataTable({
            'paging'      : true,
            'lengthChange': true,
            'searching'   : true,
            'ordering'    : false, // Keep tree hierarchy sorted
            'info'        : true,
            'autoWidth'   : false,
            'pageLength'  : 50
        });
    }

    // Chart Settings
    var pieOptions = {
        segmentShowStroke: true,
        segmentStrokeColor: "#fff",
        segmentStrokeWidth: 1,
        percentageInnerCutout: 0, 
        animationSteps: 100,
        animationEasing: "easeOutBounce",
        animateRotate: true,
        animateScale: false,
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: true,
            position: 'right'
        }
    };

    // Prepare Status Data
    <?php
    $sl = TASK_STATUS_OPT;
    $statusData = [];
    $statusColors = ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'];
    foreach ($tasks_by_status as $i => $r) {
        $label = isset($sl[$r['status']]) ? $sl[$r['status']] : $r['status'];
        $color = isset($statusColors[$i % count($statusColors)]) ? $statusColors[$i % count($statusColors)] : '#000';
        $statusData[] = [
            'value' => (int)$r['cnt'],
            'color' => $color,
            'highlight' => $color,
            'label' => $label
        ];
    }
    ?>
    if ($('#chartStatus').length) {
        var statusData = <?php echo json_encode($statusData); ?>;
        var ctxStatus = $('#chartStatus').get(0).getContext('2d');
        new Chart(ctxStatus).Pie(statusData, pieOptions);
    }

    // Prepare Priority Data (Bar Chart)
    <?php
    $pl = TASK_PRIORITY_OPT;
    $priorityLabels = [];
    $priorityDataArr = [];
    foreach ($tasks_by_priority as $r) {
        $priorityLabels[] = isset($pl[$r['priority']]) ? $pl[$r['priority']] : $r['priority'];
        $priorityDataArr[] = (int)$r['cnt'];
    }
    ?>
    if ($('#chartPriority').length) {
        var barChartData = {
            labels: <?php echo json_encode($priorityLabels); ?>,
            datasets: [
                {
                    fillColor: "#f39c12",
                    strokeColor: "#f39c12",
                    pointColor: "#f39c12",
                    pointStrokeColor: "#c1c7d1",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: <?php echo json_encode($priorityDataArr); ?>
                }
            ]
        };
        var barOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scaleBeginAtZero: true
        };
        var ctxPriority = $('#chartPriority').get(0).getContext('2d');
        new Chart(ctxPriority).Bar(barChartData, barOptions);
    }

    // Prepare Tasks By Date Data (Line Chart)
    <?php
    $dateLabels = [];
    $dateDataArr = [];
    
    $date_counts = [];
    foreach ($tasks_by_date as $r) {
        $date_counts[$r['t_date']] = (int)$r['cnt'];
    }

    if (!empty($f_date_from) && !empty($f_date_to)) {
        $start_date = strtotime($f_date_from);
        $end_date = strtotime($f_date_to);
    } else if (!empty($f_date_from)) {
        $start_date = strtotime($f_date_from);
        $end_date = time();
    } else if (!empty($f_date_to)) {
        $start_date = strtotime('-6 days', strtotime($f_date_to));
        $end_date = strtotime($f_date_to);
    } else {
        $start_date = strtotime('-6 days');
        $end_date = time();
    }
    
    // Safety cap to prevent too many points
    if (($end_date - $start_date) > 90 * 86400) {
        $start_date = $end_date - 90 * 86400;
    }
    
    $current_date = $start_date;
    while ($current_date <= $end_date) {
        $d_str = date('Y-m-d', $current_date);
        $dateLabels[] = date('M d', $current_date);
        $dateDataArr[] = isset($date_counts[$d_str]) ? $date_counts[$d_str] : 0;
        $current_date = strtotime('+1 day', $current_date);
    }
    ?>
    if ($('#chartDate').length) {
        var lineChartData = {
            labels: <?php echo json_encode($dateLabels); ?>,
            datasets: [
                {
                    fillColor: "rgba(0,166,90,0.2)",
                    strokeColor: "#00a65a",
                    pointColor: "#00a65a",
                    pointStrokeColor: "#00a65a",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "#00a65a",
                    data: <?php echo json_encode($dateDataArr); ?>
                }
            ]
        };
        var lineOptions = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: true,
            scaleBeginAtZero: true
        };
        var ctxDate = $('#chartDate').get(0).getContext('2d');
        new Chart(ctxDate).Line(lineChartData, lineOptions);
    }

});
</script>

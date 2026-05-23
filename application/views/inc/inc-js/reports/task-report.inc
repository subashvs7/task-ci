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

    <?php if ($f_project): ?>
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
    foreach ($tasks_by_date as $r) {
        $dateLabels[] = date('M d', strtotime($r['t_date']));
        $dateDataArr[] = (int)$r['cnt'];
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
    <?php endif; ?>
});
</script>

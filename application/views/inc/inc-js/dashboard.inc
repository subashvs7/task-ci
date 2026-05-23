<script src="<?php echo base_url('asset/bower_components/moment/moment.js'); ?>"></script>
<script src="<?php echo base_url('asset/bower_components/fullcalendar/dist/fullcalendar.min.js'); ?>"></script>
<script src="<?php echo base_url('asset/bower_components/chart.js/Chart.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url('asset/bower_components/fullcalendar/dist/fullcalendar.min.css'); ?>">

<script>
jQuery(function ($) {
    if($('.select2').length) {
        $('.select2').select2({ width: '100%' });
    }

    // Animate info-box numbers
    $('.info-box-number').each(function () {
        var $el = $(this);
        var val = parseInt($el.text()) || 0;
        $({ n: 0 }).animate({ n: val }, {
            duration: 800,
            step: function () { $el.text(Math.floor(this.n)); },
            complete: function () { $el.text(val); }
        });
    });

    // 1. Initialize FullCalendar
    var eventsData = [
      <?php if(isset($calendar_events)): ?>
      <?php foreach($calendar_events as $ev): ?>
      <?php 
        $evt = $ev['event_type'];
        $color = '#3498db'; // Task
        if ($evt === 'Project') $color = '#9b59b6';
        else if ($evt === 'Epic') $color = '#e67e22';
        else if ($evt === 'Story') $color = '#1abc9c';
        else if ($evt === 'Sub-task') $color = '#7f8c8d';
        
        $start_date = $ev['created_date'] ? date('Y-m-d', strtotime($ev['created_date'])) : null;
        $due_date = $ev['due_date'] ? date('Y-m-d', strtotime($ev['due_date'])) : null;
        
        $has_start = !empty($start_date);
        $has_due = !empty($due_date);
        $is_same = ($start_date === $due_date);
        
        $est_str = '-';
        if (!empty($ev['estimated_hours'])) {
            if ($ev['event_type'] === 'Epic') {
                $total_mins = (int)$ev['estimated_hours'];
            } else {
                $total_mins = round((float)$ev['estimated_hours'] * 60);
            }
            if ($total_mins > 0) {
                $h = floor($total_mins / 60);
                $m = $total_mins % 60;
                if ($h > 0 && $m > 0) $est_str = $h . 'hrs ' . $m . ' min';
                elseif ($h > 0) $est_str = $h . 'hrs';
                else $est_str = $m . ' min';
            }
        }
      ?>
      
      <?php if ($has_start): ?>
      {
        id: '<?php echo $ev['id']; ?>',
        eventType: '<?php echo $ev['event_type']; ?>',
        title: <?php echo json_encode(($has_due && !$is_same) ? "Start: " . $ev['title'] : $ev['title']); ?>,
        start: '<?php echo $start_date; ?>',
        backgroundColor: '<?php echo $color; ?>',
        borderColor: '<?php echo $color; ?>',
        status: <?php echo json_encode(ucfirst(str_replace('_', ' ', $ev['status']))); ?>,
        priority: <?php echo json_encode(ucfirst($ev['priority'] ?: '-')); ?>,
        assignee: <?php echo json_encode($ev['assignee_name'] ?: 'Unassigned'); ?>,
        assignedBy: <?php echo json_encode(isset($ev['assigned_by_name']) && $ev['assigned_by_name'] ? $ev['assigned_by_name'] : 'System'); ?>,
        est: <?php echo json_encode($est_str); ?>
      },
      <?php endif; ?>

      <?php if ($has_due && !$is_same): ?>
      {
        id: '<?php echo $ev['id']; ?>',
        eventType: '<?php echo $ev['event_type']; ?>',
        title: <?php echo json_encode("Due: " . $ev['title']); ?>,
        start: '<?php echo $due_date; ?>',
        backgroundColor: '#e74c3c', // Due dates highlight in red or keep original? We'll make it slightly redish to stand out as a due date.
        borderColor: '#e74c3c',
        status: <?php echo json_encode(ucfirst(str_replace('_', ' ', $ev['status']))); ?>,
        priority: <?php echo json_encode(ucfirst($ev['priority'] ?: '-')); ?>,
        assignee: <?php echo json_encode($ev['assignee_name'] ?: 'Unassigned'); ?>,
        assignedBy: <?php echo json_encode(isset($ev['assigned_by_name']) && $ev['assigned_by_name'] ? $ev['assigned_by_name'] : 'System'); ?>,
        est: <?php echo json_encode($est_str); ?>
      },
      <?php endif; ?>
      <?php endforeach; ?>
      <?php endif; ?>
    ];

    $('#calendar').fullCalendar({
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
      },
      contentHeight: 350,
      buttonText: {
        today: 'today',
        month: 'month',
        week: 'week',
        day: 'day'
      },
      events: eventsData,
      eventRender: function(event, element) {
        var badgeColor = '#bdc3c7';
        if (event.eventType === 'Project') badgeColor = '#9b59b6';
        if (event.eventType === 'Epic') badgeColor = '#e67e22';
        if (event.eventType === 'Task') badgeColor = '#3498db';
        if (event.eventType === 'Story') badgeColor = '#1abc9c';
        
        var tooltipHtml = '<div style="text-align:left; font-size: 12px; line-height: 1.4;">' +
                          '<div style="margin-bottom: 3px;"><span style="background:'+badgeColor+'; color:#fff; font-size:9px; padding:1px 4px; border-radius:3px;">' + event.eventType + '</span></div>' +
                          '<div style="font-weight:bold; margin-bottom: 3px; border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom:3px;">' + event.title + '</div>';
                          
        if (event.eventType !== 'Epic') {
            tooltipHtml += '<strong>Assignee:</strong> ' + event.assignee + '<br>';
        }
        
        tooltipHtml += '<strong>Status:</strong> ' + event.status + '<br>';
        
        if (event.eventType !== 'Project' && event.est !== '-') {
            tooltipHtml += '<strong>Est Time:</strong> ' + event.est + '<br>';
        }
        
        tooltipHtml += '</div>';
        
        element.attr('title', tooltipHtml);
        element.attr('data-toggle', 'tooltip');
        element.attr('data-html', 'true');
        
        // Initialize Bootstrap tooltip
        element.tooltip({
            container: 'body',
            placement: 'top',
            delay: { "show": 100, "hide": 50 }
        });
      },
      eventClick: function(calEvent, jsEvent, view) {
        // Populate modal
        $('#cal_modal_type').text(calEvent.eventType);
        $('#cal_modal_title').text(calEvent.title);
        $('#cal_modal_status').text(calEvent.status);
        $('#cal_modal_priority').text(calEvent.priority);
        $('#cal_modal_assignee').text(calEvent.assignee);
        $('#cal_modal_assigned_by').text(calEvent.assignedBy);
        $('#cal_modal_start').text(calEvent.start.format('YYYY-MM-DD'));
        $('#cal_modal_end').text(calEvent.end ? calEvent.end.subtract(1, 'days').format('YYYY-MM-DD') : calEvent.start.format('YYYY-MM-DD')); // Revert +1 day for display
        $('#cal_modal_est').text(calEvent.est);

        if (calEvent.eventType === 'Project') {
            $('#cal_modal_est_row').hide();
        } else {
            $('#cal_modal_est_row').show();
        }

        if (calEvent.eventType === 'Epic') {
            $('#cal_modal_assignee_row').hide();
        } else {
            $('#cal_modal_assignee_row').show();
        }

        // Header color based on type
        var headerColor = '#34495e';
        if (calEvent.eventType === 'Project') headerColor = 'linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%)';
        if (calEvent.eventType === 'Epic') headerColor = 'linear-gradient(135deg, #d35400 0%, #e67e22 100%)';
        if (calEvent.eventType === 'Task') headerColor = 'linear-gradient(135deg, #2980b9 0%, #3498db 100%)';
        if (calEvent.eventType === 'Story') headerColor = 'linear-gradient(135deg, #16a085 0%, #1abc9c 100%)';
        if (calEvent.eventType === 'Sub-task') headerColor = 'linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%)';
        $('#calendarEventModal .modal-header').css('background', headerColor);

        // Show modal
        $('#calendarEventModal').modal('show');
      }
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

    // Initialize Bootstrap popovers for TL dashboard notes
    $('[data-toggle="popover"]').popover({
        container: 'body',
        html: false,
        trigger: 'hover focus'
    });

});
</script>

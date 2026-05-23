<script src="<?php echo base_url('asset/bower_components/chart.js/Chart.js'); ?>"></script>
<script>
jQuery(function ($) {

    var roleColors = {admin:'#c0392b', manager:'#e67e22', team_leader:'#2980b9', staff:'#7f8c8d'};
    var roleLabels = {admin:'Admin', manager:'Manager', team_leader:'Team Leader', staff:'Staff'};

    // ── Analytics Charts ──────────────────────────────────────────────
    var priorityData = <?php echo json_encode($tasks_by_priority); ?>;
    var typeData = <?php echo json_encode($tasks_by_type); ?>;
    var timeTrendData = <?php echo json_encode($time_logged_trend); ?>;
    var statusData = <?php echo json_encode($tasks_by_status); ?>;
    var assigneeData = <?php echo json_encode($tasks_by_assignee); ?>;
    
    var totalTasks = <?php echo (int)$total_tasks; ?>;
    var doneTasks = <?php echo (int)$done_tasks; ?>;
    var overdueTasks = <?php echo (int)$overdue_tasks; ?>;
    var remainingTasks = Math.max(0, totalTasks - doneTasks - overdueTasks);

    // 1. Status Chart (Pie)
    var statusColors = {'backlog':'#95a5a6','todo':'#3498db','in_progress':'#e67e22','in_review':'#9b59b6','done':'#27ae60','closed':'#7f8c8d'};
    var statusLabels = Object.keys(statusData).map(function(k) { return k.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()); });
    var statusValues = Object.values(statusData);
    var statusBgColors = Object.keys(statusData).map(function(k) { return statusColors[k] || '#ccc'; });

    if ($('#statusChart').length) {
        new Chart($('#statusChart').get(0).getContext('2d'), {
            type: 'pie',
            data: { labels: statusLabels, datasets: [{ data: statusValues, backgroundColor: statusBgColors }] },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'right', labels: { boxWidth: 10, fontSize: 10 } } }
        });
    }

    // 2. Progress Chart (Doughnut)
    if ($('#progressChart').length) {
        new Chart($('#progressChart').get(0).getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Done', 'Overdue', 'Remaining'],
                datasets: [{ data: [doneTasks, overdueTasks, remainingTasks], backgroundColor: ['#27ae60', '#c0392b', '#f1c40f'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'right', labels: { boxWidth: 10, fontSize: 10 } } }
        });
    }

    // 3. Priority Chart (Doughnut)
    var priorityLabels = Object.keys(priorityData).map(function(k) { return k.charAt(0).toUpperCase() + k.slice(1); });
    var priorityValues = Object.values(priorityData);
    var priorityColors = { 'low': '#27ae60', 'medium': '#2980b9', 'high': '#e67e22', 'critical': '#c0392b' };
    var pBgColors = Object.keys(priorityData).map(function(k) { return priorityColors[k] || '#95a5a6'; });

    if ($('#priorityChart').length) {
        new Chart($('#priorityChart').get(0).getContext('2d'), {
            type: 'doughnut',
            data: { labels: priorityLabels, datasets: [{ data: priorityValues, backgroundColor: pBgColors }] },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'right', labels: { boxWidth: 10, fontSize: 10 } } }
        });
    }

    // 4. Type Chart (Bar)
    var typeLabels = Object.keys(typeData).map(function(k) { return k.charAt(0).toUpperCase() + k.slice(1); });
    var typeValues = Object.values(typeData);
    var typeBgColors = Object.keys(typeData).map(function(k, i) {
        var colors = ['#3498db', '#e74c3c', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
        return colors[i % colors.length];
    });

    if ($('#typeChart').length) {
        new Chart($('#typeChart').get(0).getContext('2d'), {
            type: 'bar',
            data: { labels: typeLabels, datasets: [{ label: 'Tasks', data: typeValues, backgroundColor: typeBgColors }] },
            options: { responsive: true, maintainAspectRatio: false, legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }], xAxes: [{ ticks: { fontSize: 10 } }] } }
        });
    }

    // 5. Assignee Chart (Bar)
    var assigneeLabels = Object.keys(assigneeData);
    var assigneeValues = Object.values(assigneeData);
    
    if ($('#assigneeChart').length) {
        new Chart($('#assigneeChart').get(0).getContext('2d'), {
            type: 'horizontalBar',
            data: { labels: assigneeLabels, datasets: [{ label: 'Tasks', data: assigneeValues, backgroundColor: '#e74c3c' }] },
            options: { responsive: true, maintainAspectRatio: false, legend: { display: false }, scales: { xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }], yAxes: [{ ticks: { fontSize: 10 } }] } }
        });
    }

    // 6. Time Logged Chart (Line)
    var trendLabels = Object.keys(timeTrendData);
    var trendValues = Object.values(timeTrendData);

    if ($('#timeLogChart').length) {
        new Chart($('#timeLogChart').get(0).getContext('2d'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Hours Logged',
                    data: trendValues,
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: '#2980b9',
                    pointBackgroundColor: '#2980b9',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, legend: { display: false },
                scales: { 
                    xAxes: [{ gridLines: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 5 } }],
                    yAxes: [{ ticks: { beginAtZero: true } }] 
                }
            }
        });
    }

    // ── Add Member ────────────────────────────────────────────────────
    $('#btnAddMember').on('click', function () {
        var userId     = $('#add_member_user').val();
        var role       = $('#add_member_role').val();
        var projectId  = $(this).data('project-id');

        if (!userId) { alert('Please select a user.'); return; }

        var $btn = $(this).prop('disabled', true).text('Adding…');

        $.ajax({
            url: BASE_URL + 'add-project-member',
            type: 'POST',
            dataType: 'json',
            data: { project_id: projectId, user_id: userId, project_role: role },
            success: function (res) {
                if (!res.success) { alert(res.message); return; }

                var name  = res.name;
                var init  = name.split(' ').slice(0, 2).map(function(w){ return w[0].toUpperCase(); }).join('');
                var rc    = roleColors[res.role] || '#95a5a6';
                var pr    = roleLabels[res.project_role] || res.project_role;

                var card = '<div class="member-card" id="mcard-' + res.member_id + '" '
                    + 'style="display:flex;align-items:center;gap:8px;background:#f8f9fa;border-radius:8px;'
                    + 'padding:8px 14px;border:1px solid #dee2e6;min-width:180px;">'
                    + '<div style="width:36px;height:36px;border-radius:50%;background:' + rc + ';display:flex;'
                    + 'align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">' + init + '</div>'
                    + '<div style="flex:1;">'
                    + '<div style="font-weight:600;font-size:13px;">' + $('<div>').text(name).html() + '</div>'
                    + '<span class="label" style="background:' + rc + ';font-size:10px;">' + pr + '</span>'
                    + '</div>'
                    + '<button class="btn btn-xs btn-default btn-remove-member" data-member-id="' + res.member_id + '" '
                    + 'title="Remove from project" style="opacity:.6;"><i class="fa fa-times"></i></button>'
                    + '</div>';

                $('#membersList').append(card);

                // Remove from dropdown
                $('#add_member_user option[value="' + res.user_id + '"]').remove();
                if ($('#add_member_user option').length <= 1) {
                    $('#addMemberModal').modal('hide');
                    $('[data-target="#addMemberModal"]').hide();
                }
                $('#add_member_user').val('');
                $('#addMemberModal').modal('hide');

                // Update badge count
                var badge = $('.box-warning .badge');
                badge.text(parseInt(badge.text()) + 1);
            },
            error: function () { alert('Server error. Please try again.'); },
            complete: function () { $btn.prop('disabled', false).html('<i class="fa fa-user-plus"></i> Add Member'); }
        });
    });

    // ── Remove Member ─────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-member', function () {
        if (!confirm('Remove this member from the project?')) return;

        var memberId = $(this).data('member-id');
        var $card    = $('#mcard-' + memberId);

        $.ajax({
            url: BASE_URL + 'remove-project-member',
            type: 'POST',
            dataType: 'json',
            data: { member_id: memberId },
            success: function (res) {
                if (!res.success) { alert(res.message); return; }
                $card.fadeOut(300, function () { $(this).remove(); });
                var badge = $('.box-warning .badge');
                badge.text(Math.max(1, parseInt(badge.text()) - 1));
            },
            error: function () { alert('Server error. Please try again.'); }
        });
    });

});
</script>

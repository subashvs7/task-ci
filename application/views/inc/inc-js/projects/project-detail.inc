<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
jQuery(function ($) {

    var roleColors = {admin:'#c0392b', manager:'#e67e22', team_leader:'#2980b9', staff:'#7f8c8d'};
    var roleLabels = {admin:'Admin', manager:'Manager', team_leader:'Team Leader', staff:'Staff'};

    // ── Analytics Charts ──────────────────────────────────────────────
    var priorityData   = <?php echo json_encode($tasks_by_priority); ?>;
    var typeData       = <?php echo json_encode($tasks_by_type); ?>;
    var timeTrendData  = <?php echo json_encode($time_logged_trend); ?>;
    var statusData     = <?php echo json_encode($tasks_by_status); ?>;
    var assigneeData   = <?php echo json_encode($tasks_by_assignee); ?>;

    var totalTasks     = <?php echo (int)$total_tasks; ?>;
    var doneTasks      = <?php echo (int)$done_tasks; ?>;
    var overdueTasks   = <?php echo (int)$overdue_tasks; ?>;
    var remainingTasks = Math.max(0, totalTasks - doneTasks - overdueTasks);

    // 1. Status Chart (Pie)
    var statusColors   = {'backlog':'#95a5a6','todo':'#3498db','in_progress':'#e67e22','in_review':'#9b59b6','done':'#27ae60','closed':'#7f8c8d'};
    var statusLabels   = Object.keys(statusData).map(function(k){ return k.replace(/_/g,' ').replace(/\b\w/g,function(l){return l.toUpperCase();}); });
    var statusValues   = Object.values(statusData);
    var statusBgColors = Object.keys(statusData).map(function(k){ return statusColors[k] || '#ccc'; });

    if ($('#statusChart').length && statusValues.length > 0) {
        new Chart($('#statusChart')[0].getContext('2d'), {
            type: 'pie',
            data: { labels: statusLabels, datasets: [{ data: statusValues, backgroundColor: statusBgColors, borderWidth: 2 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right', labels: { boxWidth: 10, fontSize: 10, padding: 6 } }
            }
        });
    } else if ($('#statusChart').length) {
        $('#statusChart').parent().html('<p class="text-center text-muted" style="padding:40px 0;font-size:12px;"><i class="fa fa-info-circle"></i> No tasks yet</p>');
    }

    // 2. Progress Chart (Doughnut)
    if ($('#progressChart').length) {
        var progressData = [doneTasks, overdueTasks, remainingTasks];
        if (progressData.every(function(v){return v===0;})) {
            $('#progressChart').parent().html('<p class="text-center text-muted" style="padding:40px 0;font-size:12px;"><i class="fa fa-info-circle"></i> No tasks yet</p>');
        } else {
            new Chart($('#progressChart')[0].getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Done', 'Overdue', 'Remaining'],
                    datasets: [{ data: progressData, backgroundColor: ['#27ae60','#c0392b','#f1c40f'], borderWidth: 2 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'right', labels: { boxWidth: 10, fontSize: 10, padding: 6 } }
                }
            });
        }
    }

    // 3. Priority Chart (Doughnut)
    var priorityLabels = Object.keys(priorityData).map(function(k){ return k.charAt(0).toUpperCase()+k.slice(1); });
    var priorityValues = Object.values(priorityData);
    var priorityColMap = { low:'#27ae60', medium:'#2980b9', high:'#e67e22', critical:'#c0392b' };
    var pBgColors      = Object.keys(priorityData).map(function(k){ return priorityColMap[k] || '#95a5a6'; });

    if ($('#priorityChart').length && priorityValues.length > 0) {
        new Chart($('#priorityChart')[0].getContext('2d'), {
            type: 'doughnut',
            data: { labels: priorityLabels, datasets: [{ data: priorityValues, backgroundColor: pBgColors, borderWidth: 2 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right', labels: { boxWidth: 10, fontSize: 10, padding: 6 } }
            }
        });
    } else if ($('#priorityChart').length) {
        $('#priorityChart').parent().html('<p class="text-center text-muted" style="padding:40px 0;font-size:12px;"><i class="fa fa-info-circle"></i> No tasks yet</p>');
    }

    // 4. Type Chart (Bar)
    var typeLabels   = Object.keys(typeData).map(function(k){ return k.charAt(0).toUpperCase()+k.slice(1); });
    var typeValues   = Object.values(typeData);
    var typeColorArr = ['#3498db','#e74c3c','#9b59b6','#1abc9c','#e67e22','#34495e'];
    var typeBgColors = typeLabels.map(function(k,i){ return typeColorArr[i % typeColorArr.length]; });

    if ($('#typeChart').length && typeValues.length > 0) {
        new Chart($('#typeChart')[0].getContext('2d'), {
            type: 'bar',
            data: { labels: typeLabels, datasets: [{ label:'Tasks', data: typeValues, backgroundColor: typeBgColors, borderWidth: 0 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, stepSize: 1, fontColor:'#555' } }],
                    xAxes: [{ ticks: { fontSize: 10, fontColor:'#555' } }]
                }
            }
        });
    } else if ($('#typeChart').length) {
        $('#typeChart').parent().html('<p class="text-center text-muted" style="padding:40px 0;font-size:12px;"><i class="fa fa-info-circle"></i> No tasks yet</p>');
    }

    // 5. Assignee Chart (Horizontal Bar)
    var assigneeLabels = Object.keys(assigneeData);
    var assigneeValues = Object.values(assigneeData);

    if ($('#assigneeChart').length && assigneeValues.length > 0) {
        new Chart($('#assigneeChart')[0].getContext('2d'), {
            type: 'horizontalBar',
            data: { labels: assigneeLabels, datasets: [{ label:'Tasks', data: assigneeValues, backgroundColor:'#e74c3c', borderWidth: 0 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true, stepSize: 1, fontColor:'#555' } }],
                    yAxes: [{ ticks: { fontSize: 10, fontColor:'#555' } }]
                }
            }
        });
    } else if ($('#assigneeChart').length) {
        $('#assigneeChart').parent().html('<p class="text-center text-muted" style="padding:40px 0;font-size:12px;"><i class="fa fa-info-circle"></i> No assignees yet</p>');
    }

    // 6. Time Logged Chart (Line - 7 Days)
    var trendLabels = Object.keys(timeTrendData).map(function(d){
        var parts = d.split('-');
        return parts[2] + '/' + parts[1];
    });
    var trendValues = Object.values(timeTrendData);

    if ($('#timeLogChart').length) {
        new Chart($('#timeLogChart')[0].getContext('2d'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Hours Logged',
                    data: trendValues,
                    backgroundColor: 'rgba(52, 152, 219, 0.15)',
                    borderColor: '#2980b9',
                    pointBackgroundColor: '#2980b9',
                    pointRadius: 4,
                    borderWidth: 2,
                    fill: true,
                    lineTension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ gridLines: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 7, fontColor:'#555', fontSize: 10 } }],
                    yAxes: [{ ticks: { beginAtZero: true, fontColor:'#555' } }]
                },
                tooltips: {
                    callbacks: {
                        label: function(item){ return item.yLabel + ' hrs'; }
                    }
                }
            }
        });
    }

    // ── Add Member ────────────────────────────────────────────────────
    $('#btnAddMember').on('click', function () {
        var userId    = $('#add_member_user').val();
        var role      = $('#add_member_role').val();
        var projectId = $(this).data('project-id');

        if (!userId) { alert('Please select a user.'); return; }

        var $btn = $(this).prop('disabled', true).text('Adding…');

        $.ajax({
            url: BASE_URL + 'add-project-member',
            type: 'POST',
            dataType: 'json',
            data: { project_id: projectId, user_id: userId, project_role: role },
            success: function (res) {
                if (!res.success) { alert(res.message); return; }

                var name = res.name;
                var init = name.split(' ').slice(0, 2).map(function(w){ return w[0].toUpperCase(); }).join('');
                var rc   = roleColors[res.role] || '#95a5a6';
                var pr   = roleLabels[res.project_role] || res.project_role;

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

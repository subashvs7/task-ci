  </div><!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      Page rendered in <strong>{elapsed_time}</strong> seconds.
    </div>
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#"><?php echo APP_NAME; ?></a>.</strong> All rights reserved.
  </footer>
</div><!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery-ui/jquery-ui.min.js"></script>
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Moment + DateRangePicker -->
<script src="<?php echo base_url() ?>asset/bower_components/moment/min/moment.min.js"></script>
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- DatePicker -->
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Slimscroll -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo base_url() ?>asset/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url() ?>asset/dist/js/adminlte.min.js"></script>

<script>
  $(function () {
    $('.navbar-nav .user.user-menu > .dropdown-toggle').dropdown();
    $('.navbar-nav').on('click', '.user.user-menu > .dropdown-toggle', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).dropdown('toggle');
    });
    $(document).on('click', function () {
      $('.user.user-menu.open, .user.user-menu.show').removeClass('open show')
        .find('> .dropdown-toggle[aria-expanded="true"]').attr('aria-expanded', 'false');
    });
    // Init select2 globally
    if ($.fn.select2) {
      $('.select2').select2({ width: '100%' });
    }
  });
</script>

<!-- Project Team Stats Modal -->
<div class="modal fade" id="projectTeamStatsModal" tabindex="-1" role="dialog" aria-labelledby="projectTeamStatsModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: #fff; padding: 15px 20px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="projectTeamStatsModalLabel" style="font-weight: 600; display: flex; align-items: center; gap: 8px;">
          <i class="fa fa-folder-open"></i> <span id="pts_modal_project_name">Project Title</span>
        </h4>
      </div>
      <div class="modal-body" style="padding: 20px; background: #fafbfc;">
        <!-- Loading state -->
        <div id="pts_loading" class="text-center" style="padding: 40px 0;">
          <i class="fa fa-spinner fa-spin fa-3x text-muted" style="margin-bottom: 10px;"></i>
          <p class="text-muted" style="font-weight: 500;">Loading project stats...</p>
        </div>

        <!-- Error state -->
        <div id="pts_error" class="alert alert-danger" style="display: none; margin-bottom: 0;">
          <i class="fa fa-exclamation-triangle"></i> <span id="pts_error_msg">Failed to load project details.</span>
        </div>

        <!-- Content state -->
        <div id="pts_content" style="display: none;">
          <!-- Top cards row -->
          <div class="row" style="display: flex; flex-wrap: wrap; align-items: stretch; margin-bottom: 20px;">
            <!-- Left col: Project Metadata -->
            <div class="col-md-7" style="display: flex;">
              <div class="panel panel-default" style="width: 100%; border-radius: 6px; border: 1px solid #e1e8ed; box-shadow: none; margin-bottom: 0;">
                <div class="panel-body" style="padding: 15px;">
                  <table class="table" style="margin-bottom: 0; background: transparent;">
                    <tbody>
                      <tr>
                        <td style="border-top: none; width: 130px; font-weight: bold; color: #555;">Tech Stack:</td>
                        <td style="border-top: none;" id="pts_project_stacks">-</td>
                      </tr>
                      <tr>
                        <td style="font-weight: bold; color: #555;">Priority:</td>
                        <td><span id="pts_project_priority" class="badge">Medium</span></td>
                      </tr>
                      <tr>
                        <td style="font-weight: bold; color: #555;">Timeline:</td>
                        <td id="pts_project_timeline">-</td>
                      </tr>
                      <tr>
                        <td style="font-weight: bold; color: #555;">Manager/Owner:</td>
                        <td id="pts_project_manager">-</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Right col: Overall Project Progress -->
            <div class="col-md-5" style="display: flex;">
              <div class="panel panel-default" style="width: 100%; border-radius: 6px; border: 1px solid #e1e8ed; box-shadow: none; margin-bottom: 0; display: flex; flex-direction: column; justify-content: center; padding: 15px;">
                <div style="font-size: 13px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Overall Project Progress</div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px;">
                  <span style="font-size: 32px; font-weight: 800; color: #2c3e50;" id="pts_project_progress_text">0%</span>
                  <span class="text-muted" style="font-weight: 500;" id="pts_project_tasks_ratio">0/0 Tasks Done</span>
                </div>
                <div class="progress progress-sm active" style="margin-bottom: 0; background: #eaeded; border-radius: 4px;">
                  <div id="pts_project_progress_bar" class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" style="width: 0%"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Team Title -->
          <div style="font-size: 16px; font-weight: 700; color: #2c3e50; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <i class="fa fa-users text-primary"></i> Team & Work Effort Breakdown
          </div>

          <!-- Members Stats Table -->
          <div class="table-responsive" style="border-radius: 6px; border: 1px solid #e1e8ed; background: #fff;">
            <table class="table table-hover table-striped" style="margin-bottom: 0;">
              <thead>
                <tr style="background: #f4f6f8; color: #555;">
                  <th>Team Member</th>
                  <th>Role</th>
                  <th>System Role</th>
                  <th style="width: 25%;">Task Completion Stats</th>
                  <th>Total Effort Logged</th>
                  <th>Assigned By (Flow)</th>
                </tr>
              </thead>
              <tbody id="pts_members_list">
                <!-- Appended dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="background: #f4f6f8; border-top: 1px solid #e1e8ed; padding: 15px 20px;">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px; font-weight: 600;">Close</button>
        <a href="#" id="pts_full_detail_btn" class="btn btn-primary" style="background-color: #34495e; border-color: #2c3e50; border-radius: 4px; font-weight: 600;">
          <i class="fa fa-external-link"></i> View Full Project Page
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    $(document).on('click', '.project-link-modal, .btn-view-project-modal', function (e) {
      e.preventDefault();
      var projectId = $(this).data('id');
      if (!projectId) return;

      // Reset views in the modal
      $('#pts_loading').show();
      $('#pts_content').hide();
      $('#pts_error').hide();

      // Show the modal
      $('#projectTeamStatsModal').modal('show');

      // Fetch stats
      $.ajax({
        url: '<?php echo site_url("get-project-team-stats"); ?>',
        type: 'GET',
        data: { project_id: projectId },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            var p = response.project;
            
            // Populate Project Metadata
            $('#pts_modal_project_name').text(p.name);
            $('#pts_project_stacks').text(p.stacks ? p.stacks : 'None');
            
            // Priority badge styling
            var pBadge = $('#pts_project_priority');
            pBadge.text(p.priority.charAt(0).toUpperCase() + p.priority.slice(1));
            pBadge.removeClass('badge-priority-low badge-priority-medium badge-priority-high badge-priority-critical');
            pBadge.addClass('badge-priority-' + p.priority);
            if (p.priority === 'low') pBadge.css('background-color', '#27ae60');
            else if (p.priority === 'medium') pBadge.css('background-color', '#2980b9');
            else if (p.priority === 'high') pBadge.css('background-color', '#e67e22');
            else if (p.priority === 'critical') pBadge.css('background-color', '#c0392b');

            // Timeline
            var startStr = p.start_date ? moment(p.start_date).format('DD-MMM-YYYY') : '-';
            var endStr = p.end_date ? moment(p.end_date).format('DD-MMM-YYYY') : '-';
            var timelineText = startStr + ' to ' + endStr;
            if (p.end_date) {
              var diff = moment(p.end_date).diff(moment(), 'days');
              if (diff < 0) {
                timelineText += ' <span class="label label-danger" style="margin-left:5px;">Overdue by ' + Math.abs(diff) + ' days</span>';
              } else {
                timelineText += ' <span class="label label-success" style="margin-left:5px;">' + diff + ' days remaining</span>';
              }
            }
            $('#pts_project_timeline').html(timelineText);
            $('#pts_project_manager').text(p.owner_name ? p.owner_name : '-');

            // Overall progress
            $('#pts_project_progress_text').text(p.progress_pct + '%');
            $('#pts_project_tasks_ratio').text(p.done_tasks + '/' + p.total_tasks + ' Tasks Completed');
            $('#pts_project_progress_bar').css('width', p.progress_pct + '%');

            // Populate members list
            var tbody = $('#pts_members_list');
            tbody.empty();

            if (response.members && response.members.length > 0) {
              $.each(response.members, function (idx, m) {
                var completed = parseInt(m.completed_tasks);
                var total = parseInt(m.total_tasks);
                var pct = total > 0 ? Math.round((completed / total) * 100) : 0;
                
                var progressBarColor = 'progress-bar-success';
                if (pct < 40) progressBarColor = 'progress-bar-danger';
                else if (pct < 75) progressBarColor = 'progress-bar-warning';

                // Convert hours to standard working days (assuming 8 hours = 1 working day)
                var hours = parseFloat(m.total_hours);
                var days = (hours / 8).toFixed(1);
                var effortStr = '<strong>' + hours + '</strong> hrs <span class="text-muted">(' + days + ' days)</span>';

                // Roles
                var pRoleLabel = m.project_role ? (m.project_role.charAt(0).toUpperCase() + m.project_role.slice(1)) : 'Member';
                var pRoleClass = 'label-default';
                if (m.project_role === 'manager' || m.project_role === 'owner') {
                  pRoleClass = 'label-primary';
                } else if (m.project_role === 'developer') {
                  pRoleClass = 'label-info';
                } else if (m.project_role === 'designer') {
                  pRoleClass = 'label-warning';
                } else if (m.project_role === 'tester') {
                  pRoleClass = 'label-success';
                }

                var sysRoleLabel = m.system_role ? m.system_role.replace('_', ' ') : '-';
                sysRoleLabel = sysRoleLabel.charAt(0).toUpperCase() + sysRoleLabel.slice(1);

                var tr = $('<tr>');
                tr.append($('<td>').html('<strong style="color: #34495e;">' + m.name + '</strong><br><small class="text-muted">' + m.email + '</small>'));
                tr.append($('<td>').html('<span class="label ' + pRoleClass + '">' + pRoleLabel + '</span>'));
                tr.append($('<td>').text(sysRoleLabel));
                
                // Progress bar
                var progressHtml = '<div style="margin-bottom: 2px; font-size: 11px; font-weight: 600;">' + completed + ' / ' + total + ' tasks (' + pct + '%)</div>' +
                                   '<div class="progress progress-xxs" style="margin-bottom:0; background:#f0f2f5;">' +
                                   '  <div class="progress-bar ' + progressBarColor + '" style="width:' + pct + '%;"></div>' +
                                   '</div>';
                tr.append($('<td>').html(progressHtml));
                tr.append($('<td>').html(effortStr));
                
                // Assigned By Flow
                var assignedBy = m.assigned_by_names ? m.assigned_by_names : '<span class="text-muted">-</span>';
                tr.append($('<td>').html('<small style="font-weight:600; color:#2980b9;">' + assignedBy + '</small>'));

                tbody.append(tr);
              });
            } else {
              tbody.append('<tr><td colspan="5" class="text-center text-muted" style="padding: 15px;">No team members assigned to this project yet.</td></tr>');
            }

            // Full project detail button link
            $('#pts_full_detail_btn').attr('href', '<?php echo site_url("project-detail"); ?>/' + p.project_id);

            // Display content
            $('#pts_loading').hide();
            $('#pts_content').show();
          } else {
            $('#pts_loading').hide();
            $('#pts_error_msg').text(response.message || 'Failed to load project details.');
            $('#pts_error').show();
          }
        },
        error: function () {
          $('#pts_loading').hide();
          $('#pts_error_msg').text('An error occurred while fetching project data.');
          $('#pts_error').show();
        }
      });
    });
  });
</script>

<!-- Task Quick View Modal -->
<div class="modal fade" id="taskQuickModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none; overflow: hidden;">
      <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border-bottom: none; padding: 15px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; text-shadow: none;">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" style="font-weight: 600; font-size: 16px;">
          <i class="fa fa-bolt"></i> Quick Task Actions
        </h4>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <div id="tq_loading" class="text-center" style="padding: 40px 0;">
          <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw" style="color: #3498db;"></i>
          <p style="margin-top: 15px; color: #7f8c8d; font-weight: 500;">Loading Task Data...</p>
        </div>
        
        <div id="tq_error" class="alert alert-danger" style="display: none; border-radius: 6px;">
          <i class="fa fa-exclamation-triangle"></i> <span id="tq_error_msg"></span>
        </div>

        <div id="tq_content" style="display: none;">
          <!-- Ajax content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    // ── Task Quick View Modal Trigger ──────────────────────────────────────────
    $(document).on('click', '.task-link-modal', function (e) {
      e.preventDefault();
      var taskId = $(this).data('id');
      if (!taskId) return;

      $('#tq_loading').show();
      $('#tq_content').hide().empty();
      $('#tq_error').hide();

      $('#taskQuickModal').modal('show');

      $.ajax({
        url: '<?php echo site_url("task-quick-view"); ?>',
        type: 'POST',
        data: { task_id: taskId },
        success: function (response) {
          $('#tq_loading').hide();
          $('#tq_content').html(response).fadeIn(200);
          updateTimers(); // Initialize any new timers that just loaded
        },
        error: function () {
          $('#tq_loading').hide();
          $('#tq_error_msg').text('An error occurred while fetching task data.');
          $('#tq_error').show();
        }
      });
    });

    // ── Global Work Session Toggle (Start / Stop) ────────────────────────────────────
    var GLOBAL_SESSION_URL = '<?php echo site_url("task-toggle-session"); ?>';

    $(document).on('click', '.btn-task-session', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $btn    = $(this);
        var tId     = $btn.data('task');
        var action  = $btn.data('action');
        var label   = action === 'start' ? 'Start Work Session?' : 'Stop Work Session?';
        var icon    = action === 'start' ? 'question' : 'warning';
        var confirmColor = action === 'start' ? '#27ae60' : '#c0392b';
        var confirmText  = action === 'start' ? 'Yes, Start!' : 'Yes, Stop & Log Time';

        Swal.fire({
            title: label,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: GLOBAL_SESSION_URL,
                type: 'POST',
                data: { task_id: tId },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        if (res.action === 'started') {
                            Swal.fire({
                                title: 'Session Started!',
                                html: '<i class="fa fa-play-circle text-success" style="font-size:48px; margin-bottom:10px;"></i><p>Your work session has started. Timer is running.</p>',
                                confirmButtonText: 'Got it',
                                confirmButtonColor: '#27ae60',
                                timer: 3000
                            }).then(function(){ location.reload(); });
                        } else {
                            var mins = res.duration_min;
                            var hrs  = parseFloat(res.hours_logged);
                            var eff  = res.effort || {};
                            var effortMsg = '';
                            if (eff.is_effort_overdue) {
                                effortMsg = '<br><span class="label label-warning"><i class="fa fa-clock-o"></i> Over Budget: ' + eff.total_hours + 'h / ' + eff.estimated_hours + 'h used</span>';
                            }
                            Swal.fire({
                                title: 'Session Stopped!',
                                html: '<i class="fa fa-stop-circle text-danger" style="font-size:48px; margin-bottom:10px;"></i>' +
                                      '<p>Work logged: <strong>' + mins + ' min (' + hrs + 'h)</strong></p>' + effortMsg,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#2c3e50'
                            }).then(function(){ location.reload(); });
                        }
                    } else {
                        $btn.prop('disabled', false).html(action === 'start' ? '<i class="fa fa-play-circle"></i> Start Work' : '<i class="fa fa-stop-circle"></i> Stop Work');
                        Swal.fire('Action Failed', res.message, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                }
            });
        });
    });

    // ── Global Complete Task ──────────────────────────────────────────────────────────
    var GLOBAL_COMPLETE_URL = '<?php echo site_url("task-complete"); ?>';

    $(document).on('click', '.btn-task-complete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $btn = $(this);
        var tId  = $btn.data('task');

        Swal.fire({
            title: 'Complete Task?',
            text: 'Are you sure you want to mark this task as completed? Any active session will be stopped and logged.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#27ae60',
            confirmButtonText: 'Yes, Complete it!',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: GLOBAL_COMPLETE_URL,
                type: 'POST',
                data: { task_id: tId },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            title: 'Task Completed!',
                            html: '<i class="fa fa-check-circle text-success" style="font-size:48px; margin-bottom:10px;"></i><p>The task has been marked as complete.</p>',
                            confirmButtonText: 'Great',
                            confirmButtonColor: '#27ae60',
                            timer: 2000
                        }).then(function(){ location.reload(); });
                    } else {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Complete');
                        Swal.fire('Action Failed', res.message, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Complete');
                    Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                }
            });
        });
    });

    // ── Global Live Session Timers ───────────────────────────────────────────────────
    function formatElapsed(seconds) {
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        return (h < 10 ? '0' : '') + h + ':' +
               (m < 10 ? '0' : '') + m + ':' +
               (s < 10 ? '0' : '') + s;
    }

    window.updateTimers = function() {
        var now = Math.floor(Date.now() / 1000);
        $('.session-timer').each(function() {
            var startTs;
            if ($(this).data('start-ts')) {
                startTs = parseInt($(this).data('start-ts'), 10);
            } else {
                var startRaw = $(this).data('start');
                if (!startRaw) return;
                startTs = Math.floor(new Date(startRaw.replace(' ', 'T')).getTime() / 1000);
            }
            var elapsed = Math.max(0, now - startTs);
            $(this).text(formatElapsed(elapsed));
        });
    };

    updateTimers();
    setInterval(updateTimers, 1000);
  });
</script>

<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
  import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js";

  const firebaseConfig = {
    apiKey: "AIzaSyCkURFLiJOsUgvgxv8xfB5lxwr2uq-Igec",
    authDomain: "zazu-task.firebaseapp.com",
    projectId: "zazu-task",
    storageBucket: "zazu-task.firebasestorage.app",
    messagingSenderId: "105767000196",
    appId: "1:105767000196:web:d34d6ddb1b5c28f156cad1",
    measurementId: "G-Y35F4Z0MRQ"
  };

  const app = initializeApp(firebaseConfig);
  let messaging = null;

  const isSupported = 'serviceWorker' in navigator && 'PushManager' in window;

  if (isSupported) {
    try {
      messaging = getMessaging(app);
    } catch (e) {
      console.warn("FCM getMessaging not supported/initialized in this environment: ", e);
    }
  }

  function initializeFCM() {
    if (!messaging) return;
    
    Notification.requestPermission().then((permission) => {
      if (permission === 'granted') {
        console.log('Notification permission granted.');
        
        navigator.serviceWorker.register('<?php echo base_url("firebase-messaging-sw.js"); ?>')
          .then((registration) => {
            console.log('Service Worker registered successfully with scope: ', registration.scope);
            
            getToken(messaging, { 
              serviceWorkerRegistration: registration,
              vapidKey: 'BCWZvDGIgoipRWDqbAhGVZD1aNb1xmNeTEF-tuzpxcozMkHuL6BWaNdhxU0S1FL7LIljtjLNoRahMQKsZWzEEE0' 
            })
            .then((currentToken) => {
              if (currentToken) {
                console.log('Retrieved FCM Token: ', currentToken);
                $.ajax({
                  url: '<?php echo site_url("save-fcm-token"); ?>',
                  type: 'POST',
                  data: { fcm_token: currentToken },
                  dataType: 'json',
                  success: function(res) {
                    console.log('FCM Token sync status: ', res.message);
                  }
                });
              } else {
                console.warn('No registration token available.');
              }
            })
            .catch((err) => {
              console.error('An error occurred while retrieving token. ', err);
            });
          })
          .catch((err) => {
            console.error('Service Worker registration failed: ', err);
          });
      } else {
        console.warn('Notification permission denied.');
      }
    });
  }

  let userTasksList = [];

  function fetchScheduledTasks() {
    $.ajax({
      url: '<?php echo site_url("get-scheduled-tasks"); ?>',
      type: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.success && res.tasks) {
          userTasksList = res.tasks;
          console.log('Synchronized scheduled tasks: ', userTasksList.length);
          // Run reminder check immediately after fresh data arrives
          checkTaskReminders();
        }
      },
      error: function(err) {
        console.error('Failed to fetch scheduled tasks: ', err);
      }
    });
  }

  function parseDateToTimestamp(dateStr) {
    if (!dateStr) return 0;
    var cleanStr = dateStr.replace('T', ' ');
    var parts = cleanStr.split(' ');
    var dateParts = parts[0].split('-');
    var timeParts = parts[1] ? parts[1].split(':') : [0, 0, 0];
    var year   = parseInt(dateParts[0], 10);
    var month  = parseInt(dateParts[1], 10) - 1;
    var day    = parseInt(dateParts[2], 10);
    var hour   = parseInt(timeParts[0], 10);
    var minute = parseInt(timeParts[1], 10);
    var second = parseInt(timeParts[2] || 0, 10);
    return Math.floor(new Date(year, month, day, hour, minute, second).getTime() / 1000);
  }

  function playAlertSound() {
    try {
      var context = new (window.AudioContext || window.webkitAudioContext)();
      var oscillator = context.createOscillator();
      var gain = context.createGain();
      oscillator.connect(gain);
      gain.connect(context.destination);
      oscillator.type = 'sine';
      oscillator.frequency.setValueAtTime(880, context.currentTime); 
      gain.gain.setValueAtTime(0.3, context.currentTime);
      oscillator.start();
      oscillator.stop(context.currentTime + 0.15);
    } catch(e) {
      console.warn("Could not play sound: ", e);
    }
  }

  function speakNotification(text) {
    if ('speechSynthesis' in window) {
      try {
        var utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1.0;
        utterance.pitch = 1.1;
        window.speechSynthesis.speak(utterance);
      } catch(e) {}
    }
  }

  function triggerLocalNotification(title, body, taskId) {
    playAlertSound();
    speakNotification(title + ". " + body);

    if ('Notification' in window && Notification.permission === 'granted') {
      try {
        new Notification(title, {
          body: body,
          icon: '<?php echo base_url("asset/images/logo.png"); ?>'
        });
      } catch (err) {
        if (navigator.serviceWorker && navigator.serviceWorker.controller) {
          navigator.serviceWorker.ready.then(function(reg) {
            reg.showNotification(title, { body: body });
          });
        }
      }
    }

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'warning',
        title: title,
        html: '<div style="font-size:14px; text-align:left; padding:12px; background:#fff5f5; border-radius:8px; border-left:4px solid #e74c3c; margin-top:10px;">' +
              '<p style="margin:0; color:#2c3e50; font-weight:500;">' + body.replace(/ \| /g, '<br>') + '</p>' +
              '</div>',
        confirmButtonText: '<i class="fa fa-tasks"></i> View Tasks',
        confirmButtonColor: '#e74c3c',
        allowOutsideClick: true
      }).then(function(res) {
        if (res.isConfirmed) {
          window.location.href = '<?php echo site_url("task-list"); ?>';
        }
      });
    }
  }

  function checkTaskReminders() {
    if (userTasksList.length === 0) return;
    
    const now = Math.floor(Date.now() / 1000);
    // Use a 120-second window to handle browser timer throttling in background tabs
    const NOTIFY_WINDOW_SEC = 120;
    
    userTasksList.forEach((task) => {
      const assignee = task.assignee_name || 'Unassigned';

      // 1. Active Work Session Timer & Balance Time Check
      if (task.work_session_status === 'active' && task.estimated_hours && parseFloat(task.estimated_hours) > 0) {
        const estSec = Math.round(parseFloat(task.estimated_hours) * 3600);
        const prevLoggedSec = Math.round(parseFloat(task.logged_hours || 0) * 3600);
        let activeSec = 0;
        if (task.open_session_start) {
          const sessionStartTs = parseDateToTimestamp(task.open_session_start);
          if (sessionStartTs > 0) {
            activeSec = Math.max(0, now - sessionStartTs);
          }
        }
        const totalLoggedSec = prevLoggedSec + activeSec;
        const balanceSec = estSec - totalLoggedSec;

        if (balanceSec <= NOTIFY_WINDOW_SEC) {
          // Include estimated_hours in key so re-estimates re-trigger
          const key = 'notified_balance_' + task.task_id + '_' + task.estimated_hours;
          if (!sessionStorage.getItem(key)) {
            sessionStorage.setItem(key, 'true');
            const remMins = Math.max(0, Math.ceil(balanceSec / 60));
            triggerLocalNotification(
              '⚠️ 1 Min Balance Warning',
              'Task: "' + task.title + '" | Assignee: ' + assignee + ' | Balance Time: ' + (remMins <= 1 ? '1 min' : remMins + ' mins') + ' remaining. Please turn off work session or complete task.',
              task.task_id
            );
          }
        }
      }

      // 2. Scheduled Start Time (notify within 1–2 minutes before start)
      if (task.start_time) {
        const startTs = parseDateToTimestamp(task.start_time);
        if (startTs > 0) {
          const timeDiff = startTs - now;
          if (timeDiff >= 0 && timeDiff <= NOTIFY_WINDOW_SEC) {
            // Include start_time in key so if start_time changes the new one re-triggers
            const key = 'notified_start_' + task.task_id + '_' + startTs;
            if (!sessionStorage.getItem(key)) {
              sessionStorage.setItem(key, 'true');
              triggerLocalNotification(
                '🚀 Task Starting Soon',
                'Task: "' + task.title + '" | Assignee: ' + assignee + ' | Starts in ~1 minute!',
                task.task_id
              );
            }
          }
        }
      }

      // 3. Scheduled End Time (notify within 1–2 minutes before end)
      if (task.end_time) {
        const endTs = parseDateToTimestamp(task.end_time);
        if (endTs > 0) {
          const timeDiff = endTs - now;
          if (timeDiff >= 0 && timeDiff <= NOTIFY_WINDOW_SEC) {
            // Include end_time in key so if end_time changes the new one re-triggers
            const key = 'notified_end_' + task.task_id + '_' + endTs;
            if (!sessionStorage.getItem(key)) {
              sessionStorage.setItem(key, 'true');
              triggerLocalNotification(
                '⌛ Task Ending Soon',
                'Task: "' + task.title + '" | Assignee: ' + assignee + ' | Ends in ~1 minute. Please finish or stop work session.',
                task.task_id
              );
            }
          }
        }
      }
    });
  }

  if (messaging) {
    onMessage(messaging, (payload) => {
      console.log('Message received in foreground: ', payload);
      if (payload.notification) {
        triggerLocalNotification(payload.notification.title, payload.notification.body, null);
      }
    });
  }

  $(document).ready(function() {
    <?php if ($this->session->userdata(SESS_HEAD . '_logged_in')): ?>
      if ('Notification' in window) {
        if (Notification.permission === 'default') {
          const bannerHtml = `
            <div id="notif-permission-banner" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; 
                        background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); color: white; 
                        padding: 15px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
                        max-width: 320px; border: 1px solid rgba(255,255,255,0.1); font-family: sans-serif;">
              <div style="font-weight: bold; margin-bottom: 5px; font-size: 14px;"><i class="fa fa-bell text-warning"></i> Task Reminders</div>
              <div style="font-size: 12px; margin-bottom: 12px; opacity: 0.9;">Enable push notifications to receive 1-minute warnings before tasks start, end, or balance time expires.</div>
              <div style="text-align: right;">
                <button id="btn-decline-notif" style="background: transparent; color: #cbd5e1; border: none; font-size: 12px; cursor: pointer; margin-right: 12px; padding: 5px;">Later</button>
                <button id="btn-allow-notif" style="background: #22c55e; color: white; border: none; font-size: 12px; font-weight: bold; cursor: pointer; padding: 6px 14px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">Enable</button>
              </div>
            </div>
          `;
          $('body').append(bannerHtml);
          
          $('#btn-allow-notif').on('click', function() {
            $('#notif-permission-banner').fadeOut(200, function() { $(this).remove(); });
            initializeFCM();
          });
          
          $('#btn-decline-notif').on('click', function() {
            $('#notif-permission-banner').fadeOut(200, function() { $(this).remove(); });
          });
        } else if (Notification.permission === 'granted') {
          initializeFCM();
        }
      }

      fetchScheduledTasks();
      // Fetch every 10s (was 15s) for tighter timing coverage
      setInterval(fetchScheduledTasks, 10000);
      // Check reminders every 5s; also called immediately after each fetch
      setInterval(checkTaskReminders, 5000);
    <?php endif; ?>
  });
</script>


<?php if (isset($js) && !empty($js)) include_once(VIEWPATH . 'inc/inc-js/' . $js); ?>

</body>
</html>

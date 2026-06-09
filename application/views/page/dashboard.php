<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Dashboard <small>Overview</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
  </ol>
</section>

<section class="content">
  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_error'); ?></div>
  <?php endif; ?>

  <!-- Stats Row 1 -->
  <div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-aqua"><i class="fa fa-folder-open"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Projects</span>
          <span class="info-box-number"><?php echo $total_projects; ?></span>
          <span class="info-box-text" style="font-size:11px;">Active: <?php echo $active_projects; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-green"><i class="fa fa-tasks"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Tasks</span>
          <span class="info-box-number"><?php echo $total_tasks; ?></span>
          <span class="info-box-text" style="font-size:11px;">Done: <?php echo $done_tasks; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-yellow"><i class="fa fa-user"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">My Tasks</span>
          <span class="info-box-number"><?php echo $my_tasks; ?></span>
          <span class="info-box-text" style="font-size:11px;">Open: <?php echo $my_open_tasks; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Overdue Tasks</span>
          <span class="info-box-number"><?php echo $overdue_tasks; ?></span>
          <span class="info-box-text" style="font-size:11px;">Need attention</span>
        </div>
      </div>
    </div>
  </div>

  <?php if ($this->session->userdata(SESS_HEAD . '_role') === 'admin'): ?>
  <div class="row">
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box bg-purple">
        <span class="info-box-icon"><i class="fa fa-briefcase"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Managers</span>
          <span class="info-box-number"><?php echo isset($admin_managers_count) ? $admin_managers_count : 0; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box bg-teal">
        <span class="info-box-icon"><i class="fa fa-users"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Team Leaders</span>
          <span class="info-box-number"><?php echo isset($admin_team_leaders_count) ? $admin_team_leaders_count : 0; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box bg-maroon">
        <span class="info-box-icon"><i class="fa fa-user"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Staff</span>
          <span class="info-box-number"><?php echo isset($admin_staff_count) ? $admin_staff_count : 0; ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!is_null($manager_project_count)): ?>
  <!-- Manager: My Projects Banner -->
  <div class="row">
    <div class="col-md-12">
      <div style="
        background: linear-gradient(135deg, #1565c0 0%, #2980b9 50%, #1abc9c 100%);
        border-radius: 10px;
        padding: 22px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 6px 20px rgba(21,101,192,0.30);
        margin-bottom: 20px;
      ">
        <a href="<?php echo site_url('project-list') ?>" style="text-decoration:none; display:flex; align-items:center; gap:22px;">
          <div style="
            width:70px; height:70px;
            border-radius:50%;
            background:rgba(255,255,255,0.2);
            display:flex; align-items:center; justify-content:center;
            font-size:30px; color:#fff; flex-shrink:0;
          ">
            <i class="fa fa-briefcase"></i>
          </div>
          <div>
            <div style="font-size:13px; color:rgba(255,255,255,0.80); font-weight:600; letter-spacing:1px; text-transform:uppercase;">My Projects</div>
            <div style="font-size:48px; font-weight:900; color:#fff; line-height:1.1;">
              <?php echo $manager_project_count; ?>
            </div>
            <div style="font-size:13px; color:rgba(255,255,255,0.75); margin-top:3px;">
              <i class="fa fa-circle" style="color:#2ecc71; font-size:9px;"></i>
              &nbsp;<?php echo $manager_project_active; ?> currently active
            </div>
          </div>
        </a>
        <div style="text-align:right; display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
          <div style="font-size:13px; color:rgba(255,255,255,0.75); margin-bottom:4px;">
            <i class="fa fa-folder-open"></i> &nbsp;Total assigned projects
          </div>

          <a href="<?php echo site_url('project-list') ?>" style="
            display:inline-block;
            background:rgba(255,255,255,0.22);
            color:#fff;
            padding:8px 22px;
            border-radius:20px;
            font-size:13px;
            font-weight:600;
            border: 1px solid rgba(255,255,255,0.35);
            margin-top: 4px;
            text-decoration:none;
          ">View All Projects &nbsp;<i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>





  <div class="row" style="display: flex; flex-wrap: wrap; align-items: stretch;">
    <!-- Left Column: FullCalendar -->
    <div class="col-md-7" style="display: flex; flex-direction: column; margin-bottom: 20px;">
      <div class="box box-primary" style="flex: 1; margin-bottom: 0;">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-calendar"></i> Project &amp; Task Calendar</h3>
        </div>
        <div class="box-body">
          <div id="calendar"></div>
          <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; justify-content: center; padding-top: 10px; border-top: 1px solid #f0f0f0;">
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #9b59b6; margin-right: 5px;"></span> Project</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #3498db; margin-right: 5px;"></span> Task</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #e67e22; margin-right: 5px;"></span> Epic</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #1abc9c; margin-right: 5px;"></span> Story</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #e74c3c; margin-right: 5px;"></span> Due Date</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Recent Projects & Tasks -->
    <div class="col-md-5" style="display: flex; flex-direction: column; margin-bottom: 20px;">
      
      <!-- Recent Projects -->
      <div class="box box-info" style="flex: 1; display: flex; flex-direction: column; margin-bottom: 15px;">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-folder-open"></i> Recent Projects</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('project-list') ?>" class="btn btn-xs btn-primary" style="color:#fff; font-weight:bold;">View All</a>
          </div>
        </div>
        <div class="box-body no-padding" style="flex: 1; overflow-y: auto;">
          <table class="table table-hover">
            <thead>
              <tr><th>Project</th><th>Status</th><th>Tasks</th></tr>
            </thead>
            <tbody>
              <?php if (empty($recent_projects)): ?>
              <tr><td colspan="3" class="text-center text-muted">No projects yet.</td></tr>
              <?php else: ?>
              <?php foreach ($recent_projects as $p):
                $ptask = (int)$p['task_count'];
                $pdone = (int)$p['done_count'];
                $ppct  = $ptask > 0 ? round(($pdone / $ptask) * 100) : 0;
              ?>
              <tr>
                <td>
                  <a href="#" class="project-link-modal" data-id="<?php echo $p['project_id']; ?>" style="font-weight:600;">
                    <?php echo htmlspecialchars($p['name']); ?>
                  </a>
                  <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $p['project_id']; ?>" style="margin-left: 5px; padding: 1px 5px; border-radius: 3px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
                  <?php if (!empty($p['key_name'])): ?>
                    <small class="text-muted"> [<?php echo htmlspecialchars($p['key_name']); ?>]</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="label" style="background:<?php
                    $sc = array('planning'=>'#9b59b6','active'=>'#27ae60','on_hold'=>'#e67e22','completed'=>'#3498db','cancelled'=>'#c0392b');
                    echo isset($sc[$p['status']]) ? $sc[$p['status']] : '#95a5a6';
                  ?>;"><?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?></span>
                </td>
                <td>
                  <?php echo $pdone; ?>/<?php echo $ptask; ?>
                  <div class="progress progress-xs" style="margin:3px 0 0;">
                    <div class="progress-bar bg-green" style="width:<?php echo $ppct; ?>%;"></div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="box box-success" style="flex: 1; display: flex; flex-direction: column; margin-bottom: 0;">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-tasks"></i> Recent Tasks</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('task-list') ?>" class="btn btn-xs btn-success" style="color:#fff; font-weight:bold;">View All</a>
          </div>
        </div>
        <div class="box-body no-padding" style="flex: 1; overflow-y: auto;">
          <table class="table table-hover table-condensed">
            <thead>
              <tr>
                <th>Task</th>
                <th>Status</th>
                <th>Assignee</th>
                <th style="text-align:center;">Work Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_tasks)): ?>
              <tr><td colspan="4" class="text-center text-muted">No tasks yet.</td></tr>
              <?php else: ?>
              <?php foreach ($recent_tasks as $t): ?>
              <tr>
                <td>
                  <a href="#" class="task-link-modal" data-id="<?php echo $t['task_id']; ?>" style="font-weight:600; font-size:12px;">
                    <?php echo htmlspecialchars(mb_substr($t['title'], 0, 35)); ?><?php echo strlen($t['title']) > 35 ? '...' : ''; ?>
                  </a>
                </td>
                <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sts = TASK_STATUS_OPT; echo isset($sts[$t['status']]) ? $sts[$t['status']] : $t['status']; ?></span></td>
                <td style="font-size:11px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
                <td style="white-space:nowrap; text-align:center; min-width:90px; padding:6px 4px;">
                  <?php 
                    $is_done_closed = in_array(strtolower($t['status']), ['done', 'closed']);
                    $is_active_session = ($t['work_session_status'] === 'active');
                    $is_my_session = ($is_active_session && $t['active_session_user'] == $this->session->userdata(SESS_HEAD . '_user_id'));
                  ?>
                  <?php if ($is_done_closed): ?>
                    <span class="label label-success" style="font-size:10px;"><i class="fa fa-check-circle"></i> Completed</span>
                  <?php elseif ($is_my_session): ?>
                    <button class="btn btn-xs btn-danger btn-task-session"
                      data-task="<?php echo $t['task_id']; ?>"
                      data-action="stop"
                      style="font-size:10px; font-weight:600; padding:2px 7px;">
                      <i class="fa fa-stop-circle"></i> Stop Work
                    </button>
                    <div style="font-size:11px; color:#1a1a1a; margin-top:2px; font-weight:700;"><i class="fa fa-circle fa-beat" style="font-size:6px; margin-right:2px; color:#e74c3c;"></i>Working</div>
                  <?php elseif ($is_active_session): ?>
                    <span class="label label-warning" style="font-size:10px;"><i class="fa fa-spinner fa-spin"></i> Working</span>
                    <div style="font-size:12px; color:#1a1a1a; margin-top:2px; font-weight:700;"><?php echo htmlspecialchars($t['active_worker_name']); ?></div>
                  <?php else: ?>
                    <span style="font-size:10px; color:#95a5a6;"><i class="fa fa-circle-o"></i> Not Started</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>



<!-- Calendar Event Modal -->
<div class="modal fade" id="calendarEventModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none; overflow: hidden;">
      <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border-bottom: none; padding: 15px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; text-shadow: none;">&times;</button>
        <h4 class="modal-title" style="font-weight: 600; font-size: 16px;">
          <i class="fa fa-info-circle"></i> <span id="cal_modal_type">Event</span> Details
        </h4>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <h3 id="cal_modal_title" style="margin-top: 0; font-weight: 700; color: #2c3e50;"></h3>
        <hr style="margin: 10px 0 15px;">
        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
          <tbody>
            <tr>
              <th style="width: 35%; color: #7f8c8d;">Status</th>
              <td><span id="cal_modal_status" class="label label-default"></span></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Priority</th>
              <td id="cal_modal_priority"></td>
            </tr>
            <tr id="cal_modal_assignee_row">
              <th style="color: #7f8c8d;">Assignee</th>
              <td id="cal_modal_assignee"></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Assigned By</th>
              <td id="cal_modal_assigned_by"></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Start Date</th>
              <td id="cal_modal_start"></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Due Date</th>
              <td id="cal_modal_end"></td>
            </tr>
            <tr id="cal_modal_est_row">
              <th style="color: #7f8c8d;">Estimated Time</th>
              <td id="cal_modal_est"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 12px 20px;">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px; font-weight: 600;">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

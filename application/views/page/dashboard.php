<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Dashboard <small>Overview</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
  </ol>
</section>

<section class="content">

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

  <!-- Stats Row 2: Task Status + Priority -->
  <div class="row">
    <!-- Task Status Breakdown -->
    <div class="col-md-6">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-pie-chart"></i> Tasks by Status</h3>
        </div>
        <div class="box-body no-padding">
          <table class="table">
            <tbody>
              <?php
              $status_labels = TASK_STATUS_OPT;
              foreach ($status_labels as $key => $label):
                $cnt = isset($tasks_by_status[$key]) ? $tasks_by_status[$key] : 0;
                $pct = $total_tasks > 0 ? round(($cnt / $total_tasks) * 100) : 0;
              ?>
              <tr>
                <td style="width:140px;"><span class="badge badge-status-<?php echo $key; ?>"><?php echo $label; ?></span></td>
                <td>
                  <div class="progress progress-xs" style="margin-bottom:0;">
                    <div class="progress-bar" style="width:<?php echo $pct; ?>%; background:<?php
                      $colors = array('backlog'=>'#95a5a6','todo'=>'#3498db','in_progress'=>'#e67e22','in_review'=>'#9b59b6','done'=>'#27ae60','closed'=>'#7f8c8d');
                      echo isset($colors[$key]) ? $colors[$key] : '#ccc';
                    ?>;"></div>
                  </div>
                </td>
                <td style="width:60px; text-align:right;"><strong><?php echo $cnt; ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Task Priority Breakdown -->
    <div class="col-md-6">
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-flag"></i> Tasks by Priority</h3>
        </div>
        <div class="box-body no-padding">
          <table class="table">
            <tbody>
              <?php
              $priority_labels = TASK_PRIORITY_OPT;
              foreach ($priority_labels as $key => $label):
                $cnt = isset($tasks_by_priority[$key]) ? $tasks_by_priority[$key] : 0;
                $pct = $total_tasks > 0 ? round(($cnt / $total_tasks) * 100) : 0;
              ?>
              <tr>
                <td style="width:100px;"><span class="badge badge-priority-<?php echo $key; ?>"><?php echo $label; ?></span></td>
                <td>
                  <div class="progress progress-xs" style="margin-bottom:0;">
                    <div class="progress-bar" style="width:<?php echo $pct; ?>%; background:<?php
                      $pcolors = array('low'=>'#27ae60','medium'=>'#2980b9','high'=>'#e67e22','critical'=>'#c0392b');
                      echo isset($pcolors[$key]) ? $pcolors[$key] : '#ccc';
                    ?>;"></div>
                  </div>
                </td>
                <td style="width:60px; text-align:right;"><strong><?php echo $cnt; ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- This Week -->
  <div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box bg-green">
        <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Completed This Week</span>
          <span class="info-box-number"><?php echo $done_this_week; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box bg-blue">
        <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Created This Week</span>
          <span class="info-box-number"><?php echo $created_this_week; ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Projects + Recent Tasks -->
  <div class="row">
    <!-- Recent Projects -->
    <div class="col-md-5">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-folder-open"></i> Recent Projects</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('project-list') ?>" class="btn btn-box-tool btn-xs btn-primary">View All</a>
          </div>
        </div>
        <div class="box-body no-padding">
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
                  <a href="<?php echo site_url('project-detail/' . $p['project_id']) ?>" style="font-weight:600;">
                    <?php echo htmlspecialchars($p['name']); ?>
                  </a>
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
    </div>

    <!-- Recent Tasks -->
    <div class="col-md-7">
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-tasks"></i> Recent Tasks</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('task-list') ?>" class="btn btn-box-tool btn-xs btn-success">View All</a>
          </div>
        </div>
        <div class="box-body no-padding">
          <table class="table table-hover table-condensed">
            <thead>
              <tr><th>Task</th><th>Project</th><th>Status</th><th>Priority</th><th>Assignee</th></tr>
            </thead>
            <tbody>
              <?php if (empty($recent_tasks)): ?>
              <tr><td colspan="5" class="text-center text-muted">No tasks yet.</td></tr>
              <?php else: ?>
              <?php foreach ($recent_tasks as $t): ?>
              <tr>
                <td>
                  <a href="<?php echo site_url('task-detail/' . $t['task_id']) ?>" style="font-weight:600; font-size:12px;">
                    <?php echo htmlspecialchars(mb_substr($t['title'], 0, 35)); ?><?php echo strlen($t['title']) > 35 ? '...' : ''; ?>
                  </a>
                  <br><small class="text-muted"><?php $types = TASK_TYPE_OPT; echo isset($types[$t['type']]) ? $types[$t['type']] : $t['type']; ?></small>
                </td>
                <td style="font-size:11px;"><?php echo htmlspecialchars($t['project_name'] ?: '-'); ?></td>
                <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sts = TASK_STATUS_OPT; echo isset($sts[$t['status']]) ? $sts[$t['status']] : $t['status']; ?></span></td>
                <td><span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px;"><?php $prs = TASK_PRIORITY_OPT; echo isset($prs[$t['priority']]) ? $prs[$t['priority']] : $t['priority']; ?></span></td>
                <td style="font-size:11px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
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

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

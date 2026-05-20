<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Task Report</h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Task Report</li>
  </ol>
</section>

<section class="content">

  <!-- Filter -->
  <div class="box box-default">
    <div class="box-header with-border" data-widget="collapse" style="cursor:pointer;">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-3"><div class="form-group"><label>Project</label>
            <select name="project_id" class="form-control select2">
              <option value="">All Projects</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Assignee</label>
            <select name="assignee_id" class="form-control select2">
              <option value="">All Assignees</option>
              <?php foreach ($users_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_assignee==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-2"><div class="form-group"><label>Status</label>
            <select name="f_status" class="form-control">
              <option value="">All Status</option>
              <?php foreach (TASK_STATUS_OPT as $k=>$v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-2"><div class="form-group"><label>Priority</label>
            <select name="f_priority" class="form-control">
              <option value="">All Priority</option>
              <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_priority==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-2"><div class="form-group"><label>Type</label>
            <select name="f_type" class="form-control">
              <option value="">All Types</option>
              <?php foreach (TASK_TYPE_OPT as $k=>$v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_type==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
        </div>
        <div class="row">
          <div class="col-md-3"><div class="form-group"><label>Date From</label>
            <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($f_date_from); ?>">
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Date To</label>
            <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($f_date_to); ?>">
          </div></div>
          <div class="col-md-3" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Generate Report</button>
            <a href="<?php echo site_url($s_url) ?>" class="btn btn-default">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Boxes -->
  <div class="row">
    <div class="col-md-3">
      <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-tasks"></i></span>
        <div class="info-box-content"><span class="info-box-text">Total Tasks</span><span class="info-box-number"><?php echo $total_tasks; ?></span></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
        <div class="info-box-content"><span class="info-box-text">Done</span><span class="info-box-number"><?php echo $done_tasks; ?></span></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
        <div class="info-box-content"><span class="info-box-text">Open</span><span class="info-box-number"><?php echo $open_tasks; ?></span></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-exclamation-circle"></i></span>
        <div class="info-box-content"><span class="info-box-text">Overdue</span><span class="info-box-number"><?php echo $overdue_tasks; ?></span></div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Status Breakdown -->
    <div class="col-md-4">
      <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> By Status</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <?php foreach ($tasks_by_status as $r): ?>
            <?php $sl=TASK_STATUS_OPT; ?>
            <tr>
              <td><span class="badge badge-status-<?php echo $r['status']; ?>"><?php echo isset($sl[$r['status']])?$sl[$r['status']]:$r['status']; ?></span></td>
              <td class="text-right"><strong><?php echo $r['cnt']; ?></strong></td>
              <td style="width:40%;">
                <?php $pct = $total_tasks ? round($r['cnt']/$total_tasks*100) : 0; ?>
                <div class="progress progress-xs"><div class="progress-bar" style="width:<?php echo $pct; ?>%"></div></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
    <!-- Priority Breakdown -->
    <div class="col-md-4">
      <div class="box box-warning">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-flag"></i> By Priority</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <?php foreach ($tasks_by_priority as $r): ?>
            <?php $pl=TASK_PRIORITY_OPT; ?>
            <tr>
              <td><span class="badge badge-priority-<?php echo $r['priority']; ?>"><?php echo isset($pl[$r['priority']])?$pl[$r['priority']]:$r['priority']; ?></span></td>
              <td class="text-right"><strong><?php echo $r['cnt']; ?></strong></td>
              <td style="width:40%;">
                <?php $pct = $total_tasks ? round($r['cnt']/$total_tasks*100) : 0; ?>
                <div class="progress progress-xs"><div class="progress-bar bg-yellow" style="width:<?php echo $pct; ?>%"></div></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
    <!-- Type Breakdown -->
    <div class="col-md-4">
      <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tag"></i> By Type</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <?php foreach ($tasks_by_type as $r): ?>
            <?php $tl=TASK_TYPE_OPT; ?>
            <tr>
              <td><span class="badge badge-type-<?php echo $r['type']; ?>"><?php echo isset($tl[$r['type']])?$tl[$r['type']]:$r['type']; ?></span></td>
              <td class="text-right"><strong><?php echo $r['cnt']; ?></strong></td>
              <td style="width:40%;">
                <?php $pct = $total_tasks ? round($r['cnt']/$total_tasks*100) : 0; ?>
                <div class="progress progress-xs"><div class="progress-bar bg-aqua" style="width:<?php echo $pct; ?>%"></div></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Top Assignees -->
    <div class="col-md-6">
      <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users"></i> Top Assignees</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <thead><tr><th>Assignee</th><th class="text-right">Tasks</th></tr></thead>
            <tbody>
            <?php foreach ($tasks_by_assignee as $r): ?>
            <tr><td><?php echo htmlspecialchars($r['name'] ?: 'Unassigned'); ?></td><td class="text-right"><span class="badge bg-green"><?php echo $r['cnt']; ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Top Projects -->
    <div class="col-md-6">
      <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-folder"></i> Top Projects</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <thead><tr><th>Project</th><th class="text-right">Tasks</th></tr></thead>
            <tbody>
            <?php foreach ($tasks_by_project as $r): ?>
            <tr><td><?php echo htmlspecialchars($r['name'] ?: '-'); ?></td><td class="text-right"><span class="badge bg-green"><?php echo $r['cnt']; ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Task Table -->
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-list"></i> Task Details <small>(max 200)</small></h3>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered table-condensed" id="taskReportTable">
        <thead>
          <tr><th>#</th><th>Task</th><th>Project</th><th>Assignee</th><th>Status</th><th>Priority</th><th>Type</th><th>Due Date</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:20px;">No tasks found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $t): ?>
          <?php $overdue = ($t['due_date'] && $t['due_date'] < date('Y-m-d H:i:s') && !in_array($t['status'], array('done','closed'))); ?>
          <tr class="<?php echo $overdue?'danger':''; ?>">
            <td><?php echo $j+1; ?></td>
            <td><a href="<?php echo site_url('task-detail/'.$t['task_id']); ?>"><?php echo htmlspecialchars($t['title']); ?></a></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['project_name'] ?: '-'); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
            <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$t['status']])?$sl[$t['status']]:$t['status']; ?></span></td>
            <td><span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span></td>
            <td><span class="badge badge-type-<?php echo $t['type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['type']])?$tl[$t['type']]:$t['type']; ?></span></td>
            <td style="font-size:12px;"><?php echo $t['due_date'] ? date('d-M-Y', strtotime($t['due_date'])) : '-'; ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

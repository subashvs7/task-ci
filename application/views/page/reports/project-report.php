<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Project Report</h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Project Report</li>
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
          <div class="col-md-3"><div class="form-group"><label>Status</label>
            <select name="f_status" class="form-control">
              <option value="">All Status</option>
              <?php foreach (PROJECT_STATUS_OPT as $k=>$v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Priority</label>
            <select name="f_priority" class="form-control">
              <option value="">All Priority</option>
              <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_priority==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
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
    <div class="col-md-4">
      <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-folder"></i></span>
        <div class="info-box-content"><span class="info-box-text">Total Projects</span><span class="info-box-number"><?php echo $total_projects; ?></span></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-play-circle"></i></span>
        <div class="info-box-content"><span class="info-box-text">Active</span><span class="info-box-number"><?php echo $active_projects; ?></span></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="info-box"><span class="info-box-icon bg-purple"><i class="fa fa-check-circle"></i></span>
        <div class="info-box-content"><span class="info-box-text">Completed</span><span class="info-box-number"><?php echo $completed_projects; ?></span></div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Status Breakdown -->
    <div class="col-md-6">
      <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> By Status</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <?php $psl=PROJECT_STATUS_OPT; foreach ($projects_by_status as $r): ?>
            <tr>
              <td><?php echo isset($psl[$r['status']])?$psl[$r['status']]:$r['status']; ?></td>
              <td class="text-right"><strong><?php echo $r['cnt']; ?></strong></td>
              <td style="width:40%;">
                <?php $pct = $total_projects ? round($r['cnt']/$total_projects*100) : 0; ?>
                <div class="progress progress-xs"><div class="progress-bar" style="width:<?php echo $pct; ?>%"></div></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
    <!-- Priority Breakdown -->
    <div class="col-md-6">
      <div class="box box-warning">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-flag"></i> By Priority</h3></div>
        <div class="box-body no-padding">
          <table class="table table-bordered">
            <?php $pl=TASK_PRIORITY_OPT; foreach ($projects_by_priority as $r): ?>
            <tr>
              <td><span class="badge badge-priority-<?php echo $r['priority']; ?>"><?php echo isset($pl[$r['priority']])?$pl[$r['priority']]:$r['priority']; ?></span></td>
              <td class="text-right"><strong><?php echo $r['cnt']; ?></strong></td>
              <td style="width:40%;">
                <?php $pct = $total_projects ? round($r['cnt']/$total_projects*100) : 0; ?>
                <div class="progress progress-xs"><div class="progress-bar bg-yellow" style="width:<?php echo $pct; ?>%"></div></div>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Project Table -->
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-folder-open"></i> Project Details</h3>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered table-condensed">
        <thead>
          <tr><th>#</th><th>Project</th><th>Status</th><th>Priority</th><th>Tasks</th><th>Done</th><th>Overdue</th><th>Stories</th><th>Epics</th><th>Progress</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="10" class="text-center text-muted" style="padding:20px;">No projects found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $p): ?>
          <?php $pct = $p['total_tasks'] ? round($p['done_tasks']/$p['total_tasks']*100) : 0; $psl=PROJECT_STATUS_OPT; $pl=TASK_PRIORITY_OPT; ?>
          <tr>
            <td><?php echo $j+1; ?></td>
            <td><a href="<?php echo site_url('project-detail/'.$p['project_id']); ?>"><strong><?php echo htmlspecialchars($p['name']); ?></strong></a></td>
            <td><span class="label label-default"><?php echo isset($psl[$p['status']])?$psl[$p['status']]:$p['status']; ?></span></td>
            <td><span class="badge badge-priority-<?php echo $p['priority']; ?>"><?php echo isset($pl[$p['priority']])?$pl[$p['priority']]:$p['priority']; ?></span></td>
            <td><?php echo $p['total_tasks']; ?></td>
            <td><span class="text-green"><?php echo $p['done_tasks']; ?></span></td>
            <td><span class="text-<?php echo $p['overdue_tasks']>0?'red':'muted'; ?>"><?php echo $p['overdue_tasks']; ?></span></td>
            <td><?php echo $p['story_count']; ?></td>
            <td><?php echo $p['epic_count']; ?></td>
            <td style="width:120px;">
              <div class="progress progress-xs"><div class="progress-bar progress-bar-success" style="width:<?php echo $pct; ?>%"></div></div>
              <small><?php echo $pct; ?>%</small>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

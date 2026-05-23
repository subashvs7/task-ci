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
          <div class="col-md-4"><div class="form-group"><label>Project</label>
            <select name="project_id" class="form-control select2" onchange="this.form.submit()">
              <option value="">-- Select a Project --</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-8" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> View Overview</button>
            <?php if ($f_project): ?>
              <button type="submit" name="export" value="excel" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Generate Report Excel</button>
            <?php endif; ?>
            <a href="<?php echo site_url($s_url) ?>" class="btn btn-default">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php if ($f_project): ?>

  <div class="row">
    <!-- Status Breakdown (Pie) -->
    <div class="col-md-4">
      <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-pie-chart"></i> By Status</h3></div>
        <div class="box-body">
          <canvas id="chartStatus" height="250"></canvas>
        </div>
      </div>
    </div>
    <!-- Priority Breakdown (Bar) -->
    <div class="col-md-4">
      <div class="box box-warning">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> By Priority</h3></div>
        <div class="box-body">
          <canvas id="chartPriority" height="250"></canvas>
        </div>
      </div>
    </div>
    <!-- Tasks By Date (Line) -->
    <div class="col-md-4">
      <div class="box box-success">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-line-chart"></i> Tasks By Date</h3></div>
        <div class="box-body">
          <canvas id="chartDate" height="250"></canvas>
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
          <tr><th>#</th><th>Type</th><th>Title</th><th>Project</th><th>Parent Item</th><th>Assignee</th><th>Created By</th><th>Status</th><th>Priority</th><th>Task Type</th><th>Est. Time</th><th>Logged Time</th><th>Due Date</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="13" class="text-center text-muted" style="padding:20px;">No items found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $t): ?>
          <?php $overdue = ($t['due_date'] && $t['due_date'] !== '-' && $t['due_date'] < date('Y-m-d H:i:s') && !in_array($t['status'], array('done','closed'))); ?>
          <tr class="<?php echo $overdue?'danger':''; ?>">
            <td><?php echo $j+1; ?></td>
            <td><span class="badge" style="background:#<?php 
                if($t['item_type']=='Epic') echo 'e67e22';
                elseif($t['item_type']=='Story') echo '1abc9c';
                elseif($t['item_type']=='Task') echo '3498db';
                else echo '7f8c8d'; 
            ?>;"><?php echo htmlspecialchars($t['item_type']); ?></span></td>
            <td><?php echo htmlspecialchars($t['title']); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['project_name'] ?: ''); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['parent_name'] ?: ''); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['assignee_name'] ?: ''); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['reporter_name'] ?: ''); ?></td>
            <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$t['status']])?$sl[$t['status']]:$t['status']; ?></span></td>
            <td><?php if($t['priority'] !== '' && $t['priority'] !== '-'): ?><span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span><?php endif; ?></td>
            <td><?php if($t['task_type'] !== '' && $t['task_type'] !== '-'): ?><span class="badge badge-type-<?php echo $t['task_type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['task_type']])?$tl[$t['task_type']]:$t['task_type']; ?></span><?php endif; ?></td>
            <td style="font-size:12px; text-align: center;"><?php if($t['estimated_hours']): ?><span class="badge" style="background:#9b59b6;"><?php echo $t['estimated_hours'] . 'h'; ?></span><?php endif; ?></td>
            <td style="font-size:12px; text-align: center;"><?php if($t['logged_hours']): ?><span class="badge" style="background:#27ae60;"><?php echo $t['logged_hours'] . 'h'; ?></span><?php endif; ?></td>
            <td style="font-size:12px;"><?php echo ($t['due_date'] && $t['due_date'] !== '' && $t['due_date'] !== '-') ? date('d-M-Y', strtotime($t['due_date'])) : ''; ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    </div>
  </div>

  <?php else: ?>
  <div class="alert alert-info text-center" style="margin-top: 30px;">
    <h4><i class="icon fa fa-info"></i> Please Select a Project</h4>
    <p>Select a project from the filter above to view its report overview and details.</p>
  </div>
  <?php endif; ?>

</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

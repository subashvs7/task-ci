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
            <select name="project_id" class="form-control select2" onchange="this.form.submit()">
              <option value="">-- Select a Project --</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Team Leader</label>
            <select name="team_leader_id" class="form-control select2" onchange="this.form.submit()">
              <option value="">-- All Team Leaders --</option>
              <?php foreach ($team_leaders_list as $tl): ?>
              <option value="<?php echo $tl['user_id']; ?>" <?php echo ($f_leader==$tl['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($tl['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Staff (Assignee)</label>
            <select name="assignee_id" class="form-control select2" onchange="this.form.submit()">
              <option value="">-- All Staff --</option>
              <?php foreach ($staff_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_assignee==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> View Overview</button>
              <button type="submit" name="export" value="excel" class="btn btn-success" title="Export Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
            <a href="<?php echo site_url($s_url) ?>" class="btn btn-default">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>



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

  <?php if ($f_project): ?>
  <!-- Task Details -->
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-sitemap text-primary"></i> Project Tree Flow</h3>
    </div>
    <div class="box-body" style="padding: 15px;">
        <div class="tree-container" style="background:#f9fafc; padding:15px; border-radius:4px; border:1px solid #d2d6de;">
          <?php if (empty($tree_rows)): ?>
            <p class="text-muted text-center" style="padding:20px;">No items found matching the current hierarchy.</p>
          <?php else: ?>
            <div class="tree-node project-node" style="margin-bottom: 15px;">
              <h4 style="margin-top: 0; margin-bottom: 15px;"><i class="fa fa-folder-open text-primary"></i> <strong>Project: <?php echo htmlspecialchars($project_details['name']); ?></strong></h4>
              
              <div class="table-responsive no-padding" style="border: 1px solid #d2d6de; border-radius: 4px; background:#fff;">
                <table class="table table-hover table-bordered" style="margin-bottom:0; font-size:12px;">
                  <thead>
                    <tr style="background:#f4f5f7;">
                      <th style="width: 100px; text-align:center;">Type</th>
                      <th>Title / Name</th>
                      <th>Assignee</th>
                      <th>Created By / Team Leader</th>
                      <th style="width: 160px; text-align:center;">Status</th>
                      <th style="width: 100px; text-align:center;">Priority</th>
                      <th style="width: 100px; text-align:center;">Est. Time</th>
                      <th style="width: 100px; text-align:center;">Logged Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tree_rows as $row): ?>
                      <tr>
                        <td style="text-align:center; vertical-align:middle;">
                          <span class="badge" style="background:#<?php 
                              if($row['item_type']=='Epic') echo 'e67e22';
                              elseif($row['item_type']=='Story') echo '27ae60';
                              elseif($row['item_type']=='Task') echo '3498db';
                              else echo '7f8c8d'; 
                          ?>; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 3px 8px;"><?php echo htmlspecialchars($row['item_type']); ?></span>
                        </td>
                        <td style="vertical-align:middle; padding-left: <?php echo ($row['level'] * 24) + 12; ?>px;">
                          <?php 
                            // Icon based on item type
                            if($row['item_type']=='Epic') echo '<i class="fa fa-bolt" style="color:#e67e22; margin-right:5px;"></i>';
                            elseif($row['item_type']=='Story') echo '<i class="fa fa-bookmark" style="color:#27ae60; margin-right:5px;"></i>';
                            elseif($row['item_type']=='Task') echo '<i class="fa fa-tasks" style="color:#3498db; margin-right:5px;"></i>';
                            else echo '<i class="fa fa-check-square-o" style="color:#7f8c8d; margin-right:5px;"></i>';
                          ?>
                          <span style="<?php echo ($row['item_type']=='Epic' || $row['item_type']=='Story') ? 'font-weight:bold;' : ''; ?>">
                            <?php if ($row['task_id']): ?>
                              <a href="#" class="task-link-modal" data-id="<?php echo $row['task_id']; ?>" style="color: inherit;"><?php echo htmlspecialchars($row['title']); ?></a>
                            <?php else: ?>
                              <?php echo htmlspecialchars($row['title']); ?>
                            <?php endif; ?>
                          </span>
                        </td>
                        <td style="vertical-align:middle;"><?php echo htmlspecialchars($row['assignee_name'] ?: '-'); ?></td>
                        <td style="vertical-align:middle;">
                          <?php echo htmlspecialchars($row['reporter_name'] ?: '-'); ?>
                          <?php if (($row['item_type'] == 'Task' || $row['item_type'] == 'Sub Task') && !empty($row['assignee_name']) && !empty($row['reporter_name']) && $row['assignee_name'] !== '-' && $row['assignee_name'] === $row['reporter_name']): ?>
                            <span class="label label-danger" style="font-size: 10px; margin-left: 5px; padding: 1px 4px; font-weight: bold; background-color: #dd4b39; display: inline-block;">Self Created</span>
                          <?php endif; ?>
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                          <?php if ($row['work_session_status'] === 'active'): ?>
                            <span class="label label-danger" style="font-size: 10px; display:inline-block; padding: 4px 8px;">
                              <i class="fa fa-spinner fa-spin"></i> Working (<?php echo htmlspecialchars($row['active_worker_name']); ?>)
                            </span>
                          <?php else: ?>
                            <span class="badge badge-status-<?php echo htmlspecialchars($row['status']); ?>" style="font-size: 10px; padding: 3px 8px;">
                              <?php 
                                $status_opt = TASK_STATUS_OPT; 
                                echo htmlspecialchars(isset($status_opt[$row['status']]) ? $status_opt[$row['status']] : $row['status']); 
                              ?>
                            </span>
                          <?php endif; ?>
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                          <?php if($row['priority'] && $row['priority'] !== '' && $row['priority'] !== '-'): ?>
                            <span class="badge badge-priority-<?php echo htmlspecialchars($row['priority']); ?>" style="font-size: 10px; padding: 3px 8px;">
                              <?php 
                                $priority_opt = TASK_PRIORITY_OPT; 
                                echo htmlspecialchars(isset($priority_opt[$row['priority']]) ? $priority_opt[$row['priority']] : $row['priority']); 
                              ?>
                            </span>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                          <?php echo $row['estimated_hours'] ? htmlspecialchars($row['estimated_hours']) . 'h' : '-'; ?>
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                          <?php echo $row['logged_hours'] ? htmlspecialchars($row['logged_hours']) . 'h' : '-'; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <div class="alert alert-info text-center" style="margin-top: 30px;">
    <h4><i class="icon fa fa-info"></i> Project Tree Flow Not Available</h4>
    <p>Please select a specific project to view its hierarchical tree flow.</p>
  </div>
  <?php endif; ?>

</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

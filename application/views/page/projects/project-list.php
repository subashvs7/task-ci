<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Projects <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Projects</li>
  </ol>
</section>

<section class="content">

  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo $this->session->flashdata('alert_success'); ?>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo $this->session->flashdata('alert_error'); ?>
    </div>
  <?php endif; ?>

  <!-- Filter Box -->
  <div class="box box-default collapsed-box">
    <div class="box-header with-border" style="cursor:pointer;" data-widget="collapse">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter / Search</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Project</label>
              <select name="search" class="form-control select2">
                <option value="">All Projects</option>
                <?php foreach($projects_dropdown as $pd): ?>
                <option value="<?php echo $pd['project_id']; ?>" <?php echo ($f_search == $pd['project_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($pd['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Status</label>
              <select name="f_status" class="form-control">
                <option value="">All Status</option>
                <?php foreach (PROJECT_STATUS_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_status == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Priority</label>
              <select name="f_priority" class="form-control">
                <option value="">All Priority</option>
                <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_priority == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Main Box -->
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-folder-open"></i> Project List</h3>
      <div class="box-tools pull-right">
        <a href="<?php echo site_url('project-kanban') ?>" class="btn btn-sm btn-default"><i class="fa fa-columns"></i> Kanban View</a>
        <?php if ($this->session->userdata(SESS_HEAD . '_role') === 'admin'): ?>
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addProjectModal">
          <i class="fa fa-plus"></i> Add Project
        </button>
        <?php endif; ?>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Project</th>
            <th>Stacks</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Mgr Deadline</th>
            <th>Tasks</th>
            <th>Progress</th>
            <th>Manager</th>
            <th>Dates</th>
            <th>Remaining Days</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="11" class="text-center text-muted">No projects found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $p):
            $ptask  = (int)$p['task_count'];
            $pdone  = (int)$p['done_count'];
            $ppct   = $ptask > 0 ? round(($pdone / $ptask) * 100) : 0;
            $overdue = (int)$p['overdue_count'];
            
            $row_style = '';
            if ($p['status'] === 'cancelled') {
                $row_style = 'style="background-color: #f8f9fa; opacity: 0.6; text-decoration: line-through;"';
            } elseif ($p['status'] === 'on_hold') {
                $row_style = 'style="background-color: #fff8e1; opacity: 0.8;"';
            }
          ?>
          <tr <?php echo $row_style; ?>>
            <td><?php echo $sno + $j + 1; ?></td>
             <td>
              <a href="#" class="project-link-modal" data-id="<?php echo $p['project_id']; ?>" style="font-weight:600;">
                <?php echo htmlspecialchars($p['name']); ?>
              </a>
              <?php if ($p['description']): ?>
                <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($p['description'], 0, 60)); ?>...</small>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($p['stacks'] ?: '-'); ?></td>
            <td>
              <?php
              $status_map = array(
                'planning'  => array('cls'=>'label-planning',  'label'=>'Planning'),
                'active'    => array('cls'=>'label-active',    'label'=>'Working'),
                'on_hold'   => array('cls'=>'label-on-hold',   'label'=>'On Hold'),
                'completed' => array('cls'=>'label-completed', 'label'=>'Completed'),
                'cancelled' => array('cls'=>'label-cancelled', 'label'=>'Cancelled'),
              );
              $sm = isset($status_map[$p['status']]) ? $status_map[$p['status']] : array('cls'=>'label-default','label'=>$p['status']);
              ?>
              <span class="label <?php echo $sm['cls']; ?>"><?php echo $sm['label']; ?></span>
            </td>
            <td><span class="badge badge-priority-<?php echo $p['priority']; ?>"><?php $pl = TASK_PRIORITY_OPT; echo isset($pl[$p['priority']]) ? $pl[$p['priority']] : $p['priority']; ?></span></td>
            <td>
              <?php echo $p['manager_deadline_days']; ?> days<br>
              <span class="label label-primary" style="font-size:10px; font-weight:normal;">Est: <?php echo round($p['calculated_time_hours'], 1); ?>h</span>
            </td>
            <td>
              <?php echo $pdone; ?>/<?php echo $ptask; ?>
              <?php if ($overdue > 0): ?>
                <br><span class="text-danger" style="font-size:11px;"><i class="fa fa-exclamation-triangle"></i> <?php echo $overdue; ?> overdue</span>
              <?php endif; ?>
            </td>
            <td style="min-width:100px;">
              <div class="progress progress-xs" style="margin-bottom:0;">
                <div class="progress-bar bg-green" style="width:<?php echo $ppct; ?>%;"></div>
              </div>
              <small><?php echo $ppct; ?>%</small>
            </td>
            <td><?php echo htmlspecialchars($p['owner_name'] ?: '-'); ?></td>
            <td style="font-size:11px; white-space:nowrap;">
              <?php echo $p['start_date'] ? date('d-M-Y', strtotime($p['start_date'])) : '-'; ?>
              <?php if ($p['end_date']): ?><br><span style="color:#888;">to <?php echo date('d-M-Y', strtotime($p['end_date'])); ?></span><?php endif; ?>
            </td>
            <td style="text-align:center; white-space:nowrap;">
              <?php
              if ($p['start_date'] && $p['end_date']) {
                  $startDt = new DateTime($p['start_date']);
                  $endDt   = new DateTime($p['end_date']);
                  $today   = new DateTime(date('Y-m-d'));
                  $totalDays     = (int)$startDt->diff($endDt)->days;
                  $remainDiff    = $today->diff($endDt);
                  $remainDays    = (int)$remainDiff->days;
                  $isPast        = $today > $endDt;
                  echo '<div style="line-height:1.8;">';
                  echo '<div style="font-size:11px; color:#888;"><i class="fa fa-calendar"></i> Total: <strong>' . $totalDays . ' days</strong></div>';
                  if ($isPast) {
                      echo '<span class="deadline-badge deadline-over"><i class="fa fa-exclamation-circle"></i> ' . $remainDays . 'd overdue</span>';
                  } elseif ($remainDays == 0) {
                      echo '<span class="deadline-badge deadline-warn"><i class="fa fa-clock-o"></i> Due today</span>';
                  } elseif ($remainDays <= 7) {
                      echo '<span class="deadline-badge deadline-warn"><i class="fa fa-clock-o"></i> ' . $remainDays . 'd left</span>';
                  } else {
                      echo '<span class="deadline-badge deadline-ok"><i class="fa fa-clock-o"></i> ' . $remainDays . 'd left</span>';
                  }
                  echo '</div>';
              } elseif ($p['end_date']) {
                  $endDt  = new DateTime($p['end_date']);
                  $today  = new DateTime(date('Y-m-d'));
                  $diff   = (int)$today->diff($endDt)->days;
                  $isPast = $today > $endDt;
                  if ($isPast) {
                      echo '<span class="deadline-badge deadline-over"><i class="fa fa-exclamation-circle"></i> ' . $diff . 'd overdue</span>';
                  } else {
                      echo '<span class="deadline-badge deadline-ok"><i class="fa fa-clock-o"></i> ' . $diff . 'd left</span>';
                  }
              } else {
                  echo '<span style="color:#bbb; font-size:12px;">—</span>';
              }
              ?>
            </td>
             <td style="white-space:nowrap;">
              <a href="#" class="btn btn-xs btn-info btn-view-project-modal" data-id="<?php echo $p['project_id']; ?>" title="View"><i class="fa fa-eye"></i></a>
              <button class="btn btn-xs btn-warning btn-edit-project"
                data-id="<?php echo $p['project_id']; ?>"
                data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                data-stacks="<?php echo htmlspecialchars($p['stacks'] ?: '', ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($p['description'], ENT_QUOTES); ?>"
                data-status="<?php echo $p['status']; ?>"
                data-priority="<?php echo $p['priority']; ?>"
                data-deadline="<?php echo $p['manager_deadline_days']; ?>"
                data-color="<?php echo htmlspecialchars($p['color'], ENT_QUOTES); ?>"
                data-start="<?php echo $p['start_date']; ?>"
                data-end="<?php echo $p['end_date']; ?>"
                data-owner="<?php echo $p['owner_id']; ?>"
                title="Edit">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $p['project_id']; ?>" data-tbl="tm_projects" data-col="project_id" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="box-footer clearfix">
      <?php echo $pagination; ?>
    </div>
  </div>

</section>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#2c3e50; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add New Project</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Project Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter project name" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Project Stacks (Tech Stack)</label>
                <input type="text" name="stacks" class="form-control" placeholder="e.g. React, Node.js, PHP">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Project description..."></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                  <?php foreach (PROJECT_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Priority</label>
                <select name="priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k == 'medium') ? 'selected' : ''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Manager Deadline (Days)</label>
                <input type="number" name="manager_deadline_days" class="form-control" min="0" value="0">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" id="add_start_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" id="add_end_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Manager</label>
                <select name="owner_id" class="form-control select2">
                  <option value="">-- Select Manager --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div id="add_deadline_wrapper" style="margin-top:-5px; margin-bottom:15px; font-weight:bold; font-size:13px; color:#3c8dbc; display:none;">
                <i class="fa fa-clock-o"></i> Deadline: <span id="add_deadline_days">0</span> days
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="project_id" id="edit_project_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Project</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Project Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Project Stacks (Tech Stack)</label>
                <input type="text" name="stacks" id="edit_stacks" class="form-control" placeholder="e.g. React, Node.js, PHP">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select name="status" id="edit_status" class="form-control">
                  <?php foreach (PROJECT_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Priority</label>
                <select name="priority" id="edit_priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Manager Deadline (Days)</label>
                <input type="number" name="manager_deadline_days" id="edit_deadline_input" class="form-control" min="0">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" id="edit_start_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" id="edit_end_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Manager</label>
                <select name="owner_id" id="edit_owner_id" class="form-control select2">
                  <option value="">-- Select Manager --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div id="edit_deadline_wrapper" style="margin-top:-5px; margin-bottom:15px; font-weight:bold; font-size:13px; color:#3c8dbc; display:none;">
                <i class="fa fa-clock-o"></i> Deadline: <span id="edit_deadline_days">0</span> days
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Update Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

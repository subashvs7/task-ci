<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Tasks <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Tasks</li>
  </ol>
</section>

<section class="content">

  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_error'); ?></div>
  <?php endif; ?>

  <!-- Filter Box -->
  <div class="box box-default">
    <div class="box-header with-border" style="cursor:pointer;" data-widget="collapse">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter / Search</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?php echo ($f_search||$f_status||$f_priority||$f_type||$f_project||$f_assigned||$f_overdue||$f_mine) ? 'minus' : 'plus'; ?>"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Search Title</label>
              <input type="text" name="search" class="form-control" placeholder="Task title..." value="<?php echo htmlspecialchars($f_search ?: ''); ?>">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Project</label>
              <select name="project_id" class="form-control select2">
                <option value="">All Projects</option>
                <?php foreach ($projects_list as $p): ?>
                <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project == $p['project_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Status</label>
              <select name="f_status" class="form-control">
                <option value="">All Status</option>
                <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Priority</label>
              <select name="f_priority" class="form-control">
                <option value="">All Priority</option>
                <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_priority==$k)?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label>Type</label>
              <select name="f_type" class="form-control">
                <option value="">All Types</option>
                <?php foreach (TASK_TYPE_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_type==$k)?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-1" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i></button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Assignee</label>
              <select name="assigned_to" class="form-control select2">
                <option value="">All Users</option>
                <?php foreach ($users_list as $u): ?>
                <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_assigned==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2" style="padding-top:25px;">
            <label><input type="checkbox" name="overdue" value="1" <?php echo $f_overdue ? 'checked' : ''; ?>> Overdue Only</label>
          </div>
          <div class="col-md-2" style="padding-top:25px;">
            <label><input type="checkbox" name="mine" value="1" <?php echo $f_mine ? 'checked' : ''; ?>> My Tasks Only</label>
          </div>
          <div class="col-md-2" style="padding-top:25px;">
            <a href="<?php echo site_url($s_url) ?>" class="btn btn-default btn-block"><i class="fa fa-times"></i> Clear</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Tasks Table -->
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-tasks"></i> Task List</h3>
      <div class="box-tools pull-right">
        <a href="<?php echo site_url('task-kanban') ?>" class="btn btn-sm btn-default"><i class="fa fa-columns"></i> Kanban</a>
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addTaskModal">
          <i class="fa fa-plus"></i> Add Task
        </button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered table-condensed">
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Project</th>
            <th>Type</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Assignee</th>
            <th>Due Date</th>
            <th>Progress</th>
            <th style="width:80px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="10" class="text-center text-muted" style="padding:30px;">No tasks found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $t):
            $is_overdue = !empty($t['due_date']) && strtotime($t['due_date']) < time() && !in_array($t['status'], array('done','closed'));
          ?>
          <tr <?php echo $is_overdue ? 'style="background:#fff8f8;"' : ''; ?>>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <a href="<?php echo site_url('task-detail/' . $t['task_id']) ?>" style="font-weight:600; font-size:13px;">
                <?php echo htmlspecialchars(mb_substr($t['title'], 0, 55)); ?><?php echo strlen($t['title'])>55?'...':''; ?>
              </a>
              <?php if ($t['subtask_count'] > 0): ?>
                <small class="text-muted"><i class="fa fa-sitemap"></i> <?php echo $t['subtask_count']; ?></small>
              <?php endif; ?>
              <?php if ($t['comment_count'] > 0): ?>
                <small class="text-muted"><i class="fa fa-comments"></i> <?php echo $t['comment_count']; ?></small>
              <?php endif; ?>
              <?php if ($t['project_key']): ?><br><code style="font-size:10px;"><?php echo htmlspecialchars($t['project_key']); ?></code><?php endif; ?>
            </td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['project_name'] ?: '-'); ?></td>
            <td><span class="badge badge-type-<?php echo $t['type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['type']])?$tl[$t['type']]:$t['type']; ?></span></td>
            <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$t['status']])?$sl[$t['status']]:$t['status']; ?></span></td>
            <td><span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
            <td style="font-size:12px; <?php echo $is_overdue ? 'color:#c0392b; font-weight:bold;' : ''; ?>">
              <?php echo $t['due_date'] ? date('d-M-Y', strtotime($t['due_date'])) : '-'; ?>
              <?php if ($is_overdue): ?><br><small><i class="fa fa-exclamation-triangle"></i> Overdue</small><?php endif; ?>
            </td>
            <td>
              <div class="progress progress-xs" style="margin-bottom:0;">
                <div class="progress-bar bg-<?php echo $t['completion_percentage']==100?'green':($t['completion_percentage']>=50?'yellow':'red'); ?>" style="width:<?php echo $t['completion_percentage']; ?>%;"></div>
              </div>
              <small style="font-size:10px;"><?php echo $t['completion_percentage']; ?>%</small>
            </td>
            <td style="white-space:nowrap;">
              <a href="<?php echo site_url('task-detail/' . $t['task_id']) ?>" class="btn btn-xs btn-info" title="View"><i class="fa fa-eye"></i></a>
              <button class="btn btn-xs btn-warning btn-edit-task"
                data-id="<?php echo $t['task_id']; ?>"
                data-project="<?php echo $t['project_id']; ?>"
                data-title="<?php echo htmlspecialchars($t['title'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($t['description'] ?? '', ENT_QUOTES); ?>"
                data-status="<?php echo $t['status']; ?>"
                data-priority="<?php echo $t['priority']; ?>"
                data-type="<?php echo $t['type']; ?>"
                data-assigned="<?php echo $t['assigned_to']; ?>"
                data-due="<?php echo $t['due_date'] ? date('Y-m-d', strtotime($t['due_date'])) : ''; ?>"
                data-eh="<?php echo $t['estimate_hours']; ?>"
                data-em="<?php echo $t['estimate_minutes']; ?>"
                data-pct="<?php echo $t['completion_percentage']; ?>"
                title="Edit"><i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $t['task_id']; ?>" data-tbl="tm_tasks" data-col="task_id" title="Delete">
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

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add New Task</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group"><label>Task Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="Enter task title">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Project <span class="text-danger">*</span></label>
                <select name="project_id" class="form-control select2" required>
                  <option value="">-- Select Project --</option>
                  <?php foreach ($projects_list as $p): ?>
                  <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Task description..."></textarea>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group"><label>Type</label>
                <select name="type" class="form-control">
                  <?php foreach (TASK_TYPE_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Status</label>
                <select name="status" class="form-control">
                  <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k=='todo')?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Priority</label>
                <select name="priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k=='medium')?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Due Date</label>
                <input type="date" name="due_date" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Estimate (Hours / Minutes)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" class="form-control" placeholder="Hrs" min="0" value="0" style="width:50%;">
                  <input type="number" name="estimate_minutes" class="form-control" placeholder="Min" min="0" max="59" value="0" style="width:50%;">
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Environment</label>
                <input type="text" name="environment" class="form-control" placeholder="e.g. Production">
              </div>
            </div>
          </div>
          <div class="form-group"><label>Acceptance Criteria</label>
            <textarea name="acceptance_criteria" class="form-control" rows="2" placeholder="What defines this task as done?"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Task</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="task_id" id="edit_task_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Task</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group"><label>Task Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Project</label>
                <select name="project_id" id="edit_project_id" class="form-control select2">
                  <?php foreach ($projects_list as $p): ?>
                  <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group"><label>Type</label>
                <select name="type" id="edit_type" class="form-control">
                  <?php foreach (TASK_TYPE_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Status</label>
                <select name="status" id="edit_status" class="form-control">
                  <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Priority</label>
                <select name="priority" id="edit_priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Due Date</label>
                <input type="date" name="due_date" id="edit_due_date" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" id="edit_assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Estimate (H / M)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" id="edit_estimate_hours" class="form-control" min="0" style="width:50%;">
                  <input type="number" name="estimate_minutes" id="edit_estimate_minutes" class="form-control" min="0" max="59" style="width:50%;">
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Completion %</label>
                <input type="number" name="completion_percentage" id="edit_pct" class="form-control" min="0" max="100">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Update Task</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

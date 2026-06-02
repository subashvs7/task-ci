<?php include_once(VIEWPATH . 'inc/header.php'); ?>
<?php 
$cur_uid  = (int)$this->session->userdata(SESS_HEAD . '_user_id');
$cur_role = $this->session->userdata(SESS_HEAD . '_role');
function format_hours($decimal_hours) {
    if (!$decimal_hours || $decimal_hours <= 0) return '-';
    $h = floor($decimal_hours);
    $m = round(($decimal_hours - $h) * 60);
    $parts = [];
    if ($h > 0) $parts[] = $h.'h';
    if ($m > 0) $parts[] = $m.'m';
    return empty($parts) ? '0m' : implode(' ', $parts);
}
?>

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

  <!-- Filter / Search Card -->
  <div class="panel panel-default" style="border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #e3e8ee; margin-bottom: 20px;">
    <div class="panel-body" style="padding: 15px 20px; background-color: #fdfdfd;">
      <form method="get" action="<?php echo site_url($s_url) ?>" id="taskFilterForm">
        <div class="row" style="margin-bottom: 10px;">
          
          <!-- Project Filter -->
          <div class="col-md-3">
            <select name="project_id" class="form-control select2">
              <option value="">All Projects</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project == $p['project_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="col-md-3">
            <select name="f_status" class="form-control">
              <option value="">All Statuses</option>
              <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Priority Filter -->
          <div class="col-md-3">
            <select name="f_priority" class="form-control">
              <option value="">All Priorities</option>
              <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_priority==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <!-- Assignee Filter -->
          <div class="col-md-3">
            <select name="assigned_to" class="form-control select2">
              <option value="">All Assignees</option>
              <?php foreach ($users_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_assigned==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
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
        <?php $ur = $this->session->userdata(SESS_HEAD . '_role'); ?>
        <?php if ($ur !== 'staff'): ?>
        <a href="<?php echo site_url('task-kanban') ?>" class="btn btn-sm btn-default"><i class="fa fa-columns"></i> Kanban</a>
        <?php endif; ?>
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addTaskModal">
          <i class="fa fa-plus"></i> Add Task
        </button>
      </div>
    </div>
    <?php
    $show_actions = false;
    if (in_array($cur_role, ['admin', 'manager', 'team_leader'])) {
        $show_actions = true;
    } else if (!empty($record_list)) {
        foreach ($record_list as $t) {
            if ($t['created_by'] == $cur_uid) {
                $show_actions = true;
                break;
            }
        }
    }
    ?>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered table-condensed">
        <thead>
          <tr>
            <th>#</th>
            <th>Project</th>
            <th>Title</th>
            <th>Document</th>
            <th>Epic</th>
            <th>Type</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Assignee</th>
            <th>Assigned By</th>
            <th>Work Status</th>
            <th>Due Date</th>
            <th>Est. Time</th>
            <th>Logged Time</th>
            <?php if ($show_actions): ?>
            <th style="width:90px;">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="<?php echo $show_actions ? '15' : '14'; ?>" class="text-center text-muted" style="padding:30px;">No tasks found.</td></tr>
          <?php else: ?>
          <?php
          foreach ($record_list as $j => $t):
            $is_date_overdue   = !empty($t['due_date']) && strtotime($t['due_date']) < strtotime(date('Y-m-d')) && !in_array($t['status'], array('done','closed'));
            $logged_h          = (float)$t['logged_hours'];
            $is_active_session = ($t['work_session_status'] === 'active');
            if ($is_active_session && !empty($t['open_session_start'])) {
                $start_time = strtotime($t['open_session_start']);
                $elapsed = (time() - $start_time) / 3600;
                if ($elapsed > 0) {
                    $logged_h += $elapsed;
                }
            }
            $estimated_h       = !empty($t['estimated_hours']) ? (float)$t['estimated_hours'] : 0;
            $is_effort_overdue = $estimated_h > 0 && $logged_h > $estimated_h;
            $is_my_session     = $is_active_session && ((int)$t['active_session_user'] === $cur_uid);
            $is_done_closed    = in_array($t['status'], array('done','closed'));
            $can_toggle        = empty($t['story_id']); // Allow toggling if it's a standalone task (no user story)
            $is_mine_task      = ($cur_role === 'staff') ? ((int)$t['assigned_to'] === $cur_uid) : true; // Changed to allow all users to start tasks assigned to them, admins can start any
            $row_style = '';
            if ($is_my_session)        $row_style = 'style="background:#f0fff4; border-left:3px solid #27ae60;"';
            elseif ($is_date_overdue)  $row_style = 'style="background:#fff8f8;"';
          ?>
          <tr <?php echo $row_style; ?>>
            <td><?php echo $sno + $j + 1; ?></td>
            <td style="font-size:12px;">
              <?php if ($t['project_id']): ?>
                <a href="#" class="project-link-modal" data-id="<?php echo $t['project_id']; ?>"><?php echo htmlspecialchars($t['project_name']); ?></a>
                <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $t['project_id']; ?>" style="padding: 1px 3px; border-radius: 3px; font-size: 9px;" title="Quick View Team &amp; Effort"><i class="fa fa-eye"></i></button>
              <?php else: ?>-<?php endif; ?>
            </td>
            <td>
              <a href="#" class="task-link-modal" data-id="<?php echo $t['task_id']; ?>" style="font-weight:600; font-size:13px;">
                <?php echo htmlspecialchars(mb_substr($t['title'], 0, 55)); ?><?php echo strlen($t['title'])>55?'...':''; ?>
              </a>
              <?php if ($t['subtask_count'] > 0): ?>
                <small class="text-muted" style="margin-left:4px;"><i class="fa fa-sitemap"></i> <?php echo $t['subtask_count']; ?></small>
              <?php endif; ?>
              <?php if ($t['comment_count'] > 0): ?>
                <small class="text-muted"><i class="fa fa-comments"></i> <?php echo $t['comment_count']; ?></small>
              <?php endif; ?>
              <?php if ($t['project_key']): ?><br><code style="font-size:10px;"><?php echo htmlspecialchars($t['project_key']); ?></code><?php endif; ?>
            </td>
            <td style="text-align:center;">
              <?php if (!empty($t['document'])): ?>
                <button type="button" class="btn btn-xs btn-default btn-preview-doc" data-document="<?php echo htmlspecialchars($t['document'], ENT_QUOTES); ?>" data-id="<?php echo $t['task_id']; ?>" title="View Document">
                  <i class="fa fa-file-pdf-o text-danger"></i> Docs
                </button>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($t['epic_name'])): ?>
                <span class="label" style="background-color:#9b59b6; color:#fff; font-size:10px; padding:3px 6px;"><i class="fa fa-bolt"></i> <?php echo htmlspecialchars(mb_substr($t['epic_name'], 0, 30)); ?></span>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td><span class="badge badge-type-<?php echo $t['type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['type']])?$tl[$t['type']]:$t['type']; ?></span></td>
            <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$t['status']])?$sl[$t['status']]:$t['status']; ?></span></td>
            <td><span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($t['reporter_name'] ?: '-'); ?></td>

            <!-- Work Session Status Cell -->
            <td style="white-space:nowrap; text-align:center; min-width:110px; padding:6px 4px;">
              <?php if ($is_done_closed): ?>
                <span class="label label-success" style="font-size:10px;"><i class="fa fa-check-circle"></i> Completed</span>
              <?php elseif ($is_my_session): ?>
                <button class="btn btn-xs btn-danger btn-task-session"
                  data-task="<?php echo $t['task_id']; ?>"
                  data-action="stop"
                  style="font-size:10px; font-weight:600; padding:2px 7px;">
                  <i class="fa fa-stop-circle"></i> Stop Work
                </button>
                <br><span class="session-timer text-success" data-start-ts="<?php echo strtotime($t['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($t['open_session_start']); ?>" style="font-size:11px; font-weight:700; font-family:monospace;">00:00:00</span>
                <br><button class="btn btn-xs btn-primary btn-task-complete" data-task="<?php echo $t['task_id']; ?>" style="font-size:9px; margin-top:3px; padding:1px 5px;"><i class="fa fa-check"></i> Complete</button>
              <?php elseif ($is_active_session): ?>
                <span style="color:#e67e22; font-size:11px; font-weight:600;">
                  <i class="fa fa-circle" style="color:#e67e22;"></i> Working
                </span>
                <?php if (!empty($t['active_worker_name'])): ?>
                <br><small class="text-muted" style="font-size:10px;"><?php echo htmlspecialchars($t['active_worker_name']); ?></small>
                <?php endif; ?>
              <?php elseif (!empty($t['active_child_count']) && $t['active_child_count'] > 0): ?>
                <span style="color:#e67e22; font-size:11px; font-weight:600;">
                  <i class="fa fa-circle" style="color:#e67e22;"></i> Working
                </span>
                <?php if (!empty($t['child_worker_name'])): ?>
                <br><small class="text-muted" style="font-size:10px;"><?php echo htmlspecialchars($t['child_worker_name']); ?></small>
                <?php endif; ?>
              <?php elseif ($can_toggle && $is_mine_task): ?>
                <button class="btn btn-xs btn-success btn-task-session"
                  data-task="<?php echo $t['task_id']; ?>"
                  data-action="start"
                  style="font-size:10px; font-weight:600; padding:2px 7px;">
                  <i class="fa fa-play-circle"></i> Start Work
                </button>
                <br><button class="btn btn-xs btn-primary btn-task-complete" data-task="<?php echo $t['task_id']; ?>" style="font-size:9px; margin-top:3px; padding:1px 5px;"><i class="fa fa-check"></i> Complete</button>
              <?php else: ?>
                <span class="text-muted" style="font-size:11px;"><i class="fa fa-circle-o"></i> Not Started</span>
              <?php endif; ?>
            </td>

            <!-- Due Date with Date Overdue badge -->
            <td style="font-size:12px; <?php echo $is_date_overdue ? 'color:#c0392b; font-weight:bold;' : ''; ?>">
              <?php echo $t['due_date'] ? date('d-M-Y', strtotime($t['due_date'])) : '-'; ?>
              <?php if ($is_date_overdue): ?>
                <br><span class="label label-danger" style="font-size:9px; padding:1px 4px;"><i class="fa fa-calendar-times-o"></i> Date Overdue</span>
              <?php endif; ?>
              <?php 
              if ($t['due_date']) {
                  $now_date = strtotime(date('Y-m-d'));
                  $due_date = strtotime(date('Y-m-d', strtotime($t['due_date'])));
                  $days = ($due_date - $now_date) / 86400;
                  echo '<br>';
                  if ($is_done_closed) {
                      echo '<span class="text-muted" style="font-size:10px;">Completed</span>';
                  } elseif ($days < 0) {
                      echo '<span class="text-danger" style="font-size:10px;">' . abs($days) . ' days overdue</span>';
                  } elseif ($days == 0) {
                      echo '<span class="text-warning" style="font-size:10px;">Due today</span>';
                  } else {
                      echo '<span class="text-success" style="font-size:10px;">' . $days . ' days remaining</span>';
                  }
              }
              ?>
            </td>

            <td style="font-size:12px; font-weight:600; color:#34495e;">
              <?php if ($estimated_h > 0): ?>
                <?php echo format_hours($estimated_h); ?>
                <?php if (!empty($t['epic_estimated_time']) && $t['epic_estimated_time'] > 0): ?>
                  <div style="margin-top:4px; font-size:10px; color:#9b59b6;" title="Epic Estimated Time">
                    <i class="fa fa-bolt"></i> <?php echo format_hours((float)$t['epic_estimated_time'] / 60); ?>
                  </div>
                <?php endif; ?>
              <?php elseif (!empty($t['epic_estimated_time']) && $t['epic_estimated_time'] > 0): ?>
                <div style="font-size:14px; color:#9b59b6;" title="Epic Estimated Time">
                  <i class="fa fa-bolt"></i> <?php echo format_hours((float)$t['epic_estimated_time'] / 60); ?>
                </div>
              <?php else: ?>
                --
              <?php endif; ?>
            </td>
            
            <td style="font-size:12px; font-weight:600; <?php echo $is_effort_overdue ? 'color:#e74c3c; background:#fdf0ed;' : 'color:#27ae60; background:#f0fff4;'; ?>">
              <?php echo format_hours($logged_h); ?>
              <?php if ($is_effort_overdue): ?>
                <br><span class="label label-danger" style="font-size:9px; padding:2px 4px; display:inline-block; margin-top:3px;"><i class="fa fa-warning"></i> OVERTIME</span>
              <?php endif; ?>
            </td>

            <!-- Actions -->
            <?php if ($show_actions): ?>
            <td style="white-space:nowrap;">
              <?php if (in_array($cur_role, ['admin', 'manager', 'team_leader']) || $t['created_by'] == $cur_uid): ?>
              <button class="btn btn-xs btn-warning btn-edit-task"
                data-id="<?php echo $t['task_id']; ?>"
                data-project="<?php echo $t['project_id']; ?>"
                data-story="<?php echo $t['story_id']; ?>"
                data-title="<?php echo htmlspecialchars($t['title'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($t['description'] ?? '', ENT_QUOTES); ?>"
                data-status="<?php echo $t['status']; ?>"
                data-priority="<?php echo $t['priority']; ?>"
                data-type="<?php echo $t['type']; ?>"
                data-assigned="<?php echo $t['assigned_to']; ?>"
                data-reporter="<?php echo $t['reporter_id']; ?>"
                data-due="<?php echo $t['due_date'] ? date('Y-m-d', strtotime($t['due_date'])) : ''; ?>"
                data-eh="<?php echo $t['estimate_hours']; ?>"
                data-em="<?php echo $t['estimate_minutes']; ?>"
                data-pct="<?php echo $t['completion_percentage']; ?>"
                data-document="<?php echo htmlspecialchars($t['document'] ?? '', ENT_QUOTES); ?>"
                title="Edit"><i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $t['task_id']; ?>" data-tbl="tm_tasks" data-col="task_id" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
              <?php endif; ?>
            </td>
            <?php endif; ?>
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
      <form action="<?php echo site_url($s_url) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add New Task</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group"><label>Task Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="Enter task title">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-<?php echo ($cur_role === 'manager') ? '12' : '4'; ?>">
              <div class="form-group"><label>Project <span class="text-danger">*</span></label>
                <select name="project_id" class="form-control select2" id="add_task_project" required>
                  <option value="">-- Select Project --</option>
                  <?php foreach ($projects_list as $p): ?>
                  <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php if ($cur_role !== 'manager'): ?>
            <div class="col-md-<?php echo ($cur_role === 'team_leader') ? '6' : '4'; ?>">
              <div class="form-group"><label>Epic</label>
                <select name="epic_id" id="add_task_epic" class="form-control">
                  <option value="">-- Select Epic --</option>
                  <?php foreach ($epics_list as $e): ?>
                  <option value="<?php echo $e['epic_id']; ?>" data-project="<?php echo $e['project_id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php if ($cur_role !== 'team_leader'): ?>
            <div class="col-md-4">
              <div class="form-group"><label>Story</label>
                <select name="story_id" id="add_task_story" class="form-control">
                  <option value="">-- Select Story --</option>
                  <?php foreach ($stories_list as $s): ?>
                  <option value="<?php echo $s['story_id']; ?>" data-project="<?php echo $s['project_id']; ?>" data-epic="<?php echo $s['epic_id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Task description..."></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group"><label>Type</label>
                <select name="type" class="form-control">
                  <?php foreach (TASK_TYPE_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Priority</label>
                <select name="priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k=='medium')?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Due Date</label>
                <input type="date" name="due_date" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '4'; ?>">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php 
                  $cur_role = $this->session->userdata(SESS_HEAD . '_role');
                  foreach ($users_list as $u): 
                      if ($cur_role === 'manager' && $u['role'] !== 'team_leader' && $u['user_id'] != $cur_uid) continue;
                      if ($cur_role === 'team_leader' && $u['role'] !== 'staff' && $u['user_id'] != $cur_uid) continue;
                  ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?> (<?php echo ucfirst(str_replace('_', ' ', $u['role'])); ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <input type="hidden" name="reporter_id" value="<?php echo $this->session->userdata(SESS_HEAD . '_user_id'); ?>">
            <?php if (!in_array($cur_role, array('manager', 'team_leader'))): ?>
            <div class="col-md-4">
              <div class="form-group"><label>Estimate (Hours / Minutes)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" class="form-control" placeholder="Hrs" min="0" value="0" style="width:50%;">
                  <input type="number" name="estimate_minutes" class="form-control" placeholder="Min" min="0" max="59" value="0" style="width:50%;">
                </div>
              </div>
            </div>
            <?php endif; ?>
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '4'; ?>">
              <div class="form-group"><label>Environment</label>
                <input type="text" name="environment" class="form-control" placeholder="e.g. Production">
              </div>
            </div>
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '12'; ?>">
              <div class="form-group"><label>Screenshot / Document</label>
                <input type="file" name="document" class="form-control" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
              </div>
            </div>
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
      <form action="<?php echo site_url($s_url) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="task_id" id="edit_task_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Task</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group"><label>Task Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-<?php echo ($cur_role === 'manager') ? '12' : '4'; ?>">
              <div class="form-group"><label>Project</label>
                <select name="project_id" id="edit_project_id" class="form-control select2">
                  <option value="">-- Select Project --</option>
                  <?php foreach ($projects_list as $p): ?>
                  <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php if ($cur_role !== 'manager'): ?>
            <div class="col-md-<?php echo ($cur_role === 'team_leader') ? '6' : '4'; ?>">
              <div class="form-group"><label>Epic</label>
                <select name="epic_id" id="edit_task_epic" class="form-control">
                  <option value="">-- Select Epic --</option>
                  <?php foreach ($epics_list as $e): ?>
                  <option value="<?php echo $e['epic_id']; ?>" data-project="<?php echo $e['project_id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php if ($cur_role !== 'team_leader'): ?>
            <div class="col-md-4">
              <div class="form-group"><label>Story</label>
                <select name="story_id" id="edit_task_story" class="form-control">
                  <option value="">-- Select Story --</option>
                  <?php foreach ($stories_list as $s): ?>
                  <option value="<?php echo $s['story_id']; ?>" data-project="<?php echo $s['project_id']; ?>" data-epic="<?php echo $s['epic_id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
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
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '4'; ?>">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" id="edit_assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <?php if (!in_array($cur_role, array('manager', 'team_leader'))): ?>
            <div class="col-md-4">
              <div class="form-group"><label>Estimate (H / M)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" id="edit_estimate_hours" class="form-control" min="0" style="width:50%;">
                  <input type="number" name="estimate_minutes" id="edit_estimate_minutes" class="form-control" min="0" max="59" style="width:50%;">
                </div>
              </div>
            </div>
            <?php endif; ?>
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '4'; ?>">
              <div class="form-group"><label>Completion %</label>
                <input type="number" name="completion_percentage" id="edit_pct" class="form-control" min="0" max="100">
              </div>
            </div>
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '12'; ?>">
              <div class="form-group"><label>Screenshot / Document</label>
                <input type="file" name="document" class="form-control" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                <div id="edit_document_container" style="margin-top: 10px; display: none;">
                  <button type="button" class="btn btn-sm btn-info" id="btn-view-document"><i class="fa fa-eye"></i> View Current Document</button>
                </div>
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

<script>
$(document).ready(function() {
    $('#taskFilterForm select, #taskFilterForm input[type="checkbox"]').on('change', function() {
        $('#taskFilterForm')[0].submit();
    });

    function filterCascading(projSelect, epicSelect, storySelect) {
        function updateEpics() {
            var pid = $(projSelect).val();
            $(epicSelect + ' option').each(function() {
                if ($(this).val() === '') return;
                if ($(this).data('project') == pid) $(this).show(); else $(this).hide();
            });
            $(epicSelect).val('');
            updateStories();
        }
        function updateStories() {
            var pid = $(projSelect).val();
            var eid = $(epicSelect).val();
            $(storySelect + ' option').each(function() {
                if ($(this).val() === '') return;
                var show = true;
                if (pid && $(this).data('project') != pid) show = false;
                if (eid && $(this).data('epic') != eid) show = false;
                if (show) $(this).show(); else $(this).hide();
            });
            $(storySelect).val('');
        }
        $(projSelect).on('change', updateEpics);
        $(epicSelect).on('change', updateStories);
    }
    
    filterCascading('#add_task_project', '#add_task_epic', '#add_task_story');
    filterCascading('#edit_project_id', '#edit_task_epic', '#edit_task_story');

    // Hack to populate the edit modal correctly when the edit button is clicked
    $('.btn-edit-task').on('click', function() {
        var story_id = $(this).data('story');
        setTimeout(function() {
            // Because filterCascading clears the val on project change, we need to re-set it after
            if (story_id) {
                // Find epic of this story
                var epic_id = $('#edit_task_story option[value="'+story_id+'"]').data('epic');
                $('#edit_task_epic').val(epic_id).trigger('change');
                $('#edit_task_story').val(story_id);
            }
        }, 100);
    });
});
</script>


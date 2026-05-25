<?php include_once(VIEWPATH . 'inc/header.php'); 
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
  <h1>User Stories <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">User Stories</li>
  </ol>
</section>

<section class="content">
  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="box box-default collapsed-box">
    <div class="box-header with-border" data-widget="collapse" style="cursor:pointer;">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
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
          <div class="col-md-3"><div class="form-group"><label>Epic</label>
            <select name="epic_id" class="form-control select2">
              <option value="">All Epics</option>
              <?php foreach ($epics_list as $e): ?>
              <option value="<?php echo $e['epic_id']; ?>" <?php echo ($f_epic==$e['epic_id'])?'selected':''; ?>><?php echo htmlspecialchars($e['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Status</label>
            <select name="f_status" class="form-control">
              <option value="">All Status</option>
              <?php foreach (TASK_STATUS_OPT as $k=>$v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-2" style="padding-top:25px;"><button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Search</button></div>
        </div>
      </form>
    </div>
  </div>

  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-book"></i> User Story List</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addStoryModal"><i class="fa fa-plus"></i> Add Story</button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr><th>#</th><th>Story Name</th><th>Project</th><th>Epic</th><th>Status</th><th>Priority</th><th>TL Est. Time</th><th>Est. Time</th><th>Assignee</th><th>Tasks</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="10" class="text-center text-muted" style="padding:30px;">No user stories found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $s): ?>
          <tr>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <strong><?php echo htmlspecialchars($s['name']); ?></strong>
            </td>
            <td style="font-size:12px;">
              <?php if ($s['project_id']): ?>
                <a href="#" class="project-link-modal" data-id="<?php echo $s['project_id']; ?>"><?php echo htmlspecialchars($s['project_name']); ?></a>
                <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $s['project_id']; ?>" style="padding: 1px 3px; border-radius: 3px; font-size: 9px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($s['epic_name'] ?: '-'); ?></td>
            <td><span class="badge badge-status-<?php echo $s['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$s['status']])?$sl[$s['status']]:$s['status']; ?></span></td>
            <td><span class="badge badge-priority-<?php echo $s['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$s['priority']])?$pl[$s['priority']]:$s['priority']; ?></span></td>
            <td><?php echo format_hours($s['epic_estimated_time'] ? ($s['epic_estimated_time'] / 60) : 0); ?></td>
            <td><span class="badge bg-purple" style="font-size:11px;"><?php echo format_hours($s['calculated_time_hours']); ?></span></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($s['assignee_name'] ?: '-'); ?></td>
            <td>
              <a href="#" class="btn btn-xs btn-info" onclick="$(this).closest('tr').next('.sub-tasks-row').toggle(); return false;"><?php echo $s['task_count']; ?> <i class="fa fa-tasks"></i></a>
              <?php if (in_array($this->session->userdata(SESS_HEAD . '_role'), ['admin','manager','team_leader','staff'])): ?>
              <button class="btn btn-xs btn-success btn-add-subtask" data-story="<?php echo $s['story_id']; ?>" data-project="<?php echo $s['project_id']; ?>" data-epic="<?php echo $s['epic_id']; ?>" data-epic-time="<?php echo $s['epic_estimated_time']; ?>" title="Add Task"><i class="fa fa-plus"></i></button>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn btn-xs btn-warning btn-edit-story"
                data-id="<?php echo $s['story_id']; ?>"
                data-project="<?php echo $s['project_id']; ?>"
                data-epic="<?php echo $s['epic_id']; ?>"
                data-name="<?php echo htmlspecialchars($s['name'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($s['description'], ENT_QUOTES); ?>"
                data-status="<?php echo $s['status']; ?>"
                data-priority="<?php echo $s['priority']; ?>"
                data-assignee="<?php echo $s['assignee_id']; ?>">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $s['story_id']; ?>" data-tbl="tm_user_stories" data-col="story_id"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <tr class="sub-tasks-row" style="display:none; background-color:#f9fbfc;">
            <td colspan="10" style="padding:15px;">
              <div style="background:#fff; border:1px solid #e1e8ed; border-radius:4px; padding:10px;">
                <strong style="display:block; margin-bottom:10px; color:#34495e;"><i class="fa fa-tasks"></i> Tasks for "<?php echo htmlspecialchars($s['name']); ?>"</strong>
                <?php if (empty($s['tasks_list'])): ?>
                  <p class="text-muted" style="margin:0; font-size:12px;">No tasks created yet.</p>
                <?php else: ?>
                  <table class="table table-condensed table-bordered" style="margin-bottom:0; font-size:12px;">
                    <thead style="background:#f4f6f8;"><tr><th>#</th><th>Task Name</th><th>Status</th><th>My Est. Time</th><th>Logged Time</th><th>Work Status</th><th style="text-align:center;">Actions</th></tr></thead>
                    <tbody>
                      <?php foreach ($s['tasks_list'] as $tj => $task): ?>
                        <?php 
                          $is_active_session = ($task['work_session_status'] === 'active');
                          $is_my_session     = $is_active_session && ((int)$task['active_session_user'] === (int)$this->session->userdata(SESS_HEAD . '_user_id'));
                          $is_done_closed    = in_array($task['status'], array('done','closed'));
                          $can_toggle        = !$is_done_closed; 
                          $row_bg            = $is_my_session ? 'background:#f0fff4;' : '';
                          
                          $est_h = (float)$task['estimated_hours'];
                          $log_h = (float)$task['logged_hours'];
                          
                          if ($is_active_session && !empty($task['open_session_start'])) {
                              $start_time = strtotime($task['open_session_start']);
                              $elapsed = (time() - $start_time) / 3600;
                              if ($elapsed > 0) {
                                  $log_h += $elapsed;
                              }
                          }

                          $is_overdue = ($est_h > 0 && $log_h > $est_h);
                          $over_h = max(0, $log_h - $est_h);
                          
                          $log_display = round($log_h, 2) . 'h';
                          if ($is_overdue) {
                              $log_display .= ' <span style="color:#e74c3c; font-weight:bold; font-size:10px; display:inline-block;"><br><i class="fa fa-warning"></i> +' . round($over_h, 2) . 'h Over</span>';
                          } elseif ($est_h > 0 && $log_h == $est_h) {
                              $log_display .= ' <span style="color:#f39c12; font-weight:bold; font-size:10px; display:inline-block;"><br>Limit Reached</span>';
                          }
                        ?>
                        <tr style="<?php echo $row_bg; ?>">
                          <td><?php echo $tj + 1; ?></td>
                          <td><a href="#" class="task-link-modal" data-id="<?php echo $task['task_id']; ?>"><?php echo htmlspecialchars($task['title']); ?></a></td>
                          <td>
                            <span class="badge badge-status-<?php echo $task['status']; ?>" style="<?php echo $is_active_session ? 'background-color:#10b981;' : ''; ?>">
                              <?php 
                                if ($is_active_session) {
                                    echo 'Working';
                                } else {
                                    echo isset(TASK_STATUS_OPT[$task['status']]) ? TASK_STATUS_OPT[$task['status']] : $task['status'];
                                }
                              ?>
                            </span>
                          </td>
                          <td><?php echo format_hours($est_h); ?></td>
                          <td><?php echo $log_display; ?></td>
                          <td style="min-width:110px;">
                            <?php if ($is_done_closed): ?>
                              <span class="label label-success"><i class="fa fa-check-circle"></i> Completed</span>
                            <?php elseif ($is_my_session): ?>
                              <button class="btn btn-xs btn-danger btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="stop" style="font-weight:600;"><i class="fa fa-stop-circle"></i> Stop Work</button>
                              <br><span class="session-timer text-success" data-start-ts="<?php echo strtotime($task['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($task['open_session_start']); ?>" style="font-size:11px; font-weight:700; font-family:monospace;">00:00:00</span>
                            <?php elseif ($is_active_session): ?>
                              <span style="color:#e67e22;"><i class="fa fa-circle"></i> Working (<?php echo htmlspecialchars($task['active_worker_name']); ?>)</span>
                            <?php elseif ($can_toggle): ?>
                              <button class="btn btn-xs btn-success btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="start" style="font-weight:600;"><i class="fa fa-play-circle"></i> Start Work</button>
                            <?php else: ?>
                              <span class="text-muted"><i class="fa fa-circle-o"></i> Not Started</span>
                            <?php endif; ?>
                          </td>
                          <td style="min-width:90px; text-align:center;">
                            <?php if (!$is_done_closed): ?>
                                <?php if (in_array($this->session->userdata(SESS_HEAD.'_role'), ['admin','manager','team_leader']) || $task['assigned_to'] == $this->session->userdata(SESS_HEAD.'_user_id')): ?>
                                  <button class="btn btn-xs btn-primary btn-task-complete" data-task="<?php echo $task['task_id']; ?>" style="font-weight:600;"><i class="fa fa-check"></i> Complete</button>
                                <?php endif; ?>
                            <?php endif; ?>
                          </td>
                        </tr>

                        <!-- Render Sub Tasks of this Task -->
                        <?php if (!empty($task['sub_tasks'])): ?>
                          <?php foreach ($task['sub_tasks'] as $sub): ?>
                            <?php
                              $sub_is_active_session = ($sub['work_session_status'] === 'active');
                              $sub_is_my_session     = $sub_is_active_session && ((int)$sub['active_session_user'] === (int)$this->session->userdata(SESS_HEAD . '_user_id'));
                              $sub_is_done_closed    = in_array($sub['status'], array('done','closed'));
                              $sub_can_toggle        = !$sub_is_done_closed; 
                              $sub_row_bg            = $sub_is_my_session ? 'background:#f0fff4;' : 'background:#fafbfc;';
                              
                              $sub_est_h = (float)$sub['estimated_hours'];
                              $sub_log_h = (float)$sub['logged_hours'];
                              
                              if ($sub_is_active_session && !empty($sub['open_session_start'])) {
                                  $sub_start_time = strtotime($sub['open_session_start']);
                                  $sub_elapsed = (time() - $sub_start_time) / 3600;
                                  if ($sub_elapsed > 0) {
                                      $sub_log_h += $sub_elapsed;
                                  }
                              }

                              $sub_is_overdue = ($sub_est_h > 0 && $sub_log_h > $sub_est_h);
                              $sub_over_h = max(0, $sub_log_h - $sub_est_h);
                              
                              $sub_log_display = round($sub_log_h, 2) . 'h';
                              if ($sub_is_overdue) {
                                  $sub_log_display .= ' <span style="color:#e74c3c; font-weight:bold; font-size:10px; display:inline-block;"><br><i class="fa fa-warning"></i> +' . round($sub_over_h, 2) . 'h Over</span>';
                              } elseif ($sub_est_h > 0 && $sub_log_h == $sub_est_h) {
                                  $sub_log_display .= ' <span style="color:#f39c12; font-weight:bold; font-size:10px; display:inline-block;"><br>Limit Reached</span>';
                              }
                            ?>
                            <tr style="<?php echo $sub_row_bg; ?>">
                              <td></td>
                              <td style="padding-left: 25px;">
                                <i class="fa fa-level-up fa-rotate-90 text-muted" style="margin-right: 5px;"></i>
                                <a href="#" class="task-link-modal" data-id="<?php echo $sub['task_id']; ?>"><?php echo htmlspecialchars($sub['title']); ?></a>
                                <span class="label label-xs label-default" style="font-size: 8px; padding: 1px 3px; font-weight: normal; margin-left: 5px;">Sub Task</span>
                              </td>
                              <td>
                                <span class="badge badge-status-<?php echo $sub['status']; ?>" style="<?php echo $sub_is_active_session ? 'background-color:#10b981;' : ''; ?>">
                                  <?php 
                                    if ($sub_is_active_session) {
                                        echo 'Working';
                                    } else {
                                        echo isset(TASK_STATUS_OPT[$sub['status']]) ? TASK_STATUS_OPT[$sub['status']] : $sub['status'];
                                    }
                                  ?>
                                </span>
                              </td>
                              <td><?php echo format_hours($sub_est_h); ?></td>
                              <td><?php echo $sub_log_display; ?></td>
                              <td>
                                <?php if ($sub_is_done_closed): ?>
                                  <span class="label label-success"><i class="fa fa-check-circle"></i> Completed</span>
                                <?php elseif ($sub_is_my_session): ?>
                                  <button class="btn btn-xs btn-danger btn-task-session" data-task="<?php echo $sub['task_id']; ?>" data-action="stop" style="font-weight:600;"><i class="fa fa-stop-circle"></i> Stop Work</button>
                                  <br><span class="session-timer text-success" data-start-ts="<?php echo strtotime($sub['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($sub['open_session_start']); ?>" style="font-size:11px; font-weight:700; font-family:monospace;">00:00:00</span>
                                <?php elseif ($sub_is_active_session): ?>
                                  <span style="color:#e67e22;"><i class="fa fa-circle"></i> Working (<?php echo htmlspecialchars($sub['active_worker_name']); ?>)</span>
                                <?php elseif ($sub_can_toggle): ?>
                                  <button class="btn btn-xs btn-success btn-task-session" data-task="<?php echo $sub['task_id']; ?>" data-action="start" style="font-weight:600;"><i class="fa fa-play-circle"></i> Start Work</button>
                                <?php else: ?>
                                  <span class="text-muted"><i class="fa fa-circle-o"></i> Not Started</span>
                                <?php endif; ?>
                              </td>
                              <td style="text-align:center;">
                                <?php if (!$sub_is_done_closed): ?>
                                    <?php if (in_array($this->session->userdata(SESS_HEAD.'_role'), ['admin','manager','team_leader']) || $sub['assigned_to'] == $this->session->userdata(SESS_HEAD.'_user_id')): ?>
                                      <button class="btn btn-xs btn-primary btn-task-complete" data-task="<?php echo $sub['task_id']; ?>" style="font-weight:600;"><i class="fa fa-check"></i> Complete</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Render Checklist Subtasks of this Task -->
                        <?php if (!empty($task['checklist'])): ?>
                          <?php foreach ($task['checklist'] as $chk): ?>
                            <tr style="background:#fafbfc;">
                              <td></td>
                              <td style="padding-left: 25px;">
                                <i class="fa fa-angle-double-right text-muted" style="margin-right: 5px;"></i>
                                <span class="text-muted"><?php echo htmlspecialchars($chk['title']); ?></span>
                                <span class="label label-xs label-info" style="font-size: 8px; padding: 1px 3px; font-weight: normal; margin-left: 5px;">Checklist Item</span>
                              </td>
                              <td>
                                <span class="badge <?php echo ($chk['status'] === 'done') ? 'badge-status-done' : 'badge-status-todo'; ?>" style="font-size: 10px;">
                                  <?php echo ($chk['status'] === 'done') ? 'Done' : 'Todo'; ?>
                                </span>
                              </td>
                              <td>-</td>
                              <td>-</td>
                              <td>
                                <?php if ($chk['status'] === 'done'): ?>
                                  <span class="text-success"><i class="fa fa-check-square-o"></i> Done</span>
                                <?php else: ?>
                                  <span class="text-muted"><i class="fa fa-square-o"></i> Todo</span>
                                <?php endif; ?>
                              </td>
                              <td></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="box-footer clearfix"><?php echo $pagination; ?></div>
  </div>
</section>

<!-- Add Story Modal -->
<div class="modal fade" id="addStoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add User Story</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Project <span class="text-danger">*</span></label>
              <select name="project_id" class="form-control select2" required>
                <option value="">-- Select Project --</option>
                <?php foreach ($projects_list as $p): ?>
                <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Epic</label>
            <select name="epic_id" class="form-control select2" id="add_epic_select">
                <option value="">-- No Epic --</option>
                <?php foreach ($epics_list as $e): ?>
                <option value="<?php echo $e['epic_id']; ?>" data-creator="<?php echo htmlspecialchars($e['creator_name'] ?? ''); ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
          </div>
          <div class="form-group"><label>Story Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="As a [user], I want to [action] so that [benefit]">
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Status</label>
              <select name="status" class="form-control">
                <?php foreach (TASK_STATUS_OPT as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($k=='backlog')?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Priority</label>
              <select name="priority" class="form-control">
                <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($k=='medium')?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Story</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Story Modal (same structure as Add, with ids) -->
<div class="modal fade" id="editStoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="story_id" id="edit_story_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit User Story</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Project</label>
              <select name="project_id" id="edit_story_project" class="form-control select2">
                <?php foreach ($projects_list as $p): ?>
                <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Epic</label>
              <select name="epic_id" id="edit_story_epic" class="form-control select2">
                <option value="">-- No Epic --</option>
                <?php foreach ($epics_list as $e): ?>
                <option value="<?php echo $e['epic_id']; ?>" data-creator="<?php echo htmlspecialchars($e['creator_name'] ?? ''); ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
          </div>
          <div class="form-group"><label>Story Name</label><input type="text" name="name" id="edit_story_name" class="form-control" required></div>
          <div class="form-group"><label>Description</label><textarea name="description" id="edit_story_desc" class="form-control" rows="2"></textarea></div>
          <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Status</label>
              <select name="status" id="edit_story_status" class="form-control">
                <?php foreach (TASK_STATUS_OPT as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Priority</label>
              <select name="priority" id="edit_story_priority" class="form-control">
                <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
              </select>
            </div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addSubTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="AddSubTask">
        <input type="hidden" name="story_id" id="st_story_id">
        <input type="hidden" name="project_id" id="st_project_id">
        <input type="hidden" name="epic_id" id="st_epic_id">
        <div class="modal-header" style="background:#3498db; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add Task</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Task Name <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required placeholder="Task title">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Epic Est. Time (Given by TL)</label>
                <div id="st_epic_time_display" class="form-control" style="background:#eee; pointer-events:none; font-weight:bold; color:#d35400;">--</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Allocate My Time (H / M)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" class="form-control" placeholder="Hrs" min="0" style="width:50%;">
                  <input type="number" name="estimate_minutes" class="form-control" placeholder="Min" min="0" max="59" style="width:50%;">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Task</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

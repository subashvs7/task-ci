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
          <tr><th>#</th><th>Story Name</th><th>Project</th><th>Epic</th><th>Status / Priority</th><th>TL Est. Time</th><th>Est. Time</th><th>Creator</th><th>Assignee</th><th>Tasks</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="11" class="text-center text-muted" style="padding:30px;">No user stories found.</td></tr>
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
            <td>
              <span class="badge badge-status-<?php echo $s['status']; ?>" style="font-size:10px; margin-bottom: 2px; display: inline-block;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$s['status']])?$sl[$s['status']]:$s['status']; ?></span><br>
              <span class="badge badge-priority-<?php echo $s['priority']; ?>" style="font-size:10px; display: inline-block;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$s['priority']])?$pl[$s['priority']]:$s['priority']; ?></span>
            </td>
            <td><?php echo format_hours($s['epic_estimated_time'] ? ($s['epic_estimated_time'] / 60) : 0); ?></td>
            <td><span class="badge bg-purple" style="font-size:11px;"><?php echo format_hours($s['calculated_time_hours']); ?></span></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($s['creator_name'] ?: '-'); ?></td>
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
            <td colspan="11" style="padding:15px;">
              <div style="background:#fff; border:1px solid #e1e8ed; border-radius:4px; padding:10px;">
                <strong style="display:block; margin-bottom:10px; color:#34495e;"><i class="fa fa-tasks"></i> Tasks for "<?php echo htmlspecialchars($s['name']); ?>"</strong>
                <?php if (empty($s['tasks_list'])): ?>
                  <p class="text-muted" style="margin:0; font-size:12px;">No tasks created yet.</p>
                <?php else: ?>
                  <?php 
                    $has_actions_for_me = false;
                    $cur_uid  = (int)$this->session->userdata(SESS_HEAD . '_user_id');
                    $cur_role = $this->session->userdata(SESS_HEAD . '_role');
                    $is_story_creator = ($s['created_by'] == $cur_uid || $s['project_creator'] == $cur_uid || $s['epic_creator'] == $cur_uid);

                    foreach ($s['tasks_list'] as $t_chk) {
                        if (!in_array($t_chk['status'], array('done','closed'))) {
                            if ($t_chk['assigned_to'] == $cur_uid) {
                                $has_actions_for_me = true;
                                break;
                            }
                        }
                        if (!empty($t_chk['sub_tasks'])) {
                            foreach ($t_chk['sub_tasks'] as $sub_chk) {
                                if (!in_array($sub_chk['status'], array('done','closed'))) {
                                    if ($sub_chk['assigned_to'] == $cur_uid) {
                                        $has_actions_for_me = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                  ?>
                  <table class="table table-condensed table-bordered" style="margin-bottom:0; font-size:12px;">
                    <thead style="background:#f4f6f8;">
                      <tr>
                        <th>#</th>
                        <th>Task Name</th>
                        <th>Status</th>
                        <th>Assignee</th>
                        <th>My Est. Time</th>
                        <th>Logged Time</th>
                        <th style="text-align:center;">Proof</th>
                        <th>Work Status</th>
                        <?php if ($has_actions_for_me): ?>
                          <th style="text-align:center;">Actions</th>
                        <?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($s['tasks_list'] as $tj => $task): ?>
                        <?php 
                          $is_active_session = ($task['work_session_status'] === 'active');
                          $is_my_session     = $is_active_session && ((int)$task['active_session_user'] === (int)$this->session->userdata(SESS_HEAD . '_user_id'));
                          $is_done_closed    = in_array($task['status'], array('done','closed'));
                          $can_toggle        = !$is_done_closed; 
                          $row_bg            = $is_my_session ? 'background:#f0fff4;' : '';
                          
                          $cur_uid  = (int)$this->session->userdata(SESS_HEAD . '_user_id');
                          $cur_role = $this->session->userdata(SESS_HEAD . '_role');
                          $is_mine  = ((int)$task['assigned_to'] === $cur_uid);
                          $can_start = $can_toggle && $is_mine;

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
                          <td><?php echo htmlspecialchars($task['assignee_name'] ?: 'Unassigned'); ?></td>
                          <td><?php echo format_hours($est_h); ?></td>
                          <td><?php echo $log_display; ?></td>
                          <td style="text-align:center;">
                            <?php 
                               $proof_count = (int)$task['proof_count'];
                               $badge_html = '';
                               if ($proof_count > 0) {
                                   $badge_html = '<span style="position:absolute; top:-6px; right:-6px; background:#e74c3c; color:white; border-radius:50%; width:16px; height:16px; font-size:10px; line-height:16px; text-align:center; box-shadow:0 1px 2px rgba(0,0,0,0.2);">' . $proof_count . '</span>';
                               }
                            ?>
                            <?php if ($is_mine): ?>
                               <button class="btn btn-xs btn-primary btn-proof-modal" data-id="<?php echo $task['task_id']; ?>" data-mode="upload" title="Upload Proof Screenshots" style="position:relative;"><i class="fa fa-upload"></i> Proof<?php echo $badge_html; ?></button>
                            <?php else: ?>
                               <button class="btn btn-xs btn-default btn-proof-modal" data-id="<?php echo $task['task_id']; ?>" data-mode="view" title="View Proof Screenshots" style="position:relative;"><i class="fa fa-image"></i> Proof<?php echo $badge_html; ?></button>
                            <?php endif; ?>
                          </td>
                          <td style="min-width:110px;">
                            <?php if ($is_done_closed): ?>
                              <span class="label label-success"><i class="fa fa-check-circle"></i> Completed</span>
                            <?php elseif ($is_my_session): ?>
                              <button class="btn btn-xs btn-danger btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="stop" style="font-weight:600;"><i class="fa fa-stop-circle"></i> Stop Work</button>
                              <br><span class="session-timer text-success" data-start-ts="<?php echo strtotime($task['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($task['open_session_start']); ?>" style="font-size:11px; font-weight:700; font-family:monospace;">00:00:00</span>
                            <?php elseif ($is_active_session): ?>
                              <span style="color:#e67e22;"><i class="fa fa-circle"></i> Working (<?php echo htmlspecialchars($task['active_worker_name']); ?>)</span>
                            <?php elseif ($can_start): ?>
                              <button class="btn btn-xs btn-success btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="start" style="font-weight:600;"><i class="fa fa-play-circle"></i> Start Work</button>
                            <?php else: ?>
                              <span class="text-muted"><i class="fa fa-circle-o"></i> Not Started</span>
                            <?php endif; ?>
                          </td>
                          <?php if ($has_actions_for_me): ?>
                          <td style="min-width:90px; text-align:center;">
                            <?php
                              $can_action_task = false;
                              if ($task['assigned_to'] == $cur_uid) {
                                  $can_action_task = true;
                              }
                            ?>
                            <?php if (!$is_done_closed && $can_action_task): ?>
                                  <button class="btn btn-xs btn-primary btn-task-complete" data-task="<?php echo $task['task_id']; ?>" style="font-weight:600;"><i class="fa fa-check"></i> Complete</button>
                                  <button class="btn btn-xs btn-warning btn-edit-task"
                                    data-id="<?php echo $task['task_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($task['title'], ENT_QUOTES); ?>"
                                    data-description="<?php echo htmlspecialchars($task['description'] ?? '', ENT_QUOTES); ?>"
                                    data-status="<?php echo $task['status']; ?>"
                                    data-assigned="<?php echo $task['assigned_to']; ?>"
                                    data-project="<?php echo $task['project_id']; ?>"
                                    data-epic="<?php echo $task['epic_id']; ?>"
                                    data-story="<?php echo $task['story_id']; ?>"
                                    data-type="<?php echo $task['type']; ?>"
                                    data-priority="<?php echo $task['priority']; ?>"
                                    data-due="<?php echo $task['due_date']; ?>"
                                    data-eh="<?php echo floor((float)$task['estimated_hours']); ?>"
                                    data-em="<?php echo round(((float)$task['estimated_hours'] - floor((float)$task['estimated_hours'])) * 60); ?>"
                                    style="font-weight:600;" title="Edit"><i class="fa fa-pencil"></i></button>
                            <?php endif; ?>
                          </td>
                          <?php endif; ?>
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
                              
                              $sub_is_mine = ((int)$sub['assigned_to'] === $cur_uid);
                              $sub_can_start = $sub_can_toggle && $sub_is_mine;

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
                              <td><?php echo htmlspecialchars($sub['assignee_name'] ?: 'Unassigned'); ?></td>
                              <td><?php echo format_hours($sub_est_h); ?></td>
                              <td><?php echo $sub_log_display; ?></td>
                              <td style="text-align:center;">
                                <?php 
                                   $sub_proof_count = (int)$sub['proof_count'];
                                   $sub_badge_html = '';
                                   if ($sub_proof_count > 0) {
                                       $sub_badge_html = '<span style="position:absolute; top:-6px; right:-6px; background:#e74c3c; color:white; border-radius:50%; width:16px; height:16px; font-size:10px; line-height:16px; text-align:center; box-shadow:0 1px 2px rgba(0,0,0,0.2);">' . $sub_proof_count . '</span>';
                                   }
                                ?>
                                <?php if ($sub_is_mine): ?>
                                   <button class="btn btn-xs btn-primary btn-proof-modal" data-id="<?php echo $sub['task_id']; ?>" data-mode="upload" title="Upload Proof Screenshots" style="position:relative;"><i class="fa fa-upload"></i> Proof<?php echo $sub_badge_html; ?></button>
                                <?php else: ?>
                                   <button class="btn btn-xs btn-default btn-proof-modal" data-id="<?php echo $sub['task_id']; ?>" data-mode="view" title="View Proof Screenshots" style="position:relative;"><i class="fa fa-image"></i> Proof<?php echo $sub_badge_html; ?></button>
                                <?php endif; ?>
                              </td>
                              <td>
                                <?php if ($sub_is_done_closed): ?>
                                  <span class="label label-success"><i class="fa fa-check-circle"></i> Completed</span>
                                <?php elseif ($sub_is_my_session): ?>
                                  <button class="btn btn-xs btn-danger btn-task-session" data-task="<?php echo $sub['task_id']; ?>" data-action="stop" style="font-weight:600;"><i class="fa fa-stop-circle"></i> Stop Work</button>
                                  <br><span class="session-timer text-success" data-start-ts="<?php echo strtotime($sub['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($sub['open_session_start']); ?>" style="font-size:11px; font-weight:700; font-family:monospace;">00:00:00</span>
                                <?php elseif ($sub_is_active_session): ?>
                                  <span style="color:#e67e22;"><i class="fa fa-circle"></i> Working (<?php echo htmlspecialchars($sub['active_worker_name']); ?>)</span>
                                <?php elseif ($sub_can_start): ?>
                                  <button class="btn btn-xs btn-success btn-task-session" data-task="<?php echo $sub['task_id']; ?>" data-action="start" style="font-weight:600;"><i class="fa fa-play-circle"></i> Start Work</button>
                                <?php else: ?>
                                  <span class="text-muted"><i class="fa fa-circle-o"></i> Not Started</span>
                                <?php endif; ?>
                              </td>
                              <?php if ($has_actions_for_me): ?>
                              <td style="text-align:center; min-width:90px;">
                                <?php
                                  $can_action_sub = false;
                                  if ($sub['assigned_to'] == $cur_uid) {
                                      $can_action_sub = true;
                                  }
                                ?>
                                <?php if (!$sub_is_done_closed && $can_action_sub): ?>
                                      <button class="btn btn-xs btn-primary btn-task-complete" data-task="<?php echo $sub['task_id']; ?>" style="font-weight:600;"><i class="fa fa-check"></i> Complete</button>
                                      <button class="btn btn-xs btn-warning btn-edit-task"
                                        data-id="<?php echo $sub['task_id']; ?>"
                                        data-title="<?php echo htmlspecialchars($sub['title'], ENT_QUOTES); ?>"
                                        data-description="<?php echo htmlspecialchars($sub['description'] ?? '', ENT_QUOTES); ?>"
                                        data-status="<?php echo $sub['status']; ?>"
                                        data-assigned="<?php echo $sub['assigned_to']; ?>"
                                        data-project="<?php echo $sub['project_id']; ?>"
                                        data-epic="<?php echo $sub['epic_id']; ?>"
                                        data-story="<?php echo $sub['story_id']; ?>"
                                        data-type="<?php echo $sub['type']; ?>"
                                        data-priority="<?php echo $sub['priority']; ?>"
                                        data-due="<?php echo $sub['due_date']; ?>"
                                        data-eh="<?php echo floor((float)$sub['estimated_hours']); ?>"
                                        data-em="<?php echo round(((float)$sub['estimated_hours'] - floor((float)$sub['estimated_hours'])) * 60); ?>"
                                        style="font-weight:600;" title="Edit"><i class="fa fa-pencil"></i></button>
                                <?php endif; ?>
                              </td>
                              <?php endif; ?>
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
<div class="modal fade" id="addStoryModal">
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
<div class="modal fade" id="editStoryModal">
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

<!-- Edit Sub Task Modal -->
<div class="modal fade" id="editSubTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url('task-list') ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="redirect_url" value="<?php echo current_url(); ?>">
        <input type="hidden" name="task_id" id="et_task_id">
        <input type="hidden" name="project_id" id="et_project_id">
        <input type="hidden" name="epic_id" id="et_epic_id">
        <input type="hidden" name="story_id" id="et_story_id">
        <input type="hidden" name="type" id="et_type">
        <input type="hidden" name="priority" id="et_priority">
        <input type="hidden" name="due_date" id="et_due_date">

        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Task</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Task Name <span class="text-danger">*</span></label>
            <input type="text" name="title" id="et_title" class="form-control" required placeholder="Task title">
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="et_description" class="form-control" rows="2"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Status</label>
                <select name="status" id="et_status" class="form-control">
                  <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Assignee</label>
                <select name="assigned_to" id="et_assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Estimate Time (Hrs / Mins)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" id="et_estimate_hours" class="form-control" placeholder="Hrs" min="0" style="width:50%;">
                  <input type="number" name="estimate_minutes" id="et_estimate_minutes" class="form-control" placeholder="Min" min="0" max="59" style="width:50%;">
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

<!-- Proof / Screenshots Modal -->
<div class="modal fade" id="proofModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#2980b9; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title"><i class="fa fa-camera"></i> Task Proofs / Screenshots</h4>
      </div>
      <div class="modal-body">
        
        <div id="proof-upload-section" style="display:none; margin-bottom: 20px;">
            <div id="proof-dropzone" style="border: 2px dashed #bdc3c7; border-radius: 8px; padding: 30px; text-align: center; background: #ecf0f1; cursor: pointer; transition: background 0.3s;">
                <button type="button" class="btn btn-primary" style="border-radius:50%; width:60px; height:60px; margin-bottom:15px; font-size:24px;" onclick="$('#proof-file-input').click(); event.stopPropagation();">
                    <i class="fa fa-plus"></i>
                </button>
                <h4 style="margin: 0; color: #7f8c8d;">Browse Files or Paste (Ctrl+V) an image here.</h4>
                <p class="text-muted" style="margin-top: 5px; font-size: 12px;">Maximum 5 images. Only images allowed.</p>
                <input type="file" id="proof-file-input" accept="image/*" style="display:none;" multiple>
            </div>
            <div id="proof-upload-progress" style="display:none; margin-top:10px;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" style="width: 100%">Uploading...</div>
                </div>
            </div>
        </div>

        <div id="proof-gallery-container">
            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; color:#2c3e50; font-weight:bold;">Uploaded Proofs</h5>
            <div id="proof-gallery" class="row" style="display:flex; flex-wrap:wrap;">
                <!-- Images will be injected here via AJAX -->
                <div class="col-md-12 text-center text-muted" id="proof-empty-state">Loading...</div>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

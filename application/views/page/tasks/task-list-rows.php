<?php 
if (!function_exists('format_hours')) {
    function format_hours($decimal_hours) {
        if (!$decimal_hours || $decimal_hours <= 0) return '-';
        $h = floor($decimal_hours);
        $rem_m = ($decimal_hours - $h) * 60;
        $m = floor($rem_m);
        $s = round(($rem_m - $m) * 60);
        
        if ($s == 60) { $s = 0; $m++; }
        if ($m == 60) { $m = 0; $h++; }

        $parts = [];
        if ($h > 0) $parts[] = $h.'h';
        if ($m > 0) $parts[] = $m.'m';
        if ($s > 0) $parts[] = $s.'s';
        return empty($parts) ? '0m' : implode(' ', $parts);
    }
}
?>
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
  $is_mine_task      = ((int)$t['assigned_to'] === $cur_uid);
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
    <?php if (!empty($t['document']) && $t['document'] !== 'null' && $t['document'] !== '[]'): ?>
      <button type="button" class="btn btn-xs btn-default btn-preview-doc" data-document="<?php echo htmlspecialchars($t['document'], ENT_QUOTES); ?>" data-id="<?php echo $t['task_id']; ?>" title="View Document">
        <i class="fa fa-file-pdf-o text-danger"></i> Docs
      </button>
    <?php else: ?>
      <button type="button" class="btn btn-xs btn-default btn-preview-doc" data-document="[]" data-id="<?php echo $t['task_id']; ?>" style="border: 1px dashed #7f8c8d; color: #7f8c8d; background: transparent;" title="Add Document">
        <i class="fa fa-plus"></i> Add
      </button>
    <?php endif; ?>
  </td>
  <!-- Epic -->
  <td>
    <?php if (!empty($t['epic_name'])): ?>
      <span class="label" style="background-color:#9b59b6; color:#fff; font-size:10px; padding:3px 6px;"><i class="fa fa-bolt"></i> <?php echo htmlspecialchars(mb_substr($t['epic_name'], 0, 30)); ?></span>
    <?php else: ?>
      <span class="text-muted">-</span>
    <?php endif; ?>
  </td>
  <!-- User Story -->
  <td style="font-size:11px; min-width:110px;">
    <?php if (!empty($t['story_name'])): ?>
      <span title="<?php echo htmlspecialchars($t['story_name'], ENT_QUOTES); ?>"
            style="display:inline-block; max-width:130px; padding:3px 7px; border-radius:12px;
                   background: linear-gradient(135deg,#0f9b8e,#15c39a); color:#fff;
                   font-size:10px; font-weight:600; white-space:nowrap; overflow:hidden;
                   text-overflow:ellipsis; vertical-align:middle; cursor:default;">
        <i class="fa fa-book" style="margin-right:3px;"></i><?php echo htmlspecialchars(mb_substr($t['story_name'], 0, 28)); ?><?php echo mb_strlen($t['story_name']) > 28 ? '…' : ''; ?>
      </span>
    <?php else: ?>
      <span class="text-muted" style="font-size:11px;">—</span>
    <?php endif; ?>
  </td>
  <!-- Type -->
  <td><span class="badge badge-type-<?php echo $t['type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['type']])?$tl[$t['type']]:$t['type']; ?></span></td>
  <!-- Status / Priority combined -->
  <td style="white-space:nowrap;">
    <span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px; display:inline-block; margin-bottom:3px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$t['status']])?$sl[$t['status']]:$t['status']; ?></span><br>
    <span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px; display:inline-block;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span>
  </td>
  <?php 
    $is_self = !empty($t['assigned_to']) && !empty($t['reporter_id']) && (int)$t['assigned_to'] === (int)$t['reporter_id'];
    $reporter_role_label = !empty($t['reporter_role']) ? ucwords(str_replace('_',' ',$t['reporter_role'])) : '';
  ?>
  <!-- Single Assignee cell -->
  <td style="font-size:12px; min-width:120px;">
    <?php if (!empty($t['assignee_name'])): ?>
      <span style="font-weight:600; display:block;"><?php echo htmlspecialchars($t['assignee_name']); ?></span>
    <?php else: ?>
      <span class="text-muted">—</span>
    <?php endif; ?>

    <?php if ($is_self): ?>
      <!-- Self Created badge -->
      <span style="display:inline-block; margin-top:3px; padding:2px 9px; border-radius:10px;
                   background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff;
                   font-size:9px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;">
        <i class="fa fa-user" style="margin-right:2px;"></i>Self Created
      </span>
    <?php elseif (!empty($t['reporter_name'])): ?>
      <!-- Assigned by another person - dark buckle badge -->
      <span style="display:inline-block; margin-top:3px; padding:2px 8px; border-radius:10px;
                   background:linear-gradient(135deg,#1a2035,#2c3e6b); color:#fff;
                   font-size:9px; font-weight:600; letter-spacing:0.3px; max-width:130px;
                   overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle;"
            title="Assigned by: <?php echo htmlspecialchars($t['reporter_name']); ?><?php echo $reporter_role_label ? ' ('.$reporter_role_label.')' : ''; ?>">
        <i class="fa fa-tag" style="margin-right:2px;"></i><?php echo htmlspecialchars(mb_substr($t['reporter_name'],0,14)); ?><?php echo mb_strlen($t['reporter_name'])>14?'…':''; ?>
        <?php if ($reporter_role_label): ?><span style="opacity:0.75; font-size:8px;"> · <?php echo $reporter_role_label; ?></span><?php endif; ?>
      </span>
    <?php endif; ?>
  </td>

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
    <?php if (!empty($t['start_time'])): ?>
      <div style="font-size: 10px; color: #555; margin-bottom: 2px;" title="Scheduled Start Time">
        <i class="fa fa-clock-o text-success"></i> <strong>Start:</strong> <?php echo date('d-M-Y h:i A', strtotime($t['start_time'])); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($t['end_time'])): ?>
      <div style="font-size: 10px; color: #555; margin-bottom: 4px;" title="Scheduled End Time">
        <i class="fa fa-clock-o text-danger"></i> <strong>End:</strong> <?php echo date('d-M-Y h:i A', strtotime($t['end_time'])); ?>
      </div>
    <?php endif; ?>
    
    <?php if (empty($t['start_time']) && empty($t['end_time'])): ?>
      <?php echo $t['due_date'] ? date('d-M-Y', strtotime($t['due_date'])) : '-'; ?>
    <?php elseif ($t['due_date']): ?>
      <div style="font-size: 10px; color: #777;"><strong>Due:</strong> <?php echo date('d-M-Y', strtotime($t['due_date'])); ?></div>
    <?php endif; ?>

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
  
<?php
$created_date_formatted = '-';
if (!empty($t['created_date'])) {
    $dt = new DateTime($t['created_date']);
    $dt->setTimezone(new DateTimeZone('Asia/Kolkata'));
    $created_date_formatted = $dt->format('d-M-Y h:i A');
}
$days_taken_str = '-';
if (!empty($t['created_date'])) {
    $start = strtotime($t['created_date']);
    $end = in_array($t['status'], ['done','closed']) && !empty($t['completed_at']) ? strtotime($t['completed_at']) : time();
    $diff = max(0, $end - $start);
    $days = floor($diff / 86400);
    $hrs = floor(($diff % 86400) / 3600);
    $mins = floor(($diff % 3600) / 60);
    if ($days > 0) {
        $days_taken_str = $days . 'd ' . $hrs . 'h';
    } elseif ($hrs > 0) {
        $days_taken_str = $hrs . 'h ' . $mins . 'm';
    } else {
        $days_taken_str = $mins . 'm';
    }
}
$is_completed = in_array($t['status'], ['done','closed']);
?>
  <td style="font-size:12px; font-weight:600; <?php echo $is_effort_overdue ? 'color:#e74c3c; background:#fdf0ed;' : 'color:#27ae60; background:#f0fff4;'; ?>">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <span>
            <?php echo format_hours($logged_h); ?>
            <?php if ($is_effort_overdue): ?>
              <i class="fa fa-warning text-danger" title="Overtime" style="margin-left:3px; font-size: 13px;"></i>
            <?php endif; ?>
        </span>
        <button type="button" class="btn btn-xs btn-default btn-logged-time-details" style="padding: 1px 4px; font-size: 11px; background: transparent; border-color: #bdc3c7;" title="View Time Details" data-title="<?php echo htmlspecialchars($t['title'], ENT_QUOTES); ?>" data-created="<?php echo $created_date_formatted; ?>" data-logged="<?php echo format_hours($logged_h); ?>" data-estimated="<?php echo format_hours($estimated_h); ?>" data-overdue="<?php echo $is_effort_overdue ? '1' : '0'; ?>" data-overtime="<?php echo $is_effort_overdue ? format_hours($logged_h - $estimated_h) : '0'; ?>" data-duration-str="<?php echo $days_taken_str; ?>" data-is-completed="<?php echo $is_completed ? '1' : '0'; ?>">
            <i class="fa fa-eye"></i>
        </button>
    </div>
  </td>

  <!-- Actions -->
  <?php if ($show_actions): ?>
  <td style="white-space:nowrap;">
    <?php if (in_array($cur_role, ['admin', 'manager', 'team_leader']) || $t['created_by'] == $cur_uid): ?>
    <button class="btn btn-xs btn-warning btn-edit-task"
      data-id="<?php echo $t['task_id']; ?>"
      data-project="<?php echo $t['project_id']; ?>"
      data-epic="<?php echo $t['epic_id']; ?>"
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
      data-start_time="<?php echo $t['start_time'] ? date('Y-m-d\TH:i', strtotime($t['start_time'])) : ''; ?>"
      data-end_time="<?php echo $t['end_time'] ? date('Y-m-d\TH:i', strtotime($t['end_time'])) : ''; ?>"
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

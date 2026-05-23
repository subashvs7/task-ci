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
  <h1><?php echo htmlspecialchars(mb_substr($task['title'], 0, 60)); ?><?php echo strlen($task['title'])>60?'...':''; ?></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('task-list') ?>">Tasks</a></li>
    <li class="active">Detail</li>
  </ol>
</section>

<section class="content">
  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>

  <div class="row">
    <!-- LEFT COLUMN: Task Info + Sub-tasks + Comments -->
    <div class="col-md-8">

      <!-- Task Info Card -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">
            <span class="badge badge-type-<?php echo $task['type']; ?>"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$task['type']])?$tl[$task['type']]:$task['type']; ?></span>
            &nbsp;<?php echo htmlspecialchars($task['title']); ?>
          </h3>
          <div class="box-tools pull-right">
            <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editTaskModal"><i class="fa fa-pencil"></i> Edit</button>
            <a href="<?php echo site_url('task-list') ?>" class="btn btn-xs btn-default"><i class="fa fa-arrow-left"></i> Back</a>
          </div>
        </div>
        <div class="box-body">
          <!-- Status row -->
          <div class="row" style="margin-bottom:15px;">
            <div class="col-md-2 text-center">
              <div style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:4px;">Status</div>
              <span class="badge badge-status-<?php echo $task['status']; ?>" style="font-size:13px; padding:6px 12px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$task['status']])?$sl[$task['status']]:$task['status']; ?></span>
            </div>
            <div class="col-md-2 text-center">
              <div style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:4px;">Priority</div>
              <span class="badge badge-priority-<?php echo $task['priority']; ?>" style="font-size:13px; padding:6px 12px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$task['priority']])?$pl[$task['priority']]:$task['priority']; ?></span>
            </div>
            <div class="col-md-2 text-center">
              <div style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:4px;">Project</div>
              <?php if ($task['project_id']): ?>
                <a href="#" class="project-link-modal" data-id="<?php echo $task['project_id']; ?>" style="font-weight:600; font-size:12px; display:block; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($task['project_name']); ?>"><?php echo htmlspecialchars($task['project_name']); ?></a>
                <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $task['project_id']; ?>" style="padding: 1px 3px; border-radius: 3px; font-size: 9px; margin-top:2px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
              <?php else: ?>
                -
              <?php endif; ?>
            </div>
            <div class="col-md-3 text-center">
              <div style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:4px;">Progress</div>
              <div class="progress-bar-container"><div class="progress-bar-fill" style="width:<?php echo $task['completion_percentage']; ?>%; background:<?php echo $task['completion_percentage']==100?'#27ae60':($task['completion_percentage']>=50?'#e67e22':'#3498db'); ?>;"></div></div>
              <small><?php echo $task['completion_percentage']; ?>%</small>
            </div>
            <div class="col-md-3 text-center">
              <div style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:4px;">Work Session</div>
              <?php 
                $cur_uid  = (int)$this->session->userdata(SESS_HEAD . '_user_id');
                $cur_role = $this->session->userdata(SESS_HEAD . '_role');
                $is_active_session = ($task['work_session_status'] === 'active');
                $is_my_session     = $is_active_session && ((int)$task['active_session_user'] === $cur_uid);
                $is_done_closed    = in_array($task['status'], array('done','closed'));
                $can_toggle        = in_array($cur_role, array('staff','admin')) && !$is_done_closed;
                $is_mine_task      = ((int)$task['assigned_to'] === $cur_uid);
              ?>
              <?php if ($is_done_closed): ?>
                <span class="label label-success"><i class="fa fa-check-circle"></i> Completed</span>
              <?php elseif ($is_my_session): ?>
                <button class="btn btn-sm btn-danger btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="stop" style="font-weight:600;"><i class="fa fa-stop-circle"></i> Stop Work</button><br>
                <span class="session-timer text-success" data-start-ts="<?php echo strtotime($task['open_session_start']); ?>" data-start="<?php echo htmlspecialchars($task['open_session_start']); ?>" style="font-size:12px; font-weight:700; font-family:monospace; display:inline-block; margin-top:4px;">00:00:00</span>
              <?php elseif ($is_active_session): ?>
                <span style="color:#e67e22; font-weight:600;"><i class="fa fa-circle"></i> Working</span>
              <?php elseif ($can_toggle && $is_mine_task): ?>
                <button class="btn btn-sm btn-success btn-task-session" data-task="<?php echo $task['task_id']; ?>" data-action="start" style="font-weight:600;"><i class="fa fa-play-circle"></i> Start Work</button>
              <?php else: ?>
                <span class="text-muted" style="font-size:12px;"><i class="fa fa-clock-o"></i> Inactive</span>
              <?php endif; ?>
            </div>
          </div>
          <hr style="margin-top: 5px; margin-bottom: 15px;">
          
          <!-- TIME TRACKING & EFFORT -->
          <?php 
            $logged_h    = (float)$task['total_logged_hours'];
            $estimated_h = (float)$task['estimated_hours'];
            $remaining_h = max(0, $estimated_h - $logged_h);
            $is_overdue  = ($estimated_h > 0 && $logged_h > $estimated_h);
            
            $time_progress = 0;
            if ($estimated_h > 0) {
                $time_progress = min(100, round(($logged_h / $estimated_h) * 100));
            } elseif ($is_done_closed) {
                $time_progress = 100;
            }
          ?>
          <div class="row" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 0 0 15px 0;">
              <div class="col-md-4 text-center" style="border-right: 1px solid #ddd;">
                  <h5 style="margin-top: 0; font-size: 11px; color: #7f8c8d; text-transform: uppercase;">Estimated Time (Budget)</h5>
                  <div style="font-size: 18px; font-weight: 700; color: #34495e;">
                      <?php echo $estimated_h > 0 ? format_hours($estimated_h) : '--'; ?>
                  </div>
              </div>
              <div class="col-md-4 text-center" style="border-right: 1px solid #ddd;">
                  <h5 style="margin-top: 0; font-size: 11px; color: #7f8c8d; text-transform: uppercase;">Logged Time</h5>
                  <div style="font-size: 18px; font-weight: 700; color: #2980b9;">
                      <?php echo format_hours($logged_h); ?>
                  </div>
              </div>
              <div class="col-md-4 text-center">
                  <h5 style="margin-top: 0; font-size: 11px; color: #7f8c8d; text-transform: uppercase;">Time Remaining</h5>
                  <?php if ($estimated_h > 0): ?>
                      <div style="font-size: 18px; font-weight: 700; color: <?php echo $is_overdue ? '#e74c3c' : '#27ae60'; ?>;">
                          <?php if ($is_overdue): ?>
                              <i class="fa fa-exclamation-triangle"></i> <?php echo format_hours($logged_h - $estimated_h); ?> <small>Over</small>
                          <?php else: ?>
                              <?php echo format_hours($remaining_h); ?>
                          <?php endif; ?>
                      </div>
                  <?php else: ?>
                      <div style="font-size: 18px; font-weight: 700; color: #bdc3c7;">--</div>
                  <?php endif; ?>
              </div>
              
              <div class="col-md-12" style="margin-top: 15px;">
                  <div style="font-size:10px; color:#888; text-transform:uppercase; margin-bottom:4px; text-align: center;">Time Progression vs Estimate</div>
                  <div class="progress-bar-container" style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                      <div class="progress-bar-fill" style="height: 100%; width:<?php echo $time_progress; ?>%; background:<?php echo $time_progress==100 && !$is_overdue?'#27ae60':($is_overdue?'#e74c3c':'#3498db'); ?>; transition: width 0.5s ease;"></div>
                  </div>
              </div>
          </div>
          
          <hr style="margin-top: 15px; margin-bottom: 15px;">
          <!-- Description -->
          <?php if ($task['description']): ?>
          <div class="task-detail-section">
            <h4><i class="fa fa-align-left"></i> Description</h4>
            <p style="white-space:pre-wrap; font-size:13px;"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
          </div>
          <?php endif; ?>

          <!-- Acceptance Criteria -->
          <?php if ($task['acceptance_criteria'] ?? ''): ?>
          <div class="task-detail-section">
            <h4><i class="fa fa-check-square"></i> Acceptance Criteria</h4>
            <p style="white-space:pre-wrap; font-size:13px;"><?php echo nl2br(htmlspecialchars($task['acceptance_criteria'] ?? '')); ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>      <?php if (empty($task['parent_task_id'])): ?>
      <div class="box box-primary box-solid" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: none; margin-bottom: 20px;">
          <div class="box-header with-border" style="background: #2c3e50;">
              <h3 class="box-title" style="font-weight: 600;"><i class="fa fa-sitemap"></i> Task Delegation Waterfall</h3>
          </div>
          <div class="box-body" style="background: #f8f9fa; padding: 25px;">
              <div class="hierarchy-container">
                  <!-- Parent Core Task Card -->
                  <div class="hierarchy-parent-card">
                      <span class="label label-primary" style="text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px;">Parent Core Task</span>
                      <h4 style="margin: 8px 0 5px; color: #2c3e50; font-weight: 700;"><?= htmlspecialchars($task['title']) ?></h4>
                      <p style="margin: 0; font-size: 13px; color: #7f8c8d;">Owner (Lead): <strong><?= htmlspecialchars($task['assignee_name'] ?: 'Unassigned') ?></strong></p>
                  </div>
                  
                  <div class="hierarchy-connector"><i class="fa fa-chevron-down text-muted"></i></div>
                  
                  <!-- Children Grid -->
                  <div class="hierarchy-children-grid">
                      <?php 
                      $children = $this->db->query("
                          SELECT t.*, u.name as assignee_name,
                                 COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl WHERE tl.task_id=t.task_id AND tl.status_flag='Active'), 0) as logged_hours,
                                 (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start
                          FROM tm_tasks t 
                          LEFT JOIN tm_users u ON u.user_id = t.assigned_to 
                          WHERE t.parent_task_id = ? AND t.status_flag='Active'
                      ", array($task['task_id']))->result_array();
                      
                      if (!empty($children)): 
                          foreach ($children as $c): 
                              $status_class = ($c['status'] == 'done') ? 'success' : (($c['status'] == 'in_progress') ? 'warning' : 'default');
                              $border_color = ($c['status'] == 'done') ? '#2ecc71' : (($c['status'] == 'in_progress') ? '#f1c40f' : '#95a5a6');
                              $is_active = ($c['work_session_status'] === 'active');
                      ?>
                          <div class="hierarchy-child-card" onclick="window.location.href='<?= site_url('task-detail/' . $c['task_id']) ?>';" style="border-left: 4px solid <?= $border_color ?>; cursor: pointer; display: flex; flex-direction: column;">
                              <h5 style="font-weight: 700; margin: 0 0 6px; color: #34495e; transition: color 0.2s;"><?= htmlspecialchars($c['title']) ?></h5>
                              <p style="margin: 0 0 10px; font-size: 12px; color: #7f8c8d;">Staff: <strong><?= htmlspecialchars($c['assignee_name'] ?: 'Unassigned') ?></strong></p>
                              <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                  <span class="label label-<?= $status_class ?>" style="text-transform: uppercase; font-size: 9px;"><?= strtoupper($c['status']) ?></span>
                                  <div style="font-size: 11px; font-weight: 600; color: #555;">
                                      <?php if ($is_active): ?>
                                          <i class="fa fa-circle" style="color: #e67e22; animation: blinker 1.5s linear infinite;"></i>
                                          <span class="session-timer text-warning" data-start-ts="<?= strtotime($c['open_session_start']) ?>" data-start="<?= htmlspecialchars($c['open_session_start']) ?>">00:00:00</span>
                                      <?php else: ?>
                                          <i class="fa fa-clock-o"></i> <?= round((float)$c['logged_hours'], 2) ?>h
                                      <?php endif; ?>
                                  </div>
                              </div>
                          </div>
                      <?php 
                          endforeach; 
                      else: 
                      ?>
                          <div class="col-md-12 text-center" style="grid-column: 1 / -1; padding: 20px;">
                              <p class="text-muted" style="margin:0;"><i class="fa fa-info-circle"></i> No delegated tasks assigned to staff yet.</p>
                          </div>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      </div>
      <?php endif; ?>

      <!-- Sub-tasks -->
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-sitemap"></i> Sub-tasks <span class="badge bg-green"><?php echo count($sub_tasks); ?></span></h3>
          <div class="box-tools pull-right">
            <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#addSubTaskModal"><i class="fa fa-plus"></i> Add</button>
          </div>
        </div>
        <div class="box-body no-padding">
          <?php if (empty($sub_tasks)): ?>
            <p class="text-center text-muted" style="padding:15px;">No sub-tasks yet.</p>
          <?php else: ?>
          <table class="table table-condensed table-hover">
            <tbody>
              <?php foreach ($sub_tasks as $st): ?>
              <tr>
                <td style="width:30px;">
                  <input type="checkbox" class="subtask-check" data-id="<?php echo $st['sub_task_id']; ?>" <?php echo ($st['status']=='done')?'checked':''; ?>>
                </td>
                <td style="<?php echo ($st['status']=='done')?'text-decoration:line-through; color:#aaa;':''; ?>">
                  <?php echo htmlspecialchars($st['title']); ?>
                </td>
                <td style="width:120px;"><span class="badge badge-status-<?php echo $st['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$st['status']])?$sl[$st['status']]:$st['status']; ?></span></td>
                <td style="width:100px; font-size:11px;"><?php echo htmlspecialchars($st['assignee_name'] ?: '-'); ?></td>
                <td style="width:60px;">
                  <button class="btn btn-xs btn-danger del_subtask" data-id="<?php echo $st['sub_task_id']; ?>"><i class="fa fa-trash"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- Dependencies -->
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-link"></i> Task Dependencies</h3>
          <div class="box-tools pull-right">
            <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#addDependencyModal"><i class="fa fa-plus"></i> Add Dependency</button>
          </div>
        </div>
        <div class="box-body" style="padding: 15px;">
          
          <!-- Blocked By Section -->
          <div style="margin-bottom: 20px;">
            <h5 style="font-weight: bold; color: #d35400; margin-top: 0;"><i class="fa fa-ban"></i> Blocked By (Must be completed first)</h5>
            <?php if (empty($blocked_by)): ?>
              <p class="text-muted" style="font-size: 13px;">No blocking tasks.</p>
            <?php else: ?>
              <ul class="list-group" style="margin-bottom:0;">
                <?php foreach ($blocked_by as $dep): ?>
                <li class="list-group-item" style="padding: 8px 15px; display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <span class="badge badge-status-<?php echo $dep['status']; ?>" style="font-size:10px; margin-right: 8px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$dep['status']])?$sl[$dep['status']]:$dep['status']; ?></span>
                    <a href="<?php echo site_url('task-detail/'.$dep['task_id']); ?>"><?php echo htmlspecialchars($dep['title']); ?></a>
                  </div>
                  <button class="btn btn-xs btn-danger del_dependency" data-id="<?php echo $dep['dependency_id']; ?>"><i class="fa fa-times"></i></button>
                </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <!-- Blocking Section -->
          <div>
            <h5 style="font-weight: bold; color: #2980b9;"><i class="fa fa-arrow-right"></i> Blocking (Waiting for this task)</h5>
            <?php if (empty($blocking)): ?>
              <p class="text-muted" style="font-size: 13px;">This task is not blocking any other tasks.</p>
            <?php else: ?>
              <ul class="list-group" style="margin-bottom:0;">
                <?php foreach ($blocking as $dep): ?>
                <li class="list-group-item" style="padding: 8px 15px; display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <span class="badge badge-status-<?php echo $dep['status']; ?>" style="font-size:10px; margin-right: 8px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$dep['status']])?$sl[$dep['status']]:$dep['status']; ?></span>
                    <a href="<?php echo site_url('task-detail/'.$dep['task_id']); ?>"><?php echo htmlspecialchars($dep['title']); ?></a>
                  </div>
                  <button class="btn btn-xs btn-danger del_dependency" data-id="<?php echo $dep['dependency_id']; ?>"><i class="fa fa-times"></i></button>
                </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- Comments -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-comments"></i> Comments <span class="badge bg-blue"><?php echo count($comments); ?></span></h3>
        </div>
        <div class="box-body">
          <?php foreach ($comments as $c): ?>
          <div class="comment-item">
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
              <strong style="font-size:13px;"><i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($c['user_name'] ?: 'Unknown'); ?></strong>
              <small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($c['created_date'])); ?>
                <?php if ($c['user_id'] == $this->session->userdata(SESS_HEAD.'_user_id')): ?>
                <button class="btn btn-xs btn-danger del_comment" data-id="<?php echo $c['comment_id']; ?>" style="margin-left:8px;"><i class="fa fa-trash"></i></button>
                <?php endif; ?>
              </small>
            </div>
            <p style="font-size:13px; margin:0;"><?php echo nl2br(htmlspecialchars($c['body'])); ?></p>
          </div>
          <?php endforeach; ?>
          <?php if (empty($comments)): ?>
            <p class="text-muted text-center">No comments yet. Be the first to comment!</p>
          <?php endif; ?>

          <!-- Add comment form -->
          <form id="addCommentForm" style="margin-top:15px;">
            <div class="form-group">
              <textarea id="comment_body" class="form-control" rows="3" placeholder="Write a comment..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane"></i> Post Comment</button>
          </form>
        </div>
      </div>

      <!-- Attachments -->
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-paperclip"></i> Attachments <span class="badge"><?php echo count($attachments); ?></span></h3>
          <div class="box-tools pull-right">
            <button class="btn btn-xs btn-default" data-toggle="modal" data-target="#uploadAttachModal"><i class="fa fa-upload"></i> Upload</button>
          </div>
        </div>
        <div class="box-body">
          <?php if (empty($attachments)): ?>
            <p class="text-center text-muted">No attachments.</p>
          <?php else: ?>
            <?php foreach ($attachments as $a): ?>
            <div class="attachment-item">
              <a href="<?php echo base_url('uploads/tasks/' . $a['name']); ?>" target="_blank" title="<?php echo htmlspecialchars($a['original_name']); ?>">
                <i class="fa fa-file"></i> <?php echo htmlspecialchars(mb_substr($a['original_name'], 0, 25)); ?>
              </a>
              <small class="text-muted"> (<?php echo round($a['file_size'] / 1024); ?>KB)</small>
              <button class="btn btn-xs btn-danger del_attachment" data-id="<?php echo $a['attachment_id']; ?>" style="margin-left:5px;"><i class="fa fa-times"></i></button>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /.col-md-8 -->

    <!-- RIGHT COLUMN: Meta + Time + Activity -->
    <div class="col-md-4">

      <!-- Meta Info -->
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-info-circle"></i> Details</h3>
        </div>
        <div class="box-body no-padding">
          <table class="table table-condensed" style="margin:0;">
            <tr><td><strong>Assignee</strong></td><td><?php echo htmlspecialchars($task['assignee_name'] ?: 'Unassigned'); ?></td></tr>
            <tr><td><strong>Assigned By</strong></td><td><?php echo htmlspecialchars($task['reporter_name'] ?: '-'); ?></td></tr>
            <tr><td><strong>Story</strong></td><td><?php echo htmlspecialchars($task['story_name'] ?: '-'); ?></td></tr>
            <tr><td><strong>Due Date</strong></td>
              <td style="<?php echo (!empty($task['due_date'])&&strtotime($task['due_date'])<time()&&!in_array($task['status'],array('done','closed')))?'color:#c0392b;font-weight:bold;':''; ?>">
                <?php 
                if ($task['due_date']) {
                    echo date('d-M-Y', strtotime($task['due_date'])); 
                    $diff = (strtotime($task['due_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                    if ($diff < 0 && !in_array($task['status'], array('done','closed'))) {
                        echo ' <span class="label label-danger" style="margin-left:5px;">Overdue by ' . abs(round($diff)) . ' days</span>';
                    } elseif (!in_array($task['status'], array('done','closed'))) {
                        echo ' <span class="label label-success" style="margin-left:5px;">' . round($diff) . ' days remaining</span>';
                    }
                } else {
                    echo '-';
                }
                ?>
              </td>
            </tr>
            <?php if ($task['environment'] ?? ''): ?><tr><td><strong>Environment</strong></td><td><?php echo htmlspecialchars($task['environment']); ?></td></tr><?php endif; ?>
            <?php if ($task['version'] ?? ''): ?><tr><td><strong>Version</strong></td><td><?php echo htmlspecialchars($task['version']); ?></td></tr><?php endif; ?>
            <tr><td><strong>Created</strong></td><td style="font-size:11px;"><?php echo date('d-M-Y H:i', strtotime($task['created_date'])); ?></td></tr>
            <?php if ($task['started_at']): ?><tr><td><strong>Started</strong></td><td style="font-size:11px;"><?php echo date('d-M-Y H:i', strtotime($task['started_at'])); ?></td></tr><?php endif; ?>
            <?php if ($task['completed_at']): ?><tr><td><strong>Completed</strong></td><td style="font-size:11px;"><?php echo date('d-M-Y H:i', strtotime($task['completed_at'])); ?></td></tr><?php endif; ?>
          </table>
        </div>
        <div class="box-footer">
          <!-- Quick status change -->
          <form id="quickStatusForm" style="display:flex; gap:5px;">
            <select id="quick_status" class="form-control form-control-sm" style="font-size:12px;">
              <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($task['status']==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary" style="white-space:nowrap;"><i class="fa fa-check"></i> Set</button>
          </form>
        </div>
      </div>

      <!-- Time Tracking -->
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-clock-o"></i> Time Tracking</h3>
          <div class="box-tools pull-right">
            <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#logTimeModal"><i class="fa fa-plus"></i> Log</button>
          </div>
        </div>
        <div class="box-body">
          <div class="row text-center" style="margin-bottom:10px;">
            <div class="col-xs-4">
              <div style="font-size:11px; color:#888;">Estimate</div>
              <strong><?php echo $task['estimate_hours']; ?>h <?php echo $task['estimate_minutes']; ?>m</strong>
            </div>
            <div class="col-xs-4">
              <div style="font-size:11px; color:#888;">Logged</div>
              <strong style="color:#e67e22;"><?php echo $task['logged_hours']; ?>h <?php echo $task['logged_minutes']; ?>m</strong>
            </div>
            <div class="col-xs-4">
              <div style="font-size:11px; color:#888;">Remaining</div>
              <strong style="color:<?php echo $time_remaining_min<=0?'#c0392b':'#27ae60'; ?>;">
                <?php echo floor($time_remaining_min/60); ?>h <?php echo $time_remaining_min%60; ?>m
              </strong>
            </div>
          </div>
          <div class="progress">
            <div class="progress-bar progress-bar-warning" style="width:<?php echo $time_progress_pct; ?>%;"></div>
          </div>
          <small class="text-muted"><?php echo $time_progress_pct; ?>% time used</small>

          <?php if (!empty($time_logs)): ?>
          <hr>
          <?php foreach (array_slice($time_logs, 0, 5) as $tl): ?>
          <div class="time-log-item">
            <div style="display:flex; justify-content:space-between; font-size:12px;">
              <span><strong><?php echo $tl['hours']; ?>h <?php echo $tl['minutes']; ?>m</strong> &mdash; <?php echo htmlspecialchars($tl['user_name'] ?: '?'); ?></span>
              <span class="text-muted"><?php echo date('d-M', strtotime($tl['logged_date'])); ?>
                <button class="btn btn-xs btn-danger del_timelog" data-id="<?php echo $tl['time_log_id']; ?>" style="margin-left:4px;"><i class="fa fa-trash"></i></button>
              </span>
            </div>
            <?php if ($tl['description']): ?><div style="font-size:11px; color:#777;"><?php echo htmlspecialchars($tl['description']); ?></div><?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Activity Log -->
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-history"></i> Activity</h3>
        </div>
        <div class="box-body no-padding">
          <?php if (empty($activity_logs)): ?>
            <p class="text-center text-muted" style="padding:15px; font-size:12px;">No activity yet.</p>
          <?php else: ?>
          <ul class="timeline timeline-inverse" style="padding:10px 0 10px 30px;">
            <?php foreach (array_slice($activity_logs, 0, 10) as $al): ?>
            <li>
              <i class="fa fa-<?php echo ($al['action']=='created')?'plus-circle':($al['action']=='updated'?'pencil':'times-circle'); ?> bg-<?php echo ($al['action']=='created')?'green':($al['action']=='updated'?'yellow':'red'); ?>"></i>
              <div class="timeline-item">
                <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('d-M H:i', strtotime($al['created_date'])); ?></span>
                <p style="font-size:12px; margin:0;"><?php echo htmlspecialchars($al['description']); ?> &mdash; <em><?php echo htmlspecialchars($al['user_name'] ?: '?'); ?></em></p>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /.col-md-4 -->
  </div>
</section>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url('task-list') ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Task</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($task['title']); ?>" required>
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group"><label>Project</label>
                <select name="project_id" class="form-control select2">
                  <?php foreach ($projects_list as $p): ?>
                  <option value="<?php echo $p['project_id']; ?>" <?php echo ($p['project_id']==$task['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Story</label>
                <select name="story_id" class="form-control select2">
                  <option value="">-- None --</option>
                  <?php foreach ($stories_list as $s): ?>
                  <option value="<?php echo $s['story_id']; ?>" <?php echo ($s['story_id']==$task['story_id'])?'selected':''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Type</label>
                <select name="type" class="form-control">
                  <?php foreach (TASK_TYPE_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k==$task['type'])?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Status</label>
                <select name="status" class="form-control">
                  <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k==$task['status'])?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group"><label>Priority</label>
                <select name="priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k==$task['priority'])?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>" <?php echo ($u['user_id']==$task['assigned_to'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?php echo $task['due_date'] ? date('Y-m-d', strtotime($task['due_date'])) : ''; ?>">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group"><label>Progress %</label>
                <input type="number" name="completion_percentage" class="form-control" min="0" max="100" value="<?php echo $task['completion_percentage']; ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group"><label>Estimate (H/M)</label>
                <div class="input-group">
                  <input type="number" name="estimate_hours" class="form-control" min="0" value="<?php echo $task['estimate_hours']; ?>" style="width:50%;">
                  <input type="number" name="estimate_minutes" class="form-control" min="0" max="59" value="<?php echo $task['estimate_minutes']; ?>" style="width:50%;">
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Environment</label>
                <input type="text" name="environment" class="form-control" value="<?php echo htmlspecialchars($task['environment'] ?? ''); ?>">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Version</label>
                <input type="text" name="version" class="form-control" value="<?php echo htmlspecialchars($task['version'] ?? ''); ?>">
              </div>
            </div>
          </div>
          <div class="form-group"><label>Acceptance Criteria</label>
            <textarea name="acceptance_criteria" class="form-control" rows="2"><?php echo htmlspecialchars($task['acceptance_criteria'] ?? ''); ?></textarea>
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

<!-- Add Sub-task Modal -->
<div class="modal fade" id="addSubTaskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#27ae60; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title"><i class="fa fa-plus"></i> Add Sub-task</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Title <span class="text-danger">*</span></label>
          <input type="text" id="subtask_title" class="form-control" placeholder="Sub-task title" required>
        </div>
        <div class="form-group"><label>Assign To</label>
          <select id="subtask_assigned" class="form-control select2">
            <option value="">-- Unassigned --</option>
            <?php foreach ($users_list as $u): ?>
            <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="saveSubTask" class="btn btn-success"><i class="fa fa-save"></i> Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Dependency Modal -->
<div class="modal fade" id="addDependencyModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#f39c12; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title"><i class="fa fa-link"></i> Add Dependency</h4>
      </div>
      <form id="addDependencyForm">
        <div class="modal-body">
          <p>Select a task that this task depends on (i.e. a task that must be completed first):</p>
          <div class="form-group">
            <label>Blocked By Task <span class="text-danger">*</span></label>
            <select id="depends_on_task_id" class="form-control select2" style="width: 100%;" required>
              <option value="">-- Select a Task --</option>
              <?php foreach ($project_tasks as $pt): ?>
              <option value="<?php echo $pt['task_id']; ?>"><?php echo htmlspecialchars($pt['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa fa-plus"></i> Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Log Time Modal -->
<div class="modal fade" id="logTimeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#e67e22; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title"><i class="fa fa-clock-o"></i> Log Time</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group"><label>Hours</label>
              <input type="number" id="log_hours" class="form-control" min="0" value="0">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Minutes</label>
              <input type="number" id="log_minutes" class="form-control" min="0" max="59" value="0">
            </div>
          </div>
        </div>
        <div class="form-group"><label>Date</label>
          <input type="date" id="log_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group"><label>Description</label>
          <textarea id="log_description" class="form-control" rows="2" placeholder="What did you work on?"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="saveTimeLog" class="btn btn-warning"><i class="fa fa-save"></i> Log Time</button>
      </div>
    </div>
  </div>
</div>

<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="uploadAttachForm" enctype="multipart/form-data">
        <div class="modal-header" style="background:#34495e; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-upload"></i> Upload Attachment</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Select File <span class="text-danger">*</span></label>
            <input type="file" id="attach_file" name="attachment" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Description</label>
            <input type="text" id="attach_desc" class="form-control" placeholder="Optional description">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" id="saveAttachment" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

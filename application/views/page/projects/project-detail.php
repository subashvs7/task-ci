<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1><?php echo htmlspecialchars($project['name']); ?>
    <?php if ($project['key_name']): ?><small>[<?php echo htmlspecialchars($project['key_name']); ?>]</small><?php endif; ?>
  </h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('project-list') ?>">Projects</a></li>
    <li class="active"><?php echo htmlspecialchars($project['name']); ?></li>
  </ol>
</section>

<section class="content">

  <?php
  $role_colors = array('admin'=>'#c0392b','manager'=>'#e67e22','member'=>'#2980b9','viewer'=>'#7f8c8d');
  $role_labels = array('admin'=>'Admin','manager'=>'Manager','member'=>'Member','viewer'=>'Viewer');
  ?>

  <!-- Team Members (full width) -->
  <div class="row">
    <div class="col-md-12">
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-users"></i> Team Members
            <span class="badge" style="background:#e67e22; margin-left:5px;"><?php echo count($members) + 1; ?></span>
          </h3>
          <div class="box-tools pull-right">
            <?php if (!empty($available_users)): ?>
            <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#addMemberModal">
              <i class="fa fa-user-plus"></i> Add Member
            </button>
            <?php endif; ?>
          </div>
        </div>
        <div class="box-body" style="padding:12px 15px;">
          <div style="display:flex; flex-wrap:wrap; gap:10px;" id="membersList">
            <?php
            // Owner card (no remove button)
            $ow = $project['owner_name'] ?: 'O';
            $owInit = strtoupper(implode('', array_map(function($w){ return $w[0]; }, array_slice(explode(' ', $ow), 0, 2))));
            ?>
            <div class="member-card" style="display:flex;align-items:center;gap:8px;background:#fdf2f2;border-radius:8px;padding:8px 14px;border:2px solid #c0392b;min-width:180px;">
              <div style="width:36px;height:36px;border-radius:50%;background:#c0392b;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;"><?php echo htmlspecialchars($owInit); ?></div>
              <div>
                <div style="font-weight:700;font-size:13px;"><?php echo htmlspecialchars($ow); ?></div>
                <span class="label" style="background:#c0392b;font-size:10px;"><i class="fa fa-star"></i> Owner</span>
              </div>
            </div>
            <?php foreach ($members as $m):
              $init = strtoupper(implode('', array_map(function($w){ return $w[0]; }, array_slice(explode(' ', $m['name']), 0, 2))));
              $rc = isset($role_colors[$m['role']]) ? $role_colors[$m['role']] : '#95a5a6';
              $pr = isset($role_labels[$m['project_role']]) ? $role_labels[$m['project_role']] : $m['project_role'];
            ?>
            <div class="member-card" style="display:flex;align-items:center;gap:8px;background:#f8f9fa;border-radius:8px;padding:8px 14px;border:1px solid #dee2e6;min-width:180px;" id="mcard-<?php echo $m['member_id']; ?>">
              <div style="width:36px;height:36px;border-radius:50%;background:<?php echo $rc; ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;"><?php echo htmlspecialchars($init); ?></div>
              <div style="flex:1;">
                <div style="font-weight:600;font-size:13px;"><?php echo htmlspecialchars($m['name']); ?></div>
                <span class="label" style="background:<?php echo $rc; ?>;font-size:10px;"><?php echo $pr; ?></span>
                <?php if ($m['status'] !== 'Active'): ?><span class="label label-danger" style="font-size:10px;margin-left:2px;">Inactive</span><?php endif; ?>
              </div>
              <button class="btn btn-xs btn-default btn-remove-member" data-member-id="<?php echo $m['member_id']; ?>" title="Remove from project" style="opacity:.6;"><i class="fa fa-times"></i></button>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content row -->
  <div class="row">
    <!-- Left: Project Info + Progress -->
    <div class="col-md-4">
      <div class="box box-primary">
        <div class="box-header with-border" style="background:<?php echo htmlspecialchars($project['color'] ?: '#2c3e50'); ?>; color:#fff;">
          <h3 class="box-title"><i class="fa fa-info-circle"></i> Project Info</h3>
        </div>
        <div class="box-body">
          <table class="table table-condensed" style="margin-bottom:0;">
            <tr><td><strong>Status</strong></td><td><?php $sl=PROJECT_STATUS_OPT; $k=$project['status']; echo '<span class="label label-default">'.htmlspecialchars(isset($sl[$k])?$sl[$k]:$k).'</span>'; ?></td></tr>
            <tr><td><strong>Priority</strong></td><td><span class="badge badge-priority-<?php echo $project['priority']; ?>"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$project['priority']])?$pl[$project['priority']]:$project['priority']; ?></span></td></tr>
            <tr><td><strong>Owner</strong></td><td><?php echo htmlspecialchars($project['owner_name'] ?: '-'); ?></td></tr>
            <tr><td><strong>Start</strong></td><td><?php echo $project['start_date'] ? date('d-M-Y', strtotime($project['start_date'])) : '-'; ?></td></tr>
            <tr><td><strong>End</strong></td><td><?php echo $project['end_date'] ? date('d-M-Y', strtotime($project['end_date'])) : '-'; ?></td></tr>
            <tr><td><strong>Epics</strong></td><td><?php echo $epic_count; ?></td></tr>
            <tr><td><strong>Stories</strong></td><td><?php echo $story_count; ?></td></tr>
          </table>
          <?php if ($project['description']): ?>
          <hr>
          <p style="font-size:13px;"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
          <?php endif; ?>
        </div>
        <div class="box-footer">
          <a href="<?php echo site_url('project-list') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
          <a href="<?php echo site_url('task-list?project_id=' . $project['project_id']) ?>" class="btn btn-primary btn-sm pull-right"><i class="fa fa-tasks"></i> All Tasks</a>
        </div>
      </div>

      <!-- Overall Progress -->
      <div class="box box-default">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-bar-chart"></i> Progress</h3>
        </div>
        <div class="box-body">
          <p class="text-center" style="font-size:32px; font-weight:bold; color:<?php echo $progress_pct == 100 ? '#27ae60' : '#2c3e50'; ?>;"><?php echo $progress_pct; ?>%</p>
          <div class="progress">
            <div class="progress-bar progress-bar-success" style="width:<?php echo $progress_pct; ?>%;"></div>
          </div>
          <div class="row text-center">
            <div class="col-xs-4"><strong><?php echo $total_tasks; ?></strong><br><small>Total</small></div>
            <div class="col-xs-4"><strong style="color:#27ae60;"><?php echo $done_tasks; ?></strong><br><small>Done</small></div>
            <div class="col-xs-4"><strong style="color:#c0392b;"><?php echo $overdue_tasks; ?></strong><br><small>Overdue</small></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Stats + Tasks -->
    <div class="col-md-8">
      <!-- Status breakdown -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-pie-chart"></i> Tasks by Status</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('task-kanban?project_id=' . $project['project_id']) ?>" class="btn btn-xs btn-default"><i class="fa fa-columns"></i> Kanban</a>
          </div>
        </div>
        <div class="box-body">
          <div class="row">
            <?php
            $status_colors = array('backlog'=>'#95a5a6','todo'=>'#3498db','in_progress'=>'#e67e22','in_review'=>'#9b59b6','done'=>'#27ae60','closed'=>'#7f8c8d');
            foreach (TASK_STATUS_OPT as $k => $v):
              $cnt = isset($tasks_by_status[$k]) ? $tasks_by_status[$k] : 0;
              $sc  = isset($status_colors[$k]) ? $status_colors[$k] : '#ccc';
            ?>
            <div class="col-md-4 col-xs-6" style="margin-bottom:10px; text-align:center;">
              <div style="border:2px solid <?php echo $sc; ?>; border-radius:8px; padding:10px;">
                <div style="font-size:24px; font-weight:bold; color:<?php echo $sc; ?>;"><?php echo $cnt; ?></div>
                <div style="font-size:12px; color:#666;"><?php echo $v; ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-tasks"></i> Recent Tasks</h3>
          <div class="box-tools pull-right">
            <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#quickAddTaskModal"><i class="fa fa-plus"></i> Add Task</button>
          </div>
        </div>
        <div class="box-body no-padding">
          <table class="table table-hover table-condensed">
            <thead>
              <tr><th>Title</th><th>Type</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Due</th></tr>
            </thead>
            <tbody>
              <?php if (empty($recent_tasks)): ?>
              <tr><td colspan="6" class="text-center text-muted">No tasks yet. Add your first task!</td></tr>
              <?php else: ?>
              <?php foreach ($recent_tasks as $t): ?>
              <tr>
                <td>
                  <a href="<?php echo site_url('task-detail/' . $t['task_id']) ?>" style="font-weight:600; font-size:12px;">
                    <?php echo htmlspecialchars(mb_substr($t['title'], 0, 40)); ?><?php echo strlen($t['title']) > 40 ? '...' : ''; ?>
                  </a>
                </td>
                <td><span class="badge badge-type-<?php echo $t['type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['type']])?$tl[$t['type']]:$t['type']; ?></span></td>
                <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$t['status']])?$sl[$t['status']]:$t['status']; ?></span></td>
                <td><span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span></td>
                <td style="font-size:11px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
                <td style="font-size:11px; <?php echo (!empty($t['due_date']) && strtotime($t['due_date']) < time() && !in_array($t['status'], array('done','closed'))) ? 'color:#c0392b; font-weight:bold;' : ''; ?>">
                  <?php echo $t['due_date'] ? date('d-M-Y', strtotime($t['due_date'])) : '-'; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#e67e22; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title"><i class="fa fa-user-plus"></i> Add Team Member</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Select User <span class="text-danger">*</span></label>
          <select id="add_member_user" class="form-control">
            <option value="">-- Choose a user --</option>
            <?php foreach ($available_users as $u): ?>
            <option value="<?php echo $u['user_id']; ?>"
              data-name="<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>"
              data-role="<?php echo $u['role']; ?>">
              <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['email']); ?>)
              — <?php echo isset($role_labels[$u['role']]) ? $role_labels[$u['role']] : $u['role']; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Project Role</label>
          <select id="add_member_role" class="form-control">
            <?php foreach ($role_labels as $k => $v): ?>
            <option value="<?php echo $k; ?>" <?php echo ($k==='member')?'selected':''; ?>><?php echo $v; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="btnAddMember" data-project-id="<?php echo $project['project_id']; ?>">
          <i class="fa fa-user-plus"></i> Add Member
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Quick Add Task Modal -->
<div class="modal fade" id="quickAddTaskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url('task-list') ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Quick Add Task to <?php echo htmlspecialchars($project['name']); ?></h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Task Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="Enter task title" required>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Type</label>
                <select name="type" class="form-control">
                  <?php foreach (TASK_TYPE_OPT as $k => $v): ?>
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
                  <option value="<?php echo $k; ?>" <?php echo ($k=='medium')?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" class="form-control">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Add Task</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

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
  $role_colors = array('admin'=>'#c0392b','manager'=>'#e67e22','team_leader'=>'#2980b9','staff'=>'#7f8c8d');
  $role_labels = array('admin'=>'Admin','manager'=>'Manager','team_leader'=>'Team Leader','staff'=>'Staff');
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
            <!-- Add Member button has been removed as per new logic where Team Leaders are auto-added when a Manager handles a project -->
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
                <span class="label" style="background:#c0392b;font-size:10px;"><i class="fa fa-star"></i> Manager</span>
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
  <div class="row" style="display: flex; flex-wrap: wrap;">
    <!-- Left: Project Info + Progress -->
    <div class="col-md-4" style="display: flex; flex-direction: column;">
      <div class="box box-primary" style="flex: 1; display: flex; flex-direction: column; margin-bottom: 20px;">
        <div class="box-header with-border" style="background:<?php echo htmlspecialchars($project['color'] ?: '#2c3e50'); ?>; color:#fff;">
          <h3 class="box-title"><i class="fa fa-info-circle"></i> Project Info</h3>
        </div>
        <div class="box-body" style="flex: 1;">
          <table class="table table-condensed" style="margin-bottom:0;">
            <tr><td><strong>Status</strong></td><td><?php $sl=PROJECT_STATUS_OPT; $k=$project['status']; echo '<span class="label label-default">'.htmlspecialchars(isset($sl[$k])?$sl[$k]:$k).'</span>'; ?></td></tr>
            <tr><td><strong>Priority</strong></td><td><span class="badge badge-priority-<?php echo $project['priority']; ?>"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$project['priority']])?$pl[$project['priority']]:$project['priority']; ?></span></td></tr>
            <tr><td><strong>Manager</strong></td><td><?php echo htmlspecialchars($project['owner_name'] ?: '-'); ?></td></tr>
            <?php if (!empty($project['stacks'])): ?>
            <tr><td><strong>Stacks</strong></td><td><?php echo htmlspecialchars($project['stacks']); ?></td></tr>
            <?php endif; ?>
            <tr><td><strong>Start</strong></td><td><?php echo $project['start_date'] ? date('d-M-Y', strtotime($project['start_date'])) : '-'; ?></td></tr>
            <tr>
              <td><strong>End</strong></td>
              <td>
                <?php 
                if ($project['end_date']) {
                    echo date('d-M-Y', strtotime($project['end_date']));
                    $diff = (strtotime($project['end_date']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                    if ($diff < 0 && !in_array($project['status'], array('completed','cancelled'))) {
                        echo ' <span class="label label-danger" style="margin-left:5px;">Overdue by ' . abs(round($diff)) . ' days</span>';
                    } elseif (!in_array($project['status'], array('completed','cancelled'))) {
                        echo ' <span class="label label-success" style="margin-left:5px;">' . round($diff) . ' days remaining</span>';
                    }
                } else {
                    echo '-';
                }
                ?>
              </td>
            </tr>
            <tr><td><strong>Epics</strong></td><td><?php echo $epic_count; ?></td></tr>
            <tr><td><strong>Stories</strong></td><td><?php echo $story_count; ?></td></tr>
          </table>
          <?php if ($project['description']): ?>
          <hr>
          <p style="font-size:13px;"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
          <?php endif; ?>
        </div>
        <div class="box-footer" style="background: #f9fafc;">
          <a href="<?php echo site_url('project-list') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
      </div>

    </div>

    <!-- Right: Stats + Tasks -->
    <div class="col-md-8">
      
      <!-- Analytics Charts -->
      <div class="row">
        <!-- Row 1 -->
        <div class="col-md-4">
          <div class="box box-info">
            <div class="box-header with-border" style="padding: 10px;">
              <h3 class="box-title" style="font-size:13px; font-weight:600;"><i class="fa fa-pie-chart"></i> Tasks by Status</h3>
            </div>
            <div class="box-body" style="height: 180px; display:flex; align-items:center; justify-content:center; padding: 10px;">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-success">
            <div class="box-header with-border" style="padding: 10px;">
              <h3 class="box-title" style="font-size:13px; font-weight:600;"><i class="fa fa-tasks"></i> Task Progress</h3>
            </div>
            <div class="box-body" style="height: 180px; display:flex; align-items:center; justify-content:center; padding: 10px;">
              <canvas id="progressChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-warning">
            <div class="box-header with-border" style="padding: 10px;">
              <h3 class="box-title" style="font-size:13px; font-weight:600;"><i class="fa fa-exclamation-circle"></i> Tasks by Priority</h3>
            </div>
            <div class="box-body" style="height: 180px; display:flex; align-items:center; justify-content:center; padding: 10px;">
              <canvas id="priorityChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row">
        <!-- Row 2 -->
        <div class="col-md-4">
          <div class="box box-primary">
            <div class="box-header with-border" style="padding: 10px;">
              <h3 class="box-title" style="font-size:13px; font-weight:600;"><i class="fa fa-cubes"></i> Tasks by Type</h3>
            </div>
            <div class="box-body" style="height: 180px; display:flex; align-items:center; justify-content:center; padding: 10px;">
              <canvas id="typeChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-danger">
            <div class="box-header with-border" style="padding: 10px;">
              <h3 class="box-title" style="font-size:13px; font-weight:600;"><i class="fa fa-users"></i> Tasks by Assignee</h3>
            </div>
            <div class="box-body" style="height: 180px; display:flex; align-items:center; justify-content:center; padding: 10px;">
              <canvas id="assigneeChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-default">
            <div class="box-header with-border" style="padding: 10px;">
              <h3 class="box-title" style="font-size:13px; font-weight:600;"><i class="fa fa-line-chart"></i> Time Logged (7 Days)</h3>
            </div>
            <div class="box-body" style="height: 180px; display:flex; align-items:center; justify-content:center; padding: 10px;">
              <canvas id="timeLogChart"></canvas>
            </div>
          </div>
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
            <?php 
            $assignable = get_assignable_roles();
            $allowed_keys = array_keys($assignable);
            foreach ($available_users as $u): 
              if (!in_array($u['role'], $allowed_keys)) continue;
            ?>
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
            <?php foreach (get_assignable_roles() as $k => $v): ?>
            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
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
            <div class="col-md-12">
              <div class="form-group">
                <label>Assign To</label>
                <select name="assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php 
                  $cur_role = $this->session->userdata(SESS_HEAD . '_role');
                  foreach ($users_list as $u): 
                      if ($cur_role === 'manager' && $u['role'] !== 'team_leader') continue;
                      if ($cur_role === 'team_leader' && $u['role'] !== 'staff') continue;
                  ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?> (<?php echo ucfirst(str_replace('_', ' ', $u['role'])); ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <input type="hidden" name="reporter_id" value="<?php echo $this->session->userdata(SESS_HEAD . '_user_id'); ?>">
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

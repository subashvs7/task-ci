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
            <select name="project_id" id="filter_project_id" class="form-control select2">
              <option value="">All Projects</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project == $p['project_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Status Filter -->
          <div class="col-md-2">
            <select name="f_status" id="filter_f_status" class="form-control">
              <option value="">All Statuses</option>
              <?php foreach (TASK_STATUS_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Priority Filter -->
          <div class="col-md-2">
            <select name="f_priority" id="filter_f_priority" class="form-control">
              <option value="">All Priorities</option>
              <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_priority==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <!-- Assignee Filter -->
          <div class="col-md-3">
            <select name="assigned_to" id="filter_assigned_to" class="form-control select2">
              <option value="">All Assignees</option>
              <?php foreach ($filter_users_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_assigned==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?> (<?php echo ucwords(str_replace('_', ' ', $u['role'])); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <div class="col-md-2">
            <button type="button" id="btn_reset_filters" class="btn btn-default btn-block"><i class="fa fa-refresh"></i> Reset</button>
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
            <th>User Story</th>
            <th>Type</th>
            <th>Status / Priority</th>
            <th>Assignee</th>
            <th>Work Status</th>
            <th>Due Date</th>
            <th>Est. Time</th>
            <th>Logged Time</th>
            <?php if ($show_actions): ?>
            <th style="width:90px;">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody id="task_list_tbody">
          <?php include('task-list-rows.php'); ?>
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
                 </select>
               </div>
             </div>
             <?php if ($cur_role !== 'team_leader'): ?>
             <div class="col-md-4">
               <div class="form-group"><label>Story</label>
                 <select name="story_id" id="add_task_story" class="form-control">
                   <option value="">-- Select Story --</option>
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
            <div class="col-md-6">
              <div class="form-group"><label>Start Time</label>
                <input type="datetime-local" name="start_time" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>End Time</label>
                <input type="datetime-local" name="end_time" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '4'; ?>">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($filter_users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?> (<?php echo ucwords(str_replace('_', ' ', $u['role'])); ?>)</option>
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
                 </select>
               </div>
             </div>
             <?php if ($cur_role !== 'team_leader'): ?>
             <div class="col-md-4">
               <div class="form-group"><label>Story</label>
                 <select name="story_id" id="edit_task_story" class="form-control">
                   <option value="">-- Select Story --</option>
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
            <div class="col-md-6">
              <div class="form-group"><label>Start Time</label>
                <input type="datetime-local" name="start_time" id="edit_start_time" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>End Time</label>
                <input type="datetime-local" name="end_time" id="edit_end_time" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-<?php echo in_array($cur_role, array('manager', 'team_leader')) ? '6' : '4'; ?>">
              <div class="form-group"><label>Assign To</label>
                <select name="assigned_to" id="edit_assigned_to" class="form-control select2">
                  <option value="">-- Unassigned --</option>
                  <?php foreach ($filter_users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?> (<?php echo ucwords(str_replace('_', ' ', $u['role'])); ?>)</option>
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




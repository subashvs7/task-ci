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
            <select name="project_id" id="filter_story_project" class="form-control select2">
              <option value="">All Projects</option>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Epic</label>
            <select name="epic_id" id="filter_story_epic" class="form-control select2">
              <option value="">All Epics</option>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Creator</label>
            <select name="creator_id" id="filter_story_creator" class="form-control select2">
              <option value="">All Creators</option>
              <?php foreach ($users_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_creator==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-2" style="padding-top:25px;"><button type="button" id="btn_reset_filters" class="btn btn-default btn-block"><i class="fa fa-refresh"></i> Reset</button></div>
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
        <tbody id="story_list_tbody">
          <?php include('story-list-rows.php'); ?>
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
              <select name="project_id" id="add_story_project" class="form-control select2" required>
                <option value="">-- Select Project --</option>
              </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Epic</label>
            <select name="epic_id" class="form-control select2" id="add_epic_select">
                <option value="">-- No Epic --</option>
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
                <option value="">-- Select Project --</option>
              </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Epic</label>
              <select name="epic_id" id="edit_story_epic" class="form-control select2">
                <option value="">-- No Epic --</option>
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
                <button type="button" class="btn btn-primary" style="border-radius:50%; width:60px; height:60px; margin-bottom:15px; font-size:24px; pointer-events: none;">
                    <i class="fa fa-plus"></i>
                </button>
                <h4 style="margin: 0; color: #7f8c8d;">Drag and Upload or Browse Files here.</h4>
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

<script>
window.initialFilterProject = '<?php echo $f_project ?: ""; ?>';
window.initialFilterEpic = '<?php echo $f_epic ?: ""; ?>';
</script>
<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

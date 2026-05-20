<?php include_once(VIEWPATH . 'inc/header.php'); ?>

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
          <tr><th>#</th><th>Story Name</th><th>Project</th><th>Epic</th><th>Status</th><th>Priority</th><th>Points</th><th>Assignee</th><th>Tasks</th><th>Actions</th></tr>
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
              <?php if ($s['sprint']): ?><br><span class="label label-default" style="font-size:10px;"><?php echo htmlspecialchars($s['sprint']); ?></span><?php endif; ?>
            </td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($s['project_name'] ?: '-'); ?></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($s['epic_name'] ?: '-'); ?></td>
            <td><span class="badge badge-status-<?php echo $s['status']; ?>" style="font-size:10px;"><?php $sl=TASK_STATUS_OPT; echo isset($sl[$s['status']])?$sl[$s['status']]:$s['status']; ?></span></td>
            <td><span class="badge badge-priority-<?php echo $s['priority']; ?>" style="font-size:10px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$s['priority']])?$pl[$s['priority']]:$s['priority']; ?></span></td>
            <td><span class="badge bg-purple" style="font-size:11px;"><?php echo $s['story_points']; ?> pts</span></td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($s['assignee_name'] ?: '-'); ?></td>
            <td><a href="<?php echo site_url('task-list?story_id=' . $s['story_id']); ?>" class="btn btn-xs btn-info"><?php echo $s['task_count']; ?> <i class="fa fa-tasks"></i></a></td>
            <td>
              <button class="btn btn-xs btn-warning btn-edit-story"
                data-id="<?php echo $s['story_id']; ?>"
                data-project="<?php echo $s['project_id']; ?>"
                data-epic="<?php echo $s['epic_id']; ?>"
                data-name="<?php echo htmlspecialchars($s['name'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($s['description'], ENT_QUOTES); ?>"
                data-status="<?php echo $s['status']; ?>"
                data-priority="<?php echo $s['priority']; ?>"
                data-points="<?php echo $s['story_points']; ?>"
                data-assignee="<?php echo $s['assignee_id']; ?>"
                data-sprint="<?php echo htmlspecialchars($s['sprint'], ENT_QUOTES); ?>">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $s['story_id']; ?>" data-tbl="tm_user_stories" data-col="story_id"><i class="fa fa-trash"></i></button>
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
              <select name="epic_id" class="form-control select2">
                <option value="">-- No Epic --</option>
                <?php foreach ($epics_list as $e): ?>
                <option value="<?php echo $e['epic_id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
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
            <div class="col-md-3"><div class="form-group"><label>Status</label>
              <select name="status" class="form-control">
                <?php foreach (TASK_STATUS_OPT as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($k=='backlog')?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-3"><div class="form-group"><label>Priority</label>
              <select name="priority" class="form-control">
                <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($k=='medium')?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-3"><div class="form-group"><label>Story Points</label>
              <input type="number" name="story_points" class="form-control" min="0" value="0">
            </div></div>
            <div class="col-md-3"><div class="form-group"><label>Sprint</label>
              <input type="text" name="sprint" class="form-control" placeholder="e.g. Sprint 1">
            </div></div>
          </div>
          <div class="form-group"><label>Assignee</label>
            <select name="assignee_id" class="form-control select2">
              <option value="">-- Unassigned --</option>
              <?php foreach ($users_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
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
                <option value="<?php echo $e['epic_id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
          </div>
          <div class="form-group"><label>Story Name</label><input type="text" name="name" id="edit_story_name" class="form-control" required></div>
          <div class="form-group"><label>Description</label><textarea name="description" id="edit_story_desc" class="form-control" rows="2"></textarea></div>
          <div class="row">
            <div class="col-md-3"><div class="form-group"><label>Status</label>
              <select name="status" id="edit_story_status" class="form-control">
                <?php foreach (TASK_STATUS_OPT as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-3"><div class="form-group"><label>Priority</label>
              <select name="priority" id="edit_story_priority" class="form-control">
                <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-3"><div class="form-group"><label>Story Points</label><input type="number" name="story_points" id="edit_story_points" class="form-control" min="0"></div></div>
            <div class="col-md-3"><div class="form-group"><label>Sprint</label><input type="text" name="sprint" id="edit_story_sprint" class="form-control"></div></div>
          </div>
          <div class="form-group"><label>Assignee</label>
            <select name="assignee_id" id="edit_story_assignee" class="form-control select2">
              <option value="">-- Unassigned --</option>
              <?php foreach ($users_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
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

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

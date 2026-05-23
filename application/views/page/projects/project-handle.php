<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Project Handle <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Project Handle</li>
  </ol>
</section>

<section class="content">

  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>

  <div class="box box-default collapsed-box">
    <div class="box-header with-border" data-widget="collapse" style="cursor:pointer;">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-5">
            <div class="form-group"><label>Project</label>
              <select name="project_id" class="form-control select2">
                <option value="">All Projects</option>
                <?php foreach ($projects_list as $p): ?>
                <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-5">
            <div class="form-group"><label>Team Leader</label>
              <select name="team_leader_id" class="form-control select2">
                <option value="">All Team Leaders</option>
                <?php foreach ($team_leaders_list as $u): ?>
                <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_leader==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-handshake-o"></i> Delegated Projects</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addHandlerModal"><i class="fa fa-plus"></i> Handle Project</button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr><th>#</th><th>Project</th><th>Team Leader</th><th>Due Date</th><th>Remaining Days</th><th>Status</th><th>Assigned By</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No handled projects found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $h): ?>
          <tr>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <strong><?php echo htmlspecialchars($h['project_name']); ?></strong>
              <?php if (!empty($h['notes'])): ?>
                <br><small class="text-muted"><i class="fa fa-sticky-note-o"></i> <?php echo htmlspecialchars($h['notes']); ?></small>
              <?php endif; ?>
            </td>
            <td>
              <span style="font-weight:600;"><?php echo htmlspecialchars($h['team_leader_name']); ?></span><br>
              <small class="text-muted"><?php echo htmlspecialchars($h['team_leader_email']); ?></small>
            </td>
            <td>
              <?php echo date('d-M-Y', strtotime($h['due_date'])); ?>
            </td>
            <td>
              <?php
                $now_date = strtotime(date('Y-m-d'));
                $due_date = strtotime(date('Y-m-d', strtotime($h['due_date'])));
                $days = ($due_date - $now_date) / 86400;
                if ($h['status'] === 'inactive') {
                    echo '<span class="text-muted">Completed</span>';
                } elseif ($days < 0) {
                    echo '<span class="text-danger"><strong>' . abs($days) . ' days overdue</strong></span>';
                } elseif ($days == 0) {
                    echo '<span class="text-warning"><strong>Due today</strong></span>';
                } else {
                    echo '<span class="text-success">' . $days . ' days remaining</span>';
                }
              ?>
            </td>
            <td>
              <?php if ($h['status'] == 'active'): ?>
                <span class="label label-success">Active</span>
              <?php else: ?>
                <span class="label label-default">Inactive</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($h['assigned_by_name']); ?></td>
            <td>
              <button class="btn btn-xs btn-warning btn-edit-handler"
                data-id="<?php echo $h['handler_id']; ?>"
                data-project="<?php echo $h['project_id']; ?>"
                data-leader="<?php echo $h['team_leader_id']; ?>"
                data-due="<?php echo $h['due_date']; ?>"
                data-notes="<?php echo htmlspecialchars($h['notes']); ?>"
                data-status="<?php echo $h['status']; ?>">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $h['handler_id']; ?>" data-tbl="tm_project_handlers" data-col="handler_id"><i class="fa fa-trash"></i></button>
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

<!-- Add Modal -->
<div class="modal fade" id="addHandlerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Handle Project to Team Leader</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project <span class="text-danger">*</span></label>
            <select name="project_id" class="form-control select2" required style="width:100%;">
              <option value="">-- Select Project --</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>"
                      data-enddate="<?php echo !empty($p['end_date']) ? date('d-m-Y', strtotime($p['end_date'])) : ''; ?>"
                      data-days="<?php echo $p['manager_deadline_days']; ?>">
                <?php echo htmlspecialchars($p['name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Handled To (Team Leader) <span class="text-danger">*</span></label>
            <select name="team_leader_id" class="form-control select2" required style="width:100%;">
              <option value="">-- Select Team Leader --</option>
              <?php foreach ($team_leaders_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group"><label>Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control project-due-date-input" required>
                <div class="project-deadline-info" style="margin-top: 5px; font-size: 11px; color: #7f8c8d; display: none;">
                  Overall Deadline: <span class="lbl-project-end-date">-</span> (<span class="lbl-project-days">-</span> days remaining)
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>Status</label>
                <select name="status" class="form-control">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Notes / Description</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Enter notes or delegation details..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editHandlerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="handler_id" id="edit_handler_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Project Handle</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project <span class="text-danger">*</span></label>
            <select name="project_id" id="edit_handler_project" class="form-control select2" required style="width:100%;">
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>"
                      data-enddate="<?php echo !empty($p['end_date']) ? date('d-m-Y', strtotime($p['end_date'])) : ''; ?>"
                      data-days="<?php echo $p['manager_deadline_days']; ?>">
                <?php echo htmlspecialchars($p['name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Handled To (Team Leader) <span class="text-danger">*</span></label>
            <select name="team_leader_id" id="edit_handler_leader" class="form-control select2" required style="width:100%;">
              <?php foreach ($team_leaders_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group"><label>Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" id="edit_handler_due" class="form-control project-due-date-input" required>
                <div class="project-deadline-info" style="margin-top: 5px; font-size: 11px; color: #7f8c8d; display: none;">
                  Overall Deadline: <span class="lbl-project-end-date">-</span> (<span class="lbl-project-days">-</span> days remaining)
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>Status</label>
                <select name="status" id="edit_handler_status" class="form-control">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Notes / Description</label>
            <textarea name="notes" id="edit_handler_notes" class="form-control" rows="3" placeholder="Enter notes or delegation details..."></textarea>
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

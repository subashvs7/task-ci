<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Projects <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Projects</li>
  </ol>
</section>

<section class="content">

  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo $this->session->flashdata('alert_success'); ?>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?php echo $this->session->flashdata('alert_error'); ?>
    </div>
  <?php endif; ?>

  <!-- Filter Box -->
  <div class="box box-default collapsed-box">
    <div class="box-header with-border" style="cursor:pointer;" data-widget="collapse">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter / Search</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Search Name</label>
              <input type="text" name="search" class="form-control" placeholder="Project name..." value="<?php echo htmlspecialchars($f_search ?: ''); ?>">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Status</label>
              <select name="f_status" class="form-control">
                <option value="">All Status</option>
                <?php foreach (PROJECT_STATUS_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_status == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Priority</label>
              <select name="f_priority" class="form-control">
                <option value="">All Priority</option>
                <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_priority == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
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

  <!-- Main Box -->
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-folder-open"></i> Project List</h3>
      <div class="box-tools pull-right">
        <a href="<?php echo site_url('project-kanban') ?>" class="btn btn-sm btn-default"><i class="fa fa-columns"></i> Kanban View</a>
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addProjectModal">
          <i class="fa fa-plus"></i> Add Project
        </button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Project</th>
            <th>Key</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Tasks</th>
            <th>Progress</th>
            <th>Owner</th>
            <th>Dates</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="10" class="text-center text-muted">No projects found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $p):
            $ptask  = (int)$p['task_count'];
            $pdone  = (int)$p['done_count'];
            $ppct   = $ptask > 0 ? round(($pdone / $ptask) * 100) : 0;
            $overdue = (int)$p['overdue_count'];
          ?>
          <tr>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <a href="<?php echo site_url('project-detail/' . $p['project_id']) ?>" style="font-weight:600;">
                <?php echo htmlspecialchars($p['name']); ?>
              </a>
              <?php if ($p['description']): ?>
                <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($p['description'], 0, 60)); ?>...</small>
              <?php endif; ?>
            </td>
            <td><code><?php echo htmlspecialchars($p['key_name'] ?: '-'); ?></code></td>
            <td>
              <?php
              $sc = array('planning'=>'purple','active'=>'green','on_hold'=>'yellow','completed'=>'blue','cancelled'=>'red');
              $sl = PROJECT_STATUS_OPT;
              $scl = isset($sc[$p['status']]) ? $sc[$p['status']] : 'default';
              ?>
              <span class="label label-<?php echo $scl; ?>"><?php echo isset($sl[$p['status']]) ? $sl[$p['status']] : $p['status']; ?></span>
            </td>
            <td><span class="badge badge-priority-<?php echo $p['priority']; ?>"><?php $pl = TASK_PRIORITY_OPT; echo isset($pl[$p['priority']]) ? $pl[$p['priority']] : $p['priority']; ?></span></td>
            <td>
              <?php echo $pdone; ?>/<?php echo $ptask; ?>
              <?php if ($overdue > 0): ?>
                <br><span class="text-danger" style="font-size:11px;"><i class="fa fa-exclamation-triangle"></i> <?php echo $overdue; ?> overdue</span>
              <?php endif; ?>
            </td>
            <td style="min-width:100px;">
              <div class="progress progress-xs" style="margin-bottom:0;">
                <div class="progress-bar bg-green" style="width:<?php echo $ppct; ?>%;"></div>
              </div>
              <small><?php echo $ppct; ?>%</small>
            </td>
            <td><?php echo htmlspecialchars($p['owner_name'] ?: '-'); ?></td>
            <td style="font-size:11px; white-space:nowrap;">
              <?php echo $p['start_date'] ? date('d-M-Y', strtotime($p['start_date'])) : '-'; ?>
              <?php if ($p['end_date']): ?><br>to <?php echo date('d-M-Y', strtotime($p['end_date'])); ?><?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <a href="<?php echo site_url('project-detail/' . $p['project_id']) ?>" class="btn btn-xs btn-info" title="View"><i class="fa fa-eye"></i></a>
              <button class="btn btn-xs btn-warning btn-edit-project"
                data-id="<?php echo $p['project_id']; ?>"
                data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                data-key="<?php echo htmlspecialchars($p['key_name'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($p['description'], ENT_QUOTES); ?>"
                data-status="<?php echo $p['status']; ?>"
                data-priority="<?php echo $p['priority']; ?>"
                data-color="<?php echo htmlspecialchars($p['color'], ENT_QUOTES); ?>"
                data-start="<?php echo $p['start_date']; ?>"
                data-end="<?php echo $p['end_date']; ?>"
                data-owner="<?php echo $p['owner_id']; ?>"
                title="Edit">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $p['project_id']; ?>" data-tbl="tm_projects" data-col="project_id" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="box-footer clearfix">
      <?php echo $pagination; ?>
    </div>
  </div>

</section>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#2c3e50; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add New Project</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Project Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter project name" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Project Key</label>
                <input type="text" name="key_name" class="form-control" placeholder="e.g. PROJ" maxlength="10">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Project description..."></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                  <?php foreach (PROJECT_STATUS_OPT as $k => $v): ?>
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
                  <option value="<?php echo $k; ?>" <?php echo ($k == 'medium') ? 'selected' : ''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Color</label>
                <input type="color" name="color" class="form-control" value="#2c3e50" style="height:38px; padding:2px;">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Owner</label>
                <select name="owner_id" class="form-control select2">
                  <option value="">-- Select Owner --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="project_id" id="edit_project_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Project</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Project Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Project Key</label>
                <input type="text" name="key_name" id="edit_key_name" class="form-control" maxlength="10">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Status</label>
                <select name="status" id="edit_status" class="form-control">
                  <?php foreach (PROJECT_STATUS_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Priority</label>
                <select name="priority" id="edit_priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k => $v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Color</label>
                <input type="color" name="color" id="edit_color" class="form-control" style="height:38px; padding:2px;">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" id="edit_start_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" id="edit_end_date" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Owner</label>
                <select name="owner_id" id="edit_owner_id" class="form-control select2">
                  <option value="">-- Select Owner --</option>
                  <?php foreach ($users_list as $u): ?>
                  <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Update Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

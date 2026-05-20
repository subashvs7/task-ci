<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Users <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Users</li>
  </ol>
</section>

<style>
  .user-avatar { width:36px; height:36px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; color:#fff; vertical-align:middle; }
  .ua-admin   { background:#c0392b; }
  .ua-manager { background:#e67e22; }
  .ua-member  { background:#2980b9; }
  .ua-viewer  { background:#7f8c8d; }
  .ua-default { background:#95a5a6; }
  .task-badge { font-size:10px; border-radius:10px; padding:2px 7px; }
</style>

<section class="content">
  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_error'); ?></div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="box box-default collapsed-box">
    <div class="box-header with-border" data-widget="collapse" style="cursor:pointer;">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter / Search</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?php echo ($f_search||$f_role||$f_status)?'minus':'plus'; ?>"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-4"><div class="form-group"><label>Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or email..." value="<?php echo htmlspecialchars($f_search ?? ''); ?>">
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Role</label>
            <select name="f_role" class="form-control">
              <option value="">All Roles</option>
              <?php foreach (USER_ROLE_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_role==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-3"><div class="form-group"><label>Status</label>
            <select name="f_status" class="form-control">
              <option value="">All Status</option>
              <option value="Active"   <?php echo ($f_status=='Active')  ?'selected':''; ?>>Active</option>
              <option value="Inactive" <?php echo ($f_status=='Inactive')?'selected':''; ?>>Inactive</option>
            </select>
          </div></div>
          <div class="col-md-2" style="padding-top:25px;">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Search</button>
          </div>
        </div>
        <div class="row">
          <div class="col-md-2" style="padding-top:5px;">
            <a href="<?php echo site_url($s_url) ?>" class="btn btn-default btn-block"><i class="fa fa-times"></i> Clear</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-users"></i> User List</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addUserModal" id="btnAddUser">
          <i class="fa fa-user-plus"></i> Add User
        </button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>User</th>
            <th>Role</th>
            <th>Tasks</th>
            <th>Status</th>
            <th>Joined</th>
            <th style="width:100px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No users found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $u):
            $initials = strtoupper(implode('', array_map(function($w){ return $w[0]; }, array_slice(explode(' ', $u['name']), 0, 2))));
            $avatarClass = 'ua-' . ($u['role'] ?: 'default');
          ?>
          <tr>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <div class="user-avatar <?php echo $avatarClass; ?>"><?php echo htmlspecialchars($initials); ?></div>
                <div>
                  <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                  <?php if ($this->session->userdata(SESS_HEAD.'_user_id') == $u['user_id']): ?>
                    <span class="label label-primary" style="font-size:9px;">You</span>
                  <?php endif; ?>
                  <br><small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
                </div>
              </div>
            </td>
            <td>
              <?php $rc = array('admin'=>'danger','manager'=>'warning','member'=>'info','viewer'=>'default'); $rl = USER_ROLE_OPT; ?>
              <span class="label label-<?php echo isset($rc[$u['role']])?$rc[$u['role']]:'default'; ?>">
                <?php echo isset($rl[$u['role']]) ? $rl[$u['role']] : $u['role']; ?>
              </span>
            </td>
            <td>
              <?php if ($u['task_count'] > 0): ?>
                <span class="badge task-badge" style="background:#3498db;" title="Total assigned tasks"><?php echo $u['task_count']; ?> assigned</span>
                <?php if ($u['active_task_count'] > 0): ?>
                  <span class="badge task-badge" style="background:#e67e22; margin-left:3px;" title="In Progress"><?php echo $u['active_task_count']; ?> active</span>
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted" style="font-size:12px;">No tasks</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="label label-<?php echo ($u['status']=='Active')?'success':'danger'; ?>">
                <i class="fa fa-<?php echo ($u['status']=='Active')?'check':'ban'; ?>"></i> <?php echo $u['status']; ?>
              </span>
            </td>
            <td style="font-size:12px;"><?php echo date('d-M-Y', strtotime($u['created_date'])); ?></td>
            <td style="white-space:nowrap;">
              <button class="btn btn-xs btn-warning btn-edit-user"
                data-id="<?php echo $u['user_id']; ?>"
                data-name="<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>"
                data-email="<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>"
                data-role="<?php echo $u['role']; ?>"
                title="Edit"><i class="fa fa-pencil"></i>
              </button>
              <form method="post" action="<?php echo site_url($s_url . '/' . $sno) ?>" style="display:inline;">
                <input type="hidden" name="mode" value="ToggleStatus">
                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                <?php if ($this->session->userdata(SESS_HEAD.'_user_id') != $u['user_id']): ?>
                <button type="submit" class="btn btn-xs <?php echo ($u['status']=='Active')?'btn-default':'btn-success'; ?>"
                  title="<?php echo ($u['status']=='Active')?'Deactivate':'Activate'; ?>"
                  onclick="return confirm('<?php echo ($u['status']=='Active')?'Deactivate':'Activate'; ?> <?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>?')">
                  <i class="fa fa-<?php echo ($u['status']=='Active')?'ban':'check'; ?>"></i>
                </button>
                <?php endif; ?>
              </form>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-user-plus"></i> Add New User</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. John Smith">
          </div>
          <div class="form-group"><label>Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required placeholder="user@example.com">
          </div>
          <div class="form-group">
            <label>Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password" id="add_password" class="form-control" required placeholder="Min 6 characters">
              <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-toggle-pw" data-target="#add_password"><i class="fa fa-eye"></i></button>
              </span>
            </div>
            <div id="pw_strength_bar" style="height:4px; border-radius:2px; margin-top:5px; transition:width .3s; width:0;"></div>
            <small id="pw_strength_text" class="text-muted"></small>
          </div>
          <div class="form-group"><label>Role</label>
            <select name="role" class="form-control">
              <?php foreach (USER_ROLE_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>" <?php echo ($k==='member')?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Assign to Project <small class="text-muted">(optional)</small></label>
            <select name="assign_project_id" class="form-control">
              <option value="">-- No project --</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url . '/' . $sno) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="user_id" id="edit_user_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit User</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_user_name" class="form-control" required>
          </div>
          <div class="form-group"><label>Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" id="edit_user_email" class="form-control" required>
          </div>
          <div class="form-group">
            <label>New Password <small class="text-muted">(leave blank to keep current)</small></label>
            <div class="input-group">
              <input type="password" name="new_password" id="edit_password" class="form-control" placeholder="Min 6 characters">
              <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-toggle-pw" data-target="#edit_password"><i class="fa fa-eye"></i></button>
              </span>
            </div>
          </div>
          <div class="form-group"><label>Role</label>
            <select name="role" id="edit_user_role" class="form-control select2">
              <?php foreach (USER_ROLE_OPT as $k => $v): ?>
              <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Update User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

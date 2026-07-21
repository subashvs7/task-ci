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
  .ua-team_leader { background:#2980b9; }
  .ua-staff   { background:#7f8c8d; }
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
          <div class="col-md-5"><div class="form-group"><label>Role</label>
            <select name="f_role" id="filter_user_role" class="form-control select2">
              <option value="">All Roles</option>
              <?php 
              $curr_role = $this->session->userdata(SESS_HEAD . '_role');
              $filter_roles = ($curr_role === 'admin') ? USER_ROLE_OPT : get_assignable_roles();
              foreach ($filter_roles as $k => $v): 
              ?>
              <option value="<?php echo $k; ?>" <?php echo ($f_role==$k)?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div></div>
          <div class="col-md-5"><div class="form-group"><label>Status</label>
            <select name="f_status" id="filter_user_status" class="form-control select2">
              <option value="">All Status</option>
              <option value="Active"   <?php echo ($f_status=='Active')  ?'selected':''; ?>>Active</option>
              <option value="Inactive" <?php echo ($f_status=='Inactive')?'selected':''; ?>>Inactive</option>
            </select>
          </div></div>
          <div class="col-md-2" style="padding-top:25px;">
            <button type="button" id="btn_reset_filters" class="btn btn-default btn-block"><i class="fa fa-refresh"></i> Reset</button>
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
    <div class="box-body" id="user_list_tbody" style="padding: 15px;">
      <?php include('user-list-rows.php'); ?>
    </div>
    <!-- <div class="box-footer clearfix"><?php echo isset($pagination)?$pagination:''; ?></div> -->
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
            <input type="email" name="email" class="form-control" required placeholder="user@example.com" autocomplete="off">
          </div>
          <div class="form-group">
            <label>Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password" id="add_password" class="form-control" required placeholder="Min 6 characters" autocomplete="new-password">
              <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-toggle-pw" data-target="#add_password"><i class="fa fa-eye"></i></button>
              </span>
            </div>
            <div id="pw_strength_bar" style="height:4px; border-radius:2px; margin-top:5px; transition:width .3s; width:0;"></div>
            <small id="pw_strength_text" class="text-muted"></small>
          </div>
          <div class="form-group"><label>Role</label>
            <select name="role" class="form-control">
              <?php 
              $curr_role_add = $this->session->userdata(SESS_HEAD . '_role');
              $add_roles = ($curr_role_add === 'admin') ? USER_ROLE_OPT : get_assignable_roles();
              foreach ($add_roles as $k => $v): ?>
              <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Department <span class="text-danger">*</span></label>
            <select name="department_id" class="form-control select2" style="width: 100%;" required>
              <option value="">Select Department</option>
              <?php if(isset($departments)): foreach($departments as $d): ?>
                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
              <?php endforeach; endif; ?>
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
              <?php 
              $curr_role_edit = $this->session->userdata(SESS_HEAD . '_role');
              $edit_roles = ($curr_role_edit === 'admin') ? USER_ROLE_OPT : get_assignable_roles();
              foreach ($edit_roles as $k => $v): ?>
              <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Department <span class="text-danger">*</span></label>
            <select name="department_id" id="edit_department_id" class="form-control select2" style="width: 100%;" required>
              <option value="">Select Department</option>
              <?php if(isset($departments)): foreach($departments as $d): ?>
                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
              <?php endforeach; endif; ?>
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

<script>
window.initialFilterSearch = '<?php echo $f_search ?: ""; ?>';
window.initialFilterRole = '<?php echo $f_role ?: ""; ?>';
window.initialFilterStatus = '<?php echo $f_status ?: ""; ?>';
</script>
<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

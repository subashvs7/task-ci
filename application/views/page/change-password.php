<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Change Password</h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Change Password</li>
  </ol>
</section>

<section class="content">
  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_error'); ?></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-lock"></i> Change Your Password</h3>
        </div>
        <form method="post" action="<?php echo site_url('change-password') ?>">
          <input type="hidden" name="mode" value="ChangePassword">
          <div class="box-body">
            <div class="form-group">
              <label>Current Password <span class="text-danger">*</span></label>
              <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
            </div>
            <div class="form-group">
              <label>New Password <span class="text-danger">*</span></label>
              <input type="password" name="new_password" id="new_password" class="form-control" required placeholder="Min 6 characters">
            </div>
            <div class="form-group">
              <label>Confirm New Password <span class="text-danger">*</span></label>
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Repeat new password">
            </div>
          </div>
          <div class="box-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Password</button>
            <a href="<?php echo site_url('dash') ?>" class="btn btn-default pull-right">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

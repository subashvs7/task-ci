<aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel">
      <div class="pull-left image">
        <img src="<?php echo base_url() ?>asset/images/user.jpg" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?php echo strtoupper($this->session->userdata(SESS_HEAD . '_user_name')); ?>
          <br><i><?php echo strtoupper($this->session->userdata(SESS_HEAD . '_role')); ?></i>
        </p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <ul class="sidebar-menu" data-widget="tree">
      <?php include_once('admin-menu.php'); ?>
      <li><a href="<?php echo site_url('change-password') ?>"><i class="fa fa-lock"></i> <span>Change Password</span></a></li>
      <li><a href="<?php echo site_url('logout') ?>"><i class="fa fa-sign-out"></i> <span>Logout</span></a></li>
    </ul>
  </section>
</aside>

<?php
$seg1    = $this->uri->segment(1, 0);
$role    = $this->session->userdata(SESS_HEAD . '_role');
$isAdmin = ($role === 'admin');
?>
<li class="header">MAIN NAVIGATION</li>

<?php if (has_menu_permission('dashboard')): ?>
<!-- Dashboard -->
<li <?php if ($seg1 === 'dash') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('dash') ?>">
    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
  </a>
</li>
<?php endif; ?>

<?php if (has_menu_permission('projects')): ?>
<!-- Projects -->
<li class="treeview <?php if (in_array($seg1, array('project-list','project-kanban','project-detail'))) echo 'active'; ?>">
  <a href="#">
    <i class="fa fa-folder-open"></i> <span>Projects</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu">
    <?php if (has_menu_permission('project_list')): ?>
    <li <?php if ($seg1 === 'project-list') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('project-list') ?>"><i class="fa fa-list"></i> Project List</a>
    </li>
    <?php endif; ?>
    <?php if (has_menu_permission('project_kanban')): ?>
    <li <?php if ($seg1 === 'project-kanban') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('project-kanban') ?>"><i class="fa fa-columns"></i> Project Kanban</a>
    </li>
    <?php endif; ?>

  </ul>
</li>
<?php endif; ?>

<?php if (has_menu_permission('tasks')): ?>
<!-- Tasks -->
<li class="treeview <?php if (in_array($seg1, array('task-list','task-kanban','task-detail'))) echo 'active'; ?>">
  <a href="#">
    <i class="fa fa-tasks"></i> <span>Tasks</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu">
    <?php if (has_menu_permission('task_list')): ?>
    <li <?php if ($seg1 === 'task-list') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('task-list') ?>"><i class="fa fa-list"></i> Task List</a>
    </li>
    <?php endif; ?>
    <?php if (has_menu_permission('task_kanban')): ?>
    <li <?php if ($seg1 === 'task-kanban') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('task-kanban') ?>"><i class="fa fa-columns"></i> Kanban Board</a>
    </li>
    <?php endif; ?>
  </ul>
</li>
<?php endif; ?>

<?php if (has_menu_permission('epics')): ?>
<!-- Epics -->
<li <?php if ($seg1 === 'epic-list') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('epic-list') ?>">
    <i class="fa fa-bolt"></i> <span>Epics</span>
  </a>
</li>
<?php endif; ?>

<?php if (has_menu_permission('stories')): ?>
<!-- User Stories -->
<li <?php if ($seg1 === 'story-list') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('story-list') ?>">
    <i class="fa fa-book"></i> <span>User Stories</span>
  </a>
</li>
<?php endif; ?>

<?php if (has_menu_permission('reports')): ?>
<!-- Reports -->
<li <?php if (in_array($seg1, array('task-report','project-report','feasibility-analysis'))) echo 'class="active"'; ?>>
  <a href="<?php echo site_url('task-report') ?>">
    <i class="fa fa-bar-chart"></i> <span>Report</span>
  </a>
</li>
<?php endif; ?>

<!-- Password Manager -->
<li class="treeview <?php if (in_array($seg1, array('password-manager','pm-categories'))) echo 'active'; ?>">
  <a href="#">
    <i class="fa fa-lock" style="color:#a569bd;"></i> <span>Password Manager</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
    <span class="pull-right-container" style="margin-right:18px;"><span class="label pull-right" style="background:linear-gradient(135deg,#8e44ad,#6c3483);font-size:9px;border-radius:10px;padding:2px 7px;">NEW</span></span>
  </a>
  <ul class="treeview-menu">
    <li <?php if ($seg1 === 'password-manager') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('password-manager') ?>"><i class="fa fa-key"></i> All Passwords</a>
    </li>
    <li <?php if ($seg1 === 'pm-categories') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('pm-categories') ?>"><i class="fa fa-tags"></i> Manage Categories</a>
    </li>
    <li>
      <a href="<?php echo site_url('password-manager/form') ?>"><i class="fa fa-plus"></i> Add Password</a>
    </li>
  </ul>
</li>

<?php if ($isAdmin): ?>
<!-- Contact Book -->
<li <?php if ($seg1 === 'contact-book') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('contact-book') ?>">
    <i class="fa fa-address-book" style="color:#e67e22;"></i> <span>Contact Book</span>
    <span class="pull-right-container"><span class="label pull-right" style="background:#e67e22;font-size:9px;border-radius:10px;padding:2px 7px;">Admin</span></span>
  </a>
</li>
<?php endif; ?>


<?php if (has_menu_permission('users') || has_menu_permission('role_permissions')): ?>
<!-- Administration -->
<li class="header">ADMINISTRATION</li>
<?php if (has_menu_permission('users')): ?>
<li <?php if ($seg1 === 'user-list' && $this->input->get('action') !== 'add') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('user-list') ?>">
    <i class="fa fa-users"></i> <span>User Management</span>
    <?php
    $ucount = $this->db->query("SELECT COUNT(*) as c FROM tm_users WHERE status='Active'")->row_array();
    ?>
    <span class="pull-right-container"><span class="label label-primary pull-right"><?php echo (int)$ucount['c']; ?></span></span>
  </a>
</li>
<?php endif; ?>
<?php if (has_menu_permission('role_permissions')): ?>
<li <?php if ($seg1 === 'role-permission') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('role-permission') ?>">
    <i class="fa fa-key"></i> <span>Role Permissions</span>
  </a>
</li>
<?php endif; ?>
<?php endif; ?>

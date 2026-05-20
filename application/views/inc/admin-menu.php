<?php
$seg1    = $this->uri->segment(1, 0);
$role    = $this->session->userdata(SESS_HEAD . '_role');
$isAdmin = ($role === 'admin');
?>
<li class="header">MAIN NAVIGATION</li>

<!-- Dashboard -->
<li <?php if ($seg1 === 'dash') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('dash') ?>">
    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
  </a>
</li>

<!-- Projects -->
<li class="treeview <?php if (in_array($seg1, array('project-list','project-kanban','project-detail'))) echo 'active'; ?>">
  <a href="#">
    <i class="fa fa-folder-open"></i> <span>Projects</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu">
    <li <?php if ($seg1 === 'project-list') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('project-list') ?>"><i class="fa fa-list"></i> Project List</a>
    </li>
    <li <?php if ($seg1 === 'project-kanban') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('project-kanban') ?>"><i class="fa fa-columns"></i> Project Kanban</a>
    </li>
  </ul>
</li>

<!-- Tasks -->
<li class="treeview <?php if (in_array($seg1, array('task-list','task-kanban','task-detail'))) echo 'active'; ?>">
  <a href="#">
    <i class="fa fa-tasks"></i> <span>Tasks</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu">
    <li <?php if ($seg1 === 'task-list') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('task-list') ?>"><i class="fa fa-list"></i> Task List</a>
    </li>
    <li <?php if ($seg1 === 'task-kanban') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('task-kanban') ?>"><i class="fa fa-columns"></i> Kanban Board</a>
    </li>
  </ul>
</li>

<!-- Epics -->
<li <?php if ($seg1 === 'epic-list') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('epic-list') ?>">
    <i class="fa fa-bolt"></i> <span>Epics</span>
  </a>
</li>

<!-- User Stories -->
<li <?php if ($seg1 === 'story-list') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('story-list') ?>">
    <i class="fa fa-book"></i> <span>User Stories</span>
  </a>
</li>

<!-- Reports -->
<li class="treeview <?php if (in_array($seg1, array('task-report','project-report'))) echo 'active'; ?>">
  <a href="#">
    <i class="fa fa-bar-chart"></i> <span>Reports</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu">
    <li <?php if ($seg1 === 'task-report') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('task-report') ?>"><i class="fa fa-file-text"></i> Task Report</a>
    </li>
    <li <?php if ($seg1 === 'project-report') echo 'class="active"'; ?>>
      <a href="<?php echo site_url('project-report') ?>"><i class="fa fa-file-text"></i> Project Report</a>
    </li>
  </ul>
</li>

<?php if ($isAdmin): ?>
<!-- Administration — Admin only -->
<li class="header">ADMINISTRATION</li>
<li <?php if ($seg1 === 'user-list') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('user-list') ?>">
    <i class="fa fa-users"></i> <span>User Management</span>
    <?php
    $ucount = $this->db->query("SELECT COUNT(*) as c FROM tm_users WHERE status='Active'")->row_array();
    ?>
    <span class="pull-right-container"><span class="label label-primary pull-right"><?php echo (int)$ucount['c']; ?></span></span>
  </a>
</li>
<li <?php if ($this->input->get('action') === 'add' && $seg1 === 'user-list') echo 'class="active"'; ?>>
  <a href="<?php echo site_url('user-list') ?>?action=add">
    <i class="fa fa-user-plus"></i> <span>Add New User</span>
  </a>
</li>
<?php endif; ?>

<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Project Kanban <small>Visual overview of all projects</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('project-list') ?>">Projects</a></li>
    <li class="active">Kanban</li>
  </ol>
</section>

<section class="content">
  <?php
  $status_groups = array('planning'=>array(),'active'=>array(),'on_hold'=>array(),'completed'=>array(),'cancelled'=>array());
  foreach ($projects as $p) {
      $s = $p['status'];
      if (!isset($status_groups[$s])) $status_groups[$s] = array();
      $status_groups[$s][] = $p;
  }
  $col_colors = array('planning'=>'#9b59b6','active'=>'#27ae60','on_hold'=>'#e67e22','completed'=>'#3498db','cancelled'=>'#c0392b');
  ?>

  <div class="kanban-board">
    <?php foreach (PROJECT_STATUS_OPT as $status => $label):
      $col_projects = isset($status_groups[$status]) ? $status_groups[$status] : array();
      $ccol = isset($col_colors[$status]) ? $col_colors[$status] : '#95a5a6';
    ?>
    <div class="kanban-col">
      <div class="kanban-col-header" style="background:<?php echo $ccol; ?>;">
        <?php echo $label; ?> <span class="badge" style="background:rgba(255,255,255,0.3);"><?php echo count($col_projects); ?></span>
      </div>
      <div class="kanban-body">
        <?php if (empty($col_projects)): ?>
          <p class="text-center text-muted" style="font-size:12px; padding-top:20px;">No projects</p>
        <?php else: ?>
        <?php foreach ($col_projects as $p):
          $ptask = (int)$p['task_count'];
          $pdone = (int)$p['done_count'];
          $ppct  = $ptask > 0 ? round(($pdone/$ptask)*100) : 0;
        ?>
        <div class="kanban-card project-link-modal" data-id="<?php echo $p['project_id']; ?>" style="cursor:pointer;">
          <div style="border-left:4px solid <?php echo htmlspecialchars($p['color'] ?: $ccol); ?>; padding-left:8px;">
            <div class="kanban-card-title"><?php echo htmlspecialchars($p['name']); ?></div>
            <?php if ($p['key_name']): ?><div class="kanban-card-meta"><code><?php echo htmlspecialchars($p['key_name']); ?></code></div><?php endif; ?>
          </div>
          <div class="kanban-card-meta" style="margin-top:6px;">
            <span class="badge badge-priority-<?php echo $p['priority']; ?>"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$p['priority']])?$pl[$p['priority']]:$p['priority']; ?></span>
            &nbsp; <i class="fa fa-tasks"></i> <?php echo $ptask; ?> tasks
          </div>
          <?php if ($ptask > 0): ?>
          <div style="margin-top:8px;">
            <div class="progress progress-xs" style="margin-bottom:0;">
              <div class="progress-bar bg-green" style="width:<?php echo $ppct; ?>%;"></div>
            </div>
            <small style="font-size:10px; color:#888;"><?php echo $ppct; ?>% complete</small>
          </div>
          <?php endif; ?>
          <?php if ($p['owner_name']): ?>
          <div class="kanban-card-meta" style="margin-top:4px;"><i class="fa fa-user"></i> <?php echo htmlspecialchars($p['owner_name']); ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

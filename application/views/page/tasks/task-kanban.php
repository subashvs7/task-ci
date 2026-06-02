<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Kanban Board</h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('task-list') ?>">Tasks</a></li>
    <li class="active">Kanban</li>
  </ol>
</section>

<section class="content">
  <!-- Filters -->
  <div class="box box-default" style="margin-bottom:10px;">
    <div class="box-body" style="padding:10px 15px;">
      <form method="get" action="<?php echo site_url('task-kanban') ?>" class="form-inline" id="kanbanFilterForm">
        <div class="form-group" style="margin-right:10px;">
          <label style="margin-right:5px;">Project</label>
          <select name="project_id" class="form-control input-sm select2" style="min-width:200px;">
            <option value="">All Projects</option>
            <?php foreach ($projects_list as $p): ?>
            <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-right:10px;">
          <label style="margin-right:5px;">Assignee</label>
          <select name="assigned_to" class="form-control input-sm select2" style="min-width:160px;">
            <option value="">All Users</option>
            <?php foreach ($users_list as $u): ?>
            <option value="<?php echo $u['user_id']; ?>" <?php echo ($f_assigned==$u['user_id'])?'selected':''; ?>><?php echo htmlspecialchars($u['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
        <a href="<?php echo site_url('task-kanban') ?>" class="btn btn-default btn-sm"><i class="fa fa-times"></i> Clear</a>
        <a href="<?php echo site_url('task-list') ?>" class="btn btn-default btn-sm pull-right"><i class="fa fa-list"></i> List View</a>
      </form>
    </div>
  </div>

  <?php
  $cur_role = $this->session->userdata(SESS_HEAD . '_role');
  $cur_uid  = $this->session->userdata(SESS_HEAD . '_user_id');
  
  $col_colors = array(
    'backlog'     => array('color'=>'#95a5a6', 'label'=>'Backlog'),
    'todo'        => array('color'=>'#3498db', 'label'=>'To Do'),
    'in_progress' => array('color'=>'#e67e22', 'label'=>'In Progress'),
    'in_review'   => array('color'=>'#9b59b6', 'label'=>'In Review'),
    'done'        => array('color'=>'#27ae60', 'label'=>'Done'),
    'closed'      => array('color'=>'#7f8c8d', 'label'=>'Closed'),
  );
  ?>

  <div class="kanban-board">
    <?php foreach ($col_colors as $status => $colInfo):
      $tasks_in_col = isset($columns[$status]) ? $columns[$status] : array();
    ?>
    <div class="kanban-col">
      <div class="kanban-col-header" style="background:<?php echo $colInfo['color']; ?>;">
        <?php echo $colInfo['label']; ?> <span class="badge" style="background:rgba(255,255,255,0.3);"><?php echo count($tasks_in_col); ?></span>
      </div>
      <div class="kanban-body" id="col-<?php echo $status; ?>" data-status="<?php echo $status; ?>">
        <?php if (empty($tasks_in_col)): ?>
          <p class="text-center text-muted" style="font-size:12px; padding-top:20px;">No tasks</p>
        <?php else: ?>
        <?php foreach ($tasks_in_col as $t):
          $is_overdue = !empty($t['due_date']) && strtotime($t['due_date']) < time() && !in_array($t['status'], array('done','closed'));
          $logged_h = (float)$t['logged_hours'];
          $estimated_h = !empty($t['estimated_hours']) ? (float)$t['estimated_hours'] : 0;
          $is_effort_overdue = $estimated_h > 0 && $logged_h > $estimated_h;
        ?>
        <div class="kanban-card" onclick="window.location='<?php echo site_url('task-detail/' . $t['task_id']); ?>'">
          <div style="border-left:3px solid <?php
            $pc = array('low'=>'#27ae60','medium'=>'#3498db','high'=>'#e67e22','critical'=>'#c0392b');
            echo isset($pc[$t['priority']]) ? $pc[$t['priority']] : '#ccc';
          ?>; padding-left:7px;">
            <div class="kanban-card-title"><?php echo htmlspecialchars(mb_substr($t['title'], 0, 50)); ?><?php echo strlen($t['title'])>50?'...':''; ?></div>
          </div>
          <div class="kanban-card-meta" style="margin-top:6px;">
            <span class="badge badge-type-<?php echo $t['type']; ?>" style="font-size:10px;"><?php $tl=TASK_TYPE_OPT; echo isset($tl[$t['type']])?$tl[$t['type']]:$t['type']; ?></span>
            <span class="badge badge-priority-<?php echo $t['priority']; ?>" style="font-size:10px; margin-left:3px;"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$t['priority']])?$pl[$t['priority']]:$t['priority']; ?></span>
            <?php if ($is_overdue): ?><span class="badge" style="background:#c0392b; font-size:10px;"><i class="fa fa-exclamation-triangle"></i> Overdue</span><?php endif; ?>
            <?php if ($is_effort_overdue): ?><span class="badge" style="background:#e74c3c; font-size:10px; margin-left:3px;"><i class="fa fa-warning"></i> <?php echo round($logged_h, 2); ?>h/<?php echo round($estimated_h, 2); ?>h</span><?php endif; ?>
          </div>
          <?php if ($t['assignee_name']): ?>
          <div class="kanban-card-meta" style="margin-top:4px;"><i class="fa fa-user"></i> <?php echo htmlspecialchars($t['assignee_name']); ?></div>
          <?php endif; ?>
          <?php if (!empty($t['reporter_name'])): ?>
          <div class="kanban-card-meta" style="margin-top:2px; font-size:10px; color:#777;"><i class="fa fa-user-circle"></i> Assigned By: <?php echo htmlspecialchars($t['reporter_name']); ?></div>
          <?php endif; ?>
          <div class="kanban-card-meta" style="margin-top:3px;">
            <i class="fa fa-folder-open" style="font-size:10px;"></i> 
            <small>
              <?php if ($t['project_id']): ?>
                <a href="#" class="project-link-modal" data-id="<?php echo $t['project_id']; ?>" onclick="event.stopPropagation();"><?php echo htmlspecialchars($t['project_name']); ?></a>
                <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $t['project_id']; ?>" onclick="event.stopPropagation();" style="padding: 0px 2px; border-radius: 2px; font-size: 8px; vertical-align: middle; margin-left: 2px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
              <?php else: ?>
                -
              <?php endif; ?>
            </small>
            <?php if ($t['subtask_count'] > 0): ?>&nbsp;<i class="fa fa-sitemap" style="font-size:10px;"></i> <?php echo $t['subtask_count']; ?><?php endif; ?>
            <?php if ($t['comment_count'] > 0): ?>&nbsp;<i class="fa fa-comments" style="font-size:10px;"></i> <?php echo $t['comment_count']; ?><?php endif; ?>
            <?php if ($t['due_date']): ?>&nbsp;<i class="fa fa-calendar" style="font-size:10px;"></i> <?php echo date('d-M', strtotime($t['due_date'])); ?><?php endif; ?>
          </div>
          <?php if ($t['completion_percentage'] > 0): ?>
          <div style="margin-top:6px;">
            <div class="progress progress-xs" style="margin-bottom:0;">
              <div class="progress-bar bg-green" style="width:<?php echo $t['completion_percentage']; ?>%;"></div>
            </div>
          </div>
          <?php endif; ?>
          <div style="text-align:right; margin-top:4px;">
            <?php if (in_array($cur_role, ['admin', 'manager', 'team_leader']) || $t['created_by'] == $cur_uid): ?>
            <button class="btn btn-xs btn-default kanban-status-btn" data-id="<?php echo $t['task_id']; ?>" onclick="event.stopPropagation();" title="Move"><i class="fa fa-arrows-h"></i></button>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Move Task Status Modal -->
<div class="modal fade" id="moveTaskModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background:#2c3e50; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
        <h4 class="modal-title">Move Task to...</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="move_task_id">
        <?php foreach ($col_colors as $status => $colInfo): ?>
        <button class="btn btn-block move-status-btn" data-status="<?php echo $status; ?>"
          style="background:<?php echo $colInfo['color']; ?>; color:#fff; margin-bottom:5px;">
          <?php echo $colInfo['label']; ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

<script>
$(document).ready(function() {
    $('#kanbanFilterForm select').on('change', function() {
        $('#kanbanFilterForm')[0].submit();
    });
});
</script>

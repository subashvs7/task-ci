<?php include_once(VIEWPATH . 'inc/header.php'); 
function format_hours($decimal_hours) {
    if (!$decimal_hours || $decimal_hours <= 0) return '-';
    $h = floor($decimal_hours);
    $m = round(($decimal_hours - $h) * 60);
    $parts = [];
    if ($h > 0) $parts[] = $h.'h';
    if ($m > 0) $parts[] = $m.'m';
    return empty($parts) ? '0m' : implode(' ', $parts);
}
?>

<section class="content-header">
  <h1>Epics <small><?php echo $total_records; ?> total</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Epics</li>
  </ol>
</section>

<section class="content">

  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="box box-default collapsed-box">
    <div class="box-header with-border" data-widget="collapse" style="cursor:pointer;">
      <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
      <div class="box-tools pull-right"><button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
    </div>
    <div class="box-body">
      <form method="get" action="<?php echo site_url($s_url) ?>">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group"><label>Project</label>
              <select name="project_id" class="form-control select2">
                <option value="">All Projects</option>
                <?php foreach ($projects_list as $p): ?>
                <option value="<?php echo $p['project_id']; ?>" <?php echo ($f_project==$p['project_id'])?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group"><label>Status</label>
              <select name="f_status" class="form-control">
                <option value="">All Status</option>
                <?php foreach (array('open'=>'Open','in_progress'=>'In Progress','done'=>'Done','closed'=>'Closed') as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
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
      <h3 class="box-title"><i class="fa fa-bolt"></i> Epic List</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addEpicModal"><i class="fa fa-plus"></i> Add Epic</button>
      </div>
    </div>
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr><th>#</th><th>Epic Name</th><th>Project</th><th>Status</th><th>Priority</th><th>Est. Time</th><th>Stories</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($record_list)): ?>
          <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No epics found.</td></tr>
          <?php else: ?>
          <?php foreach ($record_list as $j => $e): ?>
          <tr>
            <td><?php echo $sno + $j + 1; ?></td>
            <td>
              <span style="display:inline-block; width:12px; height:12px; background:<?php echo htmlspecialchars($e['color'] ?: '#9b59b6'); ?>; border-radius:50%; margin-right:6px;"></span>
              <strong><?php echo htmlspecialchars($e['name']); ?></strong>
              <?php if ($e['description']): ?><br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($e['description'],0,50)); ?></small><?php endif; ?>
            </td>
            <td>
              <?php if ($e['project_id']): ?>
                <a href="#" class="project-link-modal" data-id="<?php echo $e['project_id']; ?>"><?php echo htmlspecialchars($e['project_name']); ?></a>
                <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $e['project_id']; ?>" style="padding: 1px 3px; border-radius: 3px; font-size: 9px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td>
              <?php $sc=array('open'=>'default','in_progress'=>'success','done'=>'success','closed'=>'danger'); ?>
              <span class="label label-<?php echo isset($sc[$e['status']])?$sc[$e['status']]:'default'; ?>" style="<?php echo $e['status']=='in_progress' ? 'background-color:#10b981 !important;' : ''; ?>">
                <?php echo $e['status'] === 'in_progress' ? 'Working' : ucfirst(str_replace('_',' ',$e['status'])); ?>
              </span>
            </td>
            <td><span class="badge badge-priority-<?php echo $e['priority']; ?>"><?php $pl=TASK_PRIORITY_OPT; echo isset($pl[$e['priority']])?$pl[$e['priority']]:$e['priority']; ?></span></td>
            <td><span class="badge bg-purple"><?php echo format_hours($e['estimated_time'] ? ($e['estimated_time']/60) : 0); ?></span></td>
            <td><?php echo $e['story_count']; ?> stories</td>
            <td>
              <button class="btn btn-xs btn-warning btn-edit-epic"
                data-id="<?php echo $e['epic_id']; ?>"
                data-project="<?php echo $e['project_id']; ?>"
                data-name="<?php echo htmlspecialchars($e['name'], ENT_QUOTES); ?>"
                data-description="<?php echo htmlspecialchars($e['description'], ENT_QUOTES); ?>"
                data-status="<?php echo $e['status']; ?>"
                data-priority="<?php echo $e['priority']; ?>"
                data-color="<?php echo htmlspecialchars($e['color'], ENT_QUOTES); ?>"
                data-eh="<?php echo $e['estimated_time'] ? floor($e['estimated_time'] / 60) : 0; ?>"
                data-em="<?php echo $e['estimated_time'] ? ($e['estimated_time'] % 60) : 0; ?>">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="btn btn-xs btn-danger del_record" value="<?php echo $e['epic_id']; ?>" data-tbl="tm_epics" data-col="epic_id"><i class="fa fa-trash"></i></button>
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

<!-- Add Epic Modal -->
<div class="modal fade" id="addEpicModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#9b59b6; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add Epic</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project <span class="text-danger">*</span></label>
            <select name="project_id" class="form-control select2" required>
              <option value="">-- Select Project --</option>
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Epic Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="Enter epic name">
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group"><label>Status</label>
                <select name="status" class="form-control">
                  <?php foreach (array('open'=>'Open','in_progress'=>'Working','done'=>'Done','closed'=>'Closed') as $k=>$v): ?>
                  <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Priority</label>
                <select name="priority" class="form-control">
                  <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?>
                  <option value="<?php echo $k; ?>" <?php echo ($k=='medium')?'selected':''; ?>><?php echo $v; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group"><label>Color</label>
                <input type="color" name="color" class="form-control" value="#9b59b6" style="height:38px; padding:2px;">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group"><label>Estimate (Hrs) <span class="text-danger">*</span></label>
                <input type="number" name="est_hours" class="form-control" min="0" required placeholder="Hours">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>Estimate (Min) <span class="text-danger">*</span></label>
                <input type="number" name="est_minutes" class="form-control" min="0" max="59" required placeholder="Minutes" value="0">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Epic</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Epic Modal -->
<div class="modal fade" id="editEpicModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="epic_id" id="edit_epic_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Epic</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project</label>
            <select name="project_id" id="edit_epic_project" class="form-control select2">
              <?php foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_epic_name" class="form-control" required>
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" id="edit_epic_desc" class="form-control" rows="2"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4"><div class="form-group"><label>Status</label>
              <select name="status" id="edit_epic_status" class="form-control">
                <?php foreach (array('open'=>'Open','in_progress'=>'In Progress','done'=>'Done','closed'=>'Closed') as $k=>$v): ?>
                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-4"><div class="form-group"><label>Priority</label>
              <select name="priority" id="edit_epic_priority" class="form-control">
                <?php foreach (TASK_PRIORITY_OPT as $k=>$v): ?>
                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-md-4"><div class="form-group"><label>Color</label>
              <input type="color" name="color" id="edit_epic_color" class="form-control" style="height:38px; padding:2px;">
            </div></div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group"><label>Estimate (Hrs) <span class="text-danger">*</span></label>
                <input type="number" name="est_hours" id="edit_epic_eh" class="form-control" min="0" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>Estimate (Min) <span class="text-danger">*</span></label>
                <input type="number" name="est_minutes" id="edit_epic_em" class="form-control" min="0" max="59" required>
              </div>
            </div>
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

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
              <select name="project_id" id="filter_epic_project" class="form-control select2">
                <option value="">All Projects</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group"><label>Status</label>
              <select name="f_status" id="filter_epic_status" class="form-control select2">
                <option value="">All Status</option>
                <?php foreach (array('open'=>'Open','in_progress'=>'Working','done'=>'Done','closed'=>'Closed') as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo ($f_status==$k)?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-2" style="padding-top:25px;">
            <button type="button" id="btn_reset_filters" class="btn btn-default btn-block"><i class="fa fa-refresh"></i> Reset</button>
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
          <tr><th>#</th><th>Epic Name</th><th>Project</th><th>Document</th><th>Status / Priority</th><th>Est. Time</th><th>Stories</th><th>Actions</th></tr>
        </thead>
        <tbody id="epic_list_tbody">
          <?php include('epic-list-rows.php'); ?>
        </tbody>
      </table>
    </div>
    <div class="box-footer clearfix"><?php echo $pagination; ?></div>
  </div>
</section>

<!-- Add Epic Modal -->
<div class="modal fade" id="addEpicModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="Add">
        <div class="modal-header" style="background:#9b59b6; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Add Epic</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project <span class="text-danger">*</span></label>
            <select name="project_id" id="add_epic_project" class="form-control select2" required>
              <option value="">-- Select Project --</option>
            </select>
          </div>
          <div class="form-group"><label>Epic Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="Enter epic name">
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group"><label>Upload Document (PDF/Image)</label>
            <input type="file" name="document" class="form-control" accept="application/pdf,image/*">
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
<div class="modal fade" id="editEpicModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url($s_url) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="Edit">
        <input type="hidden" name="epic_id" id="edit_epic_id">
        <div class="modal-header" style="background:#e67e22; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Epic</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project</label>
            <select name="project_id" id="edit_epic_project" class="form-control select2">
              <option value="">-- Select Project --</option>
            </select>
          </div>
          <div class="form-group"><label>Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_epic_name" class="form-control" required>
          </div>
          <div class="form-group"><label>Description</label>
            <textarea name="description" id="edit_epic_desc" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group"><label>Upload Document (PDF/Image)</label>
            <input type="file" name="document" class="form-control" accept="application/pdf,image/*">
            <small class="text-muted">Uploading a new document will add a new version to the existing documents.</small>
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

<script>
window.initialFilterProject = '<?php echo $f_project ?: ""; ?>';
window.initialFilterStatus = '<?php echo $f_status ?: ""; ?>';
</script>
<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

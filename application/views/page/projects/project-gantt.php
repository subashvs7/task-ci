<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Project Roadmap <small>Gantt Chart View</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('project-list') ?>">Projects</a></li>
    <li class="active">Roadmap</li>
  </ol>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-map"></i> Project Roadmap</h3>
          <div class="box-tools pull-right" style="width: 300px;">
            <select id="project_selector" class="form-control select2">
              <option value="">-- Select a Project to View --</option>
              <?php foreach($projects as $p): ?>
                <option value="<?php echo $p['project_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="box-body" style="padding: 0;">
          <div id="gantt_here" style="width:100%; height:600px; display: none;"></div>
          <div id="no_project_msg" class="text-center text-muted" style="padding: 50px;">
            <i class="fa fa-hand-pointer-o fa-3x" style="margin-bottom: 15px;"></i>
            <p>Please select a project from the dropdown to view its roadmap.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

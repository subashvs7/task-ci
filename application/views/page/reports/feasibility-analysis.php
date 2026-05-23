<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header" style="padding: 20px 15px;">
  <h1 style="font-weight: 700; color: #2c3e50;">Capacity & Feasibility Dashboard <small>Resource Working-Load Index</small></h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-primary" style="border-radius: 8px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
        <div class="box-header with-border" style="background: #fff; padding: 15px 20px;">
          <h3 class="box-title" style="font-weight: 600; color: #2c3e50;"><i class="fa fa-balance-scale"></i> Real-time Staff Load Analysis</h3>
        </div>
        <div class="box-body no-padding">
          <div class="table-responsive">
            <table class="table table-hover table-striped" style="margin-bottom: 0; vertical-align: middle;">
              <thead>
                <tr style="background: #f8f9fa; color: #34495e;">
                  <th style="padding: 15px 20px;">Staff Member</th>
                  <th class="text-center">Active Tasks</th>
                  <th class="text-center">Earliest Deadline</th>
                  <th class="text-center">Workdays Remaining</th>
                  <th class="text-center">Effort Needed</th>
                  <th class="text-center">Working Capacity</th>
                  <th style="width: 30%; padding-right: 20px;">Feasibility Index & Risk Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($analysis_data as $row): 
                  $idx = $row['feasibility_index'];
                  if ($idx == 0) {
                      $status = 'FEASIBLE'; $lbl = 'success'; $bar = '#2ecc71';
                  } elseif ($idx <= 80) {
                      $status = 'FEASIBLE'; $lbl = 'success'; $bar = '#2ecc71';
                  } elseif ($idx <= 100) {
                      $status = 'TENSION'; $lbl = 'warning'; $bar = '#f1c40f';
                  } else {
                      $status = 'OVERLOADED'; $lbl = 'danger'; $bar = '#e74c3c';
                  }
                ?>
                <tr>
                  <td style="padding: 15px 20px;">
                    <div style="font-weight: 700; color: #2c3e50; font-size: 14px;"><i class="fa fa-user-circle text-muted"></i> &nbsp;<?= htmlspecialchars($row['staff']['name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($row['staff']['email']) ?></small>
                  </td>
                  <td class="text-center" style="vertical-align: middle;">
                    <span class="badge bg-blue" style="font-size: 11px; padding: 5px 8px;"><?= $row['task_count'] ?> Tasks</span>
                    <?php if ($row['overdue_count'] > 0): ?>
                      <span class="badge bg-red" style="font-size: 11px; padding: 5px 8px;"><i class="fa fa-warning"></i> <?= $row['overdue_count'] ?> Overdue</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center" style="vertical-align: middle; font-weight: 500;">
                    <?= $row['earliest_due'] ? date('d M Y', strtotime($row['earliest_due'])) : '<span class="text-muted">-</span>' ?>
                  </td>
                  <td class="text-center" style="vertical-align: middle; font-weight: 600; color: #34495e;">
                    <?= $row['working_days'] ?> Days
                  </td>
                  <td class="text-center" style="vertical-align: middle; font-size: 14px; font-weight: 700; color: #2c3e50;">
                    <?= $row['remaining_hours'] ?> Hrs
                  </td>
                  <td class="text-center" style="vertical-align: middle; color: #7f8c8d;">
                    <?= $row['capacity_hours'] ?> Hrs
                  </td>
                  <td style="vertical-align: middle; padding-right: 20px;">
                    <div style="margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                      <span class="label label-<?= $lbl ?>" style="font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px; font-size: 9px;"><?= $status ?></span>
                      <strong class="text-<?= $lbl ?>" style="font-size: 13px;"><?= $idx === 999 ? 'Infinite Overload' : $idx . '%' ?></strong>
                    </div>
                    <div class="progress progress-xs" style="margin-bottom: 0; background: #eaedf1; border-radius: 4px; overflow: hidden; height: 6px;">
                      <div class="progress-bar" style="width: <?= min(100, $idx) ?>%; background-color: <?= $bar ?>;"></div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

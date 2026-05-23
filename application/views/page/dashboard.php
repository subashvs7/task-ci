<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Dashboard <small>Overview</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
  </ol>
</section>

<section class="content">
  <?php if ($this->session->flashdata('alert_success')): ?>
    <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('alert_error')): ?>
    <div class="alert alert-danger alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_error'); ?></div>
  <?php endif; ?>

  <!-- Stats Row 1 -->
  <div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-aqua"><i class="fa fa-folder-open"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Projects</span>
          <span class="info-box-number"><?php echo $total_projects; ?></span>
          <span class="info-box-text" style="font-size:11px;">Active: <?php echo $active_projects; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-green"><i class="fa fa-tasks"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Tasks</span>
          <span class="info-box-number"><?php echo $total_tasks; ?></span>
          <span class="info-box-text" style="font-size:11px;">Done: <?php echo $done_tasks; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-yellow"><i class="fa fa-user"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">My Tasks</span>
          <span class="info-box-number"><?php echo $my_tasks; ?></span>
          <span class="info-box-text" style="font-size:11px;">Open: <?php echo $my_open_tasks; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
      <div class="info-box">
        <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Overdue Tasks</span>
          <span class="info-box-number"><?php echo $overdue_tasks; ?></span>
          <span class="info-box-text" style="font-size:11px;">Need attention</span>
        </div>
      </div>
    </div>
  </div>

  <?php if ($this->session->userdata(SESS_HEAD . '_role') === 'admin'): ?>
  <div class="row">
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box bg-purple">
        <span class="info-box-icon"><i class="fa fa-briefcase"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Managers</span>
          <span class="info-box-number"><?php echo isset($admin_managers_count) ? $admin_managers_count : 0; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box bg-teal">
        <span class="info-box-icon"><i class="fa fa-users"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Team Leaders</span>
          <span class="info-box-number"><?php echo isset($admin_team_leaders_count) ? $admin_team_leaders_count : 0; ?></span>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
      <div class="info-box bg-maroon">
        <span class="info-box-icon"><i class="fa fa-user"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Staff</span>
          <span class="info-box-number"><?php echo isset($admin_staff_count) ? $admin_staff_count : 0; ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!is_null($manager_project_count)): ?>
  <!-- Manager: My Projects Banner -->
  <div class="row">
    <div class="col-md-12">
      <div style="
        background: linear-gradient(135deg, #1565c0 0%, #2980b9 50%, #1abc9c 100%);
        border-radius: 10px;
        padding: 22px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 6px 20px rgba(21,101,192,0.30);
        margin-bottom: 20px;
      ">
        <a href="<?php echo site_url('project-list') ?>" style="text-decoration:none; display:flex; align-items:center; gap:22px;">
          <div style="
            width:70px; height:70px;
            border-radius:50%;
            background:rgba(255,255,255,0.2);
            display:flex; align-items:center; justify-content:center;
            font-size:30px; color:#fff; flex-shrink:0;
          ">
            <i class="fa fa-briefcase"></i>
          </div>
          <div>
            <div style="font-size:13px; color:rgba(255,255,255,0.80); font-weight:600; letter-spacing:1px; text-transform:uppercase;">My Projects</div>
            <div style="font-size:48px; font-weight:900; color:#fff; line-height:1.1;">
              <?php echo $manager_project_count; ?>
            </div>
            <div style="font-size:13px; color:rgba(255,255,255,0.75); margin-top:3px;">
              <i class="fa fa-circle" style="color:#2ecc71; font-size:9px;"></i>
              &nbsp;<?php echo $manager_project_active; ?> currently active
            </div>
          </div>
        </a>
        <div style="text-align:right; display:flex; flex-direction:column; gap:8px; align-items:flex-end;">
          <div style="font-size:13px; color:rgba(255,255,255,0.75); margin-bottom:4px;">
            <i class="fa fa-folder-open"></i> &nbsp;Total assigned projects
          </div>
          <div style="display:flex; gap:8px;">
            <button type="button" data-toggle="modal" data-target="#addHandlerModal" style="
              display:inline-block;
              background:rgba(46, 204, 113, 0.8);
              color:#fff;
              padding:8px 15px;
              border-radius:20px;
              font-size:13px;
              font-weight:600;
              border: 1px solid rgba(255,255,255,0.35);
              outline:none;
              cursor:pointer;
            " onclick="event.preventDefault();"><i class="fa fa-plus"></i> Handle Project</button>
            
            <a href="<?php echo site_url('project-handle'); ?>" style="
              display:inline-block;
              background:rgba(255,255,255,0.22);
              color:#fff;
              padding:8px 15px;
              border-radius:20px;
              font-size:13px;
              font-weight:600;
              border: 1px solid rgba(255,255,255,0.35);
              text-decoration:none;
            "><i class="fa fa-eye"></i> View Handlers</a>
          </div>
          <a href="<?php echo site_url('project-list') ?>" style="
            display:inline-block;
            background:rgba(255,255,255,0.22);
            color:#fff;
            padding:8px 22px;
            border-radius:20px;
            font-size:13px;
            font-weight:600;
            border: 1px solid rgba(255,255,255,0.35);
            margin-top: 4px;
            text-decoration:none;
          ">View All Projects &nbsp;<i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($this->session->userdata(SESS_HEAD . '_role') === 'team_leader'): ?>
  <!-- Team Leader: Your Assigned Projects Dashboard -->
  <div class="row">
    <div class="col-md-12">
      <div class="box" style="border-top:3px solid #1abc9c; border-radius:10px; box-shadow:0 4px 20px rgba(26,188,156,0.15); margin-bottom:20px; overflow:hidden;">

        <!-- Panel Header -->
        <div style="background:linear-gradient(135deg,#0d7377 0%,#14a085 50%,#1abc9c 100%); padding:16px 22px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0;">
              <i class="fa fa-briefcase"></i>
            </div>
            <div>
              <div style="font-size:18px;font-weight:800;color:#fff;line-height:1.2;">
                Your Project Dashboard
                <?php if (!empty($tl_assigned_projects)): ?>
                <span style="background:rgba(255,255,255,0.25);color:#fff;border-radius:20px;padding:2px 12px;font-size:12px;font-weight:600;margin-left:8px;border:1px solid rgba(255,255,255,0.4);">
                  <?php echo count($tl_assigned_projects); ?> Project<?php echo count($tl_assigned_projects) > 1 ? 's' : ''; ?>
                </span>
                <?php endif; ?>
              </div>
              <div style="font-size:12px;color:rgba(255,255,255,0.85);margin-top:3px;">
                <i class="fa fa-info-circle" style="margin-right:4px;"></i>Projects assigned to you by your Manager
              </div>
            </div>
          </div>
        </div>

        <div class="box-body" style="padding:16px 16px 12px;background:#f4fbf9;">

          <?php if (empty($tl_assigned_projects)): ?>
          <!-- Empty state -->
          <div style="text-align:center;padding:50px 20px;color:#95a5a6;">
            <i class="fa fa-folder-open-o" style="font-size:48px;display:block;margin-bottom:14px;color:#b2dfdb;"></i>
            <div style="font-size:16px;font-weight:700;color:#7f8c8d;margin-bottom:5px;">No Projects Assigned Yet</div>
            <div style="font-size:13px;color:#95a5a6;">Your manager hasn't assigned any projects to you yet.</div>
          </div>

          <?php else:
          $sc = ['active'=>'#27ae60','planning'=>'#8e44ad','on_hold'=>'#e67e22','completed'=>'#2980b9','cancelled'=>'#c0392b'];
          // Group projects by manager
          $by_manager = [];
          foreach ($tl_assigned_projects as $tp) {
              $mk = $tp['manager_name'];
              if (!isset($by_manager[$mk])) $by_manager[$mk] = ['email'=>$tp['manager_email'],'projects'=>[]];
              $by_manager[$mk]['projects'][] = $tp;
          }
          $mgr_palette = ['#2980b9','#8e44ad','#16a085','#c0392b','#d35400','#27ae60','#2c3e50'];
          $mi = 0;
          foreach ($by_manager as $mgr_name => $mgr_data):
            $mc  = $mgr_palette[$mi % count($mgr_palette)];
            $ini = strtoupper(implode('', array_map(function($w){return $w[0];}, array_slice(explode(' ',$mgr_name),0,2))));
            $mi++;
            $g_total   = array_sum(array_column($mgr_data['projects'],'task_count'));
            $g_done    = array_sum(array_column($mgr_data['projects'],'done_count'));
            $g_overdue = array_sum(array_column($mgr_data['projects'],'overdue_count'));
            $g_pct     = $g_total > 0 ? round(($g_done/$g_total)*100) : 0;
          ?>

          <!-- Manager Group Card -->
          <div style="background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.07);margin-bottom:16px;overflow:hidden;border-left:5px solid <?php echo $mc; ?>;">

            <!-- Manager Header: responsive flex-wrap -->
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:14px 18px 12px;background:linear-gradient(90deg,<?php echo $mc; ?>12,#fff 70%);">

              <!-- Left: avatar + info -->
              <div style="display:flex;align-items:center;gap:13px;min-width:0;">
                <div style="width:48px;height:48px;border-radius:50%;background:<?php echo $mc; ?>;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;color:#fff;box-shadow:0 3px 8px <?php echo $mc; ?>44;flex-shrink:0;">
                  <?php echo $ini; ?>
                </div>
                <div style="min-width:0;">
                  <div style="font-size:10px;color:#1abc9c;text-transform:uppercase;letter-spacing:1.2px;font-weight:700;margin-bottom:1px;">
                    <i class="fa fa-user-circle-o" style="margin-right:3px;"></i>Handled By Manager
                  </div>
                  <div style="font-size:16px;font-weight:800;color:#1a2a4a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($mgr_name); ?></div>
                  <div style="font-size:11px;color:#7f8c8d;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i class="fa fa-envelope-o" style="margin-right:3px;color:#1abc9c;"></i><?php echo htmlspecialchars($mgr_data['email']); ?>
                  </div>
                </div>
              </div>

              <!-- Right: summary stat badges -->
              <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <!-- Projects -->
                <div style="text-align:center;background:#e8f8f5;border-radius:8px;padding:7px 14px;border:1px solid #b2dfdb;">
                  <div style="font-size:22px;font-weight:800;color:#16a085;line-height:1;"><?php echo count($mgr_data['projects']); ?></div>
                  <div style="font-size:10px;color:#7f8c8d;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px;">Projects</div>
                </div>
                <!-- Done Tasks -->
                <div style="text-align:center;background:#eafaf1;border-radius:8px;padding:7px 14px;border:1px solid #a9dfbf;">
                  <div style="font-size:22px;font-weight:800;color:#27ae60;line-height:1;">
                    <?php echo $g_done; ?><span style="font-size:13px;color:#bdc3c7;font-weight:600;">/<?php echo $g_total; ?></span>
                  </div>
                  <div style="font-size:10px;color:#7f8c8d;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px;">Done Tasks</div>
                </div>
                <!-- Overdue (only if > 0) -->
                <?php if ($g_overdue > 0): ?>
                <div style="text-align:center;background:#fdf2f2;border-radius:8px;padding:7px 14px;border:1px solid #f1a9a0;">
                  <div style="font-size:22px;font-weight:800;color:#e74c3c;line-height:1;"><?php echo $g_overdue; ?></div>
                  <div style="font-size:10px;color:#7f8c8d;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px;">Overdue</div>
                </div>
                <?php endif; ?>
                <!-- Overall progress -->
                <div style="text-align:center;min-width:68px;">
                  <div style="font-size:15px;font-weight:800;color:<?php echo $mc; ?>;line-height:1;margin-bottom:4px;"><?php echo $g_pct; ?>%</div>
                  <div style="height:7px;background:#e0f2f1;border-radius:4px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $g_pct; ?>%;background:linear-gradient(90deg,#1abc9c,<?php echo $mc; ?>);border-radius:4px;transition:width 1s ease;"></div>
                  </div>
                  <div style="font-size:10px;color:#7f8c8d;margin-top:3px;">Progress</div>
                </div>
              </div>
            </div>

            <!-- Responsive Projects Table -->
            <div style="overflow-x:auto;padding:0 4px 12px;">
              <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:560px;">
                <thead>
                  <tr style="background:#e8f8f5;border-bottom:2px solid #b2dfdb;">
                    <th style="padding:9px 10px;text-align:left;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;">Project</th>
                    <th style="padding:9px 10px;text-align:center;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;">Status</th>
                    <th style="padding:9px 10px;text-align:center;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;">Tasks</th>
                    <th style="padding:9px 10px;text-align:center;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;min-width:110px;">Progress</th>
                    <th style="padding:9px 10px;text-align:center;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;min-width:130px;">Duration</th>
                    <th style="padding:9px 10px;text-align:center;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;">Handler</th>
                    <th style="padding:9px 10px;text-align:left;color:#0d7377;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;font-size:11px;">Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($mgr_data['projects'] as $tp):
                    $tp_total  = (int)$tp['task_count'];
                    $tp_done   = (int)$tp['done_count'];
                    $tp_pct    = $tp_total > 0 ? round(($tp_done/$tp_total)*100) : 0;
                    $pc        = isset($sc[$tp['status']]) ? $sc[$tp['status']] : '#95a5a6';
                    $due_ts    = !empty($tp['due_date'])      ? strtotime($tp['due_date'])      : null;
                    $start_ts  = !empty($tp['assigned_date']) ? strtotime($tp['assigned_date']) : null;
                    $is_od     = $due_ts && $due_ts < time() && !in_array($tp['status'],['completed','cancelled']);
                    $hsc       = $tp['handler_status'] === 'active' ? '#27ae60' : '#e67e22';
                    $today_ts  = strtotime(date('Y-m-d'));
                    $days_left = $due_ts ? (int)round(($due_ts - $today_ts)/86400) : null;
                    $dur_days  = ($start_ts && $due_ts) ? max(0,(int)round(($due_ts-$start_ts)/86400)) : null;
                    $nf        = trim($tp['notes'] ?? '');
                    $ns        = mb_strlen($nf) > 50 ? mb_substr($nf,0,50).'…' : $nf;
                  ?>
                  <tr style="border-bottom:1px solid #e8f8f5;transition:background 0.12s;" onmouseover="this.style.background='#f0faf8'" onmouseout="this.style.background=''">

                    <!-- Project Name -->
                    <td style="padding:10px 10px;">
                      <a href="<?php echo site_url('project-detail/'.$tp['project_id']); ?>" style="font-weight:700;color:#1a2a4a;text-decoration:none;font-size:12px;">
                        <i class="fa fa-folder-o" style="margin-right:5px;color:<?php echo $pc; ?>;"></i><?php echo htmlspecialchars($tp['project_name']); ?>
                      </a>
                    </td>

                    <!-- Status pill -->
                    <td style="padding:10px 10px;text-align:center;">
                      <span style="background:<?php echo $pc; ?>18;color:<?php echo $pc; ?>;border:1px solid <?php echo $pc; ?>55;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;white-space:nowrap;">
                        <?php echo ucfirst(str_replace('_',' ',$tp['status'])); ?>
                      </span>
                    </td>

                    <!-- Tasks done/total -->
                    <td style="padding:10px 10px;text-align:center;font-size:13px;font-weight:700;">
                      <span style="color:#27ae60;"><?php echo $tp_done; ?></span><span style="color:#bdc3c7;">/<?php echo $tp_total; ?></span>
                    </td>

                    <!-- Progress bar -->
                    <td style="padding:10px 10px;text-align:center;">
                      <div style="display:flex;align-items:center;gap:6px;">
                        <div style="flex:1;height:8px;background:#e0f2f1;border-radius:4px;overflow:hidden;">
                          <div style="height:100%;width:<?php echo $tp_pct; ?>%;background:linear-gradient(90deg,#1abc9c,<?php echo $pc; ?>);border-radius:4px;"></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:<?php echo $pc; ?>;min-width:28px;"><?php echo $tp_pct; ?>%</span>
                      </div>
                    </td>

                    <!-- Duration: start → due date + days info -->
                    <td style="padding:10px 10px;text-align:center;">
                      <?php if ($start_ts && $due_ts): ?>
                        <div style="font-size:11px;line-height:1.7;">
                          <div style="color:#7f8c8d;font-size:10px;"><i class="fa fa-play-circle-o" style="color:#1abc9c;margin-right:2px;"></i><?php echo date('d M Y',$start_ts); ?></div>
                          <div style="color:#bdc3c7;font-size:10px;">↓ <?php echo $dur_days; ?> day<?php echo $dur_days!=1?'s':''; ?></div>
                          <div style="font-weight:700;font-size:11px;color:<?php echo $is_od?'#e74c3c':'#27ae60'; ?>;">
                            <?php if ($is_od): ?>
                              <i class="fa fa-exclamation-triangle" style="margin-right:2px;"></i><?php echo date('d M Y',$due_ts); ?>
                              <div style="font-size:10px;color:#e74c3c;font-weight:600;"><?php echo abs($days_left); ?>d overdue</div>
                            <?php else: ?>
                              <i class="fa fa-flag-checkered" style="margin-right:2px;color:#1abc9c;"></i><?php echo date('d M Y',$due_ts); ?>
                              <?php if($days_left!==null): ?><div style="font-size:10px;color:#27ae60;font-weight:600;"><?php echo $days_left; ?>d left</div><?php endif; ?>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php elseif ($due_ts): ?>
                        <span style="color:<?php echo $is_od?'#e74c3c':'#7f8c8d'; ?>;font-size:11px;">
                          <?php if($is_od): ?><i class="fa fa-exclamation-triangle" style="margin-right:2px;"></i><?php endif; ?>
                          <?php echo date('d M Y',$due_ts); ?>
                        </span>
                      <?php else: ?>
                        <span style="color:#bdc3c7;">—</span>
                      <?php endif; ?>
                    </td>

                    <!-- Handler status -->
                    <td style="padding:10px 10px;text-align:center;">
                      <span style="background:<?php echo $hsc; ?>18;color:<?php echo $hsc; ?>;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;border:1px solid <?php echo $hsc; ?>44;">
                        <i class="fa fa-<?php echo $tp['handler_status']==='active'?'check-circle-o':'pause-circle-o'; ?>" style="margin-right:3px;"></i><?php echo ucfirst($tp['handler_status']); ?>
                      </span>
                    </td>

                    <!-- Notes -->
                    <td style="padding:10px 10px;max-width:180px;">
                      <?php if(!empty($nf)): ?>
                        <span style="font-size:11px;color:#5a6a7a;<?php echo mb_strlen($nf)>50?'cursor:pointer;':''; ?>"
                          <?php if(mb_strlen($nf)>50): ?>
                            data-toggle="popover" data-trigger="hover focus" data-placement="left"
                            data-content="<?php echo htmlspecialchars($nf); ?>" title="Full Notes"
                          <?php endif; ?>>
                          <i class="fa fa-sticky-note-o" style="color:#f39c12;margin-right:3px;"></i><?php echo htmlspecialchars($ns); ?>
                        </span>
                      <?php else: ?>
                        <span style="color:#bdc3c7;font-size:11px;">—</span>
                      <?php endif; ?>
                    </td>

                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>



  <div class="row" style="display: flex; flex-wrap: wrap; align-items: stretch;">
    <!-- Left Column: FullCalendar -->
    <div class="col-md-7" style="display: flex; flex-direction: column; margin-bottom: 20px;">
      <div class="box box-primary" style="flex: 1; margin-bottom: 0;">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-calendar"></i> Project &amp; Task Calendar</h3>
        </div>
        <div class="box-body">
          <div id="calendar"></div>
          <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; justify-content: center; padding-top: 10px; border-top: 1px solid #f0f0f0;">
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #9b59b6; margin-right: 5px;"></span> Project</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #3498db; margin-right: 5px;"></span> Task</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #e67e22; margin-right: 5px;"></span> Epic</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #1abc9c; margin-right: 5px;"></span> Story</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #7f8c8d; margin-right: 5px;"></span> Sub-task</span>
            <span style="display: flex; align-items: center; color: #555;"><span style="width: 12px; height: 12px; border-radius: 3px; background: #e74c3c; margin-right: 5px;"></span> Due Date</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Recent Projects & Tasks -->
    <div class="col-md-5" style="display: flex; flex-direction: column; margin-bottom: 20px;">
      
      <!-- Recent Projects -->
      <div class="box box-info" style="flex: 1; display: flex; flex-direction: column; margin-bottom: 15px;">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-folder-open"></i> Recent Projects</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('project-list') ?>" class="btn btn-box-tool btn-xs btn-primary">View All</a>
          </div>
        </div>
        <div class="box-body no-padding" style="flex: 1; overflow-y: auto;">
          <table class="table table-hover">
            <thead>
              <tr><th>Project</th><th>Status</th><th>Tasks</th></tr>
            </thead>
            <tbody>
              <?php if (empty($recent_projects)): ?>
              <tr><td colspan="3" class="text-center text-muted">No projects yet.</td></tr>
              <?php else: ?>
              <?php foreach ($recent_projects as $p):
                $ptask = (int)$p['task_count'];
                $pdone = (int)$p['done_count'];
                $ppct  = $ptask > 0 ? round(($pdone / $ptask) * 100) : 0;
              ?>
              <tr>
                <td>
                  <a href="#" class="project-link-modal" data-id="<?php echo $p['project_id']; ?>" style="font-weight:600;">
                    <?php echo htmlspecialchars($p['name']); ?>
                  </a>
                  <button class="btn btn-xs btn-default btn-view-project-modal" data-id="<?php echo $p['project_id']; ?>" style="margin-left: 5px; padding: 1px 5px; border-radius: 3px;" title="Quick View Team & Effort"><i class="fa fa-eye"></i></button>
                  <?php if (!empty($p['key_name'])): ?>
                    <small class="text-muted"> [<?php echo htmlspecialchars($p['key_name']); ?>]</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="label" style="background:<?php
                    $sc = array('planning'=>'#9b59b6','active'=>'#27ae60','on_hold'=>'#e67e22','completed'=>'#3498db','cancelled'=>'#c0392b');
                    echo isset($sc[$p['status']]) ? $sc[$p['status']] : '#95a5a6';
                  ?>;"><?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?></span>
                </td>
                <td>
                  <?php echo $pdone; ?>/<?php echo $ptask; ?>
                  <div class="progress progress-xs" style="margin:3px 0 0;">
                    <div class="progress-bar bg-green" style="width:<?php echo $ppct; ?>%;"></div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="box box-success" style="flex: 1; display: flex; flex-direction: column; margin-bottom: 0;">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-tasks"></i> Recent Tasks</h3>
          <div class="box-tools pull-right">
            <a href="<?php echo site_url('task-list') ?>" class="btn btn-box-tool btn-xs btn-success">View All</a>
          </div>
        </div>
        <div class="box-body no-padding" style="flex: 1; overflow-y: auto;">
          <table class="table table-hover table-condensed">
            <thead>
              <tr>
                <th>Task</th>
                <th>Status</th>
                <th>Assignee</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_tasks)): ?>
              <tr><td colspan="3" class="text-center text-muted">No tasks yet.</td></tr>
              <?php else: ?>
              <?php foreach ($recent_tasks as $t): ?>
              <tr>
                <td>
                  <a href="#" class="task-link-modal" data-id="<?php echo $t['task_id']; ?>" style="font-weight:600; font-size:12px;">
                    <?php echo htmlspecialchars(mb_substr($t['title'], 0, 35)); ?><?php echo strlen($t['title']) > 35 ? '...' : ''; ?>
                  </a>
                </td>
                <td><span class="badge badge-status-<?php echo $t['status']; ?>" style="font-size:10px;"><?php $sts = TASK_STATUS_OPT; echo isset($sts[$t['status']]) ? $sts[$t['status']] : $t['status']; ?></span></td>
                <td style="font-size:11px;"><?php echo htmlspecialchars($t['assignee_name'] ?: '-'); ?></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>

<?php if (!is_null($manager_project_count)): ?>
<!-- Add Modal -->
<div class="modal fade" id="addHandlerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo site_url('dash') ?>" method="post">
        <input type="hidden" name="mode" value="AddHandler">
        <div class="modal-header" style="background:#27ae60; color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
          <h4 class="modal-title"><i class="fa fa-plus"></i> Handle Project to Team Leader</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Project <span class="text-danger">*</span></label>
            <select name="project_id" class="form-control select2" required style="width:100%;">
              <option value="">-- Select Project --</option>
              <?php if(isset($projects_list)): foreach ($projects_list as $p): ?>
              <option value="<?php echo $p['project_id']; ?>"
                      data-enddate="<?php echo !empty($p['end_date']) ? date('d-m-Y', strtotime($p['end_date'])) : ''; ?>"
                      data-days="<?php echo $p['manager_deadline_days']; ?>">
                <?php echo htmlspecialchars($p['name']); ?>
              </option>
              <?php endforeach; endif; ?>
            </select>
          </div>
          <div class="form-group"><label>Handled To (Team Leader) <span class="text-danger">*</span></label>
            <select name="team_leader_id" class="form-control select2" required style="width:100%;">
              <option value="">-- Select Team Leader --</option>
              <?php if(isset($team_leaders_list)): foreach ($team_leaders_list as $u): ?>
              <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
              <?php endforeach; endif; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group"><label>Due Date <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control project-due-date-input" required>
                <div class="project-deadline-info" style="margin-top: 5px; font-size: 11px; color: #7f8c8d; display: none;">
                  Overall Deadline: <span class="lbl-project-end-date">-</span> (<span class="lbl-project-days">-</span> days remaining)
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group"><label>Status</label>
                <select name="status" class="form-control">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Notes / Description</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Enter notes or delegation details..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Calendar Event Modal -->
<div class="modal fade" id="calendarEventModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none; overflow: hidden;">
      <div class="modal-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border-bottom: none; padding: 15px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8; text-shadow: none;">&times;</button>
        <h4 class="modal-title" style="font-weight: 600; font-size: 16px;">
          <i class="fa fa-info-circle"></i> <span id="cal_modal_type">Event</span> Details
        </h4>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <h3 id="cal_modal_title" style="margin-top: 0; font-weight: 700; color: #2c3e50;"></h3>
        <hr style="margin: 10px 0 15px;">
        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
          <tbody>
            <tr>
              <th style="width: 35%; color: #7f8c8d;">Status</th>
              <td><span id="cal_modal_status" class="label label-default"></span></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Priority</th>
              <td id="cal_modal_priority"></td>
            </tr>
            <tr id="cal_modal_assignee_row">
              <th style="color: #7f8c8d;">Assignee</th>
              <td id="cal_modal_assignee"></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Assigned By</th>
              <td id="cal_modal_assigned_by"></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Start Date</th>
              <td id="cal_modal_start"></td>
            </tr>
            <tr>
              <th style="color: #7f8c8d;">Due Date</th>
              <td id="cal_modal_end"></td>
            </tr>
            <tr id="cal_modal_est_row">
              <th style="color: #7f8c8d;">Estimated Time</th>
              <td id="cal_modal_est"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 12px 20px;">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px; font-weight: 600;">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

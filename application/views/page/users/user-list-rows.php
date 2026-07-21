<?php if (empty($grouped_users)): ?>
  <div class="text-center text-muted" style="padding:30px; background:#fff; border:1px solid #ddd; border-radius:4px;">No users found.</div>
<?php else: ?>
  <div class="panel-group" id="accordionUserList" role="tablist" aria-multiselectable="true">
  <?php 
  $acc_index = 0;
  foreach ($grouped_users as $dep_name => $users): 
    $acc_index++;
    $col_id = "collapse_" . $acc_index;
    
    // Assign a color class to department headers for aesthetics
    $dep_colors = array(
      'Administration' => 'primary',
      'Development' => 'info',
      'Marketing' => 'warning',
      'Design' => 'warning',
      'QA' => 'success',
      'Tester' => 'success',
      'Support' => 'default',
      'HR' => 'info',
      'Relations' => 'info',
      'Finance' => 'warning',
      'Billing' => 'warning',
      'Payroll' => 'warning',
      'Network' => 'default',
      'System' => 'default',
      'Cloud' => 'info',
      'Business' => 'primary',
      'Product' => 'primary',
      'Recruitment' => 'success',
      'Learning' => 'info'
    );
    
    // Assign suitable font-awesome icons for departments
    $dep_icons = array(
      'Administration' => 'fa-cogs',
      'Development' => 'fa-code',
      'Marketing' => 'fa-bullhorn',
      'Design' => 'fa-paint-brush',
      'QA' => 'fa-bug',
      'Tester' => 'fa-bug',
      'Support' => 'fa-life-ring',
      'HR' => 'fa-users',
      'Relations' => 'fa-users',
      'Finance' => 'fa-money',
      'Billing' => 'fa-money',
      'Payroll' => 'fa-money',
      'Network' => 'fa-sitemap',
      'System' => 'fa-server',
      'Cloud' => 'fa-cloud',
      'Business' => 'fa-briefcase',
      'Product' => 'fa-cubes',
      'Recruitment' => 'fa-user-plus',
      'Learning' => 'fa-graduation-cap'
    );

    // Find closest match or default
    $hdr_color = 'default';
    $hdr_icon = 'fa-users'; // default icon
    
    foreach($dep_colors as $k => $c) {
      if (stripos($dep_name, $k) !== false) {
        $hdr_color = $c;
        break;
      }
    }
    
    foreach($dep_icons as $k => $i) {
      if (stripos($dep_name, $k) !== false) {
        $hdr_icon = $i;
        break;
      }
    }
  ?>
    <div class="panel panel-default" style="margin-bottom: 10px; border-radius: 6px; border: 1px solid #e1e8ed; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
      <div class="panel-heading" role="tab" id="heading_<?php echo $acc_index; ?>" style="background: #f8f9fa; cursor: pointer; padding: 15px 20px;" data-toggle="collapse" data-parent="#accordionUserList" href="#<?php echo $col_id; ?>" aria-expanded="true" aria-controls="<?php echo $col_id; ?>">
        <h4 class="panel-title" style="font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
          <span>
            <i class="fa <?php echo $hdr_icon; ?> text-<?php echo $hdr_color; ?>" style="margin-right:8px; min-width: 18px; text-align: center;"></i> 
            <?php echo htmlspecialchars($dep_name); ?>
            <span class="badge" style="margin-left: 10px; background: #eaeded; color: #555;"><?php echo count($users); ?> Users</span>
          </span>
          <i class="fa fa-chevron-down text-muted"></i>
        </h4>
      </div>
      <div id="<?php echo $col_id; ?>" class="panel-collapse collapse <?php echo ($acc_index==1)?'in':''; ?>" role="tabpanel" aria-labelledby="heading_<?php echo $acc_index; ?>">
        <div class="panel-body table-responsive no-padding">
          <table class="table table-hover table-striped" style="margin-bottom: 0;">
            <thead>
              <tr style="background:#f4f6f8;">
                <th style="width:40px;">#</th>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th style="width:100px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $j => $u):
                $initials = strtoupper(implode('', array_map(function($w){ return isset($w[0]) ? $w[0] : ''; }, array_slice(explode(' ', $u['name']), 0, 2))));
                $avatarClass = 'ua-' . ($u['role'] ?: 'default');
              ?>
              <tr>
                <td><?php echo $j + 1; ?></td>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div class="user-avatar <?php echo $avatarClass; ?>"><?php echo htmlspecialchars($initials); ?></div>
                    <div>
                      <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                      <?php if ($this->session->userdata(SESS_HEAD.'_user_id') == $u['user_id']): ?>
                        <span class="label label-primary" style="font-size:9px;">You</span>
                      <?php endif; ?>
                      <br><small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <?php $rc = array('admin'=>'danger','manager'=>'warning','team_leader'=>'info','staff'=>'default'); $rl = USER_ROLE_OPT; ?>
                  <span class="label label-<?php echo isset($rc[$u['role']])?$rc[$u['role']]:'default'; ?>">
                    <?php echo isset($rl[$u['role']]) ? $rl[$u['role']] : $u['role']; ?>
                  </span>
                </td>
                <td>
                  <span class="label label-<?php echo ($u['status']=='Active')?'success':'danger'; ?>">
                    <i class="fa fa-<?php echo ($u['status']=='Active')?'check':'ban'; ?>"></i> <?php echo $u['status']; ?>
                  </span>
                </td>
                <td style="font-size:12px;"><?php echo date('d-M-Y', strtotime($u['created_date'])); ?></td>
                <td style="white-space:nowrap;">
                  <button class="btn btn-xs btn-warning btn-edit-user"
                    data-id="<?php echo $u['user_id']; ?>"
                    data-name="<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>"
                    data-email="<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>"
                    data-role="<?php echo $u['role']; ?>"
                    data-department_id="<?php echo isset($u['department_id']) ? $u['department_id'] : ''; ?>"
                    title="Edit"><i class="fa fa-pencil"></i>
                  </button>
                  <form method="post" action="<?php echo site_url($s_url) ?>" style="display:inline;">
                    <input type="hidden" name="mode" value="ToggleStatus">
                    <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                    <?php if ($this->session->userdata(SESS_HEAD.'_user_id') != $u['user_id']): ?>
                    <button type="submit" class="btn btn-xs <?php echo ($u['status']=='Active')?'btn-default':'btn-success'; ?>"
                      title="<?php echo ($u['status']=='Active')?'Deactivate':'Activate'; ?>"
                      onclick="return confirm('<?php echo ($u['status']=='Active')?'Deactivate':'Activate'; ?> <?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>?')">
                      <i class="fa fa-<?php echo ($u['status']=='Active')?'ban':'check'; ?>"></i>
                    </button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

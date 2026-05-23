<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>Role Permissions <small>Manage menu and module access permissions for roles</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Role Permissions</li>
  </ol>
</section>

<style>
  /* Toast Notification Container */
  #toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
  }
  .custom-toast {
    background: #2c3e50;
    color: #fff;
    padding: 12px 20px;
    margin-bottom: 10px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 250px;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
  }
  .custom-toast.show {
    opacity: 1;
    transform: translateY(0);
  }
  .custom-toast-success {
    border-left: 4px solid #27ae60;
  }
  .custom-toast-error {
    border-left: 4px solid #e74c3c;
  }

  /* Slider Toggle Switch */
  .switch-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    background: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 6px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
  }
  .switch-container:hover {
    background: #f5f5f5;
    border-color: #ddd;
  }
  .switch-label-group {
    display: flex;
    flex-direction: column;
  }
  .switch-title {
    font-weight: 600;
    color: #333;
    margin: 0;
    font-size: 14px;
  }
  .switch-desc {
    font-size: 12px;
    color: #777;
    margin: 2px 0 0 0;
  }
  .switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 22px;
    margin: 0;
  }
  .switch input { 
    opacity: 0;
    width: 0;
    height: 0;
  }
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
  }
  .slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
  }
  input:checked + .slider {
    background-color: #27ae60;
  }
  input:focus + .slider {
    box-shadow: 0 0 1px #27ae60;
  }
  input:checked + .slider:before {
    transform: translateX(24px);
  }
  .slider.round {
    border-radius: 22px;
  }
  .slider.round:before {
    border-radius: 50%;
  }

  .role-avatar { 
    width: 32px; 
    height: 32px; 
    border-radius: 50%; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    font-weight: 700; 
    font-size: 13px; 
    color: #fff; 
    vertical-align: middle; 
    margin-right: 8px;
  }
  .ra-admin       { background:#c0392b; }
  .ra-manager     { background:#e67e22; }
  .ra-team-leader { background:#2980b9; }
  .ra-staff       { background:#7f8c8d; }
  .ra-default { background:#95a5a6; }
</style>

<!-- Toast Container for Notifications -->
<div id="toast-container"></div>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-key"></i> Role Directory</h3>
    </div>
    
    <div class="box-body table-responsive no-padding">
      <table class="table table-hover table-bordered">
        <thead>
          <tr>
            <th style="width: 60px;">#</th>
            <th style="width: 250px;">Role</th>
            <th>Allowed Modules</th>
            <th style="width: 150px; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $sno = 1;
          $role_classes = array(
              'admin'       => 'ra-admin',
              'manager'     => 'ra-manager',
              'team_leader' => 'ra-team-leader',
              'staff'       => 'ra-staff'
          );
          foreach ($roles as $key => $name): 
              $avatar_char = strtoupper($key[0]);
              $avatar_class = isset($role_classes[$key]) ? $role_classes[$key] : 'ra-default';
          ?>
          <tr>
            <td><?php echo $sno++; ?></td>
            <td>
              <div style="display: flex; align-items: center;">
                <div class="role-avatar <?php echo $avatar_class; ?>"><?php echo $avatar_char; ?></div>
                <strong><?php echo $name; ?></strong>
              </div>
            </td>
            <td id="allowed-modules-<?php echo $key; ?>">
              <?php 
              $has_any = false;
              foreach ($modules as $m_key => $m_name) {
                  $allowed = isset($permissions[$key][$m_key]) ? $permissions[$key][$m_key] : (($key === 'admin') ? 1 : 0);
                  if ($allowed) {
                      $has_any = true;
                      echo '<span class="label label-success" style="margin-right: 5px; display: inline-block; margin-bottom: 4px;"><i class="fa fa-check"></i> ' . $m_name . '</span>';
                  }
              }
              if (!$has_any) {
                  echo '<span class="text-muted" style="font-style: italic;">No permissions enabled</span>';
              }
              ?>
            </td>
            <td style="text-align: center;">
              <button class="btn btn-xs btn-primary btn-manage-permission"
                data-role="<?php echo $key; ?>"
                data-role-name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"
                <?php foreach ($modules as $m_key => $m_name): ?>
                  data-perm-<?php echo $m_key; ?>="<?php echo isset($permissions[$key][$m_key]) ? $permissions[$key][$m_key] : (($key === 'admin') ? 1 : 0); ?>"
                <?php endforeach; ?>
                title="Manage Permissions">
                <i class="fa fa-key"></i> Permissions
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Manage Permissions Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#2c3e50; color:#fff;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-sliders"></i> Edit Permissions: <span id="modal-role-title" style="font-weight:bold;"></span></h4>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <input type="hidden" id="modal-role-key">
        
        <div id="modal-permission-list">
          <?php foreach ($module_tree as $m_key => $m_data): ?>
          <div class="switch-container">
            <div class="switch-label-group" style="flex:1;">
              <label class="switch-title" for="switch-<?php echo $m_key; ?>">
                <?php if(!empty($m_data['sub'])): ?>
                  <a href="#" class="toggle-sub-menu" data-target="#sub-<?php echo $m_key; ?>" style="color:#333; text-decoration:none;">
                    <i class="fa fa-angle-right" id="icon-<?php echo $m_key; ?>" style="width:16px;"></i> <?php echo $m_data['label']; ?>
                  </a>
                <?php else: ?>
                  <?php echo $m_data['label']; ?>
                <?php endif; ?>
              </label>
              <span class="switch-desc"><?php echo $m_data['desc']; ?></span>
            </div>
            <div>
              <label class="switch">
                <input type="checkbox" class="toggle-permission-switch" id="switch-<?php echo $m_key; ?>" data-menu-key="<?php echo $m_key; ?>">
                <span class="slider round"></span>
              </label>
            </div>
          </div>
          
          <?php if(!empty($m_data['sub'])): ?>
            <div id="sub-<?php echo $m_key; ?>" style="display:none; margin-left:25px; padding-left:10px; border-left:2px solid #ddd; margin-bottom:10px;">
              <?php foreach ($m_data['sub'] as $sub_key => $sub_data): ?>
              <div class="switch-container" style="background:#fff; border:none; border-bottom:1px solid #f1f1f1; margin-bottom:0;">
                <div class="switch-label-group">
                  <label class="switch-title" for="switch-<?php echo $sub_key; ?>" style="font-size:13px;"><?php echo $sub_data['label']; ?></label>
                  <span class="switch-desc" style="font-size:11px;"><?php echo $sub_data['desc']; ?></span>
                </div>
                <div>
                  <label class="switch" style="width:36px; height:18px;">
                    <input type="checkbox" class="toggle-permission-switch" id="switch-<?php echo $sub_key; ?>" data-menu-key="<?php echo $sub_key; ?>">
                    <span class="slider round" style="border-radius:18px;"></span>
                  </label>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer" style="background: #f9f9f9;">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

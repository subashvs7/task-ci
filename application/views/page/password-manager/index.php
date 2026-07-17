<?php
// Category color helper
function pm_get_cat_color($cat_id) {
  $colors = [
    1  => 'linear-gradient(135deg,#3498db,#2980b9)',
    2  => 'linear-gradient(135deg,#e74c3c,#c0392b)',
    3  => 'linear-gradient(135deg,#9b59b6,#8e44ad)',
    4  => 'linear-gradient(135deg,#27ae60,#1e8449)',
    5  => 'linear-gradient(135deg,#16a085,#1abc9c)',
    6  => 'linear-gradient(135deg,#2980b9,#1abc9c)',
    7  => 'linear-gradient(135deg,#2c3e50,#34495e)',
    8  => 'linear-gradient(135deg,#e67e22,#d35400)',
    9  => 'linear-gradient(135deg,#1abc9c,#16a085)',
    10 => 'linear-gradient(135deg,#2980b9,#1a5276)',
    11 => 'linear-gradient(135deg,#8e44ad,#6c3483)',
    12 => 'linear-gradient(135deg,#2ecc71,#27ae60)',
    13 => 'linear-gradient(135deg,#e67e22,#ca6f1e)',
    14 => 'linear-gradient(135deg,#e74c3c,#922b21)',
    15 => 'linear-gradient(135deg,#9b59b6,#76448a)',
    16 => 'linear-gradient(135deg,#e50914,#8b0000)',
    17 => 'linear-gradient(135deg,#3498db,#1f618d)',
    18 => 'linear-gradient(135deg,#7f8c8d,#6c7a7d)',
  ];
  return $colors[(int)$cat_id] ?? 'linear-gradient(135deg,#8e44ad,#6c3483)';
}
?>
<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1><i class="fa fa-lock" style="color:#8e44ad;"></i> Password Manager <small>All Credentials</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Password Manager</li>
  </ol>
</section>

<section class="content">
<?php if ($this->session->flashdata('alert_success')): ?>
  <div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_success'); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('alert_error')): ?>
  <div class="alert alert-danger alert-dismissible"><button class="close" data-dismiss="alert">&times;</button><?php echo $this->session->flashdata('alert_error'); ?></div>
<?php endif; ?>

<style>
/* ===== Password Manager Styles ===== */
.pm-hero {
  background: linear-gradient(135deg, #1a0533 0%, #2c0f5e 40%, #1a1a4e 100%);
  border-radius: 16px;
  padding: 28px 32px;
  margin-bottom: 24px;
  color: #fff;
  position: relative;
  overflow: hidden;
}
.pm-hero::before {
  content: '';
  position: absolute;
  top: -60px; right: -60px;
  width: 220px; height: 220px;
  background: radial-gradient(circle, rgba(142,68,173,0.4) 0%, transparent 70%);
  border-radius: 50%;
}
.pm-hero h2 { font-size: 26px; font-weight: 700; margin: 0 0 4px; letter-spacing: 0.5px; }
.pm-hero p  { margin: 0; opacity: 0.75; font-size: 14px; }
.pm-hero .pm-hero-actions { margin-top: 18px; }

.pm-stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  border-left: 4px solid #8e44ad;
  margin-bottom: 16px;
  transition: box-shadow 0.2s;
}
.pm-stat-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.14); }
.pm-stat-card .stat-icon {
  width: 50px; height: 50px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; color: #fff;
  flex-shrink: 0;
}
.pm-stat-card .stat-val  { font-size: 26px; font-weight: 700; line-height: 1; color: #2c3e50; }
.pm-stat-card .stat-lbl  { font-size: 12px; color: #7f8c8d; margin-top: 2px; }

/* Filter bar */
.pm-filter-bar {
  background: #fff;
  border-radius: 12px;
  padding: 14px 18px;
  margin-bottom: 18px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
}
.pm-filter-bar .form-control {
  border-radius: 8px;
  height: 36px; font-size: 13px;
}
.pm-search-box {
  position: relative; flex: 1; min-width: 200px;
}
.pm-search-box input { padding-left: 34px; }
.pm-search-box .fa { position: absolute; left: 11px; top: 10px; color: #aaa; }

/* Category Sidebar */
.pm-sidebar {
  background: #fff;
  border-radius: 12px;
  padding: 16px 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.07);
}
.pm-sidebar h5 {
  font-size: 11px; font-weight: 700; color: #aaa;
  text-transform: uppercase; letter-spacing: 1px;
  padding: 0 16px 10px; margin: 0;
}
.pm-cat-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 16px;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition: all 0.15s;
  color: #4a4a6a; font-size: 13px; text-decoration: none;
}
.pm-cat-item:hover, .pm-cat-item.active {
  background: #f4eeff;
  border-left-color: #8e44ad;
  color: #8e44ad; text-decoration: none;
}
.pm-cat-item .cat-emoji { font-size: 16px; }
.pm-cat-item .cat-count {
  margin-left: auto;
  background: #ede0ff;
  color: #8e44ad;
  border-radius: 12px;
  font-size: 11px;
  padding: 1px 8px;
  font-weight: 600;
}

/* Cards grid */
.pm-card {
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  margin-bottom: 16px;
  border: 1px solid #f0eaff;
  overflow: hidden;
  transition: box-shadow 0.2s, transform 0.15s;
  position: relative;
}
.pm-card:hover {
  box-shadow: 0 8px 28px rgba(142,68,173,0.15);
  transform: translateY(-2px);
}
.pm-card-header {
  background: linear-gradient(135deg, #f9f5ff 0%, #f0eaff 100%);
  padding: 14px 16px 12px;
  display: flex; align-items: center; gap: 12px;
  border-bottom: 1px solid #f0eaff;
}
.pm-card-icon {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.pm-card-title   { font-size: 15px; font-weight: 700; color: #2c1a4e; line-height: 1.2; }
.pm-card-service { font-size: 12px; color: #7f8c8d; }
.pm-card-fav {
  margin-left: auto; cursor: pointer; font-size: 18px;
  transition: transform 0.2s;
}
.pm-card-fav:hover { transform: scale(1.3); }
.pm-card-body { padding: 12px 16px; }
.pm-field-row {
  display: flex; align-items: center; gap: 8px;
  margin-bottom: 8px; font-size: 13px;
}
.pm-field-row label {
  color: #999; font-size: 11px; min-width: 82px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin: 0;
}
.pm-field-row .pm-val {
  color: #333; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.pm-field-row .pm-val.masked { font-family: monospace; letter-spacing: 3px; color: #aaa; }
.pm-copy-btn {
  background: none; border: none; cursor: pointer; color: #8e44ad;
  padding: 2px 6px; border-radius: 6px; font-size: 13px;
  transition: background 0.15s;
}
.pm-copy-btn:hover { background: #f0eaff; }
.pm-reveal-btn {
  background: none; border: none; cursor: pointer; color: #7f8c8d;
  padding: 2px 6px; border-radius: 6px; font-size: 12px;
}
.pm-reveal-btn:hover { color: #8e44ad; }
.pm-card-footer {
  padding: 10px 16px;
  border-top: 1px solid #f5f0ff;
  display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.pm-tag {
  background: #ede0ff; color: #8e44ad;
  border-radius: 20px; font-size: 11px; padding: 2px 10px; font-weight: 500;
}
.pm-status-badge {
  border-radius: 20px; font-size: 11px; padding: 2px 10px; font-weight: 600;
}
.pm-status-active   { background: #d4edda; color: #155724; }
.pm-status-inactive { background: #f8d7da; color: #721c24; }
.pm-card-actions { margin-left: auto; display: flex; gap: 6px; }
.pm-card-actions a, .pm-card-actions button {
  width: 30px; height: 30px;
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
  font-size: 13px; border: none; cursor: pointer; transition: all 0.15s;
  text-decoration: none;
}
.pm-btn-edit   { background: #e8f4fd; color: #2980b9; }
.pm-btn-edit:hover   { background: #2980b9; color: #fff; }
.pm-btn-delete { background: #fdecea; color: #e74c3c; }
.pm-btn-delete:hover { background: #e74c3c; color: #fff; }

/* Empty state */
.pm-empty { text-align: center; padding: 60px 20px; color: #bbb; }
.pm-empty .pm-empty-icon { font-size: 60px; margin-bottom: 16px; opacity: 0.3; }
.pm-empty h4 { color: #8e44ad; opacity: 0.7; }

/* Toast notification */
#pm-toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  background: #2c1a4e; color: #fff;
  padding: 12px 22px; border-radius: 10px; font-size: 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  display: none; align-items: center; gap: 10px;
  max-width: 320px;
}
#pm-toast.show { display: flex; animation: slideUp 0.3s ease; }
@keyframes slideUp {
  from { opacity:0; transform: translateY(20px); }
  to   { opacity:1; transform: translateY(0); }
}

/* URL link */
.pm-url-link { color: #8e44ad; font-size: 12px; text-decoration: none; }
.pm-url-link:hover { text-decoration: underline; }

/* Expiry warning */
.pm-expiry-warn { color: #e74c3c; font-weight: 600; }
.pm-expiry-ok   { color: #27ae60; }
</style>

<!-- Hero Banner -->
<div class="pm-hero">
  <div class="row">
    <div class="col-md-8">
      <h2>🔐 Universal Password Manager</h2>
      <p>Securely store all your credentials — websites, banking, servers, Wi-Fi, apps & more. AES-256 encrypted.</p>
      <div class="pm-hero-actions">
        <a href="<?php echo site_url('password-manager/form') ?>" class="btn btn-warning btn-sm" style="border-radius:8px; font-weight:600; margin-right:8px;">
          <i class="fa fa-plus"></i> Add New Entry
        </a>
      </div>
    </div>
    <div class="col-md-4 hidden-sm hidden-xs" style="text-align:right; padding-top:10px; font-size: 70px; opacity: 0.15;">🔒</div>
  </div>
</div>

<!-- Stats Row -->
<div class="row">
  <div class="col-md-3 col-sm-6">
    <div class="pm-stat-card">
      <div class="stat-icon" style="background: linear-gradient(135deg,#8e44ad,#6c3483);">
        <i class="fa fa-key"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo $total_count; ?></div>
        <div class="stat-lbl">Total Entries</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="pm-stat-card" style="border-left-color:#f1c40f;">
      <div class="stat-icon" style="background: linear-gradient(135deg,#f1c40f,#d4ac0d);">
        <i class="fa fa-star"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo $fav_count; ?></div>
        <div class="stat-lbl">Favorites</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="pm-stat-card" style="border-left-color:#27ae60;">
      <div class="stat-icon" style="background: linear-gradient(135deg,#27ae60,#1e8449);">
        <i class="fa fa-check-circle"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo $active_count; ?></div>
        <div class="stat-lbl">Active</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="pm-stat-card" style="border-left-color:#e74c3c;">
      <div class="stat-icon" style="background: linear-gradient(135deg,#e74c3c,#c0392b);">
        <i class="fa fa-ban"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo $inactive_count; ?></div>
        <div class="stat-lbl">Inactive</div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="row">
  <!-- Category Sidebar -->
  <div class="col-md-2 col-sm-3 hidden-xs">
    <div class="pm-sidebar">
      <h5>Categories</h5>
      <?php
      $cur_cat = $this->input->get('category');
      $cur_fav = $this->input->get('favorite');
      ?>
      <a href="<?php echo site_url('password-manager') ?>" class="pm-cat-item <?php echo (!$cur_cat && !$cur_fav) ? 'active' : ''; ?>">
        <span class="cat-emoji">🔑</span> All
        <span class="cat-count"><?php echo $total_count; ?></span>
      </a>
      <a href="<?php echo site_url('password-manager?favorite=1') ?>" class="pm-cat-item <?php echo $cur_fav === '1' ? 'active' : ''; ?>">
        <span class="cat-emoji">⭐</span> Favorites
        <span class="cat-count" style="background:#fff5cc;color:#c7a000;"><?php echo $fav_count; ?></span>
      </a>
      <hr style="margin: 6px 16px; border-color: #f0eaff;">
      <?php foreach ($cat_list as $cat): ?>
      <a href="<?php echo site_url('password-manager?category=' . $cat['category_id']) ?>"
         class="pm-cat-item <?php echo ($cur_cat == $cat['category_id']) ? 'active' : ''; ?>">
        <span class="cat-emoji"><?php echo $cat['category_emoji']; ?></span>
        <span><?php echo htmlspecialchars($cat['category_name']); ?></span>
        <?php if ($cat['total'] > 0): ?>
        <span class="cat-count"><?php echo $cat['total']; ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Records Area -->
  <div class="col-md-10 col-sm-9 col-xs-12">
    <!-- Filter Bar -->
    <div class="pm-filter-bar">
      <div class="pm-search-box">
        <i class="fa fa-search"></i>
        <input type="text" id="pm-search" class="form-control" placeholder="Search accounts, services<?php echo isset($is_admin) && $is_admin ? ', users' : ''; ?>..." value="<?php echo htmlspecialchars($this->input->get('search') ?? ''); ?>">
      </div>
      <select id="pm-filter-status" class="form-control" style="width:120px;">
        <option value="">All Status</option>
        <option value="1" <?php if ($this->input->get('status')==='1') echo 'selected'; ?>>Active</option>
        <option value="0" <?php if ($this->input->get('status')==='0') echo 'selected'; ?>>Inactive</option>
      </select>
      <select id="pm-filter-cat" class="form-control hidden-xs" style="width:140px;">
        <option value="">All Categories</option>
        <?php foreach ($cat_list as $cat): ?>
        <option value="<?php echo $cat['category_id']; ?>" <?php if ($this->input->get('category') == $cat['category_id']) echo 'selected'; ?>>
          <?php echo $cat['category_emoji'] . ' ' . htmlspecialchars($cat['category_name']); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($is_admin) && $is_admin && !empty($user_list)): ?>
      <select id="pm-filter-owner" class="form-control hidden-xs" style="width:150px;" title="Filter by Owner">
        <option value="">👥 All Users</option>
        <?php foreach ($user_list as $u): ?>
        <option value="<?php echo $u['user_id']; ?>" <?php if ($this->input->get('owner') == $u['user_id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($u['name']); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
      <div class="btn-group">
        <button class="btn btn-default btn-sm pm-view-btn active" id="btn-grid" style="border-radius:8px 0 0 8px;" title="Grid View"><i class="fa fa-th-large"></i></button>
        <button class="btn btn-default btn-sm pm-view-btn" id="btn-list" style="border-radius:0 8px 8px 0;" title="List View"><i class="fa fa-list"></i></button>
      </div>
      <a href="<?php echo site_url('password-manager/form') ?>" class="btn btn-sm" style="background:#8e44ad;color:#fff;border-radius:8px;font-weight:600;">
        <i class="fa fa-plus"></i> Add
      </a>
    </div>

    <!-- Records Grid -->
    <div id="pm-records-grid">
    <?php if (empty($records)): ?>
      <div class="pm-empty">
        <div class="pm-empty-icon">🔒</div>
        <h4>No password entries found</h4>
        <?php if (isset($is_admin) && $is_admin): ?>
        <p>No passwords have been created yet by any user.</p>
        <?php else: ?>
        <p>Click <strong>Add New Entry</strong> to securely store your first credential.</p>
        <a href="<?php echo site_url('password-manager/form') ?>" class="btn btn-sm" style="background:#8e44ad;color:#fff;border-radius:8px;margin-top:10px;font-weight:600;">
          <i class="fa fa-plus"></i> Add First Entry
        </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <div class="row" id="pm-card-container">
    <?php foreach ($records as $rec):
      $today = date('Y-m-d');
      $exp_warn = false;
      $exp_soon = false;
      if ($rec['expiry_date']) {
        $days_left = (strtotime($rec['expiry_date']) - strtotime($today)) / 86400;
        $exp_warn  = $days_left < 0;
        $exp_soon  = $days_left >= 0 && $days_left <= 30;
      }
      $tags_arr = $rec['tags'] ? array_filter(array_map('trim', explode(',', $rec['tags']))) : [];
    ?>
    <div class="col-md-4 col-sm-6 pm-card-col" data-name="<?php echo strtolower(htmlspecialchars($rec['account_name'] . ' ' . $rec['service_name'])); ?>">
      <div class="pm-card">
        <div class="pm-card-header">
          <div class="pm-card-icon" style="background:<?php echo pm_get_cat_color($rec['category_id']); ?>;">
            <?php echo $rec['category_emoji'] ?: '🔑'; ?>
          </div>
          <div style="flex:1; overflow:hidden;">
            <div class="pm-card-title"><?php echo htmlspecialchars($rec['account_name']); ?></div>
            <div class="pm-card-service">
              <?php if ($rec['service_name']): ?>
                <?php if ($rec['login_url']): ?>
                  <a href="<?php echo htmlspecialchars($rec['login_url']); ?>" target="_blank" class="pm-url-link">
                    <i class="fa fa-external-link" style="font-size:10px;"></i>
                    <?php echo htmlspecialchars($rec['service_name']); ?>
                  </a>
                <?php else: ?>
                  <?php echo htmlspecialchars($rec['service_name']); ?>
                <?php endif; ?>
              <?php else: ?>
                <span class="pm-tag"><?php echo htmlspecialchars($rec['category_name'] ?? 'Other'); ?></span>
              <?php endif; ?>
            </div>
            <?php if (isset($is_admin) && $is_admin && !empty($rec['owner_name'])): ?>
            <div style="margin-top:3px;">
              <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;background:#f0eaff;color:#8e44ad;border-radius:20px;padding:1px 8px;font-weight:600;">
                <i class="fa fa-user" style="font-size:9px;"></i>
                <?php echo htmlspecialchars($rec['owner_name']); ?>
              </span>
            </div>
            <?php endif; ?>
          </div>
          <span class="pm-card-fav" onclick="toggleFav(<?php echo $rec['id']; ?>, this)" title="Toggle Favorite">
            <i class="fa fa-star<?php echo $rec['is_favorite'] ? '' : '-o'; ?>" style="color:<?php echo $rec['is_favorite'] ? '#f1c40f' : '#ccc'; ?>;"></i>
          </span>
        </div>
        <div class="pm-card-body">
          <?php if ($rec['username'] || $rec['email']): ?>
          <div class="pm-field-row">
            <label><i class="fa fa-user"></i> User</label>
            <span class="pm-val"><?php echo htmlspecialchars($rec['username'] ?: $rec['email']); ?></span>
            <button class="pm-copy-btn" onclick="copyText('<?php echo htmlspecialchars(addslashes($rec['username'] ?: $rec['email'])); ?>')" title="Copy"><i class="fa fa-copy"></i></button>
          </div>
          <?php endif; ?>
          <?php if ($rec['password_encrypted']): ?>
          <div class="pm-field-row">
            <label><i class="fa fa-lock"></i> Password</label>
            <span class="pm-val masked" id="pw-<?php echo $rec['id']; ?>">••••••••••</span>
            <button class="pm-reveal-btn" onclick="revealSecret(<?php echo $rec['id']; ?>, 'password_encrypted', 'pw-<?php echo $rec['id']; ?>')" title="Show/Hide">
              <i class="fa fa-eye"></i>
            </button>
            <button class="pm-copy-btn" onclick="copySecret(<?php echo $rec['id']; ?>, 'password_encrypted')" title="Copy Password"><i class="fa fa-copy"></i></button>
          </div>
          <?php endif; ?>
          <?php if ($rec['recovery_email']): ?>
          <div class="pm-field-row">
            <label><i class="fa fa-envelope"></i> Recovery</label>
            <span class="pm-val"><?php echo htmlspecialchars($rec['recovery_email']); ?></span>
          </div>
          <?php endif; ?>
          <?php if ($rec['expiry_date']): ?>
          <div class="pm-field-row">
            <label><i class="fa fa-calendar"></i> Expires</label>
            <span class="pm-val <?php echo $exp_warn ? 'pm-expiry-warn' : ($exp_soon ? 'pm-expiry-warn' : 'pm-expiry-ok'); ?>">
              <?php echo date('d M Y', strtotime($rec['expiry_date'])); ?>
              <?php if ($exp_warn): ?> <i class="fa fa-exclamation-triangle" title="Expired!"></i><?php endif; ?>
              <?php if ($exp_soon && !$exp_warn): ?> <i class="fa fa-clock-o" title="Expiring soon!"></i><?php endif; ?>
            </span>
          </div>
          <?php endif; ?>
        </div>
        <div class="pm-card-footer">
          <span class="pm-status-badge <?php echo $rec['status'] ? 'pm-status-active' : 'pm-status-inactive'; ?>">
            <?php echo $rec['status'] ? 'Active' : 'Inactive'; ?>
          </span>
          <?php foreach (array_slice($tags_arr, 0, 2) as $tag): ?>
          <span class="pm-tag"><?php echo htmlspecialchars($tag); ?></span>
          <?php endforeach; ?>
          <div class="pm-card-actions">
            <a href="<?php echo site_url('password-manager/form/' . $rec['id']) ?>" class="pm-btn-edit" title="Edit">
              <i class="fa fa-pencil"></i>
            </a>
            <button class="pm-btn-delete" onclick="deletePMRecord(<?php echo $rec['id']; ?>)" title="Delete">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    </div><!-- /.row -->
    <?php endif; ?>
    </div><!-- /#pm-records-grid -->
  </div>
</div><!-- /.row -->

</section><!-- /.content -->

<!-- Toast Notification -->
<div id="pm-toast"><i class="fa fa-check-circle"></i> <span id="pm-toast-msg">Copied!</span></div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="pmDeleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border-radius:12px;overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;padding:14px 20px;border:none;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-trash"></i> Confirm Delete</h4>
      </div>
      <div class="modal-body" style="padding:20px;text-align:center;">
        <p style="font-size:15px; margin:0;">Are you sure you want to delete this entry? <br><strong>This cannot be undone.</strong></p>
      </div>
      <div class="modal-footer" style="border:none; padding:10px 20px 20px; text-align:center;">
        <button class="btn btn-default" data-dismiss="modal" style="border-radius:8px; margin-right:8px;">Cancel</button>
        <button class="btn btn-danger" id="pm-confirm-delete" style="border-radius:8px; font-weight:600;"><i class="fa fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
var PM_BASE = '<?php echo site_url() ?>';
var pmDeleteId = null;

// Search filter
$('#pm-search').on('input', function() {
  var q = $(this).val().toLowerCase();
  $('.pm-card-col').each(function() {
    var name = $(this).data('name') || '';
    $(this).toggle(name.indexOf(q) >= 0);
  });
});

// Status filter
$('#pm-filter-status').on('change', function() {
  applyFilters();
});
$('#pm-filter-cat').on('change', function() {
  applyFilters();
});

function applyFilters() {
  var s = $('#pm-filter-status').val();
  var c = $('#pm-filter-cat').val();
  var search  = $('#pm-search').val();
  var url = PM_BASE + 'password-manager';
  var parts = [];
  if (c) parts.push('category=' + c);
  if (s !== '') parts.push('status=' + s);
  if (search) parts.push('search=' + encodeURIComponent(search));
  window.location = url + (parts.length ? '?' + parts.join('&') : '');
}

// Toggle view
$('#btn-grid').on('click', function() {
  $('#btn-grid').addClass('active'); $('#btn-list').removeClass('active');
  localStorage.setItem('pm_view', 'grid');
  renderGrid();
});
$('#btn-list').on('click', function() {
  $('#btn-list').addClass('active'); $('#btn-grid').removeClass('active');
  localStorage.setItem('pm_view', 'list');
  renderList();
});

function renderGrid() {
  $('.pm-card-col').removeClass('col-md-12 col-sm-12').addClass('col-md-4 col-sm-6');
}
function renderList() {
  $('.pm-card-col').removeClass('col-md-4 col-sm-6').addClass('col-md-12 col-sm-12');
}

// Restore view preference
$(function() {
  var v = localStorage.getItem('pm_view');
  if (v === 'list') { $('#btn-list').trigger('click'); }
});

// Reveal / hide secret
var revealedFields = {};
function revealSecret(id, field, elemId) {
  var $el = $('#' + elemId);
  if (revealedFields[elemId]) {
    $el.text('••••••••••').addClass('masked');
    delete revealedFields[elemId];
    return;
  }
  $.post(PM_BASE + 'pm-get-secret', { id: id, field: field }, function(r) {
    if (r.success) {
      $el.text(r.value).removeClass('masked');
      revealedFields[elemId] = true;
    } else {
      showToast('Error: ' + r.message, 'error');
    }
  });
}

// Copy text
function copyText(text) {
  navigator.clipboard.writeText(text).then(function() {
    showToast('Copied to clipboard!');
  }).catch(function() {
    fallbackCopy(text);
  });
}

// Copy secret (fetch then copy)
function copySecret(id, field) {
  $.post(PM_BASE + 'pm-get-secret', { id: id, field: field }, function(r) {
    if (r.success) {
      copyText(r.value);
    } else {
      showToast('Error: ' + r.message, 'error');
    }
  });
}

// Toggle favorite
function toggleFav(id, el) {
  $.post(PM_BASE + 'pm-toggle-fav', { id: id }, function(r) {
    if (r.success) {
      var $icon = $(el).find('i');
      if (r.is_favorite) {
        $icon.removeClass('fa-star-o').addClass('fa-star').css('color','#f1c40f');
        showToast('Added to favorites ⭐');
      } else {
        $icon.removeClass('fa-star').addClass('fa-star-o').css('color','#ccc');
        showToast('Removed from favorites');
      }
    }
  });
}

// Delete
function deletePMRecord(id) {
  pmDeleteId = id;
  $('#pmDeleteModal').modal('show');
}
$('#pm-confirm-delete').on('click', function() {
  if (!pmDeleteId) return;
  var $btn = $(this);
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
  $.post(PM_BASE + 'pm-delete', { id: pmDeleteId }, function(r) {
    $('#pmDeleteModal').modal('hide');
    if (r.success) {
      // Fade out card
      $('.pm-card-col').filter(function() {
        return $(this).find('[onclick*="' + pmDeleteId + '"]').length > 0;
      }).fadeOut(400, function() { $(this).remove(); });
      showToast('Entry deleted.');
    } else {
      showToast('Error: ' + r.message, 'error');
    }
    $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
    pmDeleteId = null;
  });
});

// Toast
function showToast(msg, type) {
  var $t = $('#pm-toast');
  var icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
  var bg   = type === 'error' ? '#c0392b' : '#2c1a4e';
  $t.css('background', bg);
  $t.find('i').attr('class', 'fa ' + icon);
  $('#pm-toast-msg').text(msg);
  $t.addClass('show');
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(function() { $t.removeClass('show'); }, 3000);
}

function fallbackCopy(text) {
  var ta = document.createElement('textarea');
  ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
  document.body.appendChild(ta); ta.focus(); ta.select();
  document.execCommand('copy');
  document.body.removeChild(ta);
  showToast('Copied to clipboard!');
}
</script>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>




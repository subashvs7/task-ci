<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>
    <i class="fa fa-<?php echo $id ? 'pencil' : 'plus-circle'; ?>" style="color:#8e44ad;"></i>
    <?php echo $id ? 'Edit' : 'Add New'; ?> Password Entry
  </h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('password-manager') ?>">Password Manager</a></li>
    <li class="active"><?php echo $id ? 'Edit Entry' : 'Add Entry'; ?></li>
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
/* ===== Form Styles ===== */
.pm-form-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(142,68,173,0.10);
  overflow: hidden;
  margin-bottom: 24px;
}
.pm-form-card .pm-form-header {
  background: linear-gradient(135deg, #1a0533 0%, #2c0f5e 60%, #1a1a4e 100%);
  color: #fff;
  padding: 18px 24px;
  display: flex; align-items: center; gap: 12px;
}
.pm-form-card .pm-form-header .pm-section-icon {
  width: 38px; height: 38px;
  background: rgba(255,255,255,0.15);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
}
.pm-form-card .pm-form-header h4 {
  margin: 0; font-size: 16px; font-weight: 700; letter-spacing: 0.3px;
}
.pm-form-card .pm-form-header p {
  margin: 2px 0 0; font-size: 12px; opacity: 0.7;
}
.pm-form-body { padding: 24px; }

.pm-field-group { margin-bottom: 20px; }
.pm-field-group label {
  font-size: 12px; font-weight: 700; color: #666;
  text-transform: uppercase; letter-spacing: 0.6px;
  margin-bottom: 6px; display: block;
}
.pm-field-group label .req { color: #e74c3c; margin-left: 2px; }
.pm-field-group .form-control {
  border: 1.5px solid #e8e0f0;
  border-radius: 10px;
  height: 42px;
  font-size: 14px;
  color: #2c1a4e;
  transition: border 0.2s, box-shadow 0.2s;
  background: #fafbff;
}
.pm-field-group textarea.form-control { height: auto; }
.pm-field-group .form-control:focus {
  border-color: #8e44ad;
  box-shadow: 0 0 0 3px rgba(142,68,173,0.12);
  background: #fff;
  outline: none;
}
.pm-input-group {
  position: relative;
}
.pm-input-group .form-control { padding-right: 42px; }
.pm-input-group .pm-input-action {
  position: absolute; right: 0; top: 0; bottom: 0;
  width: 42px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #8e44ad; font-size: 15px;
  background: none; border: none; border-radius: 0 10px 10px 0;
  transition: background 0.15s;
}
.pm-input-group .pm-input-action:hover { background: #f0eaff; }

/* Password Strength Bar */
.pm-strength-wrap { margin-top: 8px; }
.pm-strength-bar-bg {
  height: 6px; background: #eee; border-radius: 3px; overflow: hidden;
}
.pm-strength-bar { height: 100%; border-radius: 3px; transition: width 0.3s, background 0.3s; width: 0%; }
.pm-strength-label { font-size: 12px; margin-top: 4px; font-weight: 600; }

/* Gen password panel */
.pm-gen-panel {
  background: linear-gradient(135deg, #f9f5ff, #f0eaff);
  border: 1px solid #e0d0f8;
  border-radius: 10px;
  padding: 14px 16px;
  margin-top: 10px;
  display: none;
}
.pm-gen-panel label { font-size: 12px; color: #666; font-weight: 600; }
.pm-gen-result {
  display: flex; align-items: center; gap: 8px; margin-top: 10px;
}
.pm-gen-result input {
  flex: 1; border-radius: 8px; border: 1.5px solid #c5a8f0;
  height: 38px; padding: 0 12px; font-family: monospace; font-size: 14px;
  background: #fff; color: #2c1a4e;
}

/* Category selector pills */
.pm-cat-pills {
  display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;
}
.pm-cat-pill {
  padding: 6px 14px;
  border-radius: 20px;
  border: 2px solid #e8e0f0;
  background: #fafbff;
  cursor: pointer; font-size: 13px; color: #555;
  transition: all 0.15s;
  display: flex; align-items: center; gap: 5px;
}
.pm-cat-pill:hover { border-color: #8e44ad; color: #8e44ad; background: #f4eeff; }
.pm-cat-pill.selected {
  border-color: #8e44ad; background: #8e44ad; color: #fff;
}

/* Toggle switch */
.pm-toggle-wrap {
  display: flex; align-items: center; gap: 12px;
}
.pm-toggle {
  position: relative; display: inline-block; width: 48px; height: 26px;
}
.pm-toggle input { opacity: 0; width: 0; height: 0; }
.pm-toggle-slider {
  position: absolute; cursor: pointer; inset: 0;
  background: #ccc; border-radius: 26px; transition: 0.3s;
}
.pm-toggle-slider:before {
  content: ''; position: absolute;
  width: 20px; height: 20px; left: 3px; bottom: 3px;
  background: #fff; border-radius: 50%; transition: 0.3s;
}
.pm-toggle input:checked + .pm-toggle-slider { background: #8e44ad; }
.pm-toggle input:checked + .pm-toggle-slider:before { transform: translateX(22px); }

/* Action buttons */
.pm-action-bar {
  background: #fff;
  border-radius: 14px;
  padding: 20px 24px;
  box-shadow: 0 4px 24px rgba(142,68,173,0.10);
  display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
}
.pm-btn {
  border-radius: 10px; font-weight: 600; font-size: 14px;
  padding: 10px 24px; border: none; cursor: pointer;
  display: inline-flex; align-items: center; gap: 8px;
  transition: all 0.2s;
}
.pm-btn-save   { background: linear-gradient(135deg,#8e44ad,#6c3483); color:#fff; box-shadow: 0 4px 12px rgba(142,68,173,0.35); }
.pm-btn-save:hover { background: linear-gradient(135deg,#7d3c98,#5b2c6f); transform: translateY(-1px); }
.pm-btn-reset  { background: #f0f0f0; color: #666; }
.pm-btn-reset:hover { background: #e0e0e0; }
.pm-btn-cancel { background: #fdecea; color: #e74c3c; }
.pm-btn-cancel:hover { background: #e74c3c; color: #fff; }
.pm-btn-delete { background: #e74c3c; color: #fff; box-shadow: 0 4px 12px rgba(231,76,60,0.3); }
.pm-btn-delete:hover { background: #c0392b; }

/* Toast */
#pm-toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  background: #2c1a4e; color: #fff;
  padding: 12px 22px; border-radius: 10px; font-size: 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  display: none; align-items: center; gap: 10px;
}
#pm-toast.show { display: flex; animation: slideUp 0.3s ease; }
@keyframes slideUp {
  from { opacity:0; transform: translateY(20px); }
  to   { opacity:1; transform: translateY(0); }
}

.pm-divider {
  border: none; border-top: 1.5px dashed #ede0ff; margin: 0 0 20px;
}
</style>

<form id="pm-form" autocomplete="off">
  <input type="hidden" name="id" value="<?php echo $id; ?>">
  <input type="hidden" id="pm-category-id" name="category_id" value="<?php echo $record ? $record['category_id'] : ''; ?>">

<div class="row">
  <!-- LEFT: Main Form -->
  <div class="col-md-8">

    <!-- Section: Basic Information -->
    <div class="pm-form-card">
      <div class="pm-form-header">
        <div class="pm-section-icon">📋</div>
        <div>
          <h4>Basic Information</h4>
          <p>Account name, category, service details</p>
        </div>
      </div>
      <div class="pm-form-body">

        <!-- Category Selector -->
        <div class="pm-field-group">
          <label>Category <span class="req">*</span></label>
          <div class="pm-cat-pills" id="pm-cat-pills">
            <?php foreach ($categories as $cat): ?>
            <div class="pm-cat-pill <?php echo ($record && $record['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>"
                 data-id="<?php echo $cat['category_id']; ?>"
                 onclick="selectCategory(this, <?php echo $cat['category_id']; ?>)">
              <?php echo $cat['category_emoji']; ?>
              <?php echo htmlspecialchars($cat['category_name']); ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Account Name / Title <span class="req">*</span></label>
              <input type="text" name="account_name" class="form-control" placeholder="e.g. My Gmail Account"
                     value="<?php echo htmlspecialchars($record['account_name'] ?? ''); ?>" required id="pm-account-name">
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Platform / Service Name</label>
              <input type="text" name="service_name" class="form-control" placeholder="e.g. Gmail, Instagram, cPanel"
                     value="<?php echo htmlspecialchars($record['service_name'] ?? ''); ?>">
            </div>
          </div>
        </div>

        <div class="pm-field-group">
          <label>Website / Login URL</label>
          <div class="pm-input-group">
            <input type="url" name="login_url" class="form-control" placeholder="https://example.com/login"
                   value="<?php echo htmlspecialchars($record['login_url'] ?? ''); ?>" id="pm-url">
            <button type="button" class="pm-input-action" onclick="openUrl()" title="Open URL">
              <i class="fa fa-external-link"></i>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Section: Login Credentials -->
    <div class="pm-form-card">
      <div class="pm-form-header">
        <div class="pm-section-icon">🔑</div>
        <div>
          <h4>Login Credentials</h4>
          <p>Username, password, PIN, 2FA</p>
        </div>
      </div>
      <div class="pm-form-body">

        <div class="row">
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Username / User ID</label>
              <div class="pm-input-group">
                <input type="text" name="username" id="pm-username" class="form-control" placeholder="username or user ID"
                       value="<?php echo htmlspecialchars($record['username'] ?? ''); ?>">
                <button type="button" class="pm-input-action" onclick="copyFieldVal('pm-username')" title="Copy">
                  <i class="fa fa-copy"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Email Address</label>
              <div class="pm-input-group">
                <input type="email" name="email" id="pm-email" class="form-control" placeholder="email@example.com"
                       value="<?php echo htmlspecialchars($record['email'] ?? ''); ?>">
                <button type="button" class="pm-input-action" onclick="copyFieldVal('pm-email')" title="Copy">
                  <i class="fa fa-copy"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Password Field -->
        <div class="pm-field-group">
          <label>Password</label>
          <div class="pm-input-group" style="display:flex; gap:0;">
            <input type="password" name="password" id="pm-password" class="form-control"
                   placeholder="<?php echo $id ? 'Leave blank to keep existing password' : 'Enter password'; ?>"
                   style="border-radius:10px 0 0 10px; padding-right:0;"
                   value="">
            <button type="button" onclick="togglePwVis()" class="pm-input-action"
                    style="position:static;width:42px;border-radius:0;border:1.5px solid #e8e0f0;border-left:none;background:#fafbff;" title="Show/Hide">
              <i class="fa fa-eye" id="pw-eye-icon"></i>
            </button>
            <button type="button" onclick="copyFieldVal('pm-password')"
                    style="position:static;width:42px;border-radius:0 10px 10px 0;border:1.5px solid #e8e0f0;border-left:none;background:#fafbff;cursor:pointer;color:#8e44ad;font-size:14px;" title="Copy">
              <i class="fa fa-copy"></i>
            </button>
          </div>
          <!-- Password Strength Indicator -->
          <div class="pm-strength-wrap" id="pw-strength-wrap" style="display:none;">
            <div class="pm-strength-bar-bg">
              <div class="pm-strength-bar" id="pw-strength-bar"></div>
            </div>
            <div class="pm-strength-label" id="pw-strength-label"></div>
          </div>
          <!-- Generate Button -->
          <div style="margin-top:8px;">
            <button type="button" class="btn btn-xs" style="background:#f0eaff;color:#8e44ad;border-radius:6px;font-weight:600;border:none;" onclick="toggleGenPanel()">
              <i class="fa fa-magic"></i> Generate Strong Password
            </button>
          </div>

          <!-- Generator Panel -->
          <div class="pm-gen-panel" id="pm-gen-panel">
            <div class="row">
              <div class="col-sm-6">
                <label>Length: <strong id="gen-len-label">16</strong></label>
                <input type="range" id="gen-length" min="8" max="64" value="16" style="width:100%;" oninput="document.getElementById('gen-len-label').textContent=this.value">
              </div>
              <div class="col-sm-6">
                <label>Include:</label>
                <div>
                  <label style="text-transform:none;letter-spacing:0;font-weight:500;margin-right:10px;"><input type="checkbox" id="gen-upper" checked> A-Z</label>
                  <label style="text-transform:none;letter-spacing:0;font-weight:500;margin-right:10px;"><input type="checkbox" id="gen-lower" checked> a-z</label>
                  <label style="text-transform:none;letter-spacing:0;font-weight:500;margin-right:10px;"><input type="checkbox" id="gen-num" checked> 0-9</label>
                  <label style="text-transform:none;letter-spacing:0;font-weight:500;"><input type="checkbox" id="gen-sym" checked> !@#</label>
                </div>
              </div>
            </div>
            <div class="pm-gen-result">
              <input type="text" id="gen-result" readonly placeholder="Generated password will appear here..." onclick="this.select()">
              <button type="button" class="pm-btn pm-btn-save" style="padding:8px 14px;font-size:13px;" onclick="generatePassword()"><i class="fa fa-refresh"></i> Generate</button>
              <button type="button" class="pm-btn pm-btn-reset" style="padding:8px 14px;font-size:13px;" onclick="useGenPassword()"><i class="fa fa-arrow-up"></i> Use</button>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>PIN / Passcode <small style="font-weight:400;color:#aaa;">(optional)</small></label>
              <div class="pm-input-group">
                <input type="password" name="pin" id="pm-pin" class="form-control" placeholder="4–8 digit PIN"
                       value="">
                <button type="button" class="pm-input-action" onclick="toggleVis('pm-pin')" title="Show/Hide">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
              <?php if ($id && $record && $record['pin']): ?>
              <small style="color:#8e44ad;"><i class="fa fa-info-circle"></i> PIN is stored. Leave blank to keep existing.</small>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>2FA Secret / Backup Codes <small style="font-weight:400;color:#aaa;">(optional)</small></label>
              <div class="pm-input-group">
                <input type="password" name="two_factor_secret" id="pm-2fa" class="form-control" placeholder="2FA secret key or codes"
                       value="">
                <button type="button" class="pm-input-action" onclick="toggleVis('pm-2fa')" title="Show/Hide">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
              <?php if ($id && $record && $record['two_factor_secret']): ?>
              <small style="color:#8e44ad;"><i class="fa fa-info-circle"></i> 2FA stored. Leave blank to keep existing.</small>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Section: Recovery Information -->
    <div class="pm-form-card">
      <div class="pm-form-header">
        <div class="pm-section-icon">🛡️</div>
        <div>
          <h4>Recovery Information</h4>
          <p>Backup email, phone, security questions</p>
        </div>
      </div>
      <div class="pm-form-body">
        <div class="row">
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Recovery Email</label>
              <input type="email" name="recovery_email" class="form-control" placeholder="recovery@example.com"
                     value="<?php echo htmlspecialchars($record['recovery_email'] ?? ''); ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Recovery Phone</label>
              <input type="text" name="recovery_phone" class="form-control" placeholder="+91 9876543210"
                     value="<?php echo htmlspecialchars($record['recovery_phone'] ?? ''); ?>">
            </div>
          </div>
        </div>
        <div class="pm-field-group">
          <label>Security Question</label>
          <input type="text" name="security_question" class="form-control" placeholder="e.g. What is your pet's name?"
                 value="<?php echo htmlspecialchars($record['security_question'] ?? ''); ?>">
        </div>
        <div class="pm-field-group">
          <label>Security Answer</label>
          <div class="pm-input-group">
            <input type="password" name="security_answer" id="pm-sec-ans" class="form-control" placeholder="Your answer (stored encrypted)"
                   value="">
            <button type="button" class="pm-input-action" onclick="toggleVis('pm-sec-ans')" title="Show/Hide">
              <i class="fa fa-eye"></i>
            </button>
          </div>
          <?php if ($id && $record && $record['security_answer']): ?>
          <small style="color:#8e44ad;"><i class="fa fa-info-circle"></i> Answer is stored. Leave blank to keep existing.</small>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Section: Additional Details -->
    <div class="pm-form-card">
      <div class="pm-form-header">
        <div class="pm-section-icon">📝</div>
        <div>
          <h4>Additional Details</h4>
          <p>Account numbers, notes, tags</p>
        </div>
      </div>
      <div class="pm-form-body">
        <div class="pm-field-group">
          <label>Account Number / Customer ID <small style="font-weight:400;color:#aaa;">(optional)</small></label>
          <input type="text" name="account_number" class="form-control" placeholder="e.g. ACC123456789"
                 value="<?php echo htmlspecialchars($record['account_number'] ?? ''); ?>">
        </div>
        <div class="pm-field-group">
          <label>Notes / Description</label>
          <textarea name="notes" class="form-control" rows="4" placeholder="Any additional notes, instructions, or context..."><?php echo htmlspecialchars($record['notes'] ?? ''); ?></textarea>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Tags <small style="font-weight:400;color:#aaa;">(comma separated)</small></label>
              <input type="text" name="tags" class="form-control" placeholder="work, personal, server..."
                     value="<?php echo htmlspecialchars($record['tags'] ?? ''); ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="pm-field-group">
              <label>Expiry Date <small style="font-weight:400;color:#aaa;">(optional)</small></label>
              <input type="date" name="expiry_date" class="form-control"
                     value="<?php echo htmlspecialchars($record['expiry_date'] ?? ''); ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.col-md-8 -->

  <!-- RIGHT: Settings Sidebar -->
  <div class="col-md-4">

    <!-- Status & Favorite -->
    <div class="pm-form-card" style="margin-bottom:16px;">
      <div class="pm-form-header">
        <div class="pm-section-icon">⚙️</div>
        <div>
          <h4>Entry Settings</h4>
          <p>Status and priority flags</p>
        </div>
      </div>
      <div class="pm-form-body">
        <div class="pm-field-group">
          <label>Status</label>
          <div class="pm-toggle-wrap">
            <label class="pm-toggle">
              <input type="checkbox" name="status" id="pm-status" value="1"
                     <?php echo (!$id || (isset($record['status']) && $record['status'])) ? 'checked' : ''; ?>>
              <span class="pm-toggle-slider"></span>
            </label>
            <span id="pm-status-label" style="font-size:14px;font-weight:600;color:#27ae60;">Active</span>
          </div>
          <p style="font-size:12px;color:#aaa;margin:8px 0 0;">Toggle to mark entry as active or inactive</p>
        </div>
        <hr class="pm-divider">
        <div class="pm-field-group">
          <label>Mark as Favorite</label>
          <div class="pm-toggle-wrap">
            <label class="pm-toggle">
              <input type="checkbox" name="is_favorite" id="pm-fav" value="1"
                     <?php echo ($record && $record['is_favorite']) ? 'checked' : ''; ?>>
              <span class="pm-toggle-slider" style=""></span>
            </label>
            <span id="pm-fav-label" style="font-size:14px;font-weight:600;color:#f1c40f;">
              <i class="fa fa-star"></i> Favorite
            </span>
          </div>
          <p style="font-size:12px;color:#aaa;margin:8px 0 0;">Pin this entry to the top of your list</p>
        </div>
      </div>
    </div>

    <!-- Security Checklist -->
    <div class="pm-form-card" style="margin-bottom:16px;">
      <div class="pm-form-header" style="background: linear-gradient(135deg,#1a3a2e,#154a3a);">
        <div class="pm-section-icon">🛡️</div>
        <div>
          <h4>Security Checklist</h4>
          <p>Best practice recommendations</p>
        </div>
      </div>
      <div class="pm-form-body" style="padding:16px 20px;">
        <ul style="list-style:none;padding:0;margin:0;font-size:13px;" id="pm-checklist">
          <li class="pm-check-item" id="chk-len" style="padding:6px 0; color:#999;">
            <i class="fa fa-circle-o" style="color:#ddd;margin-right:8px;"></i> Password ≥ 12 characters
          </li>
          <li class="pm-check-item" id="chk-upper" style="padding:6px 0; color:#999;">
            <i class="fa fa-circle-o" style="color:#ddd;margin-right:8px;"></i> Uppercase letters
          </li>
          <li class="pm-check-item" id="chk-num" style="padding:6px 0; color:#999;">
            <i class="fa fa-circle-o" style="color:#ddd;margin-right:8px;"></i> Numbers included
          </li>
          <li class="pm-check-item" id="chk-sym" style="padding:6px 0; color:#999;">
            <i class="fa fa-circle-o" style="color:#ddd;margin-right:8px;"></i> Special characters
          </li>
          <li class="pm-check-item" id="chk-2fa" style="padding:6px 0; color:#999;">
            <i class="fa fa-circle-o" style="color:#ddd;margin-right:8px;"></i> 2FA secret stored
          </li>
          <li class="pm-check-item" id="chk-recovery" style="padding:6px 0; color:#999;">
            <i class="fa fa-circle-o" style="color:#ddd;margin-right:8px;"></i> Recovery info provided
          </li>
        </ul>
      </div>
    </div>

    <!-- Category Info Card (dynamic) -->
    <div class="pm-form-card" id="pm-cat-info-card" style="margin-bottom:16px; display:none;">
      <div class="pm-form-header" id="pm-cat-info-header" style="background:linear-gradient(135deg,#2c1a4e,#1a0533);">
        <div class="pm-section-icon" id="pm-cat-info-emoji">🔑</div>
        <div>
          <h4 id="pm-cat-info-name">Category</h4>
          <p id="pm-cat-info-desc">Selected category</p>
        </div>
      </div>
    </div>

    <!-- Example Tips -->
    <div class="pm-form-card" style="border:2px dashed #ede0ff;box-shadow:none;">
      <div class="pm-form-body" style="padding:16px;">
        <h5 style="font-size:13px;font-weight:700;color:#8e44ad;margin:0 0 10px;"><i class="fa fa-lightbulb-o"></i> Quick Tips</h5>
        <ul style="padding-left:16px;font-size:12px;color:#666;margin:0;line-height:1.8;">
          <li>All fields are AES-256 encrypted at rest</li>
          <li>Use the generator for strong passwords</li>
          <li>Store 2FA secrets for account recovery</li>
          <li>Use tags to quickly find entries</li>
          <li>Set expiry for SSL certs, bank PINs</li>
        </ul>
      </div>
    </div>

  </div><!-- /.col-md-4 -->
</div><!-- /.row -->

<!-- Action Bar -->
<div class="pm-action-bar">
  <button type="button" class="pm-btn pm-btn-save" onclick="saveEntry()">
    <i class="fa fa-save"></i> <?php echo $id ? 'Update Entry' : 'Save Entry'; ?>
  </button>
  <button type="button" class="pm-btn pm-btn-reset" onclick="resetForm()">
    <i class="fa fa-undo"></i> Reset
  </button>
  <a href="<?php echo site_url('password-manager') ?>" class="pm-btn pm-btn-cancel" style="text-decoration:none;">
    <i class="fa fa-times"></i> Cancel
  </a>
  <?php if ($id): ?>
  <button type="button" class="pm-btn pm-btn-delete" onclick="deletePMRecord(<?php echo $id; ?>)" style="margin-left:auto;">
    <i class="fa fa-trash"></i> Delete Entry
  </button>
  <?php endif; ?>
  <div id="pm-form-spinner" style="display:none; align-items:center; gap:8px; color:#8e44ad; font-size:13px;">
    <i class="fa fa-spinner fa-spin"></i> Saving...
  </div>
</div>

</form>

</section><!-- /.content -->

<!-- Toast Notification -->
<div id="pm-toast"><i class="fa fa-check-circle"></i> <span id="pm-toast-msg">Saved!</span></div>

<!-- Delete Modal -->
<div class="modal fade" id="pmDeleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border-radius:12px;overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;border:none;padding:14px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-trash"></i> Confirm Delete</h4>
      </div>
      <div class="modal-body" style="padding:20px;text-align:center;">
        <p>Are you sure you want to delete this entry? <br><strong>This cannot be undone.</strong></p>
      </div>
      <div class="modal-footer" style="border:none;padding:10px 20px 20px;text-align:center;">
        <button class="btn btn-default" data-dismiss="modal" style="border-radius:8px;margin-right:8px;">Cancel</button>
        <button class="btn btn-danger" id="pm-confirm-delete" style="border-radius:8px;font-weight:600;"><i class="fa fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
var PM_BASE = '<?php echo site_url() ?>';
var pmEntryId = <?php echo $id ?: 0; ?>;
var pmDeleteId = null;

// Category info descriptions
var catDescriptions = {
  'Website':       'Login credentials for websites and web apps',
  'Email':         'Email accounts (Gmail, Outlook, Yahoo, etc.)',
  'Social Media':  'Instagram, Facebook, Twitter, LinkedIn, etc.',
  'Banking':       'Online banking portals and finance apps',
  'Payment Wallet':'Razorpay, PayPal, Stripe, Paytm, etc.',
  'Cloud Storage': 'Google Drive, Dropbox, AWS S3, etc.',
  'Server / VPS':  'SSH credentials, VPS control panels',
  'Hosting':       'cPanel, Plesk, WHM, DirectAdmin',
  'Domain':        'GoDaddy, Namecheap, domain registrars',
  'Database':      'MySQL, PostgreSQL, MongoDB credentials',
  'FTP / SFTP':    'FTP/SFTP server credentials',
  'Wi-Fi':         'Wi-Fi network passwords',
  'Office / Work': 'Work-related tools and platforms',
  'Shopping':      'Amazon, Flipkart, eBay, etc.',
  'Gaming':        'Steam, Epic, PSN, Xbox, etc.',
  'Streaming':     'Netflix, Spotify, YouTube, etc.',
  'Mobile Apps':   'Mobile application credentials',
  'Other':         'Miscellaneous accounts'
};

// Category select
function selectCategory(el, id) {
  document.querySelectorAll('.pm-cat-pill').forEach(function(p) { p.classList.remove('selected'); });
  el.classList.add('selected');
  document.getElementById('pm-category-id').value = id;

  // Show info card
  var catName = el.textContent.trim().replace(/./u, '').trim();
  var emoji   = el.textContent.trim().charAt(0);
  document.getElementById('pm-cat-info-emoji').textContent = emoji;
  document.getElementById('pm-cat-info-name').textContent  = catName;
  document.getElementById('pm-cat-info-desc').textContent  = catDescriptions[catName] || 'Credentials stored securely';
  document.getElementById('pm-cat-info-card').style.display = 'block';

  updateChecklist();
}

// Toggle show/hide password main
var pwVisible = false;
function togglePwVis() {
  var inp = document.getElementById('pm-password');
  pwVisible = !pwVisible;
  inp.type = pwVisible ? 'text' : 'password';
  document.getElementById('pw-eye-icon').className = 'fa ' + (pwVisible ? 'fa-eye-slash' : 'fa-eye');
}

// Toggle any field
function toggleVis(fieldId) {
  var inp = document.getElementById(fieldId);
  inp.type = (inp.type === 'password') ? 'text' : 'password';
}

// Copy field
function copyFieldVal(fieldId) {
  var val = document.getElementById(fieldId).value;
  if (!val) { showToast('Nothing to copy!', 'error'); return; }
  navigator.clipboard.writeText(val).then(function() {
    showToast('Copied to clipboard!');
  }).catch(function() {
    fallbackCopy(val);
  });
}

// Open URL
function openUrl() {
  var url = document.getElementById('pm-url').value;
  if (url) window.open(url, '_blank');
}

// Status toggle label
document.getElementById('pm-status').addEventListener('change', function() {
  var lbl = document.getElementById('pm-status-label');
  if (this.checked) { lbl.textContent = 'Active'; lbl.style.color = '#27ae60'; }
  else              { lbl.textContent = 'Inactive'; lbl.style.color = '#e74c3c'; }
});

// Fav toggle label
document.getElementById('pm-fav').addEventListener('change', function() {
  var lbl = document.getElementById('pm-fav-label');
  if (this.checked) { lbl.innerHTML = '<i class="fa fa-star"></i> Favorite'; lbl.style.color = '#f1c40f'; }
  else              { lbl.innerHTML = '<i class="fa fa-star-o"></i> Not Favorite'; lbl.style.color = '#aaa'; }
});

// Password strength live
document.getElementById('pm-password').addEventListener('input', function() {
  var pw = this.value;
  if (!pw) { document.getElementById('pw-strength-wrap').style.display = 'none'; return; }
  document.getElementById('pw-strength-wrap').style.display = 'block';
  $.post(PM_BASE + 'pm-check-strength', { password: pw }, function(r) {
    if (r.success) {
      var s = r.strength;
      document.getElementById('pw-strength-bar').style.width = s.pct + '%';
      document.getElementById('pw-strength-bar').style.background = s.color;
      document.getElementById('pw-strength-label').textContent = s.label;
      document.getElementById('pw-strength-label').style.color  = s.color;
    }
  });
  updateChecklist();
});

// Checklist update
function updateChecklist() {
  var pw    = document.getElementById('pm-password').value;
  var tfa   = document.getElementById('pm-2fa').value;
  var rec   = document.getElementById('pm-email') ? document.getElementById('pm-email').value : '';
  var recPh = document.querySelector('[name="recovery_phone"]').value;
  var recEm = document.querySelector('[name="recovery_email"]').value;

  function setChk(id, ok) {
    var el = document.getElementById(id);
    var icon = el.querySelector('i');
    if (ok) {
      el.style.color = '#27ae60';
      icon.className = 'fa fa-check-circle';
      icon.style.color = '#27ae60';
    } else {
      el.style.color = '#999';
      icon.className = 'fa fa-circle-o';
      icon.style.color = '#ddd';
    }
  }

  setChk('chk-len',      pw.length >= 12);
  setChk('chk-upper',    /[A-Z]/.test(pw));
  setChk('chk-num',      /[0-9]/.test(pw));
  setChk('chk-sym',      /[^A-Za-z0-9]/.test(pw));
  setChk('chk-2fa',      tfa.length > 0);
  setChk('chk-recovery', (recEm.length > 0 || recPh.length > 0));
}

// 2FA and recovery change
document.getElementById('pm-2fa').addEventListener('input', updateChecklist);
document.querySelector('[name="recovery_email"]').addEventListener('input', updateChecklist);
document.querySelector('[name="recovery_phone"]').addEventListener('input', updateChecklist);

// Generator panel
function toggleGenPanel() {
  var panel = document.getElementById('pm-gen-panel');
  panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
}

function generatePassword() {
  $.post(PM_BASE + 'pm-generate-password', {
    length:  document.getElementById('gen-length').value,
    upper:   document.getElementById('gen-upper').checked  ? '1' : '0',
    lower:   document.getElementById('gen-lower').checked  ? '1' : '0',
    numbers: document.getElementById('gen-num').checked    ? '1' : '0',
    symbols: document.getElementById('gen-sym').checked    ? '1' : '0',
  }, function(r) {
    if (r.success) {
      document.getElementById('gen-result').value = r.password;
    }
  });
}

function useGenPassword() {
  var pw = document.getElementById('gen-result').value;
  if (!pw) { showToast('Generate a password first!', 'error'); return; }
  document.getElementById('pm-password').value = pw;
  document.getElementById('pm-password').dispatchEvent(new Event('input'));
  document.getElementById('pm-gen-panel').style.display = 'none';
  showToast('Password applied!');
}

// Save
function saveEntry() {
  var catId = document.getElementById('pm-category-id').value;
  var name  = document.getElementById('pm-account-name').value.trim();
  if (!catId) { showToast('Please select a category!', 'error'); return; }
  if (!name)  { showToast('Account Name is required!', 'error'); return; }

  var formData = $('#pm-form').serialize();
  // Add status checkbox value explicitly
  if (!$('#pm-status').is(':checked')) formData += '&status=0';
  if (!$('#pm-fav').is(':checked'))    formData += '&is_favorite=0';

  document.getElementById('pm-form-spinner').style.display = 'flex';
  $('[onclick="saveEntry()"]').prop('disabled', true);

  $.post(PM_BASE + 'pm-save', formData, function(r) {
    document.getElementById('pm-form-spinner').style.display = 'none';
    $('[onclick="saveEntry()"]').prop('disabled', false);
    if (r.success) {
      showToast(r.message, 'success');
      setTimeout(function() {
        window.location = PM_BASE + 'password-manager';
      }, 900);
    } else {
      showToast('Error: ' + r.message, 'error');
    }
  });
}

// Reset form
function resetForm() {
  if (!confirm('Reset all fields?')) return;
  document.getElementById('pm-form').reset();
  document.querySelectorAll('.pm-cat-pill').forEach(function(p) { p.classList.remove('selected'); });
  document.getElementById('pm-category-id').value = '';
  document.getElementById('pw-strength-wrap').style.display = 'none';
  document.getElementById('pm-cat-info-card').style.display = 'none';
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
      showToast('Entry deleted.');
      setTimeout(function() { window.location = PM_BASE + 'password-manager'; }, 800);
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
  ta.value = text; ta.style.position='fixed'; ta.style.opacity='0';
  document.body.appendChild(ta); ta.focus(); ta.select();
  document.execCommand('copy'); document.body.removeChild(ta);
  showToast('Copied to clipboard!');
}

// Init on load
$(function() {
  // Initial checklist state on edit
  updateChecklist();
  // Status/fav initial labels
  if (!$('#pm-status').is(':checked')) {
    document.getElementById('pm-status-label').textContent = 'Inactive';
    document.getElementById('pm-status-label').style.color = '#e74c3c';
  }
  if (!$('#pm-fav').is(':checked')) {
    document.getElementById('pm-fav-label').innerHTML = '<i class="fa fa-star-o"></i> Not Favorite';
    document.getElementById('pm-fav-label').style.color = '#aaa';
  }
  // Show cat info card if editing with a category
  var selPill = document.querySelector('.pm-cat-pill.selected');
  if (selPill) {
    document.getElementById('pm-cat-info-card').style.display = 'block';
    var catName = selPill.textContent.trim().substring(2).trim();
    document.getElementById('pm-cat-info-name').textContent = catName;
    document.getElementById('pm-cat-info-desc').textContent = catDescriptions[catName] || '';
    document.getElementById('pm-cat-info-emoji').textContent = selPill.textContent.trim().charAt(0);
  }
});
</script>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

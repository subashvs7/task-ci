<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1>
    <i class="fa fa-address-book" style="color:#e67e22;"></i> Contact Book
    <span style="font-size:12px; background:linear-gradient(135deg,#e67e22,#d35400); color:#fff; border-radius:20px; padding:2px 10px; vertical-align:middle; font-weight:700; margin-left:10px;">ADMIN ONLY</span>
  </h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Contact Book</li>
  </ol>
</section>

<section class="content">

<style>
/* ===== Custom Contact Book Styling ===== */
.cb-wrapper {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}

@media(max-width: 991px) {
  .cb-wrapper {
    flex-direction: column;
  }
  .cb-left-panel, .cb-right-panel {
    width: 100% !important;
  }
}

/* Left panel form styling */
.cb-left-panel {
  width: 380px;
  flex-shrink: 0;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(230,126,34,0.08);
  overflow: hidden;
  position: sticky;
  top: 20px;
  transition: all 0.3s ease;
}

.cb-panel-header {
  background: linear-gradient(135deg, #2c1a11 0%, #4a2711 60%, #2c1a11 100%);
  color: #fff;
  padding: 20px 24px;
}
.cb-panel-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 0.4px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.cb-panel-header p {
  margin: 4px 0 0;
  font-size: 12px;
  opacity: 0.75;
}

.cb-panel-body {
  padding: 24px;
}

/* Right panel listing styling */
.cb-right-panel {
  flex-grow: 1;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.04);
  padding: 24px;
  min-height: 500px;
}

/* Form controls */
.cb-field {
  margin-bottom: 18px;
}
.cb-field label {
  font-size: 11px;
  font-weight: 700;
  color: #777;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin-bottom: 6px;
  display: block;
}
.cb-field label .req {
  color: #e74c3c;
  margin-left: 2px;
}
.cb-field .form-control {
  border: 1.5px solid #ebdcd0;
  border-radius: 10px;
  height: 40px;
  font-size: 14px;
  color: #2c1a11;
  background: #fdfbf7;
  transition: all 0.25s ease;
}
.cb-field textarea.form-control {
  height: auto;
  resize: vertical;
}
.cb-field .form-control:focus {
  border-color: #e67e22;
  box-shadow: 0 0 0 3px rgba(230,126,34,0.12);
  background: #fff;
  outline: none;
}

/* Toggle Switches */
.cb-toggle-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fdfaf7;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1.5px dashed #ebdcd0;
}
.cb-switch-lbl {
  font-size: 13px;
  font-weight: 700;
  color: #555;
}
.cb-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  margin: 0;
}
.cb-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.cb-slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .3s;
  border-radius: 24px;
}
.cb-slider:before {
  position: absolute;
  content: "";
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: .3s;
  border-radius: 50%;
}
input:checked + .cb-slider {
  background-color: #e67e22;
}
input:checked + .cb-slider:before {
  transform: translateX(20px);
}

/* Form Buttons */
.cb-btn-save {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #e67e22, #d35400);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(230,126,34,0.3);
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 10px;
}
.cb-btn-save:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(230,126,34,0.4);
}
.cb-btn-reset {
  width: 100%;
  padding: 10px;
  background: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 10px;
}
.cb-btn-reset:hover {
  background: #eaeaea;
  color: #333;
}

/* Stats Cards */
.cb-stats-row {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}
.cb-stat-card {
  flex: 1;
  padding: 16px 20px;
  border-radius: 12px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  overflow: hidden;
  position: relative;
}
.cb-stat-1 { background: linear-gradient(135deg, #e67e22, #f39c12); }
.cb-stat-2 { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.cb-stat-3 { background: linear-gradient(135deg, #7f8c8d, #95a5a6); }

.cb-stat-card .info h3 { margin: 0; font-size: 26px; font-weight: 800; }
.cb-stat-card .info p { margin: 2px 0 0; font-size: 12px; opacity: 0.85; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
.cb-stat-card .icon { font-size: 32px; opacity: 0.25; }

/* Filter & Search Bar */
.cb-filter-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  align-items: center;
}
.cb-search-box {
  flex: 1;
  position: relative;
}
.cb-search-box i {
  position: absolute; left: 14px; top: 12px; color: #aaa; font-size: 14px;
}
.cb-search-box .form-control {
  padding-left: 38px;
  border-radius: 10px;
  height: 40px;
  border: 1.5px solid #ebdcd0;
}
.cb-filter-select {
  width: 150px;
  border-radius: 10px;
  height: 40px;
  border: 1.5px solid #ebdcd0;
}

/* Contact List Card Grid */
.cb-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 10px;
}

.cb-card {
  background: #fdfcf9;
  border: 1.5px solid #f2ece4;
  border-radius: 14px;
  padding: 16px;
  transition: all 0.2s ease;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.cb-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(230,126,34,0.08);
  border-color: #ebdcd0;
}

.cb-card-header {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 12px;
}

/* Color initials generator */
.cb-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  color: #fff;
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-transform: uppercase;
  flex-shrink: 0;
}

.cb-card-name {
  font-size: 15px;
  font-weight: 700;
  color: #2c1a11;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cb-card-title-sub {
  font-size: 11px;
  color: #888;
  margin: 1px 0 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cb-card-body {
  font-size: 12px;
  color: #555;
  margin-bottom: 12px;
}
.cb-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}
.cb-info-row:last-child { margin-bottom: 0; }
.cb-info-row i { color: #e67e22; width: 14px; text-align: center; }
.cb-info-row span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.cb-card-footer {
  border-top: 1px dashed #f2ece4;
  padding-top: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cb-cat-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  text-transform: uppercase;
}

/* Category styling */
.cat-general  { background: #e8e8e8; color: #555; }
.cat-client   { background: #d4e6f1; color: #1f618d; }
.cat-supplier { background: #d5f5e3; color: #1e8449; }
.cat-team     { background: #ebd8fc; color: #7d3c98; }
.cat-partner  { background: #fdebd0; color: #b9770e; }
.cat-personal { background: #fcd0cf; color: #c0392b; }

.cb-card-actions {
  display: flex;
  gap: 6px;
}
.cb-act-btn {
  background: none;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: pointer;
  transition: background 0.15s;
}
.cb-btn-edit-act { color: #e67e22; }
.cb-btn-edit-act:hover { background: #fdf2e9; }
.cb-btn-del-act  { color: #e74c3c; }
.cb-btn-del-act:hover  { background: #fdedec; }

/* Status circle indicator */
.cb-status-indicator {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.cb-status-active { background: #2ecc71; box-shadow: 0 0 8px #2ecc71; }
.cb-status-inactive { background: #95a5a6; }

/* Toast */
#cb-toast {
  position: fixed;
  bottom: 22px;
  right: 22px;
  z-index: 9999;
  padding: 12px 24px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  box-shadow: 0 4px 18px rgba(0,0,0,0.2);
  display: none;
  align-items: center;
  gap: 8px;
  animation: cbSlideUp 0.3s ease;
}
#cb-toast.show { display: flex; }
@keyframes cbSlideUp {
  from { opacity: 0; transform: translateY(15px); }
  to   { opacity: 1; transform: translateY(0); }
}

.cb-empty {
  text-align: center;
  padding: 40px;
  color: #aaa;
}
.cb-empty-icon { font-size: 48px; opacity: 0.25; margin-bottom: 12px; }
</style>

<div class="cb-wrapper">

  <!-- ===== LEFT: Add / Edit Form Panel ===== -->
  <div class="cb-left-panel">
    <div class="cb-panel-header">
      <h4 id="cb-form-title"><i class="fa fa-plus-circle"></i> Add New Contact</h4>
      <p id="cb-form-subtitle">Store a new contact details securely</p>
    </div>
    
    <div class="cb-panel-body">
      <input type="hidden" id="f-id" value="0">

      <!-- Full Name -->
      <div class="cb-field">
        <label>Full Name <span class="req">*</span></label>
        <input type="text" id="f-name" class="form-control" placeholder="e.g. John Doe" required>
      </div>

      <!-- Phone Number -->
      <div class="cb-field">
        <label>Phone Number</label>
        <input type="text" id="f-phone" class="form-control" placeholder="e.g. +1 (555) 019-2834">
      </div>

      <!-- Email Address -->
      <div class="cb-field">
        <label>Email Address</label>
        <input type="email" id="f-email" class="form-control" placeholder="e.g. johndoe@company.com">
      </div>

      <!-- Company & Job Title (Row) -->
      <div class="row">
        <div class="col-xs-6" style="padding-right: 6px;">
          <div class="cb-field">
            <label>Company</label>
            <input type="text" id="f-company" class="form-control" placeholder="e.g. Acme Corp">
          </div>
        </div>
        <div class="col-xs-6" style="padding-left: 6px;">
          <div class="cb-field">
            <label>Job Title</label>
            <input type="text" id="f-job-title" class="form-control" placeholder="e.g. Developer">
          </div>
        </div>
      </div>

      <!-- Category -->
      <div class="cb-field">
        <label>Category Group</label>
        <select id="f-category" class="form-control">
          <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Address -->
      <div class="cb-field">
        <label>Physical Address</label>
        <textarea id="f-address" class="form-control" rows="2" placeholder="e.g. 123 Main St, New York, NY"></textarea>
      </div>

      <!-- Notes -->
      <div class="cb-field">
        <label>Private Notes</label>
        <textarea id="f-notes" class="form-control" rows="2" placeholder="Private notes or details..."></textarea>
      </div>

      <!-- Status Toggle -->
      <div class="cb-field">
        <div class="cb-toggle-container">
          <span class="cb-switch-lbl">Status: <span id="f-status-lbl" style="color:#2ecc71;">Active</span></span>
          <label class="cb-switch">
            <input type="checkbox" id="f-status" checked onchange="toggleStatusLbl(this.checked)">
            <span class="cb-slider"></span>
          </label>
        </div>
      </div>

      <!-- Buttons -->
      <button class="cb-btn-save" id="cb-save-btn" onclick="saveContact()">
        <i class="fa fa-save"></i> <span id="cb-save-label">Save Contact</span>
      </button>
      <button class="cb-btn-reset" onclick="resetForm()">
        <i class="fa fa-undo"></i> Reset Form
      </button>

    </div>
  </div><!-- /.cb-left-panel -->

  <!-- ===== RIGHT: Contact Cards Listing Panel ===== -->
  <div class="cb-right-panel">
    
    <!-- Stats Row -->
    <div class="cb-stats-row">
      <div class="cb-stat-card cb-stat-1">
        <div class="info">
          <h3 id="stat-total"><?php echo (int)$total_count; ?></h3>
          <p>Total Contacts</p>
        </div>
        <i class="fa fa-address-book icon"></i>
      </div>
      <div class="cb-stat-card cb-stat-2">
        <div class="info">
          <h3 id="stat-active"><?php echo (int)$active_count; ?></h3>
          <p>Active</p>
        </div>
        <i class="fa fa-check-circle icon"></i>
      </div>
      <div class="cb-stat-card cb-stat-3">
        <div class="info">
          <h3 id="stat-inactive"><?php echo (int)$inactive_count; ?></h3>
          <p>Inactive</p>
        </div>
        <i class="fa fa-times-circle icon"></i>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="cb-filter-bar">
      <div class="cb-search-box">
        <i class="fa fa-search"></i>
        <input type="text" id="cb-search" class="form-control" placeholder="Search contacts by name, company, phone, email..." oninput="filterContacts(this.value)">
      </div>
      <select id="cb-filter-cat" class="form-control cb-filter-select" onchange="applyFilters()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
        <?php endforeach; ?>
      </select>
      <select id="cb-filter-status" class="form-control cb-filter-select" onchange="applyFilters()">
        <option value="">All Status</option>
        <option value="1">Active Only</option>
        <option value="0">Inactive Only</option>
      </select>
    </div>

    <!-- Grid Container -->
    <div id="cb-cards-grid">
      <?php if (empty($contacts)): ?>
      <div class="cb-empty" id="cb-empty-state">
        <div class="cb-empty-icon">👥</div>
        <h4>No contacts found</h4>
        <p>Start adding contacts using the form on the left.</p>
      </div>
      <?php else: ?>
      <div class="cb-grid" id="cb-grid-container">
        <?php foreach ($contacts as $c): ?>
        <?php 
          // Extract initials
          $words = explode(' ', $c['name']);
          $initials = '';
          foreach ($words as $w) {
              $initials .= strtoupper(substr($w, 0, 1));
          }
          $initials = substr($initials, 0, 2);
        ?>
        <div class="cb-card" data-id="<?php echo $c['id']; ?>" 
             data-name="<?php echo htmlspecialchars(strtolower($c['name'])); ?>"
             data-company="<?php echo htmlspecialchars(strtolower($c['company'] ?? '')); ?>"
             data-email="<?php echo htmlspecialchars(strtolower($c['email'] ?? '')); ?>"
             data-phone="<?php echo htmlspecialchars(strtolower($c['phone'] ?? '')); ?>"
             data-category="<?php echo htmlspecialchars($c['category']); ?>"
             data-status="<?php echo (int)$c['status']; ?>">
          
          <!-- Status dot -->
          <div class="cb-status-indicator <?php echo $c['status'] ? 'cb-status-active' : 'cb-status-inactive'; ?>"></div>

          <!-- Header -->
          <div class="cb-card-header">
            <div class="cb-avatar" style="background:<?php echo get_avatar_color($c['id']); ?>;">
              <?php echo htmlspecialchars($initials); ?>
            </div>
            <div style="flex:1; overflow:hidden;">
              <h4 class="cb-card-name" title="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></h4>
              <p class="cb-card-title-sub">
                <?php if ($c['job_title']): ?>
                  <?php echo htmlspecialchars($c['job_title']); ?>
                  <?php if ($c['company']) echo ' at ' . htmlspecialchars($c['company']); ?>
                <?php else: ?>
                  <?php echo htmlspecialchars($c['company'] ?? 'No Company'); ?>
                <?php endif; ?>
              </p>
            </div>
          </div>

          <!-- Body -->
          <div class="cb-card-body">
            <?php if ($c['phone']): ?>
            <div class="cb-info-row">
              <i class="fa fa-phone"></i>
              <span title="<?php echo htmlspecialchars($c['phone']); ?>"><?php echo htmlspecialchars($c['phone']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($c['email']): ?>
            <div class="cb-info-row">
              <i class="fa fa-envelope"></i>
              <span title="<?php echo htmlspecialchars($c['email']); ?>"><?php echo htmlspecialchars($c['email']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($c['address']): ?>
            <div class="cb-info-row">
              <i class="fa fa-map-marker"></i>
              <span title="<?php echo htmlspecialchars($c['address']); ?>"><?php echo htmlspecialchars($c['address']); ?></span>
            </div>
            <?php endif; ?>
          </div>

          <!-- Footer -->
          <div class="cb-card-footer">
            <span class="cb-cat-badge cat-<?php echo strtolower($c['category']); ?>">
              <?php echo htmlspecialchars($c['category']); ?>
            </span>
            <div class="cb-card-actions">
              <button class="cb-act-btn cb-btn-edit-act" title="Edit Contact" onclick="editContact(<?php echo $c['id']; ?>)">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="cb-act-btn cb-btn-del-act" title="Delete Contact" onclick="deleteContact(<?php echo $c['id']; ?>, '<?php echo addslashes($c['name']); ?>')">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>

        </div>
        <?php endforeach; ?>
      </div><!-- /.cb-grid -->
      <?php endif; ?>
    </div><!-- /#cb-cards-grid -->

  </div><!-- /.cb-right-panel -->

</div><!-- /.cb-wrapper -->

<!-- Contact Delete Confirmation Modal -->
<div class="modal fade" id="cbDeleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border-radius:12px; overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff; border:none; padding:14px 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.8;"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-trash"></i> Delete Contact</h4>
      </div>
      <div class="modal-body" style="padding:20px; text-align:center;">
        <p style="font-size:15px;">Delete contact <strong id="del-contact-name" style="color:#e67e22;">"?"</strong>?<br>
        <small style="color:#999;">This action cannot be undone.</small></p>
      </div>
      <div class="modal-footer" style="border:none; padding:10px 20px 20px; text-align:center;">
        <button class="btn btn-default" data-dismiss="modal" style="border-radius:8px; margin-right:8px;">Cancel</button>
        <button class="btn btn-danger" id="cb-confirm-del" style="border-radius:8px; font-weight:600;"><i class="fa fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>

</section>

<!-- Toast -->
<div id="cb-toast"><i class="fa fa-check-circle"></i> <span id="cb-toast-msg">Success!</span></div>

<?php 
// Avatar color generator based on ID to keep colors consistent
function get_avatar_color($id) {
    $colors = [
        '#3498db', '#e74c3c', '#2ecc71', '#9b59b6', '#f1c40f', 
        '#1abc9c', '#e67e22', '#34495e', '#16a085', '#27ae60'
    ];
    return $colors[$id % count($colors)];
}
?>

<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

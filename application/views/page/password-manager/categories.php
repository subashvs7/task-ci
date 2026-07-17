<?php include_once(VIEWPATH . 'inc/header.php'); ?>

<section class="content-header">
  <h1><i class="fa fa-tags" style="color:#8e44ad;"></i> Manage Categories <small>Password Manager Master</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo site_url('dash') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo site_url('password-manager') ?>">Password Manager</a></li>
    <li class="active">Manage Categories</li>
  </ol>
</section>

<section class="content">

<style>
/* ========== Category Master Styles ========== */
.cat-page-wrap { display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap; }

/* ---- Left: Form Panel ---- */
.cat-form-panel {
  flex: 0 0 340px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(142,68,173,0.12);
  overflow: hidden;
  position: sticky;
  top: 10px;
}
.cat-form-header {
  background: linear-gradient(135deg, #1a0533 0%, #2c0f5e 60%, #1a1a4e 100%);
  color: #fff;
  padding: 20px 24px;
}
.cat-form-header h4 { margin: 0 0 4px; font-size: 17px; font-weight: 700; }
.cat-form-header p  { margin: 0; font-size: 12px; opacity: 0.7; }
.cat-form-body { padding: 22px 24px; }

.cat-field { margin-bottom: 18px; }
.cat-field label {
  display: block; font-size: 11px; font-weight: 700; color: #666;
  text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;
}
.cat-field label .req { color: #e74c3c; }
.cat-field .form-control {
  border: 1.5px solid #e8e0f0; border-radius: 10px;
  height: 42px; font-size: 14px; color: #2c1a4e;
  background: #fafbff; transition: border 0.2s, box-shadow 0.2s;
}
.cat-field .form-control:focus {
  border-color: #8e44ad; box-shadow: 0 0 0 3px rgba(142,68,173,0.12); outline: none;
}

/* Emoji picker strip */
.emoji-strip {
  display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px;
}
.emoji-chip {
  width: 34px; height: 34px; border-radius: 8px;
  border: 2px solid #e8e0f0; background: #fafbff;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; cursor: pointer; transition: all 0.15s;
}
.emoji-chip:hover    { border-color: #8e44ad; background: #f4eeff; transform: scale(1.1); }
.emoji-chip.selected { border-color: #8e44ad; background: #8e44ad; }

/* Icon picker */
.icon-strip {
  display: grid; grid-template-columns: repeat(6,1fr); gap: 5px; margin-top: 8px;
}
.icon-chip {
  height: 34px; border-radius: 8px;
  border: 2px solid #e8e0f0; background: #fafbff;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; cursor: pointer; color: #555; transition: all 0.15s;
}
.icon-chip:hover    { border-color: #8e44ad; color: #8e44ad; background: #f4eeff; }
.icon-chip.selected { border-color: #8e44ad; background: #8e44ad; color: #fff; }

/* Preview badge */
.cat-preview {
  display: flex; align-items: center; gap: 12px;
  background: linear-gradient(135deg,#f9f5ff,#f0eaff);
  border: 1.5px solid #e0d0f8; border-radius: 10px;
  padding: 12px 16px; margin-bottom: 18px;
}
.cat-preview-icon {
  width: 44px; height: 44px; border-radius: 10px;
  background: linear-gradient(135deg,#8e44ad,#6c3483);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
}
.cat-preview-name { font-size: 15px; font-weight: 700; color: #2c1a4e; }
.cat-preview-icon-lbl { font-size: 12px; color: #888; }

/* Save button */
.cat-save-btn {
  width: 100%; border-radius: 10px; font-weight: 700; font-size: 15px;
  padding: 12px; border: none; cursor: pointer;
  background: linear-gradient(135deg,#8e44ad,#6c3483);
  color: #fff; box-shadow: 0 4px 14px rgba(142,68,173,0.35);
  transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.cat-save-btn:hover { background: linear-gradient(135deg,#7d3c98,#5b2c6f); transform: translateY(-1px); }
.cat-reset-btn {
  width: 100%; border-radius: 10px; font-weight: 600; font-size: 14px;
  padding: 10px; border: 1.5px solid #e8e0f0; cursor: pointer;
  background: #fff; color: #666; margin-top: 8px;
  transition: all 0.15s;
}
.cat-reset-btn:hover { background: #f4eeff; border-color: #8e44ad; color: #8e44ad; }

/* ---- Right: List Panel ---- */
.cat-list-panel {
  flex: 1; min-width: 0;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(142,68,173,0.10);
  overflow: hidden;
}
.cat-list-header {
  background: linear-gradient(135deg,#f9f5ff,#f0eaff);
  padding: 16px 22px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid #ede0ff;
}
.cat-list-header h4 { margin: 0; font-size: 15px; font-weight: 700; color: #2c1a4e; }
.cat-list-header .cat-count-badge {
  background: linear-gradient(135deg,#8e44ad,#6c3483);
  color: #fff; border-radius: 20px; font-size: 12px;
  padding: 3px 12px; font-weight: 700;
}

/* Search bar */
.cat-search-bar {
  padding: 12px 22px;
  border-bottom: 1px solid #f5f0ff;
  display: flex; gap: 10px;
}
.cat-search-bar input {
  flex: 1; border: 1.5px solid #e8e0f0; border-radius: 8px;
  height: 36px; padding: 0 12px 0 34px; font-size: 13px;
  background: #fafbff; color: #333;
}
.cat-search-bar .search-wrap { position: relative; flex: 1; }
.cat-search-bar .search-wrap .fa { position: absolute; left: 11px; top: 10px; color: #aaa; }

/* Table */
.cat-table { width: 100%; border-collapse: collapse; }
.cat-table thead tr th {
  background: #f8f4ff; color: #888;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
  padding: 11px 16px; border-bottom: 1px solid #ede0ff;
}
.cat-table tbody tr {
  border-bottom: 1px solid #f5f0ff;
  transition: background 0.15s;
  cursor: grab;
}
.cat-table tbody tr:hover  { background: #fdfaff; }
.cat-table tbody tr.ui-sortable-helper { box-shadow: 0 8px 20px rgba(142,68,173,0.2); background: #fff; cursor: grabbing; }
.cat-table td { padding: 11px 16px; vertical-align: middle; }

.cat-icon-badge {
  width: 38px; height: 38px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; flex-shrink: 0;
}
.cat-name-cell { display: flex; align-items: center; gap: 10px; }
.cat-usage-pill {
  background: #ede0ff; color: #8e44ad;
  border-radius: 20px; font-size: 11px; padding: 2px 10px; font-weight: 600;
}
.cat-usage-pill.zero { background: #f0f0f0; color: #aaa; }
.cat-action-btns { display: flex; gap: 6px; align-items: center; }
.cat-btn-edit {
  width: 30px; height: 30px; border-radius: 8px;
  background: #e8f4fd; color: #2980b9; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 13px;
  transition: all 0.15s;
}
.cat-btn-edit:hover { background: #2980b9; color: #fff; }
.cat-btn-del {
  width: 30px; height: 30px; border-radius: 8px;
  background: #fdecea; color: #e74c3c; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center; font-size: 13px;
  transition: all 0.15s;
}
.cat-btn-del:hover { background: #e74c3c; color: #fff; }
.cat-drag-handle {
  color: #ccc; cursor: grab; padding: 0 6px;
}
.cat-drag-handle:hover { color: #8e44ad; }

/* Sort order input */
.sort-input {
  width: 52px; border: 1.5px solid #e8e0f0; border-radius: 6px;
  height: 30px; text-align: center; font-size: 13px; color: #555;
  background: #fafbff;
}

/* Empty state */
.cat-empty { text-align: center; padding: 50px 20px; color: #bbb; }

/* Toast */
#cat-toast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  background: #2c1a4e; color: #fff;
  padding: 12px 22px; border-radius: 10px; font-size: 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  display: none; align-items: center; gap: 10px; max-width: 340px;
}
#cat-toast.show { display: flex; animation: slideUp 0.3s ease; }
@keyframes slideUp {
  from { opacity:0; transform: translateY(16px); }
  to   { opacity:1; transform: translateY(0); }
}

/* Delete modal */
.modal-content { border-radius: 12px !important; overflow: hidden; }
</style>

<!-- Page Content -->
<div class="cat-page-wrap">

  <!-- ===== LEFT: Add / Edit Form ===== -->
  <div class="cat-form-panel">
    <div class="cat-form-header">
      <h4 id="cat-form-title"><i class="fa fa-plus-circle"></i> Add New Category</h4>
      <p id="cat-form-subtitle">Fill in details and click Save</p>
    </div>
    <div class="cat-form-body">

      <input type="hidden" id="f-cat-id" value="0">

      <!-- Live Preview -->
      <div class="cat-preview">
        <div class="cat-preview-icon" id="prev-icon">🔑</div>
        <div>
          <div class="cat-preview-name" id="prev-name">Category Name</div>
          <div class="cat-preview-icon-lbl" id="prev-icon-lbl"><i class="fa fa-key"></i> fa-key</div>
        </div>
      </div>

      <!-- Category Name -->
      <div class="cat-field">
        <label>Category Name <span class="req">*</span></label>
        <input type="text" id="f-cat-name" class="form-control" placeholder="e.g. Banking, Wi-Fi, Server..."
               oninput="document.getElementById('prev-name').textContent = this.value || 'Category Name';">
      </div>

      <!-- Emoji Picker -->
      <div class="cat-field">
        <label>Emoji Icon <span class="req">*</span></label>
        <input type="text" id="f-cat-emoji" class="form-control" maxlength="10" placeholder="Pick below or type emoji"
               oninput="updatePreview()" style="font-size:20px; text-align:center; letter-spacing:4px;">
        <div class="emoji-strip" id="emoji-strip">
          <?php
          $emojis = ['🌐','📧','📱','🏦','💳','☁️','🖥️','🔐','🌍','📂','📡','📶','💼','🛒','🎮','📺','📝','🔑','🔒','⚙️','🛡️','🏠','🚀','💰','🎯','📊','🔗','🌟','💡','🎵'];
          foreach ($emojis as $em): ?>
          <div class="emoji-chip" onclick="selectEmoji('<?php echo $em; ?>')"><?php echo $em; ?></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Font Awesome Icon -->
      <div class="cat-field">
        <label>Font Awesome Icon</label>
        <div style="position:relative;">
          <input type="text" id="f-cat-icon" class="form-control" placeholder="fa-key"
                 oninput="updatePreview()">
          <span style="position:absolute;right:12px;top:11px;color:#8e44ad;font-size:13px;">
            <i id="f-icon-preview" class="fa fa-key"></i>
          </span>
        </div>
        <div class="icon-strip" id="icon-strip">
          <?php
          $icons = ['fa-globe','fa-envelope','fa-share-alt','fa-bank','fa-credit-card','fa-cloud',
                    'fa-server','fa-hdd-o','fa-database','fa-exchange','fa-wifi','fa-briefcase',
                    'fa-shopping-cart','fa-gamepad','fa-play-circle','fa-mobile','fa-key','fa-lock',
                    'fa-shield','fa-cog','fa-home','fa-bolt','fa-star','fa-tags','fa-sitemap','fa-bar-chart'];
          foreach ($icons as $ic): ?>
          <div class="icon-chip" onclick="selectIcon('<?php echo $ic; ?>')" title="<?php echo $ic; ?>">
            <i class="fa <?php echo $ic; ?>"></i>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Sort Order -->
      <div class="cat-field">
        <label>Sort Order</label>
        <input type="number" id="f-cat-sort" class="form-control" placeholder="0 = auto" min="0" max="999">
      </div>

      <!-- Buttons -->
      <button class="cat-save-btn" id="cat-save-btn" onclick="saveCategory()">
        <i class="fa fa-save"></i> <span id="cat-save-label">Save Category</span>
      </button>
      <button class="cat-reset-btn" onclick="resetCatForm()">
        <i class="fa fa-undo"></i> Reset / New
      </button>

    </div>
  </div><!-- /.cat-form-panel -->

  <!-- ===== RIGHT: Category List ===== -->
  <div class="cat-list-panel">
    <div class="cat-list-header">
      <h4><i class="fa fa-list-ul" style="color:#8e44ad;margin-right:8px;"></i> All Categories</h4>
      <span class="cat-count-badge" id="cat-total-badge"><?php echo count($categories); ?> Total</span>
    </div>

    <!-- Search bar -->
    <div class="cat-search-bar">
      <div class="search-wrap">
        <i class="fa fa-search"></i>
        <input type="text" id="cat-search" placeholder="Search categories..." oninput="filterCategories(this.value)">
      </div>
      <button class="btn btn-default btn-sm" onclick="saveAllSortOrders()" style="border-radius:8px; font-weight:600; white-space:nowrap;">
        <i class="fa fa-sort-amount-asc"></i> Save Order
      </button>
    </div>

    <!-- Info banner -->
    <div style="padding: 10px 22px; background:#fffdf0; border-bottom:1px solid #fde68a; font-size:12px; color:#92400e;">
      <i class="fa fa-info-circle" style="color:#d97706;"></i>
      Drag &amp; drop rows to reorder, or edit the sort numbers and click <strong>Save Order</strong>.
      Categories used by passwords cannot be deleted.
    </div>

    <!-- Table -->
    <div style="overflow-x:auto;">
      <table class="cat-table">
        <thead>
          <tr>
            <th style="width:32px;"></th>
            <th style="width:48px;">#</th>
            <th>Category</th>
            <th style="width:90px;">FA Icon</th>
            <th style="width:80px;">Sort</th>
            <th style="width:80px; text-align:center;">Usage</th>
            <th style="width:90px; text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody id="cat-tbody">
        <?php if (empty($categories)): ?>
          <tr><td colspan="7"><div class="cat-empty"><div style="font-size:50px;opacity:0.2;">🏷️</div><h4>No categories yet.</h4><p>Add your first category using the form on the left.</p></div></td></tr>
        <?php else: ?>
        <?php foreach ($categories as $cat): ?>
        <tr data-id="<?php echo $cat['category_id']; ?>" class="cat-row">
          <td><span class="cat-drag-handle"><i class="fa fa-bars"></i></span></td>
          <td style="color:#aaa; font-size:12px;"><?php echo $cat['category_id']; ?></td>
          <td>
            <div class="cat-name-cell">
              <div class="cat-icon-badge" style="background:<?php echo _pm_cat_grad($cat['category_id']); ?>;">
                <?php echo $cat['category_emoji'] ?: '🔑'; ?>
              </div>
              <div>
                <div style="font-weight:700; color:#2c1a4e; font-size:14px;"><?php echo htmlspecialchars($cat['category_name']); ?></div>
                <div style="font-size:11px; color:#aaa;"><?php echo $cat['category_emoji'] ?: '—'; ?></div>
              </div>
            </div>
          </td>
          <td>
            <span style="font-size:13px; color:#666;">
              <i class="fa <?php echo htmlspecialchars($cat['category_icon']); ?>" style="color:#8e44ad; margin-right:5px;"></i>
              <code style="font-size:11px; background:#f0eaff; padding:2px 6px; border-radius:4px; color:#8e44ad;"><?php echo htmlspecialchars($cat['category_icon']); ?></code>
            </span>
          </td>
          <td>
            <input type="number" class="sort-input sort-order-input"
                   data-id="<?php echo $cat['category_id']; ?>"
                   value="<?php echo (int)$cat['sort_order']; ?>" min="0" max="999">
          </td>
          <td style="text-align:center;">
            <span class="cat-usage-pill <?php echo $cat['usage_count'] == 0 ? 'zero' : ''; ?>">
              <?php echo (int)$cat['usage_count']; ?>
            </span>
          </td>
          <td style="text-align:center;">
            <div class="cat-action-btns" style="justify-content:center;">
              <button class="cat-btn-edit cat-edit-trigger" title="Edit"
                      data-id="<?php echo (int)$cat['category_id']; ?>"
                      data-name="<?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES); ?>"
                      data-icon="<?php echo htmlspecialchars($cat['category_icon'], ENT_QUOTES); ?>"
                      data-emoji="<?php echo htmlspecialchars($cat['category_emoji'], ENT_QUOTES); ?>"
                      data-sort="<?php echo (int)$cat['sort_order']; ?>">
                <i class="fa fa-pencil"></i>
              </button>
              <button class="cat-btn-del" title="Delete" onclick="deleteCategory(<?php echo $cat['category_id']; ?>, '<?php echo addslashes($cat['category_name']); ?>', <?php echo (int)$cat['usage_count']; ?>)">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div><!-- /overflow-x -->

  </div><!-- /.cat-list-panel -->
</div><!-- /.cat-page-wrap -->

</section><!-- /.content -->

<!-- Toast -->
<div id="cat-toast"><i class="fa fa-check-circle"></i> <span id="cat-toast-msg">Done!</span></div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="catDeleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;border:none;padding:14px 20px;border-radius:0;">
        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-trash"></i> Delete Category</h4>
      </div>
      <div class="modal-body" style="padding:20px; text-align:center;">
        <p style="font-size:15px;">Delete category <strong id="del-cat-name" style="color:#8e44ad;">"?"</strong>?<br>
        <small style="color:#999;">Only unused categories can be deleted.</small></p>
      </div>
      <div class="modal-footer" style="border:none;padding:10px 20px 20px;text-align:center;">
        <button class="btn btn-default" data-dismiss="modal" style="border-radius:8px;margin-right:8px;">Cancel</button>
        <button class="btn btn-danger" id="cat-confirm-del" style="border-radius:8px;font-weight:600;"><i class="fa fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>


<?php include_once(VIEWPATH . 'inc/footer.php'); ?>

<?php
// Gradient helper for PHP render
function _pm_cat_grad($cat_id) {
  $grads = [
    1=>'linear-gradient(135deg,#3498db,#2980b9)', 2=>'linear-gradient(135deg,#e74c3c,#c0392b)',
    3=>'linear-gradient(135deg,#9b59b6,#8e44ad)', 4=>'linear-gradient(135deg,#27ae60,#1e8449)',
    5=>'linear-gradient(135deg,#16a085,#1abc9c)', 6=>'linear-gradient(135deg,#2980b9,#1abc9c)',
    7=>'linear-gradient(135deg,#2c3e50,#34495e)', 8=>'linear-gradient(135deg,#e67e22,#d35400)',
    9=>'linear-gradient(135deg,#1abc9c,#16a085)',10=>'linear-gradient(135deg,#2980b9,#1a5276)',
   11=>'linear-gradient(135deg,#8e44ad,#6c3483)',12=>'linear-gradient(135deg,#2ecc71,#27ae60)',
   13=>'linear-gradient(135deg,#e67e22,#ca6f1e)',14=>'linear-gradient(135deg,#e74c3c,#922b21)',
   15=>'linear-gradient(135deg,#9b59b6,#76448a)',16=>'linear-gradient(135deg,#e50914,#8b0000)',
   17=>'linear-gradient(135deg,#3498db,#1f618d)',18=>'linear-gradient(135deg,#7f8c8d,#6c7a7d)',
  ];
  return $grads[(int)$cat_id] ?? 'linear-gradient(135deg,#8e44ad,#6c3483)';
}
?>
<script>
var PM_BASE  = '<?php echo site_url() ?>';
var delCatId = null;

/* ── Gradient map ──────────────────────────────────────── */
var catGrads = {
   1:'linear-gradient(135deg,#3498db,#2980b9)', 2:'linear-gradient(135deg,#e74c3c,#c0392b)',
   3:'linear-gradient(135deg,#9b59b6,#8e44ad)', 4:'linear-gradient(135deg,#27ae60,#1e8449)',
   5:'linear-gradient(135deg,#16a085,#1abc9c)', 6:'linear-gradient(135deg,#2980b9,#1abc9c)',
   7:'linear-gradient(135deg,#2c3e50,#34495e)', 8:'linear-gradient(135deg,#e67e22,#d35400)',
   9:'linear-gradient(135deg,#1abc9c,#16a085)',10:'linear-gradient(135deg,#2980b9,#1a5276)',
  11:'linear-gradient(135deg,#8e44ad,#6c3483)',12:'linear-gradient(135deg,#2ecc71,#27ae60)',
  13:'linear-gradient(135deg,#e67e22,#ca6f1e)',14:'linear-gradient(135deg,#e74c3c,#922b21)',
  15:'linear-gradient(135deg,#9b59b6,#76448a)',16:'linear-gradient(135deg,#e50914,#8b0000)',
  17:'linear-gradient(135deg,#3498db,#1f618d)',18:'linear-gradient(135deg,#7f8c8d,#6c7a7d)',
};
function getCatGrad(id) {
  return catGrads[parseInt(id)] || 'linear-gradient(135deg,#8e44ad,#6c3483)';
}

/* ── HTML escape helpers ──────────────────────────────── */
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ── Preview updater ─────────────────────────────────── */
function updatePreview() {
  var emoji = document.getElementById('f-cat-emoji').value || '🔑';
  var icon  = document.getElementById('f-cat-icon').value  || 'fa-key';
  document.getElementById('prev-icon').textContent = emoji;
  document.getElementById('f-icon-preview').className = 'fa ' + icon;
  document.getElementById('prev-icon-lbl').innerHTML = '<i class="fa ' + icon + '"></i> ' + icon;
}

/* ── Emoji chip click ─────────────────────────────────── */
function selectEmoji(em) {
  document.getElementById('f-cat-emoji').value = em;
  document.querySelectorAll('.emoji-chip').forEach(function(c) { c.classList.remove('selected'); });
  if (event && event.target) {
    var chip = event.target.closest ? event.target.closest('.emoji-chip') : event.target;
    if (chip) chip.classList.add('selected');
  }
  updatePreview();
}

/* ── Icon chip click ──────────────────────────────────── */
function selectIcon(ic) {
  document.getElementById('f-cat-icon').value = ic;
  document.querySelectorAll('.icon-chip').forEach(function(c) { c.classList.remove('selected'); });
  if (event && event.target) {
    var chip = event.target.closest ? event.target.closest('.icon-chip') : event.target;
    if (chip) chip.classList.add('selected');
  }
  updatePreview();
}

/* ── Save category (AJAX) ────────────────────────────── */
function saveCategory() {
  var id    = document.getElementById('f-cat-id').value;
  var name  = document.getElementById('f-cat-name').value.trim();
  var emoji = document.getElementById('f-cat-emoji').value.trim();
  var icon  = document.getElementById('f-cat-icon').value.trim() || 'fa-key';
  var sort  = document.getElementById('f-cat-sort').value;

  if (!name)  { showToast('Category name is required!', 'error'); return; }
  if (!emoji) { showToast('Please pick an emoji icon!', 'error'); return; }

  var $btn = jQuery('#cat-save-btn').prop('disabled', true);
  jQuery('#cat-save-label').text(parseInt(id) > 0 ? 'Updating...' : 'Saving...');

  jQuery.post(PM_BASE + 'pm-category-save', {
    category_id:    id,
    category_name:  name,
    category_emoji: emoji,
    category_icon:  icon,
    sort_order:     sort
  }, function(r) {
    $btn.prop('disabled', false);
    jQuery('#cat-save-label').text(parseInt(id) > 0 ? 'Update Category' : 'Save Category');
    if (r.success) {
      showToast(r.message);
      renderRow(r.row, parseInt(id) > 0 ? 'update' : 'insert');
      if (!(parseInt(id) > 0)) resetCatForm();
      updateTotalBadge();
    } else {
      showToast(r.message, 'error');
    }
  }).fail(function() {
    $btn.prop('disabled', false);
    jQuery('#cat-save-label').text('Save Category');
    showToast('Server error — please try again.', 'error');
  });
}

/* ── Render / update a row in the table ─────────────── */
function renderRow(row, mode) {
  var catId    = row.category_id;
  var grad     = getCatGrad(catId);
  var usageCls = parseInt(row.usage_count) === 0 ? 'zero' : '';
  var html =
    '<tr data-id="' + catId + '" class="cat-row">' +
      '<td><span class="cat-drag-handle"><i class="fa fa-bars"></i></span></td>' +
      '<td style="color:#aaa;font-size:12px;">' + catId + '</td>' +
      '<td><div class="cat-name-cell">' +
        '<div class="cat-icon-badge" style="background:' + grad + ';">' + row.category_emoji + '</div>' +
        '<div>' +
          '<div style="font-weight:700;color:#2c1a4e;font-size:14px;">' + escHtml(row.category_name) + '</div>' +
          '<div style="font-size:11px;color:#aaa;">' + row.category_emoji + '</div>' +
        '</div>' +
      '</div></td>' +
      '<td><span style="font-size:13px;color:#666;">' +
        '<i class="fa ' + row.category_icon + '" style="color:#8e44ad;margin-right:5px;"></i>' +
        '<code style="font-size:11px;background:#f0eaff;padding:2px 6px;border-radius:4px;color:#8e44ad;">' + escHtml(row.category_icon) + '</code>' +
      '</span></td>' +
      '<td><input type="number" class="sort-input sort-order-input" data-id="' + catId + '" value="' + row.sort_order + '" min="0" max="999"></td>' +
      '<td style="text-align:center;"><span class="cat-usage-pill ' + usageCls + '">' + row.usage_count + '</span></td>' +
      '<td style="text-align:center;"><div class="cat-action-btns" style="justify-content:center;">' +
        '<button class="cat-btn-edit cat-edit-trigger" title="Edit"' +
          ' data-id="' + catId + '"' +
          ' data-name="' + escAttr(row.category_name) + '"' +
          ' data-icon="' + escAttr(row.category_icon) + '"' +
          ' data-emoji="' + escAttr(row.category_emoji) + '"' +
          ' data-sort="' + parseInt(row.sort_order) + '">' +
          '<i class="fa fa-pencil"></i></button>' +
        '<button class="cat-btn-del" title="Delete" onclick="deleteCategory(' + catId + ',\'' + String(row.category_name).replace(/'/g,"\\'") + '\',' + row.usage_count + ')">' +
          '<i class="fa fa-trash"></i></button>' +
      '</div></td>' +
    '</tr>';

  if (mode === 'insert') {
    jQuery('#cat-tbody tr td[colspan]').closest('tr').remove();
    jQuery('#cat-tbody').append(html);
  } else {
    jQuery('#cat-tbody tr[data-id="' + catId + '"]').replaceWith(html);
  }
  jQuery('#cat-tbody tr[data-id="' + catId + '"]').css('background','#f4eeff').animate({backgroundColor:'#fff'}, 1200);
}

/* ── Populate edit form (called by delegated click) ─── */
function loadEditForm(data) {
  document.getElementById('f-cat-id').value    = data.id;
  document.getElementById('f-cat-name').value  = data.name;
  document.getElementById('f-cat-emoji').value = data.emoji;
  document.getElementById('f-cat-icon').value  = data.icon;
  document.getElementById('f-cat-sort').value  = data.sort;

  // Live preview
  document.getElementById('prev-icon').textContent     = data.emoji || '🔑';
  document.getElementById('prev-name').textContent     = data.name  || 'Category Name';
  document.getElementById('prev-icon-lbl').innerHTML   = '<i class="fa ' + data.icon + '"></i> ' + data.icon;
  document.getElementById('f-icon-preview').className  = 'fa ' + data.icon;

  // Highlight matching emoji chip
  document.querySelectorAll('.emoji-chip').forEach(function(c) {
    c.classList.toggle('selected', c.textContent.trim() === data.emoji);
  });
  // Highlight matching icon chip
  document.querySelectorAll('.icon-chip').forEach(function(c) {
    c.classList.toggle('selected', c.getAttribute('title') === data.icon);
  });

  // Update form header
  document.getElementById('cat-form-title').innerHTML   = '<i class="fa fa-pencil"></i> Edit Category';
  document.getElementById('cat-form-subtitle').textContent = 'ID #' + data.id;
  document.getElementById('cat-save-label').textContent = 'Update Category';

  // Scroll to form panel
  document.querySelector('.cat-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ── Reset form to "Add New" state ───────────────────── */
function resetCatForm() {
  document.getElementById('f-cat-id').value    = '0';
  document.getElementById('f-cat-name').value  = '';
  document.getElementById('f-cat-emoji').value = '';
  document.getElementById('f-cat-icon').value  = '';
  document.getElementById('f-cat-sort').value  = '';
  document.getElementById('prev-icon').textContent    = '🔑';
  document.getElementById('prev-name').textContent    = 'Category Name';
  document.getElementById('prev-icon-lbl').innerHTML  = '<i class="fa fa-key"></i> fa-key';
  document.getElementById('f-icon-preview').className = 'fa fa-key';
  document.querySelectorAll('.emoji-chip').forEach(function(c) { c.classList.remove('selected'); });
  document.querySelectorAll('.icon-chip').forEach(function(c)  { c.classList.remove('selected'); });
  document.getElementById('cat-form-title').innerHTML   = '<i class="fa fa-plus-circle"></i> Add New Category';
  document.getElementById('cat-form-subtitle').textContent = 'Fill in details and click Save';
  document.getElementById('cat-save-label').textContent = 'Save Category';
}

/* ── Delete ──────────────────────────────────────────── */
function deleteCategory(id, name, usage) {
  if (parseInt(usage) > 0) {
    showToast('Cannot delete — ' + usage + ' password' + (usage == 1 ? '' : 's') + ' use this category. Reassign first.', 'error');
    return;
  }
  delCatId = id;
  document.getElementById('del-cat-name').textContent = name;
  jQuery('#catDeleteModal').modal('show');
}

/* ── Toast ───────────────────────────────────────────── */
function showToast(msg, type) {
  var $t  = jQuery('#cat-toast');
  var ico = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
  var bg  = type === 'error' ? '#c0392b' : '#2c1a4e';
  $t.css('background', bg).find('i').attr('class', 'fa ' + ico);
  jQuery('#cat-toast-msg').text(msg);
  $t.addClass('show');
  clearTimeout(window._catToastTimer);
  window._catToastTimer = setTimeout(function() { $t.removeClass('show'); }, 3200);
}

/* ── Search filter ───────────────────────────────────── */
function filterCategories(q) {
  q = q.toLowerCase();
  jQuery('#cat-tbody tr.cat-row').each(function() {
    var name = jQuery(this).find('.cat-name-cell').text().toLowerCase();
    jQuery(this).toggle(name.indexOf(q) >= 0);
  });
}

/* ── Total badge ─────────────────────────────────────── */
function updateTotalBadge() {
  var n = jQuery('#cat-tbody tr.cat-row:visible').length;
  jQuery('#cat-total-badge').text(n + ' Total');
}

/* ── Save all sort orders ────────────────────────────── */
function saveAllSortOrders() {
  var order = [];
  jQuery('.sort-order-input').each(function() {
    order.push({ id: parseInt(jQuery(this).data('id')), sort_order: parseInt(jQuery(this).val()) || 0 });
  });
  jQuery.post(PM_BASE + 'pm-category-reorder', { order: JSON.stringify(order) }, function(r) {
    showToast(r.success ? 'Sort order saved!' : r.message, r.success ? '' : 'error');
  });
}

/* ── All DOM-dependent bindings ─────────────────────── */
jQuery(function($) {

  /* Edit button — delegated so it works after renderRow() too */
  $(document).on('click', '.cat-edit-trigger', function(e) {
    e.preventDefault();
    var $btn = $(this);
    loadEditForm({
      id:    $btn.data('id'),
      name:  $btn.data('name') || '',
      icon:  $btn.data('icon') || 'fa-key',
      emoji: $btn.data('emoji') || '',
      sort:  $btn.data('sort')  || 0
    });
  });

  /* Delete confirm */
  $('#cat-confirm-del').on('click', function() {
    if (!delCatId) return;
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post(PM_BASE + 'pm-category-delete', { category_id: delCatId }, function(r) {
      $('#catDeleteModal').modal('hide');
      $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
      if (r.success) {
        $('#cat-tbody tr[data-id="' + delCatId + '"]').fadeOut(400, function() {
          $(this).remove();
          updateTotalBadge();
        });
        showToast(r.message);
      } else {
        showToast(r.message, 'error');
      }
      delCatId = null;
    }).fail(function() {
      $('#catDeleteModal').modal('hide');
      $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Delete');
      showToast('Server error', 'error');
    });
  });

  /* Drag & drop sortable */
  if ($.fn.sortable) {
    $('#cat-tbody').sortable({
      handle: '.cat-drag-handle',
      axis: 'y',
      placeholder: 'ui-state-highlight',
      update: function() {
        var order = [];
        $('#cat-tbody tr[data-id]').each(function(i) {
          var id = parseInt($(this).data('id'));
          order.push({ id: id, sort_order: i + 1 });
          $(this).find('.sort-order-input').val(i + 1);
        });
        $.post(PM_BASE + 'pm-category-reorder', { order: JSON.stringify(order) }, function(r) {
          if (r.success) showToast('Order updated!');
        });
      }
    });
  }

}); /* end jQuery ready */
</script>

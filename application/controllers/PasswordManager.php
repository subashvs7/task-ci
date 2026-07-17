<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PasswordManager extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    private function _uid()
    {
        return $this->session->userdata(SESS_HEAD . '_user_id');
    }

    // -------------------------------------------------------------------------
    // Table existence guard
    // -------------------------------------------------------------------------

    /**
     * Returns TRUE if both Password Manager tables exist in the database.
     */
    private function _tables_exist()
    {
        $this->db->save_queries = FALSE; // avoid leaking state
        $cats = $this->db->query("SHOW TABLES LIKE 'tm_pm_categories'");
        $pms  = $this->db->query("SHOW TABLES LIKE 'tm_password_manager'");
        $this->db->save_queries = TRUE;
        return ($cats && $cats->num_rows() > 0) && ($pms && $pms->num_rows() > 0);
    }

    /**
     * Redirects to the setup page if tables have not been created yet.
     * Call this at the top of every public page method.
     */
    private function _require_tables()
    {
        if (!$this->_tables_exist()) {
            $this->_ensure_tables();
        }
    }

    // -------------------------------------------------------------------------
    // Password Manager List / Dashboard
    // -------------------------------------------------------------------------

    public function index()
    {
        $this->_auth();
        $this->_require_tables();

        $uid      = $this->_uid();
        $role     = $this->session->userdata(SESS_HEAD . '_role');
        $is_admin = ($role === 'admin');

        $data['title']    = 'Password Manager';
        $data['js']       = 'password-manager.inc';
        $data['is_admin'] = $is_admin;

        // Safe category fetch
        $cat_q = $this->db->order_by('sort_order', 'ASC')->get('tm_pm_categories');
        $data['categories'] = ($cat_q !== false) ? $cat_q->result_array() : [];

        // Filters
        $category   = $this->input->get('category');
        $status_f   = $this->input->get('status');
        $fav_f      = $this->input->get('favorite');
        $search_f   = $this->input->get('search');
        $owner_f    = $this->input->get('owner'); // admin-only filter by user

        // Build query — admin sees ALL, user sees own
        $this->db->select('pm.*, pmc.category_name, pmc.category_icon, pmc.category_emoji,
                           u.name as owner_name, u.email as owner_email');
        $this->db->from('tm_password_manager pm');
        $this->db->join('tm_pm_categories pmc', 'pmc.category_id = pm.category_id', 'left');
        $this->db->join('tm_users u', 'u.user_id = pm.created_by', 'left');
        $this->db->where('pm.status_flag !=', 'Delete');

        if (!$is_admin) {
            // Regular user: only their own
            $this->db->where('pm.created_by', $uid);
        } elseif ($owner_f) {
            // Admin filtering by a specific user
            $this->db->where('pm.created_by', (int)$owner_f);
        }

        if ($category)                              $this->db->where('pm.category_id', (int)$category);
        if ($status_f !== null && $status_f !== '') $this->db->where('pm.status', (int)$status_f);
        if ($fav_f === '1')                         $this->db->where('pm.is_favorite', 1);
        if ($search_f) {
            $this->db->group_start()
                     ->like('pm.account_name', $search_f)
                     ->or_like('pm.service_name', $search_f)
                     ->or_like('pm.username', $search_f)
                     ->or_like('pm.email', $search_f)
                     ->or_like('u.name', $search_f)
                     ->group_end();
        }

        $this->db->order_by('pm.is_favorite', 'DESC');
        $this->db->order_by('pm.created_at', 'DESC');

        $rec_q = $this->db->get();
        $data['records'] = ($rec_q !== false) ? $rec_q->result_array() : [];

        // Category sidebar (admin = all, user = own)
        if ($is_admin) {
            $cl_q = $this->db->query(
                "SELECT pmc.*, COUNT(pm.id) as total
                 FROM tm_pm_categories pmc
                 LEFT JOIN tm_password_manager pm
                   ON pm.category_id = pmc.category_id AND pm.status_flag != 'Delete'
                 GROUP BY pmc.category_id
                 ORDER BY pmc.sort_order ASC"
            );
        } else {
            $cl_q = $this->db->query(
                "SELECT pmc.*, COUNT(pm.id) as total
                 FROM tm_pm_categories pmc
                 LEFT JOIN tm_password_manager pm
                   ON pm.category_id = pmc.category_id
                   AND pm.created_by = ?
                   AND pm.status_flag != 'Delete'
                 GROUP BY pmc.category_id
                 ORDER BY pmc.sort_order ASC",
                array($uid)
            );
        }
        $data['cat_list'] = ($cl_q !== false) ? $cl_q->result_array() : [];

        // Stats
        $data['total_count'] = count($data['records']);
        if ($is_admin) {
            $r1 = $this->db->query("SELECT COUNT(*) as c FROM tm_password_manager WHERE is_favorite=1 AND status_flag!='Delete'");
            $r2 = $this->db->query("SELECT COUNT(*) as c FROM tm_password_manager WHERE status=1 AND status_flag!='Delete'");
            $r3 = $this->db->query("SELECT COUNT(*) as c FROM tm_password_manager WHERE status=0 AND status_flag!='Delete'");
        } else {
            $r1 = $this->db->query("SELECT COUNT(*) as c FROM tm_password_manager WHERE created_by=? AND is_favorite=1 AND status_flag!='Delete'", array($uid));
            $r2 = $this->db->query("SELECT COUNT(*) as c FROM tm_password_manager WHERE created_by=? AND status=1 AND status_flag!='Delete'", array($uid));
            $r3 = $this->db->query("SELECT COUNT(*) as c FROM tm_password_manager WHERE created_by=? AND status=0 AND status_flag!='Delete'", array($uid));
        }
        $data['fav_count']      = ($r1 && $r1->num_rows()) ? (int)$r1->row()->c : 0;
        $data['active_count']   = ($r2 && $r2->num_rows()) ? (int)$r2->row()->c : 0;
        $data['inactive_count'] = ($r3 && $r3->num_rows()) ? (int)$r3->row()->c : 0;

        // For admin: load user list for owner filter dropdown
        $data['user_list'] = [];
        if ($is_admin) {
            $uq = $this->db->select('user_id, name, email')->order_by('name', 'ASC')->get('tm_users');
            $data['user_list'] = ($uq !== false) ? $uq->result_array() : [];
        }

        $this->load->view('page/password-manager/index', $data);
    }



    // -------------------------------------------------------------------------
    // Add / Edit Form
    // -------------------------------------------------------------------------

    public function form($id = 0)
    {
        $this->_auth();
        $this->_require_tables();

        $uid = $this->_uid();
        $data['title']      = $id ? 'Edit Password Entry' : 'Add Password Entry';
        $data['js']         = 'password-manager.inc';
        $cat_q = $this->db->order_by('sort_order', 'ASC')->get('tm_pm_categories');
        $data['categories'] = ($cat_q !== false) ? $cat_q->result_array() : [];
        $data['record']     = null;
        $data['id']         = (int)$id;

        if ($id) {
            $data['record'] = $this->db->query(
                "SELECT * FROM tm_password_manager WHERE id=? AND created_by=? AND status_flag!='Delete'",
                array((int)$id, $uid)
            )->row_array();

            if (!$data['record']) {
                $this->session->set_flashdata('alert_error', 'Record not found or access denied.');
                redirect('password-manager');
            }
        }

        $this->load->view('page/password-manager/form', $data);
    }

    // -------------------------------------------------------------------------
    // Save (Insert / Update)
    // -------------------------------------------------------------------------

    public function save()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid  = $this->_uid();
        $id   = (int)$this->input->post('id');
        $mode = $id ? 'update' : 'insert';

        $password_raw = $this->input->post('password');
        $password_enc = $this->_encrypt($password_raw);

        $pin_raw = $this->input->post('pin');
        $pin_enc = $pin_raw ? $this->_encrypt($pin_raw) : '';

        $tfa_raw = $this->input->post('two_factor_secret');
        $tfa_enc = $tfa_raw ? $this->_encrypt($tfa_raw) : '';

        $sec_ans_raw = $this->input->post('security_answer');
        $sec_ans_enc = $sec_ans_raw ? $this->_encrypt($sec_ans_raw) : '';

        $row = array(
            'category_id'        => (int)$this->input->post('category_id'),
            'account_name'       => $this->input->post('account_name'),
            'service_name'       => $this->input->post('service_name'),
            'login_url'          => $this->input->post('login_url'),
            'username'           => $this->input->post('username'),
            'email'              => $this->input->post('email'),
            'password_encrypted' => $password_enc,
            'pin'                => $pin_enc,
            'two_factor_secret'  => $tfa_enc,
            'recovery_email'     => $this->input->post('recovery_email'),
            'recovery_phone'     => $this->input->post('recovery_phone'),
            'security_question'  => $this->input->post('security_question'),
            'security_answer'    => $sec_ans_enc,
            'account_number'     => $this->input->post('account_number'),
            'notes'              => $this->input->post('notes'),
            'tags'               => $this->input->post('tags'),
            'expiry_date'        => $this->input->post('expiry_date') ?: null,
            'is_favorite'        => $this->input->post('is_favorite') ? 1 : 0,
            'status'             => $this->input->post('status') !== null ? (int)$this->input->post('status') : 1,
            'updated_at'         => date('Y-m-d H:i:s'),
        );

        if ($mode === 'insert') {
            $row['created_by'] = $uid;
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['status_flag'] = 'Active';
            $this->db->insert('tm_password_manager', $row);
            $new_id = $this->db->insert_id();
            echo json_encode(array('success' => true, 'message' => 'Password entry saved successfully.', 'id' => $new_id));
        } else {
            // Verify ownership
            $check = $this->db->query(
                "SELECT id FROM tm_password_manager WHERE id=? AND created_by=? AND status_flag!='Delete'",
                array($id, $uid)
            )->row_array();

            if (!$check) {
                echo json_encode(array('success' => false, 'message' => 'Record not found or access denied.'));
                return;
            }

            $this->db->where('id', $id);
            $this->db->update('tm_password_manager', $row);
            echo json_encode(array('success' => true, 'message' => 'Password entry updated successfully.', 'id' => $id));
        }
    }

    // -------------------------------------------------------------------------
    // Delete (soft delete)
    // -------------------------------------------------------------------------

    public function delete()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid = $this->_uid();
        $id  = (int)$this->input->post('id');

        $check = $this->db->query(
            "SELECT id FROM tm_password_manager WHERE id=? AND created_by=? AND status_flag!='Delete'",
            array($id, $uid)
        )->row_array();

        if (!$check) {
            echo json_encode(array('success' => false, 'message' => 'Record not found or access denied.'));
            return;
        }

        $this->db->where('id', $id);
        $this->db->update('tm_password_manager', array('status_flag' => 'Delete', 'updated_at' => date('Y-m-d H:i:s')));

        echo json_encode(array('success' => true, 'message' => 'Entry deleted successfully.'));
    }

    // -------------------------------------------------------------------------
    // Toggle Favorite
    // -------------------------------------------------------------------------

    public function toggle_favorite()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid = $this->_uid();
        $id  = (int)$this->input->post('id');

        $rec = $this->db->query(
            "SELECT id, is_favorite FROM tm_password_manager WHERE id=? AND created_by=? AND status_flag!='Delete'",
            array($id, $uid)
        )->row_array();

        if (!$rec) {
            echo json_encode(array('success' => false, 'message' => 'Not found.'));
            return;
        }

        $new_fav = $rec['is_favorite'] ? 0 : 1;
        $this->db->where('id', $id);
        $this->db->update('tm_password_manager', array('is_favorite' => $new_fav, 'updated_at' => date('Y-m-d H:i:s')));

        echo json_encode(array('success' => true, 'is_favorite' => $new_fav));
    }

    // -------------------------------------------------------------------------
    // Get Decrypted Password (AJAX - for copy/reveal)
    // -------------------------------------------------------------------------

    public function get_secret()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid   = $this->_uid();
        $id    = (int)$this->input->post('id');
        $field = $this->input->post('field'); // password | pin | two_factor_secret | security_answer

        $allowed_fields = array('password_encrypted', 'pin', 'two_factor_secret', 'security_answer');
        if (!in_array($field, $allowed_fields)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid field.'));
            return;
        }

        $rec = $this->db->query(
            "SELECT `{$field}`, username, email FROM tm_password_manager WHERE id=? AND created_by=? AND status_flag!='Delete'",
            array($id, $uid)
        )->row_array();

        if (!$rec) {
            echo json_encode(array('success' => false, 'message' => 'Not found.'));
            return;
        }

        $decrypted = $rec[$field] ? $this->_decrypt($rec[$field]) : '';

        $extra = array();
        if ($field === 'password_encrypted') {
            $extra['username'] = $rec['username'];
            $extra['email']    = $rec['email'];
        }

        echo json_encode(array_merge(array('success' => true, 'value' => $decrypted), $extra));
    }

    // -------------------------------------------------------------------------
    // Generate Password
    // -------------------------------------------------------------------------

    public function generate_password()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $length  = max(8, min(64, (int)$this->input->post('length') ?: 16));
        $upper   = $this->input->post('upper')   !== '0';
        $lower   = $this->input->post('lower')   !== '0';
        $numbers = $this->input->post('numbers') !== '0';
        $symbols = $this->input->post('symbols') !== '0';

        $charset = '';
        if ($upper)   $charset .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($lower)   $charset .= 'abcdefghijklmnopqrstuvwxyz';
        if ($numbers) $charset .= '0123456789';
        if ($symbols) $charset .= '!@#$%^&*()_+-=[]{}|;:,.<>?';

        if (!$charset) $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $password = '';
        $max      = strlen($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $charset[random_int(0, $max)];
        }

        // Calculate strength
        $strength = $this->_password_strength($password);

        echo json_encode(array('success' => true, 'password' => $password, 'strength' => $strength));
    }

    // -------------------------------------------------------------------------
    // Check Password Strength (AJAX)
    // -------------------------------------------------------------------------

    public function check_strength()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $password = $this->input->post('password');
        $strength = $this->_password_strength($password);
        echo json_encode(array('success' => true, 'strength' => $strength));
    }

    // -------------------------------------------------------------------------
    // Private Helpers - Migration / Initialization
    // -------------------------------------------------------------------------

    private function _ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tm_pm_categories` (
            `category_id` INT(11) NOT NULL AUTO_INCREMENT,
            `category_name` VARCHAR(100) NOT NULL,
            `category_icon` VARCHAR(50) DEFAULT 'fa-key',
            `category_emoji` VARCHAR(20) DEFAULT '',
            `sort_order` INT(5) DEFAULT 0,
            PRIMARY KEY (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->db->query("ALTER TABLE `tm_pm_categories`
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->db->query("ALTER TABLE `tm_pm_categories`
            MODIFY `category_emoji` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT ''");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tm_password_manager` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `category_id` INT(11) DEFAULT NULL,
            `account_name` VARCHAR(150) NOT NULL,
            `service_name` VARCHAR(150) DEFAULT NULL,
            `login_url` VARCHAR(255) DEFAULT NULL,
            `username` VARCHAR(150) DEFAULT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `password_encrypted` TEXT DEFAULT NULL,
            `pin` VARCHAR(255) DEFAULT NULL,
            `two_factor_secret` TEXT DEFAULT NULL,
            `recovery_email` VARCHAR(150) DEFAULT NULL,
            `recovery_phone` VARCHAR(20) DEFAULT NULL,
            `security_question` VARCHAR(255) DEFAULT NULL,
            `security_answer` TEXT DEFAULT NULL,
            `account_number` VARCHAR(100) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `tags` VARCHAR(255) DEFAULT NULL,
            `expiry_date` DATE DEFAULT NULL,
            `is_favorite` TINYINT(1) DEFAULT 0,
            `status` TINYINT(1) DEFAULT 1,
            `status_flag` VARCHAR(10) DEFAULT 'Active',
            `created_by` INT(11) DEFAULT NULL,
            `created_at` DATETIME DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_created_by` (`created_by`),
            KEY `idx_category_id` (`category_id`),
            KEY `idx_status_flag` (`status_flag`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->db->query("ALTER TABLE `tm_password_manager`
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $cats_exist = $this->db->count_all('tm_pm_categories');
        if ($cats_exist == 0) {
            $seed = array(
                array('category_name'=>'Website',        'category_icon'=>'fa-globe',        'category_emoji'=>"\xF0\x9F\x8C\x90", 'sort_order'=>1),
                array('category_name'=>'Email',          'category_icon'=>'fa-envelope',     'category_emoji'=>"\xF0\x9F\x93\xA7", 'sort_order'=>2),
                array('category_name'=>'Social Media',   'category_icon'=>'fa-share-alt',    'category_emoji'=>"\xF0\x9F\x93\xB1", 'sort_order'=>3),
                array('category_name'=>'Banking',        'category_icon'=>'fa-bank',         'category_emoji'=>"\xF0\x9F\x8F\xA6", 'sort_order'=>4),
                array('category_name'=>'Payment Wallet', 'category_icon'=>'fa-credit-card',  'category_emoji'=>"\xF0\x9F\x92\xB3", 'sort_order'=>5),
                array('category_name'=>'Cloud Storage',  'category_icon'=>'fa-cloud',        'category_emoji'=>"\xE2\x98\x81\xEF\xB8\x8F", 'sort_order'=>6),
                array('category_name'=>'Server / VPS',   'category_icon'=>'fa-server',       'category_emoji'=>"\xF0\x9F\x96\xA5\xEF\xB8\x8F", 'sort_order'=>7),
                array('category_name'=>'Hosting',        'category_icon'=>'fa-hdd-o',        'category_emoji'=>"\xF0\x9F\x94\x90", 'sort_order'=>8),
                array('category_name'=>'Domain',         'category_icon'=>'fa-globe',        'category_emoji'=>"\xF0\x9F\x8C\x8D", 'sort_order'=>9),
                array('category_name'=>'Database',       'category_icon'=>'fa-database',     'category_emoji'=>"\xF0\x9F\x93\x82", 'sort_order'=>10),
                array('category_name'=>'FTP / SFTP',     'category_icon'=>'fa-exchange',     'category_emoji'=>"\xF0\x9F\x93\xA1", 'sort_order'=>11),
                array('category_name'=>'Wi-Fi',          'category_icon'=>'fa-wifi',         'category_emoji'=>"\xF0\x9F\x93\xB6", 'sort_order'=>12),
                array('category_name'=>'Office / Work',  'category_icon'=>'fa-briefcase',    'category_emoji'=>"\xF0\x9F\x92\xBC", 'sort_order'=>13),
                array('category_name'=>'Shopping',       'category_icon'=>'fa-shopping-cart','category_emoji'=>"\xF0\x9F\x9B\x92", 'sort_order'=>14),
                array('category_name'=>'Gaming',         'category_icon'=>'fa-gamepad',      'category_emoji'=>"\xF0\x9F\x8E\xAE", 'sort_order'=>15),
                array('category_name'=>'Streaming',      'category_icon'=>'fa-play-circle',  'category_emoji'=>"\xF0\x9F\x93\xBA", 'sort_order'=>16),
                array('category_name'=>'Mobile Apps',    'category_icon'=>'fa-mobile',       'category_emoji'=>"\xF0\x9F\x93\xB1", 'sort_order'=>17),
                array('category_name'=>'Other',          'category_icon'=>'fa-key',          'category_emoji'=>"\xF0\x9F\x93\x9D", 'sort_order'=>18),
            );
            $this->db->insert_batch('tm_pm_categories', $seed);
        } else {
            $emoji_map = array(
                1 => "\xF0\x9F\x8C\x90", 2 => "\xF0\x9F\x93\xA7", 3 => "\xF0\x9F\x93\xB1", 4 => "\xF0\x9F\x8F\xA6",
                5 => "\xF0\x9F\x92\xB3", 6 => "\xE2\x98\x81\xEF\xB8\x8F", 7 => "\xF0\x9F\x96\xA5\xEF\xB8\x8F", 8 => "\xF0\x9F\x94\x90",
                9 => "\xF0\x9F\x8C\x8D", 10 => "\xF0\x9F\x93\x82", 11 => "\xF0\x9F\x93\xA1", 12 => "\xF0\x9F\x93\xB6",
                13 => "\xF0\x9F\x92\xBC", 14 => "\xF0\x9F\x9B\x92", 15 => "\xF0\x9F\x8E\xAE", 16 => "\xF0\x9F\x93\xBA",
                17 => "\xF0\x9F\x93\xB1", 18 => "\xF0\x9F\x93\x9D",
            );
            foreach ($emoji_map as $cat_id => $emoji) {
                $this->db->query(
                    "UPDATE `tm_pm_categories` SET `category_emoji` = ? WHERE `category_id` = ? AND (`category_emoji` = '????' OR `category_emoji` = '' OR `category_emoji` IS NULL OR LENGTH(`category_emoji`) < 2)",
                    array($emoji, $cat_id)
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private Helpers - Encryption
    // -------------------------------------------------------------------------

    private function _encrypt($value)
    {
        if ($value === '' || $value === null) return '';
        $key    = $this->_enc_key();
        $iv     = openssl_random_pseudo_bytes(16);
        $enc    = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $enc);
    }

    private function _decrypt($value)
    {
        if ($value === '' || $value === null) return '';
        try {
            $key  = $this->_enc_key();
            $data = base64_decode($value);
            $iv   = substr($data, 0, 16);
            $enc  = substr($data, 16);
            $dec  = openssl_decrypt($enc, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            return $dec !== false ? $dec : '';
        } catch (Exception $e) {
            return '';
        }
    }

    private function _enc_key()
    {
        // Use a fixed app-level key padded/hashed to 32 bytes
        return hash('sha256', 'TM_PM_SECRET_KEY_2024_' . SESS_HEAD, true);
    }

    // -------------------------------------------------------------------------
    // Password Strength Calculator
    // -------------------------------------------------------------------------

    private function _password_strength($password)
    {
        $score = 0;
        $len   = strlen($password);

        if ($len >= 8)  $score++;
        if ($len >= 12) $score++;
        if ($len >= 16) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;

        if ($score <= 2)      return array('score' => $score, 'label' => 'Very Weak',  'color' => '#e74c3c', 'pct' => 20);
        elseif ($score <= 3)  return array('score' => $score, 'label' => 'Weak',       'color' => '#e67e22', 'pct' => 40);
        elseif ($score <= 4)  return array('score' => $score, 'label' => 'Fair',       'color' => '#f1c40f', 'pct' => 60);
        elseif ($score <= 5)  return array('score' => $score, 'label' => 'Strong',     'color' => '#2ecc71', 'pct' => 80);
        else                  return array('score' => $score, 'label' => 'Very Strong','color' => '#27ae60', 'pct' => 100);
    }

    // =========================================================================
    // SETUP PAGE (shown when tables don't exist yet)
    // =========================================================================


    // =========================================================================
    // CATEGORY MASTER MANAGEMENT
    // =========================================================================

    /**
     * Categories list + inline form page
     */
    public function categories()
    {
        $this->_auth();
        $this->_require_tables();

        $data['title'] = 'Manage Categories';

        $cat_q = $this->db
            ->select('pmc.*, COUNT(pm.id) as usage_count')
            ->from('tm_pm_categories pmc')
            ->join('tm_password_manager pm', "pm.category_id = pmc.category_id AND pm.status_flag != 'Delete'", 'left')
            ->group_by('pmc.category_id')
            ->order_by('pmc.sort_order', 'ASC')
            ->get();
        $data['categories'] = ($cat_q !== false) ? $cat_q->result_array() : [];

        $this->load->view('page/password-manager/categories', $data);
    }

    /**
     * Save category (insert or update) — AJAX POST
     */
    public function category_save()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $id   = (int)$this->input->post('category_id');
        $name = trim($this->input->post('category_name'));
        $icon = trim($this->input->post('category_icon')) ?: 'fa-key';
        $emoji = trim($this->input->post('category_emoji')) ?: '🔑';
        $sort  = (int)$this->input->post('sort_order');

        if (!$name) {
            echo json_encode(array('success' => false, 'message' => 'Category name is required.'));
            return;
        }

        // Prevent duplicate names (case-insensitive)
        $dup = $this->db->query(
            "SELECT category_id FROM tm_pm_categories WHERE LOWER(category_name) = LOWER(?) AND category_id != ?",
            array($name, $id)
        )->row_array();

        if ($dup) {
            echo json_encode(array('success' => false, 'message' => 'A category with this name already exists.'));
            return;
        }

        $row = array(
            'category_name'  => $name,
            'category_icon'  => $icon,
            'category_emoji' => $emoji,
            'sort_order'     => $sort,
        );

        if ($id > 0) {
            $this->db->where('category_id', $id);
            $this->db->update('tm_pm_categories', $row);
            $new_id = $id;
            $msg    = 'Category updated successfully.';
        } else {
            // Auto sort_order if not set
            if (!$sort) {
                $max = $this->db->query("SELECT MAX(sort_order) as m FROM tm_pm_categories")->row()->m;
                $row['sort_order'] = (int)$max + 1;
            }
            $this->db->insert('tm_pm_categories', $row);
            $new_id = $this->db->insert_id();
            $msg    = 'Category added successfully.';
        }

        // Return fresh row for UI update
        $fresh = $this->db->query(
            "SELECT pmc.*, COUNT(pm.id) as usage_count FROM tm_pm_categories pmc
             LEFT JOIN tm_password_manager pm ON pm.category_id = pmc.category_id AND pm.status_flag != 'Delete'
             WHERE pmc.category_id = ? GROUP BY pmc.category_id",
            array($new_id)
        )->row_array();

        echo json_encode(array('success' => true, 'message' => $msg, 'id' => $new_id, 'row' => $fresh));
    }

    /**
     * Delete category — AJAX POST (hard delete, only if no passwords use it)
     */
    public function category_delete()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $id = (int)$this->input->post('category_id');
        if (!$id) {
            echo json_encode(array('success' => false, 'message' => 'Invalid category ID.'));
            return;
        }

        // Check usage
        $used = $this->db->query(
            "SELECT COUNT(*) as c FROM tm_password_manager WHERE category_id = ? AND status_flag != 'Delete'",
            array($id)
        )->row()->c;

        if ((int)$used > 0) {
            echo json_encode(array(
                'success' => false,
                'message' => "Cannot delete — {$used} password entr" . ($used == 1 ? 'y uses' : 'ies use') . " this category. Reassign them first."
            ));
            return;
        }

        $this->db->where('category_id', $id);
        $this->db->delete('tm_pm_categories');

        echo json_encode(array('success' => true, 'message' => 'Category deleted successfully.'));
    }

    /**
     * Reorder categories — AJAX POST (receives JSON array of {id, sort_order})
     */
    public function category_reorder()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $raw = $this->input->post('order');
        $items = is_string($raw) ? json_decode($raw, true) : $raw;

        if (!is_array($items)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid order data.'));
            return;
        }

        foreach ($items as $item) {
            $cid  = (int)$item['id'];
            $sort = (int)$item['sort_order'];
            if ($cid > 0) {
                $this->db->where('category_id', $cid);
                $this->db->update('tm_pm_categories', array('sort_order' => $sort));
            }
        }

        echo json_encode(array('success' => true, 'message' => 'Order saved.'));
    }
}


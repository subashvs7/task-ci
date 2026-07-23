<?php
class General extends CI_Controller
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
    // Auth
    // -------------------------------------------------------------------------

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    public function change_password()
    {
        $this->_auth();

        $data['js']    = 'change-password.inc';
        $data['title'] = 'Change Password';

        if ($this->input->post('mode') == 'ChangePassword') {
            $uid          = $this->_uid();
            $current_pass = $this->input->post('current_password');
            $new_pass     = $this->input->post('new_password');
            $confirm_pass = $this->input->post('confirm_password');

            $user = $this->db->query("SELECT password FROM tm_users WHERE user_id=?", array($uid))->row_array();
            if (!password_verify($current_pass, $user['password'])) {
                $this->session->set_flashdata('alert_error', 'Current password is incorrect.');
                redirect('change-password');
            }
            if (strlen($new_pass) < 6) {
                $this->session->set_flashdata('alert_error', 'New password must be at least 6 characters.');
                redirect('change-password');
            }
            if ($new_pass !== $confirm_pass) {
                $this->session->set_flashdata('alert_error', 'Passwords do not match.');
                redirect('change-password');
            }
            $this->db->where('user_id', $uid);
            $this->db->update('tm_users', array('password' => password_hash($new_pass, PASSWORD_DEFAULT), 'updated_date' => date('Y-m-d H:i:s')));
            $this->session->set_flashdata('alert_success', 'Password changed successfully.');
            redirect('change-password');
        }

        $this->load->view('page/change-password', $data);
    }

    // -------------------------------------------------------------------------
    // Delete Record (generic soft/hard delete)
    // -------------------------------------------------------------------------

    public function delete_record()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $tbl = $this->input->post('tbl');
        $col = $this->input->post('col');
        $id  = (int)$this->input->post('id');

        $allowed_tables = array(
            'tm_projects'         => 'project_id',
            'tm_tasks'            => 'task_id',
            'tm_epics'            => 'epic_id',
            'tm_user_stories'     => 'story_id',
        );

        if (!isset($allowed_tables[$tbl]) || $allowed_tables[$tbl] !== $col) {
            echo json_encode(array('success' => false, 'message' => 'Invalid table or column.'));
            return;
        }

        $this->db->where($col, $id);
        $this->db->update($tbl, array('status_flag' => 'Delete', 'updated_date' => date('Y-m-d H:i:s')));

        echo json_encode(array('success' => true, 'message' => 'Record deleted successfully.'));
    }

    // -------------------------------------------------------------------------
    // Task Status
    // -------------------------------------------------------------------------

    public function update_task_status()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $task_id   = (int)$this->input->post('task_id');
        $new_status = $this->input->post('status');
        $uid        = $this->_uid();

        $valid_statuses = array_keys(TASK_STATUS_OPT);
        if (!in_array($new_status, $valid_statuses)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid status.'));
            return;
        }

        $upd = array('status' => $new_status, 'updated_by' => $uid, 'updated_date' => date('Y-m-d H:i:s'));

        $task = $this->db->query("SELECT status, started_at, completed_at FROM tm_tasks WHERE task_id=?", array($task_id))->row_array();
        if ($task) {
            if ($new_status === 'in_progress' && !$task['started_at'])
                $upd['started_at'] = date('Y-m-d H:i:s');
            if ($new_status === 'done' && !$task['completed_at'])
                $upd['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('task_id', $task_id);
        $this->db->update('tm_tasks', $upd);

        $this->_log_activity($task_id, 'status_change', 'Status changed to ' . $new_status, $uid);
        echo json_encode(array('success' => true, 'message' => 'Status updated.'));
    }

    public function update_subtask_status()
    {
        $this->_auth();
        header('Content-Type: application/json');

        echo json_encode(array('success' => true));
    }

    // -------------------------------------------------------------------------
    // Comments + Sub-tasks (shared endpoint)
    // -------------------------------------------------------------------------

    public function add_comment()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid         = $this->_uid();
        $task_id     = (int)$this->input->post('task_id');
        $ajax_action = $this->input->post('ajax_action');

        if ($ajax_action === 'add_subtask') {
            echo json_encode(array('success' => false, 'message' => 'Sub-tasks are disabled.'));
            return;
        }

        $comment = trim($this->input->post('comment'));
        if (!$comment) {
            echo json_encode(array('success' => false, 'message' => 'Comment cannot be empty.'));
            return;
        }
        $this->db->insert('tm_comments', array(
            'task_id'      => $task_id,
            'user_id'      => $uid,
            'comment'      => $comment,
            'status_flag'  => 'Active',
            'created_date' => date('Y-m-d H:i:s'),
        ));
        $new_id = $this->db->insert_id();
        $user   = $this->db->query("SELECT name FROM tm_users WHERE user_id=?", array($uid))->row_array();
        $this->_log_activity($task_id, 'comment', 'Added a comment', $uid);

        echo json_encode(array(
            'success'    => true,
            'comment_id' => $new_id,
            'name'       => $user['name'],
            'comment'    => htmlspecialchars($comment),
            'date'       => date('d-M-Y H:i'),
        ));
    }

    public function delete_comment()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $comment_id = (int)$this->input->post('comment_id');
        $uid        = $this->_uid();

        $comment = $this->db->query("SELECT user_id, task_id FROM tm_comments WHERE comment_id=?", array($comment_id))->row_array();
        if (!$comment) {
            echo json_encode(array('success' => false, 'message' => 'Comment not found.'));
            return;
        }

        $role = $this->session->userdata(SESS_HEAD . '_role');
        if ($comment['user_id'] != $uid && $role !== 'admin') {
            echo json_encode(array('success' => false, 'message' => 'Not authorized.'));
            return;
        }

        $this->db->where('comment_id', $comment_id);
        $this->db->update('tm_comments', array('status_flag' => 'Delete'));
        echo json_encode(array('success' => true, 'message' => 'Comment deleted.'));
    }

    // -------------------------------------------------------------------------
    // Time Logs
    // -------------------------------------------------------------------------

    public function add_time_log()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid     = $this->_uid();
        $task_id = (int)$this->input->post('task_id');
        $hours   = (float)$this->input->post('hours');
        $note    = trim($this->input->post('note'));
        $log_date = $this->input->post('log_date') ?: date('Y-m-d');

        if ($hours <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Hours must be greater than 0.'));
            return;
        }

        $this->db->insert('tm_time_logs', array(
            'task_id'      => $task_id,
            'user_id'      => $uid,
            'hours'        => $hours,
            'note'         => $note,
            'log_date'     => $log_date,
            'status_flag'  => 'Active',
            'created_date' => date('Y-m-d H:i:s'),
        ));
        $new_id = $this->db->insert_id();
        $user   = $this->db->query("SELECT name FROM tm_users WHERE user_id=?", array($uid))->row_array();
        echo json_encode(array(
            'success' => true,
            'log_id'  => $new_id,
            'name'    => $user['name'],
            'hours'   => $hours,
            'note'    => htmlspecialchars($note),
            'date'    => date('d-M-Y', strtotime($log_date)),
        ));
    }

    public function delete_time_log()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $log_id = (int)$this->input->post('log_id');
        $uid    = $this->_uid();
        $role   = $this->session->userdata(SESS_HEAD . '_role');

        $log = $this->db->query("SELECT user_id FROM tm_time_logs WHERE log_id=?", array($log_id))->row_array();
        if (!$log) {
            echo json_encode(array('success' => false, 'message' => 'Log not found.'));
            return;
        }
        if ($log['user_id'] != $uid && $role !== 'admin') {
            echo json_encode(array('success' => false, 'message' => 'Not authorized.'));
            return;
        }

        $this->db->where('log_id', $log_id);
        $this->db->update('tm_time_logs', array('status_flag' => 'Delete'));
        echo json_encode(array('success' => true, 'message' => 'Time log deleted.'));
    }

    // -------------------------------------------------------------------------
    // Attachments
    // -------------------------------------------------------------------------

    public function get_task_attachments()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $task_id = (int)$this->input->get('task_id');
        $uid = $this->_uid();

        $atts = $this->db->query("
            SELECT a.attachment_id, a.user_id, a.file_name, a.file_path, a.file_size, a.created_date, u.name as uploader_name 
            FROM tm_attachments a
            LEFT JOIN tm_users u ON u.user_id = a.user_id
            WHERE a.task_id=? AND a.status_flag='Active'
            ORDER BY a.created_date ASC
        ", array($task_id))->result_array();

        foreach ($atts as &$a) {
            $a['url'] = base_url($a['file_path']);
            $a['size'] = round($a['file_size'] / 1024, 1) . ' KB';
            $a['date'] = date('d-M-Y H:i', strtotime($a['created_date']));
        }

        echo json_encode(array('success' => true, 'attachments' => $atts, 'current_user_id' => $uid));
    }

    public function upload_attachment()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid     = $this->_uid();
        $task_id = (int)$this->input->post('task_id');

        $upload_path = FCPATH . 'uploads/attachments/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);

        $config = array(
            'upload_path'   => $upload_path,
            'allowed_types' => '*',
            'max_size'      => 10240,
            'encrypt_name'  => TRUE,
        );
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('attachment')) {
            echo json_encode(array('success' => false, 'message' => $this->upload->display_errors('', '')));
            return;
        }

        $file = $this->upload->data();
        $this->db->insert('tm_attachments', array(
            'task_id'       => $task_id,
            'user_id'       => $uid,
            'file_name'     => $file['orig_name'],
            'file_path'     => 'uploads/attachments/' . $file['file_name'],
            'file_size'     => $file['file_size'],
            'file_type'     => $file['file_type'],
            'status_flag'   => 'Active',
            'created_date'  => date('Y-m-d H:i:s'),
        ));
        $new_id = $this->db->insert_id();
        $user   = $this->db->query("SELECT name FROM tm_users WHERE user_id=?", array($uid))->row_array();
        echo json_encode(array(
            'success'       => true,
            'attachment_id' => $new_id,
            'file_name'     => $file['orig_name'],
            'file_path'     => base_url('uploads/attachments/' . $file['file_name']),
            'file_size'     => round($file['file_size'] / 1024, 1) . ' KB',
            'uploader'      => $user['name'],
            'date'          => date('d-M-Y'),
        ));
    }

    public function delete_attachment()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $att_id = (int)$this->input->post('attachment_id');
        $uid    = $this->_uid();
        $role   = $this->session->userdata(SESS_HEAD . '_role');

        $att = $this->db->query("SELECT user_id, file_path FROM tm_attachments WHERE attachment_id=?", array($att_id))->row_array();
        if (!$att) {
            echo json_encode(array('success' => false, 'message' => 'Attachment not found.'));
            return;
        }
        if ($att['user_id'] != $uid && $role !== 'admin') {
            echo json_encode(array('success' => false, 'message' => 'Not authorized.'));
            return;
        }

        $full_path = FCPATH . $att['file_path'];
        if (file_exists($full_path)) unlink($full_path);

        $this->db->where('attachment_id', $att_id);
        $this->db->update('tm_attachments', array('status_flag' => 'Delete'));
        echo json_encode(array('success' => true, 'message' => 'Attachment deleted.'));
    }

    // -------------------------------------------------------------------------
    // Project Members
    // -------------------------------------------------------------------------

    public function add_project_member()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid        = $this->_uid();
        $project_id = (int)$this->input->post('project_id');
        $user_id    = (int)$this->input->post('user_id');
        $role       = $this->input->post('project_role') ?: 'team_leader';

        if (!$project_id || !$user_id) {
            echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
            return;
        }

        $exists = $this->db->query(
            "SELECT member_id FROM tm_project_members WHERE project_id=? AND user_id=?",
            array($project_id, $user_id)
        )->row_array();
        if ($exists) {
            echo json_encode(array('success' => false, 'message' => 'User is already a member.'));
            return;
        }

        $this->db->insert('tm_project_members', array(
            'project_id'   => $project_id,
            'user_id'      => $user_id,
            'project_role' => $role,
            'added_by'     => $uid,
            'added_date'   => date('Y-m-d H:i:s'),
        ));


        $user = $this->db->query("SELECT name, email, role FROM tm_users WHERE user_id=?", array($user_id))->row_array();
        echo json_encode(array(
            'success'    => true,
            'member_id'  => $this->db->insert_id(),
            'user_id'    => $user_id,
            'name'       => $user['name'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'project_role' => $role,
        ));
    }

    public function remove_project_member()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid       = $this->_uid();
        $member_id = (int)$this->input->post('member_id');
        $role      = $this->session->userdata(SESS_HEAD . '_role');

        $member = $this->db->query(
            "SELECT user_id, added_by, project_id, project_role FROM tm_project_members WHERE member_id=?",
            array($member_id)
        )->row_array();
        if (!$member) {
            echo json_encode(array('success' => false, 'message' => 'Member not found.'));
            return;
        }
        if ($member['added_by'] != $uid && $role !== 'admin') {
            echo json_encode(array('success' => false, 'message' => 'Not authorized.'));
            return;
        }

        $this->db->where('member_id', $member_id)->delete('tm_project_members');


        echo json_encode(array('success' => true, 'message' => 'Member removed.'));
    }

    // -------------------------------------------------------------------------
    // Activity Log Helper
    // -------------------------------------------------------------------------

    private function _log_activity($task_id, $action, $description, $user_id)
    {
        $this->db->insert('tm_activity_logs', array(
            'task_id'     => $task_id,
            'user_id'     => $user_id,
            'action'      => $action,
            'description' => $description,
            'created_date'=> date('Y-m-d H:i:s'),
        ));
    }

    // -------------------------------------------------------------------------
    // Project Team & Effort Stats Modal AJAX
    // -------------------------------------------------------------------------

    public function get_project_team_stats()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $project_id = (int)$this->input->get('project_id');
        if (!$project_id) {
            echo json_encode(array('success' => false, 'message' => 'Invalid project ID.'));
            return;
        }

        // Fetch project details
        $project = $this->db->query("
            SELECT p.*, u.name as owner_name 
            FROM tm_projects p 
            LEFT JOIN tm_users u ON u.user_id = p.owner_id 
            WHERE p.project_id = ? AND p.status_flag = 'Active'
        ", array($project_id))->row_array();

        if (!$project) {
            echo json_encode(array('success' => false, 'message' => 'Project not found.'));
            return;
        }

        // Calculate overall tasks and completion
        $tasks_stat = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('done','closed') THEN 1 ELSE 0 END) as done
            FROM tm_tasks 
            WHERE project_id = ? AND status_flag = 'Active'
        ", array($project_id))->row_array();

        $project['total_tasks'] = (int)$tasks_stat['total'];
        $project['done_tasks'] = (int)$tasks_stat['done'];
        $project['progress_pct'] = $project['total_tasks'] > 0 ? round(($project['done_tasks'] / $project['total_tasks']) * 100) : 0;

        // Unified list of all team members actively working on tasks in this project
        // (Includes the owner, explicit members in tm_project_members, and anyone assigned a task)
        $members_q = $this->db->query("
            SELECT 
                u.user_id, u.name, u.email, u.role as system_role,
                IF(u.user_id = ?, 'manager', IFNULL(pm.project_role, 'member')) as project_role,
                (SELECT COUNT(*) FROM tm_tasks WHERE project_id = ? AND assigned_to = u.user_id AND status_flag = 'Active') as total_tasks,
                (SELECT COUNT(*) FROM tm_tasks WHERE project_id = ? AND assigned_to = u.user_id AND status_flag = 'Active' AND status IN ('done','closed')) as completed_tasks,
                (SELECT SUM(tl.hours) FROM tm_time_logs tl JOIN tm_tasks t ON t.task_id = tl.task_id WHERE t.project_id = ? AND tl.user_id = u.user_id AND t.status_flag = 'Active' AND tl.status_flag = 'Active') as total_hours,
                (
                    SELECT GROUP_CONCAT(DISTINCT ur.name SEPARATOR ', ') 
                    FROM tm_tasks t2 
                    JOIN tm_users ur ON ur.user_id = t2.reporter_id 
                    WHERE t2.project_id = ? AND t2.assigned_to = u.user_id AND t2.status_flag = 'Active'
                ) as assigned_by_names
            FROM tm_users u
            LEFT JOIN tm_project_members pm ON pm.user_id = u.user_id AND pm.project_id = ?
            WHERE u.user_id = ? 
               OR pm.user_id IS NOT NULL 
               OR u.user_id IN (SELECT assigned_to FROM tm_tasks WHERE project_id = ? AND status_flag = 'Active' AND assigned_to IS NOT NULL)
        ", array(
            $project['owner_id'], 
            $project_id, 
            $project_id, 
            $project_id, 
            $project_id, 
            $project_id,
            $project['owner_id'],
            $project_id
        ))->result_array();

        foreach ($members_q as $m) {
            $m['total_hours'] = $m['total_hours'] ? round((float)$m['total_hours'], 2) : 0.0;
            $members[$m['user_id']] = $m;
        }

        echo json_encode(array(
            'success' => true,
            'project' => $project,
            'members' => array_values($members)
        ));
    }

    public function get_active_projects()
    {
        $this->_auth();
        header('Content-Type: application/json');
        $projects = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        echo json_encode(array('success' => true, 'projects' => $projects));
    }

    public function get_epics_by_project()
    {
        $this->_auth();
        header('Content-Type: application/json');
        $project_id = (int)$this->input->get('project_id');
        $epics = $this->db->query("
            SELECT e.epic_id, e.name, e.project_id, e.created_by, u.name as creator_name 
            FROM tm_epics e 
            LEFT JOIN tm_users u ON u.user_id = e.created_by 
            WHERE e.status_flag='Active' AND e.project_id = ? 
            ORDER BY e.name
        ", array($project_id))->result_array();
        echo json_encode(array('success' => true, 'epics' => $epics));
     }
    public function get_stories_by_epic()
    {
        $this->_auth();
        header('Content-Type: application/json');
        $epic_id = (int)$this->input->get('epic_id');
        $stories = $this->db->query("
            SELECT story_id, name, project_id, epic_id 
            FROM tm_user_stories 
            WHERE status_flag='Active' AND epic_id = ? 
            ORDER BY name
        ", array($epic_id))->result_array();
        echo json_encode(array('success' => true, 'stories' => $stories));
    }

    public function get_users_dropdown()
    {
        $this->_auth();
        header('Content-Type: application/json');
        $department_id = $this->input->get('department_id');

        $where = "status='Active'";
        if ($department_id !== null && $department_id !== '') {
            $where .= " AND department_id = " . (int)$department_id;
        }

        $users = $this->db->query("SELECT user_id, name, department_id FROM tm_users WHERE {$where} ORDER BY name")->result_array();
        echo json_encode(array('success' => true, 'users' => $users));
    }

     // -------------------------------------------------------------------------
    // One-Time Safe Database Schema Migration
    // -------------------------------------------------------------------------


    public function run_migration()
    {
        // Enforce basic login protection
        $this->_auth();

        $role = $this->session->userdata(SESS_HEAD . '_role');
        if ($role !== 'admin') {
            show_error('Only administrators can run database migrations.', 403);
        }

        echo "<h2>Database Migration Status</h2>";

        // 1. Check current definition of parent_task_id in tm_tasks
        $query = $this->db->query("SHOW COLUMNS FROM tm_tasks LIKE 'parent_task_id'");
        $column = $query->num_rows() > 0 ? $query->row_array() : null;

        if ($column) {
            echo "<p>[info] Column 'parent_task_id' already exists in 'tm_tasks' table.</p>";
            
            // Check if type is signed (contains 'unsigned' or not)
            if (strpos(strtolower($column['Type']), 'unsigned') === false) {
                echo "<p>[info] Type is SIGNED. Altering column to UNSIGNED to match task_id type exactly...</p>";
                
                // Drop foreign key if it exists
                @$this->db->query("ALTER TABLE tm_tasks DROP FOREIGN KEY fk_tasks_parent");
                
                $alter = $this->db->query("ALTER TABLE tm_tasks MODIFY COLUMN parent_task_id INT UNSIGNED NULL DEFAULT NULL");
                if ($alter) {
                    echo "<p style='color:green;'>[success] Successfully altered column 'parent_task_id' to INT UNSIGNED!</p>";
                } else {
                    echo "<p style='color:red;'>[error] Error altering column: " . $this->db->error()['message'] . "</p>";
                }
            } else {
                echo "<p style='color:green;'>[success] Column 'parent_task_id' is already UNSIGNED.</p>";
            }
        } else {
            echo "<p>[info] Column 'parent_task_id' does not exist. Creating column as INT UNSIGNED...</p>";
            $create = $this->db->query("ALTER TABLE tm_tasks ADD COLUMN parent_task_id INT UNSIGNED NULL DEFAULT NULL AFTER task_id");
            if ($create) {
                echo "<p style='color:green;'>[success] Successfully created column 'parent_task_id'!</p>";
            } else {
                echo "<p style='color:red;'>[error] Error creating column: " . $this->db->error()['message'] . "</p>";
            }
        }

        // 2. Ensure index exists
        $idx_query = $this->db->query("SHOW INDEX FROM tm_tasks WHERE Key_name = 'idx_parent_task'");
        if ($idx_query->num_rows() > 0) {
            echo "<p style='color:green;'>[success] Index 'idx_parent_task' already exists.</p>";
        } else {
            echo "<p>[info] Creating index 'idx_parent_task'...</p>";
            $idx = $this->db->query("ALTER TABLE tm_tasks ADD INDEX idx_parent_task (parent_task_id)");
            if ($idx) {
                echo "<p style='color:green;'>[success] Successfully created index 'idx_parent_task'!</p>";
            } else {
                echo "<p style='color:red;'>[error] Error creating index: " . $this->db->error()['message'] . "</p>";
            }
        }

        // 3. Ensure foreign key constraint exists
        $db_name = $this->db->database;
        $fk_query = $this->db->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.REFERENTIAL_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = ? 
              AND TABLE_NAME = 'tm_tasks' 
              AND CONSTRAINT_NAME = 'fk_tasks_parent'
        ", array($db_name));

        if ($fk_query->num_rows() > 0) {
            echo "<p style='color:green;'>[success] Foreign key constraint 'fk_tasks_parent' already exists and is active!</p>";
        } else {
            echo "<p>[info] Establishing foreign key constraint 'fk_tasks_parent' pointing to 'tm_tasks(task_id)'...</p>";
            try {
                $fk = $this->db->query("
                    ALTER TABLE tm_tasks
                    ADD CONSTRAINT fk_tasks_parent
                    FOREIGN KEY (parent_task_id) REFERENCES tm_tasks(task_id)
                    ON DELETE SET NULL 
                    ON UPDATE CASCADE
                ");
                if ($fk) {
                    echo "<p style='color:green;'>[success] Successfully established foreign key constraint 'fk_tasks_parent'!</p>";
                } else {
                    echo "<p style='color:red;'>[error] Error establishing foreign key constraint: " . $this->db->error()['message'] . "</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color:red;'>[error] Exception establishing foreign key constraint: " . $e->getMessage() . "</p>";
            }
        }

        echo "<p><a href='" . site_url('dash') . "'>&larr; Return to Dashboard</a></p>";
    }
}

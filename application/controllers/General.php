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
            'tm_projects'      => 'project_id',
            'tm_tasks'         => 'task_id',
            'tm_epics'         => 'epic_id',
            'tm_user_stories'  => 'story_id',
            'tm_subtasks'      => 'subtask_id',
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

        $subtask_id = (int)$this->input->post('subtask_id');
        $is_done    = (int)$this->input->post('is_done');
        $uid        = $this->_uid();

        $this->db->where('subtask_id', $subtask_id);
        $this->db->update('tm_subtasks', array(
            'is_done'      => $is_done,
            'updated_by'   => $uid,
            'updated_date' => date('Y-m-d H:i:s'),
        ));

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
            $title = trim($this->input->post('title'));
            if (!$title) {
                echo json_encode(array('success' => false, 'message' => 'Sub-task title required.'));
                return;
            }
            $this->db->insert('tm_subtasks', array(
                'task_id'      => $task_id,
                'title'        => $title,
                'is_done'      => 0,
                'status_flag'  => 'Active',
                'created_by'   => $uid,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            ));
            echo json_encode(array('success' => true, 'message' => 'Sub-task added.'));
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
            'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|zip|rar',
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
        $role       = $this->input->post('project_role') ?: 'member';

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
            "SELECT added_by, project_id FROM tm_project_members WHERE member_id=?",
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
}

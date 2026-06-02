<?php
class User extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
    }

    private function _admin_only()
    {
        $this->_auth();
        if (!has_menu_permission('users')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access User Management.');
            redirect_to_fallback();
        }
    }

    public function user_list()
    {
        $this->_admin_only();

        $data['js']    = 'users/user-list.inc';
        $data['title'] = 'Users';
        $data['s_url'] = 'user-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $password = $this->input->post('password');
            if (strlen($password) < 6) {
                $this->session->set_flashdata('alert_error', 'Password must be at least 6 characters.');
                redirect($data['s_url']);
            }
            $existing = $this->db->query(
                "SELECT user_id FROM tm_users WHERE email=?",
                array($this->input->post('email'))
            )->row_array();
            if ($existing) {
                $this->session->set_flashdata('alert_error', 'Email already exists.');
                redirect($data['s_url']);
            }
            $curr_role_session = $this->session->userdata(SESS_HEAD . '_role');
            if ($curr_role_session === 'admin') {
                $allowed_keys = array_keys(USER_ROLE_OPT);
            } else {
                $assignable = get_assignable_roles();
                $allowed_keys = array_keys($assignable);
            }
            $role_val = $this->input->post('role');
            if (empty($role_val) || !in_array($role_val, $allowed_keys)) {
                $role_val = !empty($allowed_keys) ? $allowed_keys[0] : '';
            }

            $ins = array(
                'name'         => $this->input->post('name'),
                'email'        => $this->input->post('email'),
                'password'     => password_hash($password, PASSWORD_DEFAULT),
                'role'         => $role_val,
                'status'       => 'Active',
                'created_by'   => $uid,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_users', $ins);
            $new_user_id = $this->db->insert_id();

            $assign_pid = (int)$this->input->post('assign_project_id');
            if ($assign_pid) {
                $exists = $this->db->query(
                    "SELECT member_id FROM tm_project_members WHERE project_id=? AND user_id=?",
                    array($assign_pid, $new_user_id)
                )->row_array();
                if (!$exists) {
                    $this->db->insert('tm_project_members', array(
                        'project_id'   => $assign_pid,
                        'user_id'      => $new_user_id,
                        'project_role' => $role_val,
                        'added_by'     => $uid,
                        'added_date'   => date('Y-m-d H:i:s'),
                    ));
                }
            }

            $this->session->set_flashdata('alert_success', 'User created successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $curr_role_session = $this->session->userdata(SESS_HEAD . '_role');
            if ($curr_role_session === 'admin') {
                $allowed_keys = array_keys(USER_ROLE_OPT);
            } else {
                $assignable = get_assignable_roles();
                $allowed_keys = array_keys($assignable);
            }
            $role_val = $this->input->post('role');
            if (!in_array($role_val, $allowed_keys)) {
                $existing_user = $this->db->query("SELECT role FROM tm_users WHERE user_id=?", array($this->input->post('user_id')))->row_array();
                $role_val = $existing_user ? $existing_user['role'] : '';
            }

            // Check old role to detect if role was just changed to manager by admin
            $old_user = $this->db->query("SELECT role FROM tm_users WHERE user_id=?", array($this->input->post('user_id')))->row_array();
            $old_role = $old_user ? $old_user['role'] : '';

            $upd = array(
                'name'         => $this->input->post('name'),
                'email'        => $this->input->post('email'),
                'role'         => $role_val,
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            // If admin is assigning manager role (newly), flag notify_login
            if ($curr_role_session === 'admin' && $role_val === 'manager' && $old_role !== 'manager') {
                $upd['notify_login'] = 1;
            }
            $new_pass = $this->input->post('new_password');
            if ($new_pass) {
                if (strlen($new_pass) < 6) {
                    $this->session->set_flashdata('alert_error', 'Password must be at least 6 characters.');
                    redirect($data['s_url']);
                }
                $upd['password'] = password_hash($new_pass, PASSWORD_DEFAULT);
            }
            $this->db->where('user_id', $this->input->post('user_id'));
            $this->db->update('tm_users', $upd);
            $this->session->set_flashdata('alert_success', 'User updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        if ($this->input->post('mode') == 'ToggleStatus') {
            $target_uid = (int)$this->input->post('user_id');
            if ($target_uid == $uid) {
                $this->session->set_flashdata('alert_error', 'You cannot deactivate your own account.');
                redirect($data['s_url']);
            }
            $current = $this->db->query("SELECT status FROM tm_users WHERE user_id=?", array($target_uid))->row_array();
            $new_status = ($current['status'] == 'Active') ? 'Inactive' : 'Active';
            $this->db->where('user_id', $target_uid);
            $this->db->update('tm_users', array('status' => $new_status, 'updated_by' => $uid, 'updated_date' => date('Y-m-d H:i:s')));
            $this->session->set_flashdata('alert_success', 'User status updated.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        $f_search = $this->input->get('search');
        $f_role   = $this->input->get('f_role');
        $f_status = $this->input->get('f_status');

        $this->load->library('pagination');
        $curr_role = $this->session->userdata(SESS_HEAD . '_role');
        if ($curr_role === 'admin') {
            $where = "1=1";
            $allowed_keys = array_keys(USER_ROLE_OPT);
        } else {
            $assignable = get_assignable_roles();
            $allowed_keys = array_keys($assignable);
            if (!empty($allowed_keys)) {
                $where = "role IN ('" . implode("','", $allowed_keys) . "')";
            } else {
                $where = "1=0";
            }
        }
        
        if ($f_search) $where .= " AND (name LIKE '%" . $this->db->escape_like_str($f_search) . "%' OR email LIKE '%" . $this->db->escape_like_str($f_search) . "%')";
        if ($f_role && in_array($f_role, $allowed_keys)) $where .= " AND role='" . $this->db->escape_str($f_role) . "'";
        if ($f_status) $where .= " AND status='" . $this->db->escape_str($f_status) . "'";

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_users WHERE {$where}")->row_array();
        $data['total_records'] = (int)$cnt['cnt'];

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $data['total_records'], 30);
        $this->pagination->initialize($config);

        $data['record_list'] = $this->db->query(
            "SELECT u.*,
                (SELECT COUNT(*) FROM tm_tasks t WHERE t.assigned_to=u.user_id AND t.status_flag='Active') as task_count,
                (SELECT COUNT(*) FROM tm_tasks t WHERE t.assigned_to=u.user_id AND t.status='in_progress' AND t.status_flag='Active') as active_task_count
             FROM tm_users u WHERE {$where} 
             ORDER BY FIELD(u.role, 'admin', 'manager', 'team_leader', 'staff'), u.created_date DESC 
             LIMIT {$offset}, 30"
        )->result_array();
        $data['pagination']    = $this->pagination->create_links();
        $data['f_search']      = $f_search;
        $data['f_role']        = $f_role;
        $data['f_status']      = $f_status;
        $data['projects_list'] = $this->db->query(
            "SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name"
        )->result_array();

        $this->load->view('page/users/user-list', $data);
    }

    private function _pagination_config($url, $total, $per_page)
    {
        return array(
            'base_url'         => site_url($url), 'total_rows'       => $total,
            'per_page'         => $per_page,      'uri_segment'      => 2,
            'reuse_query_string' => TRUE,
            'attributes'       => array('class' => 'page-link'),
            'full_tag_open'    => '<ul class="pagination pagination-sm no-margin pull-right">',
            'full_tag_close'   => '</ul>',
            'num_tag_open'     => '<li class="page-item">', 'num_tag_close'  => '</li>',
            'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
            'cur_tag_close'    => '<span class="sr-only">(current)</span></a></li>',
            'prev_tag_open'    => '<li class="page-item">', 'prev_tag_close' => '</li>',
            'next_tag_open'    => '<li class="page-item">', 'next_tag_close' => '</li>',
            'first_tag_open'   => '<li class="page-item">', 'first_tag_close'=> '</li>',
            'last_tag_open'    => '<li class="page-item">', 'last_tag_close' => '</li>',
            'prev_link'        => 'Prev', 'next_link' => 'Next',
        );
    }
}

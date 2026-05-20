<?php
class User extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    private function _admin_only()
    {
        $this->_auth();
        if ($this->session->userdata(SESS_HEAD . '_role') !== 'admin')
            redirect('dash');
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
            $ins = array(
                'name'         => $this->input->post('name'),
                'email'        => $this->input->post('email'),
                'password'     => password_hash($password, PASSWORD_DEFAULT),
                'role'         => $this->input->post('role') ?: 'member',
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
                        'project_role' => $this->input->post('role') ?: 'member',
                        'added_by'     => $uid,
                        'added_date'   => date('Y-m-d H:i:s'),
                    ));
                }
            }

            $this->session->set_flashdata('alert_success', 'User created successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $upd = array(
                'name'         => $this->input->post('name'),
                'email'        => $this->input->post('email'),
                'role'         => $this->input->post('role'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
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
        $where = "1=1";
        if ($f_search) $where .= " AND (name LIKE '%" . $this->db->escape_like_str($f_search) . "%' OR email LIKE '%" . $this->db->escape_like_str($f_search) . "%')";
        if ($f_role)   $where .= " AND role='" . $this->db->escape_str($f_role) . "'";
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
             FROM tm_users u WHERE {$where} ORDER BY u.created_date DESC LIMIT {$offset}, 30"
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

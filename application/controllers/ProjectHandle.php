<?php
class ProjectHandle extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        $role = $this->session->userdata(SESS_HEAD . '_role');
        if ($role !== 'manager' && $role !== 'admin') {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Project Handling.');
            redirect_to_fallback();
        }
    }

    public function index()
    {
        $this->_auth();

        $data['js']    = 'projects/project-handle.inc';
        $data['title'] = 'Project Handle';
        $data['s_url'] = 'project-handle';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $pid = $this->input->post('project_id');
            $tlid = $this->input->post('team_leader_id');
            $ins = array(
                'project_id'     => $pid,
                'team_leader_id' => $tlid,
                'due_date'       => $this->input->post('due_date'),
                'notes'          => $this->input->post('notes'),
                'status'         => $this->input->post('status') ?: 'active',
                'status_flag'    => 'Active',
                'created_by'     => $uid,
                'created_date'   => date('Y-m-d H:i:s'),
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_project_handlers', $ins);

            // Auto-add as project member
            $exists = $this->db->query("SELECT 1 FROM tm_project_members WHERE project_id=? AND user_id=?", array($pid, $tlid))->row_array();
            if (!$exists) {
                $this->db->insert('tm_project_members', array(
                    'project_id'   => $pid,
                    'user_id'      => $tlid,
                    'project_role' => 'team_leader',
                    'added_by'     => $uid,
                    'added_date'   => date('Y-m-d H:i:s')
                ));
            }

            $this->session->set_flashdata('alert_success', 'Project assigned to Team Leader successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $pid = $this->input->post('project_id');
            $tlid = $this->input->post('team_leader_id');
            $upd = array(
                'project_id'     => $pid,
                'team_leader_id' => $tlid,
                'due_date'       => $this->input->post('due_date'),
                'notes'          => $this->input->post('notes'),
                'status'         => $this->input->post('status'),
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            $this->db->where('handler_id', $this->input->post('handler_id'));
            $this->db->update('tm_project_handlers', $upd);

            // Auto-add as project member
            $exists = $this->db->query("SELECT 1 FROM tm_project_members WHERE project_id=? AND user_id=?", array($pid, $tlid))->row_array();
            if (!$exists) {
                $this->db->insert('tm_project_members', array(
                    'project_id'   => $pid,
                    'user_id'      => $tlid,
                    'project_role' => 'team_leader',
                    'added_by'     => $uid,
                    'added_date'   => date('Y-m-d H:i:s')
                ));
            }

            $this->session->set_flashdata('alert_success', 'Project handling updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        $f_project = $this->input->get('project_id');
        $f_leader  = $this->input->get('team_leader_id');

        $this->load->library('pagination');
        $where = "h.status_flag='Active'";
        
        $role = $this->session->userdata(SESS_HEAD . '_role');
        if ($role === 'manager') {
            // Manager can only see projects they own
            $where .= " AND p.owner_id = {$uid}";
        }

        if ($f_project) $where .= " AND h.project_id=" . (int)$f_project;
        if ($f_leader)  $where .= " AND h.team_leader_id=" . (int)$f_leader;

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_project_handlers h JOIN tm_projects p ON p.project_id=h.project_id WHERE {$where}")->row_array();
        $data['total_records'] = (int)$cnt['cnt'];

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $data['total_records'], 30);
        $this->pagination->initialize($config);

        $sql = "SELECT h.*, p.name as project_name, u.name as team_leader_name, u.email as team_leader_email, cb.name as assigned_by_name
                FROM tm_project_handlers h
                JOIN tm_projects p ON p.project_id = h.project_id
                JOIN tm_users u ON u.user_id = h.team_leader_id
                JOIN tm_users cb ON cb.user_id = h.created_by
                WHERE {$where}
                ORDER BY h.created_date DESC
                LIMIT {$offset}, 30";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        
        // Dropdowns
        if ($role === 'manager') {
            $data['projects_list'] = $this->db->query("SELECT project_id, name, end_date, manager_deadline_days FROM tm_projects WHERE status_flag='Active' AND owner_id = ? ORDER BY name", array($uid))->result_array();
        } else {
            $data['projects_list'] = $this->db->query("SELECT project_id, name, end_date, manager_deadline_days FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        }
        $data['team_leaders_list'] = $this->db->query("SELECT user_id, name FROM tm_users WHERE role='team_leader' AND status='Active' ORDER BY name")->result_array();
        
        $data['f_project']     = $f_project;
        $data['f_leader']      = $f_leader;

        $this->load->view('page/projects/project-handle', $data);
    }

    private function _pagination_config($url, $total, $per_page)
    {
        return array(
            'base_url'         => site_url($url),
            'total_rows'       => $total,
            'per_page'         => $per_page,
            'uri_segment'      => 2,
            'reuse_query_string' => TRUE,
            'attributes'       => array('class' => 'page-link'),
            'full_tag_open'    => '<ul class="pagination pagination-sm no-margin pull-right">',
            'full_tag_close'   => '</ul>',
            'num_tag_open'     => '<li class="page-item">', 'num_tag_close'     => '</li>',
            'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
            'cur_tag_close'    => '<span class="sr-only">(current)</span></a></li>',
            'prev_tag_open'    => '<li class="page-item">', 'prev_tag_close'    => '</li>',
            'next_tag_open'    => '<li class="page-item">', 'next_tag_close'    => '</li>',
            'first_tag_open'   => '<li class="page-item">', 'first_tag_close'   => '</li>',
            'last_tag_open'    => '<li class="page-item">', 'last_tag_close'    => '</li>',
            'prev_link'        => 'Prev',
            'next_link'        => 'Next',
        );
    }
}

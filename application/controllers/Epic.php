<?php
class Epic extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        if (!has_menu_permission('epics')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Epics.');
            redirect_to_fallback();
        }
    }

    public function epic_list()
    {
        $this->_auth();

        $data['js']    = 'epics/epic-list.inc';
        $data['title'] = 'Epics';
        $data['s_url'] = 'epic-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $user_role = $this->session->userdata(SESS_HEAD . '_role');
            if (!in_array($user_role, ['admin', 'manager', 'team_leader'])) {
                $this->session->set_flashdata('alert_error', 'Only Team Leaders or Managers can create Epics.');
                redirect($data['s_url']);
            }
            $est_h = (float)$this->input->post('est_hours');
            $est_m = (float)$this->input->post('est_minutes');
            $estimated_time = round(($est_h * 60) + $est_m);
            $ins = array(
                'project_id'   => $this->input->post('project_id'),
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'open',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'color'        => $this->input->post('color') ?: '#9b59b6',
                'estimated_time'=> $estimated_time,
                'status_flag'  => 'Active',
                'created_by'   => $uid,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_epics', $ins);
            $this->session->set_flashdata('alert_success', 'Epic created successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $user_role = $this->session->userdata(SESS_HEAD . '_role');
            if (!in_array($user_role, ['admin', 'manager', 'team_leader'])) {
                $this->session->set_flashdata('alert_error', 'Only Team Leaders or Managers can edit Epics.');
                redirect($data['s_url']);
            }
            $est_h = (float)$this->input->post('est_hours');
            $est_m = (float)$this->input->post('est_minutes');
            $estimated_time = round(($est_h * 60) + $est_m);
            $upd = array(
                'project_id'   => $this->input->post('project_id'),
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'color'        => $this->input->post('color'),
                'estimated_time'=> $estimated_time,
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->where('epic_id', $this->input->post('epic_id'));
            $this->db->update('tm_epics', $upd);
            $this->session->set_flashdata('alert_success', 'Epic updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        $f_project = $this->input->get('project_id');
        $f_status  = $this->input->get('f_status');

        $this->load->library('pagination');
        $where = "e.status_flag='Active'";
        if ($f_project) $where .= " AND e.project_id=" . (int)$f_project;
        if ($f_status)  $where .= " AND e.status='" . $this->db->escape_str($f_status) . "'";

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_epics e WHERE {$where}")->row_array();
        $data['total_records'] = (int)$cnt['cnt'];

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $data['total_records'], 30);
        $this->pagination->initialize($config);

        $sql = "SELECT e.*, p.name as project_name,
                    (SELECT COUNT(*) FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active') as story_count,
                    COALESCE((SELECT SUM(t.estimated_hours) FROM tm_tasks t WHERE (t.epic_id = e.epic_id OR t.story_id IN (SELECT story_id FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active')) AND t.status_flag='Active'), 0) as calculated_time_hours
                FROM tm_epics e
                LEFT JOIN tm_projects p ON p.project_id = e.project_id
                WHERE {$where}
                ORDER BY e.created_date DESC
                LIMIT {$offset}, 30";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        $role = $this->session->userdata(SESS_HEAD . '_role');
        $uid = $this->session->userdata(SESS_HEAD . '_user_id');
        if ($role === 'team_leader') {
            $data['projects_list'] = $this->db->query("SELECT p.project_id, p.name FROM tm_projects p JOIN tm_project_handlers h ON h.project_id = p.project_id WHERE p.status_flag='Active' AND h.team_leader_id=? AND h.status='active' ORDER BY p.name", array($uid))->result_array();
        } else {
            $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        }
        $data['f_project']     = $f_project;
        $data['f_status']      = $f_status;

        $this->load->view('page/epics/epic-list', $data);
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

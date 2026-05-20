<?php
class Epic extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    public function epic_list()
    {
        $this->_auth();

        $data['js']    = 'epics/epic-list.inc';
        $data['title'] = 'Epics';
        $data['s_url'] = 'epic-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $ins = array(
                'project_id'   => $this->input->post('project_id'),
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'open',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'color'        => $this->input->post('color') ?: '#9b59b6',
                'start_date'   => $this->input->post('start_date') ?: NULL,
                'end_date'     => $this->input->post('end_date') ?: NULL,
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
            $upd = array(
                'project_id'   => $this->input->post('project_id'),
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'color'        => $this->input->post('color'),
                'start_date'   => $this->input->post('start_date') ?: NULL,
                'end_date'     => $this->input->post('end_date') ?: NULL,
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
                    (SELECT COUNT(*) FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active') as story_count
                FROM tm_epics e
                LEFT JOIN tm_projects p ON p.project_id = e.project_id
                WHERE {$where}
                ORDER BY e.created_date DESC
                LIMIT {$offset}, 30";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
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

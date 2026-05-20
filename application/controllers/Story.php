<?php
class Story extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    public function story_list()
    {
        $this->_auth();

        $data['js']    = 'stories/story-list.inc';
        $data['title'] = 'User Stories';
        $data['s_url'] = 'story-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $ins = array(
                'project_id'   => $this->input->post('project_id'),
                'epic_id'      => $this->input->post('epic_id') ?: NULL,
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'backlog',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'story_points' => (int)$this->input->post('story_points'),
                'assignee_id'  => $this->input->post('assignee_id') ?: NULL,
                'reporter_id'  => $uid,
                'sprint'       => $this->input->post('sprint'),
                'status_flag'  => 'Active',
                'created_by'   => $uid,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_user_stories', $ins);
            $this->session->set_flashdata('alert_success', 'User story created successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $upd = array(
                'project_id'   => $this->input->post('project_id'),
                'epic_id'      => $this->input->post('epic_id') ?: NULL,
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'story_points' => (int)$this->input->post('story_points'),
                'assignee_id'  => $this->input->post('assignee_id') ?: NULL,
                'sprint'       => $this->input->post('sprint'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->where('story_id', $this->input->post('story_id'));
            $this->db->update('tm_user_stories', $upd);
            $this->session->set_flashdata('alert_success', 'User story updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        $f_project = $this->input->get('project_id');
        $f_epic    = $this->input->get('epic_id');
        $f_status  = $this->input->get('f_status');

        $this->load->library('pagination');
        $where = "s.status_flag='Active'";
        if ($f_project) $where .= " AND s.project_id=" . (int)$f_project;
        if ($f_epic)    $where .= " AND s.epic_id=" . (int)$f_epic;
        if ($f_status)  $where .= " AND s.status='" . $this->db->escape_str($f_status) . "'";

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_user_stories s WHERE {$where}")->row_array();
        $data['total_records'] = (int)$cnt['cnt'];

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $data['total_records'], 30);
        $this->pagination->initialize($config);

        $sql = "SELECT s.*, p.name as project_name, e.name as epic_name, u.name as assignee_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE story_id=s.story_id AND status_flag='Active') as task_count
                FROM tm_user_stories s
                LEFT JOIN tm_projects p ON p.project_id = s.project_id
                LEFT JOIN tm_epics e ON e.epic_id = s.epic_id
                LEFT JOIN tm_users u ON u.user_id = s.assignee_id
                WHERE {$where}
                ORDER BY s.created_date DESC
                LIMIT {$offset}, 30";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['epics_list']    = $this->db->query("SELECT epic_id, name, project_id FROM tm_epics WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['f_project']     = $f_project;
        $data['f_epic']        = $f_epic;
        $data['f_status']      = $f_status;

        $this->load->view('page/stories/story-list', $data);
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

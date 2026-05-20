<?php
class Project extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    // ── Project List ─────────────────────────────────────────────────────────

    public function project_list()
    {
        $this->_auth();

        $data['js']    = 'projects/project-list.inc';
        $data['title'] = 'Project List';
        $data['s_url'] = 'project-list';

        // Handle Add
        if ($this->input->post('mode') == 'Add') {
            $ins = array(
                'name'         => $this->input->post('name'),
                'key_name'     => strtoupper($this->input->post('key_name')),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'planning',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'color'        => $this->input->post('color') ?: '#2c3e50',
                'start_date'   => $this->input->post('start_date') ?: NULL,
                'end_date'     => $this->input->post('end_date') ?: NULL,
                'owner_id'     => $this->input->post('owner_id') ?: $this->session->userdata(SESS_HEAD . '_user_id'),
                'status_flag'  => 'Active',
                'created_by'   => $this->session->userdata(SESS_HEAD . '_user_id'),
                'created_date' => date('Y-m-d H:i:s'),
                'updated_by'   => $this->session->userdata(SESS_HEAD . '_user_id'),
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_projects', $ins);
            $this->session->set_flashdata('alert_success', 'Project created successfully.');
            redirect($data['s_url']);
        }

        // Handle Edit
        if ($this->input->post('mode') == 'Edit') {
            $upd = array(
                'name'         => $this->input->post('name'),
                'key_name'     => strtoupper($this->input->post('key_name')),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'color'        => $this->input->post('color'),
                'start_date'   => $this->input->post('start_date') ?: NULL,
                'end_date'     => $this->input->post('end_date') ?: NULL,
                'owner_id'     => $this->input->post('owner_id') ?: NULL,
                'updated_by'   => $this->session->userdata(SESS_HEAD . '_user_id'),
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->where('project_id', $this->input->post('project_id'));
            $this->db->update('tm_projects', $upd);
            $this->session->set_flashdata('alert_success', 'Project updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        // Filters
        $search   = $this->input->get('search');
        $f_status = $this->input->get('f_status');
        $f_prio   = $this->input->get('f_priority');

        // Pagination
        $this->load->library('pagination');
        $this->db->from('tm_projects p');
        $this->db->join('tm_users u', 'u.user_id = p.owner_id', 'left');
        $this->db->where('p.status_flag', 'Active');
        if ($search)   $this->db->like('p.name', $search);
        if ($f_status) $this->db->where('p.status', $f_status);
        if ($f_prio)   $this->db->where('p.priority', $f_prio);
        $data['total_records'] = $cnt = $this->db->count_all_results();

        $data['sno'] = $this->uri->segment(2, 0);
        $config['base_url']    = site_url($data['s_url']);
        $config['total_rows']  = $cnt;
        $config['per_page']    = 20;
        $config['uri_segment'] = 2;
        $config['attributes']  = array('class' => 'page-link');
        $config['full_tag_open']  = '<ul class="pagination pagination-sm no-margin pull-right">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open']   = '<li class="page-item">'; $config['num_tag_close']   = '</li>';
        $config['cur_tag_open']   = '<li class="page-item active"><a href="#" class="page-link">';
        $config['cur_tag_close']  = '<span class="sr-only">(current)</span></a></li>';
        $config['prev_tag_open']  = '<li class="page-item">'; $config['prev_tag_close']  = '</li>';
        $config['next_tag_open']  = '<li class="page-item">'; $config['next_tag_close']  = '</li>';
        $config['first_tag_open'] = '<li class="page-item">'; $config['first_tag_close'] = '</li>';
        $config['last_tag_open']  = '<li class="page-item">'; $config['last_tag_close']  = '</li>';
        $config['prev_link'] = 'Prev'; $config['next_link'] = 'Next';
        $this->pagination->initialize($config);

        $offset = $this->uri->segment(2, 0);
        $sql = "SELECT p.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active') as task_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active' AND status='done') as done_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active' AND due_date < CURDATE() AND status NOT IN ('done','closed')) as overdue_count
                FROM tm_projects p
                LEFT JOIN tm_users u ON u.user_id = p.owner_id
                WHERE p.status_flag = 'Active'";
        if ($search)   $sql .= " AND p.name LIKE '%" . $this->db->escape_like_str($search) . "%'";
        if ($f_status) $sql .= " AND p.status = '" . $this->db->escape_str($f_status) . "'";
        if ($f_prio)   $sql .= " AND p.priority = '" . $this->db->escape_str($f_prio) . "'";
        $sql .= " ORDER BY p.created_date DESC LIMIT {$offset}, {$config['per_page']}";

        $data['record_list']  = $this->db->query($sql)->result_array();
        $data['pagination']   = $this->pagination->create_links();
        $data['users_list']   = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['f_search']     = $search;
        $data['f_status']     = $f_status;
        $data['f_priority']   = $f_prio;

        $this->load->view('page/projects/project-list', $data);
    }

    // ── Project Detail ────────────────────────────────────────────────────────

    public function project_detail($project_id = 0)
    {
        $this->_auth();
        if (!$project_id) redirect('project-list');

        $data['js']    = 'projects/project-detail.inc';
        $data['title'] = 'Project Detail';

        $sql = "SELECT p.*, u.name as owner_name FROM tm_projects p
                LEFT JOIN tm_users u ON u.user_id = p.owner_id
                WHERE p.project_id = ? AND p.status_flag = 'Active'";
        $project = $this->db->query($sql, array($project_id))->row_array();
        if (!$project) redirect('project-list');

        $data['project'] = $project;

        // Stats
        $r = $this->db->query("SELECT status, COUNT(*) as cnt FROM tm_tasks WHERE project_id=? AND status_flag='Active' GROUP BY status", array($project_id))->result_array();
        $data['tasks_by_status'] = array();
        foreach ($r as $row) $data['tasks_by_status'][$row['status']] = $row['cnt'];

        $r = $this->db->query("SELECT priority, COUNT(*) as cnt FROM tm_tasks WHERE project_id=? AND status_flag='Active' GROUP BY priority", array($project_id))->result_array();
        $data['tasks_by_priority'] = array();
        foreach ($r as $row) $data['tasks_by_priority'][$row['priority']] = $row['cnt'];

        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE project_id=? AND status_flag='Active'", array($project_id))->row_array();
        $data['total_tasks'] = (int)$r['cnt'];

        $done = (isset($data['tasks_by_status']['done']) ? $data['tasks_by_status']['done'] : 0)
              + (isset($data['tasks_by_status']['closed']) ? $data['tasks_by_status']['closed'] : 0);
        $data['done_tasks'] = $done;
        $data['progress_pct'] = $data['total_tasks'] > 0 ? round(($done / $data['total_tasks']) * 100) : 0;

        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE project_id=? AND status_flag='Active' AND due_date < CURDATE() AND status NOT IN ('done','closed')", array($project_id))->row_array();
        $data['overdue_tasks'] = (int)$r['cnt'];

        // Recent tasks
        $sql = "SELECT t.*, u.name as assignee_name FROM tm_tasks t
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                WHERE t.project_id = ? AND t.status_flag = 'Active'
                ORDER BY t.created_date DESC LIMIT 15";
        $data['recent_tasks'] = $this->db->query($sql, array($project_id))->result_array();

        // Epics & Stories counts
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_epics WHERE project_id=? AND status_flag='Active'", array($project_id))->row_array();
        $data['epic_count'] = (int)$r['cnt'];

        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_user_stories WHERE project_id=? AND status_flag='Active'", array($project_id))->row_array();
        $data['story_count'] = (int)$r['cnt'];

        // Team Members
        $data['members'] = $this->db->query(
            "SELECT pm.member_id, pm.project_role, pm.added_date,
                    u.user_id, u.name, u.email, u.role, u.status
             FROM tm_project_members pm
             JOIN tm_users u ON u.user_id = pm.user_id
             WHERE pm.project_id = ?
             ORDER BY pm.added_date ASC",
            array($project_id)
        )->result_array();

        $member_ids = array_column($data['members'], 'user_id');
        $member_ids[] = $project['owner_id'];
        $in = implode(',', array_map('intval', array_unique($member_ids)));
        $data['available_users'] = $this->db->query(
            "SELECT user_id, name, email, role FROM tm_users
             WHERE status='Active'" . ($in ? " AND user_id NOT IN ({$in})" : "") . "
             ORDER BY name"
        )->result_array();

        $this->load->view('page/projects/project-detail', $data);
    }

    // ── Project Kanban ────────────────────────────────────────────────────────

    public function project_kanban()
    {
        $this->_auth();

        $data['js']    = 'projects/project-kanban.inc';
        $data['title'] = 'Project Kanban';

        $sql = "SELECT p.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active') as task_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active' AND status='done') as done_count
                FROM tm_projects p
                LEFT JOIN tm_users u ON u.user_id = p.owner_id
                WHERE p.status_flag = 'Active'
                ORDER BY p.created_date DESC";
        $data['projects'] = $this->db->query($sql)->result_array();

        $this->load->view('page/projects/project-kanban', $data);
    }
}

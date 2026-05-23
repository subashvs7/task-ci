<?php
class Project extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        if (!has_menu_permission('projects')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Projects.');
            redirect_to_fallback();
        }
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
                'key_name'     => NULL,
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'planning',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'manager_deadline_days' => (int)$this->input->post('manager_deadline_days'),
                'color'        => $this->input->post('color') ?: '#2c3e50',
                'stacks'       => $this->input->post('stacks') ?: NULL,
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
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'manager_deadline_days' => (int)$this->input->post('manager_deadline_days'),
                'stacks'       => $this->input->post('stacks') ?: NULL,
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
        
        $role = $this->session->userdata(SESS_HEAD . '_role');
        $uid = $this->session->userdata(SESS_HEAD . '_user_id');
        if ($role === 'team_leader' || $role === 'staff') {
            $this->db->group_start();
            $this->db->where('p.owner_id', $uid);
            $this->db->or_where("p.project_id IN (SELECT project_id FROM tm_project_handlers WHERE team_leader_id = {$uid} AND status='active')", NULL, FALSE);
            $this->db->or_where("p.project_id IN (SELECT project_id FROM tm_project_members WHERE user_id = {$uid})", NULL, FALSE);
            $this->db->or_where("p.project_id IN (SELECT project_id FROM tm_tasks WHERE assigned_to = {$uid})", NULL, FALSE);
            $this->db->group_end();
        } else if ($role === 'manager') {
            $this->db->where('p.owner_id', $uid);
        }

        $this->db->where('p.status_flag', 'Active');
        if ($search)   $this->db->where('p.project_id', (int)$search);
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
        $config['reuse_query_string'] = TRUE;
        $this->pagination->initialize($config);

        $offset = $this->uri->segment(2, 0);
        $sql = "SELECT p.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active') as task_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active' AND status='done') as done_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active' AND due_date < CURDATE() AND status NOT IN ('done','closed')) as overdue_count,
                    COALESCE((SELECT SUM(estimated_hours) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active'), 0) as calculated_time_hours
                FROM tm_projects p
                LEFT JOIN tm_users u ON u.user_id = p.owner_id ";
        
        if ($role === 'team_leader' || $role === 'staff') {
            $sql .= " WHERE p.status_flag = 'Active' AND (p.owner_id = {$uid} OR p.project_id IN (SELECT project_id FROM tm_project_handlers WHERE team_leader_id = {$uid} AND status='active') OR p.project_id IN (SELECT project_id FROM tm_project_members WHERE user_id = {$uid}) OR p.project_id IN (SELECT project_id FROM tm_tasks WHERE assigned_to = {$uid})) ";
        } else if ($role === 'manager') {
            $sql .= " WHERE p.status_flag = 'Active' AND p.owner_id = {$uid} ";
        } else {
            $sql .= " WHERE p.status_flag = 'Active' ";
        }
        if ($search)   $sql .= " AND p.project_id = " . (int)$search;
        if ($f_status) $sql .= " AND p.status = '" . $this->db->escape_str($f_status) . "'";
        if ($f_prio)   $sql .= " AND p.priority = '" . $this->db->escape_str($f_prio) . "'";
        $sql .= " ORDER BY p.created_date DESC LIMIT {$offset}, {$config['per_page']}";

        $data['record_list']  = $this->db->query($sql)->result_array();
        $data['pagination']   = $this->pagination->create_links();
        $data['users_list']   = $this->db->query("SELECT user_id, name FROM tm_users WHERE role='manager' AND status='Active' ORDER BY name")->result_array();
        
        $p_sql = "SELECT project_id, name FROM tm_projects WHERE status_flag = 'Active'";
        if ($role === 'team_leader' || $role === 'staff') {
            $p_sql .= " AND (owner_id = {$uid} OR project_id IN (SELECT project_id FROM tm_project_handlers WHERE team_leader_id = {$uid} AND status='active') OR project_id IN (SELECT project_id FROM tm_project_members WHERE user_id = {$uid}) OR project_id IN (SELECT project_id FROM tm_tasks WHERE assigned_to = {$uid}))";
        } else if ($role === 'manager') {
            $p_sql .= " AND owner_id = {$uid}";
        }
        $p_sql .= " ORDER BY name";
        $data['projects_dropdown'] = $this->db->query($p_sql)->result_array();

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

        // Tasks by Type
        $r = $this->db->query("SELECT type, COUNT(*) as cnt FROM tm_tasks WHERE project_id=? AND status_flag='Active' GROUP BY type", array($project_id))->result_array();
        $data['tasks_by_type'] = array();
        foreach ($r as $row) $data['tasks_by_type'][$row['type']] = $row['cnt'];

        // Tasks by Assignee
        $r = $this->db->query("SELECT u.name as assignee, COUNT(t.task_id) as cnt 
                               FROM tm_tasks t 
                               LEFT JOIN tm_users u ON u.user_id = t.assigned_to 
                               WHERE t.project_id=? AND t.status_flag='Active' 
                               GROUP BY t.assigned_to", array($project_id))->result_array();
        $data['tasks_by_assignee'] = array();
        foreach ($r as $row) {
            $name = $row['assignee'] ?: 'Unassigned';
            $data['tasks_by_assignee'][$name] = $row['cnt'];
        }

        // Time Logged Trend (Last 7 Days)
        $r = $this->db->query("
            SELECT DATE(log_date) as d, SUM(hours) as hrs 
            FROM tm_time_logs 
            WHERE task_id IN (SELECT task_id FROM tm_tasks WHERE project_id=? AND status_flag='Active') 
            AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
            AND status_flag='Active' 
            GROUP BY DATE(log_date) 
            ORDER BY d ASC
        ", array($project_id))->result_array();
        
        $trend = array();
        // Initialize last 7 days with 0
        for ($i = 6; $i >= 0; $i--) {
            $trend[date('Y-m-d', strtotime("-$i days"))] = 0;
        }
        foreach ($r as $row) {
            $trend[$row['d']] = round((float)$row['hrs'], 2);
        }
        $data['time_logged_trend'] = $trend;

        // Recent tasks
        $sql = "SELECT t.*, u.name as assignee_name, ur.name as reporter_name FROM tm_tasks t
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                WHERE t.project_id = ? AND t.status_flag = 'Active' AND t.story_id IS NULL
                ORDER BY t.created_date DESC LIMIT 15";
        $data['recent_tasks'] = $this->db->query($sql, array($project_id))->result_array();

        // Pending tasks
        $sql_pending = "SELECT t.*, u.name as assignee_name, ur.name as reporter_name FROM tm_tasks t
                        LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                        LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                        WHERE t.project_id = ? AND t.status_flag = 'Active' AND t.status NOT IN ('done','closed') AND t.story_id IS NULL
                        ORDER BY t.priority ASC, t.due_date ASC LIMIT 15";
        $data['pending_tasks'] = $this->db->query($sql_pending, array($project_id))->result_array();

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

        $data['users_list'] = $this->db->query(
            "SELECT user_id, name, email, role FROM tm_users
             WHERE status='Active'
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

        $role = $this->session->userdata(SESS_HEAD . '_role');
        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        $sql = "SELECT p.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active') as task_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active' AND status='done') as done_count
                FROM tm_projects p
                LEFT JOIN tm_users u ON u.user_id = p.owner_id ";
                
        if ($role === 'team_leader' || $role === 'staff') {
            $sql .= " WHERE p.status_flag = 'Active' AND (p.owner_id = {$uid} OR p.project_id IN (SELECT project_id FROM tm_project_handlers WHERE team_leader_id = {$uid} AND status='active') OR p.project_id IN (SELECT project_id FROM tm_project_members WHERE user_id = {$uid}) OR p.project_id IN (SELECT project_id FROM tm_tasks WHERE assigned_to = {$uid})) ";
        } else if ($role === 'manager') {
            $sql .= " WHERE p.status_flag = 'Active' AND p.owner_id = {$uid} ";
        } else {
            $sql .= " WHERE p.status_flag = 'Active' ";
        }
        
        $sql .= " ORDER BY p.created_date DESC";
        $data['projects'] = $this->db->query($sql)->result_array();

        $this->load->view('page/projects/project-kanban', $data);
    }


    public function project_gantt()
    {
        $this->_auth();

        $data['js']    = 'projects/project-gantt.inc';
        $data['title'] = 'Gantt Chart (Roadmap)';

        // Get all active projects for the dropdown
        $data['projects'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag = 'Active' ORDER BY name")->result_array();

        $this->load->view('page/projects/project-gantt', $data);
    }

    public function get_gantt_data($project_id = 0)
    {
        $this->_auth();
        header('Content-Type: application/json');
        
        $project_id = (int)$project_id;
        if (!$project_id) {
            echo json_encode(['data' => [], 'links' => []]);
            return;
        }

        $data = [];
        $links = [];

        // 1. Fetch Project
        $project = $this->db->query("SELECT * FROM tm_projects WHERE project_id = ?", [$project_id])->row_array();
        if (!$project) {
            echo json_encode(['data' => [], 'links' => []]);
            return;
        }

        $data[] = [
            'id' => 'P_' . $project['project_id'],
            'text' => $project['name'],
            'start_date' => $project['start_date'] ? date('Y-m-d H:i', strtotime($project['start_date'])) : date('Y-m-d 00:00'),
            'type' => 'project',
            'open' => true,
            'duration' => 1
        ];

        // 2. Fetch Epics
        $epics = $this->db->query("SELECT * FROM tm_epics WHERE project_id = ? AND status_flag = 'Active'", [$project_id])->result_array();
        foreach ($epics as $e) {
            $data[] = [
                'id' => 'E_' . $e['epic_id'],
                'text' => $e['name'],
                'start_date' => date('Y-m-d 00:00'), // DHTMLX auto-calculates if tasks fall under it
                'parent' => 'P_' . $project_id,
                'type' => 'project',
                'open' => true,
                'duration' => 1
            ];
        }

        // 3. Fetch Stories
        $stories = $this->db->query("SELECT * FROM tm_user_stories WHERE project_id = ? AND status_flag = 'Active'", [$project_id])->result_array();
        foreach ($stories as $s) {
            $parent = $s['epic_id'] ? 'E_' . $s['epic_id'] : 'P_' . $project_id;
            $data[] = [
                'id' => 'S_' . $s['story_id'],
                'text' => $s['name'],
                'parent' => $parent,
                'type' => 'project',
                'open' => true,
                'duration' => 1
            ];
        }

        // 4. Fetch Tasks
        $tasks = $this->db->query("
            SELECT t.task_id, t.title, t.status, t.started_at, t.due_date, t.created_date, t.story_id,
            (SELECT GROUP_CONCAT(depends_on_task_id) FROM tm_task_dependencies WHERE task_id = t.task_id) as dependencies
            FROM tm_tasks t
            WHERE t.project_id = ? AND t.status_flag = 'Active'
        ", [$project_id])->result_array();

        foreach ($tasks as $t) {
            $parent = $t['story_id'] ? 'S_' . $t['story_id'] : 'P_' . $project_id;
            $start = $t['started_at'] ? date('Y-m-d 00:00', strtotime($t['started_at'])) : date('Y-m-d 00:00', strtotime($t['created_date']));
            $end = $t['due_date'] ? date('Y-m-d 23:59', strtotime($t['due_date'])) : date('Y-m-d 23:59', strtotime($start . ' +2 days'));
            $progress = 0;
            if ($t['status'] === 'done') $progress = 1.0;
            elseif ($t['status'] === 'in_progress') $progress = 0.5;

            $data[] = [
                'id' => 'T_' . $t['task_id'],
                'text' => $t['title'],
                'start_date' => $start,
                'end_date' => $end,
                'parent' => $parent,
                'progress' => $progress,
                'type' => 'task',
                'color' => $this->_get_task_color($t['status'])
            ];

            if ($t['dependencies']) {
                $dep_ids = explode(',', $t['dependencies']);
                foreach ($dep_ids as $did) {
                    $links[] = [
                        'id' => 'L_' . $t['task_id'] . '_' . trim($did),
                        'source' => 'T_' . trim($did),
                        'target' => 'T_' . $t['task_id'],
                        'type' => "0" // 0 is end-to-start link in DHTMLX Gantt
                    ];
                }
            }
        }

        echo json_encode(['data' => $data, 'links' => $links]);
    }

    private function _get_task_color($status) {
        if ($status == 'done') return '#2ecc71';
        if ($status == 'in_progress') return '#3498db';
        if ($status == 'closed') return '#95a5a6';
        return '#f39c12'; // todo
    }
}

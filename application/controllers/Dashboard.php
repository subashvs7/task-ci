<?php
class Dashboard extends CI_Controller
{
    public function index()
    {
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");

        // if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
        //     redirect('login');

        // if (!has_menu_permission('dashboard')) {
        //     $this->session->set_flashdata('alert_error', 'You do not have permission to access the Dashboard.');
        //     redirect_to_fallback();
        // }

        $data['js']    = 'dashboard.inc';
        $data['title'] = 'Dashboard';

        $uid  = $this->session->userdata(SESS_HEAD . '_user_id');
        $role = $this->session->userdata(SESS_HEAD . '_role');


        $uid  = $this->session->userdata(SESS_HEAD . '_user_id');
        $role = $this->session->userdata(SESS_HEAD . '_role');
        // Auth string for projects without alias
        $proj_auth = "1=1";
        if ($role === 'team_leader' || $role === 'staff') {
            $proj_auth = "(owner_id = {$uid} OR project_id IN (SELECT project_id FROM tm_project_members WHERE user_id = {$uid}) OR project_id IN (SELECT project_id FROM tm_tasks WHERE assigned_to = {$uid}))";
        } else if ($role === 'manager') {
            $proj_auth = "owner_id = {$uid}";
        }

        // Total projects
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects WHERE status_flag='Active' AND $proj_auth")->row_array();
        $data['total_projects'] = $r['cnt'];

        // Active projects
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects WHERE status_flag='Active' AND status='active' AND $proj_auth")->row_array();
        $data['active_projects'] = $r['cnt'];

        // Total tasks
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active'")->row_array();
        $data['total_tasks'] = $r['cnt'];

        // Overdue tasks
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' AND due_date < CURDATE() AND status NOT IN ('done','closed')")->row_array();
        $data['overdue_tasks'] = $r['cnt'];

        // My tasks (assigned to me)
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' AND assigned_to = ?", array($uid))->row_array();
        $data['my_tasks'] = $r['cnt'];

        // My open tasks
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' AND assigned_to = ? AND status NOT IN ('done','closed')", array($uid))->row_array();
        $data['my_open_tasks'] = $r['cnt'];

        // Done tasks
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' AND status IN ('done','closed')")->row_array();
        $data['done_tasks'] = $r['cnt'];

        // Recent tasks (last 10)
        $recent_where = "t.status_flag = 'Active'";
        if ($role === 'team_leader') {
            $recent_where .= " AND (t.assigned_to = {$uid} OR t.created_by = {$uid})";
        } elseif ($role === 'staff') {
            $recent_where .= " AND t.assigned_to = {$uid}";
        }

        $sql = "SELECT t.*, p.name as project_name, u.name as assignee_name, ur.name as reporter_name,
                       uw.name as active_worker_name
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                LEFT JOIN tm_users uw ON uw.user_id = t.active_session_user
                WHERE {$recent_where}
                ORDER BY (t.work_session_status = 'active') DESC, t.created_date DESC
                LIMIT 4";
        $data['recent_tasks'] = $this->db->query($sql)->result_array();

        // Auth string for projects WITH 'p.' alias
        $p_auth = "1=1";
        if ($role === 'team_leader' || $role === 'staff') {
            $p_auth = "(p.owner_id = {$uid} OR p.project_id IN (SELECT project_id FROM tm_project_members WHERE user_id = {$uid}) OR p.project_id IN (SELECT project_id FROM tm_tasks WHERE assigned_to = {$uid}))";
        } else if ($role === 'manager') {
            $p_auth = "p.owner_id = {$uid}";
        }

        // Recent projects (last 5)
        $sql = "SELECT p.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id = p.project_id AND status_flag='Active') as task_count,
                    (SELECT COUNT(*) FROM tm_tasks WHERE project_id = p.project_id AND status_flag='Active' AND status='done') as done_count
                FROM tm_projects p
                LEFT JOIN tm_users u ON u.user_id = p.created_by
                WHERE p.status_flag = 'Active'
                ORDER BY p.created_date DESC
                LIMIT 5";
        $data['recent_projects'] = $this->db->query($sql)->result_array();

        // ---------------------------------------------------------
        // Calendar Data (FullCalendar) - Projects, Epics, Tasks, Subtasks
        // ---------------------------------------------------------
        $events = [];

        // 1. Tasks & Subtasks
        $cal_where = "t.status_flag = 'Active' AND (t.due_date IS NOT NULL OR t.created_date IS NOT NULL)";
        if ($role === 'manager') {
            $cal_where .= " AND p.owner_id = {$uid}";
        } elseif ($role === 'team_leader' || $role === 'staff') {
            $cal_where .= " AND (t.assigned_to = {$uid} OR t.created_by = {$uid})";
        }
        
        $cal_sql = "SELECT t.task_id as id, t.title, t.priority, t.status, t.created_date, t.due_date, 
                           IF(t.parent_task_id IS NOT NULL, 'Sub-task', IF(t.story_id IS NOT NULL, 'Story', 'Task')) as event_type, 
                           u.name as assignee_name, cb.name as assigned_by_name, t.estimated_hours
                    FROM tm_tasks t 
                    LEFT JOIN tm_projects p ON p.project_id = t.project_id
                    LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                    LEFT JOIN tm_users cb ON cb.user_id = t.created_by
                    WHERE {$cal_where}";
        $tasks_res = $this->db->query($cal_sql)->result_array();
        $events = array_merge($events, $tasks_res);

        // 2. Projects
        $proj_where = "p.status_flag = 'Active' AND (p.end_date IS NOT NULL OR p.created_date IS NOT NULL) AND $p_auth";
        $proj_sql = "SELECT p.project_id as id, p.name as title, p.priority, p.status, p.created_date, p.end_date as due_date, 
                            'Project' as event_type, u.name as assignee_name, cb.name as assigned_by_name, NULL as estimated_hours
                     FROM tm_projects p
                     LEFT JOIN tm_users u ON u.user_id = p.owner_id
                     LEFT JOIN tm_users cb ON cb.user_id = p.created_by
                     WHERE {$proj_where}";
        $proj_res = $this->db->query($proj_sql)->result_array();
        $events = array_merge($events, $proj_res);

        // 3. Epics
        $epic_where = "e.status_flag = 'Active' AND (e.created_date IS NOT NULL) AND $p_auth";
        $epic_sql = "SELECT e.epic_id as id, e.name as title, 'medium' as priority, e.status, e.created_date, NULL as due_date, 
                            'Epic' as event_type, '-' as assignee_name, cb.name as assigned_by_name, e.estimated_time as estimated_hours
                     FROM tm_epics e
                     LEFT JOIN tm_projects p ON p.project_id = e.project_id
                     LEFT JOIN tm_users cb ON cb.user_id = e.created_by
                     WHERE {$epic_where}";
        $epic_res = $this->db->query($epic_sql)->result_array();
        $events = array_merge($events, $epic_res);

        $data['calendar_events'] = $events;

        // Admin specific data
        $data['admin_team_leaders_count'] = 0;
        $data['admin_staff_count'] = 0;
        $data['admin_managers_count'] = 0;
        
        if ($role === 'admin') {
            $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_users WHERE role='team_leader' AND status='Active'")->row_array();
            $data['admin_team_leaders_count'] = (int)$r['cnt'];
            
            $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_users WHERE role='staff' AND status='Active'")->row_array();
            $data['admin_staff_count'] = (int)$r['cnt'];
            
            $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_users WHERE role='manager' AND status='Active'")->row_array();
            $data['admin_managers_count'] = (int)$r['cnt'];
        }

        // Project owner/manager's own project count (projects where they are the owner)
        $data['manager_project_count'] = null;
        $data['manager_project_active'] = null;
        if ($role === 'manager' || $role === 'team_leader') {
            $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects WHERE owner_id = ? AND status_flag = 'Active'", array($uid))->row_array();
            $data['manager_project_count'] = (int)$r['cnt'];
            $r2 = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects WHERE owner_id = ? AND status_flag = 'Active' AND status = 'active'", array($uid))->row_array();
            $data['manager_project_active'] = (int)$r2['cnt'];
            
            $data['projects_list'] = $this->db->query("SELECT project_id, name, end_date, manager_deadline_days FROM tm_projects WHERE status_flag='Active' AND owner_id = ? ORDER BY name", array($uid))->result_array();
            $data['team_leaders_list'] = $this->db->query("SELECT user_id, name FROM tm_users WHERE role='team_leader' AND status='Active' ORDER BY name")->result_array();
        }


        $this->load->view('page/dashboard', $data);
    }
}

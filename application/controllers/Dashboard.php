<?php
class Dashboard extends CI_Controller
{
    public function index()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        $data['js']    = 'dashboard.inc';
        $data['title'] = 'Dashboard';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        // Total projects
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects WHERE status_flag='Active'")->row_array();
        $data['total_projects'] = $r['cnt'];

        // Active projects
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects WHERE status_flag='Active' AND status='active'")->row_array();
        $data['active_projects'] = $r['cnt'];

        // Total tasks
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active'")->row_array();
        $data['total_tasks'] = $r['cnt'];

        // Tasks by status
        $q = $this->db->query("SELECT status, COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' GROUP BY status");
        $data['tasks_by_status'] = array();
        foreach ($q->result_array() as $row) {
            $data['tasks_by_status'][$row['status']] = $row['cnt'];
        }

        // Tasks by priority
        $q = $this->db->query("SELECT priority, COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' GROUP BY priority");
        $data['tasks_by_priority'] = array();
        foreach ($q->result_array() as $row) {
            $data['tasks_by_priority'][$row['priority']] = $row['cnt'];
        }

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
        $done = isset($data['tasks_by_status']['done']) ? $data['tasks_by_status']['done'] : 0;
        $done += isset($data['tasks_by_status']['closed']) ? $data['tasks_by_status']['closed'] : 0;
        $data['done_tasks'] = $done;

        // Recent tasks (last 10)
        $sql = "SELECT t.*, p.name as project_name, u.name as assignee_name
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                WHERE t.status_flag = 'Active'
                ORDER BY t.created_date DESC
                LIMIT 10";
        $data['recent_tasks'] = $this->db->query($sql)->result_array();

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

        // Tasks completed this week
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' AND status='done' AND WEEK(completed_at) = WEEK(NOW()) AND YEAR(completed_at) = YEAR(NOW())")->row_array();
        $data['done_this_week'] = $r['cnt'];

        // Tasks created this week
        $r = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks WHERE status_flag='Active' AND WEEK(created_date) = WEEK(NOW()) AND YEAR(created_date) = YEAR(NOW())")->row_array();
        $data['created_this_week'] = $r['cnt'];

        $this->load->view('page/dashboard', $data);
    }
}

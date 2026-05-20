<?php
class Report extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    public function task_report()
    {
        $this->_auth();

        $data['js']    = 'reports/task-report.inc';
        $data['title'] = 'Task Report';
        $data['s_url'] = 'task-report';

        $f_project   = $this->input->get('project_id');
        $f_assignee  = $this->input->get('assignee_id');
        $f_status    = $this->input->get('f_status');
        $f_priority  = $this->input->get('f_priority');
        $f_type      = $this->input->get('f_type');
        $f_date_from = $this->input->get('date_from');
        $f_date_to   = $this->input->get('date_to');

        $where = "t.status_flag='Active'";
        if ($f_project)   $where .= " AND t.project_id=" . (int)$f_project;
        if ($f_assignee)  $where .= " AND t.assigned_to=" . (int)$f_assignee;
        if ($f_status)    $where .= " AND t.status='" . $this->db->escape_str($f_status) . "'";
        if ($f_priority)  $where .= " AND t.priority='" . $this->db->escape_str($f_priority) . "'";
        if ($f_type)      $where .= " AND t.type='" . $this->db->escape_str($f_type) . "'";
        if ($f_date_from) $where .= " AND DATE(t.created_date)>='" . $this->db->escape_str($f_date_from) . "'";
        if ($f_date_to)   $where .= " AND DATE(t.created_date)<='" . $this->db->escape_str($f_date_to) . "'";

        $data['tasks_by_status']   = $this->db->query("SELECT status, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY status")->result_array();
        $data['tasks_by_priority'] = $this->db->query("SELECT priority, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY priority")->result_array();
        $data['tasks_by_type']     = $this->db->query("SELECT type, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY type")->result_array();
        $data['tasks_by_assignee'] = $this->db->query(
            "SELECT u.name, COUNT(*) as cnt FROM tm_tasks t LEFT JOIN tm_users u ON u.user_id=t.assigned_to WHERE {$where} GROUP BY t.assigned_to ORDER BY cnt DESC LIMIT 10"
        )->result_array();
        $data['tasks_by_project']  = $this->db->query(
            "SELECT p.name, COUNT(*) as cnt FROM tm_tasks t LEFT JOIN tm_projects p ON p.project_id=t.project_id WHERE {$where} GROUP BY t.project_id ORDER BY cnt DESC LIMIT 10"
        )->result_array();

        $total = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks t WHERE {$where}")->row_array();
        $done  = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks t WHERE {$where} AND t.status='done'")->row_array();
        $overdue = $this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks t WHERE {$where} AND t.due_date < NOW() AND t.status NOT IN ('done','closed')")->row_array();

        $data['total_tasks']   = (int)$total['cnt'];
        $data['done_tasks']    = (int)$done['cnt'];
        $data['overdue_tasks'] = (int)$overdue['cnt'];
        $data['open_tasks']    = $data['total_tasks'] - $data['done_tasks'];

        $data['record_list'] = $this->db->query(
            "SELECT t.*, p.name as project_name, u.name as assignee_name
             FROM tm_tasks t
             LEFT JOIN tm_projects p ON p.project_id=t.project_id
             LEFT JOIN tm_users u ON u.user_id=t.assigned_to
             WHERE {$where}
             ORDER BY t.created_date DESC
             LIMIT 200"
        )->result_array();

        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['f_project']     = $f_project;
        $data['f_assignee']    = $f_assignee;
        $data['f_status']      = $f_status;
        $data['f_priority']    = $f_priority;
        $data['f_type']        = $f_type;
        $data['f_date_from']   = $f_date_from;
        $data['f_date_to']     = $f_date_to;

        $this->load->view('page/reports/task-report', $data);
    }

    public function project_report()
    {
        $this->_auth();

        $data['js']    = 'reports/project-report.inc';
        $data['title'] = 'Project Report';
        $data['s_url'] = 'project-report';

        $f_status   = $this->input->get('f_status');
        $f_priority = $this->input->get('f_priority');

        $where = "p.status_flag='Active'";
        if ($f_status)   $where .= " AND p.status='" . $this->db->escape_str($f_status) . "'";
        if ($f_priority) $where .= " AND p.priority='" . $this->db->escape_str($f_priority) . "'";

        $data['projects_by_status']   = $this->db->query("SELECT status, COUNT(*) as cnt FROM tm_projects p WHERE {$where} GROUP BY status")->result_array();
        $data['projects_by_priority'] = $this->db->query("SELECT priority, COUNT(*) as cnt FROM tm_projects p WHERE {$where} GROUP BY priority")->result_array();

        $data['record_list'] = $this->db->query(
            "SELECT p.*,
                (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status_flag='Active') as total_tasks,
                (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND status='done' AND status_flag='Active') as done_tasks,
                (SELECT COUNT(*) FROM tm_tasks WHERE project_id=p.project_id AND due_date < NOW() AND status NOT IN ('done','closed') AND status_flag='Active') as overdue_tasks,
                (SELECT COUNT(*) FROM tm_user_stories WHERE project_id=p.project_id AND status_flag='Active') as story_count,
                (SELECT COUNT(*) FROM tm_epics WHERE project_id=p.project_id AND status_flag='Active') as epic_count
             FROM tm_projects p
             WHERE {$where}
             ORDER BY p.created_date DESC"
        )->result_array();

        $total    = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects p WHERE {$where}")->row_array();
        $active   = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects p WHERE {$where} AND p.status='active'")->row_array();
        $done     = $this->db->query("SELECT COUNT(*) as cnt FROM tm_projects p WHERE {$where} AND p.status='completed'")->row_array();

        $data['total_projects']     = (int)$total['cnt'];
        $data['active_projects']    = (int)$active['cnt'];
        $data['completed_projects'] = (int)$done['cnt'];
        $data['f_status']           = $f_status;
        $data['f_priority']         = $f_priority;

        $this->load->view('page/reports/project-report', $data);
    }
}

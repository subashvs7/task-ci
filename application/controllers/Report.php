<?php
class Report extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        if (!has_menu_permission('reports')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Reports.');
            redirect_to_fallback();
        }
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

        $unified_sql = "
            SELECT * FROM (
                SELECT 'Epic' as item_type, '' as task_type, e.name as title, p.name as project_name, '' as parent_name, 
                       '' as assignee_name, ur.name as reporter_name, e.status, e.priority, 
                       ROUND(e.estimated_time/60, 2) as estimated_hours, 0 as logged_hours, '' as due_date, e.created_date,
                       e.project_id, NULL as assigned_to, e.status_flag
                FROM tm_epics e
                LEFT JOIN tm_projects p ON p.project_id = e.project_id
                LEFT JOIN tm_users ur ON ur.user_id = e.created_by
                
                UNION ALL
                
                SELECT 'Story' as item_type, '' as task_type, s.name as title, p.name as project_name, COALESCE(e.name, '') as parent_name,
                       u.name as assignee_name, ur.name as reporter_name, s.status, s.priority,
                       0 as estimated_hours, 0 as logged_hours, '' as due_date, s.created_date,
                       s.project_id, s.assignee_id as assigned_to, s.status_flag
                FROM tm_user_stories s
                LEFT JOIN tm_projects p ON p.project_id = s.project_id
                LEFT JOIN tm_epics e ON e.epic_id = s.epic_id
                LEFT JOIN tm_users u ON u.user_id = s.assignee_id
                LEFT JOIN tm_users ur ON ur.user_id = s.reporter_id
                
                UNION ALL
                
                SELECT IF(t.parent_task_id IS NOT NULL, 'Sub Task', 'Task') as item_type, t.type as task_type, t.title, p.name as project_name, 
                       COALESCE(s.name, e.name, '') as parent_name,
                       u.name as assignee_name, ur.name as reporter_name, t.status, t.priority,
                       t.estimated_hours, COALESCE((SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active'), 0) as logged_hours,
                       IFNULL(t.due_date, '') as due_date, t.created_date,
                       t.project_id, t.assigned_to, t.status_flag
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_user_stories s ON s.story_id = t.story_id
                LEFT JOIN tm_epics e ON e.epic_id = t.epic_id
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                
                UNION ALL
                
                SELECT 'Sub Task' as item_type, '' as task_type, st.title, p.name as project_name, COALESCE(t.title, '') as parent_name,
                       '' as assignee_name, ur.name as reporter_name, IF(st.is_done=1, 'done', 'todo') as status, '' as priority,
                       0 as estimated_hours, 0 as logged_hours, '' as due_date, st.created_date,
                       t.project_id, NULL as assigned_to, st.status_flag
                FROM tm_subtasks st
                JOIN tm_tasks t ON t.task_id = st.task_id
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_users ur ON ur.user_id = st.created_by
            ) as all_items
            WHERE status_flag='Active'
        ";

        if ($f_project)   $unified_sql .= " AND project_id=" . (int)$f_project;
        if ($f_assignee)  $unified_sql .= " AND assigned_to=" . (int)$f_assignee;
        if ($f_status)    $unified_sql .= " AND status='" . $this->db->escape_str($f_status) . "'";
        if ($f_priority)  $unified_sql .= " AND priority='" . $this->db->escape_str($f_priority) . "'";
        if ($f_date_from) $unified_sql .= " AND DATE(created_date)>='" . $this->db->escape_str($f_date_from) . "'";
        if ($f_date_to)   $unified_sql .= " AND DATE(created_date)<='" . $this->db->escape_str($f_date_to) . "'";

        $unified_sql .= " ORDER BY created_date DESC";

        if ($this->input->get('export') === 'excel') {
            $export_list = $this->db->query($unified_sql)->result_array();

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Unified_Report_' . date('Ymd_His') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('Item Type', 'Title', 'Project', 'Parent Item', 'Assignee', 'Created By', 'Status', 'Priority', 'Task Type', 'Est. Time (h)', 'Logged Time (h)', 'Due Date'));

            $sl = TASK_STATUS_OPT;
            $pl = TASK_PRIORITY_OPT;
            $tl = TASK_TYPE_OPT;

            foreach ($export_list as $row) {
                $status = isset($sl[$row['status']]) ? $sl[$row['status']] : $row['status'];
                $priority = isset($pl[$row['priority']]) ? $pl[$row['priority']] : $row['priority'];
                $task_type = isset($tl[$row['task_type']]) ? $tl[$row['task_type']] : $row['task_type'];
                
                fputcsv($output, array(
                    $row['item_type'],
                    $row['title'],
                    $row['project_name'] ? $row['project_name'] : '',
                    $row['parent_name'] ? $row['parent_name'] : '',
                    $row['assignee_name'] ? $row['assignee_name'] : '',
                    $row['reporter_name'] ? $row['reporter_name'] : '',
                    $status,
                    $priority,
                    $task_type,
                    $row['estimated_hours'] ? $row['estimated_hours'] : '0',
                    $row['logged_hours'] ? $row['logged_hours'] : '0',
                    $row['due_date']
                ));
            }
            fclose($output);
            exit;
        }

        $data['tasks_by_status']   = $this->db->query("SELECT status, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY status")->result_array();
        $data['tasks_by_priority'] = $this->db->query("SELECT priority, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY priority")->result_array();
        $data['tasks_by_type']     = $this->db->query("SELECT type, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY type")->result_array();
        $data['tasks_by_date']     = $this->db->query("SELECT DATE(created_date) as t_date, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY DATE(created_date) ORDER BY t_date ASC LIMIT 30")->result_array();
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

        $data['record_list'] = $this->db->query($unified_sql . " LIMIT 500")->result_array();

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

    /**
     * Unified Capacity & Deadline Feasibility Analysis Report
     */
    public function feasibility_analysis()
    {
        $this->_auth();
        $role = $this->session->userdata(SESS_HEAD . '_role');
        
        // Allowed for Admins, Managers, and Team Leaders
        if (!in_array($role, array('admin', 'manager', 'team_leader'))) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Feasibility Analysis.');
            redirect_to_fallback();
        }

        $data['title'] = 'Capacity & Deadline Feasibility';
        $data['js']    = '';
        $data['s_url'] = 'feasibility-analysis';

        // Fetch all active staff list
        $staff_list = $this->db->query("
            SELECT user_id, name, email 
            FROM tm_users 
            WHERE role = 'staff' AND status = 'Active' 
            ORDER BY name
        ")->result_array();

        $calculated_records = array();
        $daily_capacity = 8.0; // standard 8 hours working day

        foreach ($staff_list as $staff) {
            // Retrieve active, uncompleted tasks assigned to this staff member
            $tasks = $this->db->query("
                SELECT t.task_id, t.title, t.due_date, t.estimated_hours,
                       (SELECT COALESCE(SUM(hours), 0) FROM tm_time_logs WHERE task_id = t.task_id AND status_flag='Active') as logged_hours
                FROM tm_tasks t
                WHERE t.assigned_to = ? AND t.status_flag = 'Active' AND t.status NOT IN ('done','closed')
                ORDER BY t.due_date ASC
            ", array($staff['user_id']))->result_array();

            $total_rem_hours = 0.0;
            $overdue_count = 0;
            $earliest_due = null;

            foreach ($tasks as $t) {
                $rem = max(0, (float)$t['estimated_hours'] - (float)$t['logged_hours']);
                $total_rem_hours += $rem;

                if ($t['due_date']) {
                    if ($earliest_due === null || $t['due_date'] < $earliest_due) {
                        $earliest_due = $t['due_date'];
                    }
                    if (strtotime($t['due_date']) < time()) {
                        $overdue_count++;
                    }
                }
            }

            // Calculate workdays left until the earliest upcoming deadline
            $working_days = 0;
            if ($earliest_due) {
                $working_days = calculate_working_days(date('Y-m-d'), $earliest_due);
            }
            
            $total_capacity_hours = $working_days * $daily_capacity;
            
            // Compute Feasibility Index
            $index = 0;
            if ($total_capacity_hours > 0) {
                $index = round(($total_rem_hours / $total_capacity_hours) * 100);
            } elseif ($total_rem_hours > 0) {
                $index = 999; // Represents overloading (effort remaining with 0 workdays left)
            }

            $calculated_records[] = array(
                'staff'            => $staff,
                'task_count'       => count($tasks),
                'overdue_count'    => $overdue_count,
                'remaining_hours'  => $total_rem_hours,
                'earliest_due'     => $earliest_due,
                'working_days'     => $working_days,
                'capacity_hours'   => $total_capacity_hours,
                'feasibility_index'=> $index
            );
        }

        $data['analysis_data'] = $calculated_records;
        $this->load->view('page/reports/feasibility-analysis', $data);
    }
}

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
        $f_leader    = $this->input->get('team_leader_id');
        $f_assignee  = $this->input->get('assignee_id');
        $f_status    = $this->input->get('f_status');
        $f_priority  = $this->input->get('f_priority');
        $f_type      = $this->input->get('f_type');
        $f_date_from = $this->input->get('date_from');
        $f_date_to   = $this->input->get('date_to');

        $where = "t.status_flag='Active'";
        if ($f_project)   $where .= " AND t.project_id=" . (int)$f_project;
        if ($f_assignee)  $where .= " AND t.assigned_to=" . (int)$f_assignee;
        if ($f_leader)    $where .= " AND (t.reporter_id=" . (int)$f_leader . " OR t.created_by=" . (int)$f_leader . ")";
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
                       e.project_id, NULL as assigned_to, e.status_flag, e.created_by as reporter_id
                FROM tm_epics e
                LEFT JOIN tm_projects p ON p.project_id = e.project_id
                LEFT JOIN tm_users ur ON ur.user_id = e.created_by
                
                UNION ALL
                
                SELECT 'Story' as item_type, '' as task_type, s.name as title, p.name as project_name, COALESCE(e.name, '') as parent_name,
                       u.name as assignee_name, ur.name as reporter_name, s.status, s.priority,
                       0 as estimated_hours, 0 as logged_hours, '' as due_date, s.created_date,
                       s.project_id, s.assignee_id as assigned_to, s.status_flag, COALESCE(e.created_by, s.assignee_id, s.reporter_id) as reporter_id
                FROM tm_user_stories s
                LEFT JOIN tm_projects p ON p.project_id = s.project_id
                LEFT JOIN tm_epics e ON e.epic_id = s.epic_id
                LEFT JOIN tm_users u ON u.user_id = s.assignee_id
                LEFT JOIN tm_users ur ON ur.user_id = s.reporter_id
                
                UNION ALL
                
                SELECT IF(t.parent_task_id IS NOT NULL, 'Sub Task', 'Task') as item_type, t.type as task_type, t.title, p.name as project_name, 
                       COALESCE(pt.title, s.name, e.name, '') as parent_name,
                       u.name as assignee_name, ur.name as reporter_name, t.status, t.priority,
                       t.estimated_hours, COALESCE((SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active'), 0) as logged_hours,
                       IFNULL(t.due_date, '') as due_date, t.created_date,
                       t.project_id, t.assigned_to, t.status_flag, COALESCE(e.created_by, s.assignee_id, t.reporter_id) as reporter_id
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_tasks pt ON pt.task_id = t.parent_task_id
                LEFT JOIN tm_user_stories s ON s.story_id = t.story_id
                LEFT JOIN tm_epics e ON e.epic_id = t.epic_id
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                

            ) as all_items
            WHERE status_flag='Active'
        ";

        if ($f_project)   $unified_sql .= " AND project_id=" . (int)$f_project;
        if ($f_assignee)  $unified_sql .= " AND assigned_to=" . (int)$f_assignee;
        if ($f_leader)    $unified_sql .= " AND reporter_id=" . (int)$f_leader;
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
                    $row['estimated_hours'] ? $row['estimated_hours'] . ' hrs' : '0 hrs',
                    $row['logged_hours'] ? $row['logged_hours'] . ' hrs' : '0 hrs',
                    $row['due_date']
                ));
            }
            fclose($output);
            exit;
        }

        $data['tasks_by_status']   = $this->db->query("SELECT status, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY status")->result_array();
        $data['tasks_by_priority'] = $this->db->query("SELECT priority, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY priority")->result_array();
        $data['tasks_by_type']     = $this->db->query("SELECT type, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY type")->result_array();
        $data['tasks_by_date']     = $this->db->query("SELECT DATE(created_date) as t_date, COUNT(*) as cnt FROM tm_tasks t WHERE {$where} GROUP BY DATE(created_date) ORDER BY t_date DESC LIMIT 30")->result_array();
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
        
        // Cascading dropdown lists: load all if no project is selected
        if ($f_project) {
            $data['team_leaders_list'] = $this->db->query("
                SELECT DISTINCT u.user_id, u.name 
                FROM tm_users u
                LEFT JOIN tm_projects p ON p.owner_id = u.user_id AND p.project_id = ? AND p.status_flag='Active'
                LEFT JOIN tm_project_members m ON m.user_id = u.user_id AND m.project_id = ?
                WHERE u.role='team_leader' AND u.status='Active'
                  AND (p.project_id IS NOT NULL OR m.member_id IS NOT NULL OR u.user_id IN (SELECT reporter_id FROM tm_tasks WHERE project_id = ? AND status_flag='Active') OR u.user_id IN (SELECT created_by FROM tm_tasks WHERE project_id = ? AND status_flag='Active'))
                ORDER BY u.name
            ", array($f_project, $f_project, $f_project, $f_project))->result_array();

            $data['staff_list'] = $this->db->query("
                SELECT DISTINCT u.user_id, u.name 
                FROM tm_users u
                LEFT JOIN tm_project_members m ON m.user_id = u.user_id AND m.project_id = ?
                WHERE u.role='staff' AND u.status='Active'
                  AND (m.member_id IS NOT NULL OR u.user_id IN (SELECT assigned_to FROM tm_tasks WHERE project_id = ? AND status_flag='Active' AND assigned_to IS NOT NULL))
                ORDER BY u.name
            ", array($f_project, $f_project))->result_array();
        } else {
            $data['team_leaders_list'] = $this->db->query("SELECT user_id, name FROM tm_users WHERE role='team_leader' AND status='Active' ORDER BY name")->result_array();
            $data['staff_list'] = $this->db->query("SELECT user_id, name FROM tm_users WHERE role='staff' AND status='Active' ORDER BY name")->result_array();
        }

        // Build tree wise flow if project is selected
        $data['tree_rows'] = array();
        $data['project_details'] = array();
        
        if ($f_project) {
            $data['project_details'] = $this->db->query("SELECT * FROM tm_projects WHERE project_id = ?", array($f_project))->row_array();
            
            $epics = $this->db->query("
                SELECT 'Epic' as item_type, e.epic_id, e.name as title, '' as parent_name,
                       '' as assignee_name, ur.name as reporter_name, e.status, e.priority,
                       ROUND(e.estimated_time/60, 2) as estimated_hours, 0 as logged_hours, '' as due_date, e.created_date,
                       e.project_id, NULL as assigned_to, e.created_by as reporter_id, NULL as active_worker_name, NULL as work_session_status, NULL as open_session_start
                FROM tm_epics e
                LEFT JOIN tm_users ur ON ur.user_id = e.created_by
                WHERE e.project_id = ? AND e.status_flag = 'Active'
                ORDER BY e.created_date ASC
            ", array($f_project))->result_array();

            $stories = $this->db->query("
                SELECT 'Story' as item_type, s.story_id, s.epic_id, s.name as title, e.name as parent_name,
                       u.name as assignee_name, ur.name as reporter_name, s.status, s.priority,
                       0 as estimated_hours, 0 as logged_hours, '' as due_date, s.created_date,
                       s.project_id, s.assignee_id as assigned_to, COALESCE(e.created_by, s.assignee_id, s.reporter_id) as reporter_id,
                       NULL as active_worker_name, NULL as work_session_status, NULL as open_session_start
                FROM tm_user_stories s
                LEFT JOIN tm_epics e ON e.epic_id = s.epic_id
                LEFT JOIN tm_users u ON u.user_id = s.assignee_id
                LEFT JOIN tm_users ur ON ur.user_id = s.reporter_id
                WHERE s.project_id = ? AND s.status_flag = 'Active'
                ORDER BY s.created_date ASC
            ", array($f_project))->result_array();

            $tasks = $this->db->query("
                SELECT IF(t.parent_task_id IS NOT NULL, 'Sub Task', 'Task') as item_type, t.task_id, t.parent_task_id, t.story_id, t.epic_id, t.title,
                       COALESCE(pt.title, s.name, e.name, '') as parent_name,
                       u.name as assignee_name, ur.name as reporter_name, t.status, t.priority,
                       t.estimated_hours, COALESCE((SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active'), 0) as logged_hours,
                       IFNULL(t.due_date, '') as due_date, t.created_date,
                       t.project_id, t.assigned_to, COALESCE(e.created_by, s.assignee_id, t.reporter_id) as reporter_id,
                       uw.name as active_worker_name, t.work_session_status,
                       (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                       t.type as task_type
                FROM tm_tasks t
                LEFT JOIN tm_tasks pt ON pt.task_id = t.parent_task_id
                LEFT JOIN tm_user_stories s ON s.story_id = t.story_id
                LEFT JOIN tm_epics e ON e.epic_id = t.epic_id
                LEFT JOIN tm_users u ON u.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                LEFT JOIN tm_users uw ON uw.user_id = t.active_session_user
                WHERE t.project_id = ? AND t.status_flag = 'Active'
                ORDER BY t.created_date ASC
            ", array($f_project))->result_array();

            $subtasks = array();

            // Build structural maps
            $epic_map = array();
            $story_map = array();
            $task_map = array();

            foreach ($epics as $e) {
                $e['stories'] = array();
                $e['direct_tasks'] = array();
                $e['matches_filter'] = false;
                $e['keep'] = false;
                $epic_map[$e['epic_id']] = $e;
            }

            foreach ($stories as $s) {
                $s['tasks'] = array();
                $s['matches_filter'] = false;
                $s['keep'] = false;
                $story_map[$s['story_id']] = $s;
            }

            foreach ($tasks as $t) {
                $t['sub_tasks'] = array();
                $t['checklist'] = array();
                $t['matches_filter'] = false;
                $t['keep'] = false;
                $task_map[$t['task_id']] = $t;
            }

            // Group checklist subtasks under parent tasks
            foreach ($subtasks as $st) {
                $st['matches_filter'] = false;
                $st['keep'] = false;
                if (isset($task_map[$st['task_id']])) {
                    $task_map[$st['task_id']]['checklist'][] = $st;
                }
            }

            // Link tasks to parent tasks
            foreach ($task_map as $tid => $t) {
                if ($t['parent_task_id'] && isset($task_map[$t['parent_task_id']])) {
                    $task_map[$t['parent_task_id']]['sub_tasks'][] = $tid;
                }
            }

            // Link root tasks to stories/epics
            $root_epics = array();
            $root_stories_no_epic = array();
            $root_tasks_direct = array();

            foreach ($task_map as $tid => $t) {
                if (!$t['parent_task_id']) {
                    if ($t['story_id'] && isset($story_map[$t['story_id']])) {
                        $story_map[$t['story_id']]['tasks'][] = $tid;
                    } elseif ($t['epic_id'] && isset($epic_map[$t['epic_id']])) {
                        $epic_map[$t['epic_id']]['direct_tasks'][] = $tid;
                    } else {
                        $root_tasks_direct[] = $tid;
                    }
                }
            }

            // Link stories to epics
            foreach ($story_map as $sid => $s) {
                if ($s['epic_id'] && isset($epic_map[$s['epic_id']])) {
                    $epic_map[$s['epic_id']]['stories'][] = $sid;
                } else {
                    $root_stories_no_epic[] = $sid;
                }
            }

            foreach ($epic_map as $eid => $e) {
                $root_epics[] = $eid;
            }

            // Filter match logic
            $match_node = function($node) use ($f_leader, $f_assignee, $f_status, $f_priority, $f_type) {
                if ($f_leader && (int)$node['reporter_id'] !== (int)$f_leader) {
                    return false;
                }
                if ($f_assignee && $node['assigned_to'] !== null && (int)$node['assigned_to'] !== (int)$f_assignee) {
                    return false;
                }
                if ($f_status && $node['status'] !== $f_status) {
                    return false;
                }
                if ($f_priority && $node['priority'] && $node['priority'] !== '-' && $node['priority'] !== $f_priority) {
                    return false;
                }
                if ($f_type && isset($node['task_type']) && $node['task_type'] && $node['task_type'] !== '-' && $node['task_type'] !== $f_type) {
                    return false;
                }
                return true;
            };

            // Recursive task evaluation
            $evaluate_task = null;
            $evaluate_task = function($tid) use (&$task_map, $match_node, &$evaluate_task) {
                $t = &$task_map[$tid];
                $t['matches_filter'] = $match_node($t);
                $keep = $t['matches_filter'];

                foreach ($t['checklist'] as &$chk) {
                    $chk['matches_filter'] = $match_node($chk);
                    if ($chk['matches_filter']) {
                        $keep = true;
                    }
                }
                unset($chk);

                foreach ($t['sub_tasks'] as $sub_id) {
                    if ($evaluate_task($sub_id)) {
                        $keep = true;
                    }
                }

                $t['keep'] = $keep;
                return $keep;
            };

            foreach ($task_map as $tid => $t) {
                if (!$t['parent_task_id']) {
                    $evaluate_task($tid);
                }
            }

            // Evaluate stories
            $evaluate_story = function($sid) use (&$story_map, &$task_map, $match_node) {
                $s = &$story_map[$sid];
                $s['matches_filter'] = $match_node($s);
                $keep = $s['matches_filter'];

                foreach ($s['tasks'] as $tid) {
                    if ($task_map[$tid]['keep']) {
                        $keep = true;
                    }
                }

                $s['keep'] = $keep;
                return $keep;
            };

            foreach ($story_map as $sid => $s) {
                $evaluate_story($sid);
            }

            // Evaluate epics
            $evaluate_epic = function($eid) use (&$epic_map, &$story_map, &$task_map, $match_node) {
                $e = &$epic_map[$eid];
                $e['matches_filter'] = $match_node($e);
                $keep = $e['matches_filter'];

                foreach ($e['stories'] as $sid) {
                    if ($story_map[$sid]['keep']) {
                        $keep = true;
                    }
                }

                foreach ($e['direct_tasks'] as $tid) {
                    if ($task_map[$tid]['keep']) {
                        $keep = true;
                    }
                }

                $e['keep'] = $keep;
                return $keep;
            };

            foreach ($epic_map as $eid => $e) {
                $evaluate_epic($eid);
            }

            // Flatten pruned tree
            $flat_rows = array();
            
            $flatten_task = null;
            $flatten_task = function($tid, $level, $is_root = true) use (&$task_map, &$flat_rows, &$flatten_task) {
                $t = $task_map[$tid];
                if (!$t['keep']) return;

                $flat_rows[] = array(
                    'level' => $level,
                    'item_type' => (isset($t['task_type']) && $t['task_type'] === 'sub_task' || !$is_root) ? 'Sub Task' : 'Task',
                    'title' => $t['title'],
                    'parent_name' => $t['parent_name'],
                    'assignee_name' => $t['assignee_name'],
                    'reporter_name' => $t['reporter_name'],
                    'status' => $t['status'],
                    'priority' => $t['priority'],
                    'estimated_hours' => $t['estimated_hours'],
                    'logged_hours' => $t['logged_hours'],
                    'active_worker_name' => $t['active_worker_name'],
                    'work_session_status' => $t['work_session_status'],
                    'open_session_start' => $t['open_session_start'],
                    'task_id' => $t['task_id']
                );

                $next_level = $level + 1;

                foreach ($t['checklist'] as $chk) {
                    if (!$chk['keep']) continue;
                    $flat_rows[] = array(
                        'level' => $next_level,
                        'item_type' => 'Sub Task',
                        'title' => $chk['title'],
                        'parent_name' => $t['title'],
                        'assignee_name' => $chk['assignee_name'],
                        'reporter_name' => $chk['reporter_name'],
                        'status' => $chk['status'],
                        'priority' => '',
                        'estimated_hours' => 0,
                        'logged_hours' => 0,
                        'active_worker_name' => null,
                        'work_session_status' => null,
                        'open_session_start' => null,
                        'task_id' => null
                    );
                }

                foreach ($t['sub_tasks'] as $sub_id) {
                    $flatten_task($sub_id, $next_level, false);
                }
            };

            $flatten_story = function($sid, $level) use (&$story_map, &$flatten_task, &$flat_rows) {
                $s = $story_map[$sid];
                if (!$s['keep']) return;

                $flat_rows[] = array(
                    'level' => $level,
                    'item_type' => 'Story',
                    'title' => $s['title'],
                    'parent_name' => $s['parent_name'],
                    'assignee_name' => $s['assignee_name'],
                    'reporter_name' => $s['reporter_name'],
                    'status' => $s['status'],
                    'priority' => $s['priority'],
                    'estimated_hours' => 0,
                    'logged_hours' => 0,
                    'active_worker_name' => null,
                    'work_session_status' => null,
                    'open_session_start' => null,
                    'task_id' => null
                );

                foreach ($s['tasks'] as $tid) {
                    $flatten_task($tid, $level + 1, true);
                }
            };

            $flatten_epic = function($eid, $level) use (&$epic_map, &$flatten_story, &$flatten_task, &$flat_rows) {
                $e = $epic_map[$eid];
                if (!$e['keep']) return;

                $flat_rows[] = array(
                    'level' => $level,
                    'item_type' => 'Epic',
                    'title' => $e['title'],
                    'parent_name' => '',
                    'assignee_name' => '',
                    'reporter_name' => $e['reporter_name'],
                    'status' => $e['status'],
                    'priority' => $e['priority'],
                    'estimated_hours' => $e['estimated_hours'],
                    'logged_hours' => 0,
                    'active_worker_name' => null,
                    'work_session_status' => null,
                    'open_session_start' => null,
                    'task_id' => null
                );

                foreach ($e['direct_tasks'] as $tid) {
                    $flatten_task($tid, $level + 1, true);
                }

                foreach ($e['stories'] as $sid) {
                    $flatten_story($sid, $level + 1);
                }
            };

            foreach ($root_tasks_direct as $tid) {
                $flatten_task($tid, 0, true);
            }

            foreach ($root_stories_no_epic as $sid) {
                $flatten_story($sid, 0);
            }

            foreach ($root_epics as $eid) {
                $flatten_epic($eid, 0);
            }

            $data['tree_rows'] = $flat_rows;
        }

        $data['f_project']     = $f_project;
        $data['f_leader']      = $f_leader;
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

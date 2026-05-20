<?php
class Task extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');
    }

    // ── Task List ─────────────────────────────────────────────────────────────

    public function task_list()
    {
        $this->_auth();

        $data['js']    = 'tasks/task-list.inc';
        $data['title'] = 'Task List';
        $data['s_url'] = 'task-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $ins = array(
                'project_id'     => $this->input->post('project_id') ?: NULL,
                'story_id'       => $this->input->post('story_id') ?: NULL,
                'title'          => $this->input->post('title'),
                'description'    => $this->input->post('description'),
                'status'         => $this->input->post('status') ?: 'todo',
                'priority'       => $this->input->post('priority') ?: 'medium',
                'type'           => $this->input->post('type') ?: 'task',
                'due_date'       => $this->input->post('due_date') ?: NULL,
                'assigned_to'    => $this->input->post('assigned_to') ?: NULL,
                'reporter_id'    => $uid,
                'estimated_hours'=> $this->input->post('estimated_hours') ? (float)$this->input->post('estimated_hours') : NULL,
                'status_flag'    => 'Active',
                'created_by'     => $uid,
                'created_date'   => date('Y-m-d H:i:s'),
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_tasks', $ins);
            $task_id = $this->db->insert_id();
            $this->_log_activity($task_id, 'created', 'Task created.');
            $this->session->set_flashdata('alert_success', 'Task created successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $task_id    = (int)$this->input->post('task_id');
            $new_status = $this->input->post('status');
            $upd = array(
                'project_id'     => $this->input->post('project_id') ?: NULL,
                'story_id'       => $this->input->post('story_id') ?: NULL,
                'title'          => $this->input->post('title'),
                'description'    => $this->input->post('description'),
                'status'         => $new_status,
                'priority'       => $this->input->post('priority'),
                'type'           => $this->input->post('type'),
                'due_date'       => $this->input->post('due_date') ?: NULL,
                'assigned_to'    => $this->input->post('assigned_to') ?: NULL,
                'estimated_hours'=> $this->input->post('estimated_hours') ? (float)$this->input->post('estimated_hours') : NULL,
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            if ($new_status === 'in_progress') $upd['started_at']   = date('Y-m-d H:i:s');
            if (in_array($new_status, array('done','closed'))) $upd['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('task_id', $task_id);
            $this->db->update('tm_tasks', $upd);
            $this->_log_activity($task_id, 'updated', 'Task updated.');
            $this->session->set_flashdata('alert_success', 'Task updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        $f_project  = $this->input->get('project_id');
        $f_story    = $this->input->get('story_id');
        $f_assigned = $this->input->get('assigned_to');
        $f_status   = $this->input->get('f_status');
        $f_priority = $this->input->get('f_priority');
        $f_type     = $this->input->get('f_type');
        $f_search   = $this->input->get('search');
        $f_overdue  = $this->input->get('overdue');
        $f_mine     = $this->input->get('mine');

        $this->load->library('pagination');

        $where = "t.status_flag = 'Active'";
        if ($f_project)  $where .= " AND t.project_id = " . (int)$f_project;
        if ($f_story)    $where .= " AND t.story_id = " . (int)$f_story;
        if ($f_assigned) $where .= " AND t.assigned_to = " . (int)$f_assigned;
        if ($f_status)   $where .= " AND t.status = '" . $this->db->escape_str($f_status) . "'";
        if ($f_priority) $where .= " AND t.priority = '" . $this->db->escape_str($f_priority) . "'";
        if ($f_type)     $where .= " AND t.type = '" . $this->db->escape_str($f_type) . "'";
        if ($f_search)   $where .= " AND t.title LIKE '%" . $this->db->escape_like_str($f_search) . "%'";
        if ($f_overdue)  $where .= " AND t.due_date < CURDATE() AND t.status NOT IN ('done','closed')";
        if ($f_mine)     $where .= " AND t.assigned_to = " . (int)$uid;

        $cnt = (int)$this->db->query("SELECT COUNT(*) as cnt FROM tm_tasks t WHERE {$where}")->row_array()['cnt'];
        $data['total_records'] = $cnt;

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $cnt, 50);
        $this->pagination->initialize($config);

        $sql = "SELECT t.*, p.name as project_name, p.key_name as project_key,
                    ua.name as assignee_name, ur.name as reporter_name,
                    FLOOR(IFNULL(t.estimated_hours, 0)) as estimate_hours,
                    ROUND((IFNULL(t.estimated_hours, 0) - FLOOR(IFNULL(t.estimated_hours, 0))) * 60) as estimate_minutes,
                    (SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND status_flag='Active') as subtask_count,
                    (SELECT COUNT(*) FROM tm_comments WHERE task_id=t.task_id AND status_flag='Active') as comment_count,
                    COALESCE(ROUND(
                        (SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND is_done=1 AND status_flag='Active') * 100.0 /
                        NULLIF((SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND status_flag='Active'), 0)
                    ), 0) as completion_percentage
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                WHERE {$where}
                ORDER BY t.created_date DESC
                LIMIT {$offset}, 50";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['f_project']     = $f_project;
        $data['f_story']       = $f_story;
        $data['f_assigned']    = $f_assigned;
        $data['f_status']      = $f_status;
        $data['f_priority']    = $f_priority;
        $data['f_type']        = $f_type;
        $data['f_search']      = $f_search;
        $data['f_overdue']     = $f_overdue;
        $data['f_mine']        = $f_mine;

        $this->load->view('page/tasks/task-list', $data);
    }

    // ── Task Detail ───────────────────────────────────────────────────────────

    public function task_detail($task_id = 0)
    {
        $this->_auth();
        if (!$task_id) redirect('task-list');

        $data['js']    = 'tasks/task-detail.inc';
        $data['title'] = 'Task Detail';

        $sql = "SELECT t.*, p.name as project_name, p.key_name as project_key,
                    us.name as story_name,
                    ua.name as assignee_name, ur.name as reporter_name,
                    FLOOR(IFNULL(t.estimated_hours, 0)) as estimate_hours,
                    ROUND((IFNULL(t.estimated_hours, 0) - FLOOR(IFNULL(t.estimated_hours, 0))) * 60) as estimate_minutes,
                    COALESCE(ROUND(
                        (SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND is_done=1 AND status_flag='Active') * 100.0 /
                        NULLIF((SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND status_flag='Active'), 0)
                    ), 0) as completion_percentage
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_user_stories us ON us.story_id = t.story_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                WHERE t.task_id = ? AND t.status_flag = 'Active'";
        $task = $this->db->query($sql, array($task_id))->row_array();
        if (!$task) redirect('task-list');
        $data['task'] = $task;

        // Sub-tasks
        $data['sub_tasks'] = $this->db->query(
            "SELECT *, subtask_id as sub_task_id, IF(is_done=1,'done','todo') as status, NULL as assignee_name
             FROM tm_subtasks WHERE task_id = ? AND status_flag = 'Active' ORDER BY created_date ASC",
            array($task_id)
        )->result_array();

        // Comments
        $data['comments'] = $this->db->query(
            "SELECT c.*, u.name as user_name FROM tm_comments c
             LEFT JOIN tm_users u ON u.user_id = c.user_id
             WHERE c.task_id = ? AND c.status_flag = 'Active'
             ORDER BY c.created_date ASC",
            array($task_id)
        )->result_array();

        // Time logs — alias DB columns to what the view expects
        $data['time_logs'] = $this->db->query(
            "SELECT tl.log_id as time_log_id, tl.task_id, tl.user_id,
                    tl.log_date as logged_date, tl.note as description,
                    FLOOR(tl.hours) as hours,
                    ROUND((tl.hours - FLOOR(tl.hours)) * 60) as minutes,
                    u.name as user_name
             FROM tm_time_logs tl
             LEFT JOIN tm_users u ON u.user_id = tl.user_id
             WHERE tl.task_id = ? AND tl.status_flag = 'Active'
             ORDER BY tl.log_date DESC",
            array($task_id)
        )->result_array();

        // Attachments
        $data['attachments'] = $this->db->query(
            "SELECT a.*, u.name as user_name FROM tm_attachments a
             LEFT JOIN tm_users u ON u.user_id = a.user_id
             WHERE a.task_id = ? AND a.status_flag = 'Active'
             ORDER BY a.created_date DESC",
            array($task_id)
        )->result_array();

        // Activity logs
        $data['activity_logs'] = $this->db->query(
            "SELECT al.*, u.name as user_name FROM tm_activity_logs al
             LEFT JOIN tm_users u ON u.user_id = al.user_id
             WHERE al.task_id = ? ORDER BY al.created_date DESC LIMIT 30",
            array($task_id)
        )->result_array();

        // Time totals — work in whole minutes to avoid float rounding
        $total_logged_min            = (int)round(array_sum(array_column($data['time_logs'], 'hours')) * 60)
                                       + (int)array_sum(array_column($data['time_logs'], 'minutes'));
        $estimated_min               = (int)round(($task['estimated_hours'] ?: 0) * 60);
        $data['task']['logged_hours']   = (int)floor($total_logged_min / 60);
        $data['task']['logged_minutes'] = $total_logged_min % 60;
        $data['time_remaining_min']  = max(0, $estimated_min - $total_logged_min);
        $data['time_progress_pct']   = $estimated_min > 0 ? min(100, (int)round($total_logged_min / $estimated_min * 100)) : 0;

        // Dropdowns for edit modal
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['stories_list']  = $this->db->query(
            "SELECT story_id, name FROM tm_user_stories WHERE project_id = ? AND status_flag = 'Active' ORDER BY name",
            array($task['project_id'])
        )->result_array();

        $this->load->view('page/tasks/task-detail', $data);
    }

    // ── Kanban Board ──────────────────────────────────────────────────────────

    public function task_kanban()
    {
        $this->_auth();

        $data['js']    = 'tasks/task-kanban.inc';
        $data['title'] = 'Kanban Board';

        $f_project  = $this->input->get('project_id');
        $f_assigned = $this->input->get('assigned_to');

        $where = "t.status_flag = 'Active'";
        if ($f_project)  $where .= " AND t.project_id = " . (int)$f_project;
        if ($f_assigned) $where .= " AND t.assigned_to = " . (int)$f_assigned;

        $sql = "SELECT t.*, p.name as project_name, ua.name as assignee_name,
                    (SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND status_flag='Active') as subtask_count,
                    (SELECT COUNT(*) FROM tm_comments WHERE task_id=t.task_id AND status_flag='Active') as comment_count,
                    COALESCE(ROUND(
                        (SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND is_done=1 AND status_flag='Active') * 100.0 /
                        NULLIF((SELECT COUNT(*) FROM tm_subtasks WHERE task_id=t.task_id AND status_flag='Active'), 0)
                    ), 0) as completion_percentage
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                WHERE {$where}
                ORDER BY t.created_date DESC";
        $all_tasks = $this->db->query($sql)->result_array();

        $columns = array('backlog'=>array(),'todo'=>array(),'in_progress'=>array(),'in_review'=>array(),'done'=>array(),'closed'=>array());
        foreach ($all_tasks as $t) {
            $s = $t['status'];
            if (!isset($columns[$s])) $columns[$s] = array();
            $columns[$s][] = $t;
        }

        $data['columns']       = $columns;
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['f_project']     = $f_project;
        $data['f_assigned']    = $f_assigned;

        $this->load->view('page/tasks/task-kanban', $data);
    }

    private function _log_activity($task_id, $action, $description)
    {
        $this->db->insert('tm_activity_logs', array(
            'task_id'      => $task_id,
            'user_id'      => $this->session->userdata(SESS_HEAD . '_user_id'),
            'action'       => $action,
            'description'  => $description,
            'created_date' => date('Y-m-d H:i:s'),
        ));
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

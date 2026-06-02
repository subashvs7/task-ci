<?php
class Story extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        if (!has_menu_permission('stories')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access User Stories.');
            redirect_to_fallback();
        }

        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
    }

    public function story_list()
    {
        $this->_auth();

        $data['js']    = 'stories/story-list.inc';
        $data['title'] = 'User Stories';
        $data['s_url'] = 'story-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $user_role = $this->session->userdata(SESS_HEAD . '_role');
            $assignee_id = NULL;
            $epic_id = $this->input->post('epic_id') ?: NULL;
            if ($epic_id) {
                $epic = $this->db->query("SELECT created_by FROM tm_epics WHERE epic_id = ?", [$epic_id])->row_array();
                if ($epic && $epic['created_by']) {
                    $assignee_id = $epic['created_by'];
                }
            } else if ($user_role === 'staff') {
                $assignee_id = $uid;
            }

            $ins = array(
                'project_id'   => $this->input->post('project_id'),
                'epic_id'      => $this->input->post('epic_id') ?: NULL,
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'backlog',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'assignee_id'  => $assignee_id,
                'reporter_id'  => $uid,
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
            $user_role = $this->session->userdata(SESS_HEAD . '_role');
            $assignee_id = NULL;
            $epic_id = $this->input->post('epic_id') ?: NULL;
            if ($epic_id) {
                $epic = $this->db->query("SELECT created_by FROM tm_epics WHERE epic_id = ?", [$epic_id])->row_array();
                if ($epic && $epic['created_by']) {
                    $assignee_id = $epic['created_by'];
                }
            } else if ($user_role === 'staff') {
                $assignee_id = $uid;
            }

            $upd = array(
                'project_id'   => $this->input->post('project_id'),
                'epic_id'      => $this->input->post('epic_id') ?: NULL,
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'assignee_id'  => $assignee_id,
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
        
        $role = $this->session->userdata(SESS_HEAD . '_role');
        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        // All roles can view all active user stories
        // Role-based visibility restrictions have been removed

        if ($f_project) $where .= " AND s.project_id=" . (int)$f_project;
        if ($f_epic)    $where .= " AND s.epic_id=" . (int)$f_epic;
        if ($f_status)  $where .= " AND s.status='" . $this->db->escape_str($f_status) . "'";

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_user_stories s WHERE {$where}")->row_array();
        $data['total_records'] = (int)$cnt['cnt'];

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $data['total_records'], 10);
        $this->pagination->initialize($config);

        if ($this->input->post('mode') == 'AddSubTask') {
            $est_h = (float)$this->input->post('estimate_hours');
            $est_m = (float)$this->input->post('estimate_minutes');
            $estimated_hours = ($est_h > 0 || $est_m > 0) ? round($est_h + ($est_m / 60), 2) : NULL;

            $ins = array(
                'project_id'     => $this->input->post('project_id') ?: NULL,
                'epic_id'        => $this->input->post('epic_id') ?: NULL,
                'story_id'       => $this->input->post('story_id') ?: NULL,
                'title'          => $this->input->post('title'),
                'status'         => 'todo',
                'priority'       => 'medium',
                'type'           => 'task',
                'due_date'       => $this->input->post('due_date') ?: NULL,
                'assigned_to'    => $uid, // Auto-assign to staff creating it
                'reporter_id'    => $uid,
                'estimated_hours'=> $estimated_hours,
                'status_flag'    => 'Active',
                'created_by'     => $uid,
                'created_date'   => date('Y-m-d H:i:s'),
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_tasks', $ins);
            $this->session->set_flashdata('alert_success', 'Task created successfully.');
            redirect($data['s_url']);
        }

        $sql = "SELECT s.*, p.name as project_name, p.created_by as project_creator, e.name as epic_name, e.created_by as epic_creator, e.estimated_time as epic_estimated_time, u.name as assignee_name, uc.name as creator_name,
                    (SELECT COUNT(*) FROM tm_tasks WHERE story_id=s.story_id AND status_flag='Active') as task_count,
                    COALESCE((SELECT SUM(estimated_hours) FROM tm_tasks WHERE story_id=s.story_id AND status_flag='Active'), 0) as calculated_time_hours
                FROM tm_user_stories s
                LEFT JOIN tm_projects p ON p.project_id = s.project_id
                LEFT JOIN tm_epics e ON e.epic_id = s.epic_id
                LEFT JOIN tm_users u ON u.user_id = s.assignee_id
                LEFT JOIN tm_users uc ON uc.user_id = s.created_by
                WHERE {$where}
                ORDER BY s.created_date DESC
                LIMIT {$offset}, 10";
        $data['record_list']   = $this->db->query($sql)->result_array();

        // Fetch sub-tasks for these stories
        $story_ids = array_column($data['record_list'], 'story_id');
        $tasks_by_story = [];
        if (!empty($story_ids)) {
            $task_where = "t.story_id IN (" . implode(',', $story_ids) . ") AND t.status_flag='Active'";
            
            // Only fetch parent tasks here
            $task_sql = "SELECT t.*, u.name as active_worker_name, ua.name as assignee_name,
                            (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                            COALESCE((SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active'), 0) as logged_hours,
                            (SELECT COUNT(*) FROM tm_attachments WHERE task_id=t.task_id AND status_flag='Active') as proof_count
                         FROM tm_tasks t 
                         LEFT JOIN tm_users u ON u.user_id = t.active_session_user 
                         LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                         WHERE {$task_where} AND t.parent_task_id IS NULL
                         ORDER BY t.work_session_status DESC, t.created_date ASC";
            $tasks = $this->db->query($task_sql)->result_array();

            // Fetch child tasks (task-type subtasks)
            $parent_ids = array_column($tasks, 'task_id');
            $subtasks_by_parent = [];
            if (!empty($parent_ids)) {
                $sub_sql = "SELECT t.*, u.name as active_worker_name, ua.name as assignee_name,
                                (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                                COALESCE((SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active'), 0) as logged_hours,
                                (SELECT COUNT(*) FROM tm_attachments WHERE task_id=t.task_id AND status_flag='Active') as proof_count
                             FROM tm_tasks t 
                             LEFT JOIN tm_users u ON u.user_id = t.active_session_user 
                             LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                             WHERE t.parent_task_id IN (" . implode(',', $parent_ids) . ") AND t.status_flag='Active'
                             ORDER BY t.created_date ASC";
                $sub_res = $this->db->query($sub_sql)->result_array();
                foreach ($sub_res as $sub) {
                    $subtasks_by_parent[$sub['parent_task_id']][] = $sub;
                }
            }

            // Fetch checklist subtasks (from tm_subtasks)
            $checklist_by_parent = [];

            // Attach subtasks and checklists to each parent task
            foreach ($tasks as &$task) {
                $task['sub_tasks'] = isset($subtasks_by_parent[$task['task_id']]) ? $subtasks_by_parent[$task['task_id']] : [];
                $task['checklist'] = isset($checklist_by_parent[$task['task_id']]) ? $checklist_by_parent[$task['task_id']] : [];
            }

            foreach ($tasks as $t) {
                $tasks_by_story[$t['story_id']][] = $t;
            }
        }
        foreach ($data['record_list'] as &$st) {
            $st['tasks_list'] = isset($tasks_by_story[$st['story_id']]) ? $tasks_by_story[$st['story_id']] : [];
        }
        $data['pagination']    = $this->pagination->create_links();
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['epics_list']    = $this->db->query("SELECT e.epic_id, e.name, e.project_id, e.created_by, u.name as creator_name FROM tm_epics e LEFT JOIN tm_users u ON u.user_id = e.created_by WHERE e.status_flag='Active' ORDER BY e.name")->result_array();
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
            'reuse_query_string' => TRUE,
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

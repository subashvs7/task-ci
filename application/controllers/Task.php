<?php
class Task extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        if (!has_menu_permission('tasks')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Tasks.');
            redirect_to_fallback();
        }

        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
    }

    private function _handle_document_upload($existing_docs_json = null)
    {
        if (empty($_FILES['document']['name'])) {
            return $existing_docs_json;
        }

        $upload_path = FCPATH . 'uploads/tasks/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = '*'; // allow all types to bypass strict MIME checks
        $config['max_size']      = 10240; // 10MB
        $config['file_name']     = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['document']['name']);

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload('document')) {
            $file = $this->upload->data();
            $file_url = base_url('uploads/tasks/' . $file['file_name']);
            $file_name = $file['orig_name'];
            
            $docs = [];
            if ($existing_docs_json) {
                // If the old data is not JSON, migrate it
                if (!empty($existing_docs_json) && $existing_docs_json[0] !== '[' && $existing_docs_json !== 'null') {
                    $docs[] = [
                        'version' => 'v1',
                        'path' => base_url('uploads/tasks/' . $existing_docs_json),
                        'name' => $existing_docs_json,
                        'date' => date('Y-m-d H:i:s')
                    ];
                } else {
                    $docs = json_decode($existing_docs_json, true) ?: [];
                }
            }
            
            $next_version = count($docs) + 1;
            $uploader_name = $this->session->userdata(SESS_HEAD . '_user_name') ?: 'Unknown';
            
            $docs[] = [
                'version' => 'v' . $next_version,
                'path'    => $file_url,
                'name'    => $file_name,
                'date'    => date('Y-m-d H:i:s'),
                'uploaded_by' => $uploader_name
            ];
            
            return json_encode($docs);
        } else {
            $error = $this->upload->display_errors('', '');
            $this->session->set_flashdata('alert_error', 'File Upload Error: ' . $error);
            return $existing_docs_json;
        }
    }

    // ── Document Deletion ────────────────────────────────────────────────────
    
    public function delete_document()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $task_id = (int)$this->input->post('id');
        $index   = $this->input->post('index');
        $task = $this->db->get_where('tm_tasks', ['task_id' => $task_id])->row_array();

        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Task not found.']);
            return;
        }

        if ($task['document'] && $task['document'] !== 'null' && $task['document'] !== '[]') {
            $docs = json_decode($task['document'], true);
            if (is_array($docs)) {
                if ($index !== null && isset($docs[$index])) {
                    $file_path = str_replace(base_url(), FCPATH, $docs[$index]['path']);
                    if (file_exists($file_path)) unlink($file_path);
                    array_splice($docs, $index, 1);
                    
                    $this->db->where('task_id', $task_id);
                    if (count($docs) > 0) {
                        $this->db->update('tm_tasks', ['document' => json_encode($docs)]);
                    } else {
                        $this->db->update('tm_tasks', ['document' => NULL]);
                    }
                    echo json_encode(['success' => true, 'message' => 'Document removed.']);
                    return;
                } else {
                    foreach ($docs as $doc) {
                        $file_path = str_replace(base_url(), FCPATH, $doc['path']);
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
        }

        if ($index === null) {
            $this->db->where('task_id', $task_id);
            $this->db->update('tm_tasks', ['document' => NULL]);
            echo json_encode(['success' => true, 'message' => 'All documents deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Document not found at specified index.']);
        }
    }

    public function upload_additional_document()
    {
        $this->_auth();
        header('Content-Type: application/json');
        
        $task_id = (int)$this->input->post('id');
        $task = $this->db->get_where('tm_tasks', ['task_id' => $task_id])->row_array();
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Task not found.']);
            return;
        }
        
        if (!empty($_FILES['document']['name'])) {
            $new_docs_json = $this->_handle_document_upload($task['document']);
            if ($new_docs_json !== $task['document']) {
                $this->db->where('task_id', $task_id);
                $this->db->update('tm_tasks', ['document' => $new_docs_json]);
                echo json_encode(['success' => true, 'message' => 'Document uploaded successfully.', 'docs' => json_decode($new_docs_json)]);
                return;
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Failed to upload document.']);
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
            $est_h = (float)$this->input->post('estimate_hours');
            $est_m = (float)$this->input->post('estimate_minutes');
            $estimated_hours = ($est_h > 0 || $est_m > 0) ? round($est_h + ($est_m / 60), 2) : NULL;

            $ins = array(
                'project_id'     => $this->input->post('project_id') ?: NULL,
                'epic_id'        => $this->input->post('epic_id') ?: NULL,
                'story_id'       => $this->input->post('story_id') ?: NULL,
                'title'          => $this->input->post('title'),
                'description'    => $this->input->post('description'),
                'status'         => $this->input->post('status') ?: 'todo',
                'priority'       => $this->input->post('priority') ?: 'medium',
                'type'           => $this->input->post('type') ?: 'task',
                'due_date'       => $this->input->post('due_date') ?: NULL,
                'assigned_to'    => $this->input->post('assigned_to') ?: NULL,
                'reporter_id'    => $uid,
                'start_time'     => $this->input->post('start_time') ? str_replace('T', ' ', $this->input->post('start_time')) : NULL,
                'end_time'       => $this->input->post('end_time') ? str_replace('T', ' ', $this->input->post('end_time')) : NULL,
                'estimated_hours'=> $estimated_hours,
                'status_flag'    => 'Active',
                'created_by'     => $uid,
                'created_date'   => date('Y-m-d H:i:s'),
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            $document = $this->_handle_document_upload(null);
            if ($document) {
                $ins['document'] = $document;
            }
            $this->db->insert('tm_tasks', $ins);
            $task_id = $this->db->insert_id();
            $this->_log_activity($task_id, 'created', 'Task created.');
            $this->session->set_flashdata('alert_success', 'Task created successfully.');
            
            $redirect_url = $this->input->post('redirect_url') ?: $data['s_url'];
            redirect($redirect_url);
        }

        if ($this->input->post('mode') == 'Edit') {
            $task_id    = (int)$this->input->post('task_id');
            $new_status = $this->input->post('status');
            $est_h = (float)$this->input->post('estimate_hours');
            $est_m = (float)$this->input->post('estimate_minutes');
            $estimated_hours = ($est_h > 0 || $est_m > 0) ? round($est_h + ($est_m / 60), 2) : NULL;

            $upd = array(
                'project_id'     => $this->input->post('project_id') ?: NULL,
                'epic_id'        => $this->input->post('epic_id') ?: NULL,
                'story_id'       => $this->input->post('story_id') ?: NULL,
                'title'          => $this->input->post('title'),
                'description'    => $this->input->post('description'),
                'status'         => $new_status,
                'priority'       => $this->input->post('priority'),
                'type'           => $this->input->post('type'),
                'due_date'       => $this->input->post('due_date') ?: NULL,
                'assigned_to'    => $this->input->post('assigned_to') ?: NULL,
                'start_time'     => $this->input->post('start_time') ? str_replace('T', ' ', $this->input->post('start_time')) : NULL,
                'end_time'       => $this->input->post('end_time') ? str_replace('T', ' ', $this->input->post('end_time')) : NULL,
                // reporter_id is NOT updated on edit — it stays as the original assigner
                'estimated_hours'=> $estimated_hours,
                'updated_by'     => $uid,
                'updated_date'   => date('Y-m-d H:i:s'),
            );
            $existing = $this->db->get_where('tm_tasks', ['task_id' => $task_id])->row_array();
            $document = $this->_handle_document_upload($existing['document']);
            if ($document !== $existing['document']) {
                $upd['document'] = $document;
            }
            if ($new_status === 'in_progress') $upd['started_at']   = date('Y-m-d H:i:s');
            if (in_array($new_status, array('done','closed'))) $upd['completed_at'] = date('Y-m-d H:i:s');
            $this->db->where('task_id', $task_id);
            $this->db->update('tm_tasks', $upd);
            $this->_log_activity($task_id, 'updated', 'Task updated.');
            $this->session->set_flashdata('alert_success', 'Task updated successfully.');
            
            $story_id = $this->input->post('story_id');
            if ($story_id) {
                $this->_check_and_update_story_status($story_id);
            }
            
            $redirect_url = $this->input->post('redirect_url') ?: ($data['s_url'] . '/' . $this->uri->segment(2, 0));
            redirect($redirect_url);
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

        $role = $this->session->userdata(SESS_HEAD . '_role');
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
        $config = $this->_pagination_config($data['s_url'], $cnt, 10);
        $this->pagination->initialize($config);

        $sql = "SELECT t.*, p.name as project_name, p.key_name as project_key,
                    ua.name as assignee_name, ur.name as reporter_name, ur.role as reporter_role,
                    uw.name as active_worker_name, e.name as epic_name, e.estimated_time as epic_estimated_time,
                    us.name as story_name,
                    FLOOR(IFNULL(t.estimated_hours, 0)) as estimate_hours,
                    ROUND((IFNULL(t.estimated_hours, 0) - FLOOR(IFNULL(t.estimated_hours, 0))) * 60) as estimate_minutes,
                    0 as subtask_count,
                    (SELECT COUNT(*) FROM tm_comments WHERE task_id=t.task_id AND status_flag='Active') as comment_count,
                    0 as completion_percentage,
                    (COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl WHERE tl.task_id=t.task_id AND tl.status_flag='Active'), 0) +
                     COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl JOIN tm_tasks child ON child.task_id = tl.task_id WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND tl.status_flag='Active'), 0)) as logged_hours,
                    (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                    (SELECT COUNT(*) FROM tm_tasks child WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active') as active_child_count,
                    (SELECT uw2.name FROM tm_tasks child JOIN tm_users uw2 ON uw2.user_id = child.active_session_user WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active' LIMIT 1) as child_worker_name
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_epics e ON e.epic_id = t.epic_id
                LEFT JOIN tm_user_stories us ON us.story_id = t.story_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                LEFT JOIN tm_users uw ON uw.user_id = t.active_session_user
                WHERE {$where}
                ORDER BY (t.work_session_status = 'active') DESC, t.created_date DESC
                LIMIT {$offset}, 10";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['epics_list']    = $this->db->query("SELECT epic_id, name, project_id FROM tm_epics WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['stories_list']  = $this->db->query("SELECT story_id, name, epic_id, project_id FROM tm_user_stories WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = get_assignable_users($f_project);
        $data['filter_users_list'] = $this->db->query("SELECT user_id, name, role FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
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

    public function get_tasks_ajax()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in')) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
            return;
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

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');
        $role = $this->session->userdata(SESS_HEAD . '_role');
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

        $offset = (int)$this->input->get('offset');

        $this->load->library('pagination');
        $config = $this->_pagination_config('task-list', $cnt, 10);
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $this->pagination->initialize($config);

        $sql = "SELECT t.*, p.name as project_name, p.key_name as project_key,
                    ua.name as assignee_name, ur.name as reporter_name, ur.role as reporter_role,
                    uw.name as active_worker_name, e.name as epic_name, e.estimated_time as epic_estimated_time,
                    us.name as story_name,
                    FLOOR(IFNULL(t.estimated_hours, 0)) as estimate_hours,
                    ROUND((IFNULL(t.estimated_hours, 0) - FLOOR(IFNULL(t.estimated_hours, 0))) * 60) as estimate_minutes,
                    0 as subtask_count,
                    (SELECT COUNT(*) FROM tm_comments WHERE task_id=t.task_id AND status_flag='Active') as comment_count,
                    0 as completion_percentage,
                    (COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl WHERE tl.task_id=t.task_id AND tl.status_flag='Active'), 0) +
                     COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl JOIN tm_tasks child ON child.task_id = tl.task_id WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND tl.status_flag='Active'), 0)) as logged_hours,
                    (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                    (SELECT COUNT(*) FROM tm_tasks child WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active') as active_child_count,
                    (SELECT uw2.name FROM tm_tasks child JOIN tm_users uw2 ON uw2.user_id = child.active_session_user WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active' LIMIT 1) as child_worker_name
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_epics e ON e.epic_id = t.epic_id
                LEFT JOIN tm_user_stories us ON us.story_id = t.story_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                LEFT JOIN tm_users uw ON uw.user_id = t.active_session_user
                WHERE {$where}
                ORDER BY (t.work_session_status = 'active') DESC, t.created_date DESC
                LIMIT {$offset}, 10";
        $record_list = $this->db->query($sql)->result_array();

        $show_actions = false;
        if (in_array($role, ['admin', 'manager', 'team_leader'])) {
            $show_actions = true;
        } else if (!empty($record_list)) {
            foreach ($record_list as $t) {
                if ($t['created_by'] == $uid) {
                    $show_actions = true;
                    break;
                }
            }
        }

        $pagination = $this->pagination->create_links();

        $data = array(
            'record_list' => $record_list,
            'sno' => $offset,
            'pagination' => $pagination,
            'total_records' => $cnt,
            'cur_uid' => $uid,
            'cur_role' => $role,
            'show_actions' => $show_actions
        );

        $html = $this->load->view('page/tasks/task-list-rows', $data, TRUE);

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'html' => $html,
            'pagination' => $pagination,
            'total_records' => $cnt
        ));
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
                    0 as completion_percentage,
                    (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                    (SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active') as total_logged_hours
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
        $data['sub_tasks'] = array();

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

        // Blocking tasks (tasks that depend on THIS task)
        $data['blocking'] = $this->db->query(
            "SELECT d.id as dependency_id, t.task_id, t.title, t.status 
             FROM tm_task_dependencies d
             JOIN tm_tasks t ON t.task_id = d.task_id
             WHERE d.depends_on_task_id = ? AND t.status_flag = 'Active'",
            array($task_id)
        )->result_array();

        // Blocked By tasks (tasks that THIS task depends on)
        $data['blocked_by'] = $this->db->query(
            "SELECT d.id as dependency_id, t.task_id, t.title, t.status 
             FROM tm_task_dependencies d
             JOIN tm_tasks t ON t.task_id = d.depends_on_task_id
             WHERE d.task_id = ? AND t.status_flag = 'Active'",
            array($task_id)
        )->result_array();

        // Dropdowns for edit modal
        $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        $data['users_list']    = get_assignable_users($task['project_id'] ?: NULL);
        $data['stories_list']  = $this->db->query(
            "SELECT story_id, name FROM tm_user_stories WHERE project_id = ? AND status_flag = 'Active' ORDER BY name",
            array($task['project_id'])
        )->result_array();

        $data['project_tasks'] = $this->db->query(
            "SELECT task_id, title FROM tm_tasks 
             WHERE project_id = ? AND task_id != ? AND status_flag = 'Active' 
             ORDER BY title",
            array($task['project_id'], $task_id)
        )->result_array();

        $this->load->view('page/tasks/task-detail', $data);
    }

    // ── Task Quick View (Ajax) ────────────────────────────────────────────────
    public function task_quick_view()
    {
        $this->_auth();
        $task_id = (int)$this->input->post('task_id');
        if (!$task_id) {
            echo '<div class="alert alert-danger">Invalid Task ID</div>';
            return;
        }

        $sql = "SELECT t.*, p.name as project_name, p.key_name as project_key,
                    us.name as story_name,
                    ua.name as assignee_name, ur.name as reporter_name, e.estimated_time as epic_estimated_time,
                    FLOOR(IFNULL(t.estimated_hours, 0)) as estimate_hours,
                    ROUND((IFNULL(t.estimated_hours, 0) - FLOOR(IFNULL(t.estimated_hours, 0))) * 60) as estimate_minutes,
                    0 as completion_percentage,
                    (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start,
                    (COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl WHERE tl.task_id=t.task_id AND tl.status_flag='Active'), 0) +
                     COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl JOIN tm_tasks child ON child.task_id = tl.task_id WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND tl.status_flag='Active'), 0)) as total_logged_hours,
                    (SELECT COUNT(*) FROM tm_tasks child WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active') as active_child_count,
                    (SELECT child.active_session_user FROM tm_tasks child WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active' LIMIT 1) as child_active_user,
                    (SELECT uw2.name FROM tm_tasks child JOIN tm_users uw2 ON uw2.user_id = child.active_session_user WHERE (child.parent_task_id = t.task_id OR (t.story_id IS NULL AND child.epic_id = t.epic_id AND child.story_id IS NOT NULL)) AND child.work_session_status = 'active' LIMIT 1) as child_worker_name
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_user_stories us ON us.story_id = t.story_id
                LEFT JOIN tm_epics e ON e.epic_id = t.epic_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
                WHERE t.task_id = ? AND t.status_flag = 'Active'";
        $task = $this->db->query($sql, array($task_id))->row_array();
        
        if (!$task) {
            echo '<div class="alert alert-warning">Task not found or deleted.</div>';
            return;
        }
        
        $data['task'] = $task;
        
        $uid = $this->session->userdata(SESS_HEAD . '_user_id');
        $data['is_child_my_session'] = !empty($task['active_child_count']) && $task['active_child_count'] > 0 && $task['child_active_user'] == $uid;
        $data['is_child_active_session'] = !empty($task['active_child_count']) && $task['active_child_count'] > 0 && $task['child_active_user'] != $uid;
        
        $this->load->view('page/tasks/ajax_task_quick_view', $data);
    }

    // ── Kanban Board ──────────────────────────────────────────────────────────

    public function task_kanban()
    {
        $this->_auth();

        $data['js']    = 'tasks/task-kanban.inc';
        $data['title'] = 'Kanban Board';

        $f_project  = $this->input->get('project_id');
        $f_assigned = $this->input->get('assigned_to');

        $role = $this->session->userdata(SESS_HEAD . '_role');
        $uid  = $this->session->userdata(SESS_HEAD . '_user_id');
        $where = "t.status_flag = 'Active' AND t.story_id IS NULL";
        


        if ($f_project)  $where .= " AND t.project_id = " . (int)$f_project;
        if ($f_assigned) $where .= " AND t.assigned_to = " . (int)$f_assigned;

        $sql = "SELECT t.*, p.name as project_name, ua.name as assignee_name, ur.name as reporter_name,
                    0 as subtask_count,
                    (SELECT COUNT(*) FROM tm_comments WHERE task_id=t.task_id AND status_flag='Active') as comment_count,
                    0 as completion_percentage,
                    COALESCE((SELECT SUM(hours) FROM tm_time_logs WHERE task_id=t.task_id AND status_flag='Active'), 0) as logged_hours
                FROM tm_tasks t
                LEFT JOIN tm_projects p ON p.project_id = t.project_id
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                LEFT JOIN tm_users ur ON ur.user_id = t.reporter_id
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
        $data['users_list']    = get_assignable_users($f_project);
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

    // ── Work Session Toggle ───────────────────────────────────────────────────

    /**
     * POST: task-toggle-session
     * Toggles a work session ON (punch-in) or OFF (punch-out) for a task.
     * Role rules:
     *   - Staff: own assigned tasks only
     *   - Admin:  any task
     *   - Manager / Team Leader: cannot toggle (read-only effort view)
     */
    public function toggle_session()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid     = $this->session->userdata(SESS_HEAD . '_user_id');
        $role    = $this->session->userdata(SESS_HEAD . '_role');
        $task_id = (int)$this->input->post('task_id');

        if (!$task_id) {
            echo json_encode(array('success' => false, 'message' => 'Invalid task ID.'));
            return;
        }

        // Fetch task
        $task = $this->db->query(
            "SELECT task_id, title, assigned_to, status, work_session_status, active_session_user, estimated_hours, started_at, story_id, epic_id, project_id
             FROM tm_tasks WHERE task_id = ? AND status_flag = 'Active'",
            array($task_id)
        )->row_array();

        if (!$task) {
            echo json_encode(array('success' => false, 'message' => 'Task not found.'));
            return;
        }

        // Permission check
        if ($role === 'staff' && (int)$task['assigned_to'] !== (int)$uid) {
            echo json_encode(array('success' => false, 'message' => 'You can only toggle sessions for your own tasks.'));
            return;
        }
        if ($role === 'manager' || $role === 'team_leader') {
            echo json_encode(array('success' => false, 'message' => 'Managers and Team Leaders cannot toggle work sessions.'));
            return;
        }
        if (in_array($task['status'], array('done', 'closed'))) {
            echo json_encode(array('success' => false, 'message' => 'Cannot start a session on a completed or closed task.'));
            return;
        }

        // Check for existing open session by this user
        $open_session = $this->db->query(
            "SELECT session_id, started_at, task_id FROM tm_task_sessions
             WHERE task_id = ? AND user_id = ? AND ended_at IS NULL AND status_flag = 'Active'
             LIMIT 1",
            array($task_id, $uid)
        )->row_array();

        if (!$open_session) {
            // Check if there is an active session on a child task instead
            $child_session = $this->db->query(
                "SELECT s.session_id, s.started_at, s.task_id FROM tm_task_sessions s
                 JOIN tm_tasks child ON child.task_id = s.task_id
                 WHERE (child.parent_task_id = ? OR (? IS NULL AND child.epic_id = ? AND child.story_id IS NOT NULL))
                 AND s.user_id = ? AND s.ended_at IS NULL AND s.status_flag = 'Active'
                 LIMIT 1",
                array($task_id, $task['story_id'], $task['epic_id'], $uid)
            )->row_array();
            if ($child_session) {
                $open_session = $child_session;
                $task_id = $child_session['task_id']; // Switch context to the child task to stop its session!
                // Re-fetch the task so the cascade logic uses the correct child task
                $task = $this->db->query("SELECT * FROM tm_tasks WHERE task_id = ?", array($task_id))->row_array();
            }
        }

        $now = date('Y-m-d H:i:s');

        if ($open_session) {
            // ─── PUNCH OUT ─────────────────────────────────────────────────
            $started   = strtotime($open_session['started_at']);
            $ended     = strtotime($now);
            $dur_min   = max(1, (int)round(($ended - $started) / 60));
            $dur_hours = round($dur_min / 60, 2);

            // Close the session
            $this->db->where('session_id', $open_session['session_id']);
            $this->db->update('tm_task_sessions', array(
                'ended_at'     => $now,
                'duration_min' => $dur_min,
            ));

            // Auto-create time log entry for this session
            $this->db->insert('tm_time_logs', array(
                'task_id'      => $task_id,
                'user_id'      => $uid,
                'hours'        => $dur_hours,
                'note'         => 'Auto-logged from work session (punch-out)',
                'log_date'     => date('Y-m-d'),
                'status_flag'  => 'Active',
                'created_date' => $now,
            ));

            // Reset task work session status
            $this->db->where('task_id', $task_id);
            $this->db->update('tm_tasks', array(
                'work_session_status' => 'idle',
                'active_session_user' => NULL,
                'updated_by'          => $uid,
                'updated_date'        => $now,
            ));

            // If task was 'todo', move to 'in_progress'
            if ($task['status'] === 'todo') {
                $this->db->where('task_id', $task_id);
                $this->db->update('tm_tasks', array('status' => 'in_progress', 'started_at' => $now));
            }

            $this->_log_activity($task_id, 'session_stop', 'Work session stopped. Duration: ' . $dur_min . ' min.');

            // Calculate effort overdue
            $effort = $this->_get_effort_data($task_id, $task);

            echo json_encode(array(
                'success'      => true,
                'action'       => 'stopped',
                'duration_min' => $dur_min,
                'hours_logged' => $dur_hours,
                'effort'       => $effort,
            ));

        } else {
            // ─── PUNCH IN ──────────────────────────────────────────────────
            // Enforce: a task can only have ONE active session at a time globally
            if ($task['work_session_status'] === 'active' && (int)$task['active_session_user'] !== (int)$uid) {
                $other = $this->db->query(
                    "SELECT name FROM tm_users WHERE user_id = ?",
                    array($task['active_session_user'])
                )->row_array();
                echo json_encode(array(
                    'success' => false,
                    'message' => 'This task is currently being worked on by ' . ($other ? $other['name'] : 'another user') . '.',
                ));
                return;
            }

            // Create open session
            $session_id = $this->db->insert('tm_task_sessions', array(
                'task_id'      => $task_id,
                'user_id'      => $uid,
                'started_at'   => $now,
                'status_flag'  => 'Active',
                'created_date' => $now,
            ));

            // Update task
            $upd = array(
                'work_session_status' => 'active',
                'active_session_user' => $uid,
                'started_at'          => $task['started_at'] ?: $now,
                'updated_by'          => $uid,
                'updated_date'        => $now,
            );
            if ($task['status'] === 'todo') {
                $upd['status'] = 'in_progress';
            }
            $this->db->where('task_id', $task_id);
            $this->db->update('tm_tasks', $upd);

            // Cascade status update to parent entities
            if (!empty($task['story_id'])) {
                $this->db->query("UPDATE tm_user_stories SET status = 'in_progress' WHERE story_id = ? AND status = 'todo'", array($task['story_id']));
                $story = $this->db->query("SELECT epic_id, project_id FROM tm_user_stories WHERE story_id = ?", array($task['story_id']))->row_array();
                if ($story) {
                    if (!empty($story['epic_id'])) {
                        $this->db->query("UPDATE tm_epics SET status = 'in_progress' WHERE epic_id = ? AND status = 'todo'", array($story['epic_id']));
                    }
                    if (!empty($story['project_id'])) {
                        $this->db->query("UPDATE tm_projects SET status = 'active' WHERE project_id = ? AND status = 'planning'", array($story['project_id']));
                    }
                }
            } elseif (!empty($task['project_id'])) {
                $this->db->query("UPDATE tm_projects SET status = 'active' WHERE project_id = ? AND status = 'planning'", array($task['project_id']));
            }

            $this->_log_activity($task_id, 'session_start', 'Work session started.');

            echo json_encode(array(
                'success'    => true,
                'action'     => 'started',
                'session_id' => $session_id,
                'started_at' => $now,
            ));
        }
    }

    /**
     * POST: task-complete
     * Completes a task. If a session is active, it stops the session and logs time.
     */
    public function complete_task()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $uid     = $this->session->userdata(SESS_HEAD . '_user_id');
        $role    = $this->session->userdata(SESS_HEAD . '_role');
        $task_id = (int)$this->input->post('task_id');

        if (!$task_id) {
            echo json_encode(array('success' => false, 'message' => 'Invalid task ID.'));
            return;
        }

        $task = $this->db->query(
            "SELECT task_id, assigned_to, status, work_session_status FROM tm_tasks WHERE task_id = ? AND status_flag = 'Active'",
            array($task_id)
        )->row_array();

        if (!$task) {
            echo json_encode(array('success' => false, 'message' => 'Task not found.'));
            return;
        }

        if ($role === 'staff' && (int)$task['assigned_to'] !== (int)$uid) {
            echo json_encode(array('success' => false, 'message' => 'You can only complete your own tasks.'));
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Check for existing open session by this user
        $open_session = $this->db->query(
            "SELECT session_id, started_at FROM tm_task_sessions
             WHERE task_id = ? AND user_id = ? AND ended_at IS NULL AND status_flag = 'Active'
             LIMIT 1",
            array($task_id, $uid)
        )->row_array();

        if ($open_session) {
            // PUNCH OUT
            $started   = strtotime($open_session['started_at']);
            $ended     = strtotime($now);
            $dur_min   = max(1, (int)round(($ended - $started) / 60));
            $dur_hours = round($dur_min / 60, 2);

            $this->db->where('session_id', $open_session['session_id']);
            $this->db->update('tm_task_sessions', array(
                'ended_at'     => $now,
                'duration_min' => $dur_min,
            ));

            $this->db->insert('tm_time_logs', array(
                'task_id'      => $task_id,
                'user_id'      => $uid,
                'hours'        => $dur_hours,
                'note'         => 'Auto-logged from work session (task completed)',
                'log_date'     => date('Y-m-d'),
                'status_flag'  => 'Active',
                'created_date' => $now,
            ));
            
            $this->_log_activity($task_id, 'session_stop', 'Work session stopped upon task completion. Duration: ' . $dur_min . ' min.');
        }

        // Complete the task
        $upd = array(
            'status'              => 'done',
            'completed_at'        => $now,
            'work_session_status' => 'idle',
            'active_session_user' => NULL,
            'updated_by'          => $uid,
            'updated_date'        => $now,
        );

        $this->db->where('task_id', $task_id);
        $this->db->update('tm_tasks', $upd);

        $this->_log_activity($task_id, 'completed', 'Task marked as Complete.');

        // Auto-update story status if all tasks are done
        $task_info = $this->db->query("SELECT story_id FROM tm_tasks WHERE task_id = ?", array($task_id))->row_array();
        if ($task_info && $task_info['story_id']) {
            $this->_check_and_update_story_status($task_info['story_id']);
        }

        echo json_encode(array('success' => true));
    }

    /**
     * GET: task-sessions/{task_id}
     * Returns the session history for a given task.
     */
    public function get_sessions($task_id)
    {
        $this->_auth();
        header('Content-Type: application/json');

        $task_id = (int)$task_id;
        $uid     = $this->session->userdata(SESS_HEAD . '_user_id');
        $role    = $this->session->userdata(SESS_HEAD . '_role');

        // Fetch task
        $task = $this->db->query(
            "SELECT task_id, assigned_to FROM tm_tasks WHERE task_id = ? AND status_flag = 'Active'",
            array($task_id)
        )->row_array();

        if (!$task) {
            echo json_encode(array('success' => false, 'message' => 'Task not found.'));
            return;
        }

        // Staff can only see their own sessions
        if ($role === 'staff' && (int)$task['assigned_to'] !== (int)$uid) {
            echo json_encode(array('success' => false, 'message' => 'Access denied.'));
            return;
        }

        $sessions = $this->db->query("
            SELECT s.session_id, s.started_at, s.ended_at, s.duration_min, s.note,
                   u.name as user_name
            FROM tm_task_sessions s
            JOIN tm_users u ON u.user_id = s.user_id
            WHERE s.task_id = ? AND s.status_flag = 'Active'
            ORDER BY s.started_at DESC
        ", array($task_id))->result_array();

        // Calculate open session duration on-the-fly
        foreach ($sessions as &$s) {
            if ($s['ended_at'] === null) {
                $s['duration_min'] = (int)round((time() - strtotime($s['started_at'])) / 60);
                $s['is_active']    = true;
            } else {
                $s['is_active'] = false;
            }
        }
        unset($s);

        $effort = $this->_get_effort_data($task_id);

        echo json_encode(array(
            'success'  => true,
            'sessions' => $sessions,
            'effort'   => $effort,
        ));
    }

    /**
     * GET: task-effort-status/{task_id}
     * Returns live effort status for a task (used for dashboard timers).
     */
    public function get_effort_status($task_id)
    {
        $this->_auth();
        header('Content-Type: application/json');

        $task_id = (int)$task_id;
        $task = $this->db->query(
            "SELECT task_id, estimated_hours, due_date, status, work_session_status, active_session_user
             FROM tm_tasks WHERE task_id = ? AND status_flag = 'Active'",
            array($task_id)
        )->row_array();

        if (!$task) {
            echo json_encode(array('success' => false, 'message' => 'Not found.'));
            return;
        }

        echo json_encode(array('success' => true, 'effort' => $this->_get_effort_data($task_id, $task)));
    }

    /**
     * Helper: Compute effort data for a task.
     */
    private function _get_effort_data($task_id, $task = null)
    {
        if (!$task) {
            $task = $this->db->query(
                "SELECT task_id, estimated_hours, due_date, status FROM tm_tasks WHERE task_id = ?",
                array($task_id)
            )->row_array();
        }

        // Sum of time logs
        $logged_row = $this->db->query(
            "SELECT COALESCE(SUM(hours), 0) as total FROM tm_time_logs WHERE task_id = ? AND status_flag = 'Active'",
            array($task_id)
        )->row_array();
        $logged_hours = (float)$logged_row['total'];

        // Open session duration
        $open = $this->db->query(
            "SELECT started_at FROM tm_task_sessions
             WHERE task_id = ? AND ended_at IS NULL AND status_flag = 'Active' LIMIT 1",
            array($task_id)
        )->row_array();
        $open_hours = 0;
        if ($open) {
            $open_min   = max(0, (int)round((time() - strtotime($open['started_at'])) / 60));
            $open_hours = round($open_min / 60, 2);
        }

        $total_hours     = round($logged_hours + $open_hours, 2);
        $estimated       = $task['estimated_hours'] ? (float)$task['estimated_hours'] : null;
        $is_date_overdue = !empty($task['due_date'])
                         && strtotime($task['due_date']) < strtotime(date('Y-m-d'))
                         && !in_array($task['status'], array('done', 'closed'));
        $is_effort_over  = $estimated !== null && $total_hours > $estimated;
        $pct_used        = $estimated > 0 ? min(100, round(($total_hours / $estimated) * 100)) : null;

        return array(
            'logged_hours'     => $logged_hours,
            'open_hours'       => $open_hours,
            'total_hours'      => $total_hours,
            'estimated_hours'  => $estimated,
            'pct_used'         => $pct_used,
            'is_date_overdue'  => (bool)$is_date_overdue,
            'is_effort_overdue'=> (bool)$is_effort_over,
            'has_open_session' => !empty($open),
            'open_started_at'  => $open ? $open['started_at'] : null,
        );
    }
    public function add_dependency()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $task_id = (int)$this->input->post('task_id');
        $depends_on_task_id = (int)$this->input->post('depends_on_task_id');

        if (!$task_id || !$depends_on_task_id || $task_id == $depends_on_task_id) {
            echo json_encode(array('success' => false, 'message' => 'Invalid task IDs.'));
            return;
        }

        // Check if dependency already exists
        $exists = $this->db->query(
            "SELECT id FROM tm_task_dependencies WHERE task_id = ? AND depends_on_task_id = ?",
            array($task_id, $depends_on_task_id)
        )->row_array();

        if ($exists) {
            echo json_encode(array('success' => false, 'message' => 'Dependency already exists.'));
            return;
        }

        // Avoid cyclic dependency
        $cyclic = $this->db->query(
            "SELECT id FROM tm_task_dependencies WHERE task_id = ? AND depends_on_task_id = ?",
            array($depends_on_task_id, $task_id)
        )->row_array();

        if ($cyclic) {
            echo json_encode(array('success' => false, 'message' => 'Cannot create a cyclic dependency.'));
            return;
        }

        $data = array(
            'task_id'            => $task_id,
            'depends_on_task_id' => $depends_on_task_id,
            'created_by'         => $this->session->userdata(SESS_HEAD . '_user_id')
        );

        if ($this->db->insert('tm_task_dependencies', $data)) {
            $this->_log_activity($task_id, 'Added Dependency', "Added task #$depends_on_task_id as a blocking dependency.");
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to add dependency.'));
        }
    }

    public function delete_dependency()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $dependency_id = (int)$this->input->post('dependency_id');
        
        $dep = $this->db->query("SELECT * FROM tm_task_dependencies WHERE id = ?", array($dependency_id))->row_array();
        if (!$dep) {
            echo json_encode(array('success' => false, 'message' => 'Not found.'));
            return;
        }

        if ($this->db->delete('tm_task_dependencies', array('id' => $dependency_id))) {
            $this->_log_activity($dep['task_id'], 'Removed Dependency', "Removed blocking dependency on task #{$dep['depends_on_task_id']}.");
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to remove dependency.'));
        }
    }

    private function _check_and_update_story_status($story_id)
    {
        if (!$story_id) return;
        $story = $this->db->get_where('tm_user_stories', ['story_id' => $story_id])->row_array();
        if (!$story) return;

        $tasks = $this->db->get_where('tm_tasks', ['story_id' => $story_id, 'status_flag' => 'Active'])->result_array();
        if (empty($tasks)) return;

        $all_done = true;
        $any_in_progress = false;

        foreach ($tasks as $t) {
            if (!in_array($t['status'], ['done', 'closed'])) {
                $all_done = false;
            }
            if ($t['status'] === 'in_progress' || $t['work_session_status'] === 'active') {
                $any_in_progress = true;
            }
        }

        $new_status = $story['status'];
        if ($all_done) {
            $new_status = 'done';
        } elseif ($any_in_progress) {
            $new_status = 'in_progress';
        }

        if ($new_status !== $story['status']) {
            $this->db->where('story_id', $story_id);
            $this->db->update('tm_user_stories', ['status' => $new_status, 'updated_date' => date('Y-m-d H:i:s')]);
            
            // Auto update epic status if story is updated
            if (!empty($story['epic_id'])) {
                if ($new_status === 'in_progress') {
                    $this->db->query("UPDATE tm_epics SET status = 'in_progress' WHERE epic_id = ? AND status = 'todo'", array($story['epic_id']));
                }
            }
        }
    }

    public function get_scheduled_tasks()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in')) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
            return;
        }
        
        $sql = "SELECT t.task_id, t.title, t.start_time, t.end_time, t.due_date, 
                       t.estimated_hours, t.work_session_status, t.assigned_to,
                       COALESCE(ua.name, 'Unassigned') as assignee_name,
                       (COALESCE((SELECT SUM(tl.hours) FROM tm_time_logs tl WHERE tl.task_id=t.task_id AND tl.status_flag='Active'), 0)) as logged_hours,
                       (SELECT started_at FROM tm_task_sessions WHERE task_id=t.task_id AND ended_at IS NULL AND status_flag='Active' LIMIT 1) as open_session_start
                FROM tm_tasks t
                LEFT JOIN tm_users ua ON ua.user_id = t.assigned_to
                WHERE t.status_flag = 'Active' 
                  AND t.status NOT IN ('done', 'closed')";
        
        $tasks = $this->db->query($sql)->result_array();
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'tasks' => $tasks));
    }
}

<?php
class Epic extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in'))
            redirect('login');

        if (!has_menu_permission('epics')) {
            $this->session->set_flashdata('alert_error', 'You do not have permission to access Epics.');
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

        $upload_path = FCPATH . 'uploads/epics/';
        if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = '*'; // allow all types to bypass strict MIME checks
        $config['max_size']      = 10240; // 10MB
        $config['file_name']     = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['document']['name']);

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload('document')) {
            $file = $this->upload->data();
            $file_url = base_url('uploads/epics/' . $file['file_name']);
            $file_name = $file['orig_name'];

            $docs = [];
            if ($existing_docs_json) {
                $docs = json_decode($existing_docs_json, true) ?: [];
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
            return $existing_docs_json; // Return old so we don't overwrite with null on failure
        }
    }

    // ── Document Deletion ────────────────────────────────────────────────────
    
    public function delete_document()
    {
        $this->_auth();
        header('Content-Type: application/json');

        $epic_id = (int)$this->input->post('id');
        $index   = $this->input->post('index');
        $epic    = $this->db->get_where('tm_epics', ['epic_id' => $epic_id])->row_array();

        if (!$epic) {
            echo json_encode(['success' => false, 'message' => 'Epic not found.']);
            return;
        }

        if ($epic['document'] && $epic['document'] !== 'null' && $epic['document'] !== '[]') {
            $docs = json_decode($epic['document'], true);
            if (is_array($docs)) {
                if ($index !== null && isset($docs[$index])) {
                    $file_path = str_replace(base_url(), FCPATH, $docs[$index]['path']);
                    if (file_exists($file_path)) unlink($file_path);
                    array_splice($docs, $index, 1);
                    
                    $this->db->where('epic_id', $epic_id);
                    if (count($docs) > 0) {
                        $this->db->update('tm_epics', ['document' => json_encode($docs)]);
                    } else {
                        $this->db->update('tm_epics', ['document' => NULL]);
                    }
                    echo json_encode(['success' => true, 'message' => 'Document removed.']);
                    return;
                } else {
                    foreach ($docs as $doc) {
                        $file_path = str_replace(base_url(), FCPATH, $doc['path']);
                        if (file_exists($file_path)) unlink($file_path);
                    }
                }
            }
        }

        if ($index === null) {
            $this->db->where('epic_id', $epic_id);
            $this->db->update('tm_epics', ['document' => NULL]);
            echo json_encode(['success' => true, 'message' => 'All documents deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Document not found at specified index.']);
        }
    }

    public function upload_additional_document()
    {
        $this->_auth();
        header('Content-Type: application/json');
        
        $epic_id = (int)$this->input->post('id');
        $epic = $this->db->get_where('tm_epics', ['epic_id' => $epic_id])->row_array();
        if (!$epic) {
            echo json_encode(['success' => false, 'message' => 'Epic not found.']);
            return;
        }
        
        if (!empty($_FILES['document']['name'])) {
            $new_docs_json = $this->_handle_document_upload($epic['document']);
            if ($new_docs_json !== $epic['document']) {
                $this->db->where('epic_id', $epic_id);
                $this->db->update('tm_epics', ['document' => $new_docs_json]);
                echo json_encode(['success' => true, 'message' => 'Document uploaded successfully.', 'docs' => json_decode($new_docs_json)]);
                return;
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Failed to upload document.']);
    }

    // ── Epic List ────────────────────────────────────────────────────────────

    public function epic_list()
    {
        $this->_auth();

        $data['js']    = 'epics/epic-list.inc';
        $data['title'] = 'Epics';
        $data['s_url'] = 'epic-list';

        $uid = $this->session->userdata(SESS_HEAD . '_user_id');

        if ($this->input->post('mode') == 'Add') {
            $user_role = $this->session->userdata(SESS_HEAD . '_role');
            if (!in_array($user_role, ['admin', 'manager', 'team_leader', 'staff'])) {
                $this->session->set_flashdata('alert_error', 'You do not have permission to create Epics.');
                redirect($data['s_url']);
            }
            $est_h = (float)$this->input->post('est_hours');
            $est_m = (float)$this->input->post('est_minutes');
            $estimated_time = round(($est_h * 60) + $est_m);

            $docs_json = $this->_handle_document_upload();

            $ins = array(
                'project_id'   => $this->input->post('project_id'),
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status') ?: 'open',
                'priority'     => $this->input->post('priority') ?: 'medium',
                'color'        => $this->input->post('color') ?: '#9b59b6',
                'estimated_time' => $estimated_time,
                'document'     => $docs_json,
                'status_flag'  => 'Active',
                'created_by'   => $uid,
                'created_date' => date('Y-m-d H:i:s'),
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tm_epics', $ins);
            $this->session->set_flashdata('alert_success', 'Epic created successfully.');
            redirect($data['s_url']);
        }

        if ($this->input->post('mode') == 'Edit') {
            $user_role = $this->session->userdata(SESS_HEAD . '_role');
            if (!in_array($user_role, ['admin', 'manager', 'team_leader', 'staff'])) {
                $this->session->set_flashdata('alert_error', 'You do not have permission to edit Epics.');
                redirect($data['s_url']);
            }
            $est_h = (float)$this->input->post('est_hours');
            $est_m = (float)$this->input->post('est_minutes');
            $estimated_time = round(($est_h * 60) + $est_m);

            $epic_id = $this->input->post('epic_id');
            $existing_epic = $this->db->get_where('tm_epics', ['epic_id' => $epic_id])->row_array();
            $existing_docs_json = $existing_epic ? $existing_epic['document'] : null;

            $docs_json = $this->_handle_document_upload($existing_docs_json);

            $upd = array(
                'project_id'   => $this->input->post('project_id'),
                'name'         => $this->input->post('name'),
                'description'  => $this->input->post('description'),
                'status'       => $this->input->post('status'),
                'priority'     => $this->input->post('priority'),
                'color'        => $this->input->post('color'),
                'estimated_time' => $estimated_time,
                'document'     => $docs_json,
                'updated_by'   => $uid,
                'updated_date' => date('Y-m-d H:i:s'),
            );
            $this->db->where('epic_id', $this->input->post('epic_id'));
            $this->db->update('tm_epics', $upd);
            $this->session->set_flashdata('alert_success', 'Epic updated successfully.');
            redirect($data['s_url'] . '/' . $this->uri->segment(2, 0));
        }

        $f_project    = $this->input->get('project_id');
        $f_status     = $this->input->get('f_status');
        $f_creator    = $this->input->get('creator_id');
        $f_department = $this->input->get('department_id');
        $f_date_from  = $this->input->get('date_from');
        $f_date_to    = $this->input->get('date_to');

        $this->load->library('pagination');
        $role = $this->session->userdata(SESS_HEAD . '_role');
        $uid  = $this->session->userdata(SESS_HEAD . '_user_id');
        $where = "e.status_flag='Active'";

        // ── Role-based scope: non-privileged users see only epics they created ──
        if (!in_array($role, ['admin', 'manager', 'team_leader'])) {
            $where .= " AND e.created_by = {$uid}";
        }

        if ($f_project)    $where .= " AND e.project_id=" . (int)$f_project;
        if ($f_status)     $where .= " AND e.status='" . $this->db->escape_str($f_status) . "'";
        if ($f_creator)    $where .= " AND e.created_by=" . (int)$f_creator;
        if ($f_department) $where .= " AND e.created_by IN (SELECT user_id FROM tm_users WHERE department_id=" . (int)$f_department . ")";
        if ($f_date_from)  $where .= " AND DATE(e.created_date) >= " . $this->db->escape($f_date_from);
        if ($f_date_to)    $where .= " AND DATE(e.created_date) <= " . $this->db->escape($f_date_to);

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_epics e WHERE {$where}")->row_array();
        $data['total_records'] = (int)$cnt['cnt'];

        $data['sno'] = $offset = $this->uri->segment(2, 0);
        $config = $this->_pagination_config($data['s_url'], $data['total_records'], 10);
        $this->pagination->initialize($config);

        $sql = "SELECT e.*, p.name as project_name,
                    (SELECT COUNT(*) FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active') as story_count,
                    COALESCE((SELECT SUM(t.estimated_hours) FROM tm_tasks t WHERE (t.epic_id = e.epic_id OR t.story_id IN (SELECT story_id FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active')) AND t.status_flag='Active'), 0) as calculated_time_hours
                FROM tm_epics e
                LEFT JOIN tm_projects p ON p.project_id = e.project_id
                WHERE {$where}
                ORDER BY e.created_date DESC
                LIMIT {$offset}, 10";
        $data['record_list']   = $this->db->query($sql)->result_array();
        $data['pagination']    = $this->pagination->create_links();
        if (!isset($role)) $role = $this->session->userdata(SESS_HEAD . '_role');
        if (!isset($uid))  $uid  = $this->session->userdata(SESS_HEAD . '_user_id');
        if ($role === 'team_leader') {
            $data['projects_list'] = $this->db->query("SELECT p.project_id, p.name FROM tm_projects p WHERE p.status_flag='Active' AND (p.owner_id=? OR p.project_id IN (SELECT project_id FROM tm_project_members WHERE user_id=?)) ORDER BY p.name", array($uid, $uid))->result_array();
        } else {
            $data['projects_list'] = $this->db->query("SELECT project_id, name FROM tm_projects WHERE status_flag='Active' ORDER BY name")->result_array();
        }
        $data['departments_list'] = $this->db->query("SELECT department_id, department_name FROM tm_departments_info WHERE status='Active' ORDER BY department_name")->result_array();
        $data['users_list']       = $this->db->query("SELECT user_id, name FROM tm_users WHERE status='Active' ORDER BY name")->result_array();
        $data['f_project']        = $f_project;
        $data['f_status']         = $f_status;
        $data['f_creator']        = $f_creator;
        $data['f_department']     = $f_department;
        $data['f_date_from']      = $f_date_from;
        $data['f_date_to']        = $f_date_to;

        $this->load->view('page/epics/epic-list', $data);
    }

    public function get_epics_ajax()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in')) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
            return;
        }

        $f_project    = $this->input->get('project_id');
        $f_status     = $this->input->get('f_status');
        $f_creator    = $this->input->get('creator_id');
        $f_department = $this->input->get('department_id');
        $f_date_from  = $this->input->get('date_from');
        $f_date_to    = $this->input->get('date_to');

        $uid  = $this->session->userdata(SESS_HEAD . '_user_id');
        $role = $this->session->userdata(SESS_HEAD . '_role');

        $where = "e.status_flag='Active'";

        // ── Role-based scope: non-privileged users see only epics they created ──
        if (!in_array($role, ['admin', 'manager', 'team_leader'])) {
            $where .= " AND e.created_by = {$uid}";
        }

        if ($f_project)    $where .= " AND e.project_id=" . (int)$f_project;
        if ($f_status)     $where .= " AND e.status='" . $this->db->escape_str($f_status) . "'";
        if ($f_creator)    $where .= " AND e.created_by=" . (int)$f_creator;
        if ($f_department) $where .= " AND e.created_by IN (SELECT user_id FROM tm_users WHERE department_id=" . (int)$f_department . ")";
        if ($f_date_from)  $where .= " AND DATE(e.created_date) >= " . $this->db->escape($f_date_from);
        if ($f_date_to)    $where .= " AND DATE(e.created_date) <= " . $this->db->escape($f_date_to);

        $cnt = $this->db->query("SELECT COUNT(*) as cnt FROM tm_epics e WHERE {$where}")->row_array();
        $total_records = (int)$cnt['cnt'];

        $offset = (int)$this->input->get('offset');

        $this->load->library('pagination');
        $config = $this->_pagination_config('epic-list', $total_records, 10);
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $this->pagination->initialize($config);

        $sql = "SELECT e.*, p.name as project_name,
                    (SELECT COUNT(*) FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active') as story_count,
                    COALESCE((SELECT SUM(t.estimated_hours) FROM tm_tasks t WHERE (t.epic_id = e.epic_id OR t.story_id IN (SELECT story_id FROM tm_user_stories WHERE epic_id=e.epic_id AND status_flag='Active')) AND t.status_flag='Active'), 0) as calculated_time_hours
                FROM tm_epics e
                LEFT JOIN tm_projects p ON p.project_id = e.project_id
                WHERE {$where}
                ORDER BY e.created_date DESC
                LIMIT {$offset}, 10";
        $record_list = $this->db->query($sql)->result_array();
        $pagination  = $this->pagination->create_links();

        $data = array(
            'record_list' => $record_list,
            'sno' => $offset,
            'pagination' => $pagination,
            'total_records' => $total_records
        );

        $html = $this->load->view('page/epics/epic-list-rows', $data, TRUE);

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'html' => $html,
            'pagination' => $pagination,
            'total_records' => $total_records
        ));
    }

    private function _pagination_config($url, $total, $per_page)
    {
        return array(
            'base_url'         => site_url($url),
            'total_rows'       => $total,
            'per_page'         => $per_page,
            'uri_segment'      => 2,
            'reuse_query_string' => TRUE,
            'attributes'       => array('class' => 'page-link'),
            'full_tag_open'    => '<ul class="pagination pagination-sm no-margin pull-right">',
            'full_tag_close'   => '</ul>',
            'num_tag_open'     => '<li class="page-item">',
            'num_tag_close'     => '</li>',
            'cur_tag_open'     => '<li class="page-item active"><a href="#" class="page-link">',
            'cur_tag_close'    => '<span class="sr-only">(current)</span></a></li>',
            'prev_tag_open'    => '<li class="page-item">',
            'prev_tag_close'    => '</li>',
            'next_tag_open'    => '<li class="page-item">',
            'next_tag_close'    => '</li>',
            'first_tag_open'   => '<li class="page-item">',
            'first_tag_close'   => '</li>',
            'last_tag_open'    => '<li class="page-item">',
            'last_tag_close'    => '</li>',
            'prev_link'        => 'Prev',
            'next_link'        => 'Next',
        );
    }
}

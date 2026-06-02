<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RolePermission extends CI_Controller
{
    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in')) {
            redirect('login');
        }
    }

    private function _admin_only()
    {
        $this->_auth();
        if ($this->session->userdata(SESS_HEAD . '_role') !== 'admin') {
            $this->session->set_flashdata('alert_error', 'Unauthorized access.');
            redirect('dash');
        }
    }

    public function index()
    {
        $this->_admin_only();

        $data['title'] = 'Role Permissions';
        $data['js']    = 'users/role-permissions.inc';

        // Load roles constant
        $data['roles'] = USER_ROLE_OPT;

        // Modules/menus that can be toggled
        $data['module_tree'] = array(
            'dashboard' => array('label' => 'Dashboard', 'desc' => 'View main system metrics, active tasks, and project stats summary.', 'sub' => array()),
            'projects'  => array('label' => 'Projects', 'desc' => 'Top-level menu access for Projects.', 'sub' => array(
                'project_list'   => array('label' => 'Project List', 'desc' => 'Create, edit, view detail cards.'),
                'project_kanban' => array('label' => 'Project Kanban', 'desc' => 'Visual board view for project tracking.')
            )),
            'tasks'     => array('label' => 'Tasks', 'desc' => 'Top-level menu access for Tasks.', 'sub' => array(
                'task_list'   => array('label' => 'Task List', 'desc' => 'Manage, edit, status updates, time-logs.'),
                'task_kanban' => array('label' => 'Task Kanban', 'desc' => 'Visual Kanban boards for tasks.')
            )),
            'epics'     => array('label' => 'Epics', 'desc' => 'Manage high-level project epics and backlog roadmap items.', 'sub' => array()),
            'stories'   => array('label' => 'User Stories', 'desc' => 'Track user stories, assign points, and sprint status.', 'sub' => array()),
            'reports'   => array('label' => 'Reports', 'desc' => 'Access productivity charts and completed/overdue task reports.', 'sub' => array()),
            'users'     => array('label' => 'User Management', 'desc' => 'List users, modify attributes, and deactivate credentials.', 'sub' => array(
                'add_user' => array('label' => 'Add New User', 'desc' => 'Add new users to the system.'),
                'role_permissions' => array('label' => 'Role Permissions', 'desc' => 'Manage system access and module visibility for roles.')
            ))
        );

        $flat_modules = array();
        foreach ($data['module_tree'] as $k => $v) {
            $flat_modules[$k] = $v['label'];
            if (!empty($v['sub'])) {
                foreach ($v['sub'] as $sk => $sv) {
                    $flat_modules[$sk] = $sv['label'];
                }
            }
        }
        $data['modules'] = $flat_modules;

        // Fetch current permissions
        $permissions_raw = $this->db->query("SELECT * FROM tm_role_permissions")->result_array();

        $permissions = array();
        foreach ($permissions_raw as $p) {
            $perms = json_decode($p['permissions'], true);
            if (is_array($perms)) {
                foreach ($perms as $menu_key => $val) {
                    $permissions[$p['role']][$menu_key] = (int)$val;
                }
            }
        }

        $data['permissions'] = $permissions;

        $this->load->view('page/users/role-permissions', $data);
    }

    public function update()
    {
        // Enforce admin privileges
        if (!$this->session->userdata(SESS_HEAD . '_logged_in') || 
            $this->session->userdata(SESS_HEAD . '_role') !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Unauthorized access.'));
            return;
        }

        header('Content-Type: application/json');

        $role      = $this->input->post('role');
        $menu_key  = $this->input->post('menu_key');
        $has_access = (int)$this->input->post('has_access');

        // Validation
        $roles = array_keys(USER_ROLE_OPT);
        $modules = array(
            'dashboard', 'projects', 'project_list', 'project_kanban', 
            'tasks', 'task_list', 'task_kanban', 'epics', 'stories', 'reports', 'users', 'add_user', 'role_permissions'
        );

        if (!in_array($role, $roles) || !in_array($menu_key, $modules)) {
            echo json_encode(array('success' => false, 'message' => 'Invalid parameters.'));
            return;
        }

        // Prevent locking out admin from essential management pages
        if ($role === 'admin' && ($menu_key === 'users')) {
            echo json_encode(array('success' => false, 'message' => 'Admin role must retain access to User Management.'));
            return;
        }

        // Retrieve existing permissions JSON
        $exists = $this->db->query(
            "SELECT permissions FROM tm_role_permissions WHERE role = ? LIMIT 1",
            array($role)
        )->row_array();

        if ($exists) {
            $perms = json_decode($exists['permissions'], true);
            if (!is_array($perms)) {
                $perms = array();
            }
        } else {
            $perms = array();
        }

        // Set the new permission value
        $perms[$menu_key] = $has_access;
        $perms_json = json_encode($perms);

        if ($exists) {
            $this->db->where('role', $role);
            $this->db->update('tm_role_permissions', array('permissions' => $perms_json));
        } else {
            $this->db->insert('tm_role_permissions', array(
                'role'        => $role,
                'permissions' => $perms_json
            ));
        }

        echo json_encode(array('success' => true, 'message' => 'Permission updated successfully.'));
    }
}

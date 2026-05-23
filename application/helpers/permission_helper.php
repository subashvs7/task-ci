<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('has_menu_permission')) {
    /**
     * Check if the current user role has access to a specific menu key.
     *
     * @param string $menu_key
     * @return bool
     */
    function has_menu_permission($menu_key)
    {
        $CI =& get_instance();
        
        // Get current user role from session
        $role = $CI->session->userdata(SESS_HEAD . '_role');
        if (!$role) {
            return false;
        }

        // The "role-permission" configuration page itself is strictly restricted to Admin
        if ($menu_key === 'role-permission') {
            return ($role === 'admin');
        }

        // Query the database for the permission
        $query = $CI->db->query(
            "SELECT permissions FROM tm_role_permissions WHERE role = ? LIMIT 1",
            array($role)
        );

        if ($query->num_rows() > 0) {
            $perms = json_decode($query->row()->permissions, true);
            if (is_array($perms) && isset($perms[$menu_key])) {
                return (int)$perms[$menu_key] === 1;
            }
        }

        // Fallbacks if no permission row is found
        if ($role === 'admin') {
            return true;
        }

        return false;
    }
}

if (!function_exists('redirect_to_fallback')) {
    /**
     * Redirect to the first available module the user has permission to access.
     */
    function redirect_to_fallback()
    {
        $CI =& get_instance();

        $modules_to_check = array(
            'dashboard' => 'dash',
            'projects'  => 'project-list',
            'tasks'     => 'task-list',
            'epics'     => 'epic-list',
            'stories'   => 'story-list',
            'reports'   => 'task-report',
            'users'     => 'user-list'
        );

        foreach ($modules_to_check as $m_key => $route) {
            if (has_menu_permission($m_key)) {
                redirect($route);
            }
        }

        // Final fallback if absolutely no permission is granted
        redirect('change-password');
    }
}

if (!function_exists('get_assignable_roles')) {
    /**
     * Get the list of roles that the current logged-in user can assign.
     *
     * @return array
     */
    function get_assignable_roles()
    {
        $CI =& get_instance();
        $role = $CI->session->userdata(SESS_HEAD . '_role');

        if ($role === 'admin') {
            return array('manager' => 'Manager');
        } elseif ($role === 'manager') {
            return array('team_leader' => 'Team Leader');
        } elseif ($role === 'team_leader') {
            return array('staff' => 'Staff');
        }

        return array();
    }
}

if (!function_exists('get_assignable_users')) {
    /**
     * Get list of users the logged-in user can assign tasks to, based on role hierarchy.
     *
     * @param int|null $project_id Optional project boundary to ensure team alignment
     * @return array List of user rows
     */
    function get_assignable_users($project_id = NULL)
    {
        $CI =& get_instance();
        $role = $CI->session->userdata(SESS_HEAD . '_role');
        $uid  = $CI->session->userdata(SESS_HEAD . '_user_id');

        $CI->db->select('tm_users.user_id, tm_users.name, tm_users.role, tm_users.email');
        $CI->db->from('tm_users');
        $CI->db->where('status', 'Active');

        // Admin can assign to any active manager
        if ($role === 'admin') {
            $CI->db->where('role', 'manager');
        } elseif ($role === 'manager') {
            // Managers assign tasks to Team Leaders
            $CI->db->where('role', 'team_leader');
        } elseif ($role === 'team_leader') {
            // Team Leaders assign tasks to Staff members
            $CI->db->where('role', 'staff');
        } elseif ($role === 'staff') {
            // Staff members can only assign to themselves (self-management)
            $CI->db->where('user_id', $uid);
        }

        if ($project_id !== NULL) {
            $CI->db->join('tm_project_members', 'tm_project_members.user_id = tm_users.user_id');
            $CI->db->where('tm_project_members.project_id', (int)$project_id);
        }

        return $CI->db->order_by('name', 'ASC')->get()->result_array();
    }
}

if (!function_exists('calculate_working_days')) {
    /**
     * Calculates the exact number of weekdays (Mon-Fri) between two dates.
     *
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @return int Number of working days
     */
    function calculate_working_days($from, $to)
    {
        $from_time = strtotime($from);
        $to_time   = strtotime($to);

        if ($from_time >= $to_time) return 0;

        $working_days = 0;
        $curr = $from_time;

        while ($curr <= $to_time) {
            $w = (int)date('N', $curr);
            if ($w < 6) { // 1 = Monday, 5 = Friday
                $working_days++;
            }
            $curr = strtotime('+1 day', $curr);
        }

        return $working_days;
    }
}

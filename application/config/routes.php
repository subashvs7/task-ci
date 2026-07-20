<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login']           = 'login/index';
$route['logout']          = 'general/logout';
$route['change-password'] = 'general/change_password';

// Dashboard
$route['dash']            = 'dashboard/index';
$route['dash/(:num)']     = 'dashboard/index/$1';

// Projects
$route['project-list']              = 'project/project_list';
$route['project-list/(:num)']       = 'project/project_list/$1';
$route['project-kanban']            = 'project/project_kanban';
$route['project-gantt']             = 'project/project_gantt';
$route['get-gantt-data']            = 'project/get_gantt_data';
$route['get-gantt-data/(:num)']     = 'project/get_gantt_data/$1';
$route['project-detail/(:num)']     = 'project/project_detail/$1';

// Tasks
$route['task-list']                 = 'task/task_list';
$route['task-list/(:num)']          = 'task/task_list/$1';
$route['task-kanban']               = 'task/task_kanban';
$route['task-detail/(:num)']        = 'task/task_detail/$1';
$route['add-dependency']            = 'task/add_dependency';
$route['delete-dependency']         = 'task/delete_dependency';

// Epics
$route['epic-list']                 = 'epic/epic_list';
$route['epic-list/(:num)']          = 'epic/epic_list/$1';
$route['get-epics-ajax']            = 'epic/get_epics_ajax';

// User Stories
$route['story-list']                = 'story/story_list';
$route['story-list/(:num)']         = 'story/story_list/$1';
$route['get-stories-ajax']          = 'story/get_stories_ajax';

// Users (Admin only)
$route['user-list']                 = 'user/user_list';
$route['user-list/(:num)']          = 'user/user_list/$1';
$route['get-users-ajax']            = 'user/get_users_ajax';
$route['role-permission']           = 'rolePermission/index';
$route['update-role-permission']    = 'rolePermission/update';

// Reports
$route['task-report']               = 'report/task_report';
$route['task-report/(:num)']        = 'report/task_report/$1';
$route['project-report']            = 'report/project_report';
$route['project-report/(:num)']     = 'report/project_report/$1';
$route['feasibility-analysis']        = 'report/feasibility_analysis';
$route['feasibility-analysis/(:num)'] = 'report/feasibility_analysis/$1';

// Password Manager
$route['password-manager']                = 'passwordManager/index';
$route['password-manager/(:num)']         = 'passwordManager/index/$1';
$route['password-manager/form']           = 'passwordManager/form';
$route['password-manager/form/(:num)']    = 'passwordManager/form/$1';
$route['pm-save']                         = 'passwordManager/save';
$route['pm-delete']                       = 'passwordManager/delete';
$route['pm-toggle-fav']                   = 'passwordManager/toggle_favorite';
$route['pm-get-secret']                   = 'passwordManager/get_secret';
$route['pm-generate-password']            = 'passwordManager/generate_password';
$route['pm-check-strength']               = 'passwordManager/check_strength';
// Category Master
$route['pm-categories']                   = 'passwordManager/categories';
$route['pm-category-save']                = 'passwordManager/category_save';
$route['pm-category-delete']              = 'passwordManager/category_delete';
$route['pm-category-reorder']             = 'passwordManager/category_reorder';

// Contact Book (Admin Only)
$route['contact-book']                    = 'contactBook/index';
$route['contact-book/save']               = 'contactBook/save';
$route['contact-book/get']                = 'contactBook/get_contact';
$route['contact-book/delete']             = 'contactBook/delete';

// Database Migration
$route['run-migration']             = 'general/run_migration';

// Project Members
$route['add-project-member']        = 'general/add_project_member';
$route['remove-project-member']     = 'general/remove_project_member';

// General AJAX
$route['delete-record']             = 'general/delete_record';
$route['get-stories-by-project']    = 'general/get_stories_by_project';
$route['get-epics-by-project']      = 'general/get_epics_by_project';
$route['get-stories-by-epic']       = 'general/get_stories_by_epic';
$route['get-active-projects']       = 'general/get_active_projects';
$route['get-users-dropdown']        = 'general/get_users_dropdown';
$route['update-task-status']        = 'general/update_task_status';
$route['update-subtask-status']     = 'general/update_subtask_status';
$route['add-comment']               = 'general/add_comment';
$route['delete-comment']            = 'general/delete_comment';
$route['add-time-log']              = 'general/add_time_log';
$route['delete-time-log']           = 'general/delete_time_log';
$route['upload-attachment']         = 'general/upload_attachment';
$route['delete-attachment']         = 'general/delete_attachment';
$route['get-project-team-stats']    = 'general/get_project_team_stats';

// Task Work Sessions
$route['task-toggle-session']        = 'task/toggle_session';
$route['task-complete']              = 'task/complete_task';
$route['get-tasks-ajax']             = 'task/get_tasks_ajax';
$route['task-sessions/(:num)']       = 'task/get_sessions/$1';
$route['task-effort-status/(:num)']  = 'task/get_effort_status/$1';
$route['task-quick-view']            = 'task/task_quick_view';
$route['get-scheduled-tasks']        = 'task/get_scheduled_tasks';
$route['save-fcm-token']             = 'user/save_fcm_token';


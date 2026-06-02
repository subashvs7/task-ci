# ZazuTask - Task Manager CI

ZazuTask is a comprehensive, hierarchical Task and Project Management System built on **CodeIgniter 3**. It allows organizations to track work efficiently across different roles, from high-level Projects and Epics down to User Stories and individual Tasks.

## Features

- **Hierarchical Work Breakdown:** Organize work into Projects > Epics > User Stories > Tasks.
- **Role-Based Access Control (RBAC):** Distinct permissions for Admins, Managers, Team Leaders, and Staff members.
- **Kanban & List Views:** Visualize progress using drag-and-drop Kanban boards or detailed tabular list views.
- **Live Work Sessions:** Built-in time tracking. Users can "Start Work" and "Stop Work" to automatically log hours and calculate effort overages.
- **Gantt Charts:** Interactive visual timeline representation of project timelines and task dependencies.
- **Proof Attachments:** Upload image/PDF proofs for completed tasks. Includes built-in image previewing with Viewer.js.
- **Dependencies:** Set blocking dependencies between different tasks.
- **Dynamic Dashboards:** Real-time metrics, workload distribution, and overdue task alerts.
- **Export & Reporting:** Generate and export feasibility analysis and performance reports.

## Tech Stack

- **Backend:** PHP, CodeIgniter 3
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (jQuery), Bootstrap 3
- **UI Components:** AdminLTE 2 theme, SweetAlert2, Select2, Viewer.js

## Installation

1. Clone the repository to your local server environment (e.g., Laragon, XAMPP).
2. Create a new MySQL database (e.g., `taskci`).
3. Import the provided SQL schema (`db_alter.sql` / database dump).
4. Update the database configuration in `application/config/database.php`.
5. Update the base URL in `application/config/config.php`.
6. Access the application via your local server URL (e.g., `http://localhost/task-ci/`).

## Default Roles

- **Admin:** Full access to all projects, user management, and system settings.
- **Manager:** Can oversee assigned projects, view all tasks, and manage project members.
- **Team Leader:** Can manage tasks, epics, and stories within their assigned projects.
- **Staff:** Can view and log time on tasks specifically assigned to them or created by them.

## License

Copyright &copy; 2026 ZazuTask Manager. All rights reserved.

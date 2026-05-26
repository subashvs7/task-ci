<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo APP_NAME; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/Ionicons/css/ionicons.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/skins/_all-skins.min.css">
  <!-- Plugins -->
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/hierarchy.css">

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">

  <style>
    body, .wrapper, .main-sidebar, .main-header, .content-wrapper {
      font-family: 'Outfit', sans-serif !important;
    }
    
    .navbar-center {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      color: #1e1b4b !important;
      font-weight: bold;
      font-size: 20px;
      letter-spacing: 0.5px;
    }

    /* Top Bar - Header & Navbar Gradient */
    .skin-blue .main-header .navbar {
      background: linear-gradient(to left, #E8DBFC, #E0ECFF, #FFFFFF) !important;
      border: none !important;
      border-bottom: 1px solid #e2e8f0 !important;
      box-shadow: 0 2px 10px rgba(124, 58, 237, 0.05) !important;
      height: 50px !important;
      min-height: 50px !important;
    }
    .skin-blue .main-header .logo {
      background-color: #ffffff !important;
      color: #1e1b4b !important;
      border-right: 1px solid #f1f5f9 !important;
      border-bottom: 1px solid #e2e8f0 !important;
      height: 50px;
      transition: background-color 0.3s ease;
    }
    .skin-blue .main-header .logo:hover {
      background-color: #fafafc !important;
    }
    .skin-blue .main-header .navbar .sidebar-toggle {
      color: #475569 !important;
      transition: background-color 0.3s ease;
    }
    .skin-blue .main-header .navbar .sidebar-toggle:hover {
      background-color: rgba(124, 58, 237, 0.08) !important;
    }

    /* Navbar User Menu styling */
    .skin-blue .main-header .navbar .nav > li > a {
      color: #475569 !important;
      transition: background-color 0.3s ease;
    }
    .skin-blue .main-header .navbar .nav > li > a:hover,
    .skin-blue .main-header .navbar .nav > li > a:active,
    .skin-blue .main-header .navbar .nav > li > a:focus,
    .skin-blue .main-header .navbar .nav > li.open > a {
      background-color: rgba(124, 58, 237, 0.08) !important;
      color: #7c3aed !important;
    }

    /* Dropdown Menus */
    .skin-blue .main-header .navbar .dropdown-menu {
      border: 1px solid #e2e8f0 !important;
      border-radius: 12px !important;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
      overflow: hidden;
      padding: 0;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-header {
      background: linear-gradient(to left, #E8DBFC, #E0ECFF, #FFFFFF) !important;
      height: auto !important;
      padding: 24px 20px !important;
      border-bottom: 1px solid #e2e8f0 !important;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-header p {
      color: #1e1b4b !important;
      font-weight: 600;
      font-size: 15px;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-header small {
      color: #64748b !important;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-header img {
      border: 3px solid rgba(124, 58, 237, 0.15) !important;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-footer {
      background-color: #ffffff !important;
      padding: 15px !important;
      border-top: 1px solid #f1f5f9 !important;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-footer .btn-default {
      background: #f1f5f9 !important;
      color: #475569 !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      font-weight: 600;
      padding: 8px 16px;
      transition: all 0.3s ease;
    }
    .skin-blue .main-header .navbar .dropdown-menu li.user-footer .btn-default:hover {
      background: #e2e8f0 !important;
      color: #1e293b !important;
    }

    /* Main Sidebar Light Theme styling */
    .skin-blue .main-sidebar, .skin-blue .left-side {
      background: linear-gradient(to left, #E8DBFC, #E0ECFF, #FFFFFF) !important;
      border-right: 1px solid #e2e8f0 !important;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.01) !important;
    }
    .skin-blue .sidebar {
      padding: 0 !important;
    }

    /* Sidebar User Panel */
    .skin-blue .user-panel {
      padding: 20px 15px !important;
      border-bottom: 1px solid rgba(124, 58, 237, 0.1) !important;
      background: transparent !important;
    }
    .skin-blue .user-panel > .info > p {
      color: #1e293b !important;
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 4px;
    }
    .skin-blue .user-panel > .info > p > i {
      color: #64748b !important;
      font-weight: 500;
      font-size: 11px;
    }
    .skin-blue .user-panel > .info > a {
      color: #22c55e !important;
      font-weight: 500;
      font-size: 11px;
    }

    /* Sidebar Navigation Headers */
    .skin-blue .sidebar-menu > li.header {
      color: #94a3b8 !important;
      background: transparent !important;
      font-weight: 700;
      font-size: 11px;
      letter-spacing: 0.8px;
      padding: 20px 25px 10px 25px !important;
    }

    /* Sidebar Menu items */
    .skin-blue .sidebar-menu > li > a {
      border-left: 3px solid transparent !important;
      color: #475569 !important;
      font-weight: 500;
      font-size: 14px;
      padding: 12px 20px 12px 22px !important;
      transition: all 0.3s ease;
    }
    .skin-blue .sidebar-menu > li > a > i {
      width: 20px;
      font-size: 15px;
      margin-right: 8px;
      color: #64748b !important;
      transition: color 0.3s ease;
    }

    /* Hover & Active States */
    .skin-blue .sidebar-menu > li:hover > a,
    .skin-blue .sidebar-menu > li.active > a {
      color: #7c3aed !important;
      background: rgba(255, 255, 255, 0.6) !important;
    }
    .skin-blue .sidebar-menu > li:hover > a > i,
    .skin-blue .sidebar-menu > li.active > a > i {
      color: #7c3aed !important;
    }
    .skin-blue .sidebar-menu > li.active > a {
      border-left-color: #7c3aed !important;
      font-weight: 600;
    }

    /* Treeview Menus */
    .skin-blue .sidebar-menu > li.treeview.active > a,
    .skin-blue .sidebar-menu > li.treeview.menu-open > a,
    .skin-blue .sidebar-menu > li.menu-open > a {
      border-left-color: #7c3aed !important;
      background: rgba(255, 255, 255, 0.6) !important;
      color: #7c3aed !important;
    }
    .skin-blue .sidebar-menu > li.treeview.active > a > i,
    .skin-blue .sidebar-menu > li.treeview.menu-open > a > i,
    .skin-blue .sidebar-menu > li.menu-open > a > i {
      color: #7c3aed !important;
    }
    .skin-blue .sidebar-menu .treeview-menu {
      background: rgba(255, 255, 255, 0.4) !important;
      padding-left: 10px !important;
      padding-bottom: 5px !important;
    }
    
    /* Collapsed Sidebar Popover Styling */
    .skin-blue.sidebar-collapse .sidebar-menu > li:hover > .treeview-menu {
      background-color: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    }
    .skin-blue.sidebar-collapse .sidebar-menu > li:hover > a > span {
      background-color: #f5f3ff !important;
      color: #7c3aed !important;
      border: 1px solid #e2e8f0 !important;
    }
    .skin-blue .sidebar-menu .treeview-menu > li > a {
      color: #64748b !important;
      font-size: 13px;
      padding: 8px 15px 8px 25px !important;
      transition: all 0.3s ease;
    }
    .skin-blue .sidebar-menu .treeview-menu > li > a > i {
      font-size: 12px;
      margin-right: 6px;
      color: #94a3b8 !important;
    }
    .skin-blue .sidebar-menu .treeview-menu > li:hover > a,
    .skin-blue .sidebar-menu .treeview-menu > li.active > a {
      color: #7c3aed !important;
      background: transparent !important;
    }
    .skin-blue .sidebar-menu .treeview-menu > li:hover > a > i,
    .skin-blue .sidebar-menu .treeview-menu > li.active > a > i {
      color: #7c3aed !important;
    }

    /* Badges */
    .skin-blue .sidebar-menu .label-primary {
      background-color: #7c3aed !important;
      border-radius: 8px;
      padding: 3px 6px;
    }
    @media (min-width: 768px) {
      .main-header {
        height: 50px !important;
        max-height: none !important;
      }
      .main-header .logo {
        padding: 15px 12px !important;
        height: 110px !important;
        line-height: normal !important;
        overflow: hidden;
        display: flex !important;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid #e2e8f0 !important;
      }
      .main-sidebar {
        padding-top: 110px !important;
        transition: padding-top 0.3s ease-in-out !important;
      }
    }
    
    .main-header .logo .logo-lg {
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      justify-content: center !important;
      height: 100% !important;
      width: 100% !important;
    }
    .main-header .logo .logo-lg img {
      max-height: 45px !important;
      max-width: 100% !important;
      object-fit: contain !important;
      margin-bottom: 6px !important;
    }
    .logo-text-sub {
      font-size: 13px !important;
      font-weight: 700 !important;
      color: #6366f1 !important;
      text-transform: uppercase !important;
      letter-spacing: 1px !important;
      line-height: 1.2 !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    .main-header .logo .logo-mini {
      display: none !important;
    }
    
    /* Collapsed sidebar states */
    .sidebar-collapse .main-header .logo .logo-lg {
      display: none !important;
    }
    .sidebar-collapse .main-header .logo .logo-mini {
      display: flex !important;
      align-items: center;
      justify-content: center;
      height: 50px !important;
      width: 100%;
      padding: 0 !important;
    }
    .sidebar-collapse .main-header .logo .logo-mini img {
      max-height: 42px !important;
      max-width: 100% !important;
      object-fit: contain !important;
    }
    .sidebar-collapse .main-sidebar {
      padding-top: 50px !important;
    }
    .badge-status-backlog    { background-color: #95a5a6; }
    .badge-status-todo       { background-color: #3498db; }
    .badge-status-in_progress{ background-color: #e67e22; }
    .badge-status-in_review  { background-color: #9b59b6; }
    .badge-status-done       { background-color: #27ae60; }
    .badge-status-closed     { background-color: #7f8c8d; }
    .badge-priority-low      { background-color: #27ae60; }
    .badge-priority-medium   { background-color: #2980b9; }
    .badge-priority-high     { background-color: #e67e22; }
    .badge-priority-critical { background-color: #c0392b; }
    .badge-type-bug          { background-color: #e74c3c; }
    .badge-type-feature      { background-color: #9b59b6; }
    .badge-type-task         { background-color: #3498db; }
    .badge-type-improvement  { background-color: #1abc9c; }
    .badge-type-test         { background-color: #e67e22; }
    .badge-type-research     { background-color: #34495e; }
    .badge-type-design       { background-color: #e91e63; }
    .badge-type-documentation{ background-color: #607d8b; }
    /* Project status label colors */
    .label-planning   { background-color: #9b59b6 !important; }
    .label-active     { background-color: #27ae60 !important; }
    .label-on-hold    { background-color: #e67e22 !important; }
    .label-completed  { background-color: #3498db !important; }
    .label-cancelled  { background-color: #c0392b !important; }
    .label-purple     { background-color: #9b59b6 !important; }
    .label-orange     { background-color: #e67e22 !important; }
    /* Deadline badge */
    .deadline-badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:11px; font-weight:600; }
    .deadline-ok    { background:#d5f5e3; color:#1a8a4a; }
    .deadline-warn  { background:#fdebd0; color:#d35400; }
    .deadline-over  { background:#fadbd8; color:#c0392b; }
    .kanban-board { display: flex; gap: 15px; overflow-x: auto; padding-bottom: 15px; }
    .kanban-col { min-width: 260px; max-width: 300px; flex-shrink: 0; }
    .kanban-col-header { padding: 10px 12px; border-radius: 4px 4px 0 0; color: #fff; font-weight: bold; font-size: 13px; }
    .kanban-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; padding: 10px; margin-bottom: 8px; cursor: pointer; }
    .kanban-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
    .kanban-card-title { font-weight: 600; font-size: 13px; margin-bottom: 6px; }
    .kanban-card-meta { font-size: 11px; color: #888; }
    .kanban-body { background: #f4f6f9; border-radius: 0 0 4px 4px; padding: 10px; min-height: 120px; }
    .task-detail-section { border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 15px; }
    .task-detail-section h4 { margin-top: 0; color: #2c3e50; font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
    .progress-bar-container { height: 18px; background: #ecf0f1; border-radius: 9px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 9px; transition: width 0.4s; }
    .comment-item { border-left: 3px solid #3498db; padding: 10px 15px; margin-bottom: 10px; background: #f8f9fa; }
    .time-log-item { padding: 8px 12px; border-bottom: 1px solid #eee; }
    .attachment-item { display: inline-block; margin: 5px; padding: 6px 10px; background: #ecf0f1; border-radius: 4px; font-size: 12px; }
    @media (max-width: 767px) {
      .main-header .logo {
        height: 50px !important;
        padding: 0 12px !important;
      }
      .main-header .logo .logo-lg {
        flex-direction: row !important;
        gap: 8px !important;
      }
      .main-header .logo .logo-lg img {
        max-height: 30px !important;
        margin-bottom: 0 !important;
      }
      .logo-text-sub {
        font-size: 12px !important;
      }
      .navbar-center { font-size: 14px; }
    }

    /* Responsive Top Bar User Menu - Custom User Details block */
    .user-menu > a {
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      height: 50px !important;
      padding: 0 15px !important;
      background: transparent !important;
    }
    .user-menu > a .user-image {
      margin: 0 !important;
      float: none !important;
      width: 32px !important;
      height: 32px !important;
      border-radius: 50% !important;
      border: 1.5px solid rgba(124, 58, 237, 0.15) !important;
    }
    .user-details-nav {
      display: flex !important;
      flex-direction: column !important;
      text-align: left !important;
      line-height: 1.2 !important;
    }
    .user-name-nav {
      font-weight: 600 !important;
      font-size: 13px !important;
      color: #1e293b !important;
      display: block !important;
    }
    .user-role-nav {
      font-size: 10px !important;
      color: #64748b !important;
      font-weight: 500 !important;
      display: block !important;
    }
    .user-role-nav i.text-success {
      color: #22c55e !important;
      font-size: 8px !important;
    }

    @media (max-width: 767px) {
      .user-details-nav {
        display: none !important;
      }
      .user-menu > a {
        padding: 0 10px !important;
      }
    }
  </style>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <a href="<?php echo site_url('dash'); ?>" class="logo bg-white">
      <span class="logo-mini">
        <img src="<?php echo base_url('asset/frontend/img/zazutask_login.png') ?>" alt="Mini">
      </span>
      <span class="logo-lg">
        <img src="<?php echo base_url('asset/frontend/img/zazutask_login.png') ?>" alt="<?php echo APP_NAME; ?>">
        <div class="logo-text-sub">Task Manager</div>
      </span>
    </a>

    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <!-- <div class="navbar-center"><?php echo APP_NAME; ?></div> -->

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo base_url() ?>asset/images/user.jpg" class="user-image" alt="User Image">
              <div class="user-details-nav">
                <span class="user-name-nav"><?php echo strtoupper($this->session->userdata(SESS_HEAD . '_user_name')); ?></span>
                <span class="user-role-nav">
                  <?php echo strtoupper($this->session->userdata(SESS_HEAD . '_role')); ?> &bull; <i class="fa fa-circle text-success"></i> Online
                </span>
              </div>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="<?php echo base_url() ?>asset/images/user.jpg" class="img-circle" alt="User Image">
                <p>
                  <?php echo strtoupper($this->session->userdata(SESS_HEAD . '_user_name')); ?>
                  <small><?php echo date('d-M-Y'); ?></small>
                </p>
              </li>
              <li class="user-footer">
                <div class="pull-right">
                  <a href="<?php echo site_url('logout') ?>" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <?php include_once('left-menu.php'); ?>

  <div class="content-wrapper">

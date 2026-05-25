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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">

  <style>
    .navbar-center {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      color: #fff;
      font-weight: bold;
      font-size: 20px;
    }
    .skin-blue .main-header .logo {
      background-color: #ffffff;
      color: #fff;
      border-bottom: 0 solid transparent;
    }
    .skin-blue .main-header .navbar {
      background-color: #2c3e50;
    }
    .skin-blue .main-sidebar, .skin-blue .left-side {
      background-color: #2c3e50;
    }
    .main-header .logo {
      padding: 0 !important;
      height: 50px;
      line-height: 50px;
      overflow: hidden;
    }
    .main-header .logo .logo-lg {
      display: block;
      height: 50px;
      width: 100%;
    }
    .main-header .logo .logo-lg img {
      height: 100%;
      width: 100%;
      object-fit: contain;
      object-position: center;
    }
    .main-header .logo .logo-mini {
      position: relative;
      height: 50px;
      width: 50px;
      margin: 0 auto;
      overflow: hidden;
    }
    .main-header .logo .logo-mini img {
      height: 40px;
      width: auto;
      max-width: none;
      position: absolute;
      left: 5px;
      top: 5px;
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
      .main-header .logo .logo-lg img { max-height: 35px; }
      .navbar-center { font-size: 14px; }
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
        <img src="<?php echo base_url('asset/frontend/img/zazutask_new.png') ?>" alt="Mini">
      </span>
      <span class="logo-lg">
        <img src="<?php echo base_url('asset/frontend/img/zazutask_new.png') ?>" alt="<?php echo APP_NAME; ?>">
      </span>
    </a>

    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-center"><?php echo APP_NAME; ?></div>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo base_url() ?>asset/images/user.jpg" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo strtoupper($this->session->userdata(SESS_HEAD . '_user_name')); ?></span>
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

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo APP_NAME; ?> &mdash; Login</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>asset/dist/css/skins/skin-blue.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap">
  <style>
    body {
      background: linear-gradient(to left, #E8DBFC, #E0ECFF, #FFFFFF) !important;
      min-height: 100vh;
      margin: 0;
      font-family: 'Outfit', 'Source Sans Pro', sans-serif !important;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow-x: hidden;
    }
    /* Animated Ambient Blobs */
    .bg-blobs {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      overflow: hidden;
      pointer-events: none;
    }
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(100px);
      opacity: 0.55;
      animation: float 20s infinite ease-in-out;
    }
    .blob-1 {
      top: -10%;
      right: -10%;
      width: 450px;
      height: 450px;
      background: #D8B4FE; /* soft purple */
      animation-delay: 0s;
    }
    .blob-2 {
      bottom: -10%;
      left: -10%;
      width: 500px;
      height: 500px;
      background: #93C5FD; /* soft blue */
      animation-delay: -5s;
    }
    @keyframes float {
      0%, 100% {
        transform: translate(0, 0) scale(1);
      }
      50% {
        transform: translate(50px, -70px) scale(1.15);
      }
    }

    .login-box {
      width: 100%;
      max-width: 400px;
      margin: 0 auto;
      padding: 20px;
      z-index: 10;
      position: relative;
    }
    .login-logo {
      text-align: center;
      margin-bottom: 24px;
    }
    .login-logo img {
      max-height: 75px;
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.05));
    }
    .login-logo p {
      color: #1e1b4b !important; /* Dark indigo */
      font-size: 24px;
      font-weight: 700;
      margin-top: 12px;
      letter-spacing: 0.5px;
      font-family: 'Outfit', sans-serif;
    }
    
    /* Glassmorphic Login Card */
    .login-box-body {
      background: rgba(255, 255, 255, 0.85) !important;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 2px solid #e2d9f3 !important;
      border-radius: 16px !important;
      padding: 35px 30px !important;
      box-shadow: 
        0 4px 30px rgba(0, 0, 0, 0.02),
        0 20px 50px rgba(124, 58, 237, 0.06),
        0 1px 0 rgba(255, 255, 255, 0.8) inset !important;
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s ease;
    }
    .login-box-body:hover {
      transform: translateY(-2px);
      border-color: #d2c4f1 !important;
      box-shadow: 
        0 6px 35px rgba(0, 0, 0, 0.03),
        0 24px 60px rgba(124, 58, 237, 0.10),
        0 1px 0 rgba(255, 255, 255, 0.9) inset;
    }

    /* Message / Title */
    .login-box-msg {
      color: #475569 !important;
      font-weight: 500;
      font-size: 15px;
      margin-bottom: 25px;
      text-align: center;
      padding: 0;
    }

    /* Form Controls */
    .form-group.has-feedback {
      position: relative;
      margin-bottom: 22px;
    }
    .login-box-body .form-control {
      border-radius: 10px !important;
      height: 48px !important;
      font-size: 14px !important;
      background: #f8fafc !important;
      border: 2px solid #e2d9f3 !important;
      padding-left: 16px !important;
      padding-right: 42px !important;
      color: #1e293b !important;
      transition: all 0.3s ease !important;
      box-shadow: none !important;
    }
    .login-box-body .form-control:focus {
      border-color: #818cf8 !important;
      background: #fff !important;
      box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15) !important;
    }
    .form-control-feedback {
      position: absolute;
      top: 0;
      right: 0;
      z-index: 2;
      display: block;
      width: 48px;
      height: 48px;
      line-height: 48px;
      text-align: center;
      color: #94a3b8 !important;
      transition: color 0.3s ease;
    }
    .form-control:focus + .form-control-feedback {
      color: #6366f1 !important;
    }

    /* Buttons */
    .login-box-body .btn-primary {
      background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%) !important;
      border: none !important;
      height: 48px !important;
      font-size: 15px !important;
      font-weight: 600 !important;
      letter-spacing: 0.5px !important;
      border-radius: 10px !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2) !important;
      color: #fff !important;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .login-box-body .btn-primary:hover,
    .login-box-body .btn-primary:focus {
      background: linear-gradient(135deg, #8b5cf6 0%, #2563eb 100%) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 20px rgba(124, 58, 237, 0.35) !important;
      color: #fff !important;
      outline: none !important;
    }
    .login-box-body .btn-primary:active {
      transform: translateY(1px) !important;
      box-shadow: 0 4px 8px rgba(124, 58, 237, 0.15) !important;
    }

    /* Alerts */
    .alert-danger {
      background-color: #fef2f2 !important;
      border-color: #fee2e2 !important;
      color: #991b1b !important;
      border-radius: 10px !important;
      font-size: 13px !important;
      padding: 12px 16px !important;
      margin-bottom: 20px !important;
      border: 1px solid #fee2e2 !important;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.05) !important;
    }
    .alert-danger .close {
      color: #991b1b !important;
      opacity: 0.6 !important;
      font-size: 20px !important;
      line-height: 1 !important;
    }
    .alert-danger .close:hover {
      opacity: 1 !important;
    }

    /* Footer */
    .login-footer {
      text-align: center;
      color: #64748b !important;
      font-size: 13px !important;
      margin-top: 24px !important;
      font-weight: 500 !important;
    }
  </style>
</head>
<body>
<div class="bg-blobs">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
</div>


<div class="login-box">

  <div class="login-box-body">
    <div class="login-logo">
      <img src="<?php echo base_url('asset/frontend/img/zazutask_login.png') ?>" alt="<?php echo APP_NAME; ?>">
    </div>
    <p class="login-box-msg">Sign in to your account</p>

    <?php if ($this->session->flashdata('alert_error')): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo $this->session->flashdata('alert_error'); ?>
      </div>
    <?php endif; ?>

    <form action="<?php echo site_url('login') ?>" method="post">
      <input type="hidden" name="mode" value="Login">
      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <button type="submit" class="btn btn-primary btn-block btn-flat">
            <i class="fa fa-sign-in"></i> Sign In
          </button>
        </div>
      </div>
    </form>
  </div>
  <div class="login-footer">Copyright &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></div>
</div>

<script src="<?php echo base_url() ?>asset/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>

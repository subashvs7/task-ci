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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700">
  <style>
    body { background: #2c3e50; }
    .login-box { width: 380px; margin: 80px auto; }
    .login-logo { text-align: center; margin-bottom: 20px; }
    .login-logo img { max-height: 70px; }
    .login-logo p { color: #fff; font-size: 22px; font-weight: 700; margin-top: 10px; letter-spacing: 1px; }
    .login-box-body { background: #fff; border-radius: 6px; padding: 30px; box-shadow: 0 4px 24px rgba(0,0,0,0.25); }
    .login-box-body .form-control { border-radius: 4px; height: 42px; font-size: 14px; }
    .login-box-body .btn-primary { background: #2c3e50; border-color: #2c3e50; height: 42px; font-size: 15px; font-weight: 600; letter-spacing: 0.5px; }
    .login-box-body .btn-primary:hover { background: #1a252f; border-color: #1a252f; }
    .login-footer { text-align: center; color: #aaa; font-size: 12px; margin-top: 18px; }
  </style>
</head>
<body>

<div class="login-box">
  <div class="login-logo">
    <img src="<?php echo base_url('asset/frontend/img/logo.png') ?>" alt="<?php echo APP_NAME; ?>">
    <p><?php echo APP_NAME; ?></p>
  </div>

  <div class="login-box-body">
    <p class="login-box-msg" style="color:#555; margin-bottom:20px;">Sign in to your account</p>

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

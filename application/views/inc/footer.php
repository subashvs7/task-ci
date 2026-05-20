  </div><!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      Page rendered in <strong>{elapsed_time}</strong> seconds.
    </div>
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#"><?php echo APP_NAME; ?></a>.</strong> All rights reserved.
  </footer>
</div><!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery-ui/jquery-ui.min.js"></script>
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Moment + DateRangePicker -->
<script src="<?php echo base_url() ?>asset/bower_components/moment/min/moment.min.js"></script>
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- DatePicker -->
<script src="<?php echo base_url() ?>asset/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Slimscroll -->
<script src="<?php echo base_url() ?>asset/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo base_url() ?>asset/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url() ?>asset/dist/js/adminlte.min.js"></script>

<script>
  $(function () {
    $('.navbar-nav .user.user-menu > .dropdown-toggle').dropdown();
    $('.navbar-nav').on('click', '.user.user-menu > .dropdown-toggle', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).dropdown('toggle');
    });
    $(document).on('click', function () {
      $('.user.user-menu.open, .user.user-menu.show').removeClass('open show')
        .find('> .dropdown-toggle[aria-expanded="true"]').attr('aria-expanded', 'false');
    });
    // Init select2 globally
    if ($.fn.select2) {
      $('.select2').select2({ width: '100%' });
    }
  });
</script>

<?php include_once('inc-js/' . $js); ?>

</body>
</html>

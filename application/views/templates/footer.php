    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            Version 1.0.0
        </div>
        <strong>Copyright &copy; <?= date('Y') ?> <a href="#" style="color:var(--primary-color);">Institut Teknologi PLN</a>.</strong> All rights reserved.
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="<?= base_url('assets/vendor/jquery/jquery-3.7.0.min.js') ?>"></script>
<!-- Bootstrap 5 Bundle -->
<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE App (requires jQuery & Bootstrap 4 plugins usually, but works mostly fine with 5 or we just use it for layout) -->
<!-- We load bootstrap 4 bundle from CDN just for adminlte internal scripts if needed, but since we use bs5, we'll try to stick to bs5 bundle. AdminLTE 3 requires bs4, so for some interactive components we might need it, but let's try with bs5 bundle and adminlte js. -->
<script src="<?= base_url('assets/vendor/adminlte/adminlte.min.js') ?>"></script>
<!-- DataTables  & Plugins -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<!-- Select2 -->
<script src="<?= base_url('assets/vendor/select2/js/select2.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js') ?>"></script>
<!-- ChartJS -->
<script src="<?= base_url('assets/vendor/chartjs/chart.min.js') ?>"></script>
<!-- Custom App JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<!-- Inline Scripts -->
<script>
    var base_url = '<?= base_url() ?>';
</script>
<?php if (!empty($page_js)): ?>
<script>
<?= $page_js ?>
</script>
<?php endif; ?>
</body>
</html>


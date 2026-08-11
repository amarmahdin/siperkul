<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? $title . ' - SIPERKUL' : 'SIPERKUL ITPLN' ?></title>
    
    <!-- Google Font: Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,400i,600,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>">
    <!-- Bootstrap 5 -->
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Select2 Bootstrap 5 Theme -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/select2/css/select2.min.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/vendor/select2/css/select2-bootstrap-5-theme.min.css') ?>" />
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.css') ?>">
    <!-- Theme style AdminLTE 3 -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/adminlte/adminlte.min.css') ?>">
    <!-- Custom ITPLN Theme -->
    <link rel="stylesheet" href="<?= base_url('assets/css/itpln-theme.css') ?>">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('dashboard') ?>" class="nav-link">Sistem Informasi Pengaturan Ruang Kuliah</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Fullscreen -->
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <!-- User Profile -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                    <i class="fas fa-user-circle fa-lg text-primary"></i> <?= $this->session->userdata('nama_lengkap') ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><h6 class="dropdown-header"><?= $this->session->userdata('role') ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="<?= base_url('auth/logout') ?>" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

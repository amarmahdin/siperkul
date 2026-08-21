<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SIPERKUL ITPLN</title>
    <!-- Google Font: Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,400i,600,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-color: #003E7E;
            --secondary-color: #FDB813;
            --bg-color: #F4F7FC;
        }
        body {
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
        }
        .login-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, #001f3f 100%);
            color: white;
            padding: 40px;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .login-right {
            background: white;
            padding: 50px;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .btn-login {
            background-color: var(--secondary-color);
            color: #000;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 10px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #e6a710;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                max-width: 400px;
            }
            .login-left, .login-right {
                width: 100%;
            }
            .login-left {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card fade-in">
        <div class="login-left">
            <i class="fas fa-university fa-4x mb-3" style="color: var(--secondary-color);"></i>
            <h2 class="fw-bold mb-2">SIPERKUL</h2>
            <p class="mb-0">Sistem Informasi Pengaturan Ruang Kuliah</p>
            <h5 class="mt-3 text-warning">Institut Teknologi PLN</h5>
        </div>
        <div class="login-right">
            <h3 class="fw-bold text-center mb-4" style="color: var(--primary-color);">Masuk ke Akun</h3>
            <form id="formLogin">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control border-start-0" name="username" placeholder="Masukkan Username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control border-start-0" name="password" placeholder="Masukkan Password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-login w-100" id="btnLogin">
                    Login <i class="fas fa-sign-in-alt ms-1"></i>
                </button>
            </form>

            <?php /* SSO Microsoft dinonaktifkan sementara
            <div class="text-center my-3 text-muted">atau</div>

            <a href="<?= base_url('auth/microsoft_login') ?>" class="btn btn-outline-primary w-100">
                <i class="fab fa-microsoft me-1"></i> Login dengan Microsoft
            </a>
            */ ?>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    <?php if ($this->session->flashdata('info')): ?>
    Swal.fire({
        icon: 'info',
        title: 'Menunggu Verifikasi',
        text: <?= json_encode($this->session->flashdata('info')) ?>,
        confirmButtonText: 'Mengerti'
    });
    <?php elseif ($this->session->flashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: <?= json_encode($this->session->flashdata('error')) ?>
    });
    <?php endif; ?>

    $('#formLogin').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnLogin');
        let originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url("auth/process_login") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect || '<?= base_url("dashboard") ?>';
                    });
                } else if (response.status === 'info') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Verifikasi',
                        text: response.message,
                        confirmButtonText: 'Mengerti'
                    });
                    btn.html(originalText).prop('disabled', false);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                    btn.html(originalText).prop('disabled', false);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan pada server.'
                });
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>

</body>
</html>

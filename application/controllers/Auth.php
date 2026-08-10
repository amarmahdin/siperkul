<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->model('Audit_model');
    }

    public function index() {
        if ($this->session->userdata('id_user')) {
            redirect($this->_home_by_role($this->session->userdata('role')));
        }
        $this->load->view('auth/login');
    }

    public function process_login() {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Username dan Password harus diisi!']);
            return;
        }

        $user = $this->Auth_model->get_user($username);

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan!']);
            return;
        }

        // Viewer SSO biasanya tanpa password lokal
        if (empty($user->password)) {
            echo json_encode(['status' => 'error', 'message' => 'Akun ini hanya bisa login menggunakan Microsoft SSO.']);
            return;
        }

        if (!password_verify($password, $user->password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password salah!']);
            return;
        }

        if ($user->role === 'Viewer') {
            $status_flash = $this->_viewer_status_flash($user);
            if ($status_flash !== null) {
                echo json_encode([
                    'status' => $status_flash['type'] === 'info' ? 'info' : 'error',
                    'message' => $status_flash['message']
                ]);
                return;
            }
        }

        $this->_set_user_session($user);
        $this->Audit_model->log_activity('Login', 'User login ke sistem');

        echo json_encode([
            'status' => 'success',
            'message' => 'Login Berhasil!',
            'redirect' => base_url($this->_home_by_role($user->role))
        ]);
    }

    public function microsoft_login() {
        $this->load->library('microsoft_oauth');

        if (!$this->microsoft_oauth->is_configured()) {
            $this->session->set_flashdata('error', 'Microsoft SSO belum dikonfigurasi. Isi kredensial di file .env (lihat .env.example).');
            redirect('auth');
            return;
        }

        $state = bin2hex(random_bytes(16));
        $this->session->set_userdata('microsoft_oauth_state', $state);
        redirect($this->microsoft_oauth->get_authorize_url($state));
    }

    public function microsoft_callback() {
        $this->load->library('microsoft_oauth');

        $state = $this->input->get('state');
        $saved_state = $this->session->userdata('microsoft_oauth_state');
        $this->session->unset_userdata('microsoft_oauth_state');

        if (!$state || !$saved_state || !hash_equals((string) $saved_state, (string) $state)) {
            $this->session->set_flashdata('error', 'Validasi keamanan login Microsoft gagal. Silakan coba lagi.');
            redirect('auth');
            return;
        }

        if ($this->input->get('error')) {
            $desc = $this->input->get('error_description');
            $this->session->set_flashdata('error', $desc ? $desc : 'Login Microsoft dibatalkan.');
            redirect('auth');
            return;
        }

        $code = $this->input->get('code');
        if (!$code) {
            $this->session->set_flashdata('error', 'Kode otorisasi Microsoft tidak ditemukan.');
            redirect('auth');
            return;
        }

        try {
            $token = $this->microsoft_oauth->exchange_code($code);
            $profile = $this->microsoft_oauth->get_profile($token['access_token']);
        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('auth');
            return;
        }

        $email = '';
        if (!empty($profile['mail'])) {
            $email = $profile['mail'];
        } elseif (!empty($profile['userPrincipalName'])) {
            $email = $profile['userPrincipalName'];
        }
        $email = strtolower(trim($email));
        $display_name = !empty($profile['displayName']) ? $profile['displayName'] : $email;

        if ($email === '') {
            $this->session->set_flashdata('error', 'Email Microsoft tidak ditemukan pada akun Anda.');
            redirect('auth');
            return;
        }

        $this->config->load('microsoft', TRUE);
        $allowed_domain = strtolower((string) $this->config->item('microsoft_allowed_domain', 'microsoft'));
        if ($allowed_domain === '') {
            $allowed_domain = 'itpln.ac.id';
        }

        $email_domain = substr(strrchr($email, '@'), 1);
        if ($email_domain !== $allowed_domain) {
            $this->session->set_flashdata('error', 'Hanya akun @' . $allowed_domain . ' yang diizinkan login SSO.');
            redirect('auth');
            return;
        }

        // Khusus Viewer: daftar otomatis status Menunggu, atau login jika Aktif
        $user = $this->Auth_model->get_user_by_email($email);
        if (!$user) {
            $user = $this->Auth_model->create_pending_viewer($email, $display_name);
            $this->session->set_flashdata(
                'info',
                'Pendaftaran SSO berhasil. Akun Anda menunggu verifikasi Admin/BAAK sebelum dapat masuk.'
            );
            redirect('auth');
            return;
        }

        // Akun non-Viewer (admin dll) yang punya email: izinkan SSO jika Aktif
        if ($user->role !== 'Viewer') {
            if (isset($user->status) && $user->status !== 'Aktif') {
                $this->session->set_flashdata('error', 'Akun Anda tidak aktif.');
                redirect('auth');
                return;
            }
            $this->_set_user_session($user);
            $this->Audit_model->log_activity('Login', 'User login via Microsoft SSO');
            redirect($this->_home_by_role($user->role));
            return;
        }

        $status_flash = $this->_viewer_status_flash($user);
        if ($status_flash !== null) {
            $this->session->set_flashdata($status_flash['type'], $status_flash['message']);
            redirect('auth');
            return;
        }

        $this->_set_user_session($user);
        $this->Audit_model->log_activity('Login', 'User login via Microsoft SSO');
        redirect($this->_home_by_role($user->role));
    }

    public function logout() {
        $this->Audit_model->log_activity('Logout', 'User logout dari sistem');
        $this->session->sess_destroy();
        redirect('auth');
    }

    private function _viewer_status_message($user) {
        $flash = $this->_viewer_status_flash($user);
        return $flash ? $flash['message'] : null;
    }

    private function _viewer_status_flash($user) {
        $status = isset($user->status) ? $user->status : 'Aktif';
        if ($status === 'Menunggu') {
            return array(
                'type' => 'info',
                'message' => 'Akun Viewer Anda masih menunggu verifikasi Admin/BAAK.'
            );
        }
        if ($status === 'Ditolak') {
            return array(
                'type' => 'error',
                'message' => 'Akun Viewer Anda ditolak. Hubungi Admin/BAAK.'
            );
        }
        if ($status !== 'Aktif') {
            return array(
                'type' => 'error',
                'message' => 'Akun Viewer Anda tidak aktif.'
            );
        }
        return null;
    }

    private function _set_user_session($user) {
        $id_dosen = null;
        $nama_dosen = null;

        if ($user->role === 'Viewer' && !empty($user->id_dosen)) {
            $id_dosen = $user->id_dosen;
            $dosen = $this->Auth_model->get_dosen_by_id($user->id_dosen);
            if ($dosen) {
                $nama_dosen = $dosen->nama;
            }
        }

        $this->session->set_userdata(array(
            'id_user'      => $user->id_user,
            'username'     => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'email'        => isset($user->email) ? $user->email : null,
            'role'         => $user->role,
            'status'       => isset($user->status) ? $user->status : 'Aktif',
            'id_fakultas'  => $user->id_fakultas,
            'id_prodi'     => $user->id_prodi,
            'id_dosen'     => $id_dosen,
            'nama_dosen'   => $nama_dosen,
            'logged_in'    => TRUE
        ));
    }

    private function _home_by_role($role) {
        return ($role === 'Viewer') ? 'monitoring' : 'dashboard';
    }
}

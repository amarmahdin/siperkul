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
            redirect('dashboard');
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

        if ($user) {
            if (password_verify($password, $user->password)) {
                $session_data = array(
                    'id_user'      => $user->id_user,
                    'username'     => $user->username,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role'         => $user->role,
                    'id_fakultas'  => $user->id_fakultas,
                    'id_prodi'     => $user->id_prodi,
                    'logged_in'    => TRUE
                );
                $this->session->set_userdata($session_data);
                
                $this->Audit_model->log_activity('Login', 'User login ke sistem');

                echo json_encode(['status' => 'success', 'message' => 'Login Berhasil!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Password salah!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan!']);
        }
    }

    public function logout() {
        $this->Audit_model->log_activity('Logout', 'User logout dari sistem');
        $this->session->sess_destroy();
        redirect('auth');
    }
}

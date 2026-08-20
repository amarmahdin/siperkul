<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Verifikasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if (!in_array($this->session->userdata('role'), array('Administrator', 'BAAK'), true)) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Verifikasi_model');
        $this->load->model('Audit_model');
    }

    public function index() {
        $data['title'] = 'Verifikasi Akun Viewer';
        $data['pending'] = $this->Verifikasi_model->get_pending();
        $data['viewers'] = $this->Verifikasi_model->get_all_viewer();
        $data['dosen'] = $this->db->order_by('nama', 'ASC')->get('tb_dosen')->result();
        $data['jml_pending'] = $this->Verifikasi_model->count_pending();
        $data['page_js'] = $this->_page_js();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('verifikasi/index', $data);
        $this->load->view('templates/footer', $data);
    }

    private function _page_js() {
        return <<<'JS'
$(function () {
    // Select2 untuk .select-dosen diinisialisasi global via app.js

    $(document).on('click', '.btn-approve', function () {
        var row = $(this).closest('tr');
        var id_user = row.data('id');
        var id_dosen = row.find('.select-dosen').val();

        if (!id_dosen) {
            Swal.fire('Perhatian', 'Pilih dosen yang akan dihubungkan terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Aktifkan akun ini?',
            text: 'Viewer akan terhubung ke dosen terpilih dan bisa melihat jadwalnya.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Acc'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post(base_url + 'verifikasi/approve', {
                id_user: id_user,
                id_dosen: id_dosen
            }, function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil', res.message, 'success').then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            });
        });
    });

    $(document).on('click', '.btn-reject', function () {
        var id_user = $(this).closest('tr').data('id');

        Swal.fire({
            title: 'Tolak akun ini?',
            text: 'Akun tidak diizinkan untuk login.', 
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post(base_url + 'verifikasi/reject', {
                id_user: id_user
            }, function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil', res.message, 'success').then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
            });
        });
    });
});
JS;
    }

    public function approve() {
        $id_user = (int) $this->input->post('id_user');
        $id_dosen = (int) $this->input->post('id_dosen');

        if (!$id_user || !$id_dosen) {
            echo json_encode(array('status' => 'error', 'message' => 'Pilih dosen yang akan dihubungkan.'));
            return;
        }

        $user = $this->db->get_where('tb_users', array('id_user' => $id_user, 'role' => 'Viewer'))->row();
        if (!$user || $user->status !== 'Menunggu') {
            echo json_encode(array('status' => 'error', 'message' => 'Akun tidak valid atau sudah diproses.'));
            return;
        }

        $dosen = $this->db->get_where('tb_dosen', array('id_dosen' => $id_dosen))->row();
        if (!$dosen) {
            echo json_encode(array('status' => 'error', 'message' => 'Data dosen tidak ditemukan.'));
            return;
        }

        $this->Verifikasi_model->approve($id_user, $id_dosen);
        $this->Audit_model->log_activity('Verifikasi Viewer', 'Acc ' . $user->email . ' -> dosen ' . $dosen->kode_dosen);

        echo json_encode(array('status' => 'success', 'message' => 'Akun Viewer diterima.'));
    }

    public function reject() {
        $id_user = (int) $this->input->post('id_user');
        $user = $this->db->get_where('tb_users', array('id_user' => $id_user, 'role' => 'Viewer'))->row();
        if (!$user || $user->status !== 'Menunggu') {
            echo json_encode(array('status' => 'error', 'message' => 'Akun tidak valid atau sudah diproses.'));
            return;
        }

        $this->Verifikasi_model->reject($id_user);
        $this->Audit_model->log_activity('Verifikasi Viewer', 'Tolak & hapus antrian ' . $user->email);

        echo json_encode(array(
            'status' => 'success',
            'message' => 'Akun ditolak'
        ));
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('Dashboard_model');
    }

    public function index() {
        $data['title'] = 'Dashboard';
        $is_viewer = $this->session->userdata('role') === 'Viewer';
        $id_dosen = null;

        if ($is_viewer) {
            $id_dosen = $this->session->userdata('id_dosen') ? (int) $this->session->userdata('id_dosen') : 0;
            $data['jml_fakultas'] = $this->Dashboard_model->count_fakultas_dosen($id_dosen);
            $data['jml_prodi']    = $this->Dashboard_model->count_distinct_from_jadwal('id_prodi', $id_dosen);
            $data['jml_mk']       = $this->Dashboard_model->count_distinct_from_jadwal('id_mk', $id_dosen);
            $data['jml_dosen']    = $id_dosen ? 1 : 0;
            $data['jml_ruangan']  = $this->Dashboard_model->count_distinct_from_jadwal('id_ruangan', $id_dosen);
            $data['jml_jadwal']   = $this->Dashboard_model->count_jadwal($id_dosen);
        } else {
            $data['jml_fakultas'] = $this->Dashboard_model->count_table('tb_fakultas');
            $data['jml_prodi']    = $this->Dashboard_model->count_table('tb_prodi');
            $data['jml_mk']       = $this->Dashboard_model->count_table('tb_mata_kuliah');
            $data['jml_dosen']    = $this->Dashboard_model->count_table('tb_dosen');
            $data['jml_ruangan']  = $this->Dashboard_model->count_table('tb_ruangan');
            $data['jml_jadwal']   = $this->Dashboard_model->count_jadwal();
        }

        $data['is_viewer'] = $is_viewer;
        $data['jml_bentrok']  = 0;
        $data['jml_kosong']   = 0;

        $jadwal_per_hari = $this->Dashboard_model->get_jadwal_per_hari($id_dosen);
        $hari_labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $hari_data = [0, 0, 0, 0, 0];

        foreach ($jadwal_per_hari as $row) {
            $index = array_search($row['hari'], $hari_labels);
            if ($index !== false) {
                $hari_data[$index] = (int) $row['total'];
            }
        }
        $data['chart_hari_labels'] = json_encode($hari_labels);
        $data['chart_hari_data'] = json_encode($hari_data);

        $jadwal_per_fakultas = $this->Dashboard_model->get_jadwal_per_fakultas($id_dosen);
        $fakultas_labels = [];
        $fakultas_data = [];
        $fakultas_colors = ['#003E7E', '#FDB813', '#28a745', '#dc3545', '#17a2b8', '#fd7e14'];
        $colors = [];

        foreach ($jadwal_per_fakultas as $index => $row) {
            $fakultas_labels[] = $row['kode_fakultas'];
            $fakultas_data[] = (int) $row['total'];
            $colors[] = $fakultas_colors[$index % count($fakultas_colors)];
        }

        if (empty($fakultas_labels)) {
            $data['chart_fakultas_labels'] = json_encode(['Belum ada data']);
            $data['chart_fakultas_data'] = json_encode([1]);
            $data['chart_fakultas_colors'] = json_encode(['#cccccc']);
        } else {
            $data['chart_fakultas_labels'] = json_encode($fakultas_labels);
            $data['chart_fakultas_data'] = json_encode($fakultas_data);
            $data['chart_fakultas_colors'] = json_encode($colors);
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if ($this->session->userdata('role') !== 'Administrator') {
            show_error('Hanya Administrator yang memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Audit_model');
    }

    public function index() {
        $data['title'] = 'Audit Trail';
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('audit/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Audit_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = date('d/m/Y H:i:s', strtotime($field->tanggal));
            $row[] = $field->nama_lengkap . ' <br><small class="text-muted">(' . $field->role . ')</small>';
            $row[] = $field->aktivitas;
            $row[] = $field->keterangan;
            $row[] = $field->ip_address;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Audit_model->count_all(),
            "recordsFiltered" => $this->Audit_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }
}

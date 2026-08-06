<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if ($this->session->userdata('role') !== 'Administrator') {
            show_error('Hanya Administrator yang memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Pengaturan_model');
    }

    public function index() {
        $data['title'] = 'Sistem Settings (Tahun Akademik)';
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('pengaturan/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Pengaturan_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->tahun_akademik;
            $row[] = $field->semester;
            
            if($field->status == 1) {
                $row[] = '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Aktif</span>';
                $btn = '<button type="button" class="btn btn-sm btn-secondary" disabled title="Sudah Aktif"><i class="fas fa-power-off"></i></button> ';
            } else {
                $row[] = '<span class="badge bg-secondary">Non-Aktif</span>';
                $btn = '<button type="button" class="btn btn-sm btn-success btn-activate" data-id="'.$field->id_ta.'" title="Set Aktif"><i class="fas fa-power-off"></i> Set Aktif</button> ';
            }
            
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_ta.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Pengaturan_model->count_all(),
            "recordsFiltered" => $this->Pengaturan_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function save() {
        $this->form_validation->set_rules('tahun_akademik', 'Tahun Akademik', 'required');
        $this->form_validation->set_rules('semester', 'Semester', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'tahun_akademik' => $this->input->post('tahun_akademik'),
            'semester' => $this->input->post('semester'),
            'status' => 0 // Default non-aktif, harus diset manual
        );

        $this->Pengaturan_model->save($data);
        echo json_encode(['status' => 'success', 'message' => 'Data Tahun Akademik berhasil disimpan']);
    }

    public function set_aktif($id) {
        // Set all to 0
        $this->db->update('tb_tahun_akademik', ['status' => 0]);
        
        // Set selected to 1
        $this->db->where('id_ta', $id);
        $this->db->update('tb_tahun_akademik', ['status' => 1]);
        
        echo json_encode(['status' => 'success', 'message' => 'Tahun Akademik berhasil diaktifkan.']);
    }

    public function delete($id) {
        $this->Pengaturan_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

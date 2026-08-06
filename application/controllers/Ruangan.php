<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ruangan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Ruangan_model');
    }

    public function index() {
        $data['title'] = 'Data Ruangan';
        
        $data['gedung'] = $this->db->get('tb_gedung')->result();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('ruangan/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Ruangan_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->kode_ruangan;
            $row[] = $field->nama_ruangan;
            $row[] = $field->nama_gedung;
            $row[] = 'Lantai ' . $field->lantai;
            $row[] = $field->kapasitas_kuliah;
            $row[] = $field->kapasitas_ujian;
            
            if($field->status == 'Aktif') {
                $row[] = '<span class="badge bg-success">Aktif</span>';
            } else {
                $row[] = '<span class="badge bg-danger">Non-Aktif</span>';
            }
            
            $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_ruangan.'" title="Edit"><i class="fas fa-edit"></i></button> ';
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_ruangan.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Ruangan_model->count_all(),
            "recordsFiltered" => $this->Ruangan_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Ruangan_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        $this->form_validation->set_rules('kode_ruangan', 'Kode Ruangan', 'required');
        $this->form_validation->set_rules('nama_ruangan', 'Nama Ruangan', 'required');
        $this->form_validation->set_rules('id_gedung', 'Gedung', 'required');
        $this->form_validation->set_rules('lantai', 'Lantai', 'required|numeric');
        $this->form_validation->set_rules('nomor_ruang', 'Nomor Ruang', 'required');
        $this->form_validation->set_rules('kapasitas_kuliah', 'Kapasitas Kuliah', 'required|numeric');
        $this->form_validation->set_rules('kapasitas_ujian', 'Kapasitas Ujian', 'required|numeric');
        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'kode_ruangan' => $this->input->post('kode_ruangan'),
            'nama_ruangan' => $this->input->post('nama_ruangan'),
            'id_gedung' => $this->input->post('id_gedung'),
            'lantai' => $this->input->post('lantai'),
            'nomor_ruang' => $this->input->post('nomor_ruang'),
            'kapasitas_kuliah' => $this->input->post('kapasitas_kuliah'),
            'kapasitas_ujian' => $this->input->post('kapasitas_ujian'),
            'status' => $this->input->post('status'),
        );

        if ($this->input->post('id_ruangan')) {
            $this->Ruangan_model->update(array('id_ruangan' => $this->input->post('id_ruangan')), $data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            if($this->db->get_where('tb_ruangan', ['kode_ruangan' => $data['kode_ruangan']])->num_rows() > 0){
                 echo json_encode(['status' => 'error', 'message' => 'Kode Ruangan sudah ada!']);
                 return;
            }
            $this->Ruangan_model->save($data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function delete($id) {
        $this->Ruangan_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

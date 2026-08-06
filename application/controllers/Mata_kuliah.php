<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mata_kuliah extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Mata_kuliah_model');
    }

    public function index() {
        $data['title'] = 'Data Mata Kuliah';
        
        $data['prodi'] = $this->db->get('tb_prodi')->result();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('mata_kuliah/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Mata_kuliah_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->kode_mk;
            $row[] = $field->nama_mk;
            $row[] = $field->sks;
            $row[] = $field->semester;
            $row[] = $field->jenis;
            $row[] = $field->nama_prodi;
            
            $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_mk.'" title="Edit"><i class="fas fa-edit"></i></button> ';
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_mk.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Mata_kuliah_model->count_all(),
            "recordsFiltered" => $this->Mata_kuliah_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Mata_kuliah_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        $this->form_validation->set_rules('kode_mk', 'Kode MK', 'required');
        $this->form_validation->set_rules('nama_mk', 'Nama MK', 'required');
        $this->form_validation->set_rules('sks', 'SKS', 'required|numeric');
        $this->form_validation->set_rules('semester', 'Semester', 'required|numeric');
        $this->form_validation->set_rules('jenis', 'Jenis', 'required');
        $this->form_validation->set_rules('id_prodi', 'Prodi', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'kode_mk' => $this->input->post('kode_mk'),
            'nama_mk' => $this->input->post('nama_mk'),
            'sks' => $this->input->post('sks'),
            'semester' => $this->input->post('semester'),
            'jenis' => $this->input->post('jenis'),
            'id_prodi' => $this->input->post('id_prodi'),
        );

        if ($this->input->post('id_mk')) {
            $this->Mata_kuliah_model->update(array('id_mk' => $this->input->post('id_mk')), $data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            if($this->db->get_where('tb_mata_kuliah', ['kode_mk' => $data['kode_mk']])->num_rows() > 0){
                 echo json_encode(['status' => 'error', 'message' => 'Kode MK sudah ada!']);
                 return;
            }
            $this->Mata_kuliah_model->save($data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function delete($id) {
        $this->Mata_kuliah_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

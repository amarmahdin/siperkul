<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Prodi_model');
    }

    public function index() {
        $data['title'] = 'Data Program Studi';
        
        // Fetch for select dropdown
        $this->load->model('Fakultas_model');
        $data['fakultas'] = $this->db->get('tb_fakultas')->result();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('prodi/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Prodi_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->kode_prodi;
            $row[] = $field->nama_prodi;
            $row[] = $field->nama_fakultas;
            
            $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_prodi.'" title="Edit"><i class="fas fa-edit"></i></button> ';
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_prodi.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Prodi_model->count_all(),
            "recordsFiltered" => $this->Prodi_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Prodi_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        $this->form_validation->set_rules('kode_prodi', 'Kode Prodi', 'required');
        $this->form_validation->set_rules('nama_prodi', 'Nama Prodi', 'required');
        $this->form_validation->set_rules('id_fakultas', 'Fakultas', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'kode_prodi' => $this->input->post('kode_prodi'),
            'nama_prodi' => $this->input->post('nama_prodi'),
            'id_fakultas' => $this->input->post('id_fakultas'),
        );

        if ($this->input->post('id_prodi')) {
            $this->Prodi_model->update(array('id_prodi' => $this->input->post('id_prodi')), $data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            if($this->db->get_where('tb_prodi', ['kode_prodi' => $data['kode_prodi']])->num_rows() > 0){
                 echo json_encode(['status' => 'error', 'message' => 'Kode Prodi sudah ada!']);
                 return;
            }
            $this->Prodi_model->save($data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function delete($id) {
        $this->Prodi_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

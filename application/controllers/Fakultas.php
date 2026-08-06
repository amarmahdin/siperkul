<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fakultas extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        // Only Admin and BAAK can access Master Data
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Fakultas_model');
    }

    public function index() {
        $data['title'] = 'Data Fakultas';
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('fakultas/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Fakultas_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->kode_fakultas;
            $row[] = $field->nama_fakultas;
            
            $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_fakultas.'" title="Edit"><i class="fas fa-edit"></i></button> ';
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_fakultas.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Fakultas_model->count_all(),
            "recordsFiltered" => $this->Fakultas_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Fakultas_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        $this->form_validation->set_rules('kode_fakultas', 'Kode Fakultas', 'required');
        $this->form_validation->set_rules('nama_fakultas', 'Nama Fakultas', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'kode_fakultas' => $this->input->post('kode_fakultas'),
            'nama_fakultas' => $this->input->post('nama_fakultas'),
        );

        if ($this->input->post('id_fakultas')) {
            // Update
            $this->Fakultas_model->update(array('id_fakultas' => $this->input->post('id_fakultas')), $data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            // Insert
            // Cek kode
            if($this->db->get_where('tb_fakultas', ['kode_fakultas' => $data['kode_fakultas']])->num_rows() > 0){
                 echo json_encode(['status' => 'error', 'message' => 'Kode Fakultas sudah ada!']);
                 return;
            }
            $this->Fakultas_model->save($data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function delete($id) {
        $this->Fakultas_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

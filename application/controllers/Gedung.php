<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gedung extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Gedung_model');
    }

    public function index() {
        $data['title'] = 'Data Gedung';
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('gedung/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Gedung_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->kode_gedung;
            $row[] = $field->nama_gedung;
            
            $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_gedung.'" title="Edit"><i class="fas fa-edit"></i></button> ';
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_gedung.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Gedung_model->count_all(),
            "recordsFiltered" => $this->Gedung_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Gedung_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        $this->form_validation->set_rules('kode_gedung', 'Kode Gedung', 'required');
        $this->form_validation->set_rules('nama_gedung', 'Nama Gedung', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'kode_gedung' => $this->input->post('kode_gedung'),
            'nama_gedung' => $this->input->post('nama_gedung')
        );

        if ($this->input->post('id_gedung')) {
            $this->Gedung_model->update(array('id_gedung' => $this->input->post('id_gedung')), $data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            if($this->db->get_where('tb_gedung', ['kode_gedung' => $data['kode_gedung']])->num_rows() > 0){
                 echo json_encode(['status' => 'error', 'message' => 'Kode Gedung sudah ada!']);
                 return;
            }
            $this->Gedung_model->save($data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function delete($id) {
        $this->Gedung_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

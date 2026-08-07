<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dosen extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK'])) {
            show_error('Anda tidak memiliki akses ke halaman ini.', 403, 'Akses Ditolak');
        }
        $this->load->model('Dosen_model');
    }

    public function index() {
        $this->_sync_sevima();
        $data['title'] = 'Data Dosen';
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('dosen/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Dosen_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->nidn;
            $row[] = $field->kode_dosen;
            $row[] = $field->nama;
            $row[] = $field->email;
            $row[] = $field->no_hp;
            
            $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_dosen.'" title="Edit"><i class="fas fa-edit"></i></button> ';
            $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_dosen.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Dosen_model->count_all(),
            "recordsFiltered" => $this->Dosen_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Dosen_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        $this->form_validation->set_rules('kode_dosen', 'Kode Dosen', 'required');
        $this->form_validation->set_rules('nama', 'Nama Dosen', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = array(
            'nidn' => $this->input->post('nidn'),
            'kode_dosen' => $this->input->post('kode_dosen'),
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'no_hp' => $this->input->post('no_hp'),
        );

        if ($this->input->post('id_dosen')) {
            $this->Dosen_model->update(array('id_dosen' => $this->input->post('id_dosen')), $data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            if($this->db->get_where('tb_dosen', ['kode_dosen' => $data['kode_dosen']])->num_rows() > 0){
                 echo json_encode(['status' => 'error', 'message' => 'Kode Dosen sudah ada!']);
                 return;
            }
            $this->Dosen_model->save($data);
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan']);
        }
    }

    public function delete($id) {
        $this->Dosen_model->delete_by_id($id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }

    private function _sync_sevima() {
        if ($this->session->userdata('last_sync_dosen') && (time() - $this->session->userdata('last_sync_dosen') < 3600)) {
            return;
        }

        $url = "https://api.sevimaplatform.com/siakadcloud/v1/dosen";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'X-App-Key: 326E047C0915C6F86D875AB85EB48D26',
            'X-Secret-Key: CDBA495093339309249FE2A7C9381DC6C666318D4A21B294BA5DBDB1A9651BF8'
        ));
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode == 200 && $response) {
            $data_sevima = json_decode($response, true);
            if (isset($data_sevima['data']) && is_array($data_sevima['data'])) {
                foreach ($data_sevima['data'] as $item) {
                    $attr = $item['attributes'];
                    $nidn = isset($attr['nidn']) ? trim($attr['nidn']) : '';
                    $nama_asli = isset($attr['nama']) ? trim($attr['nama']) : '';
                    $gelar_depan = isset($attr['gelar_depan']) ? trim($attr['gelar_depan']) : '';
                    $gelar_belakang = isset($attr['gelar_belakang']) ? trim($attr['gelar_belakang']) : '';
                    $email = isset($attr['email']) ? trim($attr['email']) : '';
                    $no_hp = isset($attr['nomor_hp']) ? trim($attr['nomor_hp']) : '';
                    
                    if (empty($nama_asli)) continue;

                    $nama = $nama_asli;
                    if (!empty($gelar_depan)) {
                        $nama = $gelar_depan . ' ' . $nama;
                    }
                    if (!empty($gelar_belakang)) {
                        $nama = $nama . ', ' . $gelar_belakang;
                    }
                    $nama = trim($nama);

                    $words = explode(" ", preg_replace("/[^a-zA-Z\s]/", "", $nama_asli));
                    $initials = "";
                    foreach ($words as $w) {
                        if (!empty($w)) {
                            $initials .= strtoupper($w[0]);
                        }
                    }
                    if (empty($initials)) $initials = "DSN";
                    
                    $kode_dosen = substr($initials, 0, 5); 
                    
                    $this->db->group_start();
                    if (!empty($nidn)) {
                        $this->db->where('nidn', $nidn);
                        $this->db->or_where('nama', $nama);
                    } else {
                        $this->db->where('nama', $nama);
                    }
                    $this->db->group_end();
                    
                    $exist = $this->db->get('tb_dosen')->row();
                    
                    $data_db = array(
                        'nidn' => $nidn,
                        'nama' => $nama,
                        'email' => $email,
                        'no_hp' => $no_hp
                    );

                    if ($exist) {
                        $this->Dosen_model->update(array('id_dosen' => $exist->id_dosen), $data_db);
                    } else {
                        $base_kode = $kode_dosen;
                        $counter = 1;
                        while ($this->db->get_where('tb_dosen', ['kode_dosen' => $kode_dosen])->num_rows() > 0) {
                            $kode_dosen = $base_kode . $counter;
                            $counter++;
                        }
                        
                        $data_db['kode_dosen'] = $kode_dosen;
                        $this->Dosen_model->save($data_db);
                    }
                }
                $this->session->set_userdata('last_sync_dosen', time());
            }
        }
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('Jadwal_model');
        $this->load->model('Audit_model');
    }

    public function index() {
        $data['title'] = 'Jadwal Kuliah';
        
        // Get Active Tahun Akademik
        $ta_aktif = $this->db->get_where('tb_tahun_akademik', ['status' => 1])->row();
        if(!$ta_aktif) {
            show_error('Tahun Akademik Aktif belum diatur oleh Administrator. Silakan hubungi Administrator.', 500, 'Kesalahan Sistem');
        }
        $data['ta_aktif'] = $ta_aktif;

        // Populate dropdowns
        $data['prodi'] = $this->db->get('tb_prodi')->result();
        if ($this->db->field_exists('id_kurikulum', 'tb_mata_kuliah')) {
            $this->db->order_by('id_kurikulum', 'DESC');
        }
        $this->db->order_by('kode_mk', 'ASC');
        $this->db->order_by('nama_mk', 'ASC');
        $data['mata_kuliah'] = $this->db->get('tb_mata_kuliah')->result();
        $data['dosen'] = $this->db->get('tb_dosen')->result();
        // Only active ruangan
        $data['ruangan'] = $this->db->get_where('tb_ruangan', ['status' => 'Aktif'])->result();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('jadwal/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_data() {
        $list = $this->Jadwal_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->nama_prodi;
            $mk_label = $field->kode_mk . ' - ' . $field->nama_mk;
            if (!empty($field->id_kurikulum)) {
                $mk_label .= ' <small class="text-muted">[Kurikulum ' . htmlspecialchars($field->id_kurikulum) . ']</small>';
            }
            $row[] = $mk_label;
            $row[] = $field->kelas;
            $row[] = $field->nama_dosen;
            $row[] = $field->hari . '<br><small>' . date('H:i', strtotime($field->jam_mulai)) . ' - ' . date('H:i', strtotime($field->jam_selesai)) . '</small>';
            $row[] = $field->nama_ruangan . '<br><small>Kap: ' . $field->kapasitas_kuliah . '</small>';
            $row[] = $field->kapasitas_mhs;
            
            $btn = '';
            if(in_array($this->session->userdata('role'), ['Administrator', 'BAAK', 'Operator Prodi'])) {
                $btn = '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$field->id_jadwal.'" title="Edit"><i class="fas fa-edit"></i></button> ';
                $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$field->id_jadwal.'" title="Hapus"><i class="fas fa-trash"></i></button>';
            }
            
            $row[] = $btn;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Jadwal_model->count_all(),
            "recordsFiltered" => $this->Jadwal_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function get_by_id($id) {
        $data = $this->Jadwal_model->get_by_id($id);
        echo json_encode($data);
    }

    public function save() {
        if(!in_array($this->session->userdata('role'), ['Administrator', 'BAAK', 'Operator Prodi'])) {
            echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengubah data ini.']);
            return;
        }

        $this->form_validation->set_rules('id_prodi', 'Prodi', 'required');
        $this->form_validation->set_rules('id_mk', 'Mata Kuliah', 'required');
        $this->form_validation->set_rules('kelas', 'Kelas', 'required');
        $this->form_validation->set_rules('id_dosen', 'Dosen', 'required');
        $this->form_validation->set_rules('hari', 'Hari', 'required');
        $this->form_validation->set_rules('jam_mulai', 'Jam Mulai', 'required');
        $this->form_validation->set_rules('jam_selesai', 'Jam Selesai', 'required');
        $this->form_validation->set_rules('id_ruangan', 'Ruangan', 'required');
        $this->form_validation->set_rules('kapasitas_mhs', 'Kapasitas Mahasiswa', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        // Get Active TA
        $ta_aktif = $this->db->get_where('tb_tahun_akademik', ['status' => 1])->row();
        if(!$ta_aktif) {
            echo json_encode(['status' => 'error', 'message' => 'Tahun Akademik aktif tidak ditemukan.']);
            return;
        }

        $id_jadwal = $this->input->post('id_jadwal');
        $id_prodi = $this->input->post('id_prodi');
        $id_mk = $this->input->post('id_mk');
        $kelas = $this->input->post('kelas');
        $id_dosen = $this->input->post('id_dosen');
        $hari = $this->input->post('hari');
        $jam_mulai = $this->input->post('jam_mulai');
        $jam_selesai = $this->input->post('jam_selesai');
        $id_ruangan = $this->input->post('id_ruangan');
        $kapasitas_mhs = $this->input->post('kapasitas_mhs');

        // CLASH VALIDATION
        // 1. Bentrok Waktu & Ruangan
        $cek_ruang = $this->Jadwal_model->cek_bentrok_ruang($id_ruangan, $hari, $jam_mulai, $jam_selesai, $ta_aktif->id_ta, $id_jadwal);
        if($cek_ruang) {
            echo json_encode(['status' => 'error', 'message' => 'RUANGAN BENTROK! Ruangan ini sudah digunakan pada hari dan jam tersebut.']);
            return;
        }

        // 2. Bentrok Dosen
        $cek_dosen = $this->Jadwal_model->cek_bentrok_dosen($id_dosen, $hari, $jam_mulai, $jam_selesai, $ta_aktif->id_ta, $id_jadwal);
        if($cek_dosen) {
            echo json_encode(['status' => 'error', 'message' => 'DOSEN BENTROK! Dosen ini sudah ada jadwal mengajar di tempat lain pada jam tersebut.']);
            return;
        }

        // 3. Bentrok Kelas
        $cek_kelas = $this->Jadwal_model->cek_bentrok_kelas($id_prodi, $kelas, $hari, $jam_mulai, $jam_selesai, $ta_aktif->id_ta, $id_jadwal);
        if($cek_kelas) {
            echo json_encode(['status' => 'error', 'message' => 'KELAS BENTROK! Kelas ini sudah memiliki jadwal pada waktu tersebut.']);
            return;
        }

        // 4. Kapasitas Checking (Warning only, handled in frontend if possible, but here we just accept it, or we could return a specific status. For simplicity, we just save but maybe audit log it)
        $ruang = $this->db->get_where('tb_ruangan', ['id_ruangan' => $id_ruangan])->row();
        $is_over_capacity = false;
        if($ruang && $kapasitas_mhs > $ruang->kapasitas_kuliah) {
            $is_over_capacity = true;
        }

        $data = array(
            'id_prodi'     => $id_prodi,
            'id_mk'        => $id_mk,
            'kelas'        => $kelas,
            'id_dosen'     => $id_dosen,
            'hari'         => $hari,
            'jam_mulai'    => $jam_mulai,
            'jam_selesai'  => $jam_selesai,
            'id_ruangan'   => $id_ruangan,
            'kapasitas_mhs'=> $kapasitas_mhs,
            'id_ta'        => $ta_aktif->id_ta,
            'status'       => 'Aktif',
            'created_by'   => $this->session->userdata('id_user'),
        );

        if ($id_jadwal) {
            $this->Jadwal_model->update(array('id_jadwal' => $id_jadwal), $data);
            $this->Audit_model->log_activity('Update Jadwal', "Memperbarui jadwal ID $id_jadwal");
            $msg = $is_over_capacity ? 'Data diupdate. PERINGATAN: Kapasitas Mhs melebihi Kapasitas Ruang!' : 'Data berhasil diupdate';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            $new_id = $this->Jadwal_model->save($data);
            $this->Audit_model->log_activity('Tambah Jadwal', "Menambah jadwal baru ID $new_id");
            $msg = $is_over_capacity ? 'Data disimpan. PERINGATAN: Kapasitas Mhs melebihi Kapasitas Ruang!' : 'Data berhasil disimpan';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        }
    }

    public function delete($id) {
        if(!in_array($this->session->userdata('role'), ['Administrator', 'BAAK', 'Operator Prodi'])) {
            echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses.']);
            return;
        }
        $this->Jadwal_model->delete_by_id($id);
        $this->Audit_model->log_activity('Hapus Jadwal', "Menghapus jadwal ID $id");
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
    }
}

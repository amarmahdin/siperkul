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
        $data['title'] = 'Data Dosen';
        $data['sync_message'] = $this->_sync_sevima();
        
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

    /**
     * Ambil semua halaman dari API Sevima, simpan ke tb_dosen.
     * Tampilan tetap pakai pagination DataTables (server-side).
     * Contact fields (NIDN / email / no HP) hanya diisi jika API punya nilai.
     */
    private function _sync_sevima() {
        $force = $this->input->get('force_sync') === '1';
        // Key v2: sync ulang contact fields (email_kampus / telepon fallback)
        if (!$force && $this->session->userdata('last_sync_dosen_contact_v2') && (time() - $this->session->userdata('last_sync_dosen_contact_v2') < 3600)) {
            return null;
        }

        @set_time_limit(300);

        $page = 1;
        $page_size = 100;
        $last_page = null;
        $synced_any = false;
        $total_synced = 0;
        $contact_updated = 0;
        $api_error = null;

        while ($page <= 500) {
            // Sevima hanya menerima param `page` (bukan per_page / page[size])
            $url = 'https://api.sevimaplatform.com/siakadcloud/v1/dosen?' . http_build_query(array(
                'page' => $page,
            ));

            $result = $this->_sevima_get($url);
            if (!$result['ok']) {
                $api_error = $result['error'];
                break;
            }

            $payload = $result['data'];
            $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : array();
            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                if ($this->_upsert_dosen_from_sevima($item)) {
                    $contact_updated++;
                }
                $total_synced++;
            }
            $synced_any = true;

            $meta = isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : array();
            if (isset($meta['last_page'])) {
                $last_page = (int) $meta['last_page'];
            } elseif (isset($meta['page']['lastPage'])) {
                $last_page = (int) $meta['page']['lastPage'];
            }
            if (isset($meta['per_page'])) {
                $page_size = (int) $meta['per_page'];
            }

            if ($last_page !== null && $page >= $last_page) {
                break;
            }
            if (isset($payload['links']['next']) && empty($payload['links']['next'])) {
                break;
            }
            if ($last_page === null && count($items) < $page_size) {
                break;
            }

            $page++;
            usleep(150000);
        }

        if ($synced_any) {
            $this->session->set_userdata('last_sync_dosen_contact_v2', time());
            return array(
                'type' => 'success',
                'text' => 'Sinkronisasi Sevima berhasil (' . $total_synced . ' baris, ' . $contact_updated . ' kontak diperbarui dari ' . $page . ' halaman).',
            );
        }

        if ($api_error) {
            return array(
                'type' => 'danger',
                'text' => 'Gagal sync API Sevima: ' . $api_error,
            );
        }

        return null;
    }

    private function _sevima_get($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // XAMPP sering belum punya CA bundle lengkap
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'X-App-Key: 326E047C0915C6F86D875AB85EB48D26',
            'X-Secret-Key: CDBA495093339309249FE2A7C9381DC6C666318D4A21B294BA5DBDB1A9651BF8'
        ));

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($curl_err) {
            return array('ok' => false, 'error' => $curl_err, 'data' => null);
        }

        $decoded = json_decode($response, true);

        if ($httpcode != 200) {
            $detail = 'HTTP ' . $httpcode;
            if (isset($decoded['errors']['detail'])) {
                $detail = $decoded['errors']['detail'];
            } elseif (isset($decoded['message'])) {
                $detail = $decoded['message'];
            }
            return array('ok' => false, 'error' => $detail, 'data' => null);
        }

        if (!is_array($decoded)) {
            return array('ok' => false, 'error' => 'Respons API tidak valid', 'data' => null);
        }

        return array('ok' => true, 'error' => null, 'data' => $decoded);
    }

    /**
     * Upsert dosen dari Sevima.
     * Untuk baris yang sudah ada: hanya isi nidn/email/no_hp jika API punya nilai (tidak menimpa kosong).
     * @return bool true jika kontak diperbarui atau baris baru disimpan
     */
    private function _upsert_dosen_from_sevima($item) {
        if (!isset($item['attributes']) || !is_array($item['attributes'])) {
            return false;
        }

        $attr = $item['attributes'];
        $nidn = isset($attr['nidn']) ? trim((string) $attr['nidn']) : '';
        $nama_asli = isset($attr['nama']) ? trim((string) $attr['nama']) : '';
        $gelar_depan = isset($attr['gelar_depan']) ? trim((string) $attr['gelar_depan']) : '';
        $gelar_belakang = isset($attr['gelar_belakang']) ? trim((string) $attr['gelar_belakang']) : '';

        // Prioritas kontak: kampus dulu, lalu pribadi / alternatif
        $email = '';
        if (!empty($attr['email_kampus']) && trim((string) $attr['email_kampus']) !== '') {
            $email = trim((string) $attr['email_kampus']);
        } elseif (!empty($attr['email']) && trim((string) $attr['email']) !== '') {
            $email = trim((string) $attr['email']);
        }

        $no_hp = '';
        if (!empty($attr['nomor_hp']) && trim((string) $attr['nomor_hp']) !== '') {
            $no_hp = trim((string) $attr['nomor_hp']);
        } elseif (!empty($attr['telepon']) && trim((string) $attr['telepon']) !== '') {
            $no_hp = trim((string) $attr['telepon']);
        } elseif (!empty($attr['telepon_alternatif']) && trim((string) $attr['telepon_alternatif']) !== '') {
            $no_hp = trim((string) $attr['telepon_alternatif']);
        }

        if ($nama_asli === '') {
            return false;
        }

        $nama = $nama_asli;
        if ($gelar_depan !== '') {
            $nama = $gelar_depan . ' ' . $nama;
        }
        if ($gelar_belakang !== '') {
            $nama = $nama . ', ' . $gelar_belakang;
        }
        $nama = trim($nama);

        $exist = null;
        if ($nidn !== '') {
            $exist = $this->db->get_where('tb_dosen', array('nidn' => $nidn))->row();
        }
        if (!$exist) {
            $exist = $this->db->get_where('tb_dosen', array('nama' => $nama))->row();
        }
        if (!$exist && $nama_asli !== $nama) {
            $exist = $this->db->get_where('tb_dosen', array('nama' => $nama_asli))->row();
        }

        // Hanya field kontak yang tersedia di API
        $contact = array();
        if ($nidn !== '') {
            $contact['nidn'] = $nidn;
        }
        if ($email !== '') {
            $contact['email'] = $email;
        }
        if ($no_hp !== '') {
            $contact['no_hp'] = $no_hp;
        }

        if ($exist) {
            if (empty($contact)) {
                return false;
            }
            // Jangan timpa nilai lokal yang sudah terisi dengan yang sama; tetap update jika API punya data
            $this->Dosen_model->update(array('id_dosen' => $exist->id_dosen), $contact);
            return true;
        }

        // Dosen baru dari API (nama belum ada lokal)
        $words = explode(' ', preg_replace('/[^a-zA-Z\s]/', '', $nama_asli));
        $initials = '';
        foreach ($words as $w) {
            if ($w !== '') {
                $initials .= strtoupper($w[0]);
            }
        }
        if ($initials === '') {
            $initials = 'DSN';
        }

        $kode_dosen = substr($initials, 0, 5);
        $base_kode = $kode_dosen;
        $counter = 1;
        while ($this->db->get_where('tb_dosen', array('kode_dosen' => $kode_dosen))->num_rows() > 0) {
            $kode_dosen = $base_kode . $counter;
            $counter++;
        }

        $data_db = array_merge(array(
            'nama' => $nama,
            'kode_dosen' => $kode_dosen,
            'nidn' => $nidn !== '' ? $nidn : null,
            'email' => $email !== '' ? $email : null,
            'no_hp' => $no_hp !== '' ? $no_hp : null,
        ), $contact);

        $this->Dosen_model->save($data_db);
        return true;
    }
}

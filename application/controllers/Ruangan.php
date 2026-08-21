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
        $data['gedung'] = $this->db->order_by('nama_gedung', 'ASC')->get('tb_gedung')->result();
        $data['sync_message'] = $this->_sync_sevima();

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
            $row[] = !empty($field->lokasi_ruang) ? $field->lokasi_ruang : '-';
            $row[] = 'Lantai ' . $field->lantai;
            $row[] = $field->kapasitas_kuliah;
            $row[] = $field->kapasitas_ujian;

            if ($field->status == 'Aktif') {
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
            if ($this->db->get_where('tb_ruangan', ['kode_ruangan' => $data['kode_ruangan']])->num_rows() > 0) {
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

    /**
     * Sync semua halaman API Sevima /ruang (master ruang, tanpa tahun ajaran).
     */
    private function _sync_sevima() {
        $force = $this->input->get('force_sync') === '1';
        if (!$force && $this->session->userdata('last_sync_ruangan_v1') && (time() - $this->session->userdata('last_sync_ruangan_v1') < 3600)) {
            return null;
        }

        @set_time_limit(300);
        $this->_ensure_ruangan_sevima_columns();

        $page = 1;
        $page_size = 100;
        $last_page = null;
        $synced_any = false;
        $total_synced = 0;
        $api_error = null;
        $api_total = null;

        while ($page <= 500) {
            $url = 'https://api.sevimaplatform.com/siakadcloud/v1/ruang?' . http_build_query(array(
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

            $meta = isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : array();
            if ($api_total === null && isset($meta['total'])) {
                $api_total = (int) $meta['total'];
            }

            foreach ($items as $item) {
                if ($this->_upsert_ruangan_from_sevima($item)) {
                    $total_synced++;
                }
            }
            $synced_any = true;

            if (isset($meta['last_page'])) {
                $last_page = (int) $meta['last_page'];
            }
            if (isset($meta['per_page'])) {
                $page_size = (int) $meta['per_page'];
            }

            if ($last_page !== null && $page >= $last_page) {
                break;
            }
            if ($last_page === null && count($items) < $page_size) {
                break;
            }

            $page++;
            usleep(150000);
        }

        if ($synced_any) {
            $this->session->set_userdata('last_sync_ruangan_v1', time());
            $extra = $api_total !== null ? ' / total API ' . $api_total : '';
            return array(
                'type' => 'success',
                'text' => 'Sinkronisasi Sevima Ruangan berhasil (' . $total_synced . ' baris dari ' . $page . ' halaman' . $extra . ').',
            );
        }

        if ($api_error) {
            return array(
                'type' => 'danger',
                'text' => 'Gagal sync API Sevima Ruangan: ' . $api_error,
            );
        }

        return null;
    }

    private function _ensure_ruangan_sevima_columns() {
        if (!$this->db->field_exists('id_sevima', 'tb_ruangan')) {
            $this->db->query("ALTER TABLE `tb_ruangan` ADD `id_sevima` varchar(50) DEFAULT NULL AFTER `id_ruangan`");
            $this->db->query("ALTER TABLE `tb_ruangan` ADD UNIQUE KEY `id_sevima` (`id_sevima`)");
        }
        if (!$this->db->field_exists('lokasi_ruang', 'tb_ruangan')) {
            $this->db->query("ALTER TABLE `tb_ruangan` ADD `lokasi_ruang` varchar(255) DEFAULT NULL AFTER `nama_ruangan`");
        }
    }

    private function _sevima_get($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'X-App-Key: 326E047C0915C6F86D875AB85EB48D26',
            'X-Secret-Key: CDBA495093339309249FE2A7C9381DC6C666318D4A21B294BA5DBDB1A9651BF8',
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

    private function _upsert_ruangan_from_sevima($item) {
        if (!isset($item['attributes']) || !is_array($item['attributes'])) {
            return false;
        }

        $attr = $item['attributes'];
        if (isset($attr['is_deleted']) && (string) $attr['is_deleted'] === '1') {
            return false;
        }

        $id_sevima = '';
        if (!empty($attr['id_ruang'])) {
            $id_sevima = trim((string) $attr['id_ruang']);
        } elseif (!empty($item['id'])) {
            $id_sevima = trim((string) $item['id']);
        }

        $nama = isset($attr['nama_ruang']) ? trim((string) $attr['nama_ruang']) : '';
        if ($id_sevima === '' || $nama === '') {
            return false;
        }

        $lokasi = isset($attr['lokasi_ruang']) ? trim((string) $attr['lokasi_ruang']) : '';
        $lantai = isset($attr['lantai']) && $attr['lantai'] !== '' ? (int) $attr['lantai'] : 1;
        $kuota = isset($attr['kuota_ruang']) && $attr['kuota_ruang'] !== '' ? (int) $attr['kuota_ruang'] : 0;
        $is_aktif = !isset($attr['is_aktif']) || (string) $attr['is_aktif'] === '1';

        $kode = 'R-' . $id_sevima;
        $nomor = $id_sevima;
        if (preg_match('/(\d+)\s*$/', $nama, $m)) {
            $nomor = $m[1];
        }

        $id_gedung = $this->_resolve_gedung_id($lokasi, $attr);

        $data_db = array(
            'id_sevima' => $id_sevima,
            'id_gedung' => $id_gedung,
            'kode_ruangan' => $kode,
            'nama_ruangan' => $nama,
            'lokasi_ruang' => $lokasi !== '' ? $lokasi : null,
            'lantai' => $lantai > 0 ? $lantai : 1,
            'nomor_ruang' => substr($nomor, 0, 10),
            'kapasitas_kuliah' => $kuota,
            'kapasitas_ujian' => $kuota > 0 ? max(1, (int) floor($kuota / 2)) : 0,
            'status' => $is_aktif ? 'Aktif' : 'Non-Aktif',
        );

        $exist = $this->db->get_where('tb_ruangan', array('id_sevima' => $id_sevima))->row();
        if (!$exist) {
            $exist = $this->db->get_where('tb_ruangan', array('kode_ruangan' => $kode))->row();
        }

        if ($exist) {
            $this->Ruangan_model->update(array('id_ruangan' => $exist->id_ruangan), $data_db);
        } else {
            $this->Ruangan_model->save($data_db);
        }

        return true;
    }

    /**
     * Petakan lokasi API ke tb_gedung lokal (API sering kosongkan nama_gedung).
     */
    private function _resolve_gedung_id($lokasi, $attr) {
        $nama_api = '';
        if (!empty($attr['nama_gedung'])) {
            $nama_api = trim((string) $attr['nama_gedung']);
        } elseif (!empty($attr['nama_kampus'])) {
            $nama_api = trim((string) $attr['nama_kampus']);
        }

        if ($nama_api !== '') {
            $row = $this->db->like('nama_gedung', $nama_api)->get('tb_gedung')->row();
            if ($row) {
                return (int) $row->id_gedung;
            }
        }

        $hay = strtolower($lokasi . ' ' . $nama_api);

        // Prioritas: kampus spesifik dulu
        if (strpos($hay, 'usat') !== false || strpos($hay, 'cengkareng') !== false) {
            $row = $this->db->get_where('tb_gedung', array('kode_gedung' => 'USAT'))->row();
            if ($row) {
                return (int) $row->id_gedung;
            }
        }
        if (strpos($hay, 'lebak bulus') !== false || strpos($hay, 'ypkpln') !== false) {
            $row = $this->db->get_where('tb_gedung', array('kode_gedung' => 'LBP'))->row();
            if ($row) {
                return (int) $row->id_gedung;
            }
        }
        if (strpos($hay, 'duri kosambi') !== false || strpos($hay, 'kosambi') !== false) {
            $row = $this->db->get_where('tb_gedung', array('kode_gedung' => 'IT-PLN'))->row();
            if ($row) {
                return (int) $row->id_gedung;
            }
        }

        // Fallback: Gedung A/B/C/D tanpa kampus → Duri Kosambi
        if (preg_match('/gedung\s*[abcd]/i', $hay) || strpos($hay, 'gedung utama') !== false) {
            $row = $this->db->get_where('tb_gedung', array('kode_gedung' => 'IT-PLN'))->row();
            if ($row) {
                return (int) $row->id_gedung;
            }
        }

        $fallback = $this->db->order_by('id_gedung', 'ASC')->get('tb_gedung')->row();
        if ($fallback) {
            return (int) $fallback->id_gedung;
        }

        // Buat gedung default jika tabel kosong
        $this->db->insert('tb_gedung', array(
            'kode_gedung' => 'DEF',
            'nama_gedung' => 'Default',
        ));
        return (int) $this->db->insert_id();
    }
}

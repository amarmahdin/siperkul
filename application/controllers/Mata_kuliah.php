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
        $this->_sync_sevima();

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

    /**
     * Sync semua halaman API Sevima (target = meta.total Postman, ~6393).
     * Filter kurikulum hanya jika SEVIMA_MK_ID_KURIKULUM di-set di .env.
     */
    private function _sync_sevima() {
        $force = ($this->input->get('force_sync') === '1');
        if (
            !$force &&
            $this->session->userdata('last_sync_mata_kuliah_full_v2') &&
            (time() - $this->session->userdata('last_sync_mata_kuliah_full_v2') < 3600)
        ) {
            return null;
        }

        @set_time_limit(1800);
        $this->_ensure_mk_sevima_columns();

        $page = 1;
        $page_size = 100;
        $last_page = null;
        $api_error = null;
        $retry429 = 0;
        $total_synced = 0;
        $total_skipped = 0;
        $api_total = null;
        $completed = false;

        $configured = function_exists('env') ? env('SEVIMA_MK_ID_KURIKULUM', '') : '';
        $filter_kurikulum = ($configured !== NULL && $configured !== '') ? (string) $configured : null;

        while ($page <= 1000) {
            $url = 'https://api.sevimaplatform.com/siakadcloud/v1/mata-kuliah?' . http_build_query(array(
                'page' => $page,
            ));

            $result = $this->_sevima_get($url);
            if (!$result['ok']) {
                if (isset($result['httpcode']) && (int) $result['httpcode'] === 429 && $retry429 < 40) {
                    $retry429++;
                    sleep(min(30, 3 * $retry429));
                    continue; // ulang halaman yang sama
                }
                $api_error = $result['error'];
                break;
            }

            $retry429 = 0;
            $payload = $result['data'];
            $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : array();
            if (empty($items)) {
                $completed = true;
                break;
            }

            $meta = isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : array();
            if (isset($meta['total'])) {
                $api_total = (int) $meta['total'];
            }
            if (isset($meta['last_page'])) {
                $last_page = (int) $meta['last_page'];
            } elseif (isset($meta['page']['lastPage'])) {
                $last_page = (int) $meta['page']['lastPage'];
            }
            if (isset($meta['per_page'])) {
                $page_size = (int) $meta['per_page'];
            }

            foreach ($items as $item) {
                $attr = isset($item['attributes']) && is_array($item['attributes']) ? $item['attributes'] : array();
                if ($filter_kurikulum !== null) {
                    $id_kurikulum = isset($attr['id_kurikulum']) ? trim((string) $attr['id_kurikulum']) : '';
                    if ($id_kurikulum !== $filter_kurikulum) {
                        $total_skipped++;
                        continue;
                    }
                }
                if ($this->_upsert_mk_from_sevima($item)) {
                    $total_synced++;
                } else {
                    $total_skipped++;
                }
            }

            if ($last_page !== null && $page >= $last_page) {
                $completed = true;
                break;
            }
            if (isset($payload['links']['next']) && empty($payload['links']['next'])) {
                $completed = true;
                break;
            }
            if ($last_page === null && count($items) < $page_size) {
                $completed = true;
                break;
            }

            $page++;
            sleep(1); // jeda antar halaman agar tidak 429
        }

        if ($api_error && $total_synced === 0) {
            return array(
                'type' => 'danger',
                'text' => 'Gagal sync API Sevima Mata Kuliah: ' . $api_error,
            );
        }

        // Hanya kunci 1 jam jika sync selesai penuh
        if ($completed && !$api_error) {
            $this->session->set_userdata('last_sync_mata_kuliah_full_v2', time());
        } else {
            $this->session->unset_userdata('last_sync_mata_kuliah_full_v2');
        }

        $db_total = (int) $this->db->count_all('tb_mata_kuliah');
        $text = 'Sinkronisasi Mata Kuliah: ' . $total_synced . ' diproses di sesi ini';
        if ($api_total !== null) {
            $text .= ' (API total ' . $api_total . ')';
        }
        $text .= ', di database sekarang ' . $db_total . ' baris, sampai halaman ' . $page;
        if ($filter_kurikulum !== null) {
            $text .= ', filter kurikulum ' . $filter_kurikulum;
        }
        if ($total_skipped > 0) {
            $text .= ', dilewati ' . $total_skipped;
        }
        if ($api_error) {
            $text .= '. TERPUTUS: ' . $api_error . ' — buka lagi /mata_kuliah?force_sync=1 untuk lanjut.';
            return array('type' => 'warning', 'text' => $text);
        }
        if (!$completed) {
            $text .= '. Belum selesai — buka /mata_kuliah?force_sync=1 untuk lanjut.';
            return array('type' => 'warning', 'text' => $text);
        }
        $text .= '.';

        return array(
            'type' => 'success',
            'text' => $text,
        );
    }

    private function _ensure_mk_sevima_columns() {
        if (!$this->db->field_exists('id_sevima', 'tb_mata_kuliah')) {
            $this->db->query("ALTER TABLE `tb_mata_kuliah` ADD `id_sevima` varchar(50) DEFAULT NULL AFTER `id_mk`");
            $this->db->query("ALTER TABLE `tb_mata_kuliah` ADD UNIQUE KEY `id_sevima` (`id_sevima`)");
        }
        if (!$this->db->field_exists('id_kurikulum', 'tb_mata_kuliah')) {
            $this->db->query("ALTER TABLE `tb_mata_kuliah` ADD `id_kurikulum` varchar(20) DEFAULT NULL AFTER `id_prodi`");
            $this->db->query("ALTER TABLE `tb_mata_kuliah` ADD KEY `id_kurikulum` (`id_kurikulum`)");
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
            return array('ok' => false, 'error' => $detail, 'data' => null, 'httpcode' => $httpcode);
        }

        if (!is_array($decoded)) {
            return array('ok' => false, 'error' => 'Respons API tidak valid', 'data' => null, 'httpcode' => $httpcode);
        }

        return array('ok' => true, 'error' => null, 'data' => $decoded, 'httpcode' => $httpcode);
    }

    /**
     * @return bool true jika baris disimpan/diupdate
     */
    private function _upsert_mk_from_sevima($item) {
        if (!isset($item['attributes']) || !is_array($item['attributes'])) {
            return false;
        }

        $attr = $item['attributes'];

        if (isset($attr['is_deleted']) && (string) $attr['is_deleted'] === '1') {
            return false;
        }

        $id_sevima = isset($item['id']) ? trim((string) $item['id']) : '';
        $id_kurikulum = isset($attr['id_kurikulum']) ? trim((string) $attr['id_kurikulum']) : '';
        $kode_mk = trim(isset($attr['kode']) ? $attr['kode'] : (isset($attr['kode_mk']) ? $attr['kode_mk'] : (isset($attr['kode_mata_kuliah']) ? $attr['kode_mata_kuliah'] : '')));
        $nama_mk = trim(isset($attr['nama']) ? $attr['nama'] : (isset($attr['nama_mk']) ? $attr['nama_mk'] : (isset($attr['nama_mata_kuliah']) ? $attr['nama_mata_kuliah'] : '')));
        $sks = isset($attr['sks']) ? intval($attr['sks']) : (isset($attr['jumlah_sks']) ? intval($attr['jumlah_sks']) : 0);
        $semester = isset($attr['semester']) ? intval($attr['semester']) : 0;
        $jenis = trim(isset($attr['jenis']) ? $attr['jenis'] : (isset($attr['type']) ? $attr['type'] : 'Wajib'));

        if ($nama_mk === '') {
            return false;
        }

        $jenis_normalized = strtolower($jenis);
        if (strpos($jenis_normalized, 'pilihan') !== false) {
            $jenis = 'Pilihan';
        } else {
            $jenis = 'Wajib';
        }

        $id_prodi = $this->_resolve_prodi_id($attr);
        if (!$id_prodi) {
            return false;
        }

        if ($kode_mk === '') {
            $kode_mk = 'MK-' . substr(md5($id_sevima . '|' . $nama_mk), 0, 8);
        }

        $data_db = array(
            'id_sevima' => $id_sevima !== '' ? $id_sevima : null,
            'id_kurikulum' => $id_kurikulum !== '' ? $id_kurikulum : null,
            'kode_mk' => $kode_mk,
            'nama_mk' => $nama_mk,
            'sks' => $sks > 0 ? $sks : 0,
            'semester' => $semester > 0 ? $semester : 0,
            'jenis' => $jenis,
            'id_prodi' => $id_prodi,
        );

        $this->Mata_kuliah_model->upsert_from_sevima($data_db);
        return true;
    }

    private function _resolve_prodi_id($attr) {
        $kode_api = '';
        if (isset($attr['id_program_studi']) && trim((string) $attr['id_program_studi']) !== '') {
            $kode_api = trim((string) $attr['id_program_studi']);
        } elseif (isset($attr['kode_prodi']) && trim((string) $attr['kode_prodi']) !== '') {
            $kode_api = trim((string) $attr['kode_prodi']);
        }

        $nama_api = '';
        if (isset($attr['program_studi']) && trim((string) $attr['program_studi']) !== '') {
            $nama_api = trim((string) $attr['program_studi']);
        } elseif (isset($attr['nama_prodi']) && trim((string) $attr['nama_prodi']) !== '') {
            $nama_api = trim((string) $attr['nama_prodi']);
        } elseif (isset($attr['nama_program_studi']) && trim((string) $attr['nama_program_studi']) !== '') {
            $nama_api = trim((string) $attr['nama_program_studi']);
        }

        if ($kode_api === '' && $nama_api === '') {
            return null;
        }

        // 1) Cocokkan kode prodi Sevima/PDDIKTI
        if ($kode_api !== '') {
            $row = $this->db->get_where('tb_prodi', array('kode_prodi' => $kode_api))->row();
            if ($row) {
                if ($nama_api !== '' && strcasecmp(trim($row->nama_prodi), $nama_api) !== 0) {
                    // Update nama jika berbeda, tetap pakai id yang sama
                    $this->db->where('id_prodi', $row->id_prodi)->update('tb_prodi', array('nama_prodi' => $nama_api));
                }
                return (int) $row->id_prodi;
            }
        }

        // 2) Cocokkan nama lokal (mis. "S1 Teknik Informatika" ~ "TEKNIK INFORMATIKA")
        //    lalu set kode_prodi ke kode API agar sync berikutnya stabil
        if ($nama_api !== '') {
            $norm_api = $this->_normalize_prodi_name($nama_api);
            $candidates = $this->db->get('tb_prodi')->result();
            $matched = null;
            foreach ($candidates as $p) {
                $norm_local = $this->_normalize_prodi_name($p->nama_prodi);
                if ($norm_local === $norm_api || strpos($norm_local, $norm_api) !== false || strpos($norm_api, $norm_local) !== false) {
                    // Hindari tabrakan nama generik jika kode API beda sudah dipakai prodi lain
                    if ($kode_api !== '') {
                        $kode_taken = $this->db->get_where('tb_prodi', array('kode_prodi' => $kode_api))->row();
                        if ($kode_taken && (int) $kode_taken->id_prodi !== (int) $p->id_prodi) {
                            continue;
                        }
                    }
                    $matched = $p;
                    break;
                }
            }

            if ($matched) {
                $local_kode = (string) $matched->kode_prodi;
                $local_is_pddikti = (bool) preg_match('/^\d{5,}$/', $local_kode);

                // Jangan gabungkan prodi PDDIKTI berbeda meski namanya sama
                // (contoh: TEKNIK MESIN 21103 vs 21201 vs 21401)
                if ($local_is_pddikti && $kode_api !== '' && $kode_api !== $local_kode) {
                    $matched = null;
                }
            }

            if ($matched) {
                $update = array();
                if ($kode_api !== '' && $matched->kode_prodi !== $kode_api) {
                    $update['kode_prodi'] = $kode_api;
                }
                if ($nama_api !== '' && strcasecmp(trim($matched->nama_prodi), $nama_api) !== 0) {
                    $update['nama_prodi'] = $nama_api;
                }
                if (!empty($update)) {
                    $this->db->where('id_prodi', $matched->id_prodi)->update('tb_prodi', $update);
                }
                return (int) $matched->id_prodi;
            }
        }

        // 3) Buat prodi baru dari API (jangan fallback ke prodi pertama)
        $fakultas = $this->db->order_by('id_fakultas', 'ASC')->get('tb_fakultas')->row();
        if (!$fakultas) {
            return null;
        }

        $insert = array(
            'kode_prodi' => $kode_api !== '' ? $kode_api : ('P' . strtoupper(substr(md5($nama_api), 0, 5))),
            'nama_prodi' => $nama_api !== '' ? $nama_api : ('Prodi ' . $kode_api),
            'id_fakultas' => (int) $fakultas->id_fakultas,
        );
        $this->db->insert('tb_prodi', $insert);
        $new_id = (int) $this->db->insert_id();
        return $new_id > 0 ? $new_id : null;
    }

    private function _normalize_prodi_name($name) {
        $n = strtolower(trim((string) $name));
        $n = preg_replace('/^(s1|s2|s3|d3|d4)\s+/u', '', $n);
        $n = preg_replace('/\s+/u', ' ', $n);
        return trim($n);
    }
}

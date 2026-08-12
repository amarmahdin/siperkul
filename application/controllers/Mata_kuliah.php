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

    private function _sync_sevima() {
        if ($this->session->userdata('last_sync_mata_kuliah_all') && (time() - $this->session->userdata('last_sync_mata_kuliah_all') < 3600)) {
            return null;
        }

        @set_time_limit(1200);

        $page = 1;
        $page_size = 100;
        $last_page = null;
        $synced_any = false;
        $total_synced = 0;
        $api_error = null;

        $retry429 = 0;
        while ($page <= 1000) {
            $url = 'https://api.sevimaplatform.com/siakadcloud/v1/mata-kuliah?' . http_build_query(array(
                'page' => $page,
            ));

            $result = $this->_sevima_get($url);
            if (!$result['ok']) {
                if (isset($result['httpcode']) && $result['httpcode'] === 429 && $retry429 < 20) {
                    $retry429++;
                    sleep(min(10, 2 * $retry429));
                    continue;
                }
                $api_error = $result['error'];
                break;
            }

            $retry429 = 0;
            $payload = $result['data'];
            $items = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : array();
            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                $this->_upsert_mk_from_sevima($item);
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
            sleep(2);
        }

        if ($synced_any) {
            $this->session->set_userdata('last_sync_mata_kuliah_all', time());
            return array(
                'type' => 'success',
                'text' => 'Sinkronisasi Mata Kuliah dari Sevima berhasil (' . $total_synced . ' baris diproses dari ' . $page . ' halaman).',
            );
        }

        if ($api_error) {
            return array(
                'type' => 'danger',
                'text' => 'Gagal sync API Sevima Mata Kuliah: ' . $api_error,
            );
        }

        return null;
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

    private function _upsert_mk_from_sevima($item) {
        if (!isset($item['attributes']) || !is_array($item['attributes'])) {
            return;
        }

        $attr = $item['attributes'];
        $kode_mk = trim(isset($attr['kode']) ? $attr['kode'] : (isset($attr['kode_mk']) ? $attr['kode_mk'] : (isset($attr['kode_mata_kuliah']) ? $attr['kode_mata_kuliah'] : '')));
        $nama_mk = trim(isset($attr['nama']) ? $attr['nama'] : (isset($attr['nama_mk']) ? $attr['nama_mk'] : (isset($attr['nama_mata_kuliah']) ? $attr['nama_mata_kuliah'] : '')));
        $sks = isset($attr['sks']) ? intval($attr['sks']) : (isset($attr['jumlah_sks']) ? intval($attr['jumlah_sks']) : 0);
        $semester = isset($attr['semester']) ? intval($attr['semester']) : 0;
        $jenis = trim(isset($attr['jenis']) ? $attr['jenis'] : (isset($attr['type']) ? $attr['type'] : 'Wajib'));

        if ($nama_mk === '') {
            return;
        }

        $jenis_normalized = strtolower($jenis);
        if (strpos($jenis_normalized, 'pilihan') !== false) {
            $jenis = 'Pilihan';
        } else {
            $jenis = 'Wajib';
        }

        $id_prodi = $this->_resolve_prodi_id($attr);
        if (!$id_prodi) {
            return;
        }

        $data_db = array(
            'kode_mk' => $kode_mk ?: 'MK-' . substr(md5($nama_mk), 0, 6),
            'nama_mk' => $nama_mk,
            'sks' => $sks > 0 ? $sks : 0,
            'semester' => $semester > 0 ? $semester : 0,
            'jenis' => $jenis,
            'id_prodi' => $id_prodi,
        );

        $this->Mata_kuliah_model->save($data_db);
    }

    private function _resolve_prodi_id($attr) {
        $search = array(
            'id_prodi',
            'id_program_studi',
            'prodi_id',
            'kode_prodi',
            'kode_program_studi',
            'nama_prodi',
            'prodi',
            'program_studi',
            'nama_program_studi'
        );

        foreach ($search as $key) {
            if (!isset($attr[$key]) || $attr[$key] === '') {
                continue;
            }

            $value = trim((string) $attr[$key]);
            if ($value === '') {
                continue;
            }

            if (is_numeric($value)) {
                $row = $this->db->get_where('tb_prodi', array('id_prodi' => intval($value)))->row();
                if ($row) {
                    return $row->id_prodi;
                }
            }

            $row = $this->db->where('kode_prodi', $value)->or_where('LOWER(nama_prodi)', strtolower($value))->get('tb_prodi')->row();
            if ($row) {
                return $row->id_prodi;
            }
        }

        $fallback = $this->db->order_by('id_prodi', 'ASC')->get('tb_prodi')->row();
        return $fallback ? $fallback->id_prodi : null;
    }
}

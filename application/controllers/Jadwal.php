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
        $data['prodi'] = $this->db->order_by('nama_prodi', 'ASC')->get('tb_prodi')->result();
        if ($this->db->field_exists('id_kurikulum', 'tb_mata_kuliah')) {
            $this->db->order_by('id_kurikulum', 'DESC');
        }
        $this->db->order_by('kode_mk', 'ASC');
        $this->db->order_by('nama_mk', 'ASC');
        $data['mata_kuliah'] = $this->db->get('tb_mata_kuliah')->result();
        $data['dosen'] = $this->db->order_by('nama', 'ASC')->get('tb_dosen')->result();
        // Only active ruangan
        $data['ruangan'] = $this->db->order_by('nama_ruangan', 'ASC')->get_where('tb_ruangan', ['status' => 'Aktif'])->result();

        $data['list_kurikulum'] = array();
        if ($this->db->field_exists('id_kurikulum', 'tb_mata_kuliah')) {
            $this->db->select('id_kurikulum');
            $this->db->distinct();
            $this->db->from('tb_mata_kuliah');
            $this->db->where('id_kurikulum IS NOT NULL', null, false);
            $this->db->where("id_kurikulum <> ''", null, false);
            $this->db->order_by('id_kurikulum', 'DESC');
            $data['list_kurikulum'] = $this->db->get()->result();
        }

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

    public function export() {
        $ta_aktif = $this->db->get_where('tb_tahun_akademik', ['status' => 1])->row();
        if (!$ta_aktif) {
            show_error('Tahun Akademik aktif tidak ditemukan.', 500);
            return;
        }

        $id_kurikulum = $this->input->get('id_kurikulum');
        $rows = $this->Jadwal_model->get_for_export($ta_aktif->id_ta, $id_kurikulum);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal');

        $title = 'JADWAL AKADEMIK TAHUN AJARAN ' . strtoupper($ta_aktif->semester) . ' ' . $ta_aktif->tahun_akademik;
        if ($id_kurikulum) {
            $title .= ' (Kurikulum ' . $id_kurikulum . ')';
        }
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:M1');

        $headers = array('No', 'Nama Prodi', 'Hari', 'Jam', 'Kode MK', 'Matakuliah', 'SKS', 'Kelas', 'Jenis', 'Dosen', 'Target', 'Kurikulum', 'Ruang');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }

        $no = 1;
        $r = 4;
        foreach ($rows as $row) {
            $jam = date('H.i', strtotime($row->jam_mulai)) . ' - ' . date('H.i', strtotime($row->jam_selesai));
            $ruang = $row->nama_ruangan;
            if (stripos($ruang, 'BELUM') !== false) {
                $ruang = '';
            }
            $dosen = $row->nama_dosen;
            if (stripos($dosen, 'BELUM') !== false) {
                $dosen = '';
            }
            $jenis = '';
            if ($this->db->field_exists('jenis_kuliah', 'tb_jadwal') && !empty($row->jenis_kuliah)) {
                $jenis = $row->jenis_kuliah;
            }

            $sheet->setCellValue('A' . $r, $no);
            $sheet->setCellValue('B' . $r, $row->nama_prodi);
            $sheet->setCellValue('C' . $r, $row->hari);
            $sheet->setCellValue('D' . $r, $jam);
            $sheet->setCellValue('E' . $r, $row->kode_mk);
            $sheet->setCellValue('F' . $r, $row->nama_mk);
            $sheet->setCellValue('G' . $r, $row->sks);
            $sheet->setCellValue('H' . $r, $row->kelas);
            $sheet->setCellValue('I' . $r, $jenis);
            $sheet->setCellValue('J' . $r, $dosen);
            $sheet->setCellValue('K' . $r, $row->kapasitas_mhs);
            $sheet->setCellValue('L' . $r, $row->id_kurikulum);
            $sheet->setCellValue('M' . $r, $ruang);
            $no++;
            $r++;
        }

        $filename = 'Jadwal_Kuliah_' . str_replace('/', '-', $ta_aktif->tahun_akademik) . '_' . $ta_aktif->semester;
        if ($id_kurikulum) {
            $filename .= '_Kurikulum' . $id_kurikulum;
        }
        $filename .= '.xlsx';

        $this->Audit_model->log_activity('Export Jadwal', 'Export ' . count($rows) . ' baris jadwal');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import() {
        if (!in_array($this->session->userdata('role'), ['Administrator', 'BAAK', 'Operator Prodi'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Anda tidak memiliki hak akses.'));
            return;
        }

        $ta_aktif = $this->db->get_where('tb_tahun_akademik', ['status' => 1])->row();
        if (!$ta_aktif) {
            echo json_encode(array('status' => 'error', 'message' => 'Tahun Akademik aktif tidak ditemukan.'));
            return;
        }

        if (empty($_FILES['file_excel']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'message' => 'File Excel belum dipilih.'));
            return;
        }

        $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('xlsx', 'xls'))) {
            echo json_encode(array('status' => 'error', 'message' => 'Format harus .xlsx atau .xls'));
            return;
        }

        @set_time_limit(300);
        $this->_ensure_jadwal_import_columns();

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['file_excel']['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => 'Gagal membaca Excel: ' . $e->getMessage()));
            return;
        }

        $header_map = null;
        $header_row_num = null;
        foreach ($rows as $num => $row) {
            $norm = array();
            foreach ($row as $col => $val) {
                $norm[$col] = $this->_norm_text($val);
            }
            $joined = implode('|', $norm);
            if (stripos($joined, 'nama prodi') !== false && stripos($joined, 'hari') !== false && (stripos($joined, 'matakuliah') !== false || stripos($joined, 'mata kuliah') !== false)) {
                $header_map = $this->_map_excel_headers($norm);
                $header_row_num = $num;
                break;
            }
        }

        if (!$header_map || empty($header_map['hari']) || empty($header_map['matakuliah'])) {
            echo json_encode(array('status' => 'error', 'message' => 'Header Excel tidak dikenali. Pastikan ada kolom: Nama Prodi, Hari, Jam, Matakuliah, Kurikulum, dll.'));
            return;
        }

        $id_dosen_tba = $this->_get_or_create_dosen_tba();
        $id_ruangan_tba = $this->_get_or_create_ruangan_tba();

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = array();

        foreach ($rows as $num => $row) {
            if ($num <= $header_row_num) {
                continue;
            }

            $prodi_nama = $this->_cell($row, $header_map, 'prodi');
            $hari = $this->_cell($row, $header_map, 'hari');
            $jam = $this->_cell($row, $header_map, 'jam');
            $kode_mk = $this->_cell($row, $header_map, 'kode_mk');
            $nama_mk = $this->_cell($row, $header_map, 'matakuliah');
            $kelas = $this->_cell($row, $header_map, 'kelas');
            $jenis = $this->_cell($row, $header_map, 'jenis');
            $dosen_nama = $this->_cell($row, $header_map, 'dosen');
            $target = $this->_cell($row, $header_map, 'target');
            $kurikulum = $this->_cell($row, $header_map, 'kurikulum');
            $ruang_nama = $this->_cell($row, $header_map, 'ruang');

            if ($hari === '' && $nama_mk === '' && $prodi_nama === '') {
                continue;
            }
            if ($hari === '' || $nama_mk === '' || $jam === '') {
                $skipped++;
                $errors[] = "Baris $num: hari/jam/matakuliah kosong";
                continue;
            }

            $times = $this->_parse_jam($jam);
            if (!$times) {
                $skipped++;
                $errors[] = "Baris $num: format jam tidak valid ($jam)";
                continue;
            }

            $hari = $this->_normalize_hari($hari);
            if ($hari === '') {
                $skipped++;
                $errors[] = "Baris $num: hari tidak valid";
                continue;
            }

            $id_prodi = $this->_resolve_prodi($prodi_nama);
            if (!$id_prodi) {
                $skipped++;
                $errors[] = "Baris $num: prodi tidak ditemukan ($prodi_nama)";
                continue;
            }

            $id_mk = $this->_resolve_mk($kode_mk, $nama_mk, $kurikulum, $id_prodi);
            if (!$id_mk) {
                $skipped++;
                $errors[] = "Baris $num: mata kuliah tidak ditemukan ($kode_mk $nama_mk / kur $kurikulum)";
                continue;
            }

            $id_dosen = $dosen_nama !== '' ? $this->_resolve_dosen($dosen_nama) : null;
            if (!$id_dosen) {
                $id_dosen = $id_dosen_tba;
            }

            $id_ruangan = $ruang_nama !== '' ? $this->_resolve_ruangan($ruang_nama) : null;
            if (!$id_ruangan) {
                $id_ruangan = $id_ruangan_tba;
            }

            $kelas = $kelas !== '' ? $kelas : '-';
            $kapasitas = is_numeric($target) ? (int) $target : 0;

            $data = array(
                'id_prodi' => $id_prodi,
                'id_mk' => $id_mk,
                'kelas' => substr($kelas, 0, 10),
                'id_dosen' => $id_dosen,
                'hari' => $hari,
                'jam_mulai' => $times['mulai'],
                'jam_selesai' => $times['selesai'],
                'id_ruangan' => $id_ruangan,
                'kapasitas_mhs' => $kapasitas,
                'id_ta' => $ta_aktif->id_ta,
                'status' => 'Aktif',
                'created_by' => $this->session->userdata('id_user'),
            );
            if ($this->db->field_exists('jenis_kuliah', 'tb_jadwal')) {
                $data['jenis_kuliah'] = $jenis !== '' ? substr($jenis, 0, 50) : null;
            }

            $exist = $this->Jadwal_model->find_existing($id_prodi, $id_mk, $data['kelas'], $hari, $times['mulai'], $ta_aktif->id_ta);
            if ($exist) {
                $this->Jadwal_model->update(array('id_jadwal' => $exist->id_jadwal), $data);
                $updated++;
            } else {
                // Bentrok ruang/dosen: tetap simpan agar import lengkap; admin bisa rapikan nanti
                $this->Jadwal_model->save($data);
                $imported++;
            }
        }

        $this->Audit_model->log_activity(
            'Import Jadwal',
            "Import Excel: +$imported baru, $updated update, $skipped skip"
        );

        $msg = "Import selesai: $imported ditambah, $updated diperbarui, $skipped dilewati.";
        if (!empty($errors)) {
            $preview = array_slice($errors, 0, 8);
            $msg .= ' Detail: ' . implode('; ', $preview);
            if (count($errors) > 8) {
                $msg .= ' (+' . (count($errors) - 8) . ' lainnya)';
            }
        }

        echo json_encode(array(
            'status' => ($imported + $updated) > 0 ? 'success' : 'error',
            'message' => $msg,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ));
    }

    private function _ensure_jadwal_import_columns() {
        if (!$this->db->field_exists('jenis_kuliah', 'tb_jadwal')) {
            $this->db->query("ALTER TABLE `tb_jadwal` ADD `jenis_kuliah` varchar(50) DEFAULT NULL AFTER `kelas`");
        }
    }

    private function _map_excel_headers($norm_row) {
        $map = array();
        foreach ($norm_row as $col => $label) {
            $l = strtolower($label);
            if ($l === '') {
                continue;
            }
            if (strpos($l, 'nama prodi') !== false || $l === 'prodi') {
                $map['prodi'] = $col;
            } elseif ($l === 'hari') {
                $map['hari'] = $col;
            } elseif ($l === 'jam' || strpos($l, 'waktu') !== false) {
                $map['jam'] = $col;
            } elseif (strpos($l, 'kode') !== false && strpos($l, 'mk') !== false) {
                $map['kode_mk'] = $col;
            } elseif (strpos($l, 'matakuliah') !== false || strpos($l, 'mata kuliah') !== false) {
                $map['matakuliah'] = $col;
            } elseif ($l === 'sks') {
                $map['sks'] = $col;
            } elseif ($l === 'kelas') {
                $map['kelas'] = $col;
            } elseif ($l === 'jenis') {
                $map['jenis'] = $col;
            } elseif ($l === 'dosen') {
                $map['dosen'] = $col;
            } elseif ($l === 'target' || strpos($l, 'kapasitas') !== false || strpos($l, 'peserta') !== false) {
                $map['target'] = $col;
            } elseif (strpos($l, 'kurikulum') !== false) {
                $map['kurikulum'] = $col;
            } elseif ($l === 'ruang' || strpos($l, 'ruangan') !== false) {
                $map['ruang'] = $col;
            }
        }
        return $map;
    }

    private function _cell($row, $map, $key) {
        if (empty($map[$key]) || !isset($row[$map[$key]])) {
            return '';
        }
        return $this->_norm_text($row[$map[$key]]);
    }

    private function _norm_text($val) {
        if ($val === null) {
            return '';
        }
        $s = trim((string) $val);
        // NBSP + line break di sel Excel (prodi wrap) → spasi
        $s = str_replace(array("\xc2\xa0", "\xA0", "\r\n", "\r", "\n"), ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    private function _parse_jam($jam) {
        $jam = str_replace(array('.', '–', '—'), array(':', '-', '-'), $jam);
        if (!preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $jam, $m)) {
            return null;
        }
        return array(
            'mulai' => sprintf('%02d:%02d:00', (int) $m[1], (int) $m[2]),
            'selesai' => sprintf('%02d:%02d:00', (int) $m[3], (int) $m[4]),
        );
    }

    private function _normalize_hari($hari) {
        $hari = ucfirst(strtolower($this->_norm_text($hari)));
        $valid = array('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu');
        return in_array($hari, $valid, true) ? $hari : '';
    }

    private function _resolve_prodi($nama) {
        $nama = $this->_norm_text($nama);
        if ($nama === '') {
            return null;
        }

        $row = $this->db->where('nama_prodi', $nama)->get('tb_prodi')->row();
        if ($row) {
            return (int) $row->id_prodi;
        }

        $clean = preg_replace('/^(program\s+studi\s+)?(s1|s2|d3|d4)\s+/iu', '', $nama);
        $clean = trim($clean);
        if ($clean !== '') {
            $this->db->like('nama_prodi', $clean);
            $row = $this->db->get('tb_prodi')->row();
            if ($row) {
                return (int) $row->id_prodi;
            }
        }

        $this->db->like('nama_prodi', $nama);
        $row = $this->db->get('tb_prodi')->row();
        return $row ? (int) $row->id_prodi : null;
    }

    private function _resolve_mk($kode_mk, $nama_mk, $kurikulum, $id_prodi) {
        $kode_mk = $this->_norm_text($kode_mk);
        $nama_mk = $this->_norm_text($nama_mk);
        $kurikulum = $this->_norm_text($kurikulum);

        if ($kode_mk !== '') {
            $this->db->where('kode_mk', $kode_mk);
            if ($kurikulum !== '') {
                $this->db->where('id_kurikulum', $kurikulum);
            }
            $row = $this->db->get('tb_mata_kuliah')->row();
            if ($row) {
                return (int) $row->id_mk;
            }
            // kode tanpa kurikulum
            $row = $this->db->get_where('tb_mata_kuliah', array('kode_mk' => $kode_mk))->row();
            if ($row) {
                return (int) $row->id_mk;
            }
        }

        if ($nama_mk === '') {
            return null;
        }

        // Prefer same prodi + kurikulum + nama
        $this->db->group_start();
        $this->db->where('nama_mk', $nama_mk);
        $this->db->or_where('nama_mk', strtoupper($nama_mk));
        $this->db->group_end();
        if ($id_prodi) {
            $this->db->where('id_prodi', $id_prodi);
        }
        if ($kurikulum !== '') {
            $this->db->where('id_kurikulum', $kurikulum);
        }
        $row = $this->db->get('tb_mata_kuliah')->row();
        if ($row) {
            return (int) $row->id_mk;
        }

        $this->db->like('nama_mk', $nama_mk);
        if ($id_prodi) {
            $this->db->where('id_prodi', $id_prodi);
        }
        if ($kurikulum !== '') {
            $this->db->where('id_kurikulum', $kurikulum);
        }
        $row = $this->db->get('tb_mata_kuliah')->row();
        return $row ? (int) $row->id_mk : null;
    }

    /**
     * Nama inti dosen tanpa gelar (untuk matching fuzzy).
     */
    private function _core_person_name($nama) {
        $s = strtolower($this->_norm_text($nama));
        $gelar = 'dr|dra|ir|prof|h|hj|st|mt|mkom|mti|msi|mmsi|mm|mmi|mmat|ssi|skom|spd|mpd|phd|'
            . 'm\.?\s*kom|m\.?\s*t\.?\s*i|m\.?\s*m\.?\s*s\.?\s*i|m\.?\s*msi|s\.?\s*kom|s\.?\s*si|'
            . 's\.?\s*t|s\.?\s*mat|m\.?\s*mat|m\.?\s*sc|m\.?\s*m|s\.?\s*pd|m\.?\s*pd|p\.?\s*h\.?\s*d';
        $s = preg_replace('/\b(' . $gelar . ')\b\.?/iu', ' ', $s);
        $s = preg_replace('/[^a-z\s]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    /**
     * Cocokkan token Excel vs DB (termasuk inisial: DK≈Dian Kemala, B N≈Bahat Nauli).
     */
    private function _person_tokens_match($excel_core, $db_core) {
        if ($excel_core === '' || $db_core === '') {
            return false;
        }
        if ($excel_core === $db_core) {
            return true;
        }
        if (strpos($db_core, $excel_core) === 0 || strpos($excel_core, $db_core) === 0) {
            return true;
        }

        $ex = preg_split('/\s+/', $excel_core);
        $db = preg_split('/\s+/', $db_core);
        if (empty($ex) || empty($db) || $ex[0] !== $db[0]) {
            return false;
        }

        $i = 0;
        $j = 0;
        while ($i < count($ex) && $j < count($db)) {
            $et = $ex[$i];
            $dt = $db[$j];
            if ($et === $dt) {
                $i++;
                $j++;
                continue;
            }
            // "dk" vs "dian"+"kemala"
            if (strlen($et) >= 2 && strlen($et) <= 3 && ctype_alpha($et)) {
                $need = strlen($et);
                $built = '';
                $k = $j;
                while ($k < count($db) && strlen($built) < $need) {
                    $built .= substr($db[$k], 0, 1);
                    $k++;
                }
                if ($built === $et) {
                    $i++;
                    $j = $k;
                    continue;
                }
            }
            // "b" vs "bahat" / sebaliknya
            if (strlen($et) === 1 && isset($dt[0]) && $et === $dt[0]) {
                $i++;
                $j++;
                continue;
            }
            if (strlen($dt) === 1 && isset($et[0]) && $dt === $et[0]) {
                $i++;
                $j++;
                continue;
            }
            return false;
        }
        return $i === count($ex);
    }

    private function _resolve_dosen($nama) {
        $nama = $this->_norm_text($nama);
        if ($nama === '') {
            return null;
        }

        $row = $this->db->where('nama', $nama)->get('tb_dosen')->row();
        if ($row) {
            return (int) $row->id_dosen;
        }

        $core = $this->_core_person_name($nama);
        if ($core === '') {
            return null;
        }

        $parts = explode(' ', $core);
        $first = $parts[0];
        if ($first === '') {
            return null;
        }

        $candidates = $this->db->like('nama', $first)->get('tb_dosen')->result();
        $best = null;
        $best_score = -1;
        foreach ($candidates as $c) {
            $c_core = $this->_core_person_name($c->nama);
            if ($this->_person_tokens_match($core, $c_core) || $this->_person_tokens_match($c_core, $core)) {
                $score = similar_text($core, $c_core);
                if ($score > $best_score) {
                    $best_score = $score;
                    $best = $c;
                }
            }
        }
        if ($best) {
            return (int) $best->id_dosen;
        }

        // Fallback: 2 kata pertama sama
        if (count($parts) >= 2) {
            $prefix = $parts[0] . ' ' . $parts[1];
            foreach ($candidates as $c) {
                $c_core = $this->_core_person_name($c->nama);
                if (strpos($c_core, $prefix) === 0) {
                    return (int) $c->id_dosen;
                }
            }
        }

        return null;
    }

    private function _resolve_ruangan($nama) {
        $nama = $this->_norm_text($nama);
        if ($nama === '') {
            return null;
        }

        $row = $this->db->where('nama_ruangan', $nama)->get('tb_ruangan')->row();
        if ($row) {
            return (int) $row->id_ruangan;
        }
        $row = $this->db->where('kode_ruangan', $nama)->get('tb_ruangan')->row();
        if ($row) {
            return (int) $row->id_ruangan;
        }
        $this->db->like('nama_ruangan', $nama);
        $row = $this->db->get('tb_ruangan')->row();
        return $row ? (int) $row->id_ruangan : null;
    }

    private function _get_or_create_dosen_tba() {
        $row = $this->db->get_where('tb_dosen', array('kode_dosen' => 'TBA'))->row();
        if ($row) {
            return (int) $row->id_dosen;
        }
        $this->db->insert('tb_dosen', array(
            'nidn' => null,
            'kode_dosen' => 'TBA',
            'nama' => 'BELUM DITENTUKAN',
            'email' => null,
            'no_hp' => null,
        ));
        return (int) $this->db->insert_id();
    }

    private function _get_or_create_ruangan_tba() {
        $row = $this->db->get_where('tb_ruangan', array('kode_ruangan' => 'TBA'))->row();
        if ($row) {
            return (int) $row->id_ruangan;
        }
        $gedung = $this->db->order_by('id_gedung', 'ASC')->get('tb_gedung')->row();
        $id_gedung = $gedung ? (int) $gedung->id_gedung : 1;
        $this->db->insert('tb_ruangan', array(
            'id_gedung' => $id_gedung,
            'kode_ruangan' => 'TBA',
            'nama_ruangan' => 'BELUM DITENTUKAN',
            'lantai' => 1,
            'nomor_ruang' => '-',
            'kapasitas_kuliah' => 0,
            'kapasitas_ujian' => 0,
            'status' => 'Aktif',
        ));
        return (int) $this->db->insert_id();
    }
}

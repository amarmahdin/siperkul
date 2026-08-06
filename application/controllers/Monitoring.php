<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('Monitoring_model');
    }

    public function index() {
        $data['title'] = 'Monitoring Ruangan Realtime';
        
        $data['gedung'] = $this->db->get('tb_gedung')->result();

        // Default to current day
        $hari_ini = $this->_hari_indonesia(date('l'));
        $data['hari_ini'] = $hari_ini;
        
        $ta_aktif = $this->db->get_where('tb_tahun_akademik', ['status' => 1])->row();
        $data['ta_aktif'] = $ta_aktif;

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('monitoring/index', $data);
        $this->load->view('templates/footer');
    }

    public function load_grid() {
        $hari = $this->input->post('hari') ? $this->input->post('hari') : $this->_hari_indonesia(date('l'));
        $id_gedung = $this->input->post('id_gedung');

        $ta_aktif = $this->db->get_where('tb_tahun_akademik', ['status' => 1])->row();
        $id_ta = $ta_aktif ? $ta_aktif->id_ta : 0;

        $ruangan = $this->Monitoring_model->get_ruangan($id_gedung);
        $jadwal = $this->Monitoring_model->get_jadwal_by_hari($hari, $id_ta);

        // Jam slots 07:00 to 18:00
        $jam_slots = [];
        for($i = 7; $i <= 18; $i++) {
            $jam_slots[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }

        $grid = [];
        foreach($ruangan as $r) {
            $row = [
                'ruangan' => $r->nama_ruangan,
                'kapasitas' => $r->kapasitas_kuliah,
                'cells' => []
            ];

            foreach($jam_slots as $jam) {
                // Check if any schedule falls in this hour slot
                $slot_time = strtotime($jam);
                $end_slot = strtotime('+1 hour', $slot_time);
                
                $jadwal_ditemukan = null;
                foreach($jadwal as $j) {
                    if($j->id_ruangan == $r->id_ruangan) {
                        $j_mulai = strtotime($j->jam_mulai);
                        $j_selesai = strtotime($j->jam_selesai);

                        // If slot overlaps with schedule
                        if($j_mulai < $end_slot && $j_selesai > $slot_time) {
                            $jadwal_ditemukan = $j;
                            break;
                        }
                    }
                }

                if($jadwal_ditemukan) {
                    // Cek kapasitas (Kuning = Hampir Penuh > 80%, Hijau = Terpakai)
                    // Merah = Bentrok (sudah dicegah, tapi kita bisa handle warna lain)
                    $persentase = ($jadwal_ditemukan->kapasitas_mhs / $r->kapasitas_kuliah) * 100;
                    $warna = 'bg-success'; // Hijau
                    if($persentase > 90) {
                        $warna = 'bg-warning text-dark'; // Kuning hampir penuh
                    }
                    if($persentase > 100) {
                        $warna = 'bg-danger'; // Merah overcapacity
                    }

                    $row['cells'][] = [
                        'status' => 'terpakai',
                        'warna' => $warna,
                        'mk' => $jadwal_ditemukan->nama_mk,
                        'dosen' => $jadwal_ditemukan->nama_dosen,
                        'kelas' => $jadwal_ditemukan->kelas,
                        'waktu' => date('H:i', strtotime($jadwal_ditemukan->jam_mulai)) . ' - ' . date('H:i', strtotime($jadwal_ditemukan->jam_selesai)),
                        'mhs' => $jadwal_ditemukan->kapasitas_mhs
                    ];
                } else {
                    $row['cells'][] = [
                        'status' => 'kosong',
                        'warna' => 'bg-light',
                        'mk' => '',
                        'dosen' => '',
                        'kelas' => '',
                        'waktu' => '',
                        'mhs' => 0
                    ];
                }
            }
            $grid[] = $row;
        }

        echo json_encode([
            'jam_slots' => $jam_slots,
            'grid' => $grid
        ]);
    }

    private function _hari_indonesia($day) {
        $hari = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        return isset($hari[$day]) ? $hari[$day] : 'Senin';
    }
}

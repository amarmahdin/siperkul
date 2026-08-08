<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring_model extends CI_Model {

    public function get_ruangan($id_gedung = null, $id_dosen = null) {
        $this->db->select('tb_ruangan.*');
        $this->db->from('tb_ruangan');
        $this->db->where('tb_ruangan.status', 'Aktif');
        if ($id_gedung) {
            $this->db->where('tb_ruangan.id_gedung', $id_gedung);
        }

        // Dosen Viewer: hanya ruangan yang dipakai jadwalnya (null = semua role non-viewer)
        if ($id_dosen !== null) {
            $this->db->join('tb_jadwal', 'tb_jadwal.id_ruangan = tb_ruangan.id_ruangan');
            $this->db->where('tb_jadwal.id_dosen', (int) $id_dosen);
            $this->db->group_by('tb_ruangan.id_ruangan');
        }

        $this->db->order_by('tb_ruangan.nama_ruangan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_jadwal_by_hari($hari, $id_ta, $id_dosen = null) {
        $this->db->select('tb_jadwal.*, tb_mata_kuliah.nama_mk, tb_dosen.nama as nama_dosen');
        $this->db->from('tb_jadwal');
        $this->db->join('tb_mata_kuliah', 'tb_mata_kuliah.id_mk = tb_jadwal.id_mk');
        $this->db->join('tb_dosen', 'tb_dosen.id_dosen = tb_jadwal.id_dosen');
        $this->db->where('tb_jadwal.hari', $hari);
        $this->db->where('tb_jadwal.id_ta', $id_ta);
        if ($id_dosen !== null) {
            $this->db->where('tb_jadwal.id_dosen', (int) $id_dosen);
        }
        return $this->db->get()->result();
    }
}

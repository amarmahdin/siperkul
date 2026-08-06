<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring_model extends CI_Model {

    public function get_ruangan($id_gedung = null) {
        $this->db->select('*');
        $this->db->from('tb_ruangan');
        $this->db->where('status', 'Aktif');
        if($id_gedung) {
            $this->db->where('id_gedung', $id_gedung);
        }
        $this->db->order_by('nama_ruangan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_jadwal_by_hari($hari, $id_ta) {
        $this->db->select('tb_jadwal.*, tb_mata_kuliah.nama_mk, tb_dosen.nama as nama_dosen');
        $this->db->from('tb_jadwal');
        $this->db->join('tb_mata_kuliah', 'tb_mata_kuliah.id_mk = tb_jadwal.id_mk');
        $this->db->join('tb_dosen', 'tb_dosen.id_dosen = tb_jadwal.id_dosen');
        $this->db->where('tb_jadwal.hari', $hari);
        $this->db->where('tb_jadwal.id_ta', $id_ta);
        return $this->db->get()->result();
    }
}

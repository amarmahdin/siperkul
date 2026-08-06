<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    public function count_table($table) {
        return $this->db->count_all($table);
    }

    public function get_jadwal_per_hari() {
        $this->db->select('hari, count(id_jadwal) as total');
        $this->db->group_by('hari');
        $query = $this->db->get('tb_jadwal');
        return $query->result_array();
    }

    public function get_jadwal_per_fakultas() {
        $this->db->select('f.kode_fakultas, f.nama_fakultas, count(j.id_jadwal) as total');
        $this->db->from('tb_jadwal j');
        $this->db->join('tb_prodi p', 'j.id_prodi = p.id_prodi');
        $this->db->join('tb_fakultas f', 'p.id_fakultas = f.id_fakultas');
        $this->db->group_by('f.id_fakultas');
        $query = $this->db->get();
        return $query->result_array();
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    public function count_table($table) {
        return $this->db->count_all($table);
    }

    public function count_jadwal($id_dosen = null) {
        $this->db->from('tb_jadwal j');
        $this->db->join('tb_tahun_akademik ta', 'ta.id_ta = j.id_ta');
        $this->db->where('ta.status', 1);
        if ($id_dosen !== null) {
            $this->db->where('j.id_dosen', (int) $id_dosen);
        }
        return $this->db->count_all_results();
    }

    public function count_distinct_from_jadwal($column, $id_dosen) {
        $this->db->select('COUNT(DISTINCT j.' . $column . ') AS total', false);
        $this->db->from('tb_jadwal j');
        $this->db->join('tb_tahun_akademik ta', 'ta.id_ta = j.id_ta');
        $this->db->where('ta.status', 1);
        $this->db->where('j.id_dosen', (int) $id_dosen);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    public function count_fakultas_dosen($id_dosen) {
        $this->db->select('COUNT(DISTINCT f.id_fakultas) AS total', false);
        $this->db->from('tb_jadwal j');
        $this->db->join('tb_tahun_akademik ta', 'ta.id_ta = j.id_ta');
        $this->db->join('tb_prodi p', 'p.id_prodi = j.id_prodi');
        $this->db->join('tb_fakultas f', 'f.id_fakultas = p.id_fakultas');
        $this->db->where('ta.status', 1);
        $this->db->where('j.id_dosen', (int) $id_dosen);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    public function get_jadwal_per_hari($id_dosen = null) {
        $this->db->select('j.hari, count(j.id_jadwal) as total');
        $this->db->from('tb_jadwal j');
        $this->db->join('tb_tahun_akademik ta', 'ta.id_ta = j.id_ta');
        $this->db->where('ta.status', 1);
        if ($id_dosen !== null) {
            $this->db->where('j.id_dosen', (int) $id_dosen);
        }
        $this->db->group_by('j.hari');
        return $this->db->get()->result_array();
    }

    public function get_jadwal_per_fakultas($id_dosen = null) {
        $this->db->select('f.kode_fakultas, f.nama_fakultas, count(j.id_jadwal) as total');
        $this->db->from('tb_jadwal j');
        $this->db->join('tb_tahun_akademik ta', 'ta.id_ta = j.id_ta');
        $this->db->join('tb_prodi p', 'j.id_prodi = p.id_prodi');
        $this->db->join('tb_fakultas f', 'p.id_fakultas = f.id_fakultas');
        $this->db->where('ta.status', 1);
        if ($id_dosen !== null) {
            $this->db->where('j.id_dosen', (int) $id_dosen);
        }
        $this->db->group_by('f.id_fakultas');
        return $this->db->get()->result_array();
    }
}

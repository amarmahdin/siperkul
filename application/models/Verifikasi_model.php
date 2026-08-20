<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Verifikasi_model extends CI_Model {

    public function get_pending() {
        $this->db->select('tb_users.*, tb_dosen.nama as nama_dosen, tb_dosen.kode_dosen');
        $this->db->from('tb_users');
        $this->db->join('tb_dosen', 'tb_dosen.id_dosen = tb_users.id_dosen', 'left');
        $this->db->where('tb_users.role', 'Viewer');
        $this->db->where('tb_users.status', 'Menunggu');
        $this->db->order_by('tb_users.created_at', 'ASC');
        return $this->db->get()->result();
    }

    public function count_pending() {
        $this->db->where('role', 'Viewer');
        $this->db->where('status', 'Menunggu');
        return $this->db->count_all_results('tb_users');
    }

    public function get_all_viewer() {
       $this->db->select('tb_users.*, tb_dosen.nama as nama_dosen, tb_dosen.kode_dosen');
       $this->db->from('tb_users');
       $this->db->join('tb_dosen', 'tb_dosen.id_dosen = tb_users.id_dosen', 'left');
       $this->db->where('tb_users.role', 'Viewer');
       $this->db->where_in('tb_users.status', ['Aktif', 'Menunggu']);
    }

    public function approve($id_user, $id_dosen) {
        $this->db->where('id_user', $id_user);
        $this->db->where('role', 'Viewer');
        return $this->db->update('tb_users', array(
            'status'   => 'Aktif',
            'id_dosen' => $id_dosen,
        ));
    }

    public function reject($id_user) {
        $this->db->where('id_user', $id_user);
        $this->db->where('role', 'Viewer');
        return $this->db->update('tb_users', array(
            'status'   => 'Ditolak',
            'id_dosen' => null,
        ));
    }
}

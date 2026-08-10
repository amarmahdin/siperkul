<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    public function get_user($username) {
        if ($username === null || $username === '') {
            return null;
        }
        $this->db->where('username', $username);
        return $this->db->get('tb_users')->row();
    }

    public function get_user_by_email($email) {
        $email = strtolower(trim(str_replace(array("\r", "\n"), '', $email)));
        if ($email === '') {
            return null;
        }
        $normalized = "LOWER(TRIM(REPLACE(REPLACE(IFNULL(email, ''), CHAR(13), ''), CHAR(10), '')))";
        $this->db->where($normalized . ' = ' . $this->db->escape($email), NULL, FALSE);
        return $this->db->get('tb_users')->row();
    }

    public function get_user_by_id($id_user) {
        return $this->db->get_where('tb_users', array('id_user' => $id_user))->row();
    }

    /**
     * Buat akun Viewer dari SSO (menunggu verifikasi admin).
     */
    public function create_pending_viewer($email, $nama_lengkap) {
        $email = strtolower(trim(str_replace(array("\r", "\n"), '', $email)));
        $data = array(
            'username'     => null,
            'password'     => null,
            'nama_lengkap' => $nama_lengkap !== '' ? $nama_lengkap : $email,
            'email'        => $email,
            'role'         => 'Viewer',
            'status'       => 'Menunggu',
            'id_fakultas'  => null,
            'id_prodi'     => null,
            'id_dosen'     => null,
        );
        $this->db->insert('tb_users', $data);
        return $this->get_user_by_email($email);
    }

    public function get_dosen_by_id($id_dosen) {
        return $this->db->get_where('tb_dosen', array('id_dosen' => $id_dosen))->row();
    }
}

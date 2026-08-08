<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    public function get_user($username) {
        $this->db->where('username', $username);
        return $this->db->get('tb_users')->row();
    }

    /**
     * Cari dosen berdasarkan email (sumber utama untuk SSO).
     */
    public function get_dosen_by_email($email) {
        $email = strtolower(trim(str_replace(array("\r", "\n"), '', $email)));
        if ($email === '') {
            return null;
        }

        $normalized = "LOWER(TRIM(REPLACE(REPLACE(IFNULL(email, ''), CHAR(13), ''), CHAR(10), '')))";
        $this->db->where($normalized . ' = ' . $this->db->escape($email), NULL, FALSE);
        return $this->db->get('tb_dosen')->row();
    }

    /**
     * Map akun login ke dosen: username wajib = kode_dosen.
     */
    public function get_dosen_for_user($user) {
        if (!$user || empty($user->username)) {
            return null;
        }

        $this->db->where('kode_dosen', $user->username);
        return $this->db->get('tb_dosen')->row();
    }
}

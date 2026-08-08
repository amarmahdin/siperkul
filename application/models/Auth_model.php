<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    public function get_user($username) {
        $this->db->where('username', $username);
        return $this->db->get('tb_users')->row();
    }

    public function get_user_by_email($email) {
        $email = strtolower(trim(str_replace(array("\r", "\n"), '', $email)));
        // Normalize DB value too (handles accidental Enter/spaces from phpMyAdmin paste)
        $normalized = "LOWER(TRIM(REPLACE(REPLACE(IFNULL(email, ''), CHAR(13), ''), CHAR(10), '')))";
        $this->db->where($normalized . ' = ' . $this->db->escape($email), NULL, FALSE);
        return $this->db->get('tb_users')->row();
    }

    /**
     * Map Viewer/dosen login ke tb_dosen via email, lalu kode_dosen = username.
     */
    public function get_dosen_for_user($user) {
        if (!$user) {
            return null;
        }

        if (!empty($user->email)) {
            $email = strtolower(trim(str_replace(array("\r", "\n"), '', $user->email)));
            $normalized = "LOWER(TRIM(REPLACE(REPLACE(IFNULL(email, ''), CHAR(13), ''), CHAR(10), '')))";
            $this->db->where($normalized . ' = ' . $this->db->escape($email), NULL, FALSE);
            $dosen = $this->db->get('tb_dosen')->row();
            if ($dosen) {
                return $dosen;
            }
        }

        if (!empty($user->username)) {
            $this->db->where('kode_dosen', $user->username);
            $dosen = $this->db->get('tb_dosen')->row();
            if ($dosen) {
                return $dosen;
            }
        }

        return null;
    }

    public function create_sso_user($email, $display_name = '') {
        $email = strtolower(trim($email));
        $local = strstr($email, '@', true);
        $username = $local ? preg_replace('/[^a-zA-Z0-9._-]/', '', $local) : 'user';
        if ($username === '') {
            $username = 'user';
        }

        $base = $username;
        $n = 1;
        while ($this->db->where('username', $username)->count_all_results('tb_users') > 0) {
            $username = $base . $n;
            $n++;
            $this->db->reset_query();
        }

        $data = array(
            'username'     => $username,
            'password'     => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
            'nama_lengkap' => $display_name !== '' ? $display_name : $username,
            'email'        => $email,
            'role'         => 'Viewer',
            'id_fakultas'  => null,
            'id_prodi'     => null,
        );

        $this->db->insert('tb_users', $data);
        return $this->get_user_by_email($email);
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    public function get_user($username) {
        $this->db->where('username', $username);
        return $this->db->get('tb_users')->row();
    }

    public function get_user_by_email($email) {
        $this->db->where('email', strtolower(trim($email)));
        return $this->db->get('tb_users')->row();
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

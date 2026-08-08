<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {

    var $table = 'tb_audit_trail';
    var $column_order = array(null, 'tb_audit_trail.tanggal', 'tb_users.nama_lengkap', 'tb_audit_trail.aktivitas', 'tb_audit_trail.keterangan', 'tb_audit_trail.ip_address');
    var $column_search = array('tb_users.nama_lengkap', 'tb_audit_trail.aktivitas', 'tb_audit_trail.keterangan', 'tb_audit_trail.ip_address');
    var $order = array('tb_audit_trail.id_audit' => 'desc');

    private function _get_datatables_query()
    {
        $this->db->select('tb_audit_trail.*, tb_users.nama_lengkap, tb_users.role');
        $this->db->from($this->table);
        $this->db->join('tb_users', 'tb_users.id_user = tb_audit_trail.id_user', 'left');

        $i = 0;
        foreach ($this->column_search as $item) 
        {
            if($_POST['search']['value']) 
            {
                if($i===0) 
                {
                    $this->db->group_start(); 
                    $this->db->like($item, $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if(count($this->column_search) - 1 == $i) 
                    $this->db->group_end(); 
            }
            $i++;
        }
        
        if(isset($_POST['order'])) 
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->order))
        {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    // Helper method to log activity
    public function log_activity($aktivitas, $keterangan = '')
    {
        $id_user = $this->session->userdata('id_user');
        if (!$id_user) {
            return;
        }

        // Skip if user was deleted while still logged in (FK would fail)
        $user_exists = $this->db->where('id_user', $id_user)->count_all_results('tb_users') > 0;
        if (!$user_exists) {
            return;
        }

        $data = array(
            'id_user'    => $id_user,
            'action'     => $aktivitas,
            'ip_address' => $this->input->ip_address()
        );

        // Optional columns (schema may differ between installs)
        if ($this->db->field_exists('aktivitas', $this->table)) {
            $data['aktivitas'] = $aktivitas;
        }
        if ($this->db->field_exists('keterangan', $this->table)) {
            $data['keterangan'] = $keterangan;
        }
        if ($this->db->field_exists('tanggal', $this->table)) {
            $data['tanggal'] = date('Y-m-d H:i:s');
        }

        $this->db->insert($this->table, $data);
    }
}


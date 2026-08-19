<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mata_kuliah_model extends CI_Model {

    var $table = 'tb_mata_kuliah';
    var $column_order = array(null, 'tb_mata_kuliah.kode_mk', 'tb_mata_kuliah.nama_mk', 'tb_mata_kuliah.sks', 'tb_mata_kuliah.semester', 'tb_mata_kuliah.jenis', 'tb_prodi.nama_prodi', null);
    var $column_search = array('tb_mata_kuliah.kode_mk', 'tb_mata_kuliah.nama_mk', 'tb_prodi.nama_prodi');
    var $order = array('tb_mata_kuliah.id_mk' => 'desc');

    private function _get_datatables_query()
    {
        $this->db->select('tb_mata_kuliah.*, tb_prodi.nama_prodi');
        $this->db->from($this->table);
        $this->db->join('tb_prodi', 'tb_prodi.id_prodi = tb_mata_kuliah.id_prodi');

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

    public function get_by_id($id)
    {
        $this->db->from($this->table);
        $this->db->where('id_mk',$id);
        $query = $this->db->get();
        return $query->row();
    }

    public function save($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Insert atau update berdasarkan kode_mk (unik logis dari Sevima).
     */
    public function upsert_by_kode($data)
    {
        if (empty($data['kode_mk'])) {
            return $this->save($data);
        }

        $existing = $this->db->get_where($this->table, array('kode_mk' => $data['kode_mk']))->row();
        if ($existing) {
            $this->db->where('id_mk', $existing->id_mk)->update($this->table, $data);
            return $existing->id_mk;
        }

        return $this->save($data);
    }

    /**
     * Upsert berdasarkan id_sevima (1 baris API = 1 baris DB), fallback kode+kurikulum.
     */
    public function upsert_from_sevima($data)
    {
        $existing = null;
        if (!empty($data['id_sevima'])) {
            $existing = $this->db->get_where($this->table, array('id_sevima' => $data['id_sevima']))->row();
        }
        if (!$existing && !empty($data['kode_mk']) && !empty($data['id_kurikulum']) && $this->db->field_exists('id_kurikulum', $this->table)) {
            $existing = $this->db->get_where($this->table, array(
                'kode_mk' => $data['kode_mk'],
                'id_kurikulum' => $data['id_kurikulum'],
            ))->row();
        }
        // Jangan fallback ke kode_mk saja: bisa menggabungkan kurikulum berbeda jadi 1 baris

        if ($existing) {
            $this->db->where('id_mk', $existing->id_mk)->update($this->table, $data);
            return $existing->id_mk;
        }

        return $this->save($data);
    }

    public function update($where, $data)
    {
        $this->db->update($this->table, $data, $where);
        return $this->db->affected_rows();
    }

    public function delete_by_id($id)
    {
        $this->db->where('id_mk', $id);
        $this->db->delete($this->table);
    }
}

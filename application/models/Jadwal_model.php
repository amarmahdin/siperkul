<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal_model extends CI_Model {

    var $table = 'tb_jadwal';
    var $column_order = array(null, 'tb_prodi.nama_prodi', 'tb_mata_kuliah.nama_mk', 'tb_jadwal.kelas', 'tb_dosen.nama', 'tb_jadwal.hari', 'tb_ruangan.nama_ruangan', 'tb_jadwal.kapasitas_mhs', null);
    var $column_search = array('tb_prodi.nama_prodi', 'tb_mata_kuliah.nama_mk', 'tb_mata_kuliah.kode_mk', 'tb_jadwal.kelas', 'tb_dosen.nama', 'tb_ruangan.nama_ruangan', 'tb_jadwal.hari');
    var $order = array('tb_jadwal.id_jadwal' => 'desc');

    private function _get_datatables_query()
    {
        $this->db->select('tb_jadwal.*, tb_prodi.nama_prodi, tb_mata_kuliah.kode_mk, tb_mata_kuliah.nama_mk, tb_mata_kuliah.id_kurikulum, tb_dosen.nama as nama_dosen, tb_ruangan.nama_ruangan, tb_ruangan.kapasitas_kuliah, tb_tahun_akademik.tahun_akademik, tb_tahun_akademik.semester');
        $this->db->from($this->table);
        $this->db->join('tb_prodi', 'tb_prodi.id_prodi = tb_jadwal.id_prodi');
        $this->db->join('tb_mata_kuliah', 'tb_mata_kuliah.id_mk = tb_jadwal.id_mk');
        $this->db->join('tb_dosen', 'tb_dosen.id_dosen = tb_jadwal.id_dosen');
        $this->db->join('tb_ruangan', 'tb_ruangan.id_ruangan = tb_jadwal.id_ruangan');
        $this->db->join('tb_tahun_akademik', 'tb_tahun_akademik.id_ta = tb_jadwal.id_ta');

        // Only show for active TA
        $this->db->where('tb_tahun_akademik.status', 1);

        $id_kurikulum = $this->input->post('id_kurikulum');
        if ($id_kurikulum !== null && $id_kurikulum !== '') {
            $this->db->where('tb_mata_kuliah.id_kurikulum', $id_kurikulum);
        }

        // Viewer (dosen) hanya melihat jadwal yang diampu sendiri
        if ($this->session->userdata('role') === 'Viewer') {
            $id_dosen = $this->session->userdata('id_dosen');
            $this->db->where('tb_jadwal.id_dosen', $id_dosen ? $id_dosen : 0);
        }

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
        $this->db->join('tb_tahun_akademik', 'tb_tahun_akademik.id_ta = tb_jadwal.id_ta');
        $this->db->where('tb_tahun_akademik.status', 1);
        if ($this->session->userdata('role') === 'Viewer') {
            $id_dosen = $this->session->userdata('id_dosen');
            $this->db->where('tb_jadwal.id_dosen', $id_dosen ? $id_dosen : 0);
        }
        return $this->db->count_all_results();
    }

    public function get_by_id($id)
    {
        $this->db->from($this->table);
        $this->db->where('id_jadwal',$id);
        $query = $this->db->get();
        return $query->row();
    }

    public function save($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($where, $data)
    {
        $this->db->update($this->table, $data, $where);
        return $this->db->affected_rows();
    }

    public function delete_by_id($id)
    {
        $this->db->where('id_jadwal', $id);
        $this->db->delete($this->table);
    }

    public function get_for_export($id_ta, $id_kurikulum = null)
    {
        $this->db->select('tb_jadwal.*, tb_prodi.nama_prodi, tb_mata_kuliah.kode_mk, tb_mata_kuliah.nama_mk, tb_mata_kuliah.sks, tb_mata_kuliah.id_kurikulum, tb_dosen.nama as nama_dosen, tb_ruangan.nama_ruangan, tb_ruangan.kode_ruangan');
        $this->db->from($this->table);
        $this->db->join('tb_prodi', 'tb_prodi.id_prodi = tb_jadwal.id_prodi');
        $this->db->join('tb_mata_kuliah', 'tb_mata_kuliah.id_mk = tb_jadwal.id_mk');
        $this->db->join('tb_dosen', 'tb_dosen.id_dosen = tb_jadwal.id_dosen');
        $this->db->join('tb_ruangan', 'tb_ruangan.id_ruangan = tb_jadwal.id_ruangan');
        $this->db->where('tb_jadwal.id_ta', (int) $id_ta);
        $this->db->where('tb_jadwal.status', 'Aktif');
        if ($id_kurikulum !== null && $id_kurikulum !== '') {
            $this->db->where('tb_mata_kuliah.id_kurikulum', $id_kurikulum);
        }
        if ($this->session->userdata('role') === 'Viewer') {
            $id_dosen = $this->session->userdata('id_dosen');
            $this->db->where('tb_jadwal.id_dosen', $id_dosen ? $id_dosen : 0);
        }
        $this->db->order_by("FIELD(tb_jadwal.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')", '', false);
        $this->db->order_by('tb_jadwal.jam_mulai', 'ASC');
        return $this->db->get()->result();
    }

    public function find_existing($id_prodi, $id_mk, $kelas, $hari, $jam_mulai, $id_ta, $jenis_kuliah = null)
    {
        $where = array(
            'id_prodi' => $id_prodi,
            'id_mk' => $id_mk,
            'kelas' => $kelas,
            'hari' => $hari,
            'jam_mulai' => $jam_mulai,
            'id_ta' => $id_ta,
        );
        // Kuliah vs Praktikum di slot sama = entri berbeda
        if ($jenis_kuliah !== null && $jenis_kuliah !== '' && $this->db->field_exists('jenis_kuliah', $this->table)) {
            $where['jenis_kuliah'] = $jenis_kuliah;
        }
        return $this->db->get_where($this->table, $where)->row();
    }

    // CLASH DETECTION ENGINES
    
    public function cek_bentrok_ruang($id_ruangan, $hari, $jam_mulai, $jam_selesai, $id_ta, $id_jadwal = null)
    {
        $this->db->where('id_ruangan', $id_ruangan);
        $this->db->where('hari', $hari);
        $this->db->where('id_ta', $id_ta);
        $this->db->where('jam_mulai <', $jam_selesai);
        $this->db->where('jam_selesai >', $jam_mulai);
        
        if($id_jadwal) {
            $this->db->where('id_jadwal !=', $id_jadwal);
        }
        
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    public function cek_bentrok_dosen($id_dosen, $hari, $jam_mulai, $jam_selesai, $id_ta, $id_jadwal = null)
    {
        $this->db->where('id_dosen', $id_dosen);
        $this->db->where('hari', $hari);
        $this->db->where('id_ta', $id_ta);
        $this->db->where('jam_mulai <', $jam_selesai);
        $this->db->where('jam_selesai >', $jam_mulai);
        
        if($id_jadwal) {
            $this->db->where('id_jadwal !=', $id_jadwal);
        }
        
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    public function cek_bentrok_kelas($id_prodi, $kelas, $hari, $jam_mulai, $jam_selesai, $id_ta, $id_jadwal = null)
    {
        $this->db->where('id_prodi', $id_prodi);
        $this->db->where('kelas', $kelas);
        $this->db->where('hari', $hari);
        $this->db->where('id_ta', $id_ta);
        $this->db->where('jam_mulai <', $jam_selesai);
        $this->db->where('jam_selesai >', $jam_mulai);
        
        if($id_jadwal) {
            $this->db->where('id_jadwal !=', $id_jadwal);
        }
        
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }
}

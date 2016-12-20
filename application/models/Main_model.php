<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// echo '<br>' . $this->db->last_query() . '<br>';

Class main_model extends CI_Model
{
    function select($id)
    {
       $this->db->select('*');
       $this->db->from('table');
       $this->db->where('id', $id);
       $query = $this->db->get();
       return = $query->result_array();
    }

    function insert($column)
    {
        $data = array(
            'column' => $column,
        );
        $this->db->insert('table', $data);
    }

    function update($id, $column)
    {
        $data = array(
            'column' => $column,
            'modified' => date('Y-m-d H:i:s', time())
        );
        $this->db->where('id', $id);
        $this->db->update('table', $data);
    }

    function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('table');
    }
}
?>
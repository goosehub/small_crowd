<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// echo '<br>' . $this->db->last_query() . '<br>';

Class main_model extends CI_Model
{
    function select_row($table, $id)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    function insert_row($table, $column)
    {
        $data = array(
            'column' => $column,
        );
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    function update_row($table, $id, $column)
    {
        $data = array(
            'column' => $column,
            'modified' => date('Y-m-d H:i:s', time())
        );
        $this->db->where('id', $id);
        $this->db->update($table, $data);
    }

    function delete_row($table, $id)
    {
        $this->db->where('id', $id);
        $this->db->delete($table);
    }

    function get_user_by_id($id)
    {
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    }

    function remove_user_by_id($user_id)
    {
        $this->db->where('id', $user_id);
        $this->db->delete('user');
    }

    function get_room_by_id($id)
    {
        $this->db->select('*');
        $this->db->from('room');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    }

    function get_room_by_slug($slug)
    {
        $this->db->select('*');
        $this->db->from('room');
        $this->db->where('slug', $slug);
        $query = $this->db->get();
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    }

    function get_available_room($room_capacity)
    {
        $room_capacity = (int) $room_capacity;
        $query = $this->db->query("
            SELECT room.* 
            FROM room
            LEFT JOIN user
                ON room.id = user.room_key
            GROUP BY room.id
            HAVING COUNT(user.id) < " . $room_capacity . ";"
            );
        $result = $query->result_array();
        return isset($result[0]) ? $result[0] : false;
    }

    function create_room($slug)
    {
        $data = array(
            'slug' => $slug,
            'last_load' => date('Y-m-d H:i:s', time()),
            'modified' => date('Y-m-d H:i:s', time()),
        );
        $this->db->insert('room', $data);
        return $this->db->insert_id();
    }

    function create_user($room_key, $username, $location, $color, $ip)
    {
        $data = array(
            'room_key' => $room_key,
            'username' => $username,
            'location' => $location,
            'color' => $color,
            'ip' => $ip,
            'last_load' => date('Y-m-d H:i:s', time()),
            'modified' => date('Y-m-d H:i:s', time()),
        );
        $this->db->insert('user', $data);
        return $this->db->insert_id();
    }

    function update_user_last_load($user_id)
    {
        $data = array(
            'last_load' => date('Y-m-d H:i:s', time()),
            'modified' => date('Y-m-d H:i:s', time())
        );
        $this->db->where('id', $user_id);
        $this->db->update('user', $data);
    }

    function users_missing($missing_wait_seconds)
    {
        $query = $this->db->query("
            SELECT *
            FROM `user`
            WHERE `last_load` < (now() - INTERVAL " . $missing_wait_seconds . " SECOND);
        ");
        return $query->result_array();
    }
}
?>
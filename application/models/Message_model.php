<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Use this where needed for debugging
    // echo '<br>' . $this->db->last_query() . '<br>';

Class message_model extends CI_Model
{
  // Load message
  function load_message_by_limit($room_key, $limit)
  {
    $this->db->select('*');
    $this->db->from('message');
    $this->db->where('room_key', $room_key);
    $this->db->limit($limit);
    $this->db->order_by('id', 'desc');
    $query = $this->db->get();
    $result = $query->result_array();
    return $result;
  }
  // Insert new message
  function new_message($user_key, $username, $color, $message, $room_key)
  {
    $data = array(
    'user_key' => $user_key,
    'username' => $username,
    'color' => $color,
    'message' => $message,
    'room_key' => $room_key
    );
    $this->db->insert('message', $data);
  }
  // message spam check
  function recent_messages($user_key, $message_limit_length)
  {
    $query = $this->db->query("
        SELECT COUNT(id) as recent_messages
        FROM `message`
        WHERE `user_key` = '" . $user_key . "'
        AND `timestamp` > (now() - INTERVAL " . $message_limit_length . " SECOND);
    ");
    $result = $query->result_array();
    return isset($result[0]['recent_messages']) ? $result[0]['recent_messages'] : false;
  }
}
?>
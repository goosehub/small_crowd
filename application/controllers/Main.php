<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    public $room_capacity;
    public $system_user_id;
    public $system_welcome_slug;

    function __construct()
    {
        parent::__construct();
        // Uncomment after creating database
        $this->load->model('main_model', '', TRUE);
        $this->room_capacity = 4;
        $this->system_user_id = 0;
        $this->system_welcome_slug = 'welcome';
        $this->system_leave_slug = 'leave';
    }

    public function start()
    {
        $data['page_title'] = site_name();
        $this->load->view('template/header', $data);
        $this->load->view('start', $data);
        $this->load->view('template/footer', $data);
    }

    public function join_room()
    {
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'username', 'trim|max_length[32]');
        $this->form_validation->set_rules('location', 'location', 'trim|max_length[64]');
        
        // Fail
        if ($this->form_validation->run() == FALSE) {
            // Set fail message and redirect to map
            $this->session->set_flashdata('validation_errors', validation_errors());
            header('Location: ' . base_url() . 'error');
            return false;
            exit();
        }

        // Input
        $username = $this->input->post('username');
        if (!$username) {
            $username = 'Someone';
        }
        $location = $this->input->post('location');
        if (!$location) {
            $location = 'Somewhere';
        }
        $color = $this->input->post('color');
        $ip = $_SERVER['REMOTE_ADDR'];

        // Look for room
        $available_room = $this->main_model->get_available_room($this->room_capacity);

        // If no room, make one
        if (empty($available_room)) {
            $slug = uniqid();
            $available_room_id = $this->main_model->create_room($slug);
            $available_room = $this->main_model->get_room_by_id($available_room_id);
        }
        $room_key = $available_room['id'];

        // User added to room
        $user_id = $this->main_model->create_user($available_room['id'], $username, $location, $color, $ip);
        
        $sess_array = array(
            'id' => $user_id,
            'room_key' => $room_key,
            'username' => $username,
            'color' => $color
        );
        $this->session->set_userdata('user_session', $sess_array);

        // System Welcome Message
        $message = 'Welcome ' . $username . ' from ' . $location;
        $result = $this->main_model->new_message($this->system_user_id, $this->system_welcome_slug, '#000000', $message, $room_key);

        header('Location: ' . 'room/' . $available_room['slug']);
        exit();
    }

    public function room($slug)
    {
        $data['room'] = $this->main_model->get_room_by_slug($slug);
        $user = $this->get_user_by_session();
        if ($user['room_key'] != $data['room']['id']) {
            header('Location: ' . base_url());
            return false;
        }
        $data['load_interval'] = 1 * 1000;
        if (is_dev()) {
            $data['load_interval'] = 4 * 1000;
        }
        $data['page_title'] = site_name();
        $this->load->view('template/header', $data);
        $this->load->view('main', $data);
        $this->load->view('script', $data);
        $this->load->view('template/footer', $data);
    }

    // Load messages
    public function load()
    {
        // In dev, this replaces cron remove_inactive_users
        if (is_dev()) {
            $this->remove_inactive_users();
            $this->remove_inactive_rooms();
        }

        // Set parameters
        $room_key = $this->input->post('room_key');
        $inital_load = $this->input->post('inital_load');
        if ($inital_load) {
            $limit = 50;
        }
        else {
            $limit = 5;
        }

        $user = $this->get_user_by_session();
        if (!$user) {
            $error_message['error'] = 'Your session has expired';
            echo json_encode($error_message);
            return false;
        }
        if ($user['room_key'] != $room_key) {
            $error_message['error'] = 'This is not your room';
            echo json_encode($error_message);
            return false;
        }

        // Update user last load
        $this->main_model->update_user_last_load($user['id']);
        $this->main_model->update_room_last_load($user['room_key']);

        // Get messages
        $messages = $this->main_model->load_message_by_limit($room_key, $limit);

        // Reverse array for ascending order (Could refactor into sql)
        $messages = array_reverse($messages);

        echo json_encode($messages);
    }

    // For new messages
    public function new_message()
    {
        $user = $this->get_user_by_session();
        if (!$user) {
            echo 'Your session has expired';
            return false;
        }
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('message_input', 'Message', 'trim|required|max_length[1000]');

        if ($this->form_validation->run() == FALSE) {
            echo validation_errors();
            return false;
        }
        $user = $this->main_model->get_user_by_id($user['id']);
        if (empty($user)) {
            echo 'Your session has expired';
            return false;
        }

        // Set variables
        $message = htmlspecialchars($this->input->post('message_input'));

        // Insert message
        $result = $this->main_model->new_message($user['id'], $user['username'], $user['color'], $message, $user['room_key']);
        return true;
    }

    // Message Callback
    public function new_message_validation()
    {
        $user = $this->get_user_by_session();
        if (!$user) {
            return false;
        }
        // Limit number of new messages in a timespan
        $message_spam_limit_amount = 8;
        $message_spam_limit_length = 60;
        $recent_messages = $this->main_model->recent_messages($user['id'], $message_spam_limit_length);
        if (!is_dev() && $recent_messages > $message_spam_limit_amount) {
            echo 'Your talking too much';
            return false;
        }

        return true;
    }

    public function about()
    {
        $data['page_title'] = site_name();
        $this->load->view('template/header', $data);
        $this->load->view('template/footer', $data);
    }

    public function error()
    {
        $data['validation_errors'] = $this->session->flashdata('validation_errors');
        if (!$data['validation_errors']) {
            $data['validation_errors'] = 'Something went wrong';
        }
        $data['page_title'] = site_name();
        $this->load->view('template/header', $data);
        $this->load->view('error', $data);
        $this->load->view('template/footer', $data);
    }

    public function cron($cron_token = false)
    {
        // Use hash equals function to prevent timing attack
        if (!$cron_token) {
            $this->load->view('errors/page_not_found');
            return false;
        }
        $token = '1234';
        if ( !hash_equals($token, $cron_token) ) {
            $this->load->view('errors/page_not_found');
            return false;
        }
        echo 'Running Cron - ';

        $this->remove_inactive_users();
        $this->remove_inactive_rooms();

        echo 'End Cron - ';
    }

    public function remove_inactive_users()
    {
        $inactive_user_wait_seconds = 1 * 60;
        $inactive_users = $this->main_model->inactive_users($inactive_user_wait_seconds);

        foreach ($inactive_users as $user) {
            echo 'User Being Deleted - ';
            echo '<pre>'; print_r($user); echo '</pre>';
            $this->main_model->remove_user_by_id($user['id']);

            // System Leave Message
            $message = $user['username'] . ' has left';
            $result = $this->main_model->new_message($this->system_user_id, $this->system_leave_slug, '#000000', $message, $user['room_key']);
        }
    }

    public function remove_inactive_rooms()
    {
        $inactive_room_wait_seconds = 1 * 60;
        $inactive_rooms = $this->main_model->inactive_rooms($inactive_room_wait_seconds);

        foreach ($inactive_rooms as $room) {
            echo 'Room Being Deleted - ';
            echo '<pre>'; print_r($room); echo '</pre>';
            $this->main_model->remove_room_by_id($room['id']);
        }
    }

    function get_user_by_session()
    {
        $user_session = $this->session->userdata('user_session');
        if (!$user_session) {
            return false;
        }
        $user = $this->main_model->get_user_by_id($user_session['id']);
        if (empty($user)) {
            return false;
        }
        return $user;
    }
}

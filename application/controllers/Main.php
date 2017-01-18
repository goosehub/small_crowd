<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        // Uncomment after creating database
        $this->load->model('main_model', '', TRUE);
        $this->room_capacity = 4;
        $this->system_user_id = 0;
        $this->system_welcome_slug = 'welcome';
        $this->system_start_room_slug = 'start_room';
        $this->system_leave_slug = 'leave';
        $this->system_archive_room_slug = 'archive_room';
        $this->inactive_wait_time = 60;
        if (is_dev()) {
            $this->inactive_wait_time = 15;
        }
    }

    public function start()
    {
        $data['page_title'] = site_name();
        $data['error'] = ucfirst(str_replace('_', ' ', $this->input->get('error')));
        $this->load->view('template/header', $data);
        $this->load->view('start', $data);
        $this->load->view('template/footer', $data);
    }

    public function join_start($slug)
    {
        $data['page_title'] = site_name();
        $data['room'] = $this->main_model->get_room_by_slug($slug);
        if (empty($data['room'])) {
            header('Location: ' . base_url() . '?error=room_not_found');
            return false;
        }
        $this->load->view('template/header', $data);
        $this->load->view('join_start', $data);
        $this->load->view('template/footer', $data);
    }

    public function join_room($slug = false)
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
        if (strpos($color, '#') === false) {
            $color = '#' . $color;
        }
        $ip = $_SERVER['REMOTE_ADDR'];

        // If room slug is passed, use that room
        if ($slug) {
            $available_room = $this->main_model->get_room_by_slug($slug);
            if (empty($available_room)) {
                header('Location: ' . base_url() . '?error=room_not_found');
                return false;
            }
        }
        // Else, look for room
        else {
            $available_room = $this->main_model->get_available_room($this->room_capacity);

            // If no room available, make a new one
            if (empty($available_room)) {
                $slug = uniqid();
                $available_room_id = $this->main_model->create_room($slug);
                $available_room = $this->main_model->get_room_by_id($available_room_id);

                // System Start Room Message
                $message = $this->system_start_room_message();
                $result = $this->main_model->new_message($this->system_user_id, $this->system_start_room_slug, '#000000', $message, $available_room['id']);
            }
        }

        // User added to room
        $username = htmlspecialchars($username);
        $location = htmlspecialchars($location);
        $color = htmlspecialchars($color);
        $user_id = $this->main_model->create_user($available_room['id'], $username, $location, $color, $ip);
        
        $sess_array = array(
            'id' => $user_id,
            'room_key' => $available_room['id'],
            'username' => $username,
            'color' => $color
        );
        $this->session->set_userdata($available_room['slug'], $sess_array);

        // System Welcome Message
        $message = 'Welcome ' . $username . ' from ' . $location;
        $result = $this->main_model->new_message($this->system_user_id, $this->system_welcome_slug, '#000000', $message, $available_room['id']);

        header('Location: ' . base_url() . 'room/' . $available_room['slug']);
        return true;
    }

    public function room($slug)
    {
        $data['room'] = $this->main_model->get_room_by_slug($slug);
        $user = $this->get_user_by_session($slug);
        if (empty($data['room'])) {
            header('Location: ' . base_url() . '?error=room_not_found');
            return false;
        }
        if ($user['room_key'] != $data['room']['id']) {
            header('Location: ' . base_url() . 'join_start/' . $slug);
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
        // In dev, this replaces cron archive_inactive_users
        if (is_dev()) {
            $this->archive_inactive_users();
            $this->archive_inactive_rooms();
        }

        // Set parameters
        $room_key = $this->input->post('room_key');
        $slug = $this->input->post('slug');
        $inital_load = $this->input->post('inital_load');
        if ($inital_load) {
            $limit = 100;
        }
        else {
            $limit = 5;
        }

        $user = $this->get_user_by_session($slug);
        if (!$user || $user['archived']) {
            $error_message['error'] = 'Your session has expired';
            echo json_encode($error_message);
            return false;
        }
        if ($user['room_key'] != $room_key) {
            // This shouldn't happen, so we'll give a more standard handling of it
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
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('message_input', 'Message', 'trim|max_length[3000]|callback_new_message_validation');

        if ($this->form_validation->run() == FALSE) {
            echo validation_errors();
            return false;
        }

        // Set variables
        $user = $this->get_user_by_session($this->input->post('slug'));
        $message = htmlspecialchars($this->input->post('message_input'));

        // Insert message
        $result = $this->main_model->new_message($user['id'], $user['username'], $user['color'], $message, $user['room_key']);
    }

    // Message Callback
    public function new_message_validation()
    {
        // This shouldn't happen except by malicious means, but handle gracefully just in case
        $user = $this->get_user_by_session($this->input->post('slug'));
        if (!$user || $user['archived']) {
            $this->form_validation->set_message('new_message_validation', 'Your session has expired');
            return false;
        }
        if (!$this->input->post('message_input')) {
            $this->form_validation->set_message('new_message_validation', '');
            return false;
        }
        // Limit number of new messages in a timespan
        $message_spam_limit_amount = 10;
        $message_spam_limit_length = 60;
        $recent_messages = $this->main_model->recent_messages($user['id'], $message_spam_limit_length);
        if (!is_dev() && $recent_messages > $message_spam_limit_amount) {
            $this->form_validation->set_message('new_message_validation', 'Your talking too much');
            return false;
        }

        return true;
    }

    public function about()
    {
        $data['page_title'] = site_name();
        $this->load->view('template/header', $data);
        $this->load->view('about', $data);
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
        $auth = auth();
        $token = $auth->token;
        if (is_dev()) {
            $token = '1234';
        }
        if ( !hash_equals($token, $cron_token) ) {
            $this->load->view('errors/page_not_found');
            return false;
        }
        echo 'Running Cron - ';

        $this->archive_inactive_users();
        $this->archive_inactive_rooms();

        echo 'End Cron - ';
    }

    public function archive_inactive_users()
    {
        $inactive_users = $this->main_model->inactive_users($this->inactive_wait_time);

        foreach ($inactive_users as $user) {
            $this->main_model->archive_user_by_id($user['id']);

            // System Leave Message
            $message = $user['username'] . ' has left';
            $result = $this->main_model->new_message($this->system_user_id, $this->system_leave_slug, '#000000', $message, $user['room_key']);
        }
    }

    public function archive_inactive_rooms()
    {
        $inactive_rooms = $this->main_model->inactive_rooms($this->inactive_wait_time);

        foreach ($inactive_rooms as $room) {
            // Archive room
            $this->main_model->archive_room_by_id($room['id']);

            // System Archive Message
            $message = 'This room has been archived. No new users will be placed into this room, but this room can still be accessed by direct URL.';
            $result = $this->main_model->new_message($this->system_user_id, $this->system_archive_room_slug, '#000000', $message, $room['id']);
        }
    }

    function get_user_by_session($slug)
    {
        $user_session = $this->session->userdata($slug);
        if (!$user_session) {
            return false;
        }
        $user = $this->main_model->get_user_by_id($user_session['id']);
        if (empty($user)) {
            return false;
        }
        return $user;
    }

    function system_start_room_message()
    {
        $message = "";
        $message .= "Welcome! You&#39;re the first! Others will join soon. Some tips: Embed Youtube, Vimeo, Twitch, Vocaroo, and Images by posting the URL. Pin posts to keep in view as you chat. Share this url to invite others to join directly.";
        return $message;
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    public $room_capacity;

    function __construct()
    {
        parent::__construct();
        // Uncomment after creating database
        $this->load->model('main_model', '', TRUE);
        $this->load->model('message_model', '', TRUE);
        $this->room_capacity = 4;
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
        if (!$username) {
            $username = 'Somewhere';
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

        // User added to room
        $user_id = $this->main_model->create_user($available_room['id'], $username, $location, $color, $ip);
        
        $sess_array = array(
            'id' => $user_id,
            'username' => $username,
            'color' => $color
        );
        $this->session->set_userdata('logged_in', $sess_array);

        header('Location: ' . 'room/' . $available_room['slug']);
        exit();
    }

    public function room($slug)
    {
        $data['room'] = $this->main_model->get_room_by_slug($slug);
        $data['load_interval'] = 1 * 1000;
        if (is_dev()) {
            $data['load_interval'] = 3 * 1000;
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
        // Set parameters
        $room_key = $this->input->post('room_key');
        $inital_load = $this->input->post('inital_load');
        if ($inital_load) {
            $limit = 50;
        }
        else {
            $limit = 5;
        }

        // Get messages
        $messages = $this->message_model->load_message_by_limit($room_key, $limit);

        // Reverse array for ascending order (Could refactor into sql)
        $messages = array_reverse($messages);

        echo json_encode($messages);
    }

    // For new messages
    public function new_message()
    {
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('room_key', 'World Key', 'trim|required|integer|max_length[10]|callback_new_message_validation');
        $this->form_validation->set_rules('message_input', 'Message', 'trim|required|max_length[1000]');
        // $this->form_validation->set_rules('token', 'Token', 'trim|max_length[1000]');

        if ($this->form_validation->run() == FALSE) {
            echo validation_errors();
            return false;
        }
        // Authentication
        if (!$this->session->userdata('logged_in')) {
            echo 'Your session has expired';
            return false;
        }
        $session_data = $this->session->userdata('logged_in');
        $user_id = $data['user_id'] = $session_data['id'];
        $data['user'] = $this->main_model->get_user_by_id($user_id);

        // Set variables
        $room_key = $this->input->post('room_key');
        $username = $data['user']['username'];
        $color = $data['user']['color'];
        $message = htmlspecialchars($this->input->post('message_input'));

        // Insert message
        $result = $this->message_model->new_message($user_id, $username, $color, $message, $room_key);
        return true;
    }

    // Message Callback
    public function new_message_validation()
    {
        // Authentication
        if ($this->session->userdata('logged_in')) {
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
        } else {
            return false;
        }
        // Limit number of new messages in a timespan
        $message_spam_limit_amount = 8;
        $message_spam_limit_length = 60;
        $recent_messages = $this->message_model->recent_messages($user_id, $message_spam_limit_length);
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
}

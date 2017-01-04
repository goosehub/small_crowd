<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');

class Message extends CI_Controller {

	function __construct() {
	    parent::__construct();
        $this->load->model('main_model', '', TRUE);
        $this->load->model('message_model', '', TRUE);
	}

	// Load messages
	public function load()
	{
        // Set parameters
        $room_key = $this->input->post('room_key');
        $inital_load = $this->input->post('inital_load');
        if ($inital_load) {
            $limit = 100;
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
        $room_key = $_POST['room_key'];
        $username = $data['user']['username'];
        $color = $data['user']['color'];
        $message = htmlspecialchars($_POST['message_input']);

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

}
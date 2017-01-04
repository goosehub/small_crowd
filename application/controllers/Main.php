<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    public $room_capacity;

    function __construct()
    {
        parent::__construct();
        // Uncomment after creating database
        $this->load->model('main_model', '', TRUE);
        $this->room_capacity = 4;
    }

    public function start()
    {
        $data['page_title'] = site_name();
        $this->load->view('template/header', $data);
        $this->load->view('start', $data);
        $this->load->view('template/footer', $data);
    }

    public function new_room()
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
        $color = '#000000';
        $ip = $_SERVER['REMOTE_ADDR'];

        // Look for room
        $available_room = $this->main_model->get_available_room();

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
            'username' => $username
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

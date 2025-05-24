<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Plan_config extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            //Configuration Data
            $this->db->select('*');
            $this->db->from("planning_config");
            $records = $this->db->get()->row();
            $data['config'] = $records;

            $this->load->view('template/header', $data);
            $this->load->view('planning/plan_config');
        } else {
            redirect('error_access');
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $arrayLogo = array();

            // if ($_FILES['logo']["name"] != "") {
            //     $uploadLogo = $this->api->upload('logo', ['png', 'jpg', 'jpeg'], 'assets/image/config/');
            //     $arrayLogo = array('logo' => $uploadLogo);
            // }

            // $post_final = array_merge($post, $arrayLogo);
            $send = $this->db->update("planning_config", $post);
            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}

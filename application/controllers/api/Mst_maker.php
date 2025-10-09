<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Mst_maker extends CI_Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->load->library('Ciqrcode');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_maker');
        } else {
            redirect('error_access');
        }
    }

    public function readMaker()
    {
        $send = $this->api->query("SELECT * FROM mst_maker");
        $result['total'] = count($send);
        $result = array_merge($result, ['rows' => $send]);
        die(json_encode($result));
    }
}

<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->username)) { redirect('error_session'); }
        else { redirect('error_access'); }
    }

    public function readAll()
    {
        $this->db->distinct()->select("TRIM(number) AS supplier_code, TRIM(name) AS supplier_name, TRIM(type) AS supplier_type")
        ->from("suppliers")->order_by("supplier_code");
        // die($this->db->get_compiled_select());
        echo json_encode($this->db->get()->result());
    }
}

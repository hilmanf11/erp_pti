<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_contracts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('email');
        $this->load->model('api');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function dataInput($table='')
    {
        $result = [];

        if (!empty($table)) {
            if ($table == 'plant') {
                $this->db->distinct()->select(
                    "TRIM(id) AS id,".
                    "TRIM(name) AS name"
                )->from("plants")->order_by("name");
            } elseif ($table == 'prodfam') {
                $this->db->distinct()->select(
                    "TRIM(id) AS id,".
                    "TRIM(name) AS name"
                )->from("mst_item_family")->order_by("name");
            } elseif ($table == 'part_no') {
                $this->db->distinct()->select("TRIM(item_id) AS item")
                ->from("mst_item")->order_by("item");
            } elseif ($table == 'maker') {
                $this->db->distinct()->select(
                    "TRIM(number) AS id,".
                    "TRIM(name) AS name"
                )->from("mst_maker")->order_by("name");
            } elseif ($table == 'supp_part_no') {
                $this->db->distinct()->select("*")->from("supplier_items")->order_by("item_id");
            }
            $result = $this->db->get()->result();
        }

        echo json_encode($result);
    }
}

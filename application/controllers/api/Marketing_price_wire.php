<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Marketing_price_wire extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
    }

    public function index($param = '')
    {
        $data = [];

        $this->db->distinct()->select(
             "'GS' AS plant_id,"
            ."a.*,"
            ."COALESCE(TRIM(b.name),'') AS supp_name,"
            ."COALESCE(TRIM(c.name),'') AS maker_name"
        )->from("marketing_price_wire a")
            ->join("suppliers b", "a.supp_id = b.number")
            ->join("mst_maker c", "a.maker_code = c.number");
        if (!empty($param)) { $this->db->like('a.item_id', base64_decode($param)); }
        $get = $this->db->get()->result_array();
        // die($this->db->last_query());
        foreach ($get as $index => $row) {
            $row['price'] = floatval($row['price']);
            $row['duty_price'] = floatval(number_format(floatval($row['import_duty']) * $row['price'], 4));
            $row['insurance_price'] = floatval(number_format(floatval($row['insurance']) * $row['price'], 4));
            $row['transport_price'] = floatval(number_format(floatval($row['transportation']) * $row['price'], 4));
            $row['total_price'] = floatval(number_format($row['price'] + $row['duty_price'] + $row['insurance_price'] + $row['transport_price'], 4));
            array_push($data, $row);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}

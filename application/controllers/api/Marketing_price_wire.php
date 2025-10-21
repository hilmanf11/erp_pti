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
            ."TRIM(a.item_id) AS item,"
            ."TRIM(a.ven_id) AS supplier,"
            ."COALESCE(TRIM(b.type),'') AS type,"
            ."TRIM(a.maker_code) AS maker,"
            ."TRIM(a.data_lme) AS lme,"
            ."COALESCE(TRIM(c.percentage),0) AS duty,"
            ."COALESCE(TRIM(d.percentage),0) AS insurance,"
            ."COALESCE(TRIM(e.percentage),0) AS transport,"
            ."COALESCE(TRIM(b.name),'') AS supp_name,"
            ."COALESCE(TRIM(f.name),'') AS maker_name"
        )->from("lme_prices a")
            ->join("suppliers b", "a.ven_id = b.number")
            ->join("mst_import_duty c", "b.id = c.supplier_id AND a.item_id = c.item_id", "left")
            ->join("mst_assurance d", "b.type = d.type")
            ->join("mst_transportation e", "b.type = e.type")
            ->join("mst_maker f", "a.maker_code = f.number")
        ->order_by('item');
        $get = $this->db->get()->result_object();
        // die($this->db->last_query());
        // die(var_dump($get));
        foreach ($get as $index => $row) {
            $ranges = json_decode($row->lme);
            foreach ($ranges as $key => $value) {
                $price = floatval($value->price);
                $duty_price = floatval(number_format((floatval($row->duty) / 100) * $price, 4));
                $insurance_price = floatval(number_format(floatval($row->insurance) * $price, 4));
                $transport_price = floatval(number_format(floatval($row->transport) * $price, 4));
                $total_price = floatval(number_format($price + $duty_price + $insurance_price + $transport_price, 4));
                array_push($data, [
                    "plant_id" => $row->plant_id,
                    "item_id" => $row->item,
                    "supp_id" => $row->supplier,
                    "supp_name" => $row->supp_name,
                    "type" => $row->type,
                    "maker_code" => $row->maker,
                    "maker_name" => $row->maker_name,
                    "ranges" => $value->range,
                    "price" => $price,
                    "duty_price" => $duty_price,
                    "insurance_price" => $insurance_price,
                    "transport_price" => $transport_price,
                    "total_price" => $total_price
                ]);
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}

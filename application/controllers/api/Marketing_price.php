<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');



class Marketing_price extends CI_Controller

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

            $this->load->view('planning/marketing_price');
        } else {

            redirect('error_access');
        }
    }



    public function readTotalPrice()

    {

        // if ($this->input->get()) {
        $filter_plant  = base64_decode($this->input->get('plant'));
        $filter_suppliers  = base64_decode($this->input->get('suppliers'));
        $filter_category  = base64_decode($this->input->get('category'));
        $filter_maker  = base64_decode($this->input->get('maker'));
        $filter_items = base64_decode($this->input->get('items'));
        $filter_item_vendor = base64_decode($this->input->get('item_vendor'));
        $filter_share_order = base64_decode($this->input->get('share_order'));
        $filter_check = $this->input->get('check');

        $result = array();

        $this->db->select('a.*, g.start_date, g.valid_date, g.price, g.currency, c.item_id, c.item_name, c.item_supplier, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.id', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number');
        $this->db->join("(SELECT start_date,supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id)) g", 'a.id = g.supplier_item_id', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_id FROM supplier_items GROUP BY item_id HAVING COUNT(*) > 1) h", 'a.item_id = h.item_id');
        }
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_category != "") {
            $this->db->like('c.pfm_id', $filter_category);
        }
        $this->db->like('a.supplier_id', $filter_suppliers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('f.number', $filter_maker);
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        } else {
            $this->db->where("a.share_order > 0");
        }
        if ($filter_check == "false") {
            $this->db->order_by('b.name', 'ASC');
        }
        $this->db->order_by('a.item_id', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $arr = [];
        foreach ($records as $record) {
            if ($record['type'] == 'IMPORT') {
                $assurance = $this->api->read('mst_assurance', [], ["type" => 'IMPORT']);
                $transportation = $this->api->read('mst_transportation', [], ["type" => 'IMPORT']);
            } else {
                $assurance = $this->api->read('mst_assurance', [], ["type" => 'LOCAL']);
                $transportation = $this->api->read('mst_transportation', [], ["type" => 'LOCAL']);
            }

            $mst_import_duty = $this->api->read('mst_import_duty', [], ["plant" => $record['plant'], "supplier_id" => $record['supplier_id'], "item_id" => $record['item_id']]);
            if (!empty(@$mst_import_duty->percentage)) {
                $import_duty_price = $record['price'] * (@$mst_import_duty->percentage / 100);
            } else {
                $import_duty_price = 0;
            }

            $assurance_price = $record['price'] * @$assurance->percentage;
            $transportation_price = $record['price'] * @$transportation->percentage;
            $total_price = $record['price'] + $assurance_price + $transportation_price + $import_duty_price;
            $arr[] = array(
                "plant" => $record['plant'],
                "supplier_number" => $record['supplier_number'],
                "supplier_name" => $record['supplier_name'],
                "item_supplier" => $record['item_supplier'],
                "item_id" => $record['item_id'],
                "item_name" => $record['item_name'],
                "pfm_name" => $record['pfm_name'],
                "type" => $record['type'],
                "mpq" => $record['mpq'],
                "moq" => $record['moq'],
                "leadtime" => $record['leadtime'],
                "maker_code" => $record['maker_code'],
                "maker_name" => $record['maker_name'],
                "maker_type" => $record['maker_type'],
                "price" => $record['price'],
                "currency" => $record['currency'],
                "start_date" => $record['start_date'],
                "valid_date" => $record['valid_date'],
                "assurance_price" => $assurance_price,
                "transportation_price" => $transportation_price,
                "import_duty_price" => $import_duty_price,
                "import_duty_percentage" => @$mst_import_duty->percentage,
                "total_price" => number_format($total_price, 4, ".", ""),
            );
        }
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);
        die(json_encode($result));
        // } else {

        //     show_error("Cannot Process your Request");
        // }
    }
}

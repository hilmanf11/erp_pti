<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Forecast_supplier extends CI_Controller
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
        $this->load->library('dompdf_gen');
    }
    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/forecast_supplier');
        } else {
            redirect('error_access');
        }
    }
    function readPoSearch()
    {
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->api->query("SELECT DISTINCT purchase_order_no FROM purchase_orders WHERE supplier_id = '$supplier_id'");
        echo json_encode($datas);
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $item_id = trim(base64_decode($this->input->get('item_id')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));
            //Select Query
            $this->db->select('a.*');
            $this->db->from('forecast_supplier a');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.share_order >', 0);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $item_id);
            $this->db->like('a.vendor_id', $filter_supplier);
            // $this->db->group_by('a.id');
            $this->db->order_by('a.vendor_name', 'asc');
            $this->db->order_by('a.product_family', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    // public function datatables()
    // {
    //     if ($this->input->get()) {
    //         $filter_month = base64_decode($this->input->get('filter_month'));
    //         $filter_year = base64_decode($this->input->get('filter_year'));
    //         $filter_revision = base64_decode($this->input->get('filter_revision'));
    //         $filter_product_family = base64_decode($this->input->get('filter_product_family'));
    //         $item_id = trim(base64_decode($this->input->get('item_id')));
    //         $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
    //         $filter_po = trim(base64_decode($this->input->get('filter_po')));
    //         //Select Query
    //         $this->db->select('a.*,
    //                 ((a.need_1 + a.need_2 + a.need_3 + a.need_4) / 4) as need_avg,
    //                 b.number as vendor_id, 
    //                 b.name as vendor_name, 
    //                 b.type as vendor_type, 
    //                 b.origin as vendor_origin, 
    //                 c.share_order,
    //                 d.name as maker_name, 
    //                 d.type as maker_type');
    //         $this->db->from('generate_mrp_finals a');
    //         $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
    //         $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
    //         $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
    //         $this->db->where('a.p_month', $filter_month);
    //         $this->db->where('a.p_year', $filter_year);
    //         $this->db->where('a.revision', $filter_revision);
    //         $this->db->where('c.share_order >', 0);
    //         $this->db->like('a.product_family', $filter_product_family);
    //         $this->db->like('a.part_no', $item_id);
    //         $this->db->like('c.supplier_id', $filter_supplier);
    //         $this->db->group_by('a.id');
    //         $this->db->order_by('b.name', 'asc');
    //         $this->db->order_by('a.product_family', 'asc');
    //         //Total Data
    //         $totalRows = $this->db->count_all_results('', false);
    //         $records = $this->db->get()->result_array();
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $item_id = trim(base64_decode($this->input->get('item_id')));
            //Select Query
            $this->db->select("a.p_month,
                    a.p_year,a.revision,a.part_no, a.part_name, a.product_family, 
                    a.issued_1, a.issued_2, a.issued_3, a.issued_avg, a.qty_rm, a.qty_wip, a.qty_vendor, a.total_stock, a.qty_supply, a.qty_wo,a.total_wo, 
                    a.need_1, a.need_2, a.need_3, a.need_4, a.need_5, a.need_6,
                    a.balance_1, 
                    coalesce(a.balance_1-a.need_2,0) as balance_2, 
                    coalesce(a.balance_2-a.need_3,0) as balance_3, 
                    coalesce(a.balance_3-a.need_4,0) as balance_4, 
                    coalesce(a.balance_4-a.need_5,0) as balance_5, 
                    coalesce(a.balance_5-a.need_6,0) as balance_6,
                    (case when a.balance_1 > 0 then 0  else a.need_1 end) as forecast_1,
                    (case when coalesce(a.balance_1 - a.need_2,0) > 0 then 0  else a.need_2 end) as forecast_2,
                    (case when coalesce(a.balance_2 - a.need_3,0) > 0 then 0  else a.need_3 end) as forecast_3,
                    (case when coalesce(a.balance_3 - a.need_4,0) > 0 then 0  else a.need_4 end) as forecast_4,
                    (case when coalesce(a.balance_4 - a.need_5,0) > 0 then 0  else a.need_5 end) as forecast_5,
                    (case when coalesce(a.balance_5 - a.need_6,0) > 0 then 0  else a.need_6 end) as forecast_6,
                    (case when coalesce(a.avg_balance,0) > 0 then 0  else a.avg_need end) as avg_forecast,
                    a.avg_need, coalesce(a.avg_balance,0) as avg_balance, 
                    a.total_need, a.purchase_order,
                    b.id as vendor_id, 
                    b.name as vendor_name, 
                    b.type as vendor_type, 
                    b.origin as vendor_origin, 
                    c.share_order,
                    c.leadtime,
                    c.mpq,
                    c.moq,
                    d.name as maker_name, 
                    d.type as maker_type");
            $this->db->from('generate_mrp_finals a');
            $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
            $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
            $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('c.share_order >', 0);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $item_id);
            $this->db->order_by('a.part_no', 'asc');
            // $this->db->limit(10);
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Mapping Data
            foreach ($records as $record) {
                $month_from_leadtime = ceil($record['leadtime'] / 30);

                if ($record['maker_type'] == 'IMPORT') { // jika maker type import maka need dimulai m+2 atau need_3
                    if ($month_from_leadtime == 1) {
                        $record_need_1 = $record['need_3'];
                        $record_need_2 = $record['need_4'];
                        $record_need_3 = $record['need_5'];
                        $record_need_4 = $record['need_6'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_3'];
                        $record_balance_2 = $record['balance_4'];
                        $record_balance_3 = $record['balance_5'];
                        $record_balance_4 = $record['balance_6'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_3'];
                        $record_forecast_2 = $record['forecast_4'];
                        $record_forecast_3 = $record['forecast_5'];
                        $record_forecast_4 = $record['forecast_6'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 2) {
                        $record_need_1 = $record['need_4'];
                        $record_need_2 = $record['need_5'];
                        $record_need_3 = $record['need_6'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_4'];
                        $record_balance_2 = $record['balance_5'];
                        $record_balance_3 = $record['balance_6'];
                        $record_balance_4 = $record['avg_balance'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_4'];
                        $record_forecast_2 = $record['forecast_5'];
                        $record_forecast_3 = $record['forecast_6'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 3) {
                        $record_need_1 = $record['need_5'];
                        $record_need_2 = $record['need_6'];
                        $record_need_3 = $record['avg_need'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_5'];
                        $record_balance_2 = $record['balance_6'];
                        $record_balance_3 = $record['avg_balance'];
                        $record_balance_4 = $record['avg_balance'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_5'];
                        $record_forecast_2 = $record['forecast_6'];
                        $record_forecast_3 = $record['avg_forecast'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 4) {
                        $record_need_1 = $record['need_6'];
                        $record_need_2 = $record['avg_need'];
                        $record_need_3 = $record['avg_need'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_6'];
                        $record_balance_2 = $record['avg_balance'];
                        $record_balance_3 = $record['avg_balance'];
                        $record_balance_4 = $record['avg_balance'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_6'];
                        $record_forecast_2 = $record['avg_forecast'];
                        $record_forecast_3 = $record['avg_forecast'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else {
                        $record_need_1 = $record['avg_need'];
                        $record_need_2 = $record['avg_need'];
                        $record_need_3 = $record['avg_need'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['avg_balance'];
                        $record_balance_2 = $record_balance_1 - $record['avg_need'];
                        $record_balance_3 = $record_balance_2 - $record['avg_need'];
                        $record_balance_4 = $record_balance_3 - $record['avg_need'];
                        $record_balance_5 = $record_balance_4 - $record['avg_need'];
                        $record_balance_6 =  $record_balance_5 - $record['avg_need'];
                        $record_forecast_1 = $record['avg_forecast'];
                        $record_forecast_2 = $record['avg_forecast'];
                        $record_forecast_3 = $record['avg_forecast'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    }
                } else { // jika maker type local maka need dimulai m+1 atau need_2
                    if ($month_from_leadtime == 1) {
                        $record_need_1 = $record['need_2'];
                        $record_need_2 = $record['need_3'];
                        $record_need_3 = $record['need_4'];
                        $record_need_4 = $record['need_5'];
                        $record_need_5 = $record['need_6'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_2'];
                        $record_balance_2 = $record['balance_3'];
                        $record_balance_3 = $record['balance_4'];
                        $record_balance_4 = $record['balance_5'];
                        $record_balance_5 = $record['balance_6'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_2'];
                        $record_forecast_2 = $record['forecast_3'];
                        $record_forecast_3 = $record['forecast_4'];
                        $record_forecast_4 = $record['forecast_5'];
                        $record_forecast_5 = $record['forecast_6'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 2) {
                        $record_need_1 = $record['need_3'];
                        $record_need_2 = $record['need_4'];
                        $record_need_3 = $record['need_5'];
                        $record_need_4 = $record['need_6'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_3'];
                        $record_balance_2 = $record['balance_4'];
                        $record_balance_3 = $record['balance_5'];
                        $record_balance_4 = $record['balance_6'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_3'];
                        $record_forecast_2 = $record['forecast_4'];
                        $record_forecast_3 = $record['forecast_5'];
                        $record_forecast_4 = $record['forecast_6'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 3) {
                        $record_need_1 = $record['need_4'];
                        $record_need_2 = $record['need_5'];
                        $record_need_3 = $record['need_6'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_4'];
                        $record_balance_2 = $record['balance_5'];
                        $record_balance_3 = $record['balance_6'];
                        $record_balance_4 = $record['avg_balance'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_4'];
                        $record_forecast_2 = $record['forecast_5'];
                        $record_forecast_3 = $record['forecast_6'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 4) {
                        $record_need_1 = $record['need_5'];
                        $record_need_2 = $record['need_6'];
                        $record_need_3 = $record['avg_need'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_5'];
                        $record_balance_2 = $record['balance_6'];
                        $record_balance_3 = $record['avg_balance'];
                        $record_balance_4 = $record['avg_balance'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_5'];
                        $record_forecast_2 = $record['forecast_6'];
                        $record_forecast_3 = $record['avg_forecast'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else if ($month_from_leadtime == 5) {
                        $record_need_1 = $record['need_6'];
                        $record_need_2 = $record['avg_need'];
                        $record_need_3 = $record['avg_need'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['balance_6'];
                        $record_balance_2 = $record['avg_balance'];
                        $record_balance_3 = $record['avg_balance'];
                        $record_balance_4 = $record['avg_balance'];
                        $record_balance_5 = $record['avg_balance'];
                        $record_balance_6 = $record['avg_balance'];
                        $record_forecast_1 = $record['forecast_6'];
                        $record_forecast_2 = $record['avg_forecast'];
                        $record_forecast_3 = $record['avg_forecast'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    } else {
                        $record_need_1 = $record['avg_need'];
                        $record_need_2 = $record['avg_need'];
                        $record_need_3 = $record['avg_need'];
                        $record_need_4 = $record['avg_need'];
                        $record_need_5 = $record['avg_need'];
                        $record_need_6 = $record['avg_need'];
                        $record_balance_1 = $record['avg_balance'];
                        $record_balance_2 = $record_balance_1 - $record['avg_need'];
                        $record_balance_3 = $record_balance_2 - $record['avg_need'];
                        $record_balance_4 = $record_balance_3 - $record['avg_need'];
                        $record_balance_5 = $record_balance_4 - $record['avg_need'];
                        $record_balance_6 = $record_balance_5 - $record['avg_need'];
                        $record_forecast_1 = $record['avg_forecast'];
                        $record_forecast_2 = $record['avg_forecast'];
                        $record_forecast_3 = $record['avg_forecast'];
                        $record_forecast_4 = $record['avg_forecast'];
                        $record_forecast_5 = $record['avg_forecast'];
                        $record_forecast_6 = $record['avg_forecast'];
                    }
                }

                // jika ada share order maka split
                if ($record['share_order'] < '100') {
                    if ($record_need_1 > 0) {
                        $need_1 = (ceil((($record_need_1 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $need_1 =  $record_need_1;
                    }

                    if ($record_need_2 > 0) {
                        $need_2 = (ceil((($record_need_2 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $need_2 = $record_need_2;
                    }

                    if ($record_need_3 > 0) {
                        $need_3 = (ceil((($record_need_3 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $need_3 = $record_need_3;
                    }

                    if ($record_need_4 > 0) {
                        $need_4 = (ceil((($record_need_4 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $need_4 = $record_need_4;
                    }

                    if ($record_need_5 > 0) {
                        $need_5 = (ceil((($record_need_5 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $need_5 = $record_need_5;
                    }

                    if ($record_need_6 > 0) {
                        $need_6 = (ceil((($record_need_6 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $need_6 = $record_need_6;
                    }

                    if ($record_balance_1 > 0) {
                        $balance_1 = (ceil((($record_balance_1 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $balance_1 = $record_balance_1;
                    }

                    if ($record_balance_2 > 0) {
                        $balance_2 = (ceil((($record_balance_2 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $balance_2 = $record_balance_2;
                    }

                    if ($record_balance_3 > 0) {
                        $balance_3 = (ceil((($record_balance_3 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $balance_3 = $record_balance_3;
                    }

                    if ($record_balance_4 > 0) {
                        $balance_4 = (ceil((($record_balance_4 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $balance_4 = $record_balance_4;
                    }

                    if ($record_balance_5 > 0) {
                        $balance_5 = (ceil((($record_balance_5 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $balance_5 = $record_balance_5;
                    }

                    if ($record_balance_6 > 0) {
                        $balance_6 = (ceil((($record_balance_6 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $balance_6 = $record_balance_6;
                    }

                    if ($record_forecast_1 > 0) {
                        $forecast_1 = (ceil((($record_forecast_1 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $forecast_1 = $record_forecast_1;
                    }

                    if ($record_forecast_2 > 0) {
                        $forecast_2 = (ceil((($record_forecast_2 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $forecast_2 = $record_forecast_2;
                    }

                    if ($record_forecast_3 > 0) {
                        $forecast_3 = (ceil((($record_forecast_3 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $forecast_3 = $record_forecast_3;
                    }

                    if ($record_forecast_4 > 0) {
                        $forecast_4 = (ceil((($record_forecast_4 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $forecast_4 = $record_forecast_4;
                    }

                    if ($record_forecast_5 > 0) {
                        $forecast_5 = (ceil((($record_forecast_5 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $forecast_5 = $record_forecast_5;
                    }

                    if ($record_forecast_6 > 0) {
                        $forecast_6 = (ceil((($record_forecast_6 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                    } else {
                        $forecast_6 = $record_forecast_6;
                    }

                    if ($need_1 + $need_2 + $need_3 + $need_4 > 0) {
                        $avg_need = ($need_1 + $need_2 + $need_3 + $need_4) / 4;
                    } else {
                        $avg_need = $record['avg_need'];
                    }

                    if ($balance_1 + $balance_2 + $balance_3 + $balance_4 > 0) {
                        $avg_balance = ($balance_1 + $balance_2 + $balance_3 + $balance_4) / 4;
                    } else {
                        $avg_balance = $record['avg_balance'];
                    }

                    if ($forecast_1 + $forecast_2 + $forecast_3 + $forecast_4 > 0) {
                        $avg_forecast = ($forecast_1 + $forecast_2 + $forecast_3 + $forecast_4) / 4;
                    } else {
                        $avg_forecast = $record['avg_forecast'];
                    }

                    $total_need = $need_1 + $need_2 + $need_3 + $need_4;
                    if ($total_need > 0) {
                        $purchase_order = (ceil((($total_need) / $record['moq'])) * $record['moq']);
                    } else {
                        $purchase_order = $total_need;
                    }
                } else {
                    $need_1 = $record_need_1;
                    $need_2 = $record_need_2;
                    $need_3 = $record_need_3;
                    $need_4 = $record_need_4;
                    $need_5 = $record_need_5;
                    $need_6 = $record_need_6;
                    $balance_1 = $record_balance_1;
                    $balance_2 = $record_balance_2;
                    $balance_3 = $record_balance_3;
                    $balance_4 = $record_balance_4;
                    $balance_5 = $record_balance_5;
                    $balance_6 = $record_balance_6;
                    $forecast_1 = $record_forecast_1;
                    $forecast_2 = $record_forecast_2;
                    $forecast_3 = $record_forecast_3;
                    $forecast_4 = $record_forecast_4;
                    $forecast_5 = $record_forecast_5;
                    $forecast_6 = $record_forecast_6;
                    $avg_need = $record['avg_need'];
                    $avg_balance = $record['avg_balance'];
                    $avg_forecast = $record['avg_forecast'];
                    $total_need = $record['total_need'];
                    $purchase_order = $record['purchase_order'];
                }
                $arr[] = array(
                    "p_month" => $record['p_month'],
                    "p_year" => $record['p_year'],
                    "revision" => $record['revision'],
                    "part_no" => $record['part_no'],
                    "part_name" => $record['part_name'],
                    "product_family" => $record['product_family'],
                    "issued_1" => round($record['issued_1']),
                    "issued_2" => round($record['issued_2']),
                    "issued_3" => round($record['issued_3']),
                    "issued_avg" => round($record['issued_avg']),
                    "qty_rm" => round($record['qty_rm']),
                    "qty_wip" => round($record['qty_wip']),
                    "qty_vendor" => round($record['qty_vendor']),
                    "total_stock" => round($record['total_stock']),
                    "qty_supply" => round($record['qty_supply']),
                    "qty_wo" => round($record['qty_wo']),
                    "total_wo" => round($record['total_wo']),
                    "need_1" => round($need_1),
                    "need_2" => round($need_2),
                    "need_3" => round($need_3),
                    "need_4" => round($need_4),
                    "need_5" => round($need_5),
                    "need_6" => round($need_6),
                    "balance_1" => round($balance_1),
                    "balance_2" => round($balance_2),
                    "balance_3" => round($balance_3),
                    "balance_4" => round($balance_4),
                    "balance_5" => round($balance_5),
                    "balance_6" => round($balance_6),
                    "forecast_1" => round($forecast_1),
                    "forecast_2" => round($forecast_2),
                    "forecast_3" => round($forecast_3),
                    "forecast_4" => round($forecast_4),
                    "forecast_5" => round($forecast_5),
                    "forecast_6" => round($forecast_6),
                    "avg_need" => round($avg_need),
                    "avg_balance" => round($avg_balance),
                    "avg_forecast" => round($avg_forecast),
                    "total_need" => round($total_need),
                    "purchase_order" => round($purchase_order),
                    "vendor_id" => $record['vendor_id'],
                    "vendor_name" => $record['vendor_name'],
                    "vendor_type" => $record['vendor_type'],
                    "vendor_origin" => $record['vendor_origin'],
                    "share_order" => round($record['share_order']),
                    "leadtime" => round($record['leadtime']),
                    "mpq" => round($record['mpq']),
                    "moq" => round($record['moq']),
                    "maker_name" => $record['maker_name'],
                    "maker_type" => $record['maker_type'],
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        }
    }


    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            // $post = $post['data'];

            $forecast_supplier = $this->api->read("forecast_supplier", [], ["p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision'], "part_no" => $post['part_no'], "vendor_id" => $post['vendor_id']]);
            if (@$forecast_supplier->part_no != "") {
                // $send = $this->api->update('forecast_supplier', ["item_id" => $post['item_id'], "component_id" => $post['component_id']], $post);
                $send = $this->api->update('forecast_supplier', ["id" => @$forecast_supplier->id], $post);
            } else {
                $send = $this->api->create('forecast_supplier', $post, "MRP", "JUTA");
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updatefc()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $postFinal = array(
                "forecast_1" => $post['forecast_1'],
                "forecast_2" => $post['forecast_2'],
                "forecast_3" => $post['forecast_3'],
                "forecast_4" => $post['forecast_4'],
                "reason" => $post['reason'],
                "status" => 'EDIT',
            );

            $send = $this->api->update('forecast_supplier', ["id" => $post['id']], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('forecast_supplier', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_supplier_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $item_id = trim(base64_decode($this->input->get('item_id')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        $fc_4 = $this->readMonths(date('m', strtotime('+4 month', strtotime($periode))));

        //Select Query
        // $this->db->select('a.*,
        //     ((a.need_1 + a.need_2 + a.need_3 + a.need_4) / 4) as need_avg,
        //     b.number as vendor_id, 
        //     b.name as vendor_name, 
        //     b.type as vendor_type, 
        //     b.origin as vendor_origin, 
        //     c.share_order,
        //     d.name as maker_name, 
        //     d.type as maker_type');
        // $this->db->from('generate_mrp_finals a');
        // $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        // $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
        // $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
        // $this->db->where('a.p_month', $filter_month);
        // $this->db->where('a.p_year', $filter_year);
        // $this->db->where('a.revision', $filter_revision);
        // $this->db->where('c.share_order >', 0);
        // $this->db->like('a.product_family', $filter_product_family);
        // $this->db->like('a.part_no', $item_id);
        // $this->db->like('c.supplier_id', $filter_supplier);
        // $this->db->group_by('a.id');
        // $this->db->order_by('b.name', 'asc');
        // $this->db->order_by('a.product_family', 'asc');
        // $records = $this->db->get()->result_array();

        $this->db->select('a.*');
        $this->db->from('forecast_supplier a');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.share_order >', 0);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $item_id);
        $this->db->like('a.vendor_id', $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>FORECAST SUPPLIER</small>
                        </td>
                    </tr>
                </table>
            </div>
        </center>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Part No</th>
                <th rowspan="2" style="text-align:center;">Part Name</th>
                <th rowspan="2" style="text-align:center;">Product Family</th>
                <th rowspan="2" style="text-align:center;">Vendor Name</th>
                <th rowspan="2" style="text-align:center;">Vendor Type</th>
                <th rowspan="2" style="text-align:center;">Origin</th>
                <th rowspan="2" style="text-align:center;">Leadtime</th>
                <th rowspan="2" style="text-align:center;">Share Order</th>
                <th rowspan="2" style="text-align:center;">MPQ</th>
                <th rowspan="2" style="text-align:center;">MOQ</th>
                <th rowspan="2" style="text-align:center;">Maker Name</th>
                <th rowspan="2" style="text-align:center;">Maker Type</th>
                <th colspan="4" style="text-align:center;">Workorder</th>
                <th rowspan="2" style="text-align:center;">Stock WHS</th>
                <th rowspan="2" style="text-align:center;">OS PO</th>
                <th colspan="13" style="text-align:center;">Forecast</th>
            </tr>
            <tr>
                <th>' . $wo_1 . '</th>
                <th>' . $wo_2 . '</th>
                <th>' . $wo_3 . '</th>
                <th>Xbar</th>
                <th>Need M1</th>
                <th>Bal 1</th>
                <th>Fc 1</th>
                <th>Need M2</th>
                <th>Bal 2</th>
                <th>Fc 2</th>
                <th>Need M3</th>
                <th>Bal 3</th>
                <th>Fc 3</th>
                <th>Need M4</th>
                <th>Bal 4</th>
                <th>Fc 4</th>
                <th>Xbar</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_family'] . '</td>
                            <td>' . $data['vendor_name'] . '</td>
                            <td>' . $data['vendor_type'] . '</td>
                            <td>' . $data['vendor_origin'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['leadtime'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['share_order'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['mpq'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['moq'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td>' . $data['issued_1'] . '</td>
                            <td>' . $data['issued_2'] . '</td>
                            <td>' . $data['issued_3'] . '</td>
                            <td>' . $data['issued_avg'] . '</td>
                            <td>' . $data['total_stock'] . '</td>
                            <td>' . $data['qty_vendor'] . '</td>
                            <td>' . $data['need_1'] . '</td>
                            <td>' . $data['balance_1'] . '</td>
                            <td>' . $data['forecast_1'] . '</td>
                            <td>' . $data['need_2'] . '</td>
                            <td>' . $data['balance_2'] . '</td>
                            <td>' . $data['forecast_2'] . '</td>
                            <td>' . $data['need_3'] . '</td>
                            <td>' . $data['balance_3'] . '</td>
                            <td>' . $data['forecast_3'] . '</td>
                            <td>' . $data['need_4'] . '</td>
                            <td>' . $data['balance_4'] . '</td>
                            <td>' . $data['forecast_4'] . '</td>
                            <td>' . $data['avg_need'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printPreview($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=preview_forecast_supplier_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        $fc_4 = $this->readMonths(date('m', strtotime('+4 month', strtotime($periode))));

        $this->db->select('a.p_month,
                    a.p_year,a.revision,a.part_no, a.part_name, a.product_family, 
                    a.issued_1, a.issued_2, a.issued_3, a.issued_avg, a.qty_rm, a.qty_wip, a.qty_vendor, a.total_stock, a.qty_supply, a.qty_wo,a.total_wo, 
                    a.need_1, a.need_2, a.need_3, a.need_4, a.need_5, a.need_6,
                    a.balance_1, 
                    coalesce(a.balance_1-a.need_2,0) as balance_2, 
                    coalesce(a.balance_2-a.need_3,0) as balance_3, 
                    coalesce(a.balance_3-a.need_4,0) as balance_4, 
                    coalesce(a.balance_4-a.need_5,0) as balance_5, 
                    coalesce(a.balance_5-a.need_6,0) as balance_6,
                    (case when a.balance_1 > 0 then 0  else a.need_1 end) as forecast_1,
                    (case when coalesce(a.balance_1 - a.need_2,0) > 0 then 0  else a.need_2 end) as forecast_2,
                    (case when coalesce(a.balance_2 - a.need_3,0) > 0 then 0  else a.need_3 end) as forecast_3,
                    (case when coalesce(a.balance_3 - a.need_4,0) > 0 then 0  else a.need_4 end) as forecast_4,
                    (case when coalesce(a.balance_4 - a.need_5,0) > 0 then 0  else a.need_5 end) as forecast_5,
                    (case when coalesce(a.balance_5 - a.need_6,0) > 0 then 0  else a.need_6 end) as forecast_6,
                    (case when coalesce(a.avg_balance,0) > 0 then 0  else a.avg_need end) as avg_forecast,
                    a.avg_need, coalesce(a.avg_balance,0) as avg_balance, 
                    a.total_need, a.purchase_order,
                    b.id as vendor_id, 
                    b.name as vendor_name, 
                    b.type as vendor_type, 
                    b.origin as vendor_origin, 
                    c.share_order,
                    c.leadtime,
                    c.mpq,
                    c.moq,
                    d.name as maker_name, 
                    d.type as maker_type');
        $this->db->from('generate_mrp_finals a');
        $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
        $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('c.share_order >', 0);
        $this->db->order_by('a.part_no', 'asc');
        // $this->db->limit(2);
        //Total Data
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>FORECAST SUPPLIER</small>
                        </td>
                    </tr>
                </table>
            </div>
        </center>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Part No</th>
                <th rowspan="2" style="text-align:center;">Part Name</th>
                <th rowspan="2" style="text-align:center;">Product Family</th>
                <th rowspan="2" style="text-align:center;">Vendor Name</th>
                <th rowspan="2" style="text-align:center;">Vendor Type</th>
                <th rowspan="2" style="text-align:center;">Origin</th>
                <th rowspan="2" style="text-align:center;">Leadtime</th>
                <th rowspan="2" style="text-align:center;">Share Order</th>
                <th rowspan="2" style="text-align:center;">MPQ</th>
                <th rowspan="2" style="text-align:center;">MOQ</th>
                <th rowspan="2" style="text-align:center;">Maker Name</th>
                <th rowspan="2" style="text-align:center;">Maker Type</th>
                <th colspan="4" style="text-align:center;">Workorder</th>
                <th rowspan="2" style="text-align:center;">Stock WHS</th>
                <th rowspan="2" style="text-align:center;">OS PO</th>
                <th colspan="13" style="text-align:center;">Forecast</th>
            </tr>
            <tr>
                <th>' . $wo_1 . '</th>
                <th>' . $wo_2 . '</th>
                <th>' . $wo_3 . '</th>
                <th>Xbar</th>
                <th>NEED M1</th>
                <th>BAL 1</th>
                <th>FC 1</th>
                <th>NEED M2</th>
                <th>BAL 2</th>
                <th>FC 2</th>
                <th>NEED M3</th>
                <th>BAL 3</th>
                <th>FC 3</th>
                <th>NEED M4</th>
                <th>BAL 4</th>
                <th>FC 4</th>
                <th>Xbar</th>
            </tr>';
        $no = 1;
        foreach ($records as $record) {
            $month_from_leadtime = ceil($record['leadtime'] / 30);
            if ($record['maker_type'] == 'IMPORT') { // jika maker type import maka need dimulai m+2 atau need_3
                if ($month_from_leadtime == 1) {
                    $record_need_1 = $record['need_3'];
                    $record_need_2 = $record['need_4'];
                    $record_need_3 = $record['need_5'];
                    $record_need_4 = $record['need_6'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_3'];
                    $record_balance_2 = $record['balance_4'];
                    $record_balance_3 = $record['balance_5'];
                    $record_balance_4 = $record['balance_6'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_3'];
                    $record_forecast_2 = $record['forecast_4'];
                    $record_forecast_3 = $record['forecast_5'];
                    $record_forecast_4 = $record['forecast_6'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 2) {
                    $record_need_1 = $record['need_4'];
                    $record_need_2 = $record['need_5'];
                    $record_need_3 = $record['need_6'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_4'];
                    $record_balance_2 = $record['balance_5'];
                    $record_balance_3 = $record['balance_6'];
                    $record_balance_4 = $record['avg_balance'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_4'];
                    $record_forecast_2 = $record['forecast_5'];
                    $record_forecast_3 = $record['forecast_6'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 3) {
                    $record_need_1 = $record['need_5'];
                    $record_need_2 = $record['need_6'];
                    $record_need_3 = $record['avg_need'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_5'];
                    $record_balance_2 = $record['balance_6'];
                    $record_balance_3 = $record['avg_balance'];
                    $record_balance_4 = $record['avg_balance'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_5'];
                    $record_forecast_2 = $record['forecast_6'];
                    $record_forecast_3 = $record['avg_forecast'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 4) {
                    $record_need_1 = $record['need_6'];
                    $record_need_2 = $record['avg_need'];
                    $record_need_3 = $record['avg_need'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_6'];
                    $record_balance_2 = $record['avg_balance'];
                    $record_balance_3 = $record['avg_balance'];
                    $record_balance_4 = $record['avg_balance'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_6'];
                    $record_forecast_2 = $record['avg_forecast'];
                    $record_forecast_3 = $record['avg_forecast'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else {
                    $record_need_1 = $record['avg_need'];
                    $record_need_2 = $record['avg_need'];
                    $record_need_3 = $record['avg_need'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['avg_balance'];
                    $record_balance_2 = $record_balance_1 - $record['avg_need'];
                    $record_balance_3 = $record_balance_2 - $record['avg_need'];
                    $record_balance_4 = $record_balance_3 - $record['avg_need'];
                    $record_balance_5 = $record_balance_4 - $record['avg_need'];
                    $record_balance_6 =  $record_balance_5 - $record['avg_need'];
                    $record_forecast_1 = $record['avg_forecast'];
                    $record_forecast_2 = $record['avg_forecast'];
                    $record_forecast_3 = $record['avg_forecast'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                }
            } else { // jika maker type local maka need dimulai m+1 atau need_2
                if ($month_from_leadtime == 1) {
                    $record_need_1 = $record['need_2'];
                    $record_need_2 = $record['need_3'];
                    $record_need_3 = $record['need_4'];
                    $record_need_4 = $record['need_5'];
                    $record_need_5 = $record['need_6'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_2'];
                    $record_balance_2 = $record['balance_3'];
                    $record_balance_3 = $record['balance_4'];
                    $record_balance_4 = $record['balance_5'];
                    $record_balance_5 = $record['balance_6'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_2'];
                    $record_forecast_2 = $record['forecast_3'];
                    $record_forecast_3 = $record['forecast_4'];
                    $record_forecast_4 = $record['forecast_5'];
                    $record_forecast_5 = $record['forecast_6'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 2) {
                    $record_need_1 = $record['need_3'];
                    $record_need_2 = $record['need_4'];
                    $record_need_3 = $record['need_5'];
                    $record_need_4 = $record['need_6'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_3'];
                    $record_balance_2 = $record['balance_4'];
                    $record_balance_3 = $record['balance_5'];
                    $record_balance_4 = $record['balance_6'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_3'];
                    $record_forecast_2 = $record['forecast_4'];
                    $record_forecast_3 = $record['forecast_5'];
                    $record_forecast_4 = $record['forecast_6'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 3) {
                    $record_need_1 = $record['need_4'];
                    $record_need_2 = $record['need_5'];
                    $record_need_3 = $record['need_6'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_4'];
                    $record_balance_2 = $record['balance_5'];
                    $record_balance_3 = $record['balance_6'];
                    $record_balance_4 = $record['avg_balance'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_4'];
                    $record_forecast_2 = $record['forecast_5'];
                    $record_forecast_3 = $record['forecast_6'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 4) {
                    $record_need_1 = $record['need_5'];
                    $record_need_2 = $record['need_6'];
                    $record_need_3 = $record['avg_need'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_5'];
                    $record_balance_2 = $record['balance_6'];
                    $record_balance_3 = $record['avg_balance'];
                    $record_balance_4 = $record['avg_balance'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_5'];
                    $record_forecast_2 = $record['forecast_6'];
                    $record_forecast_3 = $record['avg_forecast'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else if ($month_from_leadtime == 5) {
                    $record_need_1 = $record['need_6'];
                    $record_need_2 = $record['avg_need'];
                    $record_need_3 = $record['avg_need'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['balance_6'];
                    $record_balance_2 = $record['avg_balance'];
                    $record_balance_3 = $record['avg_balance'];
                    $record_balance_4 = $record['avg_balance'];
                    $record_balance_5 = $record['avg_balance'];
                    $record_balance_6 = $record['avg_balance'];
                    $record_forecast_1 = $record['forecast_6'];
                    $record_forecast_2 = $record['avg_forecast'];
                    $record_forecast_3 = $record['avg_forecast'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                } else {
                    $record_need_1 = $record['avg_need'];
                    $record_need_2 = $record['avg_need'];
                    $record_need_3 = $record['avg_need'];
                    $record_need_4 = $record['avg_need'];
                    $record_need_5 = $record['avg_need'];
                    $record_need_6 = $record['avg_need'];
                    $record_balance_1 = $record['avg_balance'];
                    $record_balance_2 = $record_balance_1 - $record['avg_need'];
                    $record_balance_3 = $record_balance_2 - $record['avg_need'];
                    $record_balance_4 = $record_balance_3 - $record['avg_need'];
                    $record_balance_5 = $record_balance_4 - $record['avg_need'];
                    $record_balance_6 = $record_balance_5 - $record['avg_need'];
                    $record_forecast_1 = $record['avg_forecast'];
                    $record_forecast_2 = $record['avg_forecast'];
                    $record_forecast_3 = $record['avg_forecast'];
                    $record_forecast_4 = $record['avg_forecast'];
                    $record_forecast_5 = $record['avg_forecast'];
                    $record_forecast_6 = $record['avg_forecast'];
                }
            }

            // jika ada share order maka split
            if ($record['share_order'] < '100') {
                if ($record_need_1 > 0) {
                    $need_1 = (ceil((($record_need_1 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $need_1 =  $record_need_1;
                }

                if ($record_need_2 > 0) {
                    $need_2 = (ceil((($record_need_2 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $need_2 = $record_need_2;
                }

                if ($record_need_3 > 0) {
                    $need_3 = (ceil((($record_need_3 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $need_3 = $record_need_3;
                }

                if ($record_need_4 > 0) {
                    $need_4 = (ceil((($record_need_4 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $need_4 = $record_need_4;
                }

                if ($record_need_5 > 0) {
                    $need_5 = (ceil((($record_need_5 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $need_5 = $record_need_5;
                }

                if ($record_need_6 > 0) {
                    $need_6 = (ceil((($record_need_6 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $need_6 = $record_need_6;
                }

                if ($record_balance_1 > 0) {
                    $balance_1 = (ceil((($record_balance_1 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $balance_1 = $record_balance_1;
                }

                if ($record_balance_2 > 0) {
                    $balance_2 = (ceil((($record_balance_2 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $balance_2 = $record_balance_2;
                }

                if ($record_balance_3 > 0) {
                    $balance_3 = (ceil((($record_balance_3 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $balance_3 = $record_balance_3;
                }

                if ($record_balance_4 > 0) {
                    $balance_4 = (ceil((($record_balance_4 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $balance_4 = $record_balance_4;
                }

                if ($record_balance_5 > 0) {
                    $balance_5 = (ceil((($record_balance_5 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $balance_5 = $record_balance_5;
                }

                if ($record_balance_6 > 0) {
                    $balance_6 = (ceil((($record_balance_6 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $balance_6 = $record_balance_6;
                }


                if ($record_forecast_1 > 0) {
                    $forecast_1 = (ceil((($record_forecast_1 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $forecast_1 = $record_forecast_1;
                }

                if ($record_forecast_2 > 0) {
                    $forecast_2 = (ceil((($record_forecast_2 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $forecast_2 = $record_forecast_2;
                }

                if ($record_forecast_3 > 0) {
                    $forecast_3 = (ceil((($record_forecast_3 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $forecast_3 = $record_forecast_3;
                }

                if ($record_forecast_4 > 0) {
                    $forecast_4 = (ceil((($record_forecast_4 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $forecast_4 = $record_forecast_4;
                }

                if ($record_forecast_5 > 0) {
                    $forecast_5 = (ceil((($record_forecast_5 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $forecast_5 = $record_forecast_5;
                }

                if ($record_forecast_6 > 0) {
                    $forecast_6 = (ceil((($record_forecast_6 * ($record['share_order'] / 100)) / $record['moq'])) * $record['moq']);
                } else {
                    $forecast_6 = $record_forecast_6;
                }

                if ($need_1 + $need_2 + $need_3 + $need_4 > 0) {
                    $avg_need = ($need_1 + $need_2 + $need_3 + $need_4) / 4;
                } else {
                    $avg_need = $record['avg_need'];
                }

                if ($balance_1 + $balance_2 + $balance_3 + $balance_4 > 0) {
                    $avg_balance = ($balance_1 + $balance_2 + $balance_3 + $balance_4) / 4;
                } else {
                    $avg_balance = $record['avg_balance'];
                }

                if ($forecast_1 + $forecast_2 + $forecast_3 + $forecast_4 > 0) {
                    $avg_forecast = ($forecast_1 + $forecast_2 + $forecast_3 + $forecast_4) / 4;
                } else {
                    $avg_forecast = $record['avg_forecast'];
                }

                $total_need = $need_1 + $need_2 + $need_3 + $need_4;
                if ($total_need > 0) {
                    $purchase_order = (ceil((($total_need) / $record['moq'])) * $record['moq']);
                } else {
                    $purchase_order = $total_need;
                }
            } else {
                $need_1 = $record_need_1;
                $need_2 = $record_need_2;
                $need_3 = $record_need_3;
                $need_4 = $record_need_4;
                $need_5 = $record_need_5;
                $need_6 = $record_need_6;
                $balance_1 = $record_balance_1;
                $balance_2 = $record_balance_2;
                $balance_3 = $record_balance_3;
                $balance_4 = $record_balance_4;
                $balance_5 = $record_balance_5;
                $balance_6 = $record_balance_6;
                $forecast_1 = $record_forecast_1;
                $forecast_2 = $record_forecast_2;
                $forecast_3 = $record_forecast_3;
                $forecast_4 = $record_forecast_4;
                $forecast_5 = $record_forecast_5;
                $forecast_6 = $record_forecast_6;
                $avg_need = $record['avg_need'];
                $avg_balance = $record['avg_balance'];
                $avg_forecast = $record['avg_forecast'];
                $total_need = $record['total_need'];
                $purchase_order = $record['purchase_order'];
            }

            $color_issued_1 = '';
            $color_issued_2 = '';
            $color_issued_3 = '';
            $color_issued_avg = '';
            $color_total_stock = '';
            $color_qty_vendor = '';
            $color_need_1 = '';
            $color_balance_1 = '';
            $color_forecast_1 = '';
            $color_need_2 = '';
            $color_balance_2 = '';
            $color_forecast_2 = '';
            $color_need_3 = '';
            $color_balance_3 = '';
            $color_forecast_3 = '';
            $color_need_4 = '';
            $color_balance_4 = '';
            $color_forecast_4 = '';
            $color_avg_need = '';

            if ($record['issued_1'] <= '0' || $record['issued_1'] <= '0.00') {
                $color_issued_1 = 'color: red;';
            }
            if ($record['issued_2'] <= '0' || $record['issued_2'] <= '0.00') {
                $color_issued_2 = 'color: red;';
            }
            if ($record['issued_3'] <= '0' || $record['issued_3'] <= '0.00') {
                $color_issued_3 = 'color: red;';
            }
            if ($record['issued_avg'] <= '0' || $record['issued_avg'] <= '0.00') {
                $color_issued_avg = 'color: red;';
            }
            if ($record['total_stock'] <= '0' || $record['total_stock'] <= '0.00') {
                $color_total_stock = 'color: red;';
            }
            if ($record['qty_vendor'] <= '0' || $record['qty_vendor'] <= '0.00') {
                $color_qty_vendor = 'color: red;';
            }
            if ($need_1 <= '0' || $need_1 <= '0.00') {
                $color_need_1 = 'color: red;';
            }
            if ($balance_1 <= '0' || $balance_1 <= '0.00') {
                $color_balance_1 = 'color: red;';
            }
            if ($forecast_1 <= '0' || $forecast_1 <= '0.00') {
                $color_forecast_1 = 'color: red;';
            }

            if ($need_2 <= '0' || $need_2 <= '0.00') {
                $color_need_2 = 'color: red;';
            }
            if ($balance_2 <= '0' || $balance_2 <= '0.00') {
                $color_balance_2 = 'color: red;';
            }
            if ($forecast_2 <= '0' || $forecast_2 <= '0.00') {
                $color_forecast_2 = 'color: red;';
            }
            if ($need_3 <= '0' || $need_3 <= '0.00') {
                $color_need_3 = 'color: red;';
            }
            if ($balance_3 <= '0' || $balance_3 <= '0.00') {
                $color_balance_3 = 'color: red;';
            }
            if ($forecast_3 <= '0' || $forecast_3 <= '0.00') {
                $color_forecast_3 = 'color: red;';
            }

            if ($need_4 <= '0' || $need_4 <= '0.00') {
                $color_need_4 = 'color: red;';
            }
            if ($balance_4 <= '0' || $balance_4 <= '0.00') {
                $color_balance_4 = 'color: red;';
            }
            if ($forecast_4 <= '0' || $forecast_4 <= '0.00') {
                $color_forecast_4 = 'color: red;';
            }

            if ($avg_need <= '0' || $avg_need <= '0.00') {
                $color_avg_need = 'color: red;';
            }
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['product_family'] . '</td>
                            <td>' . $record['vendor_name'] . '</td>
                            <td>' . $record['vendor_type'] . '</td>
                            <td>' . $record['vendor_origin'] . '</td>
                            <td align="right" style="mso-number-format:\@;">' . $record['leadtime'] . '</td>
                            <td align="right" style="mso-number-format:\@;">' . $record['share_order'] . '</td>
                            <td align="right" style="mso-number-format:\@;">' . round($record['mpq']) . '</td>
                            <td align="right" style="mso-number-format:\@;">' . round($record['moq']) . '</td>
                            <td>' . $record['maker_name'] . '</td>
                            <td>' . $record['maker_type'] . '</td>
                            <td align="right" style="' . $color_issued_1 . '">' . round($record['issued_1']) . '</td>
                            <td align="right" style="' . $color_issued_2 . '">' . round($record['issued_2']) . '</td>
                            <td align="right" style="' . $color_issued_3 . '">' . round($record['issued_3']) . '</td>
                            <td align="right" style="' . $color_issued_avg . '">' . round($record['issued_avg']) . '</td>
                            <td align="right" style="' . $color_total_stock . '">' . round($record['total_stock']) . '</td>
                            <td align="right" style="' . $color_qty_vendor . '">' . round($record['qty_vendor']) . '</td>
                            <td align="right" style="' . $color_need_1 . '">' . round($need_1) . '</td>
                            <td align="right" style="' . $color_balance_1 . '">' . round($balance_1) . '</td>
                            <td align="right" style="' . $color_forecast_1 . '">' . round($forecast_1) . '</td>
                            <td align="right" style="' . $color_need_2 . '">' . round($need_2) . '</td>
                            <td align="right" style="' . $color_balance_2 . '">' . round($balance_2) . '</td>
                            <td align="right" style="' . $color_forecast_2 . '">' . round($forecast_2) . '</td>
                            <td align="right" style="' . $color_need_3 . '">' . round($need_3) . '</td>
                            <td align="right" style="' . $color_balance_3 . '">' . round($balance_3) . '</td>
                            <td align="right" style="' . $color_forecast_3 . '">' . round($forecast_3) . '</td>
                            <td align="right" style="' . $color_need_4 . '">' . round($need_4) . '</td>
                            <td align="right" style="' . $color_balance_4 . '">' . round($balance_4) . '</td>
                            <td align="right" style="' . $color_forecast_4 . '">' . round($forecast_4) . '</td>
                            <td align="right" style="' . $color_avg_need . '">' . round($avg_need) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function readMonths($month)
    {
        $months = array('01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR', '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG', '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC');
        $month = $months[$month];
        return $month;
    }

    function printForecastCheckApproved()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $datas = $this->api->query("SELECT distinct approved_to FROM forecast_supplier WHERE p_month='$filter_month' and p_year='$filter_year' and revision='$filter_revision' and vendor_id='$filter_supplier' and (approved_to <> '' or approved_to is not null)");

        // die($this->db->last_query());
        if (@$datas[0]->approved_to == "") {
            echo "Approved";
        } else {
            echo "Checking";
        }
    }

    public function printForecast($export = "")
    {
        set_time_limit(1800); // 1800 detik = 30 menit
        ini_set('max_execution_time', '1800'); // 1800 detik = 30 menit
        ini_set('memory_limit', '1024M'); // Set memory limit to 1024MB
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $item_id = trim(base64_decode($this->input->get('item_id')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        $supplier = $this->db->get_where('suppliers', ['id' => $filter_supplier])->row();

        $period_header = strtoupper(date("F", strtotime($filter_year . "-" . $filter_month . "-01")));
        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $periode_from = date('F', strtotime('+1 month', strtotime($periode)));
        $periode_to = date('F Y', strtotime('+3 month', strtotime($periode)));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        // 1️⃣ Ambil semua data sekaligus
        $this->db->select('a.*,
            f.item_supplier,
            e.class,
            g.name as uom,
            h.name as plant_name');
        $this->db->from('forecast_supplier a');
        $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        $this->db->join('generate_mrp_abcclass e', 'a.part_no = e.part_no and a.p_month = e.p_month and a.p_year = e.p_year and a.revision = e.revision', 'left');
        $this->db->join('mst_item_raw f', 'a.part_no = f.item_id', 'left');
        $this->db->join('uom g', 'g.number = f.um_id', 'left');
        $this->db->join('plants h', 'c.plant = h.number', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('c.share_order >', 0);
        $this->db->where('a.vendor_id', $filter_supplier);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $item_id);
        $this->db->group_by('a.id');
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
        $records = $this->db->get()->result_array();
        $totalRows = count($records);

        $approval = $this->api->read('approval', [], ["table_name" => "forecast_supplier"]);
        $user_1 = $this->api->read('user', [], ["username" => $approval->user_approval_1]);
        $user_2 = $this->api->read('user', [], ["username" => $approval->user_approval_2]);

        $qr_user_1 = "";
        $qr_user_2 = "";

        if (@$records[0]['approved_to'] == "") {
            $hidden = "";
        } else {
            $hidden = "hidden";
        }

        $qr_user_1 = "src='" . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . "'";
        $qr_user_2 = "src='" . base_url('assets/image/qrcode/' . @$user_2->name . '.png') . "'";

        $this->createQrcode(@$user_1->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_2->name, "assets/image/qrcode/");
        if ($export == "") {
            $media = "@media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }";
            $media2 = ' <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>';
        } else {
            $media = ".print {
                        zoom: 0.5;
                    }";
            $media2 = "";
        }
        $html = '<html>
                    <head>
                        <title>' . @$supplier->name . ' (' . $periode_from . '-' . $periode_to . '_Plant ' . @$records[0]['plant_name'] . ')</title>
                        <link rel="icon" href="' . base_url('assets/image/config/' . $config->favicon) . '" type="image/png" sizes="16x16">
                    </head>
                    
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        .customers {
                            border-collapse: collapse;
                            font-size: 11px;
                        }
                        .customers td, .customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        .customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        ' . $media . '
                    </style>
                    <body>
                        ' . $media2 . '
                        <div class="print">';

        // Loop data dan buat halaman baru tiap 15 baris
        $rowsPerPage = 15;
        $pageCount = ceil($totalRows / $rowsPerPage);
        $no = 1;
        for ($page = 0; $page < $pageCount; $page++) {
            // ===== HEADER =====
            $html .= '<center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>FORECAST</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . '
                    </div>
                    <br><br>
                    <div style="float: center; font-size: 16px; text-align: center;">
                        <h3>FORECAST ' . $period_header . ' ' . $filter_year . '</h3>
                    </div>
                </center>
                <table class="customers" style="width:100%;">
                    <tr>
                        <th rowspan="2" width="20" style="text-align:center; background:#6ba7e1">NO</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NAME</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">PRODUCT FAMILY</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO. VENDOR</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER TYPE</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">CLASS<br>A/B/C</th>
                        <th colspan="3" style="text-align:center; background:#6ba7e1">FORECAST</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">UOM</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">SUPPLIER\'S CAPACITY</th>
                        <th rowspan="2" style="text-align:center; background:#6ba7e1">CONFIRMATION OF<br> SUPPLIER\'S ABILITY</th>
                    </tr>
                    <tr>
                        <th style="text-align:center; background:#6ba7e1; width:70px;">' . $fc_1 . '</th>
                        <th style="text-align:center; background:#6ba7e1; width:70px;">' . $fc_2 . '</th>
                        <th style="text-align:center; background:#6ba7e1; width:70px;">' . $fc_3 . '</th>
                    </tr>';

            // ===== ISI DATA =====
            $pageData = array_slice($records, $page * $rowsPerPage, $rowsPerPage);
            foreach ($pageData as $data) {
                $html .= '<tr>
                            <td align="center">' . $no++ . '</td>
                            <td>' . $data['part_no'] . '</td>
                            <td>' . $data['part_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['item_supplier'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td align="center">' . $data['class'] . '</td>
                            <td align="right">' . $data['forecast_1'] . '</td>
                            <td align="right">' . $data['forecast_2'] . '</td>
                            <td align="right">' . $data['forecast_3'] . '</td>
                            <td align="center">' . $data['uom'] . '</td>
                            <td align="center"></td>
                            <td align="center"></td>
                        </tr>';
            }
            $html .= '</tbody></table>';

            // ===== FOOTER (hanya di halaman terakhir) =====
            if ($page + 1 == $pageCount) {
                $html .= '<br><span style="font-size:12px"><b>Note :</b><br>
                            1. Angka pada kolom forecast merupakan estimasi quantity open PO oleh BEI pada bulan tersebut <i>(The number in the forecast column is the estimated quantity of open PO by BEI in that month).</i><br>
                            2. Data Forecast berdasarkan hasil dari Generate MRP bulan berjalan <i>(Forecast data is based on the results of the current month\'s MRP Generate).</i><br>
                            </span>';
                $html .= '<br><center><table width="85%" border="0" style="font-size:12px;">
                            <tr>
                                <td align="center" width="250px"><b>' . @$supplier->name . '</b></td>
                                <td align="center" colspan="2"><b>PT. ' . @$config->name . '</b></td>
                            </tr>
                            <tr>
                                <td align="center"><b>ACCEPTED BY,</td>
                                <td align="center"><b>CONFIRM,</td>
                                <td align="center"><b>CHECKED,</td>
                            </tr>
                            <tr>
                                <td align="center" style="height:80px; width:120px;"></td>
                                <td align="center" style="height:80px; width:120px;"' . $hidden . '><img ' . $qr_user_2 . ' width="80"/></td>
                                <td align="center" style="height:80px; width:120px;"' . $hidden . '><img ' . $qr_user_1 . ' width="80"/></td>
                            </tr>
                            <tr>
                                <td align="center">________________________</td>
                                <td align="center"><b><u>' . @$user_2->name . '</u><br>' . @$user_2->position . '</b></td>
                                <td align="center"><b><u>' . @$user_1->name . '</u><br>' . @$user_1->position . '</b></td>
                            </tr>
                
                </table></center>';
            }

            // ===== PAGE BREAK (jika bukan halaman terakhir) =====
            if ($page + 1 < $pageCount) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
        }
        $html .= '</div></body></html>';
        if ($export == "") {
            $html .= '<script>window.print()</script>';
            die($html);
        } else {
            // Load HTML content
            $this->dompdf_gen->loadHtml($html);

            // (Optional) Set paper size and orientation
            $this->dompdf_gen->setPaper('A4', 'landscape');

            // Render the HTML as PDF
            $this->dompdf_gen->render();

            // Output the generated PDF (1 = download and 0 = preview)
            // $this->dompdf_gen->stream("sample.pdf", array("Attachment" => 0));
            $judul_file = @$supplier->name . ' (' . $periode_from . '-' . $periode_to . '_Plant ' . @$datas[0]['plant_name'] . ')';
            $filePath = FCPATH . 'assets/fcs/' . $judul_file . '.pdf'; // FCPATH ensures the path is relative to the project root
            $this->dompdf_gen->save($filePath);

            $this->dompdf_gen->stream($judul_file, ['Attachment' => true]);
        }
    }


    public function print2($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_supplier_2_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $item_id = trim(base64_decode($this->input->get('item_id')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $supplier = $this->db->get_where('suppliers', ['id' => $filter_supplier])->row();

        $period_header = strtoupper(date("F", strtotime($filter_year . "-" . $filter_month . "-01")));
        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        // $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        // $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        // $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        //Select Query
        $this->db->select('a.*,
            f.item_supplier,
            e.class,
            g.name as uom');
        $this->db->from('forecast_supplier a');
        $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        $this->db->join('generate_mrp_abcclass e', 'a.part_no = e.part_no and a.p_month = e.p_month and a.p_year = e.p_year and a.revision = e.revision', 'left');
        $this->db->join('mst_item_raw f', 'a.part_no = f.item_id', 'left');
        $this->db->join('uom g', 'g.number = f.um_id', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.share_order >', 0);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $item_id);
        $this->db->like('a.vendor_id', $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
        $records = $this->db->get()->result_array();

        $approval = $this->api->read('approval', [], ["table_name" => "forecast_supplier"]);
        $user_1 = $this->api->read('user', [], ["username" => $approval->user_approval_1]);
        $user_2 = $this->api->read('user', [], ["username" => $approval->user_approval_2]);

        // $qr_user_1 = "";
        // $qr_user_2 = "";

        // if (@$records[0]['approved_to'] == "") {
        //     $qr_user_1 = "<img src='" . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . "' width='80'/>";
        //     $qr_user_2 = "<img src='" . base_url('assets/image/qrcode/' . @$user_2->name . '.png') . "' width='80'/>";
        // } else {
        //     $qr_user_1 = "";
        //     $qr_user_2 = "";
        // }

        // $this->createQrcode(@$user_1->name, "assets/image/qrcode/");
        // $this->createQrcode(@$user_2->name, "assets/image/qrcode/");

        $html = '<html>
                    <head>
                        <title>Forecast Supplier</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    
                    <style>
                        * {
                        box-sizing: border-box;
                        }
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                        border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}
                        .column {
                        float: left;
                        width: 50%;
                        border: 1px black solid;
                        }

                        /* Clear floats after the columns */
                        .row:after {
                        content: "";
                        display: table;
                        clear: both;
                        }
                    </style>
                    <body>';
        $html .= '<center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>FORECAST</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . '
            </div>
            <br><br>
            <div style="float: center; font-size: 16px; text-align: center;">
                <h3>FORECAST ' . $period_header . ' ' . $filter_year . '</h3>
            </div>
        </center>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center; background:#6ba7e1">NO</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NAME</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PRODUCT FAMILY</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO. VENDOR</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER TYPE</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">CLASS<br>A/B/C</th>
                <th colspan="3" style="text-align:center; background:#6ba7e1">FORECAST</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">UOM</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">SUPPLIER\'S CAPACITY</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">CONFIRMATION OF<br> SUPPLIER\'S ABILITY</th>
            </tr>
            <tr>
                <th style="text-align:center; background:#6ba7e1">' . $fc_1 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_2 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_3 . '</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_family'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td align="center">' . $data['class'] . '</td>
                            <td align="right">' . $data['forecast_1'] . '</td>
                            <td align="right">' . $data['forecast_2'] . '</td>
                            <td align="right">' . $data['forecast_3'] . '</td>
                            <td align="center">' . $data['uom'] . '</td>
                            <td align="center"></td>
                            <td align="center"></td>
                        </tr>';
            $no++;
        }
        $html .= '</table>';
        $html .= '<br><span style="font-size:12px"><b>Note :</b><br>
                            1. Angka pada kolom forecast merupakan estimasi quantity open PO oleh BEI pada bulan tersebut <i>(The number in the forecast column is the estimated quantity of open PO by BEI in that month).</i><br>
                            2. Data Forecast berdasarkan hasil dari Generate MRP bulan berjalan <i>(Forecast data is based on the results of the current month\'s MRP Generate).</i><br>
                            </span>';
        $html .= '<br><center><table width="85%" border="0" style="font-size:12px;">
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center" width="250px"><b>' . @$supplier->name . '</b></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center" colspan="4"><b>PT. ' . @$config->name . '</b></td>
                                </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"><b>ACCEPTED BY,</td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"><b>CONFIRM,</td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"><b>CHECKED,</td>
                            </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center" style="height:80px; width:120px;"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center" style="height:80px; width:120px;"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center" style="height:80px; width:120px;"></td>
                                </tr>
                            <tr>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center">________________________</td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"><b><u>' . @$user_2->name . '</u><br>' . @$user_2->position . '</b></td>
                                <td align="center"></td>
                                <td align="center"></td>
                                <td align="center"><b><u>' . @$user_1->name . '</u><br>' . @$user_1->position . '</b></td>
                            </tr>
                
                </table></center>';
        $html .= '</body></html>';
        echo $html;
    }
}

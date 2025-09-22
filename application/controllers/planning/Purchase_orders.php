<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends CI_Controller
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
        $this->load->library('email');
        // $this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);
        $this->gp = $this->load->database('gp', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Part No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/purchase_orders');
        } else {
            redirect('error_access');
        }
    }

    function apiSupplier($supplier_code){
        $url = "https://gpai.aeconsys.com/api/supplier?code=$supplier_code";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        //    'APIKEY: 111111111111111111111',
            'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){
            die("Connection Failure");
        }
        curl_close($curl);

        $gpai = json_decode($result, true);

        return $gpai;
    }

    function readPoSearch(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $datas = $this->api->query("SELECT DISTINCT purchase_order_no FROM purchase_orders WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision='$filter_revision'");
        echo json_encode($datas);
    }

    function readSupplier(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->api->query("SELECT * FROM suppliers WHERE `number` like '%$post%' or `name` like '%$post%'");
        echo json_encode($datas);
    }

    function readPo($p_month, $p_year, $revision, $product_family){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->api->query("SELECT DISTINCT a.supplier_id, a.purchase_order_no, b.number as supplier_no, b.name as supplier_name, a.product_family_name FROM purchase_orders a
            JOIN suppliers b ON a.supplier_id = b.id 
            WHERE a.p_month = '$p_month' and a.p_year = '$p_year' and a.revision = '$revision' and a.product_family = '$product_family' and (
            a.purchase_order_no like '%$post%' or b.name like '%$post%' or a.product_family_name like '%$post%')");
        echo json_encode($datas);
    }

    function readPono(){
        $datas = $this->api->query("SELECT distinct a.purchase_order_no, b.number FROM purchase_orders a JOIN suppliers b ON a.supplier_id = b.id ORDER BY a.purchase_order_no ASC");
        echo json_encode($datas);
    }

    function forecast($item_id, $month, $year, $revision){
        $q_forecast = $this->db->query("SELECT a.component_id, c.item_name, SUM(a.qty) as qty, 
            (SUM(a.qty) * b.month_1) as m1, 
            (SUM(a.qty) * b.month_2) as m2, 
            (SUM(a.qty) * b.month_3) as m3,
            (SUM(a.qty) * b.month_4) as m4,
            (SUM(a.qty) * b.month_5) as m5,
            (SUM(a.qty) * b.month_6) as m6,
            (SUM(a.qty) * b.month_7) as m7,
            (SUM(a.qty) * b.month_8) as m8,
            (SUM(a.qty) * b.month_9) as m9,
            (SUM(a.qty) * b.month_10) as m10,
            (SUM(a.qty) * b.month_11) as m11,
            (SUM(a.qty) * b.month_12) as m12
            FROM mst_bom a
            JOIN mst_forecast b ON a.item_id = b.product_no
            JOIN mst_item_raw c ON a.component_id = c.item_id
            WHERE b.p_month = '$month' and b.p_year = '$year' and b.revision = '$revision' 
            and (b.month_1 > 0 or b.month_2 > 0 or b.month_3 > 0) and a.component_id = '$item_id'
            GROUP BY a.component_id");
        $forecast = $q_forecast->row();

        return $forecast;
    }

    function get_delivery_schedules(){
        if ($this->input->post()) {
            $po_no = base64_decode($this->input->post('po_no'));
            $part_no = base64_decode($this->input->post('part_no'));

            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('delivery_schedules a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.po_no', $po_no);
            $this->db->where('a.part_no', $part_no);
            //Get Data Array
            $records = $this->db->get()->row();

            die(json_encode($records));
        }
    }

    public function checkPR()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('purchase_requests');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->where('approved_to !=', '');
        $this->db->group_by('part_no');
        $records = $this->db->get()->result_array();

        $purchase_requests = $this->api->reads("purchase_requests", [], ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

        if(count($purchase_requests) > 0){
            if (count($records) > 0) {
                echo json_encode(array("theme" => "error"));
            } else {
                echo json_encode(array("theme" => "success"));
            }
        }else{
            echo json_encode(array("theme" => "error"));
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));
            $filter_status = trim(base64_decode($this->input->get('filter_status')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, SUM(a.qty) as total_qty, SUM(a.total) as total_amount, (CASE WHEN COALESCE(c.total_dn, 0) = SUM(a.qty) THEN 1 ELSE 0 END) as print_sd, d.number as supplier_no, d.name as supplier_name, d.email as email_to, d.email_cc, d.type as supplier_type');
            // $this->db->select('a.*, SUM(a.qty) as total_qty, (SUM(a.qty) * e.price) as total_amount, COUNT(c.po_no) as print_sd, d.number as supplier_no, d.name as supplier_name, d.email as email_to, d.email_cc');
            $this->db->from('purchase_orders a');
            $this->db->join('(select SUM(qty) as total_dn, po_no from delivery_schedules GROUP BY po_no) c', "a.purchase_order_no = c.po_no", 'left');
            $this->db->join('suppliers d', "a.supplier_id = d.id", 'left');
            // $this->db->join('supplier_items e', "a.supplier_id = d.id and a.item_id = e.item_id", 'left');
            // $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id)) g", 'e.id = g.supplier_item_id', 'left');    
            // $this->db->join('purchase_requests e', 'a.purchase_request_no = e.purchase_request_no and a.item_id = e.part_no');
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            if($filter_status == "Send"){
                $this->db->where('a.email_status', "SENT");
            }
            if($filter_status == "Unsend"){
                $this->db->where('a.email_status', null);
            }
            if($filter_status == "Push"){
                $this->db->where('a.push', "OK");
            }
            if($filter_status == "Unpush"){
                $this->db->where('a.push', null);
            }
            if($filter_status == "Receipt"){
                $this->db->where('a.confirm', null);
            }
            if($filter_status == "Confirm"){
                $this->db->where('a.confirm', "Confirm");
            }
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            if($filter_revision != ""){
                $this->db->where('a.revision', $filter_revision);
            }
            $this->db->where('a.type', "REGULAR");
            $this->db->like('a.product_family_name', $filter_product_family);
            $this->db->like('a.item_id', $filter_part_no);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.purchase_order_no', $filter_po);
            $this->db->group_by('a.purchase_order_no');
            $this->db->order_by('d.name');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails($purchase_order_no, $revision){
        $purchase_order_no = base64_decode($purchase_order_no);
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));

        $po = $this->api->read("purchase_orders", [], ["purchase_order_no" => $purchase_order_no]);
        $start_date = date("Y-m-d");
        $valid_date = date("Y-m-t", strtotime(@$po->p_year."-".@$po->p_month."-10"));

        //Select Query
        $this->db->select('a.*, b.number as supplier_no, b.name as supplier_name, c.mpq, c.moq, g.price as dprice, g.currency as dcurrency, b.email as email_to, b.email_cc, d.type as maker_type');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('purchase_requests c', 'a.purchase_request_no = c.purchase_request_no and a.item_id = c.part_no', 'left');
        $this->db->join('supplier_items f', 'a.supplier_id = f.supplier_id and a.item_id = f.item_id');
        $this->db->join('mst_maker d', 'f.maker_code = d.number');
        $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices where start_date <= '$start_date' and valid_date >= '$start_date' GROUP BY supplier_item_id)) g", 'f.id = g.supplier_item_id', 'left');
        $this->db->where('a.purchase_order_no', $purchase_order_no);
        $this->db->where('a.revision', $revision);
        $this->db->like('a.item_id', $filter_part_no);
        $this->db->order_by('a.item_id');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $total_qty = 0;
        $total_amount = 0;
        foreach ($records as $record) {
            if($record['maker_type'] == "LOCAL"){
                $etd = date("Y-m-d", strtotime("-14 days", strtotime($record['eta'])));
            }else{
                $etd = date("Y-m-d", strtotime("-21 days", strtotime($record['eta'])));
            }
            $total_qty += $record['qty'];
            $total_amount += $record['total'];
            $data[] = array_merge($record, ["etd" => $etd]);
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $data,
            'footer' => array([
                "item_id" => "Grand Total",
                "qty" => $total_qty,
                "price" => ($total_amount / $total_qty),
                "sync" => 0,
                "total" => $total_amount
            ])
        ]);
        echo json_encode($result);
    }

    public function datatableConfirm(){
        //Select Query
        $this->db->select("a.p_month, a.p_year, a.revision, CONCAT('/', a.part_no) as part_no, a.part_name, a.purchase_request_no, a.purchase_request_date, a.product_family, b.name as supplier_name, c.purchase_order_no, c.purchase_order_date, 
            a.purchase_request as total_pr, 
            c.qty as total_po,
            c.total as total_amount,
            a.reason as reason_pr,
            c.reason as reason_po");
        $this->db->from('purchase_requests a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('purchase_orders c', 'a.part_no = c.item_id and a.supplier_id = c.supplier_id and a.purchase_request_no = c.purchase_request_no');
        $this->db->where('a.purchase_request != c.qty');
        $this->db->order_by('a.updated_date');
        $this->db->order_by('a.created_date');
        
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $records
        ]);
        echo json_encode($result);
    }

    public function purchaseOrderNo($code){
        $sqlGetID	= $this->db->query("SELECT max(purchase_order_no) as kode FROM purchase_orders WHERE purchase_order_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;
        
        // if ($kode == NULL) {
        //     $autoID = sprintf("%02s", $kode + 1);
        // } else {
            $urutan = (int) substr($kode, -2);
            $urutan++;
            $autoID	= sprintf("%02s", $urutan);
        // }

        return $code . $autoID;
    }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $purchase_request_no = base64_decode($this->input->get('purchase_request_no'));

            $pr = $this->api->read("purchase_requests", ["purchase_request_no" => $purchase_request_no]);
            $this->api->delete("purchase_orders", ["purchase_request_no" => $purchase_request_no]);

            $start_date = date("Y-m-d");
            $valid_date = date("Y-m-t", strtotime(@$pr->p_year."-".@$pr->p_month."-10"));

            //Select Query
            // $this->db->select('a.*, b.number as pfm_number, d.name as uom, e.currency, e.price, c.item_supplier as supplier_part_code, f.attention, f.address, f.email, f.number as supplier_no, f.name as supplier_name');
            $this->db->select('a.*, b.number as pfm_number, d.name as uom, g.currency, g.price, c.item_supplier as supplier_part_code, f.attention, f.address, f.email, f.number as supplier_no, f.name as supplier_name');
            $this->db->from('purchase_requests a');
            $this->db->join('mst_item_family b', 'a.product_family = b.name');
            $this->db->join('mst_item_raw c', 'a.part_no = c.item_id');
            $this->db->join('uom d', 'c.um_id = d.number', 'left');
            $this->db->join('supplier_items e', "c.item_id = e.item_id and a.supplier_id = e.supplier_id and e.share_order > 0", 'left');
            $this->db->join('suppliers f', 'a.supplier_id = f.id');
            $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices where start_date <= '$start_date' and valid_date >= '$start_date' GROUP BY supplier_item_id)) g", 'e.id = g.supplier_item_id', 'left');
            // $this->db->join('notification f', "a.id = f.table_id and f.table_name = 'purchase_requests'", 'left');
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.purchase_request_no', $purchase_request_no);
            $this->db->where('a.purchase_request >', 0);
            // $this->db->group_by('a.supplier_id');
            // $this->db->group_by('b.number');
            // $this->db->group_by('e.currency');
            // $this->db->group_by('a.part_no');
            $this->db->order_by('e.currency', 'asc');
            $purchase_requests = $this->db->get()->result_array();

            $arr = array();
            $no = 0;
            $end_supplier = "";
            $end_product_family = "";
            $end_currency = "-";
            $end_purchase_order_no = "";
            foreach ($purchase_requests as $purchase_request) {
                $date = $purchase_request['p_year'] . "-" . $purchase_request['p_month'] . "-01";
                $item_id = $purchase_request['part_no'];
                $leadtime = $purchase_request['leadtime'];
                $revision = $purchase_request['revision'];
                $p_month = $purchase_request['p_month'];
                $p_year = $purchase_request['p_year'];

                $m1 = date('Y-m-01', strtotime($date, strtotime($leadtime)));
                $m2 = date('Y-m-01', strtotime($date, strtotime('+1 month', strtotime($leadtime))));
                $m3 = date('Y-m-01', strtotime($date, strtotime('+2 month', strtotime($leadtime))));
                $m11 = "m".date('n', strtotime($date, strtotime($leadtime)));
                $m22 = "m".date('n', strtotime($date, strtotime('+1 month', strtotime($leadtime))));
                $m33 = "m".date('n', strtotime($date, strtotime('+2 month', strtotime($leadtime))));

                $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms1 = $qmps1->row();

                $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms2 = $qmps2->row();

                $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms3 = $qmps3->row();

                $forecast = $this->forecast($item_id, $purchase_request['p_month'], $purchase_request['p_year'], @$revision);

                if(@$rpms1->qty > 0){
                    $prod1 = @$rpms1->qty;
                }else{
                    $prod1 = @$forecast->$m11;
                }

                if(@$rpms2->qty > 0){
                    $prod2 = @$rpms2->qty;
                }else{
                    $prod2 = @$forecast->$m22;
                }

                if(@$rpms3->qty > 0){
                    $prod3 = @$rpms3->qty;
                }else{
                    $prod3 = @$forecast->$m33;
                }

                $supplier_no = $purchase_request['supplier_no'];
                $leadtime = $purchase_request['leadtime'];
                $eta = date("Y-m-d", strtotime("+".$leadtime." days", strtotime($purchase_request['purchase_request_date'])));
                $period = $purchase_request['p_year'].$purchase_request['p_month'];

                if($purchase_request['currency'] == $end_currency){
                    $purchase_order_no = $end_purchase_order_no;
                    $no = 2;
                }else{
                    $purchase_order_no = $this->purchaseOrderNo("MC-IND".@trim($supplier_no).$period. "-" .trim($purchase_request['pfm_number']));
                    $no = 1;
                }

                $convert = $this->api->read("convertions", [], ["item_id" => $purchase_request['part_no']]);
                if(empty($convert->uom_po)){
                    $uom = $purchase_request['uom'];
                    $qty = $purchase_request['purchase_request'];
                    $total = ($purchase_request['purchase_request'] * $purchase_request['price']);
                }else{
                    $uom = $convert->uom_to;
                    if($convert->type == "Divided"){
                        $qty = ($purchase_request['purchase_request'] / $convert->amount);
                        $total = ($qty * $purchase_request['price']);
                    }else{
                        $qty = ($purchase_request['purchase_request'] * $convert->amount);
                        $total = ($qty * $purchase_request['price']);
                    }
                }

                $arr[] = array(
                    "flag" => $no,
                    "p_month" => $purchase_request['p_month'],
                    "p_year" => $purchase_request['p_year'],
                    "revision" => $purchase_request['revision'],
                    "purchase_request_no" => $purchase_request_no,
                    "purchase_order_no" => $purchase_order_no,
                    "purchase_order_date" => date("Y-m-d"),
                    "item_id" => $purchase_request['part_no'],
                    "item_vendor" => $purchase_request['supplier_part_code'],
                    "item_name" => $purchase_request['part_name'],
                    "product_family" => $purchase_request['pfm_number'],
                    "product_family_name" => $purchase_request['product_family'],
                    "supplier_id" => $purchase_request['supplier_id'],
                    "qty" => $qty,
                    "price" => $purchase_request['price'],
                    "total" => $total,
                    "currency" => $purchase_request['currency'],
                    "uom" => $uom,
                    "leadtime" => $purchase_request['leadtime'],
                    "eta" => $eta,
                    "contact" => $purchase_request['attention'],
                    "email" => strtolower($purchase_request['email'],),
                    "forecast_1" => $prod1,
                    "forecast_2" => $prod2,
                    "forecast_3" => $prod3,
                );

                $end_supplier = $supplier_no;
                $end_currency = $purchase_request['currency'];
                $end_product_family = $purchase_request['product_family'];
                $end_purchase_order_no = $purchase_order_no;
            }

            if(count($arr) > 0){
                $this->db->update("purchase_requests", ["create_po" => date("Y-m-d H:i:s"), "confirm" => 0, "confirm_date" => null], ["purchase_request_no" => $purchase_request_no]);
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function getData()
    // {
    //     if ($this->input->get()) {
    //         //Filter Data
    //         $filter_month = base64_decode($this->input->get('filter_month'));
    //         $filter_year = base64_decode($this->input->get('filter_year'));
    //         $filter_revision = base64_decode($this->input->get('filter_revision'));
    //         $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
    //         $filter_product_family = base64_decode($this->input->get('filter_product_family'));
    //         $filter_po = base64_decode($this->input->get('filter_po'));

    //         //Select Query
    //         $this->db->select('a.*, e.currency as currency_new, e.price as price_new');
    //         $this->db->from('purchase_orders a');
    //         $this->db->join('mst_item_raw c', 'a.item_id = c.item_id');
    //         $this->db->join('upload_gp e', "c.item_gp = e.product_no and a.supplier_no = e.supplier_no and e.p_month = a.p_month and e.p_year = a.p_year and e.revision = a.revision");
    //         // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
    //         $this->db->where('a.p_month', $filter_month);
    //         $this->db->where('a.p_year', $filter_year);
    //         $this->db->where('a.revision', $filter_revision);
    //         $this->db->where('a.purchase_order_no', $filter_po);
    //         $this->db->group_by('a.item_id');
    //         $purchase_requests = $this->db->get()->result_array();

    //         $purchase_requests['total'] = @count($purchase_requests);
    //         die(json_encode($purchase_requests));
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->api->reads('purchase_orders', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "purchase_request_no" => $post['purchase_request_no'],
                "item_id" => $post['item_id'],
                "supplier_id" => $post['supplier_id']
            ]);

            if (count($records) > 0) {
                $send = $this->api->update('purchase_orders', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "purchase_request_no" => $post['purchase_request_no'],
                    "item_id" => $post['item_id'],
                    "supplier_id" => $post['supplier_id']
                ], [
                    "qty" => $post['qty'],
                    "eta" => $post['eta'],
                    "item_vendor" => $post['item_vendor'],
                    "contact" => $post['contact'],
                    "price" => $post['price'],
                    "total" => $post['total'],
                    "currency" => $post['currency'],
                    "email" => $post['email'],
                    "forecast_1" => $post['forecast_1'],
                    "forecast_2" => $post['forecast_2'],
                    "forecast_3" => $post['forecast_3']
                ]);

                echo $send;
            } else {
                $send = $this->api->create('purchase_orders', $post, "PO", "JUTA");
                echo $send;
            }
        }
    }

    public function updateConfirm(){
        if ($this->input->post()) {
            $purchase_request_no = $this->input->post('purchase_request_no');
            $this->db->update('purchase_requests', ["confirm" => 1, "confirm_date" => date("Y-m-d H:i:s")], ["purchase_request_no" => $purchase_request_no]);
            
            echo json_encode(array("message" => "success"));
        }
    }

    public function completeMail(){
        if ($this->input->post()) {
            $purchase_order_no = $this->input->post('purchase_order_no');
            $email_date = $this->input->post('email_date');
            
            $this->db->update('purchase_orders', ["email_status" => "SENT", "confirm" => "Pending", "email_date" => $email_date], ["purchase_order_no" => $purchase_order_no]);
            
            echo json_encode(array("message" => "Complete Mail Success"));
        }
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post('data');
    //         $records = $this->api->reads('purchase_orders', [], [
    //             "p_month" => $post['p_month'],
    //             "p_year" => $post['p_year'],
    //             "revision" => $post['revision'],
    //             "item_id" => $post['item_id'],
    //             "purchase_order_no" => $post['purchase_order_no']
    //         ]);

    //         if (count($records) > 0) {
    //             $send = $this->db->update('purchase_orders', [
    //                 "qty" => $post['qty'],
    //                 "price" => $post['price_new'],
    //                 "total" => ($post['qty'] * $post['price_new']),
    //                 "currency" => $post['currency_new'],
    //             ], [
    //                 "p_month" => $post['p_month'],
    //                 "p_year" => $post['p_year'],
    //                 "revision" => $post['revision'],
    //                 "item_id" => $post['item_id'],
    //                 "purchase_order_no" => $post['purchase_order_no']
    //             ]);

    //             echo $send;
    //         } else {
    //             $send = $this->api->create('purchase_orders', $post, "PO", "JUTA");
    //             echo $send;
    //         }
    //     }
    // }

    public function syncPrice(){
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $data = $this->api->read("purchase_orders", [], ["id" => $id]);
            $purchase_order_no = @$data->purchase_order_no;
            $maxRev = $this->api->query("SELECT max(rev) as rev FROM purchase_orders WHERE purchase_order_no = '$purchase_order_no'");

            $start_date = date("Y-m-d");

            $this->db->select('b.price, b.currency');
            $this->db->from('supplier_items a');
            $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices where start_date <= '$start_date' and valid_date >= '$start_date' GROUP BY supplier_item_id)) b", 'a.id = b.supplier_item_id');
            $this->db->where('a.supplier_id', $data->supplier_id);
            $this->db->where('a.item_id', $data->item_id);
            $supplier_item = $this->db->get()->row();

            if(!empty($supplier_item)){
                $total = ($data->qty * $supplier_item->price);
                $update = $this->db->update('purchase_orders', ["price" => $supplier_item->price, "currency" => $supplier_item->currency, "total" => $total, "rev" => (@$maxRev->rev + 1)], ["id" => $id]);
                if($update){
                    echo json_encode(array("title" => "Good Job", "message" => $data->item_id . " | Sync Price Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $data->item_id . " | Sync Price Failed", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Valid Date not match", "theme" => "error"));
            }
        }
    }

    public function syncPriceAll(){
        if ($this->input->post()) {
            $purchase_order_no = $this->input->post('purchase_order_no');

            $datas = $this->api->reads("purchase_orders", [], ["purchase_order_no" => $purchase_order_no]);
            $message = json_encode(array("title" => "Error", "message" => "Sync Price Failed", "theme" => "error"));
            foreach ($datas as $data) {
                $id = $data->id;

                $maxRev = $this->api->query("SELECT max(rev) as rev FROM purchase_orders WHERE purchase_order_no = '$purchase_order_no'");
                $start_date = date("Y-m-d");

                $this->db->select('b.price, b.currency');
                $this->db->from('supplier_items a');
                $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices where start_date <= '$start_date' and valid_date >= '$start_date' GROUP BY supplier_item_id)) b", 'a.id = b.supplier_item_id');
                $this->db->where('a.supplier_id', $data->supplier_id);
                $this->db->where('a.item_id', $data->item_id);
                $supplier_item = $this->db->get()->row();

                if(!empty($supplier_item)){
                    $total = ($data->qty * $supplier_item->price);
                    $update = $this->db->update('purchase_orders', ["price" => $supplier_item->price, "currency" => $supplier_item->currency, "total" => $total, "rev" => (@$maxRev->rev + 1)], ["id" => $id]);
                    if($update){
                        $message = json_encode(array("title" => "Good Job", "message" => $data->item_id . " | Sync Price Successfully", "theme" => "success"));
                    } else {
                        $message = json_encode(array("title" => "Error", "message" => $data->item_id . " | Sync Price Failed", "theme" => "error"));
                    }
                }else{
                    $message = json_encode(array("title" => "Error", "message" => $data->item_id . " Valid Date not match", "theme" => "error"));
                }
            }

            echo $message;
        }
    }

    public function pushData(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);

            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", $post['item_id']);
            $mst_item = $this->dummy->get()->row();

            $this->dummy->select('*');
            $this->dummy->from("poc");
            $this->dummy->where("poc_id", $post['purchase_order_no']);
            $this->dummy->where("item_id", $post['item_id']);
            $poc = $this->dummy->get()->row();

            // $gpai = $this->apiSupplier($post['supplier_no']);
            // $supplier = $gpai['data'];

            $postFinal = array(
                "poc_id" => $post['purchase_order_no'],
                "ven_id" => @$mst_item->ven_id,
                // "ven_id" => @$supplier[0]['alias_code'],
                "poc_date" => $post['purchase_order_date'],
                "poc_emp" => $this->session->name,
                "item_id" => $post['item_id'],
                "poc_reqdate" => $post['eta'],
                "etd_date" => $post['etd'],
                "poc_reqqty" => $post['qty'],
                "currency" => $post['currency'],
                "periode" => $post['p_year'].$post['p_month'],
                // "poc_remark" => $post['reason'],
            );

            if($poc){
                $this->dummy->where("poc_id", $post['purchase_order_no']);
                $this->dummy->where("item_id", $post['item_id']);
                if ($this->dummy->update('poc', ["poc_reqqty" => $post['qty'], "poc_reqdate" => $post['eta'], "etd_date" => $post['etd']])) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['item_id'] . " | Data Update Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['item_id'] . " | Data Unsaved", "theme" => "error"));
                }
            }else{
                if ($this->dummy->insert('poc', $postFinal)) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['item_id'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['item_id'] . " | Data Unsaved", "theme" => "error"));
                }
            }

            $this->db->where("item_id", $post['item_id']);
            $this->db->where("purchase_order_no", $post['purchase_order_no']);
            $this->db->update('purchase_orders', ["push_date" => date("Y-m-d H:i:s"), "push" => "OK"]);
        }
    }

    public function updateDetails(){
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $total = round($post['qty'] * $post['price'], 4);
            // $revision = $post['revision'] + 1;
            $revision = $post['revision'];
            $databf = $this->api->read("purchase_orders", [], ["id" => $id]);

            $purchase_order_no = @$databf->purchase_order_no;
            $maxRev = $this->api->query("SELECT max(rev) as rev FROM purchase_orders WHERE purchase_order_no = '$purchase_order_no'");

            $send = $this->db->update('purchase_orders', [
                    "purchase_order_no" => $post['purchase_order_no'], 
                    "supplier_id" => $post['supplier_id'], 
                    "qty" => $post['qty'], 
                    "price" => $post['price'], 
                    "currency" => $post['currency'], 
                    "total" => $total, 
                    "reason" => $post['reason'], 
                    "revision" => $revision,
                    "status_edit" => 1,
                    "rev" => (@$maxRev->rev + 1),
                    "log" => json_encode(["qty" => $databf->qty, "total" => $databf->total, "revision" => $databf->revision, "log" => $databf->log, "reason" => $databf->reason])
                ], ["id" => $id]
            );

            if($send){
                echo json_encode(array("title" => "Good Job", "message" => "Update data Successfully", "theme" => "success"));
            }else{
                echo json_encode(array("title" => "Failed", "message" => "Update data Failed", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('purchase_orders', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'po_no' => $data->val($i, 2),
                'po_date' => $data->val($i, 3),
                'item_id' => $data->val($i, 4),
                'item_name' => $data->val($i, 5),
                'qty' => $data->val($i, 6),
                'price' => $data->val($i, 7),
                'currency' => $data->val($i, 8),
                'uom' => $data->val($i, 9),
                'leadtime' => $data->val($i, 10),
                'eta' => $data->val($i, 11),
                'forecast_1' => $data->val($i, 12),
                'forecast_2' => $data->val($i, 13),
                'forecast_3' => $data->val($i, 14),
                'contact' => $data->val($i, 15),
                'email' => $data->val($i, 16),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $item_id = $data['item_id'];
            $po_no = $data['po_no'];

            $purchase_orders = $this->api->read("purchase_orders", [], ["item_id" => $item_id, "purchase_order_no" => $po_no]);
            $mst_item = $this->api->read("mst_item_raw", [], ["item_id" => $item_id]);
            $mst_item_family = $this->api->read("mst_item_family", [], ["number" => @$mst_item->pfm_id]);

            if(empty($purchase_orders)){
                if(empty($mst_item)){
                    echo json_encode(array("title" => "Not Found", "message" => "Part No ".$item_id." Not Found in Master Item", "theme" => "error"));
                }else{
                    $postFinal = array_merge($data, array("product_family" => $mst_item_family->number, "product_family_name" => $mst_item_family->name));
                    $send = $this->api->create('purchase_orders', $postFinal, "PO", "JUTA");
                    echo $send;
                }
            }else{
                echo json_encode(array("title" => "Duplicate", "message" => "Part No ".$item_id." in PO ".$po_no." is Duplicate", "theme" => "error"));
            }
        }
    }

    public function print_po($po_no, $export = "")
    {
        $purchase_orders_total = $this->api->reads('purchase_orders', [], ["purchase_order_no" => base64_decode($po_no)]);
        $purchase_orders = $this->api->read('purchase_orders', [], ["purchase_order_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->api->read('suppliers', [], ["id" => @$purchase_orders->supplier_id]);
        $approval = $this->api->read('approval', [], ["table_name" => "purchase_orders"]);

        $user_3 = $this->api->read('user', [], ["username" => $approval->user_approval_2]);
        $user_4 = $this->api->read('user', [], ["username" => $approval->user_approval_3]);
        
        $qr_user_3 = "";
        $qr_user_4 = "";
        
        if($purchase_orders->approved == 3){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
        }elseif($purchase_orders->approved == 4){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
            $qr_user_4 = "src='" .base_url('assets/image/qrcode/' . @$user_4->name . '.png')."'";
        }

        // $month_1 =  date('M Y', strtotime('+1 month', strtotime(@$purchase_orders->purchase_order_date)));
        // $month_2 =  date('M Y', strtotime('+2 month', strtotime(@$purchase_orders->purchase_order_date)));
        // $month_3 =  date('M Y', strtotime('+3 month', strtotime(@$purchase_orders->purchase_order_date)));

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_orders_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_orders->purchase_order_no, "assets/image/qrcode/");

        $this->createQrcode(@$user_3->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_4->name, "assets/image/qrcode/");

        if($export == ""){
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
        }else{
            $media = ".print {
                        zoom: 0.5;
                    }";
            $media2 = "";
        }

        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->purchase_order_no . '</title>
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
                        '.$media.'
                    </style>
                    <body>
                        '.$media2.'
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.item_supplier, d.name as maker_name, d.type as maker_type');
            $this->db->from('purchase_orders a');
            $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
            $this->db->join('supplier_items c', 'a.supplier_id = c.supplier_id and a.item_id = c.item_id');
            $this->db->join('mst_maker d', 'c.maker_code = d.number');
            $this->db->where('a.purchase_order_no', base64_decode($po_no));
            $this->db->where('a.qty >', 0);
            $this->db->order_by('a.item_id');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . base_url('assets/image/config/' . $config->favicon) . '" width="40" />
                                </th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->purchase_order_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100"></td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>PURCHASE ORDER</u></h3>
                                    <small>NO : ' . @$purchase_orders->purchase_order_no . '</small>
                                </center>
                                <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Supplier</td>
                                        <td width="10">:</td>
                                        <td width="73%">
                                            <b>' . @$supplier->name . '</b>
                                        </td>
                                        <td style="text-align:left; padding-right: 20px;" rowspan="7">
                                            PO Date: <b>' . date("d F Y", strtotime($purchase_orders->purchase_order_date)) . '</b><br>
                                            Revision: <b>' . $purchase_orders->revision . '</b><br>
                                            Revision Date: <b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                            Payment Terms: <b>' . @$supplier->payment_term . ' Days</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Attention</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->attention . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Page</td>
                                        <td width="10">:</td>
                                        <td><b>' . $hal  . '</b> of <b> ' . $page . '</b></td>
                                    </tr>
                                </table>
                                <table class="customers" style="width:100%;">
                                    <tr>
                                        <th rowspan="2" style="text-align:center;">No</th>
                                        <th rowspan="2" style="text-align:center;">Part No</th>
                                        <th rowspan="2" style="text-align:center;">Part No Vendor</th>
                                        <th rowspan="2" style="text-align:center;">Part Name</th>
                                        <th rowspan="2" style="text-align:center;">Maker</th>
                                        <th rowspan="2" style="text-align:center;">ETA</th>
                                        <th rowspan="2" style="text-align:center;">ETD</th>
                                        <th rowspan="2" style="text-align:center;">Qty</th>
                                        <th rowspan="2" style="text-align:center;">Unit</th>
                                        <th rowspan="2" style="text-align:center;">Unit<br>Price</th>
                                        <th rowspan="2" style="text-align:center;">Currency</th>
                                        <th rowspan="2" style="text-align:center;">Amount</th>
                                        <th colspan="3" style="text-align:center;">Forecast</th>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center;">M1</th>
                                        <th style="text-align:center;">M2</th>
                                        <th style="text-align:center;">M3</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $item_id = $record['item_id'];
                $revision = $record['revision'];
                $p_month = $record['p_month'];
                $p_year = $record['p_year'];
                $ending = date("Y-m-t", strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
                $leadtime = date('Y-m-01', strtotime('+'.$record['leadtime'].' days', strtotime($ending)));
                if($record['maker_type'] == "LOCAL"){
                    $etd = date("Y-m-d", strtotime("-14 days", strtotime($record['eta'])));
                }else{
                    $etd = date("Y-m-d", strtotime("-21 days", strtotime($record['eta'])));
                }
                
                $m1 = date('Y-m-01', strtotime($leadtime));
                $m2 = date('Y-m-01', strtotime('+1 month', strtotime($leadtime)));
                $m3 = date('Y-m-01', strtotime('+2 month', strtotime($leadtime)));
                $m11 = "m".date('n', strtotime($leadtime));
                $m22 = "m".date('n', strtotime('+1 month', strtotime($leadtime)));
                $m33 = "m".date('n', strtotime('+2 month', strtotime($leadtime)));

                $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms1 = $qmps1->row();

                $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms2 = $qmps2->row();

                $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
                $rpms3 = $qmps3->row();

                $forecast = $this->forecast($item_id, $record['p_month'], $record['p_year'], @$revision);

                if(@$rpms1->qty > 0){
                    $prod1 = @$rpms1->qty;
                }else{
                    $prod1 = @$forecast->$m11;
                }

                if(@$rpms2->qty > 0){
                    $prod2 = @$rpms2->qty;
                }else{
                    $prod2 = @$forecast->$m22;
                }

                if(@$rpms3->qty > 0){
                    $prod3 = @$rpms3->qty;
                }else{
                    $prod3 = @$forecast->$m33;
                }

                $subtotal += ($record['total']);
                if ($record['currency'] == "USD") {
                    $digits = 4;
                } elseif ($record['currency'] == "JPY") {
                    $digits = 2;
                } else {
                    $digits = 0;
                }

                if(!empty($record['reason'])){
                    $styles = "background:#FFDD9E;";
                }else{
                    $styles = "";
                }
                
                $html .= '  <tr style="'.$styles.'">
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_id'] . '</td>
                                <td>' . $record['item_supplier'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td>' . $record['maker_name'] . '</td>
                                <td style="text-align:center;">' . $record['eta'] . '</td>
                                <td style="text-align:center;">' . $etd . '</td>
                                <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['price'], $digits) . '</td>
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], $digits) . '</td>
                                <td style="text-align:right;">' . number_format($prod1) . '</td>
                                <td style="text-align:right;">' . number_format($prod2) . '</td>
                                <td style="text-align:right;">' . number_format($prod3) . '</td>
                            </tr>';
                $row++;
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="9" rowspan="5">
                                    <b>Note :</b> <br>';
                $html .= '      </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Sub Total</th>
                                <th style="text-align:right;">' . number_format($subtotal, $digits) . '</th>
                                <th style="text-align:left" colspan="3" rowspan="4"></th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">VAT (' . @$supplier->vat . '%)</th>
                                <th style="text-align:right;">' . number_format(round((@$subtotal * @$supplier->vat) / 100), $digits) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Down Payment</th>
                                <th style="text-align:right;">-</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Total Amount</th>
                                <th style="text-align:right;">' . number_format($subtotal + ((@$subtotal * @$supplier->vat) / 100), $digits) . '</th>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }
            
            if (($i + 1) == $page) {
                $html .= '  <div style="width:100%; display: grid; grid-template-columns: auto auto auto;">
                                <div style="width:50%;">
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th width="200" style="text-align:center;">Supplier Approval</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:center;">(...................................)</th>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:50%; position: absolute; right: 50px;">
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th colspan="2" width="800" style="text-align:center;">Approved By</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"><img '.$qr_user_3.' width="80"/></th>
                                            <th style="height:80px;"><img '.$qr_user_4.' width="80"/></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:center;">'.@$user_3->name.'<br>'.@$user_3->position.'</th>
                                            <th style="height:20px; text-align:center;">'.@$user_4->name.'<br>'.@$user_4->position.'</th>
                                        </tr>
                                    </table>
                                    <br>
                                    <i style="font-size:8px;">Electronic Auto Generating Approval No Need Signature</i>
                                </div>
                            </div>

                        <br>
                        <b style="font-size:12px;">Term & Condition</b>
                        <br>
                        <table style="width:100%; font-size:12px; margin-top:20px;">
                            <tr>
                                <td width="20">1.</td>
                                <td>Please Acknowledge us in one week of time upon Receipt of PO <br>email to : takao.masahiko@tb-banshu.com, procurement01@banshu-ind.com, procurement02@banshu-ind.com</td>
                            </tr>
                            <tr>
                                <td width="20">2.</td>
                                <td>We will buy the next month confirmed FC in this PO</td>
                            </tr>
                            <tr>
                                <td width="20">3.</td>
                                <td>The PO quantity and forecast quantity is as per our customer demand</td>
                            </tr>
                        </table><br>
                        PLEASE PROCESS OUR PO FORECAST
                    </div>
                </div>';
            }else{
                $html .= "</div></div>";
            }

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;

            // <img '.$qr_user_4.' width="80"/>
        }
        
        if($export == ""){
            $html .= '<script>window.print()</script>';
            die($html);
        }else{
            // Load HTML content
            $this->dompdf_gen->loadHtml($html);

            // (Optional) Set paper size and orientation
            $this->dompdf_gen->setPaper('A4', 'potrait');

            // Render the HTML as PDF
            $this->dompdf_gen->render();

            // Output the generated PDF (1 = download and 0 = preview)
            // $this->dompdf_gen->stream("sample.pdf", array("Attachment" => 0));

            $filePath = FCPATH . 'assets/po/'.base64_decode($po_no).'.pdf'; // FCPATH ensures the path is relative to the project root
            $this->dompdf_gen->save($filePath);
        }
    }

    public function print_delivery($po_no)
    {
        $po_no = base64_decode($po_no);
        $purchase_order = $this->api->read('purchase_orders', [], ["purchase_order_no" => $po_no], "", "revision", "desc");
        $supplier = $this->api->read('suppliers', [], ["id" => @$purchase_order->id]);

        $filter_year = $purchase_order->p_year;
        $filter_month = $purchase_order->p_month;
        $revision = $purchase_order->revision;

        $period_1 = date("F Y", strtotime($filter_year."-".$filter_month."-01"));
        $period_2 = date("F Y",  strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_3 = date("F Y",  strtotime("2 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_4 = date("F Y",  strtotime("3 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_5 = date("F Y",  strtotime("4 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_6 = date("F Y",  strtotime("5 month", strtotime($filter_year."-".$filter_month."-01")));

        $config = $this->db->get('config')->row();

        $html = '<html>
                    <head>
                        <title>Schedule Delivery '.$po_no.'</title>
                        <link rel="icon" href="' . base_url('assets/image/config/' . $config->favicon) . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 10px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                        <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>
                        <div class="print">';

        $this->db->select('*');
        $this->db->from('delivery_schedules');
        $this->db->where('po_no', $po_no);
        $this->db->order_by('part_no');
        $records = $this->db->get()->result_array();

        $html .= '  <table style="width:100%;">
                        <tr>
                            <th width="10">
                                <img src="' . base_url('assets/image/config/' . $config->favicon) . '" width="40" />
                            </th>
                            <td width="300" style="padding:10px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                            <th width="100" style="text-align:right;"></th>
                        </tr>
                    </table>
                        <div style="padding:10px;">
                            <center>
                                <br>
                                <h3 style="margin:0;"><u>SCHEDULE DELIVERY</u></h3>
                                <small>NO : ' . @$po_no . '</small>
                            </center>
                            <table style="width:50%; font-size:12px; margin-bottom:10px;">
                                <tr>
                                    <td width="20">Period</td>
                                    <td width="10">:</td>
                                    <td width="100"><b>' . date("F Y", strtotime($purchase_order->p_year."-".$purchase_order->p_month."-01")) . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Revision</td>
                                    <td width="10">:</td>
                                    <td><b>' . $purchase_order->revision . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Part Family</td>
                                    <td width="10">:</td>
                                    <td><b>' . $purchase_order->product_family_name . '</b></td>
                                </tr>
                                <tr>
                                    <td width="20">Supplier</td>
                                    <td width="10">:</td>
                                    <td><b>' . @$supplier->name . '</b></td>
                                </tr>
                            </table>
                            <table id="customers">
                                <tr>
                                    <th rowspan="2" width="10" style="text-align:center;">No</th>
                                    <th rowspan="2" width="100" style="text-align:center;">Part No</th>
                                    <th rowspan="2" width="50" style="text-align:center;">Part Name</th>
                                    <th rowspan="2" width="30" style="text-align:center;">LT</th>
                                    <th rowspan="2" width="50" style="text-align:center;">MPQ</th>
                                    <th rowspan="2" width="50" style="text-align:center;">MOQ</th>
                                    <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_1.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_2.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_3.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_4.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_5.'</th>
                                    <th colspan="3" width="50" style="text-align:center;">'.$period_6.'</th>
                                </tr>
                                <tr>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                    <th width="50" style="text-align:center;">Qty</th>
                                    <th width="50" style="text-align:center;">ETD</th>
                                    <th width="50" style="text-align:center;">ETA</th>
                                </tr>';
        $no = 1;
        foreach ($records as $record) {
            if($record['delivey_date_1'] == "0000-00-00"){ $eta_1 = "-"; }else{ $eta_1 = $record['delivey_date_1']; }
            if($record['delivey_date_2'] == "0000-00-00"){ $eta_2 = "-"; }else{ $eta_2 = $record['delivey_date_2']; }
            if($record['delivey_date_3'] == "0000-00-00"){ $eta_3 = "-"; }else{ $eta_3 = $record['delivey_date_3']; }
            if($record['delivey_date_4'] == "0000-00-00"){ $eta_4 = "-"; }else{ $eta_4 = $record['delivey_date_4']; }
            if($record['delivey_date_5'] == "0000-00-00"){ $eta_5 = "-"; }else{ $eta_5 = $record['delivey_date_5']; }
            if($record['delivey_date_6'] == "0000-00-00"){ $eta_6 = "-"; }else{ $eta_6 = $record['delivey_date_6']; }
            if($record['etd_1'] == "0000-00-00"){ $etd_1 = "-"; }else{ $etd_1 = $record['etd_1']; }
            if($record['etd_2'] == "0000-00-00"){ $etd_2 = "-"; }else{ $etd_2 = $record['etd_2']; }
            if($record['etd_3'] == "0000-00-00"){ $etd_3 = "-"; }else{ $etd_3 = $record['etd_3']; }
            if($record['etd_4'] == "0000-00-00"){ $etd_4 = "-"; }else{ $etd_4 = $record['etd_4']; }
            if($record['etd_5'] == "0000-00-00"){ $etd_5 = "-"; }else{ $etd_5 = $record['etd_5']; }
            if($record['etd_6'] == "0000-00-00"){ $etd_6 = "-"; }else{ $etd_6 = $record['etd_6']; }

            $html .= '  <tr>
                            <td  style="text-align:center;">' . $no . '</td>
                            <td>' . $record['part_no'] . '</td>
                            <td>' . $record['part_name'] . '</td>
                            <td style="text-align:right;">' . number_format($record['leadtime']) . '</td>
                            <td style="text-align:right;">' . number_format($record['mpq']) . '</td>
                            <td style="text-align:right;">' . number_format($record['moq']) . '</td>
                            <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_1']) . '</td>
                            <td style="text-align:center;">'.$etd_1.'</td>
                            <td style="text-align:center;">'.$eta_1.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_2']) . '</td>
                            <td style="text-align:center;">'.$etd_2.'</td>
                            <td style="text-align:center;">'.$eta_2.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_3']) . '</td>
                            <td style="text-align:center;">'.$etd_3.'</td>
                            <td style="text-align:center;">'.$eta_3.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_4']) . '</td>
                            <td style="text-align:center;">'.$etd_4.'</td>
                            <td style="text-align:center;">'.$eta_4.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_5']) . '</td>
                            <td style="text-align:center;">'.$etd_5.'</td>
                            <td style="text-align:center;">'.$eta_5.'</td>
                            <td style="text-align:right;">' . number_format($record['delivey_qty_6']) . '</td>
                            <td style="text-align:center;">'.$etd_6.'</td>
                            <td style="text-align:center;">'.$eta_6.'</td>
                        </tr>';
            $no++;
        }
        $html .= '</table>';
        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function csv()
    {
        header("Content-type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=purchase_orders_" . time() . ".csv");
        $output = fopen("php://output", "w");
        fputcsv($output, array(
            "Month", "Year", "Revision", "PO No", "PO Date", "Part No", "Part Name", "Part Supplier", "Product Family", "Supplier No",
            "Supplier Name", "Qty", "Price", "Total", "Currency", "Uom", "Leadtime", "ETA", "Forecast 1",
            "Forecast 2", "Forecast 3"
        ));

        $this->db->select('a.*, b.item_supplier, c.number as supplier_no, c.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->where('a.type', "REGULAR");
        $this->db->order_by('a.p_month', 'asc');
        $this->db->order_by('a.p_year', 'asc');
        $this->db->order_by('a.revision', 'asc');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('a.product_family_name', 'asc');
        $this->db->order_by('a.item_id', 'asc');
        $records = $this->db->get()->result_array();

        $no = 1;
        foreach ($records as $data) {
            $data = array(
                $data['p_month'],
                $data['p_year'],
                $data['revision'],
                $data['purchase_order_no'],
                $data['purchase_order_date'],
                $data['item_id'],
                $data['item_supplier'],
                $data['item_name'],
                $data['product_family_name'],
                $data['supplier_no'],
                $data['supplier_name'],
                $data['qty'],
                $data['price'],
                $data['total'],
                $data['currency'],
                $data['uom'],
                $data['leadtime'],
                $data['eta'],
                $data['forecast_1'],
                $data['forecast_2'],
                $data['forecast_3'],
            );

            fputcsv($output, $data);

            $no++;
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("YmdHis");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        //Select Query
        $this->db->select('a.*, b.item_supplier, c.number as supplier_no, c.name as supplier_name, d.maker_code, e.name as maker_name, e.type as maker_type');
        $this->db->from('purchase_orders a');
        $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_id = d.item_id');
        $this->db->join('mst_maker e', 'd.maker_code = e.number', 'left');
        $this->db->where('a.type', "REGULAR");
        $this->db->like('a.p_month', $filter_month);
        $this->db->like('a.p_year', $filter_year);
        if($filter_revision != ""){
            $this->db->like('a.revision', $filter_revision);
        }
        if($filter_product_family != ""){
            $this->db->where('a.product_family_name', $filter_product_family);
        }
        if($filter_part_no != ""){
            $this->db->where('a.item_id', $filter_part_no);
        }
        if($filter_supplier != ""){
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if($filter_po != ""){
            $this->db->where('a.purchase_order_no', $filter_po);
        }
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('a.product_family_name', 'asc');
        $this->db->order_by('a.item_id', 'asc');
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
                            <small>PURCHASE ORDER</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
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
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">PO No</th>
                <th style="text-align:center;">PO Date</th>
                <th style="text-align:center;">Revision</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Part Name</th>
                <th style="text-align:center;">Part Supplier</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Supplier No</th>
                <th style="text-align:center;">Supplier Name</th>
                <th style="text-align:center;">Maker Code</th>
                <th style="text-align:center;">Maker Name</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Price</th>
                <th style="text-align:center;">Total</th>
                <th style="text-align:center;">Currency</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Leadtime</th>
                <th style="text-align:center;">ETA</th>
                <th style="text-align:center;">ETD</th>
                <th style="text-align:center;">Contact</th>
                <th style="text-align:center;">Email</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $item_id = $data['item_id'];
            $revision = $data['revision'];
            $p_month = $data['p_month'];
            $p_year = $data['p_year'];
            $ending = date("Y-m-t", strtotime($data['p_year'] . "-" . $data['p_month'] . "-01"));
            $leadtime = date('Y-m-01', strtotime('+'.$data['leadtime'].' days', strtotime($ending)));

            if($data['maker_type'] == "LOCAL"){
                $etd = date("Y-m-d", strtotime("-14 days", strtotime($data['eta'])));
            }else{
                $etd = date("Y-m-d", strtotime("-21 days", strtotime($data['eta'])));
            }

            // $m1 = date('Y-m-01', strtotime($leadtime));
            // $m2 = date('Y-m-01', strtotime('+1 month', strtotime($leadtime)));
            // $m3 = date('Y-m-01', strtotime('+2 month', strtotime($leadtime)));
            // $m11 = "m".date('n', strtotime($leadtime));
            // $m22 = "m".date('n', strtotime('+1 month', strtotime($leadtime)));
            // $m33 = "m".date('n', strtotime('+2 month', strtotime($leadtime)));

            // $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
            // $rpms1 = $qmps1->row();

            // $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
            // $rpms2 = $qmps2->row();

            // $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and product_no = '$item_id' and revision = '$revision' and p_month = '$p_month' and p_year = '$p_year'");
            // $rpms3 = $qmps3->row();

            // $forecast = $this->forecast($item_id, $data['p_month'], $data['p_year'], @$revision);

            // if(@$rpms1->qty > 0){
            //     $prod1 = @$rpms1->qty;
            // }else{
            //     $prod1 = @$forecast->$m11;
            // }

            // if(@$rpms2->qty > 0){
            //     $prod2 = @$rpms2->qty;
            // }else{
            //     $prod2 = @$forecast->$m22;
            // }

            // if(@$rpms3->qty > 0){
            //     $prod3 = @$rpms3->qty;
            // }else{
            //     $prod3 = @$forecast->$m33;
            // }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['purchase_order_no'] . '</td>
                            <td>' . $data['purchase_order_date'] . '</td>
                            <td style="text-align:center;">' . $data['revision'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['product_family_name'] . '</td>
                            <td>' . $data['supplier_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['maker_code'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:right;">' . $data['price'] . '</td>
                            <td style="text-align:right;">' . $data['total'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td>' . $data['eta'] . '</td>
                            <td>' . $etd . '</td>
                            <td>' . $data['contact'] . '</td>
                            <td>' . $data['email'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function sendMail(){
        error_reporting(0);
        $post = $this->input->post();

        $mail_to = explode(";", $post['mail_to']);
        // $mail_to = "hilmanfadillah11@gmail.com";
        $mail_cc = explode(";", $post['mail_cc']);
        // $mail_cc = "aeconsys.dev@gmail.com";
        $mail_format = $post['mail_format'];
        $mail_po = $post['mail_po'];

        $message = $this->api->read("config_mail", [], ["module" => "purchase_orders", "language" => $mail_format]);
        $po = $this->api->read("purchase_orders", [], ["purchase_order_no" => $mail_po]);
        $supplier = $this->api->read("suppliers", [], ["id" => @$po->supplier_id]);
        $contact_name = @$supplier->attention;
        $company = @$supplier->attention;
        $address = @$supplier->address;

        //Setting Upload Image
        $file = $_FILES["attachment"]["name"];
        $extension_explode = explode('.', $file);
        $extension_final = strtolower(end($extension_explode));
        $temporary = $_FILES["attachment"]['tmp_name'];

        $file2 = $_FILES["attachment2"]["name"];
        $extension_explode2 = explode('.', $file2);
        $extension_final2 = strtolower(end($extension_explode2));
        $temporary2 = $_FILES["attachment2"]['tmp_name'];

        $file3 = $_FILES["attachment3"]["name"];
        $extension_explode3 = explode('.', $file3);
        $extension_final3 = strtolower(end($extension_explode3));
        $temporary3 = $_FILES["attachment3"]['tmp_name'];

        $file4 = $_FILES["attachment4"]["name"];
        $extension_explode4 = explode('.', $file4);
        $extension_final4 = strtolower(end($extension_explode4));
        $temporary4 = $_FILES["attachment4"]['tmp_name'];

        $file5 = $_FILES["attachment5"]["name"];
        $extension_explode5 = explode('.', $file5);
        $extension_final5 = strtolower(end($extension_explode5));
        $temporary5 = $_FILES["attachment5"]['tmp_name'];

        if ((in_array($extension_final, ["pdf"]) === true || $file != "") && (in_array($extension_final2, ["pdf"]) === true || $file2 != "")) {
                @move_uploaded_file($temporary, "assets/po/" . $file);
                @move_uploaded_file($temporary2, "assets/po/" . $file2);
                @move_uploaded_file($temporary3, "assets/po/" . $file3);
                @move_uploaded_file($temporary4, "assets/po/" . $file4);
                @move_uploaded_file($temporary5, "assets/po/" . $file5);
                sleep(3);

                $config = array(
                    'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
                    'smtp_host' => 'mail.banshu-ind.com',
                    'smtp_port' => 465,
                    'smtp_user' => 'procurement01@banshu-ind.com',
                    'smtp_pass' => 'pRo!177$BeiAi',
                    'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
                    'mailtype' => 'html', //plaintext 'text' mails or 'html'
                    'smtp_timeout' => '4', //in seconds
                    'charset' => 'iso-8859-1',
                    'wordwrap' => TRUE
                );
                
                $description = $message->description;
                $description = str_replace("{{contact}}", $contact_name, $description);
                $description = str_replace("{{po_no}}", $mail_po, $description);
                $description = str_replace("{{company}}", $company, $description);
                $description = str_replace("{{address}}", $address, $description);

                $this->email->initialize($config);
                $this->email->from($post['mail_from'], 'BANSHU ELECTRIC INDONESIA');
                $this->email->to($mail_to);
                $this->email->cc($mail_cc);
                $this->email->subject($post['mail_subject']);
                $this->email->message($description);

                // Attach the PDF file
                $attachment = 'assets/po/'.$file; // Replace with the path to your PDF file
                $attachment2 = 'assets/po/'.$file2; // Replace with the path to your PDF file
                $attachment3 = 'assets/po/'.$file3; // Replace with the path to your PDF file
                $attachment4 = 'assets/po/'.$file4; // Replace with the path to your PDF file
                $attachment5 = 'assets/po/'.$file5; // Replace with the path to your PDF file
                
                $this->email->attach($attachment);
                $this->email->attach($attachment2);
                if($file3 != ""){
                    $this->email->attach($attachment3);
                }
                if($file4 != ""){
                    $this->email->attach($attachment4);
                }
                if($file5 != ""){
                    $this->email->attach($attachment5);
                }
                
                if ($this->email->send()) {
                    echo json_encode(array("title" => "Successs", "message" => "Email Sending Successfully", "theme" => "success"));

                    $this->db->where("purchase_order_no", $mail_po);
                    $this->db->update('purchase_orders', ["email_date" => date("Y-m-d H:i:s"), "email_status" => "SENT", "confirm" => "Pending"]);

                } else {
                    echo json_encode(array("title" => "Failed", "message" => "Email Sending Failed", "theme" => "error"));
                }
        } else {
            show_error("Your extension file is not recognized", 200, "File Upload Error");
        }
    }
}
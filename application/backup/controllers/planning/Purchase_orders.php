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

    function readSupplier(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->api->query("SELECT DISTINCT supplier_no, supplier_name FROM purchase_orders WHERE supplier_no like '%$post%' or supplier_name like '%$post%'");
        echo json_encode($datas);
    }

    function readPo($p_month, $p_year, $revision, $product_family){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->api->query("SELECT DISTINCT purchase_order_no, supplier_no, supplier_name, product_family_name FROM purchase_orders 
            WHERE p_month = '$p_month' and p_year = '$p_year' and revision = '$revision' and product_family = '$product_family' and (
            purchase_order_no like '%$post%' or supplier_name like '%$post%' or product_family_name like '%$post%')");
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

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, SUM(a.qty) as total_qty, COUNT(c.po_no) as print_sd');
            $this->db->from('purchase_orders a');
            $this->db->join('delivery_schedules c', "a.purchase_order_no = c.po_no", 'left');
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.product_family_name', $filter_product_family);
            $this->db->like('a.item_id', $filter_part_no);
            $this->db->like('a.supplier_no', $filter_supplier);
            $this->db->group_by('a.supplier_no');
            $this->db->group_by('a.product_family');
            $this->db->group_by('a.purchase_order_no');
            $this->db->order_by('a.supplier_name');
            $this->db->group_by('a.product_family');
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

        //Select Query
        $this->db->select('*');
        $this->db->from('purchase_orders');
        $this->db->where('purchase_order_no', $purchase_order_no);
        $this->db->where('revision', $revision);
        $this->db->like('item_id', $filter_part_no);
        $this->db->order_by('item_id');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $total_qty = 0;
        $total_amount = 0;
        foreach ($records as $record) {
            $total_qty += $record['qty'];
            $total_amount += $record['total'];
            array_push($data, $record);
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $data,
            'footer' => array([
                "item_id" => "Grand Total",
                "qty" => $total_qty,
                "price" => ($total_amount / $total_qty),
                "total" => $total_amount
            ])
        ]);
        echo json_encode($result);
    }

    public function purchaseOrderNo($code){
        $sqlGetID	= $this->db->query("SELECT max(purchase_order_no) as kode FROM purchase_orders WHERE purchase_order_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;
        
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID	= sprintf("%04s", $urutan);
        }

        return $code . $autoID;
    }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));

            if($filter_product_family != ""){
                if($filter_product_no != ""){
                    $array_pfm = array("product_family_name" => $filter_product_family, "item_id" => $filter_product_no);
                }else{
                    $array_pfm = array("product_family_name" => $filter_product_family);
                }
            }else{
                $array_pfm = array();
            }

            $this->api->delete("purchase_orders", array_merge(["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision], $array_pfm));

            //Select Query
            $this->db->select('a.*, b.number as pfm_number, d.name as uom, e.currency, e.price, e.supplier_part_code');
            $this->db->from('purchase_requests a');
            $this->db->join('mst_item_family b', 'a.product_family = b.name');
            $this->db->join('mst_item_raw c', 'a.part_no = c.item_id');
            $this->db->join('uom d', 'c.um_id = d.number', 'left');
            $this->db->join('upload_gp e', "c.item_gp = e.product_no and a.supplier_no = e.supplier_no and e.p_month = '$filter_month' and e.p_year = '$filter_year' and e.revision = '$filter_revision'");
            // $this->db->join('notification f', "a.id = f.table_id and f.table_name = 'purchase_requests'", 'left');
            $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.purchase_request >', 0);
            // $this->db->where("(f.id_user = '' or f.id_user is null)");
            $this->db->like('a.part_no', $filter_product_no);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->group_by('a.supplier_name');
            $this->db->group_by('a.part_no');
            $this->db->order_by('a.supplier_name');
            $this->db->order_by('a.product_family');
            $purchase_requests = $this->db->get()->result_array();

            $arr = array();
            $no = 0;
            $end_supplier = "";
            $end_product_family = "";
            $end_purchase_order_no = "";
            foreach ($purchase_requests as $purchase_request) {
                $supplier_no = $purchase_request['supplier_no'];
                $leadtime = $purchase_request['leadtime'];
                $eta = date("Y-m-d", strtotime("+".$leadtime." days", strtotime(date("Y-m-d"))));

                // $supplierq = $this->gp->query("SELECT code, `name`, email, contact_person FROM suppliers WHERE `code` LIKE '%$supplier_no%' and deleted_by is null");
                // $supplier = $supplierq->result_array();

                $supplier = $this->apiSupplier($supplier_no);
                // $supplier = $gpai['data'];

                if($supplier_no == $end_supplier && $purchase_request['product_family'] == $end_product_family){
                    $purchase_order_no = $end_purchase_order_no;
                    $no++;
                }else{
                    $purchase_order_no = $this->purchaseOrderNo("PO".@$supplier_no.date("Ym"). "-" .$purchase_request['pfm_number']);
                    $no = 1;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "purchase_order_no" => $purchase_order_no,
                    "purchase_order_date" => date("Y-m-d"),
                    "line_no" => $no,
                    "item_id" => $purchase_request['part_no'],
                    "item_vendor" => $purchase_request['supplier_part_code'],
                    "item_name" => $purchase_request['part_name'],
                    "product_family" => $purchase_request['pfm_number'],
                    "product_family_name" => $purchase_request['product_family'],
                    "supplier_no" => @$supplier_no,
                    "supplier_name" => $purchase_request['supplier_name'],
                    "qty" => $purchase_request['purchase_request'],
                    "price" => $purchase_request['price'],
                    "total" => ($purchase_request['purchase_request'] * $purchase_request['price']),
                    "currency" => $purchase_request['currency'],
                    "uom" => $purchase_request['uom'],
                    "leadtime" => $purchase_request['leadtime'],
                    "eta" => $eta,
                    "contact" => @$supplier[0]['contact_person'],
                    "email" => strtolower(@$supplier[0]['email']),
                    "forecast_1" => $purchase_request['forecast_plus_1'],
                    "forecast_2" => $purchase_request['forecast_plus_2'],
                    "forecast_3" => $purchase_request['forecast_plus_3'],
                );

                $end_supplier = $supplier_no;
                $end_product_family = $purchase_request['product_family'];
                $end_purchase_order_no = $purchase_order_no;
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->api->reads('purchase_orders', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_id" => $post['item_id'],
                "supplier_no" => $post['supplier_no']
            ]);

            if (count($records) > 0) {
                $send = $this->api->update('purchase_orders', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "item_id" => $post['item_id'],
                    "supplier_no" => $post['supplier_no']
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

    public function pushData(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);

            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", $post['item_id']);
            $mst_item = $this->dummy->get()->row();

            $this->db->select('*');
            $this->db->from("poc");
            $this->db->where("poc_id", $post['purchase_order_no']);
            $this->db->where("item_id", $post['item_id']);
            $poc = $this->db->get()->row();

            $postFinal = array(
                "poc_id" => $post['purchase_order_no'],
                "ven_id" => @$mst_item->ven_id,
                "poc_date" => $post['purchase_order_date'],
                "poc_emp" => $this->session->name,
                "item_id" => $post['item_id'],
                "poc_reqdate" => date("Y-m-d"),
                "poc_reqqty" => $post['qty'],
                "currency" => $post['currency'],
                "periode" => date("Ym"),
                // "poc_remark" => $post['reason'],
            );

            if($poc){
                $this->db->where("poc_id", $post['purchase_order_no']);
                $this->db->where("item_id", $post['item_id']);
                if ($this->db->update('poc', ["poc_reqqty" => $post['qty']])) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['item_id'] . " | Data Update Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['item_id'] . " | Data Unsaved", "theme" => "error"));
                }
            }else{
                if ($this->db->insert('poc', $postFinal)) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['item_id'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['item_id'] . " | Data Unsaved", "theme" => "error"));
                }
            }

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

            $send = $this->api->update('purchase_orders', ["id" => $id], [
                "purchase_order_no" => $post['purchase_order_no'], 
                "supplier_no" => $post['supplier_no'], 
                "supplier_name" => $post['supplier_name'], 
                "qty" => $post['qty'], 
                "price" => $post['price'], 
                "currency" => $post['currency'], 
                "total" => $total, 
                "reason" => $post['reason'], 
                "revision" => $revision,
                "log" => json_encode(["qty" => $databf->qty, "total" => $databf->total, "revision" => $databf->revision, "log" => $databf->log, "reason" => $databf->reason])
            ]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
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

    public function print_po($po_no)
    {
        $purchase_orders_total = $this->api->reads('purchase_orders', [], ["purchase_order_no" => base64_decode($po_no)]);
        $purchase_orders = $this->api->read('purchase_orders', [], ["purchase_order_no" => base64_decode($po_no)], "", "revision", "desc");
        $approval = $this->api->read('approval', [], ["table_name" => "purchase_orders"]);

        $user_3 = $this->api->read('user', [], ["username" => $approval->user_approval_3]);
        $user_4 = $this->api->read('user', [], ["username" => $approval->user_approval_4]);
        
        $qr_user_3 = "";
        $qr_user_4 = "";
        
        if($purchase_orders->approved == 4){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
        }elseif($purchase_orders->approved == 5){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
            $qr_user_4 = "src='" .base_url('assets/image/qrcode/' . @$user_4->name . '.png')."'";
        }

        $supplier_code = $purchase_orders->supplier_no;
        // $supplierq = $this->gp->query("SELECT bill_address, payment_term_days, tax_rates, contact_person, phone_no, email FROM suppliers WHERE `code` = '$supplier_code' and deleted_by is null");
        // $supplier = $supplierq->result_array();

        $gpai = $this->apiSupplier($supplier_code);
        $supplier = $gpai['data'];

        // $month_1 =  date('M Y', strtotime('+1 month', strtotime(@$purchase_orders->purchase_order_date)));
        // $month_2 =  date('M Y', strtotime('+2 month', strtotime(@$purchase_orders->purchase_order_date)));
        // $month_3 =  date('M Y', strtotime('+3 month', strtotime(@$purchase_orders->purchase_order_date)));

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 8;
        $page = ceil(count($purchase_orders_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_orders->purchase_order_no, "assets/image/qrcode/");

        $this->createQrcode(@$user_3->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_4->name, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->purchase_order_no . '</title>
                        <link rel="icon" href="' . base_url('assets/image/config/' . $config->favicon) . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
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
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.item_supplier');
            $this->db->from('purchase_orders a');
            $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
            $this->db->where('a.purchase_order_no', base64_decode($po_no));
            $this->db->where('a.qty >', 0);
            $this->db->order_by('a.item_id');
            $this->db->limit(8, ($i * 8));
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
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Supplier</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$purchase_orders->supplier_name . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            PO Date:<br><b>' . date("d F Y", strtotime($purchase_orders->purchase_order_date)) . '</b><br>
                                            Revision:<br><b>' . $purchase_orders->revision . '</b><br>
                                            Revision Date:<br><b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                            Payment Terms:<br><b>' . @$supplier[0]['payment_term_days'] . ' Days</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Address</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier[0]['bill_address'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Attention</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier[0]['contact_person'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Phone</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier[0]['phone_no'] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Fax</td>
                                        <td width="10">:</td>
                                        <td><b>-</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Email</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier[0]['email'] . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <tr>
                                        <th rowspan="2" width="10" style="text-align:center;">No</th>
                                        <th rowspan="2" width="100" style="text-align:center;">Part No</th>
                                        <th rowspan="2" width="100" style="text-align:center;">Part No Vendor</th>
                                        <th rowspan="2" width="100" style="text-align:center;">Part Name</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Unit</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Unit<br>Price</th>
                                        <th rowspan="2" width="50" style="text-align:center;">Currency</th>
                                        <th rowspan="2" width="80" style="text-align:center;">Amount</th>
                                        <th colspan="3" width="50" style="text-align:center;">Forecast</th>
                                    </tr>
                                    <tr>
                                        <th width="50" style="text-align:center;">M1</th>
                                        <th width="50" style="text-align:center;">M2</th>
                                        <th width="50" style="text-align:center;">M3</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $item_id = $record['item_id'];
                $revision = $record['revision'];
                $ending = date("Y-m-t", strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
                $leadtime = date('Y-m-01', strtotime('+'.$record['leadtime'].' days', strtotime($ending)));
                
                $m1 = date('Y-m-01', strtotime($leadtime));
                $m2 = date('Y-m-01', strtotime('+1 month', strtotime($leadtime)));
                $m3 = date('Y-m-01', strtotime('+2 month', strtotime($leadtime)));
                $m11 = "m".date('n', strtotime($leadtime));
                $m22 = "m".date('n', strtotime('+1 month', strtotime($leadtime)));
                $m33 = "m".date('n', strtotime('+2 month', strtotime($leadtime)));

                $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and product_no = '$item_id' and revision = '$revision'");
                $rpms1 = $qmps1->row();

                $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and product_no = '$item_id' and revision = '$revision'");
                $rpms2 = $qmps2->row();

                $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and product_no = '$item_id' and revision = '$revision'");
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
                if ($record['currency'] != "IDR") {
                    $digits = 4;
                } else {
                    $digits = 0;
                }
                
                $html .= '  <tr>
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_id'] . '</td>
                                <td>' . $record['item_supplier'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['price'], $digits) . '</td>
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($prod1) . '</td>
                                <td style="text-align:right;">' . number_format($prod2) . '</td>
                                <td style="text-align:right;">' . number_format($prod3) . '</td>
                            </tr>';
                $row++;
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="6" rowspan="5">
                                    <b>Note :</b> <br>';
                $html .= '      </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Sub Total</th>
                                <th style="text-align:right;">' . number_format($subtotal, 2) . '</th>
                                <th style="text-align:left" colspan="3" rowspan="4"></th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">VAT (' . @$supplier[0]['tax_rates'] . '%)</th>
                                <th style="text-align:right;">' . number_format(round((@$subtotal * @$supplier[0]['tax_rates']) / 100), 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Down Payment</th>
                                <th style="text-align:right;">-</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Total Amount</th>
                                <th style="text-align:right;">' . number_format($subtotal + ((@$subtotal * @$supplier[0]['tax_rates']) / 100), 2) . '</th>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }
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
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;">'.@$user_3->name.'<br>'.@$user_3->position.'</th>
                                    <th style="height:20px; text-align:center;">'.@$user_4->name.'<br>'.@$user_4->position.'</th>
                                </tr>
                            </table>
                            <br>
                            <i style="font-size:8px;">Electronic Auto Generating Approval No Nedd Signature</i>
                        </div>
                    </div>

                    <br>
                    <b style="font-size:12px;">Term & Condition</b>
                    <br>
                    <table style="width:100%; font-size:12px; margin-top:20px;">
                        <tr>
                            <td width="20">1.</td>
                            <td>Please Acknowledge us in one week of time upon Receipt of PO <br>email to : takao.masahiko@tb-banshu.com, mohan@banshu-ind.com, cost.control@banshu-ind.com, mclproject@banshu-ind.com, mcl@banshu-ind.com</td>
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
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;

            // <img '.$qr_user_4.' width="80"/>
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }

    public function print_delivery($po_no)
    {
        $po_no = base64_decode($po_no);
        $purchase_order = $this->api->read('purchase_orders', [], ["purchase_order_no" => $po_no], "", "revision", "desc");

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
                                    <td><b>' . @$purchase_order->supplier_name . '</b></td>
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
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

        //Select Query
        $this->db->select('a.*, b.item_supplier');
        $this->db->from('purchase_orders a');
        $this->db->join('mst_item_raw b', 'a.item_id = b.item_id', 'left');
        $this->db->like('a.p_month', $filter_month);
        $this->db->like('a.p_year', $filter_year);
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.item_id', $filter_part_no);
        $this->db->like('a.supplier_no', $filter_supplier);
        $this->db->order_by('a.supplier_name', 'asc');
        $this->db->order_by('a.product_family_name', 'asc');
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
                <th style="text-align:center;">Product No</th>
                <th style="text-align:center;">Product Name</th>
                <th style="text-align:center;">Product Supplier</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Supplier No</th>
                <th style="text-align:center;">Supplier Name</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Price</th>
                <th style="text-align:center;">Total</th>
                <th style="text-align:center;">Currency</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Leadtime</th>
                <th style="text-align:center;">ETA</th>
                <th style="text-align:center;">Forecast 1</th>
                <th style="text-align:center;">Forecast 2</th>
                <th style="text-align:center;">Forecast 3</th>
                <th style="text-align:center;">Contact</th>
                <th style="text-align:center;">Email</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['purchase_order_no'] . '</td>
                            <td>' . $data['purchase_order_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['product_family_name'] . '</td>
                            <td>' . $data['supplier_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:right;">' . $data['price'] . '</td>
                            <td style="text-align:right;">' . $data['total'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td>' . $data['eta'] . '</td>
                            <td style="text-align:right;">' . $data['forecast_1'] . '</td>
                            <td style="text-align:right;">' . $data['forecast_2'] . '</td>
                            <td style="text-align:right;">' . $data['forecast_3'] . '</td>
                            <td>' . $data['contact'] . '</td>
                            <td>' . $data['email'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
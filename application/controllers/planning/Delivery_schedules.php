<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_schedules extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->pg = $this->load->database('pg', TRUE);
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/delivery_schedules');
        } else {
            redirect('error_access');
        }
    }

    public function readMonths()
    {
        $month = $this->input->get('month');
        $year = $this->input->get('year');

        $start = strtotime($year . "-" . $month . "-01");
        $end =  strtotime(date('Y-m-d', strtotime('+6 month', $start)));

        $monthName = array();
        while ($start < $end) {
            $monthName[] = date('M Y', $start);
            $start = strtotime("+1 month", $start);
        }

        die(json_encode($monthName));
    }
    
    public function readPo($filter_month, $filter_year, $filter_revision)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select('purchase_order_no, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like("a.purchase_order_no", $post);
        $this->db->or_like("b.name", $post);
        $this->db->group_by('a.purchase_order_no');
        $this->db->order_by('a.purchase_order_no', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            // $filter_month = base64_decode($this->input->get('filter_month'));
            // $filter_year = base64_decode($this->input->get('filter_year'));
            // $filter_revision = base64_decode($this->input->get('filter_revision'));
            // $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            // $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            // $filter_po_no = base64_decode($this->input->get('filter_po_no'));
            $purchase_order_no = base64_decode($this->input->get('purchase_order_no'));

            $this->db->select('a.*, b.mpq, b.moq');
            $this->db->from('purchase_orders a');
            $this->db->join('supplier_items b', 'a.supplier_id = b.supplier_id and a.item_id = b.item_id');
            $this->db->join('mst_maker c', 'b.maker_code = c.number');
            // $this->db->where('a.p_month', $filter_month);
            // $this->db->where('a.p_year', $filter_year);
            // $this->db->where('a.revision', $filter_revision);
            // $this->db->like('a.purchase_order_no', $filter_po_no);
            $this->db->where('a.purchase_order_no', $purchase_order_no);
            // $this->db->like('a.product_family_name', $filter_product_family);
            // $this->db->like('a.item_id', $filter_product_no);
            // $this->db->where('b.purchase_request >', 0);
            // $this->db->where('c.type', 'IMPORT');
            $this->db->group_by('a.item_id');
            $this->db->group_by('a.supplier_id');
            $this->db->order_by('a.item_id', 'asc');
            $records = $this->db->get()->result_array();

            $arr = array();
            foreach ($records as $generate) {
                $item_id = $generate['item_id'];

                $filter_month = $generate['p_month'];
                $filter_year = $generate['p_year'];
                $filter_revision = $generate['revision'];

                $mrp = $this->db->query("SELECT balance_1, balance_2, balance_3, balance_4, balance_5, balance_6 
                    FROM generate_mrp_finals 
                    WHERE p_month = '$filter_month' 
                    AND p_year = '$filter_year'
                    AND revision = '$filter_revision'
                    AND part_no = '$item_id'");
                $data = $mrp->row();
                
                if(@$data->balance_1 > 0){
                    $roundup_1 = 0;
                    $eta_1 = NULL;
                    $etd_1 = NULL;
                }else{
                    $eta_1 = date("Y-m-d", strtotime("+17 days", strtotime($filter_year."-".$filter_month."-01")));
                    $etd_1 = date("Y-m-d", strtotime("+3 days", strtotime($filter_year."-".$filter_month."-01")));
                    $roundup_1 = (ceil(abs(@$data->balance_1) / $generate['mpq']) * $generate['mpq']);
                    if($roundup_1 > $generate['qty']){
                        $roundup_1 = $generate['qty'];
                    }
                }

                if(@$data->balance_2 > 0 || $roundup_1 >= $generate['qty']){
                    $roundup_2 = 0;
                    $eta_2 = NULL;
                    $etd_2 = NULL;
                }else{
                    $eta_2 = date("Y-m-01",  strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));
                    $etd_2 = date("Y-m-d", strtotime("-14 days", strtotime($eta_2)));
                    $roundup_2 = (ceil(abs(@$data->balance_2) / $generate['mpq']) * $generate['mpq']);
                    if(($roundup_1 + $roundup_2) > $generate['qty']){
                        $roundup_2 = abs($roundup_1 - $generate['qty']);
                    }
                }

                if(@$data->balance_3 > 0 || ($roundup_1 + $roundup_2) >= $generate['qty']){
                    $roundup_3 = 0;
                    $eta_3 = NULL;
                    $etd_3 = NULL;
                }else{
                    $eta_3 = date("Y-m-01", strtotime("2 month", strtotime($filter_year."-".$filter_month."-01")));
                    $etd_3 = date("Y-m-d", strtotime("-14 day", strtotime($eta_3)));
                    $roundup_3 = (ceil(abs(@$data->balance_3) / $generate['mpq']) * $generate['mpq']);
                    if(($roundup_1 + $roundup_2 + $roundup_3) > $generate['qty']){
                        $roundup_3 = abs($roundup_1 + $roundup_2 - $generate['qty']);
                    }
                }

                if(@$data->balance_4 > 0 || ($roundup_1 + $roundup_2 + $roundup_3) >= $generate['qty']){
                    $roundup_4 = 0;
                    $eta_4 = NULL;
                    $etd_4 = NULL;
                }else{
                    $eta_4 = date("Y-m-01",  strtotime("3 month", strtotime($filter_year."-".$filter_month."-01")));
                    $etd_4 = date("Y-m-d", strtotime("-14 days", strtotime($eta_4)));
                    $roundup_4 = (ceil(abs(@$data->balance_4) / $generate['mpq']) * $generate['mpq']);
                    if(($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4) > $generate['qty']){
                        $roundup_4 = abs($roundup_1 + $roundup_2 + $roundup_3 - $generate['qty']);
                    }
                }

                if(@$data->balance_5 > 0 || ($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4) >= $generate['qty']){
                    $roundup_5 = 0;
                    $eta_5 = NULL;
                    $etd_5 = NULL;
                }else{
                    $eta_5 = date("Y-m-01",  strtotime("4 month", strtotime($filter_year."-".$filter_month."-01")));
                    $etd_5 = date("Y-m-d", strtotime("-14 days", strtotime($eta_5)));
                    $roundup_5 = (ceil(abs(@$data->balance_5) / $generate['mpq']) * $generate['mpq']);
                    if(($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4 + $roundup_5) > $generate['qty']){
                        $roundup_5 = abs($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4 - $generate['qty']);
                    }
                }

                if(@$data->balance_6 > 0 || ($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4 + $roundup_5) >= $generate['qty']){
                    $roundup_6 = 0;
                    $eta_6 = NULL;
                    $etd_6 = NULL;
                }else{
                    $eta_6 = date("Y-m-01",  strtotime("5 month", strtotime($filter_year."-".$filter_month."-01")));
                    $etd_6 = date("Y-m-d", strtotime("-14 days", strtotime($eta_6)));
                    $roundup_6 = (ceil(abs(@$data->balance_6) / $generate['mpq']) * $generate['mpq']);
                    if(($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4 + $roundup_5 + $roundup_6) > $generate['qty']){
                        $roundup_6 = abs($roundup_1 + $roundup_2 + $roundup_3 + $roundup_4 + $roundup_5 - $generate['qty']);
                    }
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "po_no" => $generate['purchase_order_no'],
                    "supplier_id" => $generate['supplier_id'],
                    "part_no" => $generate['item_id'],
                    "part_name" => $generate['item_name'],
                    "product_family" => $generate['product_family_name'],
                    "mpq" => $generate['mpq'],
                    "moq" => $generate['moq'],
                    "leadtime" => $generate['leadtime'],
                    "qty" => $generate['qty'],
                    "delivey_qty_1" => @$roundup_1,
                    "delivey_qty_2" => @$roundup_2,
                    "delivey_qty_3" => @$roundup_3,
                    "delivey_qty_4" => @$roundup_4,
                    "delivey_qty_5" => @$roundup_5,
                    "delivey_qty_6" => @$roundup_6,
                    "delivey_date_1" => @$eta_1,
                    "delivey_date_2" => @$eta_2,
                    "delivey_date_3" => @$eta_3,
                    "delivey_date_4" => @$eta_4,
                    "delivey_date_5" => @$eta_5,
                    "delivey_date_6" => @$eta_6,
                    "etd_1" => @$etd_1,
                    "etd_2" => @$etd_2,
                    "etd_3" => @$etd_3,
                    "etd_4" => @$etd_4,
                    "etd_5" => @$etd_5,
                    "etd_6" => @$etd_6,
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
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
            $filter_po_no = trim(base64_decode($this->input->get('filter_po_no')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('delivery_schedules a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            if($filter_revision != ""){
                $this->db->where('a.revision', $filter_revision);
            }
            if($filter_supplier != ""){
                $this->db->like('b.id', $filter_supplier);
            }
            if($filter_po_no != ""){
                $this->db->like('a.po_no', $filter_po_no);
            }
            if($filter_product_family != ""){
                $this->db->like('a.product_family', $filter_product_family);
            }
            if($filter_part_no != ""){
                $this->db->like('a.part_no', $filter_part_no);
            }
            $this->db->order_by('a.po_no', 'ASC');
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

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->api->reads('delivery_schedules', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "part_no" => $post['part_no'],
                "supplier_id" => $post['supplier_id'],
                "po_no" => $post['po_no']
            ]);

            if (count($records) > 0) {
                $send = $this->api->update('delivery_schedules', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "part_no" => $post['part_no'],
                    "supplier_id" => $post['supplier_id'],
                    "po_no" => $post['po_no']
                ], $post);

                echo $send;
            } else {
                $send = $this->api->create('delivery_schedules', $post, "DS", "JUTA");
                echo $send;
            }
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();

            $today = date("Y-m-d", strtotime("+3 days"));
            if(empty($post['delivey_date_1']) || $post['delivey_date_1'] > $today){
                //
            }else{
                die(json_encode(array("theme" => "error", "message" => "ETA must bigger on $today")));
            }

            if(empty($post['delivey_date_2']) || $post['delivey_date_2'] > $today){
                //
            }else{
                die(json_encode(array("theme" => "error", "message" => "ETA must bigger on $today")));
            }

            if(empty($post['delivey_date_3']) || $post['delivey_date_3'] > $today){
                //
            }else{
                die(json_encode(array("theme" => "error", "message" => "ETA must bigger on $today")));
            }

            if(empty($post['delivey_date_4']) || $post['delivey_date_4'] > $today){
                //
            }else{
                die(json_encode(array("theme" => "error", "message" => "ETA must bigger on $today")));
            }

            if(empty($post['delivey_date_5']) || $post['delivey_date_5'] > $today){
                //
            }else{
                die(json_encode(array("theme" => "error", "message" => "ETA must bigger on $today")));
            }

            if(empty($post['delivey_date_6']) || $post['delivey_date_6'] > $today){
                //
            }else{
                die(json_encode(array("theme" => "error", "message" => "ETA must bigger on $today")));
            }

            if(($post['qty'] - ($post['delivey_qty_1'] + $post['delivey_qty_2'] + $post['delivey_qty_3'] + $post['delivey_qty_4'] + $post['delivey_qty_5'] + $post['delivey_qty_6'])) == 0){
                $send = $this->api->update('delivery_schedules', ["id" => $id], $post);
                echo $send;
            }else{
                show_error("Period Qty must be the same as Qty Delivery");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleted()
    {
        $data = $this->input->post();
        $send = $this->api->delete('delivery_schedules', $data);
        echo $send;
    }

    public function print2($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_schedule_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_po_no = trim(base64_decode($this->input->get('filter_po_no')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));

        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('delivery_schedules a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $filter_part_no);
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->order_by('b.name');
        $this->db->order_by('a.part_no');
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
                            <small>DELIVERY SCHEDULE</small>
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
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="10" style="text-align:center;">No</th>
                <th rowspan="2" width="50" style="text-align:center;">PO NO</th>
                <th rowspan="2" width="100" style="text-align:center;">Supplier Name</th>
                <th rowspan="2" width="50" style="text-align:center;">Product Family</th>
                <th rowspan="2" width="80" style="text-align:center;">Product No</th>
                <th rowspan="2" width="50" style="text-align:center;">Product Name</th>
                <th rowspan="2" width="30" style="text-align:center;">LT</th>
                <th rowspan="2" width="50" style="text-align:center;">MPQ</th>
                <th rowspan="2" width="50" style="text-align:center;">MOQ</th>
                <th rowspan="2" width="50" style="text-align:center;">Qty</th>
                <th colspan="3" width="50" style="text-align:center;">Period</th>
            </tr>
            <tr>
                <th width="50" style="text-align:center;">Qty</th>
                <th width="50" style="text-align:center;">ETD</th>
                <th width="50" style="text-align:center;">ETA</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['delivey_date_1'] == "0000-00-00"){ $eta_1 = "-"; }else{ $eta_1 = $data['delivey_date_1']; }
            if($data['delivey_date_2'] == "0000-00-00"){ $eta_2 = "-"; }else{ $eta_2 = $data['delivey_date_2']; }
            if($data['delivey_date_3'] == "0000-00-00"){ $eta_3 = "-"; }else{ $eta_3 = $data['delivey_date_3']; }
            if($data['delivey_date_4'] == "0000-00-00"){ $eta_4 = "-"; }else{ $eta_4 = $data['delivey_date_4']; }
            if($data['delivey_date_5'] == "0000-00-00"){ $eta_5 = "-"; }else{ $eta_5 = $data['delivey_date_5']; }
            if($data['delivey_date_6'] == "0000-00-00"){ $eta_6 = "-"; }else{ $eta_6 = $data['delivey_date_6']; }
            if($data['etd_1'] == "0000-00-00"){ $etd_1 = "-"; }else{ $etd_1 = $data['etd_1']; }
            if($data['etd_2'] == "0000-00-00"){ $etd_2 = "-"; }else{ $etd_2 = $data['etd_2']; }
            if($data['etd_3'] == "0000-00-00"){ $etd_3 = "-"; }else{ $etd_3 = $data['etd_3']; }
            if($data['etd_4'] == "0000-00-00"){ $etd_4 = "-"; }else{ $etd_4 = $data['etd_4']; }
            if($data['etd_5'] == "0000-00-00"){ $etd_5 = "-"; }else{ $etd_5 = $data['etd_5']; }
            if($data['etd_6'] == "0000-00-00"){ $etd_6 = "-"; }else{ $etd_6 = $data['etd_6']; }

            if($data['delivey_qty_1'] > 0){
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td>' . $data['product_family'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                                <td style="text-align:right;">' . $data['leadtime'] . '</td>
                                <td style="text-align:right;">' . $data['mpq'] . '</td>
                                <td style="text-align:right;">' . $data['moq'] . '</td>
                                <td style="text-align:right;">' . $data['qty'] . '</td>
                                <td style="text-align:right;">' . number_format($data['delivey_qty_1']) . '</td>
                                <td style="text-align:center;">'.$etd_1.'</td>
                                <td style="text-align:center;">'.$eta_1.'</td>
                            </tr>';  
            }

            if($data['delivey_qty_2'] > 0){
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td>' . $data['product_family'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                                <td style="text-align:right;">' . $data['leadtime'] . '</td>
                                <td style="text-align:right;">' . $data['mpq'] . '</td>
                                <td style="text-align:right;">' . $data['moq'] . '</td>
                                <td style="text-align:right;">' . $data['qty'] . '</td>
                                <td style="text-align:right;">' . number_format($data['delivey_qty_2']) . '</td>
                                <td style="text-align:center;">'.$etd_2.'</td>
                                <td style="text-align:center;">'.$eta_2.'</td>
                            </tr>'; 
            }

            if($data['delivey_qty_3'] > 0){
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td>' . $data['product_family'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                                <td style="text-align:right;">' . $data['leadtime'] . '</td>
                                <td style="text-align:right;">' . $data['mpq'] . '</td>
                                <td style="text-align:right;">' . $data['moq'] . '</td>
                                <td style="text-align:right;">' . $data['qty'] . '</td>
                                <td style="text-align:right;">' . number_format($data['delivey_qty_3']) . '</td>
                                <td style="text-align:center;">'.$etd_3.'</td>
                                <td style="text-align:center;">'.$eta_3.'</td>
                            </tr>';  
            }

            if($data['delivey_qty_4'] > 0){
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td>' . $data['product_family'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                                <td style="text-align:right;">' . $data['leadtime'] . '</td>
                                <td style="text-align:right;">' . $data['mpq'] . '</td>
                                <td style="text-align:right;">' . $data['moq'] . '</td>
                                <td style="text-align:right;">' . $data['qty'] . '</td>
                                <td style="text-align:right;">' . number_format($data['delivey_qty_4']) . '</td>
                                <td style="text-align:center;">'.$etd_4.'</td>
                                <td style="text-align:center;">'.$eta_4.'</td>
                            </tr>';  
            }

            if($data['delivey_qty_5'] > 0){
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td>' . $data['product_family'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                                <td style="text-align:right;">' . $data['leadtime'] . '</td>
                                <td style="text-align:right;">' . $data['mpq'] . '</td>
                                <td style="text-align:right;">' . $data['moq'] . '</td>
                                <td style="text-align:right;">' . $data['qty'] . '</td>
                                <td style="text-align:right;">' . number_format($data['delivey_qty_5']) . '</td>
                                <td style="text-align:center;">'.$etd_5.'</td>
                                <td style="text-align:center;">'.$eta_5.'</td>
                            </tr>'; 
            }

            if($data['delivey_qty_6'] > 0){
                $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="text-align:right;">' . $data['leadtime'] . '</td>
                            <td style="text-align:right;">' . $data['mpq'] . '</td>
                            <td style="text-align:right;">' . $data['moq'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_6']) . '</td>
                            <td style="text-align:center;">'.$etd_6.'</td>
                            <td style="text-align:center;">'.$eta_6.'</td>
                        </tr>'; 
            }

            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_schedule_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_po_no = trim(base64_decode($this->input->get('filter_po_no')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));

        $period_1 = date("F Y", strtotime($filter_year."-".$filter_month."-01"));
        $period_2 = date("F Y",  strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_3 = date("F Y",  strtotime("2 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_4 = date("F Y",  strtotime("3 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_5 = date("F Y",  strtotime("4 month", strtotime($filter_year."-".$filter_month."-01")));
        $period_6 = date("F Y",  strtotime("5 month", strtotime($filter_year."-".$filter_month."-01")));

        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('delivery_schedules a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $filter_part_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->order_by('b.name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
        $this->db->order_by('a.part_no', 'asc');
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
                            <small>DELIVERY SCHEDULE</small>
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
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="10" style="text-align:center;">No</th>
                <th rowspan="2" width="50" style="text-align:center;">PO NO</th>
                <th rowspan="2" width="100" style="text-align:center;">Supplier Name</th>
                <th rowspan="2" width="50" style="text-align:center;">Product Family</th>
                <th rowspan="2" width="80" style="text-align:center;">Product No</th>
                <th rowspan="2" width="50" style="text-align:center;">Product Name</th>
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
        foreach ($records as $data) {
            if($data['delivey_date_1'] == "0000-00-00"){ $eta_1 = "-"; }else{ $eta_1 = $data['delivey_date_1']; }
            if($data['delivey_date_2'] == "0000-00-00"){ $eta_2 = "-"; }else{ $eta_2 = $data['delivey_date_2']; }
            if($data['delivey_date_3'] == "0000-00-00"){ $eta_3 = "-"; }else{ $eta_3 = $data['delivey_date_3']; }
            if($data['delivey_date_4'] == "0000-00-00"){ $eta_4 = "-"; }else{ $eta_4 = $data['delivey_date_4']; }
            if($data['delivey_date_5'] == "0000-00-00"){ $eta_5 = "-"; }else{ $eta_5 = $data['delivey_date_5']; }
            if($data['delivey_date_6'] == "0000-00-00"){ $eta_6 = "-"; }else{ $eta_6 = $data['delivey_date_6']; }
            if($data['etd_1'] == "0000-00-00"){ $etd_1 = "-"; }else{ $etd_1 = $data['etd_1']; }
            if($data['etd_2'] == "0000-00-00"){ $etd_2 = "-"; }else{ $etd_2 = $data['etd_2']; }
            if($data['etd_3'] == "0000-00-00"){ $etd_3 = "-"; }else{ $etd_3 = $data['etd_3']; }
            if($data['etd_4'] == "0000-00-00"){ $etd_4 = "-"; }else{ $etd_4 = $data['etd_4']; }
            if($data['etd_5'] == "0000-00-00"){ $etd_5 = "-"; }else{ $etd_5 = $data['etd_5']; }
            if($data['etd_6'] == "0000-00-00"){ $etd_6 = "-"; }else{ $etd_6 = $data['etd_6']; }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['po_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="text-align:right;">' . $data['leadtime'] . '</td>
                            <td style="text-align:right;">' . $data['mpq'] . '</td>
                            <td style="text-align:right;">' . $data['moq'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_1']) . '</td>
                            <td style="text-align:center;">'.$etd_1.'</td>
                            <td style="text-align:center;">'.$eta_1.'</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_2']) . '</td>
                            <td style="text-align:center;">'.$etd_2.'</td>
                            <td style="text-align:center;">'.$eta_2.'</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_3']) . '</td>
                            <td style="text-align:center;">'.$etd_3.'</td>
                            <td style="text-align:center;">'.$eta_3.'</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_4']) . '</td>
                            <td style="text-align:center;">'.$etd_4.'</td>
                            <td style="text-align:center;">'.$eta_4.'</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_5']) . '</td>
                            <td style="text-align:center;">'.$etd_5.'</td>
                            <td style="text-align:center;">'.$eta_5.'</td>
                            <td style="text-align:right;">' . number_format($data['delivey_qty_6']) . '</td>
                            <td style="text-align:center;">'.$etd_6.'</td>
                            <td style="text-align:center;">'.$eta_6.'</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
?>
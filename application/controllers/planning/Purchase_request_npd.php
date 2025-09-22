<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_request_npd extends CI_Controller
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
            $this->load->view('planning/purchase_request_npd');
        } else {
            redirect('error_access');
        }
    }

    public function readSupplier(){
        $query = $this->db->query("SELECT id as supplier_id, `number` as supplier_no, `name` as supplier_name FROM suppliers ORDER BY `name` ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readPr($supplier_id, $prodfam){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier_id = base64_decode($supplier_id);
        $prodfam = base64_decode($prodfam);
        
        $query = $this->db->query("SELECT a.purchase_request_no, b.name as supplier_name
            FROM purchase_requests a 
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.supplier_id = '$supplier_id' and a.product_family = '$prodfam' and a.purchase_request_no like '%$post%'
            GROUP BY a.purchase_request_no
            ORDER BY a.purchase_request_no ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readMonths()
    {
        $month = $this->input->get('month');
        $year = $this->input->get('year');

        $start = strtotime($year . "-" . $month . "-01");
        $begin =  strtotime(date('Y-m-d', strtotime('+4 month', $start)));
        $end =  strtotime(date('Y-m-d', strtotime('-3 month', $start)));

        $monthName = array();
        while ($end < $begin) {
            $monthName[] = date('M Y', $end);
            $end = strtotime("+1 month", $end);
        }

        die(json_encode($monthName));
    }

    public function purchaseRequestNo($code, $add = ""){

        if($add != ""){
            $code = base64_decode($code);
        }

        $sqlGetID	= $this->db->query("SELECT max(purchase_request_no) as kode FROM purchase_requests WHERE purchase_request_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;
        
        if ($kode == NULL) {
            $autoID = sprintf("%02s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -2);
            $urutan++;
            $autoID	= sprintf("%02s", $urutan);
        }

        if($add == ""){
            return $code.$autoID;
        }else{
            echo $code.$autoID;
        }
    }

    public function getDataMrp()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));

            //Select Query
            $this->db->select('c.mpq, c.moq, c.leadtime, a.request_no, a.request_date, a.request_reason, a.requirement_date, a.qty, b.item_id, b.item_name, b.pfm_id, d.name as prodfam, c.share_order, c.supplier_id, e.number as supplier_no');
            $this->db->from('request_npd a');
            $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
            $this->db->join('supplier_items c', 'a.item_id = c.item_id and a.supplier_id = c.supplier_id and c.share_order > 0');
            $this->db->join('mst_item_family d', 'b.pfm_id = d.number');
            $this->db->join('suppliers e', 'c.supplier_id = e.id');
            $this->db->where('a.status', 0);
            $this->db->where('a.confirm', "CONFIRMED");
            $this->db->where("a.request_date BETWEEN '$filter_from' and '$filter_to'");
            if($filter_product_family != ""){
                $this->db->where('b.pfm_name', $filter_product_family);
            }
            if($filter_supplier != ""){
                $this->db->where('a.supplier_id', $filter_supplier);
            }
            if($filter_product_no != ""){
                $this->db->where('b.item_id', $filter_product_no);
            }
            $this->db->group_by('c.supplier_id');
            $this->db->group_by('a.item_id');
            $this->db->order_by('c.supplier_id');
            $this->db->order_by('a.item_id');
            $mpsDetails = $this->db->get()->result_array();

            $arr = array();
            $end_supplier = "";
            $end_product_family = "";
            $end_purchase_request_no = "";
            foreach ($mpsDetails as $generate) {
                $supplier_no = $generate['supplier_no'];
                $item_id = $generate['item_id'];
                $request_date = $generate['request_date'];

                if($supplier_no == $end_supplier && $generate['pfm_id'] == $end_product_family){
                    $purchase_request_no = $end_purchase_request_no;
                    $no++;
                }else{
                    $purchase_request_no = $this->purchaseRequestNo("PRNPD-IND".@trim($supplier_no.date("Ym")). "-" .trim($generate['pfm_id']));
                    $no = 1;
                }

                $arr[] = array(
                    "p_month" => date("m", strtotime($request_date)),
                    "p_year" => date("Y", strtotime($request_date)),
                    "request_npd" => $generate['request_no'],
                    "purchase_request_no" => $purchase_request_no,
                    "purchase_request_date" => date("Y-m-d"),
                    "requirement_date" => $generate['requirement_date'],
                    "supplier_id" => $generate['supplier_id'],
                    "part_no" => $generate['item_id'],
                    "part_name" => $generate['item_name'],
                    "product_family" => $generate['prodfam'],
                    "mpq" => $generate['mpq'],
                    "moq" => $generate['moq'],
                    "leadtime" => $generate['leadtime'],
                    "share_order" => $generate['share_order'],
                    "total_need" => $generate['qty'],
                    "purchase_order" => $generate['qty'],
                    "type" => "NPD",
                    "purchase_request" => $generate['qty'],
                    "reason" => $generate['request_reason']
                );

                $end_supplier = $supplier_no;
                $end_product_family = $generate['pfm_id'];
                $end_purchase_request_no = $purchase_request_no;
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
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name, SUM(a.purchase_request) as total_pr');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.type', "NPD");
            $this->db->where("a.purchase_request_date between '$filter_from' and '$filter_to'");
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_part_no);
            $this->db->group_by('a.purchase_request_no');
            $this->db->order_by('a.purchase_request_no', 'ASC');
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

    public function datatableDetails($purchase_request_no)
    {
        $purchase_request_no = base64_decode($purchase_request_no);
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_from = trim(base64_decode($this->input->get('filter_from')));
        $filter_to = trim(base64_decode($this->input->get('filter_to')));

        // $ending = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        // $po_date_1 = date('Y-m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        // $po_date_2 = date('Y-m', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        // $po_date_3 = date('Y-m', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_requests a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.purchase_request_no', $purchase_request_no);
        $this->db->like('a.part_no', $filter_part_no);
        $this->db->order_by('a.part_no', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $productTotals = [];
        foreach ($records as $record) {
            $product = $record['part_no'];
            $shareOrder = $record['share_order'];
            $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
        }

        $finals = array();
        $total_purchase_request = 0;
        $total_purchase_order = 0;
        $total_need = 0;
        foreach ($records as $data) {

            $class = ($productTotals[$data['part_no']] >= 100) ? 'red' : '';
            $finals[] = array_merge($data, [
                "color" => $class,
            ]);

            $total_purchase_request += $data['purchase_request'];
            $total_purchase_order += $data['purchase_order'];
            $total_need += $data['total_need'];
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
        'rows' => $finals, 
        'footer' => array([
                "part_no" => "Grand Total",
                "purchase_request" => $total_purchase_request,
                "purchase_order" => $total_purchase_order,
                "total_need" => $total_need
            ])
        ]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->api->reads('purchase_requests', [], [
                "part_no" => $post['part_no'],
                "type" => $post['type'],
                "request_npd" => $post['request_npd'],
                "supplier_id" => $post['supplier_id']
            ]);

            if (count($records) > 0) {
                $send = $this->api->update('purchase_requests', [
                    "part_no" => $post['part_no'],
                    "type" => $post['type'],
                    "request_npd" => $post['request_npd'],
                    "supplier_id" => $post['supplier_id']
                ], $post);

                echo $send;
            } else {
                $send = $this->api->create('purchase_requests', $post, "PR", "JUTA");
                echo $send;
            }

            $send = $this->db->update('request_npd', ["status" => 1], ["item_id" => $post['part_no'],"request_no" => $post['request_npd']]);
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $send = $this->api->update('purchase_requests', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('purchase_requests', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=purchase_requests_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));

            // $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            // $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            // $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            // $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            // $month_min_3 =  date('M Y', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            // $month_min_2 =  date('M Y', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            // $month_min_1 =  date('M Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.type', "NPD");
            $this->db->where("a.purchase_request_date between '$filter_from' and '$filter_to'");
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_product_no);
            $this->db->order_by('a.part_no', 'ASC');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
            .box-green{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #D6FFCF;
            }

            .box-red{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #FFCFCF;
            }
            </style>
            <body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>PURCHASE REQUEST NPD</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                    <br><br><br>
                </center>

                <table id="customers" border="1">
                    <tr>
                        <th style="text-align:center;" width="20">No</th>
                        <th style="text-align:center;">PERIOD</th>
                        <th style="text-align:center;">REVISION</th>
                        <th style="text-align:center;">VENDOR</th>
                        <th style="text-align:center;">ITEM NO</th>
                        <th style="text-align:center;">ITEM NAME</th>
                        <th style="text-align:center;">PRODFAM</th>
                        <th style="text-align:center;">SHARE<br>ORDER</th>
                        <th style="text-align:center;">MPQ</th>
                        <th style="text-align:center;">MOQ</th>
                        <th style="text-align:center;">LEADTIME</th>
                        <th style="text-align:center;">TOTAL NEED</th>
                        <th style="text-align:center;">MRP RESULT</th>
                        <th style="text-align:center;">PURCHASE REQUEST</th>
                        <th style="text-align:center;">REASON</th>
                    </tr>';

                    $no = 1;

                    $productTotals = [];
                    foreach ($records as $record) {
                        $product = $record['part_no'];
                        $shareOrder = $record['share_order'];
                        $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
                    }
                    foreach ($records as $record) {
                        $class = ($productTotals[$record['part_no']] >= 100) ? 'red' : '';
                        if($class == ""){
                            $style = "style='background:#FFD2D2;'";
                        }else{
                            $style = "";
                        }

                        $html .= "  <tr ".$style.">
                                        <td>".$no."</td>
                                        <td>".$record['p_year'] . $record['p_month']."</td>
                                        <td>".$record['revision']."</td>
                                        <td>".$record['supplier_name']."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['part_no'])."</td>
                                        <td>".$record['part_name']."</td>
                                        <td>".$record['product_family']."</td>
                                        <td>".$record['share_order']."</td>
                                        <td>".$record['mpq']."</td>
                                        <td>".$record['moq']."</td>
                                        <td>".$record['leadtime']."</td>
                                        <td style='text-align:right;'>".round($record['total_need'])."</td>
                                        <td style='text-align:right;'>".round($record['purchase_order'])."</td>
                                        <td style='text-align:right;'>".round($record['purchase_request'])."</td>
                                        <td style='text-align:right;'>".round($record['reason'])."</td>
                                    </tr>";
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
?>
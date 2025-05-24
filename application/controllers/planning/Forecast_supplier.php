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

    function readPoSearch(){
        $supplier_id = base64_decode($this->input->get('supplier_id'));

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $datas = $this->api->query("SELECT DISTINCT purchase_order_no FROM purchase_orders WHERE supplier_id = '$supplier_id'");
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

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $item_id = trim(base64_decode($this->input->get('item_id')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            // echo 'Decoded filter_part_no: ' . $filter_part_no . "<br>";
            // echo "Filter Month: " . $filter_month . "<br>";
// echo "Filter Year: " . $filter_year . "<br>";
// echo "Filter Revision: " . $filter_revision . "<br>";
// echo "Filter Product Family: " . $filter_product_family . "<br>";
// echo "Filter Part No: " . $filter_part_no . "<br>";
// echo "Filter Supplier: " . $filter_supplier . "<br>";
// echo "Filter PO: " . $filter_po . "<br>";

            //Select Query
            $this->db->select('a.revision, a.p_year, a.p_month, a.product_no as item_id, 
            b.number as vendor_id, b.name as vendor_name, 
            b.type as vendor_type, b.origin as vendor_origin, 
            c.item_id as supplier_item_id, a.product_name as product_name, 
            c.leadtime as supplier_item_leadtime, c.mpq as supplier_item_mpq, 
            c.moq as supplier_item_moq, d.name as maker_name, d.type as maker_type');
    $this->db->from('generate_mrp a');
    $this->db->join('supplier_items c', 'a.product_no = c.item_id', 'left');
    $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
    $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
        $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
    $this->db->where('a.revision', '');
    $this->db->where('c.share_order >', 0);
    $this->db->like('a.product_family', $filter_product_family); // For LIKE '%Terminal%'
    $this->db->like('a.product_no', $item_id); // For LIKE '%+2236793-3%'
    $this->db->like('c.supplier_id', $filter_supplier); // This condition may be redundant as '%%' is equivalent to no filtering.
    $this->db->order_by('b.name', 'asc');
    $this->db->order_by('a.product_family', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            
            // echo $this->db->get_compiled_select();
            $records = $this->db->get()->result_array();
            
            // die();
            $finals = array();
            foreach ($records as $record) {
                $ending = date("Y-m-t", strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
                $item_id = $record['item_id'];
                $leadtime = date('Y-m-01', strtotime('+'.$record['supplier_item_leadtime'].' days', strtotime($ending)));
                $revision = $record['revision'];
                $p_month = $record['p_month'];
                $p_year = $record['p_year'];

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

                $finals[] = array_merge($record, [
                    "forecast_xbar" => round((@$prod1 + @$prod2 + @$prod3) / 3),
                    "forecast_1" => round($prod1),
                    "forecast_2" => round($prod2),
                    "forecast_3" => round($prod3)
                ]);
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $finals]);
            echo json_encode($result);
        }
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

        //Select Query
       
        $this->db->select('a.revision, a.p_year, a.p_month, a.product_no as item_id, 
        b.number as vendor_id, b.name as vendor_name, 
        b.type as vendor_type, b.origin as vendor_origin, 
        c.item_id as supplier_item_id, a.product_name as product_name, 
        c.leadtime as supplier_item_leadtime, c.mpq as supplier_item_mpq, 
        c.moq as supplier_item_moq, d.name as maker_name, d.type as maker_type');
$this->db->from('generate_mrp a');
$this->db->join('supplier_items c', 'a.product_no = c.item_id', 'left');
$this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
$this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
    $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
$this->db->where('a.revision', '');
$this->db->where('c.share_order >', 0);
$this->db->like('a.product_family', $filter_product_family); // For LIKE '%Terminal%'
$this->db->like('a.product_no', $item_id); // For LIKE '%+2236793-3%'
$this->db->like('c.supplier_id', $filter_supplier); // This condition may be redundant as '%%' is equivalent to no filtering.
$this->db->order_by('b.name', 'asc');
$this->db->order_by('a.product_family', 'asc');

        // $this->db->select('a.revision, a.p_year, a.p_month, a.item_id, b.number as vendor_id, b.name as vendor_name, b.type as vendor_type, b.origin as vendor_origin, c.item_id as supplier_item_id, a.item_name as item_name, c.leadtime as supplier_item_leadtime, c.mpq as supplier_item_mpq, c.moq as supplier_item_moq, d.name as maker_name, d.type as maker_type');
        // $this->db->from('purchase_orders a');
        // $this->db->join('supplier_items c', "a.item_id = c.item_id AND a.supplier_id = c.supplier_id", 'left');
        // $this->db->join('suppliers b', "c.supplier_id = b.id", 'left');
        // $this->db->join('mst_maker d', "c.maker_code = d.number", 'left');
        // $this->db->where('a.p_month', $filter_month);
        // $this->db->where('a.p_year', $filter_year);
        // $this->db->where('a.revision', $filter_revision);
        // $this->db->where('c.share_order >',0);
        // $this->db->like('a.product_family_name', $filter_product_family);
        // $this->db->like('a.item_id', $item_id);
        // $this->db->like('a.supplier_id', $filter_supplier);
        // $this->db->like('a.purchase_order_no', $filter_po);
        // $this->db->order_by('b.name', 'asc');
        // $this->db->order_by('a.product_family_name', 'asc');

//         $query = $this->db->get_compiled_select();
// echo $query;

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
                <th style="text-align:center;">Vendor ID</th>
                <th style="text-align:center;">Vendor Name AJAJ</th>
                <th style="text-align:center;">Vendor Type</th>
                <th style="text-align:center;">Origin</th>
                <th style="text-align:center;">Item ID</th>
                <th style="text-align:center;">Item Name</th>
                <th style="text-align:center;">LeadTime</th>
                <th style="text-align:center;">MPQ</th>
                <th style="text-align:center;">MOQ</th>
                <th style="text-align:center;">Maker Name</th>
                <th style="text-align:center;">Maker Type</th>
                <th style="text-align:center;">Xbar -3 Forecast</th>
                <th style="text-align:center;">Forecast 1</th>
                <th style="text-align:center;">Forecast 2</th>
                <th style="text-align:center;">Forecast 3</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $ending = date("Y-m-t", strtotime($data['p_year'] . "-" . $data['p_month'] . "-01"));
            $item_id = $data['item_id'];
            $leadtime = date('Y-m-01', strtotime('+'.$data['supplier_item_leadtime'].' days', strtotime($ending)));
            $revision = $data['revision'];
            $p_month = $data['p_month'];
            $p_year = $data['p_year'];

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

            $forecast = $this->forecast($item_id, $data['p_month'], $data['p_year'], @$revision);

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

            $html .= ' <tr>
                            <td>TESS</td>
                            <td>tesssss</td>
                            <td>tessss</td>
                            <td>tessss</td>
                            <td>tessss</td>
                            <td>tessss</td>
                            <td>tessss</td>
                            <td>tessss</td>
                            <td style="mso-number-format:\@;">' . $data['supplier_item_mpq'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['supplier_item_moq'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td style="text-align:right;">' . round($prod1 + $prod2 + $prod3) . '</td>
                            <td style="text-align:right;">' . round($prod1) . '</td>
                            <td style="text-align:right;">' . round($prod2) . '</td>
                            <td style="text-align:right;">' . round($prod3) . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

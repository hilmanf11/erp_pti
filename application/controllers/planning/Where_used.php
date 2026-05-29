<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Where_used extends CI_Controller
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

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/where_used');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=where_used_$format.xls");
        }

        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        $datas = array();
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_id' => $data->val($i, 2),
            );
        }

        unlink($_FILES['file_upload']['name']);
        $item_ids = array_column($datas, 'item_id');
        $item_ids_escaped = array_map(function($id) {
            return "'".addslashes($id)."'";
        }, $item_ids);
        $item_ids_str = implode(',', $item_ids_escaped);


        if(count($datas) > 0){
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');

            $query = $this->db->query("SELECT a.component_id, d.item_name as component_name, a.item_id, b.item_name, a.qty, c.name as customer_name
                FROM mst_bom a
                LEFT JOIN mst_item b ON a.item_id = b.item_id
                LEFT JOIN mst_customer c ON b.id_customer = c.number
                LEFT JOIN mst_item_raw d ON a.component_id = d.item_id
                WHERE b.pfm_id = '06' and a.component_id IN ($item_ids_str) ORDER BY a.component_id ASC");
            $records = $query->result_array();
        }else{
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $where_item = "";
            if($filter_item_id != ""){
                $where_item = "and a.component_id = '$filter_item_id'";
            }

            $query = $this->db->query("SELECT a.component_id, d.item_name as component_name, a.item_id, b.item_name, a.qty, c.name as customer_name
                FROM mst_bom a
                LEFT JOIN mst_item b ON a.item_id = b.item_id
                LEFT JOIN mst_customer c ON b.id_customer = c.number
                LEFT JOIN mst_item_raw d ON a.component_id = d.item_id
                WHERE b.pfm_id = '06' $where_item ORDER BY a.component_id ASC");
            $records = $query->result_array();
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
                <style> .str{ mso-number-format:\@; } </style>
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
                                    <small>WHERE USED</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                </center>
                <br><br>
                <center>
                    <b style="font-size:16px;">WHERE USED</b><br><br>
                </center>
                <table id="customers" border="1">
                    <tr>
                        <th width="30">No</th>
                        <th style="text-align:center;">Part No</th>
                        <th style="text-align:center;">Part Name</th>
                        <th style="text-align:center;">Product No</th>
                        <th style="text-align:center;">Product Name</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:center;">Customer</th>
                        <th style="text-align:center;">Period</th>
                        <th style="text-align:center;">Prodplan 1</th>
                        <th style="text-align:center;">Prodplan 2</th>
                        <th style="text-align:center;">Prodplan 3</th>
                        <th style="text-align:center;">Prodplan 4</th>
                        <th style="text-align:center;">Prodplan 5</th>
                        <th style="text-align:center;">Prodplan 6</th>
                    </tr>';
        $no = 1;
        foreach ($records as $record) {
            $product_no = $record['item_id'];
            $query2 = $this->db->query("SELECT prod_plan FROM generate_mps_detail WHERE product_no='$product_no' and p_month='$filter_month' and p_year='$filter_year' and revision = '$filter_revision'");
            $mps = $query2->result_array();

            if(@$mps[0]['prod_plan'] > 0){ $prod_plan_1 = @$mps[0]['prod_plan']; }else{ $prod_plan_1 = 0; }
            if(@$mps[1]['prod_plan'] > 0){ $prod_plan_2 = @$mps[1]['prod_plan']; }else{ $prod_plan_2 = 0; }
            if(@$mps[2]['prod_plan'] > 0){ $prod_plan_3 = @$mps[2]['prod_plan']; }else{ $prod_plan_3 = 0; }
            if(@$mps[3]['prod_plan'] > 0){ $prod_plan_4 = @$mps[3]['prod_plan']; }else{ $prod_plan_4 = 0; }
            if(@$mps[4]['prod_plan'] > 0){ $prod_plan_5 = @$mps[4]['prod_plan']; }else{ $prod_plan_5 = 0; }
            if(@$mps[5]['prod_plan'] > 0){ $prod_plan_6 = @$mps[5]['prod_plan']; }else{ $prod_plan_6 = 0; }

            $html .= '<tr>
                        <td style="text-align:center;">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $record['component_id'] . '</td>
                        <td>' . $record['component_name'] . '</td>
                        <td style="mso-number-format:\@;">' . $record['item_id'] . '</td>
                        <td>' . $record['item_name'] . '</td>
                        <td>' . round($record['qty'], 4) . '</td>
                        <td>' . $record['customer_name'] . '</td>
                        <td>' . $filter_year.$filter_month . '</td>
                        <td>' . $prod_plan_1. '</td>
                        <td>' . $prod_plan_2. '</td>
                        <td>' . $prod_plan_3. '</td>
                        <td>' . $prod_plan_4. '</td>
                        <td>' . $prod_plan_5. '</td>
                        <td>' . $prod_plan_6. '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table>';

        $html .= '</body></html>';
        echo $html;
    }
}

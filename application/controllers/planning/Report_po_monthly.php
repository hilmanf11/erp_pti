<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_po_monthly extends CI_Controller
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
            $this->load->view('planning/report_po_monthly');
        } else {
            redirect('error_access');
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_po_monthly_$format.xls");
        }
        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_month2 = base64_decode($this->input->get('filter_month2'));
            $filter_year2 = base64_decode($this->input->get('filter_year2'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $period_start = date("Y-m-d", strtotime($filter_year."-".$filter_month."-01"));
            $period_finish = date("Y-m-d", strtotime($filter_year2."-".$filter_month2."-01"));
            $header = "";
            $period = [];
            $totalH = 0;
            while (strtotime($period_start) <= strtotime($period_finish)) {
                $monthName = date('MY', strtotime($period_start));
                $header .= '<th style="text-align:center;">'.$monthName.'</th>';
                $period[] = date("Ym", strtotime($period_start));
                $totalH++;
                $period_start = date("Y-m-d", strtotime("+1 month", strtotime($period_start)));
            }
            $this->db->select('a.item_id, 
                a.item_vendor, 
                a.item_name, 
                c.name AS item_family_name, 
                a.supplier_id, 
                b.name AS supplier_name, 
                e.name as maker_name,
                b.type, 
                a.uom');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('mst_item_family c', 'a.product_family = c.number');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_id = d.item_id', 'left');
            $this->db->join('mst_maker e', 'e.number = d.maker_code', 'left');
            if($filter_supplier != ""){
                $this->db->where('a.supplier_id', $filter_supplier);
            }
            if($filter_product_family != ""){
                $this->db->where('a.product_family', $filter_product_family);
            }
            if($filter_part_no != ""){
                $this->db->where('a.item_id', $filter_part_no);
            }
            $this->db->where_in("CONCAT(a.p_year, a.p_month)", $period);
            $this->db->group_by('a.item_id, a.supplier_id');
            $this->db->order_by('c.name', 'asc');
            $this->db->order_by('b.name', 'asc');
            $this->db->order_by('a.item_id', 'asc');
            $records = $this->db->get()->result_array();
            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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
                                        <small>REPORT MONTHLY PURCHASE ORDER</small><br><br>
                                        Print Date ' . date("d M Y H:m:s") . ' <br>
                                        Print By ' . $this->session->username . '<br><br>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </center>
                    <br><br><br>
                    <table id="customers" border="1">
                        <tr>
                            <th width="30">No</th>
                            <th style="text-align:center;">Vendor Name</th>
                            <th style="text-align:center;">Part No</th>
                            <th style="text-align:center;">Part No Vendor</th>
                            <th style="text-align:center;">Part Name</th>
                            <th style="text-align:center;">Product Family</th>
                            <th style="text-align:center;">Maker Name</th>
                            <th style="text-align:center;">Vendor Type</th>
                            <th style="text-align:center;">Uom</th>
                            '.$header.'
                            <th style="text-align:center;">Grand Total</th>
                            <th style="text-align:center;">AVG</th>
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                $supplier_id = $record['supplier_id'];
                $item_id = $record['item_id'];
                $period_start2 = date("Y-m-d", strtotime($filter_year."-".$filter_month."-01"));
                $period_finish2 = date("Y-m-d", strtotime($filter_year2."-".$filter_month2."-01"));
                $content = "";
                $grand_total = 0;
                while (strtotime($period_start2) <= strtotime($period_finish2)) {
                    $periodDate = date('Ym', strtotime($period_start2));
                    $query = $this->db->query("SELECT SUM(qty) as total FROM purchase_orders WHERE CONCAT(p_year, p_month) = '$periodDate' and supplier_id = '$supplier_id' and item_id = '$item_id'");
                    $row = $query->row();
                    $total = empty(@$row->total) ? 0 : @$row->total;
                    $content .= '<td style="text-align:right;">'.$total.'</td>';
                    $grand_total += $total;
                    $period_start2 = date("Y-m-d", strtotime("+1 month", strtotime($period_start2)));
                }
                $html .= '<tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td>' . $record['supplier_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_vendor'] . '</td>
                            <td style="mso-number-format:\@;">' . $record['item_name'] . '</td>
                            <td>' . $record['item_family_name'] . '</td>
                            <td>' . $record['maker_name'] . '</td>
                            <td>' . $record['type'] . '</td>
                            <td>' . $record['uom'] . '</td>
                            '.$content.'
                            <td style="text-align:right;">' . $grand_total . '</td>
                            <td style="text-align:right;">' . round($grand_total / $totalH) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table>';
        }
        $html .= '</body></html>';
        echo $html;
    }
}

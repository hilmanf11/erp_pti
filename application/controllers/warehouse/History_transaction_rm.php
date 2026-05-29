<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class History_transaction_rm extends CI_Controller
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
            $this->load->view('warehouse/history_transaction_rm');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transaction_rm_$format.xls");
        }

        $this->dummy = $this->load->database('dummy', TRUE);

        if ($this->input->get()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $filter_display = $this->input->get('filter_display');

            $where_part = "";
            if($filter_part_no != ""){
                $where_part = "and a.item_id = '$filter_part_no'";
            }

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
                                        <small>REPORT HISTORY TRANSACTION RM</small><br><br>
                                        Print Date ' . date("d M Y H:m:s") . ' <br>
                                        Print By ' . $this->session->username . '<br><br>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </center>
                    <br>';

            if ($filter_display == "false") {
                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th width="30">No</th>
                                    <th width="200" style="text-align:center;">Part No</th>
                                    <th width="150" style="text-align:center;">Part Name</th>
                                    <th width="150" style="text-align:center;">Product Family</th>
                                    <th width="50" style="text-align:center;">Uom</th>
                                    <th width="80" style="text-align:center;">Begin</th>
                                    <th width="80" style="text-align:center;">In</th>
                                    <th width="80" style="text-align:center;">Out</th>
                                    <th width="80" style="text-align:center;">Ending</th>
                                </tr>';

                $query = $this->dummy->query("SELECT a.item_id, a.item_name, e.pfm_name, f.um_name, COALESCE(b.qty_begin, 0) as qty_begin, COALESCE(c.qty_in, 0) as qty_in, COALESCE(abs(d.qty_out), 0) as qty_out,
                    (COALESCE(b.qty_begin, 0) + COALESCE(c.qty_in, 0) - COALESCE(abs(d.qty_out), 0)) as qty_balance
                    FROM mst_item a
                    LEFT JOIN (SELECT ith_item_id, SUM(ith_qty) as qty_begin FROM ith WHERE ith_date < '$filter_from' and (whs_id is null or whs_id = 'WHS001') GROUP BY ith_item_id) b ON a.item_id = b.ith_item_id
                    LEFT JOIN (SELECT ith_item_id, SUM(ith_qty) as qty_in FROM ith WHERE ith_qty > 0 and ith_date between '$filter_from' and '$filter_to' and (whs_id is null or whs_id = 'WHS001') GROUP BY ith_item_id) c ON a.item_id = c.ith_item_id
                    LEFT JOIN (SELECT ith_item_id, SUM(ith_qty) as qty_out FROM ith WHERE ith_qty < 0 and ith_date between '$filter_from' and '$filter_to' and (whs_id is null or whs_id = 'WHS001') GROUP BY ith_item_id) d ON a.item_id = d.ith_item_id
                    JOIN r_prodfam e ON a.pfm_id = e.pfm_id
                    LEFT JOIN r_unit_measure f ON a.um_id = f.um_id
                    WHERE a.pfm_id like '%$filter_product_family%' $where_part and (COALESCE(b.qty_begin, 0) > 0 or COALESCE(c.qty_in, 0) > 0 or COALESCE(abs(d.qty_out), 0) > 0)
                    ORDER BY a.item_id asc");
                $records = $query->result_array();

                $no = 1;
                foreach ($records as $record) {
                    $html .= '<tr>
                                <td style="text-align:center;">' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record['item_id'] . '</td>
                                <td style="mso-number-format:\@;">' . $record['item_name'] . '</td>
                                <td>' . $record['pfm_name'] . '</td>
                                <td>' . $record['um_name'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty_begin'], 0, ",", ".") . '</td>
                                <td style="text-align:right;">' . number_format($record['qty_in'], 0, ",", ".") . '</td>
                                <td style="text-align:right;">' . number_format($record['qty_out'], 0, ",", ".") . '</td>
                                <td style="text-align:right;">' . number_format($record['qty_balance'], 0, ",", ".") . '</td>
                            </tr>';
                    $no++;
                }
            }else{
                $html .= '  <table id="customers" border="1">
                                <tr>
                                    <th width="20" style="text-align:center;">No</th>
                                    <th style="text-align:center;">Part No</th>
                                    <th style="text-align:center;">Part Name</th>
                                    <th style="text-align:center;">Product Family</th>
                                    <th style="text-align:center;">Trans Type</th>
                                    <th style="text-align:center;">Employee</th>
                                    <th style="text-align:center;">Trans Date</th>
                                    <th style="text-align:center;">Document No</th>
                                    <th style="text-align:center;">Invoice No</th>
                                    <th style="text-align:center;">Uom</th>
                                    <th style="text-align:center;">Begin</th>
                                    <th style="text-align:center;">Qty</th>
                                    <th style="text-align:center;">Ending</th>
                                </tr>';

                $queryd = $this->dummy->query("SELECT a.item_id, a.item_name, b.ith_date, b.ith_form, b.ith_docno, coalesce(c.qty_begin, 0) as ith_qtybf, b.ith_qty, b.ith_qtyend, b.employee, b.inv_no, d.pfm_name, e.um_name
                FROM mst_item a 
                JOIN ith b ON a.item_id = b.ith_item_id
                JOIN r_prodfam d ON a.pfm_id = d.pfm_id
                LEFT JOIN r_unit_measure e ON a.um_id = e.um_id
                LEFT JOIN (SELECT ith_item_id, SUM(ith_qty) as qty_begin FROM ith WHERE ith_date < '$filter_from' and (whs_id is null or whs_id = 'WHS001') GROUP BY ith_item_id) c ON a.item_id = c.ith_item_id
                WHERE a.pfm_id like '%$filter_product_family%' $where_part and b.ith_date between '$filter_from' and '$filter_to' and (b.whs_id is null or b.whs_id = 'WHS001')
                order by a.item_id, b.ith_date asc");
                $details = $queryd->result_array();
                $no = 1;
                $item_id = "";
                foreach ($details as $detail) {
                    if($detail['item_id'] != $item_id){
                        $begin_balance = $detail['ith_qtybf'];
                    }

                    $html .= '  <tr>
                                    <td style="text-align:center">'.$no.'</td>
                                    <td style="mso-number-format:\@;">' . $detail['item_id'] . '</td>
                                    <td style="mso-number-format:\@;">' . $detail['item_name'] . '</td>
                                    <td style="mso-number-format:\@;">' . $detail['pfm_name'] . '</td>
                                    <td>' . $detail['ith_form'] . '</td>
                                    <td>' . $detail['employee'] . '</td>
                                    <td>' . $detail['ith_date'] . '</td>
                                    <td>' . $detail['ith_docno'] . '</td>
                                    <td>' . $detail['inv_no'] . '</td>
                                    <td>' . $detail['um_name'] . '</td>
                                    <td style="text-align:right;">' . number_format($begin_balance, 0, ",", ".") . '</td>
                                    <td style="text-align:right;">' . number_format($detail['ith_qty'], 0, ",", ".")  . '</td>
                                    <td style="text-align:right;">' . number_format(($begin_balance + $detail['ith_qty']), 0, ",", ".")  . '</td>
                                </tr>';

                    $begin_balance = ($begin_balance + $detail['ith_qty']);
                    $item_id = $detail['item_id'];
                    $no++;
                }
            }

            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}

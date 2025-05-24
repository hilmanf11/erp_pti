<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_matrix extends CI_Controller
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
            $this->load->view('planning/purchase_order_matrix');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=where_used_$format.xls");
        }

        if ($this->input->get()) {
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));

            $query = $this->db->query("SELECT * FROM mst_item_family WHERE number like '%$filter_product_family%' ORDER BY `number` asc");
            $records = $query->result_array();

            $year_before = date("Y", strtotime("-1 year", strtotime($filter_year)));

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
                                        <small>' . $config->description . '</small>
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
                    <center>
                        <b style="font-size:16px;">PURCHASE ORDER MATRIX</b><br><small>Year '.$filter_year.'</small><br><br>
                    </center>
                    <table id="customers" border="1">
                        <tr>
                            <th rowspan="2" style="text-align:center;" width="30">No</th>
                            <th rowspan="2" style="text-align:center;" width="100">Product Family</th>
                            <th rowspan="2" style="text-align:center;" width="50">Kind</th>
                            <th rowspan="2" style="text-align:center;" width="50">Xbar <br>(Oct - Dec '.$year_before.')</th>
                            <th colspan="3" style="text-align:center;">Period</th>
                            <th rowspan="2" style="text-align:center;" width="50">Xbar</th>
                            <th colspan="3" style="text-align:center;">Period</th>
                            <th rowspan="2" style="text-align:center;" width="50">Xbar</th>
                            <th colspan="3" style="text-align:center;">Period</th>
                            <th rowspan="2" style="text-align:center;" width="50">Xbar</th>
                            <th colspan="3" style="text-align:center;">Period</th>
                            <th rowspan="2" style="text-align:center;" width="50">Xbar</th>
                        </tr>
                        <tr>
                            <th style="text-align:center;" width="50">Jan</th>
                            <th style="text-align:center;" width="50">Feb</th>
                            <th style="text-align:center;" width="50">Mar</th>
                            <th style="text-align:center;" width="50">Apr</th>
                            <th style="text-align:center;" width="50">May</th>
                            <th style="text-align:center;" width="50">Jun</th>
                            <th style="text-align:center;" width="50">Jul</th>
                            <th style="text-align:center;" width="50">Aug</th>
                            <th style="text-align:center;" width="50">Sep</th>
                            <th style="text-align:center;" width="50">Oct</th>
                            <th style="text-align:center;" width="50">Nov</th>
                            <th style="text-align:center;" width="50">Des</th>
                        </tr>';
            $no = 1;
            // $htmlOspo = "";
            // $htmlEndStock = "";
            // $htmlPo = "";
            // $htmlReceipt = "";
            // $htmlWo = "";
            // $htmlSupply = "";
            $content = "";
            $htmlEnd = "";
            foreach ($records as $record) {
                $pfm_id = $record['number'];

                $ospo3 = $this->dummy->query("SELECT x.pfm_id, x.receive, x.request, z.balance FROM (
                    SELECT a.pfm_id, SUM(c.por_receiveqty) as receive, SUM(b.poc_reqqty) as request
                    FROM mst_item a
                    LEFT JOIN (SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where TO_CHAR(poc_date, 'yyyy-mm') in ('$year_before-10','$year_before-11','$year_before-12') GROUP BY item_id) b ON a.item_id = b.item_id
                    LEFT JOIN (SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE TO_CHAR(por_date, 'yyyy-mm') in ('$year_before-10','$year_before-11','$year_before-12') GROUP BY item_id) c ON a.item_id = c.item_id
                    WHERE a.pfm_id = '$pfm_id'
                    GROUP BY a.pfm_id) x
                    JOIN (
                    SELECT a.pfm_id, SUM(CASE WHEN (b.poc_reqqty - c.por_receiveqty) < 0 THEN 0 ELSE (b.poc_reqqty - c.por_receiveqty) END) as balance
                    FROM mst_item a
                    LEFT JOIN (SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where TO_CHAR(poc_date, 'yyyy-mm') = '$year_before-12' GROUP BY item_id) b ON a.item_id = b.item_id
                    LEFT JOIN (SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE TO_CHAR(por_date, 'yyyy-mm') = '$year_before-12' GROUP BY item_id) c ON a.item_id = c.item_id
                    WHERE a.pfm_id = '$pfm_id'
                    GROUP BY a.pfm_id) z ON x.pfm_id = z.pfm_id");
                $ospoRow3 = $ospo3->result_array();

                $year_before1 = $year_before."10";
                $year_before2 = $year_before."11";
                $year_before3 = $year_before."12";
                $wo3 = $this->dummy->query("SELECT (SUM(z.need) / 3) as need FROM(
                    SELECT b.bom_com_item, a.periode, (a.qty * b.bom_qty_perassy) as need 
                    FROM wip_trx_mpp a
                    JOIN mst_bom b ON b.bom_par_item = a.assy_no
                    JOIN mst_item c ON b.bom_com_item = c.item_id
                    where a.periode in ('$year_before1','$year_before2','$year_before3') and c.pfm_id = '$pfm_id'
                    order by b.bom_com_item, a.periode) z");
                $woRow3 = $wo3->result_array();

                $endstock3 = $this->dummy->query("SELECT coalesce(round(sum(a.ith_qty)),0) as stock
                from ith a
                JOIN mst_item b ON a.ith_item_id = b.item_id and b.stscode_id = '01'
                where TO_CHAR(a.ith_date, 'yyyy-mm') <= '$year_before-12' and b.pfm_id = '$pfm_id'");
                $endstockRow3 = $endstock3->result_array();

                $supply3 = $this->dummy->query("SELECT coalesce(round(sum(a.ith_qty)),0) as stock
                from ith a
                JOIN mst_item b ON a.ith_item_id = b.item_id and b.stscode_id = '01'
                where TO_CHAR(a.ith_date, 'yyyy-mm') in ('$year_before-10','$year_before-11','$year_before-12')  and b.pfm_id = '$pfm_id'
                and a.ith_form like '%OU%' and a.ith_form not in ('OU - Adjust Out','OU - ADJUST OUT','ADJUST OUT','OU - ADJ OUT IDM','ADJUST-OUT')");
                $supplyRow3 = $supply3->result_array();

                $htmlOspo       = '<tr>
                                        <td rowspan="6" style="text-align:center;">' . $no . '</td>
                                        <td rowspan="6">' . $record['name'] . '</td>
                                        <td>OS PO</td>
                                        <td style="text-align:right;">'.number_format((@$ospoRow3[0]['balance'] / 3)).'</td>';
                $htmlEndStock   = '<tr>
                                        <td>End Stock</td>
                                        <td style="text-align:right;">'.number_format(@$endstockRow3[0]['stock']).'</td>';
                $htmlPo         = '<tr>
                                        <td>PO</td>
                                        <td style="text-align:right;">'.number_format(@$ospoRow3[0]['request'] / 3).'</td>';
                $htmlReceipt    = '<tr>
                                        <td>Receipt</td>
                                        <td style="text-align:right;">'.number_format(@$ospoRow3[0]['receive'] / 3).'</td>';
                $htmlWo         = '<tr>
                                        <td>WO</td>
                                        <td style="text-align:right;">'.number_format(@$woRow3[0]['need']).'</td>';
                $htmlSupply     = '<tr>
                                        <td>Supply</td>
                                        <td style="text-align:right;">'.number_format(abs(@$supplyRow3[0]['stock']) / 3).'</td>';
                
                $months = array(
                    '01' => 'January', 
                    '02' => 'February', 
                    '03' => 'March',
                    '13' => 'xbar',
                    '04' => 'April', 
                    '05' => 'May', 
                    '06' => 'June', 
                    '14' => 'xbar',
                    '07' => 'July', 
                    '08' => 'August', 
                    '09' => 'September',
                    '15' => 'xbar', 
                    '10' => 'October', 
                    '11' => 'November', 
                    '12' => 'December',
                    '16' => 'xbar'
                );

                $htmlOspo_1 = "";
                $htmlEndStock_1 = "";
                $htmlPo_1 = "";
                $htmlReceipt_1 = "";
                $htmlWo_1 = "";
                $htmlSupply_1 = "";
                $xbarOspo = 0;
                $xbarEndstock = 0;
                $xbarPo = 0;
                $xbarReceipt = 0;
                $xbarWo = 0;
                $xbarSupply = 0;
                foreach ($months as $key => $value) {
                    if($value != "xbar"){
                        $ospo = $this->dummy->query("SELECT x.pfm_id, x.receive, x.request, z.balance FROM (
                            SELECT a.pfm_id, SUM(c.por_receiveqty) as receive, SUM(b.poc_reqqty) as request
                            FROM mst_item a
                            LEFT JOIN (SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where TO_CHAR(poc_date, 'yyyy-mm') = '$filter_year-$key' GROUP BY item_id) b ON a.item_id = b.item_id
                            LEFT JOIN (SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE TO_CHAR(por_date, 'yyyy-mm') = '$filter_year-$key' GROUP BY item_id) c ON a.item_id = c.item_id
                            WHERE a.pfm_id = '$pfm_id'
                            GROUP BY a.pfm_id) x
                            JOIN (
                            SELECT a.pfm_id, SUM(CASE WHEN (b.poc_reqqty - c.por_receiveqty) < 0 THEN 0 ELSE (b.poc_reqqty - c.por_receiveqty) END) as balance
                            FROM mst_item a
                            LEFT JOIN (SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where TO_CHAR(poc_date, 'yyyy-mm') <= '$filter_year-$key' GROUP BY item_id) b ON a.item_id = b.item_id
                            LEFT JOIN (SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE TO_CHAR(por_date, 'yyyy-mm') <= '$filter_year-$key' GROUP BY item_id) c ON a.item_id = c.item_id
                            WHERE a.pfm_id = '$pfm_id'
                            GROUP BY a.pfm_id) z ON x.pfm_id = z.pfm_id");
                        $ospoRow = $ospo->result_array();

                        $wo = $this->dummy->query("SELECT SUM(z.need) as need FROM(
                            SELECT b.bom_com_item, a.periode, (a.qty * b.bom_qty_perassy) as need 
                            FROM wip_trx_mpp a
                            JOIN mst_bom b ON b.bom_par_item = a.assy_no
                            JOIN mst_item c ON b.bom_com_item = c.item_id
                            where a.periode = '$filter_year$key' and c.pfm_id = '$pfm_id'
                            order by b.bom_com_item, a.periode) z");
                        $woRow = $wo->result_array();

                        $endstock = $this->dummy->query("SELECT coalesce(round(sum(a.ith_qty)),0) as stock
                        from ith a
                        JOIN mst_item b ON a.ith_item_id = b.item_id and b.stscode_id = '01'
                        where TO_CHAR(a.ith_date, 'yyyy-mm') <= '$filter_year-$key' and b.pfm_id = '$pfm_id'");
                        $endstockRow = $endstock->result_array();

                        $supply = $this->dummy->query("SELECT coalesce(round(sum(a.ith_qty)),0) as stock
                        from ith a
                        JOIN mst_item b ON a.ith_item_id = b.item_id and b.stscode_id = '01'
                        where TO_CHAR(a.ith_date, 'yyyy-mm') = '$filter_year-$key' and b.pfm_id = '$pfm_id'
                        and a.ith_form like '%OU%' and a.ith_form not in ('OU - Adjust Out','OU - ADJUST OUT','ADJUST OUT','OU - ADJ OUT IDM','ADJUST-OUT')");
                        $supplyRow = $supply->result_array();

                        $xbarOspo += @$ospoRow[0]['balance'];
                        $xbarEndstock += @$endstockRow[0]['stock'];
                        $xbarPo += @$ospoRow[0]['request'];
                        $xbarReceipt += @$ospoRow[0]['receive'];
                        $xbarWo += @$woRow[0]['need'];
                        $xbarSupply += @$supplyRow[0]['stock'];
                    }

                    if($value != "xbar"){
                        $htmlOspo_1 .= '<td style="text-align:right;">'.number_format(@$ospoRow[0]['balance']).'</td>';
                    }else{
                        $htmlOspo_1 .= '<td style="text-align:right;">'.number_format(@$xbarOspo / 3).'</td>';
                    }

                    if($value != "xbar"){
                        if(@$endstockRow[0]['stock'] < 0){
                            $color = "background:#ffbcbc;";
                        }else{
                            $color = "";
                        }

                        $htmlEndStock_1 .= '<td style="text-align:right;'.$color.'">'.number_format(@$endstockRow[0]['stock']).'</td>';
                    }else{
                        if(@$xbarEndstock < 0){
                            $color = "background:#ffbcbc;";
                        }else{
                            $color = "";
                        }

                        $htmlEndStock_1 .= '<td style="text-align:right;'.$color.'">'.number_format(@$xbarEndstock / 3).'</td>';
                    }

                    if($value != "xbar"){
                        $htmlPo_1 .= '<td style="text-align:right;">'.number_format(@$ospoRow[0]['request']).'</td>';
                    }else{
                        $htmlPo_1 .= '<td style="text-align:right;">'.number_format(@$xbarPo / 3).'</td>';
                    }

                    if($value != "xbar"){
                        $htmlReceipt_1 .= '<td style="text-align:right;">'.number_format(@$ospoRow[0]['receive']).'</td>';
                    }else{
                        $htmlReceipt_1 .= '<td style="text-align:right;">'.number_format(@$xbarReceipt / 3).'</td>';
                    }

                    if($value != "xbar"){
                        $htmlWo_1 .= '<td style="text-align:right;">'.number_format(@$woRow[0]['need']).'</td>';
                    }else{
                        $htmlWo_1 .= '<td style="text-align:right;">'.number_format(@$xbarWo / 3).'</td>';
                    }

                    if($value != "xbar"){
                        $htmlSupply_1 .= '<td style="text-align:right;">'.number_format(abs(@$supplyRow[0]['stock'])).'</td>';
                    }else{
                        $htmlSupply_1 .= '<td style="text-align:right;">'.number_format(abs(@$xbarSupply / 3)).'</td>';
                    }

                    if($value == "xbar"){
                        $xbarOspo = 0;
                        $xbarEndstock = 0;
                        $xbarPo = 0;
                        $xbarReceipt = 0;
                        $xbarWo = 0;
                        $xbarSupply = 0;
                    }
                }

                $htmlOspo .= $htmlOspo_1. "</tr>";
                $htmlEndStock .= $htmlEndStock_1. "</tr>";
                $htmlPo .= $htmlPo_1. "</tr>";
                $htmlReceipt .= $htmlReceipt_1. "</tr>";
                $htmlWo .= $htmlWo_1. "</tr>";
                $htmlSupply .= $htmlSupply_1. "</tr>";

                $content .= $htmlOspo . $htmlEndStock . $htmlPo . $htmlReceipt . $htmlWo . $htmlSupply;
                $no++;
            }

            $htmlEnd .= '</table>';
            $htmlEnd .= '</body></html>';
            echo $html . $content . $htmlEnd;
        }
    }
}


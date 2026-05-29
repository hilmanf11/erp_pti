<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Report_po_analisys extends CI_Controller
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
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/report_po_analisys');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_po_analysis_$format.xls");
        }



        $this->dummy = $this->load->database('dummy', TRUE);

        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $period = $filter_year."-".$filter_month;
            $period3 = date("Y-m", strtotime("-3 month", strtotime($period)));
            $period2 = date("Y-m", strtotime("-2 month", strtotime($period)));
            $period1 = date("Y-m", strtotime("-1 month", strtotime($period)));
// var_dump($filter_month,'azis',$filter_year,'okee',$filter_product_family,'kebak',$filter_part_no,'damnv',$filter_revision);
            $query = $this->dummy->query("SELECT f.pfm_name, a.item_id, a.item_name,
                COALESCE(b.qty3, 0) as qty3,
                COALESCE(c.qty2, 0) as qty2,
                COALESCE(d.qty1, 0) as qty1,
                COALESCE(e.qty, 0) as qty
            from mst_item a
            join r_prodfam f ON a.pfm_id = f.pfm_id
            left join (select item_id, SUM(poc_reqqty) as qty3 from poc WHERE TO_CHAR(poc_reqdate, 'YYYY-MM') = '$period3' GROUP BY item_id) b ON a.item_id = b.item_id
            left join (select item_id, SUM(poc_reqqty) as qty2 from poc WHERE TO_CHAR(poc_reqdate, 'YYYY-MM') = '$period2' GROUP BY item_id) c ON a.item_id = c.item_id
            left join (select item_id, SUM(poc_reqqty) as qty1 from poc WHERE TO_CHAR(poc_reqdate, 'YYYY-MM') = '$period1' GROUP BY item_id) d ON a.item_id = d.item_id
            left join (select item_id, SUM(poc_reqqty) as qty from poc WHERE TO_CHAR(poc_reqdate, 'YYYY-MM') = '$period' GROUP BY item_id) e ON a.item_id = e.item_id
            WHERE a.pfm_id like '%$filter_product_family%' and a.item_id like '%$filter_part_no%' and (b.qty3 > 0 or c.qty2 > 0 or d.qty1 > 0 or e.qty > 0)
            ORDER BY a.pfm_id asc");
            $records = $query->result_array();

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
                                        <small>REPORT PO ANALYSIS</small><br><br>
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
                            <th rowspan="2" style="text-align:center;" width="30">No</th>
                            <th rowspan="2" style="text-align:center;">Product Family</th>
                            <th rowspan="2" style="text-align:center;">Part No</th>
                            <th rowspan="2" style="text-align:center;">Revisi</th>
                            <th rowspan="2" style="text-align:center;">Part Name</th>
                            <th colspan="4" style="text-align:center;">Previous 3 Month</th>
                            <th rowspan="2" style="text-align:center;">Current Month</th>
                            <th rowspan="2" style="text-align:center;">Deviation <br> > +- 20%</th>
                            <th rowspan="2" style="text-align:center;">Correction<br>Qty</th>
                            <th rowspan="2" style="text-align:center;">Remarks</th>
                        </tr>
                        <tr>
                            <td style="text-align:center;">M-3</td>
                            <td style="text-align:center;">M-2</td>
                            <td style="text-align:center;">M-1</td>
                            <td style="text-align:center;">Xbar</td>
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                if($filter_revision == '') {
                    $query = $this->db->query("
                        SELECT revision
                        FROM `purchase_orders` 
                        WHERE item_id LIKE '%" . trim($record['item_id']) . "%' 
                          AND revision = (
                              SELECT MAX(revision) 
                              FROM `purchase_orders` 
                              WHERE item_id LIKE '%" . trim($record['item_id']) . "%'
                          )
                        ORDER BY ABS(DATEDIFF(purchase_order_date, CURRENT_DATE())) ASC 
                        LIMIT 1
                    ");
                    
                    $revisinow_result = $query->row_array();
                } else {
                    // If $filter_revision is not empty, filter by revision
                    $query = $this->db->query("
                        SELECT revision
                        FROM `purchase_orders` 
                        WHERE item_id LIKE '%" . trim($record['item_id']) . "%' 
                          AND revision = (
                              SELECT MAX(revision) 
                              FROM `purchase_orders` 
                              WHERE item_id LIKE '%" . trim($record['item_id']) . "%'
                          )
                          AND revision = '" . $filter_revision . "' 
                        ORDER BY ABS(DATEDIFF(purchase_order_date, CURRENT_DATE())) ASC 
                        LIMIT 1
                    ");
                    
                    $revisinow_result = $query->row_array();
                }
            
                // Check if $revisinow_result is null or empty
                if (empty($revisinow_result['revision'])) {
                    continue;  // Skip to the next iteration of the loop if revision is null or empty
                }
            
                // If revision is found, use it
                $revisinow = $revisinow_result['revision'];  // If a valid revision is found
            
                // Your further processing (e.g., calculating and displaying the results)
                $item_id = $record['item_id'];

                $query2 = $this->db->query("SELECT part_no, reason, COALESCE(SUM(purchase_request), 0) as total_pr FROM purchase_requests WHERE part_no = '$item_id' and p_month = '$filter_month' and p_year='$filter_year' order by revision desc");
                $record2 = $query2->row();


                // $xbar = round(($record['qty3'] + $record['qty2'] + $record['qty1']) / 3);
                
                $qty3 = $record['qty3'];
                $qty2 = $record['qty2'];
                $qty1 = $record['qty1'];
                
                // Hitung rata-rata, abaikan nilai non-numerik
                $values = array_filter([$qty3, $qty2, $qty1], 'is_numeric');
                $xbar = count($values) > 0 ? array_sum($values) / count($values) : 0;
                
                if($record['qty'] > 0 && $xbar > 0){
                    $deviation = round((($xbar - $record['qty']) / $xbar * 100), 2);
                }else{
                    $deviation = 0;
                }

                if($deviation > 20){
                    $style = "style='background: #fde6e6;'";
                }else{
                    $style = "";
                }

                // Menghitung nilai presentase atau rasio
                $percentage = $xbar ? (($xbar - $record['qty']) / $xbar) : 0; // Menghindari pembagian dengan nol

                // Mengatur gaya berdasarkan nilai deviasi
                $percentage = $xbar ? (($xbar - $record['qty']) / $xbar) : 0; // Menghindari pembagian dengan nol

                // Menambahkan baris ke HTML
                $html .= '<tr>
                <td style="text-align:center;">' . $no . '</td>
               <td>' . htmlspecialchars($record['pfm_name']) . '</td>
 <td>' . htmlspecialchars($record['item_id']) . '</td>
          
 <td>'.$revisinow.'</td>
                <td>' . htmlspecialchars($record['item_name']) . '</td>
                <td>' . htmlspecialchars($record['qty3']) . '</td>
                <td>' . htmlspecialchars($record['qty2']) . '</td> 
                <td>' . htmlspecialchars($record['qty1']) . '</td>
                <td>' . htmlspecialchars(round($xbar)) . '</td>
                <td>' . $record['qty'] . '</td>
                <td style="background-color:' . ($deviation > 20 ? 'red' : 'transparent') . '; color:' . ($deviation > 20 ? 'white' : 'black') . ';">
                    ' . $deviation . '%
                </td>
                <td>' . ($record2->total_pr ?? 0) . '</td>
                <td>' . htmlspecialchars($record2->reason ?? '') . '</td>
           </tr>';

$no++;
            }
            $html .= '</table>';
        }
        

        $html .= '</body></html>';
        echo $html;
    }
}

<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Report_os_supply extends CI_Controller
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
            $this->load->view('planning/report_os_supply');
        } else {
            redirect('error_access');
        }
    }

    function getWp($filter_year, $filter_month){
        $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
        $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month")));

        $wp = 0;
        $tgl = 1;
        $alfabet = "z";
        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date('Y-m-d', strtotime($firstDate));

            $this->db->select('remarks');
            $this->db->from('working_calendar');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    if($alfabet == "z"){
                        $alfabets = "A";
                    }elseif($alfabet == "A"){
                        $alfabets = "B";
                    }elseif($alfabet == "B"){
                        $alfabets = "C";
                    }elseif($alfabet == "C"){
                        $alfabets = "D";
                    }elseif($alfabet == "D"){
                        $alfabets = "E";
                    }elseif($alfabet == "E"){
                        $alfabets = "F";
                    }elseif($alfabet == "F"){
                        $alfabets = "G";
                    }elseif($alfabet == "G"){
                        $alfabets = "H";
                    }elseif($alfabet == "H"){
                        $alfabets = "I";
                    }elseif($alfabet == "I"){
                        $alfabets = "J";
                    }elseif($alfabet == "J"){
                        $alfabets = "K";
                    }elseif($alfabet == "K"){
                        $alfabets = "L";
                    }elseif($alfabet == "L"){
                        $alfabets = "M";
                    }elseif($alfabet == "M"){
                        $alfabets = "N";
                    }elseif($alfabet == "N"){
                        $alfabets = "O";
                    }else{  
                        $alfabets = "";
                    }

                    $wpp[$working_date] = $wp.$alfabets;
                    $alfabet = $alfabets;
                    $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                    $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date_check);
                    $holiday_check = $this->db->get()->row();

                    if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                        if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                            $wp++;
                        }
                    }
                }else{
                    if($wp == 0){
                        $wp = 1;
                    }

                    $wpp[$working_date] = $wp;
                    $alfabet = "z";
                    $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                    $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date_check);
                    $holiday_check = $this->db->get()->row();

                    if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                        if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                            $wp++;
                        }
                    }
                }
            }else{
                if($alfabet == "z"){
                    $alfabets = "A";
                }elseif($alfabet == "A"){
                    $alfabets = "B";
                }elseif($alfabet == "B"){
                    $alfabets = "C";
                }elseif($alfabet == "C"){
                    $alfabets = "D";
                }elseif($alfabet == "D"){
                    $alfabets = "E";
                }elseif($alfabet == "E"){
                    $alfabets = "F";
                }elseif($alfabet == "F"){
                    $alfabets = "G";
                }elseif($alfabet == "G"){
                    $alfabets = "H";
                }elseif($alfabet == "H"){
                    $alfabets = "I";
                }elseif($alfabet == "I"){
                    $alfabets = "J";
                }elseif($alfabet == "J"){
                    $alfabets = "K";
                }elseif($alfabet == "K"){
                    $alfabets = "L";
                }elseif($alfabet == "L"){
                    $alfabets = "M";
                }elseif($alfabet == "M"){
                    $alfabets = "N";
                }elseif($alfabet == "N"){
                    $alfabets = "O";
                }else{  
                    $alfabets = "";
                }

                $wpp[$working_date] = $wp.$alfabets;
                $alfabet = $alfabets;
                $firstDate_check = date("d M", strtotime("+1 day", strtotime($firstDate)));
                $working_date_check = date('Y-m-d', strtotime($firstDate_check));
                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $working_date_check);
                $holiday_check = $this->db->get()->row();

                if (date('w', strtotime($firstDate_check)) !== '0' && date('w', strtotime($firstDate_check)) !== '6') {
                    if (@$holiday_check->remarks == null or @$holiday_check->remarks == "") {
                        $wp++;
                    }
                }
            }
            $tgl++;
            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        }

        return $wpp;
    }

    public function pagenasion($option = "") {
        // Sample dynamic content for pagination
        $html = '<div style="text-align:center;">';
        $html .= '<button onclick="prevPage()">Previous</button>';
        $html .= '<span> Page 1 of 10 </span>';
        $html .= '<button onclick="nextPage()">Next</button>';
        $html .= '</div>';
    
        echo $html;
    }
    


    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_os_supply_$format.xls");
        }

        $this->dummy = $this->load->database('dummy', TRUE);

        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));

            $period_start = date("Y-m-d", strtotime($filter_year."-".$filter_month."-01"));
            $period_finish = date("Y-m-d", strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));

            $header = "";
            $content = "";
            while (strtotime($period_start) < strtotime($period_finish)) {
                $monthName = date('d, D', strtotime($period_start));
                $wpFormat = date('Y-m-d', strtotime($period_start));

                $wp = $this->getWp($filter_year, $filter_month);

                if(date("w", strtotime($period_start)) == "0" || date("w", strtotime($period_start)) == "6"){
                    $style = "background: #efbaba;";
                }else{
                    $style = "";
                }
                $header .= '<th colspan="3" style="text-align:center;'.$style.'">'.$monthName.' (WP '.$wp[$wpFormat].')</th>';
                $content .= '<th style="text-align:center;'.$style.'">Need</th><th style="text-align:center;'.$style.'">Supply</th><th style="text-align:center;'.$style.'">Bal</th>';
                $period_start = date("Y-m-d", strtotime("+1 day", strtotime($period_start)));
            }
            $rows_per_page = 10; // Number of rows per page
            $total_rows_query = $this->db->query("SELECT COUNT(*) as total FROM mst_bom a
                JOIN mst_item_raw b ON a.component_id = b.item_id
                JOIN generate_mpp c ON a.item_id = c.product_no
                WHERE c.p_month = '$filter_month' and c.p_year = '$filter_year' and b.pfm_id = '$filter_product_family' and a.component_id like '%$filter_part_no%'");
            $total_rows = $total_rows_query->row()->total;
            $total_pages = ceil($total_rows / $rows_per_page);
            $current_page = $this->input->get('page') ? intval($this->input->get('page')) : 1; // Get current page from URL
            $offset = ($current_page - 1) * $rows_per_page; 

            $query = $this->db->query("SELECT b.item_id, b.item_name, a.qty as qpa, 
            SUM(c.date_1) as date_01,
            SUM(c.date_2) as date_02,
            SUM(c.date_3) as date_03,
            SUM(c.date_4) as date_04,
            SUM(c.date_5) as date_05,
            ROUND(SUM(c.prod_plan * a.qty), 0) as need
        FROM mst_bom a
        JOIN mst_item_raw b ON a.component_id = b.item_id
        JOIN generate_mpp c ON a.item_id = c.product_no
        WHERE c.p_month = '$filter_month' and c.p_year = '$filter_year' and b.pfm_id = '$filter_product_family' and a.component_id like '%$filter_part_no%'
        GROUP BY a.component_id
        LIMIT $rows_per_page OFFSET $offset");
        $records = $query->result_array();
            $this->db   ->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();
            //Config
            $html = '<html>
            <head>
                <title>Print Data</title>
                <style>
                    body { 
                        font-family: Arial, Helvetica, sans-serif; 
                    }
                    #customers {
                        border-collapse: collapse;
                        width: 280%;
                        font-size: 10px;
                    }
                    #customers td, #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }
                    #customers tr:hover {
                        background-color: #ddd;
                    }
                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: left;
                        color: black;
                    }
            
                    .str {
                        mso-number-format: \@;
                    }
            
                    @media print {
   #customers {
        transform: scale(0.3); /* Scale down the table content */
        transform-origin: top left; /* Ensure it scales from the top left */
        width: 50%; /* Slightly increase the width if needed */
    }
                        @page {
                            size: landscape; /* Set orientation to landscape */
                        }
                            
                    }
                </style>
            </head>
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
                                    <small>REPORT OS SUPPLY</small><br><br>
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
                        <th rowspan="2" width="30">No</th>
                        <th rowspan="2" style="text-align:center;">Part No</th>
                        <th rowspan="2" style="text-align:center;">Part Name</th>
                        <th colspan="4" style="text-align:center;">Recap Last Month</th>
                        <th colspan="3" style="text-align:center;">Recap Current Month</th>
                        <th rowspan="2" style="text-align:center;">Balance<br>WIP</th>
                        <th rowspan="2" style="text-align:center;">Curent<br>WP</th>
                        <th rowspan="2" style="text-align:center;">Last<br>Supply</th>
                        ' . $header . '
                    </tr>
                    <tr>
                        <th style="text-align:center;">Need</th>
                        <th style="text-align:center;">Supply</th>
                        <th style="text-align:center;">OS</th>
                        <th style="text-align:center;">Last<br>Supply</th>
                        <th style="text-align:center;">Need</th>
                        <th style="text-align:center;">Supply</th>
                        <th style="text-align:center;">OS</th>
                        ' . $content . '
                    </tr>';
            $no = 1;
            $content = "";
            
            foreach ($records as $record) {
                $item_id = $record['item_id'];
                
                //CURRENT NEED
                $monthCurrent = date("m", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
                $yearCurrent = date("Y", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
                $queryCurrentNeed = $this->db->query("SELECT a.component_id, a.qty as qpa, 
                    ROUND(SUM(c.prod_plan * a.qty), 0) as need
                FROM mst_bom a
                JOIN generate_mpp c ON a.item_id = c.product_no
                WHERE c.p_month = '$monthCurrent' and c.p_year = '$yearCurrent' and a.component_id = '$item_id'
                GROUP BY a.component_id");
                $rowCurrentNeed = $queryCurrentNeed->row();
                $current_need = empty(@$rowCurrentNeed->need) ? 0 : @$rowCurrentNeed->need;

                //CURRENT WP
                $cutoff_form =  date('Y-m-01', strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
                $cutoff_to =  date('Y-m-t', strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
              
                $queryCurrentWp = $this->dummy->query("SELECT z.item_id, z.item_name, z.pfm_name, max(z.wp) as wp from (
                    select a.temp_woc_id, a.periode, a.wp, d.item_id, d.item_name, e.pfm_name
                    from wip_trx_mpp a
                    join mst_bom b ON a.assy_no = b.bom_par_item
                    join mst_item d on b.bom_com_item = d.item_id and d.stscode_id = '01'
                        join r_prodfam e on d.pfm_id = e.pfm_id and e.prod_group = 'Raw Material' and e.pfm_id != '05'
                    where b.bom_com_item = '$item_id' and a.wp_date between '$cutoff_form' and '$cutoff_to'
                    group by a.temp_woc_id, a.periode, a.wp, a.qty, b.bom_qty_perassy, d.item_id, d.item_name, e.pfm_name) z
                    GROUP BY z.item_id, z.item_name, z.pfm_name 
                ");
                $rowCurrentWp = $queryCurrentWp->row();
                $current_wp = empty(@$rowCurrentWp->wp) ? "-" : "WP ".@$rowCurrentWp->wp;

                $queryCurrentSupply = $this->dummy->query("SELECT ith_item_id, coalesce(round(sum(ith_qty)),0) as stock
                    from ith
                    where ith_date between '$cutoff_form' and '$cutoff_to' and ith_item_id = '$item_id'
                    and ith_form like '%OU%' and ith_form not in ('OU - Adjust Out','OU - ADJUST OUT','ADJUST OUT','OU - ADJ OUT IDM','ADJUST-OUT')
                    GROUP by ith_item_id
                ");
                $rowCurrentSupply = $queryCurrentSupply->row();
                $current_supply = empty(@$rowCurrentSupply->stock) ? 0 : abs(@$rowCurrentSupply->stock);

                //CURRENT WP
                $wpFormat2 = date('Y-m-d');
                $wp2 = $this->getWp(date("Y"), date("m"));
                $wpp2 = sprintf("%02s", $wp2[$wpFormat2])."/".date("m");

                $cutoff_form =  date('Y-m-01', strtotime($filter_year."-".$filter_month."-01"));
                $cutoff_to =  date('Y-m-t', strtotime($filter_year."-".$filter_month."-01"));
          
            $queryoutqty = $this->dummy->query("
            select outqty from serial_barcode_outgoing where itemid = '$item_id' order by outscan desc limit 1
            ");
            $rowCurrentoutqty = $queryoutqty->row();
            $current_outqty = empty(@$rowCurrentoutqty->outqty) ? 0 : abs(@$rowCurrentoutqty->outqty);
                $querySupply = $this->dummy->query("SELECT z.wp, z.stock as supply from (
                    select a.temp_woc_id, a.periode, a.wp, d.item_id, d.item_name, e.pfm_name, coalesce(f.stock, 0) as stock
                    from wip_trx_mpp a
                    join mst_bom b  ON a.assy_no = b.bom_par_item
                    join mst_item d on b.bom_com_item = d.item_id and d.stscode_id = '01'
                    join r_prodfam e on d.pfm_id = e.pfm_id and e.prod_group = 'Raw Material' and e.pfm_id != '05'
                    left join (SELECT ith_item_id, ith_date, coalesce(round(sum(ith_qty)),0) as stock
                        from ith
                        where ith_form like '%OU%' and ith_form not in ('OU - Adjust Out','OU - ADJUST OUT','ADJUST OUT','OU - ADJ OUT IDM','ADJUST-OUT')
                        GROUP by ith_item_id, ith_date) f ON d.item_id = f.ith_item_id and a.wp_date = f.ith_date
                    where b.bom_com_item = '$item_id' and a.wp_date between '$cutoff_form' and '$cutoff_to'
                    group by a.temp_woc_id, a.periode, a.wp, d.item_id, d.item_name, e.pfm_name, f.stock) z
                    GROUP BY z.wp, z.stock
                    ORDER BY z.wp
                ");
                $dataSupply = $querySupply->result_array();
                $wp = $this->getWp($filter_year, $filter_month);

                $nowSupply = 0;
                $nowSupplyOs = 0;
                $nowWp = "";
                $dataArr = [];
                foreach ($dataSupply as $rowSupply) {
                    $dataArr[$rowSupply['wp']] = abs($rowSupply['supply']);
                    $nowSupply += abs($rowSupply['supply']);
                    $nowSupplyOs += $rowSupply['supply'] - $record['need'];
                    $nowWp = $rowSupply['wp'];
                }

                    $tgl = 1;
    $period_start2 = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
    $period_finish2 = date("Y-m-d", strtotime("1 month", strtotime($filter_year . "-" . $filter_month . "-01")));
    $content = "";
    $hold = [];
    while (strtotime($period_start2) < strtotime($period_finish2)) {
        $monthName = date('d, D', strtotime($period_start2));
        $wpFormat = date('Y-m-d', strtotime($period_start2));

        $wpp = sprintf("%02s", $wp[$wpFormat]) . "/" . $filter_month;
        $wpFormat = date('Y-m-d', strtotime($period_start2));
        $periodefilter = $filter_year.$filter_month;
        $querySupplyKanban = $this->dummy->query("
      select sum(a.qty) as supply from wip_trx_mpp a inner join mst_bom b ON a.assy_no = b.bom_par_item
	where b.bom_com_item = '$item_id' and a.periode = '$periodefilter' and wp = '$wpp'
    ");
    
    // Fetch the supply value
    $rowloopSupply = $querySupplyKanban->row();
    $currentloopsupply = empty($rowloopSupply->supply) ? 0 : abs($rowloopSupply->supply);

        $supply = empty($dataArr[$wpp]) ? 0 : round($dataArr[$wpp]);

        if (date("w", strtotime($period_start2)) == "0" || date("w", strtotime($period_start2)) == "6") {
            $style = "background: #efbaba;";
        } else {
            $style = "";
        }

        $need = (@$record['date_' . $tgl] == "W" || @$record['date_' . $tgl] == "") ? 0 : @$record['date_' . $tgl];
       
        $currentDay = date('d', strtotime($period_start2));
        $fieldName = 'date_' . $currentDay; // Generates "date_01", "date_02", etc.
        
        // Access the value dynamically from the $record array
        $dateloop = isset($record[$fieldName]) ? $record[$fieldName] : 0;
        // $dateqty_trx = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . date('d', strtotime($period_start2)));
        $dateqty_trx = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-" . date('d', strtotime($period_start2))));
        $queryQty_trx = $this->dummy->query("
            SELECT DISTINCT SUM(qty) AS qty_trx
            FROM public.wip_trx_mpp a
            join mst_bom b ON a.assy_no = b.bom_par_item
where b.bom_com_item = '$item_id' and  date(trx_date) = '$dateqty_trx';
        ");
                $dataqueryQty_trx = $queryQty_trx->result_array();
                $datasqty_trx = isset($dataqueryQty_trx[0]['qty_trx']) ? $dataqueryQty_trx[0]['qty_trx'] : 0; // Default to 0 if no value found
                $oslastmonth = $current_supply - $current_need;
                $osbalance = $nowSupply - $record['need'];
            $baldayone = $oslastmonth + ($currentloopsupply - $dateloop);
        
        if (date('d', strtotime($period_start2)) == "01") {
            $hold[] = $baldayone;
            
            $bgColor = $baldayone < 0 ? 'background-color:red;' : '';
            $content .= '
                <td style="text-align:center;"><b>' . $dateloop . '</b></td>
                <td style="text-align:center;"><b>' . $currentloopsupply . '</b></td>
            <td style="text-align:center;' . $style . $bgColor . '"><b>' . $baldayone . '</b></td>';
    } else {
        if(date('d', strtotime($period_start2)) == "02"){
            $indexid = 0;
            $valueAtTgl = isset($hold[0]) ? $hold[0] : 0;
            $fixwp = $valueAtTgl + ($currentloopsupply - $dateloop);
            $hold[] = $fixwp;
         }else{
            $indexid = $tgl - 2;
            $valueAtTgl = isset($hold[$indexid]) ? $hold[$indexid] : 0;
         
            $fixwp = $valueAtTgl + ($currentloopsupply - $dateloop);
            $hold[] = $fixwp;
        }
        $bgColor = $fixwp < 0 ? 'background-color:red;' : '';


     
        $content .= '
            <td style="text-align:center;"><b>' . $dateloop . '</b></td>
                <td style="text-align:center;"><b>' . $currentloopsupply . '</b></td>
            <td style="text-align:center;' . $style . $bgColor . '">' . $fixwp . '</td>';
    }

    $period_start2 = date("Y-m-d", strtotime("+1 day", strtotime($period_start2)));
    $tgl++;
}  
       
            $balance_wip = $oslastmonth - $osbalance;
                $html .= '<tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td>' . $record['item_id'] . '</td>
                            <td>' . $record['item_name'] . '</td>
                            <td>' . $current_need . '</td>
                            <td>' . $current_supply . '</td>
                            <td>' . $oslastmonth . '</td>
                            <td>' . $current_wp . '</td>
                            <td>' . $record['need'] . '</td>
                            <td>' . $nowSupply . '</td>
                            <td>' . $osbalance . '</td>
                            <td>' . $balance_wip . '</td>
                            <td>' . $wpp2 . '</td>
                            <td>' . $nowWp. '</td>
                            '.$content.'
                        </tr>';
                $no++;
            }
            //oslastmontsh
            // $balance_wip
            $html .= '</table>';
        }
       
        // $html .= '<div style="text-align: center; margin-top: 20px;">';
        // for ($i = 1; $i <= $total_pages; $i++) {
        //     $style = ($i == $current_page) ? 'background-color: red; color: white; font-weight: bold;' : '';
        //     $html .= '<button onclick="navigatePage()" style="' . $style . '">' . $i . '</button> ';
        // }
        // $html .= '</div>';
        $html .= '</body></html>';
      
        echo $html;
        
    }
}

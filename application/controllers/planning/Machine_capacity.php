<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Machine_capacity extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[mpp_realization.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/machine_capacity');
        } else {
            redirect('error_access');
        }
    }

    public function checkCalendar($period)
    {
        $monthStart = strtotime("01 " . $period);
        $start = strtotime(date('Y-m-01', $monthStart));
        $finish = strtotime(date('Y-m-t', $monthStart));

        //HKW 1
        $hkw = 0;
        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $z);

            $this->db->select('remarks');
            $this->db->from('working_calendar');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $hkw += 0;
                } else {
                    $hkw += 1;
                }
            } else {
                $hkw += 0;
            }
        }

        return $hkw;
    }

    public function getData(){
    	if ($this->input->get()) {
	        //Filter Data
	        $filter_month = base64_decode($this->input->get('filter_month'));
	        $filter_year = base64_decode($this->input->get('filter_year'));
	        $filter_revision = base64_decode($this->input->get('filter_revision'));
	        $filter_process_main_number = base64_decode($this->input->get('filter_process_main_number'));
	        $filter_process_sub_number = base64_decode($this->input->get('filter_process_sub_number'));

	        $this->db->select('a.number as process_main_number, 
	        	a.description as process_main_name, 
	        	b.number as process_sub_number,
	        	b.name as process_sub_name');
	        $this->db->from('mst_process_main a');
	        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
	        $this->db->where('a.description', $filter_process_main_number);
	        $this->db->like('b.number', $filter_process_sub_number);
	        $this->db->where('b.deleted_is', 0);
	        $this->db->group_by('b.number');
	        $this->db->order_by('b.flag', 'asc');
	        $records = $this->db->get()->result_array();

	        $this->db->delete('generate_machine_capacity', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);
	        echo json_encode($records);
	    }
    }

    public function create(){
    	if ($this->input->post()) {
	        //Filter Data
	        $post = $this->input->post('data');
	        $filter_month = base64_decode($this->input->get('filter_month'));
	        $filter_year = base64_decode($this->input->get('filter_year'));
	        $filter_revision = base64_decode($this->input->get('filter_revision'));
	        $filter_shift = base64_decode($this->input->get('filter_shift'));
            $filter_overtime = base64_decode($this->input->get('filter_overtime'));
	        $process_main_number = $post['process_main_number'];
	        $process_main_name = $post['process_main_name'];
	        $process_sub_number = $post['process_sub_number'];
	        $process_sub_name = $post['process_sub_name'];

	        $month =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $hkw = $this->checkCalendar($month);

	        $qMachine = $this->db->query("SELECT 
	                    a.process_sub_number, 
	                    COUNT(a.process_sub_number) as total_machine,
	                    b.efficiency
	                    FROM mst_machine a
	                    JOIN mst_process_sub b ON a.process_sub_number = b.number
	                    WHERE a.process_sub_number = '$process_sub_number' and a.status_is = '0'");
	        $dMachine = $qMachine->row();

	        $qCapacity = $this->db->query("SELECT 
	                    process_sub_number, 
	                    manpower,
	                    efficiency
	                    FROM mst_capacity 
	                    WHERE process_sub_number = '$process_sub_number'
	                    GROUP BY process_sub_number");
	        $dCapacity = $qCapacity->row();

	        if (!empty($dMachine->process_sub_number)) {
                $machine_qty = @$dMachine->total_machine;
                $efficiency = @$dMachine->efficiency;
            } else {
                $machine_qty = @$dCapacity->manpower;
                $efficiency = @$dCapacity->efficiency;
            }

            if ($filter_overtime != "0") {
                $overtime = @((($machine_qty * $hkw) * $filter_shift) * $filter_overtime);
            } else {
                $overtime = 0;
            }

            $query = $this->db->query("SELECT 
            		(SUM(a.date_1) * (SUM(b.total_pp) / 3600)) as date_1,
            		(SUM(a.date_2) * (SUM(b.total_pp) / 3600)) as date_2,
            		(SUM(a.date_3) * (SUM(b.total_pp) / 3600)) as date_3,
            		(SUM(a.date_4) * (SUM(b.total_pp) / 3600)) as date_4,
            		(SUM(a.date_5) * (SUM(b.total_pp) / 3600)) as date_5,
            		(SUM(a.date_6) * (SUM(b.total_pp) / 3600)) as date_6,
            		(SUM(a.date_7) * (SUM(b.total_pp) / 3600)) as date_7,
            		(SUM(a.date_8) * (SUM(b.total_pp) / 3600)) as date_8,
            		(SUM(a.date_9) * (SUM(b.total_pp) / 3600)) as date_9,
            		(SUM(a.date_10) * (SUM(b.total_pp) / 3600)) as date_10,
            		(SUM(a.date_11) * (SUM(b.total_pp) / 3600)) as date_11,
            		(SUM(a.date_12) * (SUM(b.total_pp) / 3600)) as date_12,
            		(SUM(a.date_13) * (SUM(b.total_pp) / 3600)) as date_13,
            		(SUM(a.date_14) * (SUM(b.total_pp) / 3600)) as date_14,
            		(SUM(a.date_15) * (SUM(b.total_pp) / 3600)) as date_15,
            		(SUM(a.date_16) * (SUM(b.total_pp) / 3600)) as date_16,
            		(SUM(a.date_17) * (SUM(b.total_pp) / 3600)) as date_17,
            		(SUM(a.date_18) * (SUM(b.total_pp) / 3600)) as date_18,
            		(SUM(a.date_19) * (SUM(b.total_pp) / 3600)) as date_19,
            		(SUM(a.date_20) * (SUM(b.total_pp) / 3600)) as date_20,
            		(SUM(a.date_21) * (SUM(b.total_pp) / 3600)) as date_21,
            		(SUM(a.date_22) * (SUM(b.total_pp) / 3600)) as date_22,
            		(SUM(a.date_23) * (SUM(b.total_pp) / 3600)) as date_23,
            		(SUM(a.date_24) * (SUM(b.total_pp) / 3600)) as date_24,
            		(SUM(a.date_25) * (SUM(b.total_pp) / 3600)) as date_25,
            		(SUM(a.date_26) * (SUM(b.total_pp) / 3600)) as date_26,
            		(SUM(a.date_27) * (SUM(b.total_pp) / 3600)) as date_27,
            		(SUM(a.date_28) * (SUM(b.total_pp) / 3600)) as date_28,
            		(SUM(a.date_29) * (SUM(b.total_pp) / 3600)) as date_29,
            		(SUM(a.date_30) * (SUM(b.total_pp) / 3600)) as date_30,
            		(SUM(a.date_31) * (SUM(b.total_pp) / 3600)) as date_31
                FROM generate_mpp a 
                JOIN (SELECT line_no, product_no, sum(cycle_time) as total_pp FROM mst_umh WHERE process_sub_number = '$process_sub_number' GROUP BY product_no) b on a.product_no = b.product_no and a.line_no = b.line_no
                WHERE a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision'
                GROUP BY a.product_no
                HAVING (SUM(a.date_1) * (SUM(b.total_pp) / 3600)) > 0
                ORDER BY a.product_no asc");
            $records = $query->result_array();

            $capacity = @((($machine_qty * 8) * $filter_shift + $overtime) * ($efficiency / 100));

            $date_1 = 0;
            $date_2 = 0;
            $date_3 = 0;
            $date_4 = 0;
            $date_5 = 0;
            $date_6 = 0;
            $date_7 = 0;
            $date_8 = 0;
            $date_9 = 0;
            $date_10 = 0;
            $date_11 = 0;
            $date_12 = 0;
            $date_13 = 0;
            $date_14 = 0;
            $date_15 = 0;
            $date_16 = 0;
            $date_17 = 0;
            $date_18 = 0;
            $date_19 = 0;
            $date_20 = 0;
            $date_21 = 0;
            $date_22 = 0;
            $date_23 = 0;
            $date_24 = 0;
            $date_25 = 0;
            $date_26 = 0;
            $date_27 = 0;
            $date_28 = 0;
            $date_29 = 0;
            $date_30 = 0;
            $date_31 = 0;
            foreach ($records as $record) {
                $date_1 += @$record['date_1'];
                $date_2 += @$record['date_2'];
                $date_3 += @$record['date_3'];
                $date_4 += @$record['date_4'];
                $date_5 += @$record['date_5'];
                $date_6 += @$record['date_6'];
                $date_7 += @$record['date_7'];
                $date_8 += @$record['date_8'];
                $date_9 += @$record['date_9'];
                $date_10 += @$record['date_10'];
                $date_11 += @$record['date_11'];
                $date_12 += @$record['date_12'];
                $date_13 += @$record['date_13'];
                $date_14 += @$record['date_14'];
                $date_15 += @$record['date_15'];
                $date_16 += @$record['date_16'];
                $date_17 += @$record['date_17'];
                $date_18 += @$record['date_18'];
                $date_19 += @$record['date_19'];
                $date_20 += @$record['date_20'];
                $date_21 += @$record['date_21'];
                $date_22 += @$record['date_22'];
                $date_23 += @$record['date_23'];
                $date_24 += @$record['date_24'];
                $date_25 += @$record['date_25'];
                $date_26 += @$record['date_26'];
                $date_27 += @$record['date_27'];
                $date_28 += @$record['date_28'];
                $date_29 += @$record['date_29'];
                $date_30 += @$record['date_30'];
                $date_31 += @$record['date_31'];
            }

            $postFinal = array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision,
                "shift" => $filter_shift,
                "overtime" => $filter_overtime,
                "process_main_number" => $process_main_number,
                "process_main_name" => $process_main_name,
                "process_sub_number" => $process_sub_number,
                "process_sub_name" => $process_sub_name,
                "machine_qty" => @$machine_qty,
                "efficiency" => @$efficiency,
                "capacity" => $capacity,
                "date_1" => round($date_1, 2),
                "date_2" => round($date_2, 2),
                "date_3" => round($date_3, 2),
                "date_4" => round($date_4, 2),
                "date_5" => round($date_5, 2),
                "date_6" => round($date_6, 2),
                "date_7" => round($date_7, 2),
                "date_8" => round($date_8, 2),
                "date_9" => round($date_9, 2),
                "date_10" => round($date_10, 2),
                "date_11" => round($date_11, 2),
                "date_12" => round($date_12, 2),
                "date_13" => round($date_13, 2),
                "date_14" => round($date_14, 2),
                "date_15" => round($date_15, 2),
                "date_16" => round($date_16, 2),
                "date_17" => round($date_17, 2),
                "date_18" => round($date_18, 2),
                "date_19" => round($date_19, 2),
                "date_20" => round($date_20, 2),
                "date_21" => round($date_21, 2),
                "date_22" => round($date_22, 2),
                "date_23" => round($date_23, 2),
                "date_24" => round($date_24, 2),
                "date_25" => round($date_25, 2),
                "date_26" => round($date_26, 2),
                "date_27" => round($date_27, 2),
                "date_28" => round($date_28, 2),
                "date_29" => round($date_29, 2),
                "date_30" => round($date_30, 2),
                "date_31" => round($date_31, 2),
                "total" => round($date_1 + $date_2 + $date_3 + $date_4 + $date_5 + $date_6 + $date_7 + $date_8 + $date_9 + $date_10 + $date_11 + $date_12 + $date_13 + $date_14 + $date_15 + $date_16 + $date_17 + $date_18 + $date_19 + $date_20 + $date_21 + $date_22 +$date_23 + $date_24 + $date_25 + $date_26 + $date_27 + $date_28 + $date_29 + $date_30 + $date_31, 2)
            );

            if ($this->api->create('generate_machine_capacity', $postFinal, "GMC", "GMC")) {
                echo json_encode(array("title" => "Good Job", "message" => $process_sub_name . " | Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Error", "message" => $process_sub_name . " | Data Unsaved", "theme" => "error"));
            }
	    } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=material_calculation_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Database Server MRP
        $this->dummy = $this->load->database('dummy', TRUE);

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_shift = base64_decode($this->input->get('filter_shift'));
        $filter_overtime = base64_decode($this->input->get('filter_overtime'));
        $filter_process_main_number = base64_decode($this->input->get('filter_process_main_number'));
        $filter_process_sub_number = base64_decode($this->input->get('filter_process_sub_number'));

        $firstDate = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));

        $this->db->select('c.*');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('generate_machine_capacity c', 'b.number = c.process_sub_number');
        $this->db->where('a.description', $filter_process_main_number);
        $this->db->where('c.p_month', $filter_month);
        $this->db->where('c.p_year', $filter_year);
        $this->db->where('c.revision', $filter_revision);
        $this->db->where('c.shift', $filter_shift);
        $this->db->where('c.overtime', $filter_overtime);
        $this->db->like('b.number', $filter_process_sub_number);
        $this->db->group_by("b.number");
        $this->db->order_by("b.flag", "asc");
        $records = $this->db->get()->result_array();

        //Setting Header
        $styles = "";
        $cut = '<tr>';
        $cut_no = 0;
        $cut_content = "";
        $date = '<tr>';
        $content = '';
        $no = 1;
        $colspan = 0;

        while (strtotime($firstDate) <= strtotime($endDate)) {
            if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                $styles = "";
                $cut_no += 1;
                $cut_content = "W/P ". $cut_no;
            } else {
                $styles = 'background:#FFC2C2;';
                $cut_no += 0;
                $cut_content = "";
            }

            //Setting Header
            $cut .= '<th width="50" style="text-align:center; ' . $styles . '">' . $cut_content . '</th>';
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';

            $colspan++;
            $firstDate = date("d M", strtotime("+1 day", strtotime($firstDate)));
        }

        //Setting Header
        $cut .= '</tr>';
        $date .= '</tr>';

        foreach ($records as $record) {
            $day = 1;
            $i = 0;
            $firstDate2 = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $firstDate3 = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate3 = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $firstDate4 = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate4 = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));

            $content .= "<tr>
	                         <td rowspan='3'>" . $no . "</td>
	                         <td rowspan='3' style='mso-number-format:\@;'>" . $record['process_main_name'] . "</td>
	                         <td rowspan='3'>" . $record['process_sub_name'] . "</td>
	                         <td rowspan='3' style='text-align:right;'>".$record['machine_qty']."</td>
	                         <td>LOADING</td>";

            $styles2 = "";
            $loading = 1;
            while (strtotime($firstDate2) <= strtotime($endDate2)) {

                if (date('w', strtotime($firstDate2)) !== '0' && date('w', strtotime($firstDate2)) !== '6') {
                    $styles2 = "";
                } else {
                    $styles2 = 'background:#FFC2C2;';
                }

                $content .= "<td style='text-align:right; ".$styles2."'>".$record['date_'.$loading]."</td>";

                $firstDate2 = date("d M", strtotime("+1 day", strtotime($firstDate2)));
                $loading++;
            }

            $content .= "<td style='text-align:right;'>".$record['total']."</td></tr>";
            $content .= "<tr>
	                        <td>CAPACITY</td>";

            $styles2 = "";
            while (strtotime($firstDate3) <= strtotime($endDate3)) {

                if (date('w', strtotime($firstDate3)) !== '0' && date('w', strtotime($firstDate3)) !== '6') {
                    $styles2 = "";
                    $capacity = $record['capacity'];
                } else {
                    $styles2 = 'background:#FFC2C2;';
                    $capacity = 0;
                }

                $content .= "<td style='text-align:right; ".$styles2."'>".$capacity."</td>";

                $firstDate3 = date("d M", strtotime("+1 day", strtotime($firstDate3)));
                $day++;
            }

            $content .= "<td style='text-align:right;'>".($record['capacity'])."</td></tr>";
            $content .= "<tr>
	                        <td>BALANCE</td>";

            $styles2 = "";
            $loading2 = 1;
            while (strtotime($firstDate4) <= strtotime($endDate4)) {

                if (date('w', strtotime($firstDate4)) !== '0' && date('w', strtotime($firstDate4)) !== '6') {
                    $styles2 = "";
                    $balance = ($record['capacity'] - $record['date_'.$loading2]);
                } else {
                    $styles2 = 'background:#FFC2C2;';
                    $balance = 0;
                }

                $content .= "<td style='text-align:right; ".$styles2."'>".$balance."</td>";

                $firstDate4 = date("d M", strtotime("+1 day", strtotime($firstDate4)));
                $loading2++;
            }

            $content .= "<td style='text-align:right;'>".($record['capacity'] - $record['total'])."</td></tr>";
            $no++;
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>CAPACITY MACHINE</small>
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

        <table id="customers" border="1" style="width:150%;">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">NO</th>
                <th style="text-align:center;" width="100" rowspan="3">MAIN PROCESS</th>
                <th style="text-align:center;" width="150" rowspan="3">SUB PROCESS</th>
                <th style="text-align:center;" width="100" rowspan="3">MACHINE UNIT</th>
                <th style="text-align:center;" width="150" rowspan="3">DESCRIPTION</th>
                <th style="text-align:center;" width="50" colspan="'.$colspan.'">QUANTITY NEED FROM WORKORDER</th>
                <th style="text-align:center;" width="100" rowspan="3">TOTAL</th>
            </tr>' . $cut . $date . $content;
        echo $html;
    }
}

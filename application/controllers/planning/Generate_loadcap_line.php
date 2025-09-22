<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_loadcap_line extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_loadcap_line');
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

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_shift = base64_decode($this->input->get('filter_shift'));
            $filter_overtime = base64_decode($this->input->get('filter_overtime'));
            $filter_process_main_number = base64_decode($this->input->get('filter_process_main_number'));
            $filter_process_sub_number = trim(base64_decode($this->input->get('filter_process_sub_number')));
            $period = $filter_year . "-" . $filter_month . "-01";

            if ($filter_process_main_number == "PP") {
                // Select Query
                $this->db->select('a.number as process_main_number, b.number as process_sub_number, a.name as process_main_name, b.name as process_sub_name, b.efficiency');
                $this->db->from('mst_process_main a');
                $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                $this->db->like('a.description', $filter_process_main_number);
                $this->db->like('b.number', $filter_process_sub_number);
                $this->db->where('b.deleted_is', 0);
                $this->db->group_by('b.number');
                $this->db->order_by('b.flag', 'asc');
                $records = $this->db->get()->result_array();

                $arr['total'] = @count($records);
                $array = array_merge($records, $arr);

                die(json_encode($array));
            } else {
                // Select Query
                $this->db->select('
                    a.process_main_number, 
                    b.name as process_main_name,
                    c.number as process_sub_number, 
                    c.name as process_sub_name, 
                    a.manpower,
                    a.efficiency');
                $this->db->from('mst_process_main b');
                $this->db->join('mst_process_sub c', 'b.number = c.process_main_number');
                $this->db->join('mst_capacity a', 'a.process_main_number = b.number and c.number = a.process_sub_number');
                $this->db->like('b.description', $filter_process_main_number);
                $this->db->like('c.number', $filter_process_sub_number);
                //$this->db->where('a.line_no', $filter_line_no);
                $this->db->where('a.deleted_is', 0);
                $this->db->group_by('c.number');
                $this->db->order_by('c.flag', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $data) {
                    $process_main_number = $data['process_main_number'];
                    $process_main_name = $data['process_main_name'];
                    $process_sub_number = $data['process_sub_number'];
                    $process_sub_name = $data['process_sub_name'];
                    $manpower = $data['manpower'];
                    $efficiency = $data['efficiency'];

                    $mst_umh = $this->db->query("SELECT process_sub_number, line_no, product_no, (SUM(cycle_time) / 3600) as total_time FROM mst_umh_havcp WHERE process_sub_number = '$process_sub_number' GROUP BY product_no");
                    $mst_umh_datas = $mst_umh->result_array();

                    $totalm1 = 0;
	                $totalm2 = 0;
	                $totalm3 = 0;
	                $totalm4 = 0;
	                $totalm5 = 0;
	                $totalm6 = 0;
                    foreach ($mst_umh_datas as $mst_umh_data) {
                        $product_no = $mst_umh_data['product_no'];
                        $total_time = $mst_umh_data['total_time'];

                        $generate_mps_detail = $this->db->query("SELECT ltpp_month, ltpp_month2, SUM(prod_plan) as prod_plan
                            FROM generate_mps_detail
                            WHERE product_no = '$product_no' and p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 
                            GROUP BY product_no, ltpp_month2 
                            ORDER BY product_no, ltpp_month2 asc");
                        $generate_mps_detail_data = $generate_mps_detail->result_array();

                        $totalm1 += @round(($generate_mps_detail_data[0]['prod_plan']) * $total_time);
                        $totalm2 += @round(($generate_mps_detail_data[1]['prod_plan']) * $total_time);
                        $totalm3 += @round(($generate_mps_detail_data[2]['prod_plan']) * $total_time);
                        $totalm4 += @round(($generate_mps_detail_data[3]['prod_plan']) * $total_time);
                        $totalm5 += @round(($generate_mps_detail_data[4]['prod_plan']) * $total_time);
                        $totalm6 += @round(($generate_mps_detail_data[5]['prod_plan']) * $total_time);

                        $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                        $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                        $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                        $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                        $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                        $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));

                        $hkw_1 = $this->checkCalendar($month_0);
                        $hkw_2 = $this->checkCalendar($month_1);
                        $hkw_3 = $this->checkCalendar($month_2);
                        $hkw_4 = $this->checkCalendar($month_3);
                        $hkw_5 = $this->checkCalendar($month_4);
                        $hkw_6 = $this->checkCalendar($month_5);

                        if ($filter_overtime != "0") {
                            $overtime = @((($manpower * $hkw_1) * 1) * $filter_overtime);
                        } else {
                            $overtime = 0;
                        }

                        $capacity1 = @((($manpower * $hkw_1 * 8) * 1 + $overtime) * ($efficiency / 100));
                        $capacity2 = @((($manpower * $hkw_2 * 8) * 1 + $overtime) * ($efficiency / 100));
                        $capacity3 = @((($manpower * $hkw_3 * 8) * 1 + $overtime) * ($efficiency / 100));
                        $capacity4 = @((($manpower * $hkw_4 * 8) * 1 + $overtime) * ($efficiency / 100));
                        $capacity5 = @((($manpower * $hkw_5 * 8) * 1 + $overtime) * ($efficiency / 100));
                        $capacity6 = @((($manpower * $hkw_6 * 8) * 1 + $overtime) * ($efficiency / 100));

                    }

                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "shift" => $filter_shift,
                        "overtime" => $filter_overtime,
                        "line_no" => $filter_line_no,
                        "process_main_number" => $process_main_number,
                        "process_main_name" => $process_main_name,
                        "process_sub_number" => $process_sub_number,
                        "process_sub_name" => $process_sub_name,
                        "machine_qty" => $manpower,
                        "efficiency" => $efficiency,
                        "month_1" => $totalm1,
                        "month_2" => $totalm2,
                        "month_3" => $totalm3,
                        "month_4" => $totalm4,
                        "month_5" => $totalm5,
                        "month_6" => $totalm6,
                        "capacity_1" => $capacity1,
                        "capacity_2" => $capacity2,
                        "capacity_3" => $capacity3,
                        "capacity_4" => $capacity4,
                        "capacity_5" => $capacity5,
                        "capacity_6" => $capacity6
                    );
                }

                $arr['total'] = @count($arr);
                die(json_encode($arr));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getData2()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_process_sub_number = base64_decode($this->input->get('filter_process_sub_number'));
            $filter_process_sub_name = base64_decode($this->input->get('filter_process_sub_name'));
            $filter_process_main_number = base64_decode($this->input->get('filter_process_main_number'));
            $filter_process_main_name = base64_decode($this->input->get('filter_process_main_name'));
            $filter_efficiency = base64_decode($this->input->get('filter_efficiency'));

            $this->db->select('b.line_no, a.product_no, a.product_name, b.remarks');
            $this->db->from('generate_mps a');
            $this->db->join('mst_line b', 'a.line_no = b.line_no');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            //$this->db->where('a.line_no', $filter_line_no);
            $this->db->where('a.deleted_is', 0);
            $this->db->group_by('a.product_no');
            $this->db->order_by('a.product_no', 'asc');
            $records2 = $this->db->get()->result_array();

            $arr['total'] = @count($records2);
            $array = array_merge($records2, $arr);

            die(json_encode($array));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getData3()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_shift = base64_decode($this->input->get('filter_shift'));
            $filter_overtime = base64_decode($this->input->get('filter_overtime'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_process_sub_number = base64_decode($this->input->get('filter_process_sub_number'));
            $filter_process_sub_name = base64_decode($this->input->get('filter_process_sub_name'));
            $filter_process_main_number = base64_decode($this->input->get('filter_process_main_number'));
            $filter_process_main_name = base64_decode($this->input->get('filter_process_main_name'));
            $filter_efficiency = base64_decode($this->input->get('filter_efficiency'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            $qMachine = $this->db->query("SELECT 
                        process_sub_number, 
                        COUNT(*) as total_machine 
                        FROM mst_machine 
                        WHERE process_sub_number = '$filter_process_sub_number' and status_is = '0'
                        GROUP BY process_sub_number");
            $dMachine = $qMachine->row();

            $qCapacity = $this->db->query("SELECT 
                        process_sub_number, 
                        manpower,
                        efficiency
                        FROM mst_capacity 
                        WHERE process_sub_number = '$filter_process_sub_number'
                        GROUP BY process_sub_number");
            $dCapacity = $qCapacity->row();

            if (!empty($dMachine->process_sub_number)) {
                $machine_qty = @$dMachine->total_machine;
                $efficiency = @$filter_efficiency;
            } else {
                $machine_qty = @$dCapacity->manpower;
                $efficiency = @$dCapacity->efficiency;
            }

            $query = $this->db->query("SELECT a.ltpp_month, a.ltpp_month2, a.prod_plan, (SUM(b.total_pp) / 3600) as umh, SUM(a.prod_plan) as prod_plan, 
                (SUM(a.prod_plan) * (SUM(b.total_pp) / 3600)) as total_umh 
                FROM generate_mps_detail a 
                JOIN (SELECT line_no, product_no, sum(cycle_time) as total_pp FROM mst_umh WHERE process_sub_number = '$filter_process_sub_number' GROUP BY product_no) b on a.product_no = b.product_no and a.line_no = b.line_no 
                WHERE a.product_no = '$filter_product_no' and a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision' 
                GROUP BY a.product_no, a.ltpp_month2 
                ORDER BY a.product_no, a.ltpp_month2 asc");
            $records3 = $query->result_array();

            if ($filter_overtime != "0") {
                $overtime = @((($record['machine_qty'] * $hkw_1) * $shift) * $filter_overtime);
            } else {
                $overtime = 0;
            }

            $totalm1 = @round($records3[0]['total_umh']);
            $totalm2 = @round($records3[1]['total_umh']);
            $totalm3 = @round($records3[2]['total_umh']);
            $totalm4 = @round($records3[3]['total_umh']);
            $totalm5 = @round($records3[4]['total_umh']);
            $totalm6 = @round($records3[5]['total_umh']);

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $hkw_1 = $this->checkCalendar($month_0);
            $hkw_2 = $this->checkCalendar($month_1);
            $hkw_3 = $this->checkCalendar($month_2);
            $hkw_4 = $this->checkCalendar($month_3);
            $hkw_5 = $this->checkCalendar($month_4);
            $hkw_6 = $this->checkCalendar($month_5);

            $capacity1 = @((($machine_qty * $hkw_1 * 8) * $filter_shift + $overtime) * ($efficiency / 100));
            $capacity2 = @((($machine_qty * $hkw_2 * 8) * $filter_shift + $overtime) * ($efficiency / 100));
            $capacity3 = @((($machine_qty * $hkw_3 * 8) * $filter_shift + $overtime) * ($efficiency / 100));
            $capacity4 = @((($machine_qty * $hkw_4 * 8) * $filter_shift + $overtime) * ($efficiency / 100));
            $capacity5 = @((($machine_qty * $hkw_5 * 8) * $filter_shift + $overtime) * ($efficiency / 100));
            $capacity6 = @((($machine_qty * $hkw_6 * 8) * $filter_shift + $overtime) * ($efficiency / 100));

            $arr = array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision,
                "shift" => $filter_shift,
                "overtime" => $filter_overtime,
                "line_no" => $filter_line_no,
                "process_main_number" => $filter_process_main_number,
                "process_main_name" => $filter_process_main_name,
                "process_sub_number" => $filter_process_sub_number,
                "process_sub_name" => $filter_process_sub_name,
                "machine_qty" => @$machine_qty,
                "efficiency" => $efficiency,
                "month_1" => $totalm1,
                "month_2" => $totalm2,
                "month_3" => $totalm3,
                "month_4" => $totalm4,
                "month_5" => $totalm5,
                "month_6" => $totalm6,
                "capacity_1" => $capacity1,
                "capacity_2" => $capacity2,
                "capacity_3" => $capacity3,
                "capacity_4" => $capacity4,
                "capacity_5" => $capacity5,
                "capacity_6" => $capacity6,
            );

            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $loadcap = $this->api->reads('generate_loadcap_line', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "line_no" => $post['line_no'],
                "process_sub_number" => $post['process_sub_number']
            ]);

            if (count($loadcap) > 0) {
                $send = $this->db->delete('generate_loadcap_line', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "line_no" => $post['line_no'],
                    "process_sub_number" => $post['process_sub_number']
                ]);

                $create = $this->api->create('generate_loadcap_line', $post, "LOADCAP", "LOADCAP");
                echo json_encode(array("title" => "Good Job", "message" => $post['process_sub_name'] . " Updated Successfully", "theme" => "success"));
            } else {
                $send   = $this->api->create('generate_loadcap_line', $post, "LOADCAP", "LOADCAP");
                echo json_encode(array("title" => "Good Job", "message" => $post['process_sub_name'] . " Saved Successfully", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/generate_loadcap_line.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/generate_loadcap_line.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/generate_loadcap_line.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=generate_loadcap_line_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_shift = base64_decode($this->input->get('filter_shift'));
            $filter_overtime = base64_decode($this->input->get('filter_overtime'));
            $filter_process_main_number = base64_decode($this->input->get('filter_process_main_number'));
            $filter_process_sub_number = trim(base64_decode($this->input->get('filter_process_sub_number')));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $hkw_1 = $this->checkCalendar($month_0);
            $hkw_2 = $this->checkCalendar($month_1);
            $hkw_3 = $this->checkCalendar($month_2);
            $hkw_4 = $this->checkCalendar($month_3);
            $hkw_5 = $this->checkCalendar($month_4);
            $hkw_6 = $this->checkCalendar($month_5);

            //SELECT LINE
            //$line = $this->api->read('mst_line', [], ["line_no" => $filter_line_no]);

            // Select Query
            $this->db->select('a.*, c.mp_actual');
            $this->db->from('generate_loadcap_line a');
            $this->db->join('mst_process_main b', 'a.process_main_number = b.number');
            $this->db->join('mst_process_sub c', 'a.process_sub_number = c.number');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.shift', $filter_shift);
            $this->db->where('a.overtime', $filter_overtime);
            $this->db->where('b.description', $filter_process_main_number);
            $this->db->like('a.process_sub_number', $filter_process_sub_number);
            //$this->db->where('a.line_no', $filter_line_no);
            $this->db->where('a.deleted_is', 0);
            $this->db->group_by("a.process_sub_number");
            $this->db->order_by("c.flag", "asc");
            $records = $this->db->get()->result_array();

            if($filter_process_main_number == "PP"){

                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>LOADING CAPACITY PROCESS</small>
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
                PERIOD <b>' . $filter_year . '/' . $filter_month . '</b>
                REVISION <b>' . $filter_revision . '</b>
            </center>
            <br>
            <table id="customers" border="1">
                <tr>
                    <th style="text-align:center;" rowspan="2" width="20">No</th>
                    <th style="text-align:center;" rowspan="2">Main Process</th>
                    <th style="text-align:center;" rowspan="2">Main Sub Process</th>
                    <th style="text-align:center;" rowspan="2">Total<br>Unit</th>
                    <th style="text-align:center;" rowspan="2">MP Actual</th>
                    <th style="text-align:center;" rowspan="2">Efficiency %</th>
                    <th style="text-align:center;" rowspan="2">Description</th>
                    <th style="text-align:center;" colspan="6">MONTH</th>
                    <th style="text-align:center;" rowspan="2">Average</th>
                </tr>
                <tr>
                    <th style="text-align:center;">' . $month_0 . '</th>
                    <th style="text-align:center;">' . $month_1 . '</th>
                    <th style="text-align:center;">' . $month_2 . '</th>
                    <th style="text-align:center;">' . $month_3 . '</th>
                    <th style="text-align:center;">' . $month_4 . '</th>
                    <th style="text-align:center;">' . $month_5 . '</th>
                </tr>';

                $no = 1;
                foreach ($records as $record) {
                    $color1 = "";
                    $color2 = "";
                    $color3 = "";
                    $color4 = "";
                    $color5 = "";
                    $color6 = "";
                    $color7 = "";

                    $color_mc_1 = "";
                    $color_mc_2 = "";
                    $color_mc_3 = "";
                    $color_mc_4 = "";
                    $color_mc_5 = "";
                    $color_mc_6 = "";
                    $color_mc_7 = "";

                    $color_mp_1 = "";
                    $color_mp_2 = "";
                    $color_mp_3 = "";
                    $color_mp_4 = "";
                    $color_mp_5 = "";
                    $color_mp_6 = "";
                    $color_mp_7 = "";

                    $mst_capacity = $this->api->reads("mst_capacity", [], ["process_sub_number" => $record['process_sub_number']]);
                    if(count($mst_capacity) > 0){
                        $shift = 1;
                        $mp = 0;

                        $mp_req_1 = abs($record['month_1'] / (8 * $hkw_1)); 
                        $mp_req_2 = abs($record['month_2'] / (8 * $hkw_2));
                        $mp_req_3 = abs($record['month_3'] / (8 * $hkw_3));
                        $mp_req_4 = abs($record['month_4'] / (8 * $hkw_4));
                        $mp_req_5 = abs($record['month_5'] / (8 * $hkw_5));
                        $mp_req_6 = abs($record['month_6'] / (8 * $hkw_6));

                        $machine_req_1 = 0;
                        $machine_req_2 = 0;
                        $machine_req_3 = 0;
                        $machine_req_4 = 0;
                        $machine_req_5 = 0;
                        $machine_req_6 = 0;

                        $machine_bal_1 = 0;
                        $machine_bal_2 = 0;
                        $machine_bal_3 = 0;
                        $machine_bal_4 = 0;
                        $machine_bal_5 = 0;
                        $machine_bal_6 = 0;
                    }else{
                        $shift = $filter_shift;
                        $mp = 1;

                        // $machine_req_1 = floor($record['machine_qty'] + ($record['month_1'] / ($record['machine_qty'] * $shift * 8 * $hkw_1 * ($record['efficiency'] / 100))));
                        // $machine_req_2 = floor($record['machine_qty'] + ($record['month_2'] / ($record['machine_qty'] * $shift * 8 * $hkw_2 * ($record['efficiency'] / 100))));
                        // $machine_req_3 = floor($record['machine_qty'] + ($record['month_3'] / ($record['machine_qty'] * $shift * 8 * $hkw_3 * ($record['efficiency'] / 100))));
                        // $machine_req_4 = floor($record['machine_qty'] + ($record['month_4'] / ($record['machine_qty'] * $shift * 8 * $hkw_4 * ($record['efficiency'] / 100))));
                        // $machine_req_5 = floor($record['machine_qty'] + ($record['month_5'] / ($record['machine_qty'] * $shift * 8 * $hkw_5 * ($record['efficiency'] / 100))));
                        // $machine_req_6 = floor($record['machine_qty'] + ($record['month_6'] / ($record['machine_qty'] * $shift * 8 * $hkw_6 * ($record['efficiency'] / 100))));
                        $machine_req_1 = round(($record['month_1'] / $hkw_1 / 8 / $shift) * ($record['efficiency'] / 100));
                        $machine_req_2 = round(($record['month_2'] / $hkw_2 / 8 / $shift) * ($record['efficiency'] / 100));
                        $machine_req_3 = round(($record['month_3'] / $hkw_3 / 8 / $shift) * ($record['efficiency'] / 100));
                        $machine_req_4 = round(($record['month_4'] / $hkw_4 / 8 / $shift) * ($record['efficiency'] / 100));
                        $machine_req_5 = round(($record['month_5'] / $hkw_5 / 8 / $shift) * ($record['efficiency'] / 100));
                        $machine_req_6 = round(($record['month_6'] / $hkw_6 / 8 / $shift) * ($record['efficiency'] / 100));

                        // $mp_req_1 = abs(($record['machine_qty'] - ($record['machine_qty'] + $machine_req_1)) * $shift); 
                        // $mp_req_2 = abs(($record['machine_qty'] - ($record['machine_qty'] + $machine_req_2)) * $shift);
                        // $mp_req_3 = abs(($record['machine_qty'] - ($record['machine_qty'] + $machine_req_3)) * $shift);
                        // $mp_req_4 = abs(($record['machine_qty'] - ($record['machine_qty'] + $machine_req_4)) * $shift);
                        // $mp_req_5 = abs(($record['machine_qty'] - ($record['machine_qty'] + $machine_req_5)) * $shift);
                        // $mp_req_6 = abs(($record['machine_qty'] - ($record['machine_qty'] + $machine_req_6)) * $shift);
                        $mp_req_1 = round(($record['month_1'] / $hkw_1 / 8));
                        $mp_req_2 = round(($record['month_2'] / $hkw_2 / 8));
                        $mp_req_3 = round(($record['month_3'] / $hkw_3 / 8));
                        $mp_req_4 = round(($record['month_4'] / $hkw_4 / 8));
                        $mp_req_5 = round(($record['month_5'] / $hkw_5 / 8));
                        $mp_req_6 = round(($record['month_6'] / $hkw_6 / 8));

                        $machine_bal_1 = ($record['machine_qty'] - $machine_req_1);
                        $machine_bal_2 = ($record['machine_qty'] - $machine_req_2);
                        $machine_bal_3 = ($record['machine_qty'] - $machine_req_3);
                        $machine_bal_4 = ($record['machine_qty'] - $machine_req_4);
                        $machine_bal_5 = ($record['machine_qty'] - $machine_req_5);
                        $machine_bal_6 = ($record['machine_qty'] - $machine_req_6);

                        if ($machine_bal_1 < 0) {
                            $color_mc_1 = "color:red;";
                        }
                        if ($machine_bal_2 < 0) {
                            $color_mc_2 = "color:red;";
                        }
                        if ($machine_bal_3 < 0) {
                            $color_mc_3 = "color:red;";
                        }
                        if ($machine_bal_4 < 0) {
                            $color_mc_4 = "color:red;";
                        }
                        if ($machine_bal_5 < 0) {
                            $color_mc_5 = "color:red;";
                        }
                        if ($machine_bal_6 < 0) {
                            $color_mc_6 = "color:red;";
                        }
                    }

                    if (($record['capacity_1'] - $record['month_1']) < 0) {
                        $color1 = "color:red;";
                    }
                    if (($record['capacity_2'] - $record['month_2']) < 0) {
                        $color2 = "color:red;";
                    }
                    if (($record['capacity_3'] - $record['month_3']) < 0) {
                        $color3 = "color:red;";
                    }
                    if (($record['capacity_4'] - $record['month_4']) < 0) {
                        $color4 = "color:red;";
                    }
                    if (($record['capacity_5'] - $record['month_5']) < 0) {
                        $color5 = "color:red;";
                    }
                    if (($record['capacity_6'] - $record['month_6']) < 0) {
                        $color6 = "color:red;";
                    }

                    if(((($record['capacity_1'] - $record['month_1']) +
                    ($record['capacity_2'] - $record['month_2']) +
                    ($record['capacity_3'] - $record['month_3']) +
                    ($record['capacity_4'] - $record['month_4']) +
                    ($record['capacity_5'] - $record['month_5']) +
                    ($record['capacity_6'] - $record['month_6'])) / 6) < 0){
                        $color7 = "color:red;";
                    }

                    $machine1 = (((1 * $hkw_1 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine2 = (((1 * $hkw_2 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine3 = (((1 * $hkw_3 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine4 = (((1 * $hkw_4 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine5 = (((1 * $hkw_5 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine6 = (((1 * $hkw_6 * 8) * $filter_shift) * ($record['efficiency'] / 100));

                    $manpower1 = (((1 * $hkw_1 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower2 = (((1 * $hkw_2 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower3 = (((1 * $hkw_3 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower4 = (((1 * $hkw_4 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower5 = (((1 * $hkw_5 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower6 = (((1 * $hkw_6 * 8) * 1) * ($record['efficiency'] / 100));

                    $balance_1 = ($record['capacity_1'] - $record['month_1']);
                    $balance_2 = ($record['capacity_2'] - $record['month_2']);
                    $balance_3 = ($record['capacity_3'] - $record['month_3']);
                    $balance_4 = ($record['capacity_4'] - $record['month_4']);
                    $balance_5 = ($record['capacity_5'] - $record['month_5']);
                    $balance_6 = ($record['capacity_6'] - $record['month_6']);

                    $mp_bal_1 = ($record['mp_actual'] - $mp_req_1);
                    $mp_bal_2 = ($record['mp_actual'] - $mp_req_2);
                    $mp_bal_3 = ($record['mp_actual'] - $mp_req_3);
                    $mp_bal_4 = ($record['mp_actual'] - $mp_req_4);
                    $mp_bal_5 = ($record['mp_actual'] - $mp_req_5);
                    $mp_bal_6 = ($record['mp_actual'] - $mp_req_6);

                    if ($machine_bal_1 <= 0 && $mp_bal_1 >= 0) {
                        $f_mp_bal_1 = $mp_bal_1;
                    }else{
                        $f_mp_bal_1 = $mp_bal_1;
                    }

                    if ($machine_bal_2 <= 0 && $mp_bal_2 >= 0) {
                        $f_mp_bal_2 = $mp_bal_2;
                    }else{
                        $f_mp_bal_2 = $mp_bal_2;
                    }

                    if ($machine_bal_3 <= 0 && $mp_bal_3 >= 0) {
                        $f_mp_bal_3 = $mp_bal_3;
                    }else{
                        $f_mp_bal_3 = $mp_bal_3;
                    }

                    if ($machine_bal_4 <= 0 && $mp_bal_4 >= 0) {
                        $f_mp_bal_4 = $mp_bal_4;
                    }else{
                        $f_mp_bal_4 = $mp_bal_4;
                    }

                    if ($machine_bal_5 <= 0 && $mp_bal_5 >= 0) {
                        $f_mp_bal_5 = $mp_bal_5;
                    }else{
                        $f_mp_bal_5 = $mp_bal_5;
                    }

                    if ($machine_bal_6 <= 0 && $mp_bal_6 >= 0) {
                        $f_mp_bal_6 = $mp_bal_6;
                    }else{
                        $f_mp_bal_6 = $mp_bal_6;
                    }

                    if((($machine_bal_1 + $machine_bal_2 + $machine_bal_3 + $machine_bal_4 + $machine_bal_5 + $machine_bal_6) / 6) <= 0 && (($mp_bal_1 + $mp_bal_2 + $mp_bal_3 + $mp_bal_4 + $mp_bal_5 + $mp_bal_6) / 6) >= 0){
                        $f_mp_bal_7 = (($mp_bal_1 + $mp_bal_2 + $mp_bal_3 + $mp_bal_4 + $mp_bal_5 + $mp_bal_6) / 6);
                    }else{
                        $f_mp_bal_7 = (($mp_bal_1 + $mp_bal_2 + $mp_bal_3 + $mp_bal_4 + $mp_bal_5 + $mp_bal_6) / 6);
                    }

                    if ($f_mp_bal_1 <= 0) {
                        $color_mp_1 = "color:red;";
                    }
                    if ($f_mp_bal_2 <= 0) {
                        $color_mp_2 = "color:red;";
                    }
                    if ($f_mp_bal_3 <= 0) {
                        $color_mp_3 = "color:red;";
                    }
                    if ($f_mp_bal_4 <= 0) {
                        $color_mp_4 = "color:red;";
                    }
                    if ($f_mp_bal_5 <= 0) {
                        $color_mp_5 = "color:red;";
                    }
                    if ($f_mp_bal_6 <= 0) {
                        $color_mp_6 = "color:red;";
                    }
                    if ($f_mp_bal_7 <= 0) {
                        $color_mp_7 = "color:red;";
                    }

                    $html .= '  <tr>
                                    <td rowspan="9" style="text-align:center;">' . $no . '</td>
                                    <td rowspan="9" style="text-align:center;">' . $record['process_main_name'] . '</td>
                                    <td rowspan="9">' . $record['process_sub_name'] . '</td>
                                    <td rowspan="9" style="text-align:center;">' . $record['machine_qty'] . '</td>
                                    <td rowspan="9" style="text-align:center;">' . $record['mp_actual'] . '</td>
                                    <td rowspan="9" style="text-align:center;">' . $record['efficiency'] . '</td>
                                </tr>
                                <tr>
                                    <td>LOADING</td>
                                    <td style="text-align:right;">' . number_format($record['month_1'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_2'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_3'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_4'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_5'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_6'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format((
                                        $record['month_1'] + 
                                        $record['month_2'] + 
                                        $record['month_3'] +
                                        $record['month_4'] +
                                        $record['month_5'] +
                                        $record['month_6']) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>TOTAL CAPACITY</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_1'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_2'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_3'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_4'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_5'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_6'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format(((
                                        $record['capacity_1'] + 
                                        $record['capacity_2'] + 
                                        $record['capacity_3'] +
                                        $record['capacity_4'] +
                                        $record['capacity_5'] +
                                        $record['capacity_6']) / 6), 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>BALANCE</td>
                                    <td style="text-align:right;' . $color1 . '">' . number_format($record['capacity_1'] - $record['month_1'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color2 . '">' . number_format($record['capacity_2'] - $record['month_2'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color3 . '">' . number_format($record['capacity_3'] - $record['month_3'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color4 . '">' . number_format($record['capacity_4'] - $record['month_4'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color5 . '">' . number_format($record['capacity_5'] - $record['month_5'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color6 . '">' . number_format($record['capacity_6'] - $record['month_6'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color7 . '">' . number_format((
                                        ($record['capacity_1'] - $record['month_1']) +
                                        ($record['capacity_2'] - $record['month_2']) +
                                        ($record['capacity_3'] - $record['month_3']) +
                                        ($record['capacity_4'] - $record['month_4']) +
                                        ($record['capacity_5'] - $record['month_5']) +
                                        ($record['capacity_6'] - $record['month_6'])) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>BALANCE %</td>
                                    <td style="text-align:right;' . $color1 . '">' . number_format(((($record['capacity_1'] - $record['month_1']) / $record['capacity_1']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color2 . '">' . number_format(((($record['capacity_2'] - $record['month_2']) / $record['capacity_2']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color3 . '">' . number_format(((($record['capacity_3'] - $record['month_3']) / $record['capacity_3']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color4 . '">' . number_format(((($record['capacity_4'] - $record['month_4']) / $record['capacity_4']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color5 . '">' . number_format(((($record['capacity_5'] - $record['month_5']) / $record['capacity_5']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color6 . '">' . number_format(((($record['capacity_6'] - $record['month_6']) / $record['capacity_6']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color7 . '">' . number_format((
                                        ((($record['capacity_1'] - $record['month_1']) / $record['capacity_1']) * 100) +
                                        ((($record['capacity_2'] - $record['month_2']) / $record['capacity_2']) * 100) +
                                        ((($record['capacity_3'] - $record['month_3']) / $record['capacity_3']) * 100) +
                                        ((($record['capacity_4'] - $record['month_4']) / $record['capacity_4']) * 100) +
                                        ((($record['capacity_5'] - $record['month_5']) / $record['capacity_5']) * 100) +
                                        ((($record['capacity_6'] - $record['month_6']) / $record['capacity_6']) * 100)) / 6, 1, ',', '.') . ' %</td>
                                </tr>
                                <tr>
                                    <td>MACHINE REQUIREMENT</td>
                                    <td style="text-align:right;">' . number_format($machine_req_1, 1, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($machine_req_2, 1, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($machine_req_3, 1, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($machine_req_4, 1, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($machine_req_5, 1, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($machine_req_6, 1, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format((
                                        $machine_req_1 + 
                                        $machine_req_2 +
                                        $machine_req_3 +
                                        $machine_req_4 +
                                        $machine_req_5 +
                                        $machine_req_6) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>MACHINE BALANCE</td>
                                    <td style="text-align:right;' . $color_mc_1 . '">' . number_format($machine_bal_1, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color_mc_2 . '">' . number_format($machine_bal_2, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color_mc_3 . '">' . number_format($machine_bal_3, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color_mc_4 . '">' . number_format($machine_bal_4, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color_mc_5 . '">' . number_format($machine_bal_5, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color_mc_6 . '">' . number_format($machine_bal_6, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color_mc_1 . '">' . number_format((
                                        $machine_bal_1 + 
                                        $machine_bal_2 +
                                        $machine_bal_3 +
                                        $machine_bal_4 +
                                        $machine_bal_5 +
                                        $machine_bal_6) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>MAN POWER REQUIREMENT</td>
                                    <td style="text-align:right;">' . number_format($mp_req_1, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_2, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_3, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_4, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_5, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_6, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format((
                                        $mp_req_1 + 
                                        $mp_req_2 +
                                        $mp_req_3 + 
                                        $mp_req_4 + 
                                        $mp_req_5 +
                                        $mp_req_6) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>MAN POWER BALANCE</td>
                                    <td style="text-align:right;' . $color_mp_1 . '">' . number_format($f_mp_bal_1) . '</td>
                                    <td style="text-align:right;' . $color_mp_2 . '">' . number_format($f_mp_bal_2) . '</td>
                                    <td style="text-align:right;' . $color_mp_3 . '">' . number_format($f_mp_bal_3) . '</td>
                                    <td style="text-align:right;' . $color_mp_4 . '">' . number_format($f_mp_bal_4) . '</td>
                                    <td style="text-align:right;' . $color_mp_5 . '">' . number_format($f_mp_bal_5) . '</td>
                                    <td style="text-align:right;' . $color_mp_6 . '">' . number_format($f_mp_bal_6) . '</td>
                                    <td style="text-align:right;' . $color_mp_7 . '">' . number_format($f_mp_bal_7) . '</td>
                                </tr>';
                    $no++;
                }
            }else{
                $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>LOADING CAPACITY PROCESS</small>
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
                PERIOD <b>' . $filter_year . '/' . $filter_month . '</b>
                REVISION <b>' . $filter_revision . '</b>
            </center>
            <br>
            <table id="customers" border="1">
                <tr>
                    <th style="text-align:center;" rowspan="2" width="20">No</th>
                    <th style="text-align:center;" rowspan="2">Main Process</th>
                    <th style="text-align:center;" rowspan="2">Main Sub Process</th>
                    <th style="text-align:center;" rowspan="2">MP Actual</th>
                    <th style="text-align:center;" rowspan="2">Efficiency %</th>
                    <th style="text-align:center;" rowspan="2">Description</th>
                    <th style="text-align:center;" colspan="6">MONTH</th>
                    <th style="text-align:center;" rowspan="2">Average</th>
                </tr>
                <tr>
                    <th style="text-align:center;">' . $month_0 . '</th>
                    <th style="text-align:center;">' . $month_1 . '</th>
                    <th style="text-align:center;">' . $month_2 . '</th>
                    <th style="text-align:center;">' . $month_3 . '</th>
                    <th style="text-align:center;">' . $month_4 . '</th>
                    <th style="text-align:center;">' . $month_5 . '</th>
                </tr>';

                $no = 1;
                foreach ($records as $record) {
                    $color1 = "";
                    $color2 = "";
                    $color3 = "";
                    $color4 = "";
                    $color5 = "";
                    $color6 = "";
                    $color7 = "";
                    $colorMp1 = "";
                    $colorMp2 = "";
                    $colorMp3 = "";
                    $colorMp4 = "";
                    $colorMp5 = "";
                    $colorMp6 = "";
                    $colorMp7 = "";

                    $mst_capacity = $this->api->reads("mst_capacity", [], ["process_sub_number" => $record['process_sub_number']]);
                    if(count($mst_capacity) > 0){
                        $shift = 1;
                        $mp = 0;

                        $mp_req_1 = abs($record['month_1'] / (8 * $hkw_1)); 
                        $mp_req_2 = abs($record['month_2'] / (8 * $hkw_2));
                        $mp_req_3 = abs($record['month_3'] / (8 * $hkw_3));
                        $mp_req_4 = abs($record['month_4'] / (8 * $hkw_4));
                        $mp_req_5 = abs($record['month_5'] / (8 * $hkw_5));
                        $mp_req_6 = abs($record['month_6'] / (8 * $hkw_6));
                    }else{
                        $shift = $filter_shift;
                        $mp = 1;

                        $mp_req_1 = abs($record['mp_actual'] * $shift); 
                        $mp_req_2 = abs($record['mp_actual'] * $shift);
                        $mp_req_3 = abs($record['mp_actual'] * $shift);
                        $mp_req_4 = abs($record['mp_actual'] * $shift);
                        $mp_req_5 = abs($record['mp_actual'] * $shift);
                        $mp_req_6 = abs($record['mp_actual'] * $shift);
                    }

                    if (($record['capacity_1'] - $record['month_1']) < 0) {
                        $color1 = "color:red;";
                    }
                    if (($record['capacity_2'] - $record['month_2']) < 0) {
                        $color2 = "color:red;";
                    }
                    if (($record['capacity_3'] - $record['month_3']) < 0) {
                        $color3 = "color:red;";
                    }
                    if (($record['capacity_4'] - $record['month_4']) < 0) {
                        $color4 = "color:red;";
                    }
                    if (($record['capacity_5'] - $record['month_5']) < 0) {
                        $color5 = "color:red;";
                    }
                    if (($record['capacity_6'] - $record['month_6']) < 0) {
                        $color6 = "color:red;";
                    }

                    if(((($record['capacity_1'] - $record['month_1']) +
                    ($record['capacity_2'] - $record['month_2']) +
                    ($record['capacity_3'] - $record['month_3']) +
                    ($record['capacity_4'] - $record['month_4']) +
                    ($record['capacity_5'] - $record['month_5']) +
                    ($record['capacity_6'] - $record['month_6'])) / 6) < 0){
                        $color7 = "color:red;";
                    }

                    $machine1 = (((1 * $hkw_1 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine2 = (((1 * $hkw_2 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine3 = (((1 * $hkw_3 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine4 = (((1 * $hkw_4 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine5 = (((1 * $hkw_5 * 8) * $filter_shift) * ($record['efficiency'] / 100));
                    $machine6 = (((1 * $hkw_6 * 8) * $filter_shift) * ($record['efficiency'] / 100));

                    $manpower1 = (((1 * $hkw_1 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower2 = (((1 * $hkw_2 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower3 = (((1 * $hkw_3 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower4 = (((1 * $hkw_4 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower5 = (((1 * $hkw_5 * 8) * 1) * ($record['efficiency'] / 100));
                    $manpower6 = (((1 * $hkw_6 * 8) * 1) * ($record['efficiency'] / 100));

                    $balance_1 = ($record['capacity_1'] - $record['month_1']);
                    $balance_2 = ($record['capacity_2'] - $record['month_2']);
                    $balance_3 = ($record['capacity_3'] - $record['month_3']);
                    $balance_4 = ($record['capacity_4'] - $record['month_4']);
                    $balance_5 = ($record['capacity_5'] - $record['month_5']);
                    $balance_6 = ($record['capacity_6'] - $record['month_6']);

                    if($balance_1 > 0){ $machine_req_1 = 0; }else{ $machine_req_1 = abs($balance_1 / $machine1) * $mp; }
                    if($balance_2 > 0){ $machine_req_2 = 0; }else{ $machine_req_2 = abs($balance_2 / $machine2) * $mp; }
                    if($balance_3 > 0){ $machine_req_3 = 0; }else{ $machine_req_3 = abs($balance_3 / $machine3) * $mp; }
                    if($balance_4 > 0){ $machine_req_4 = 0; }else{ $machine_req_4 = abs($balance_4 / $machine4) * $mp; }
                    if($balance_5 > 0){ $machine_req_5 = 0; }else{ $machine_req_5 = abs($balance_5 / $machine5) * $mp; }
                    if($balance_6 > 0){ $machine_req_6 = 0; }else{ $machine_req_6 = abs($balance_6 / $machine6) * $mp; }

                    $mp_bal_1 = ($record['mp_actual'] - $mp_req_1);
                    $mp_bal_2 = ($record['mp_actual'] - $mp_req_2);
                    $mp_bal_3 = ($record['mp_actual'] - $mp_req_3);
                    $mp_bal_4 = ($record['mp_actual'] - $mp_req_4);
                    $mp_bal_5 = ($record['mp_actual'] - $mp_req_5);
                    $mp_bal_6 = ($record['mp_actual'] - $mp_req_6);

                    if ($mp_bal_1 < 0) {
                        $colorMp1 = "color:red;";
                    }

                    if ($mp_bal_2 < 0) {
                        $colorMp2 = "color:red;";
                    }

                    if ($mp_bal_3 < 0) {
                        $colorMp3 = "color:red;";
                    }

                    if ($mp_bal_4 < 0) {
                        $colorMp4 = "color:red;";
                    }

                    if ($mp_bal_5 < 0) {
                        $colorMp5 = "color:red;";
                    }

                    if ($mp_bal_6 < 0) {
                        $colorMp6 = "color:red;";
                    }

                    if((($mp_bal_1 + $mp_bal_2 + $mp_bal_3 + $mp_bal_4 + $mp_bal_5 + $mp_bal_6) / 6) < 0){
                        $colorMp7 = "color:red;";
                    }

                    $html .= '  <tr>
                                    <td rowspan="7" style="text-align:center;">' . $no . '</td>
                                    <td rowspan="7" style="text-align:center;">' . $record['process_main_name'] . '</td>
                                    <td rowspan="7">' . $record['process_sub_name'] . '</td>
                                    <td rowspan="7" style="text-align:center;">' . $record['mp_actual'] . '</td>
                                    <td rowspan="7" style="text-align:center;">' . $record['efficiency'] . '</td>
                                </tr>
                                <tr>
                                    <td>LOADING</td>
                                    <td style="text-align:right;">' . number_format($record['month_1'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_2'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_3'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_4'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_5'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['month_6'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format((
                                        $record['month_1'] + 
                                        $record['month_2'] + 
                                        $record['month_3'] +
                                        $record['month_4'] +
                                        $record['month_5'] +
                                        $record['month_6']) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>TOTAL CAPACITY</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_1'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_2'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_3'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_4'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_5'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($record['capacity_6'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format(((
                                        $record['capacity_1'] + 
                                        $record['capacity_2'] + 
                                        $record['capacity_3'] +
                                        $record['capacity_4'] +
                                        $record['capacity_5'] +
                                        $record['capacity_6']) / 6), 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>BALANCE</td>
                                    <td style="text-align:right;' . $color1 . '">' . number_format($record['capacity_1'] - $record['month_1'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color2 . '">' . number_format($record['capacity_2'] - $record['month_2'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color3 . '">' . number_format($record['capacity_3'] - $record['month_3'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color4 . '">' . number_format($record['capacity_4'] - $record['month_4'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color5 . '">' . number_format($record['capacity_5'] - $record['month_5'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color6 . '">' . number_format($record['capacity_6'] - $record['month_6'], 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $color7 . '">' . number_format((
                                        ($record['capacity_1'] - $record['month_1']) +
                                        ($record['capacity_2'] - $record['month_2']) +
                                        ($record['capacity_3'] - $record['month_3']) +
                                        ($record['capacity_4'] - $record['month_4']) +
                                        ($record['capacity_5'] - $record['month_5']) +
                                        ($record['capacity_6'] - $record['month_6'])) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>BALANCE %</td>
                                    <td style="text-align:right;' . $color1 . '">' . number_format(((($record['capacity_1'] - $record['month_1']) / $record['capacity_1']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color2 . '">' . number_format(((($record['capacity_2'] - $record['month_2']) / $record['capacity_2']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color3 . '">' . number_format(((($record['capacity_3'] - $record['month_3']) / $record['capacity_3']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color4 . '">' . number_format(((($record['capacity_4'] - $record['month_4']) / $record['capacity_4']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color5 . '">' . number_format(((($record['capacity_5'] - $record['month_5']) / $record['capacity_5']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color6 . '">' . number_format(((($record['capacity_6'] - $record['month_6']) / $record['capacity_6']) * 100), 1, ',', '.') . ' %</td>
                                    <td style="text-align:right;' . $color7 . '">' . number_format((
                                        ((($record['capacity_1'] - $record['month_1']) / $record['capacity_1']) * 100) +
                                        ((($record['capacity_2'] - $record['month_2']) / $record['capacity_2']) * 100) +
                                        ((($record['capacity_3'] - $record['month_3']) / $record['capacity_3']) * 100) +
                                        ((($record['capacity_4'] - $record['month_4']) / $record['capacity_4']) * 100) +
                                        ((($record['capacity_5'] - $record['month_5']) / $record['capacity_5']) * 100) +
                                        ((($record['capacity_6'] - $record['month_6']) / $record['capacity_6']) * 100)) / 6, 1, ',', '.') . ' %</td>
                                </tr>
                                <tr>
                                    <td>MAN POWER REQUIREMENT</td>
                                    <td style="text-align:right;">' . number_format($mp_req_1, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_2, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_3, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_4, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_5, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format($mp_req_6, 0, ',', '.') . '</td>
                                    <td style="text-align:right;">' . number_format((
                                        $mp_req_1 + 
                                        $mp_req_2 +
                                        $mp_req_3 + 
                                        $mp_req_4 + 
                                        $mp_req_5 +
                                        $mp_req_6) / 6, 0, ',', '.') . '</td>
                                </tr>
                                <tr>
                                    <td>MAN POWER BALANCE</td>
                                    <td style="text-align:right;' . $colorMp1 . '">' . number_format($mp_bal_1, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $colorMp2 . '">' . number_format($mp_bal_2, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $colorMp3 . '">' . number_format($mp_bal_3, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $colorMp4 . '">' . number_format($mp_bal_4, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $colorMp5 . '">' . number_format($mp_bal_5, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $colorMp6 . '">' . number_format($mp_bal_6, 0, ',', '.') . '</td>
                                    <td style="text-align:right;' . $colorMp7 . '">' . number_format((
                                        $mp_bal_1 + 
                                        $mp_bal_2 +
                                        $mp_bal_3 + 
                                        $mp_bal_4 + 
                                        $mp_bal_5 +
                                        $mp_bal_6) / 6, 0, ',', '.') . '</td>
                                </tr>';
                    $no++;
                }
            }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}

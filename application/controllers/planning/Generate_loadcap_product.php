<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_loadcap_product extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');

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
            $this->load->view('planning/generate_loadcap_product');
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

            // Select Query
            $this->db->select('b.line_no, a.product_no, a.product_name, b.remarks');
            $this->db->from('generate_mps a');
            $this->db->join('mst_line b', 'a.line_no = b.line_no');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.line_no', $filter_line_no);
            $this->db->where('a.deleted_is', 0);
            $this->db->group_by('a.product_no');
            $this->db->order_by('a.product_no', 'asc');
            $records = $this->db->get()->result_array();

            foreach ($records as $data) {
                $product_no = $data['product_no'];
                $line_no = $data['line_no'];
                $query = $this->db->query("SELECT a.ltpp_month, a.ltpp_month2, a.prod_plan, (b.total_pp / 3600) as umh, (SUM(a.prod_plan) * (b.total_pp / 3600)) as total_umh
                    FROM generate_mps_detail a
                    JOIN (SELECT line_no, product_no, sum(cycle_time) as total_pp FROM mst_umh WHERE process_sub_number IN ('CUT100','CUT200','CUT300','CUT400','CUT500') GROUP BY product_no) b on a.product_no = b.product_no and a.line_no = b.line_no
                    WHERE a.product_no = '$product_no' and a.line_no = '$line_no' and a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision'
                    GROUP BY a.product_no, a.ltpp_month2
                    ORDER BY a.product_no, a.ltpp_month2 asc");
                $details = $query->result_array();

                if (@$details[0]['umh'] > 0.00000) {
                    $status = "OK";
                } else {
                    $status = "NG";
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "line_no" => $filter_line_no,
                    "line_name" => $data['remarks'],
                    "product_no" => $data['product_no'],
                    "product_name" => $data['product_name'],
                    "month_1_prodplan" => @$details[0]['prod_plan'],
                    "month_1_umh" => @$details[0]['umh'],
                    "month_1_total" => @$details[0]['total_umh'],
                    "month_2_prodplan" => @$details[1]['prod_plan'],
                    "month_2_umh" => @$details[1]['umh'],
                    "month_2_total" => @$details[1]['total_umh'],
                    "month_3_prodplan" => @$details[2]['prod_plan'],
                    "month_3_umh" => @$details[2]['umh'],
                    "month_3_total" => @$details[2]['total_umh'],
                    "month_4_prodplan" => @$details[3]['prod_plan'],
                    "month_4_umh" => @$details[3]['umh'],
                    "month_4_total" => @$details[3]['total_umh'],
                    "month_5_prodplan" => @$details[4]['prod_plan'],
                    "month_5_umh" => @$details[4]['umh'],
                    "month_5_total" => @$details[4]['total_umh'],
                    "month_6_prodplan" => @$details[5]['prod_plan'],
                    "month_6_umh" => @$details[5]['umh'],
                    "month_6_total" => @$details[5]['total_umh'],
                    "status" => $status,
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $loadcap = $this->api->reads('generate_loadcap_product', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "line_no" => $post['line_no'],
                "product_no" => $post['product_no']
            ]);

            if (count($loadcap) > 0) {
                $send   = $this->api->update('generate_loadcap_product', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "line_no" => $post['line_no'],
                    "product_no" => $post['product_no'],
                ], $post);
                echo json_encode(array("title" => "Good Job", "message" => $post['product_no'] . " Updated Successfully", "theme" => "success"));
            } else {
                $send   = $this->api->create('generate_loadcap_product', $post, "LOADCAP", "LOADCAP");
                echo json_encode(array("title" => "Good Job", "message" => $post['product_no'] . " Saved Successfully", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=mst_forecast_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            // Select Query
            $this->db->select('*');
            $this->db->from('generate_loadcap_product');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('p_month', $filter_month);
                $this->db->where('p_year', $filter_year);
            }
            $this->db->like('revision', $filter_revision);
            $this->db->like('line_no', $filter_line_no);
            $this->db->where('deleted_is', 0);
            $this->db->order_by('product_no', 'desc');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 120%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
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
            </style><body>
            <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>LOADING CAPACITY PRODUCT</small>
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
            <h3 style="margin:0;"><u>' . strtoupper(@$records[0]['line_name']) . '</u></h3>
            PERIOD <b>' . $filter_year . '/' . $filter_month . '</b>
            REVISION <b>' . $filter_revision . '</b>
        </center>
        <br>
        <div style="width:150px; float:left;">
            <div class="box-green">
                <div style="width:200px;"><span style="margin-left:20px;">Available in UMH</span></div>
            </div>
        </div> 
        <div style="width:150px; float:left;">
            <div class="box-red">
                <div style="width:200px;"><span style="margin-left:20px;">Not Available in UMH</span></div> 
            </div>
        </div>
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">No</th>
                <th style="text-align:center;" rowspan="3">PRODUCT NO</th>
                <th style="text-align:center;" rowspan="3">PRODUCT NAME</th>
                <th style="text-align:center;" rowspan="3">CCT</th>
                <th style="text-align:center;" rowspan="3">UMH<br>CUTTING</th>
                <th style="text-align:center;" colspan="12">MONTH</th>
                <th style="text-align:center;" rowspan="3">TOTAL</th>
                <th style="text-align:center;" rowspan="3">AVERAGE</th>
            </tr>
            <tr>
                <th style="text-align:center;" colspan="2">' . $month_0 . '</th>
                <th style="text-align:center;" colspan="2">' . $month_1 . '</th>
                <th style="text-align:center;" colspan="2">' . $month_2 . '</th>
                <th style="text-align:center;" colspan="2">' . $month_3 . '</th>
                <th style="text-align:center;" colspan="2">' . $month_4 . '</th>
                <th style="text-align:center;" colspan="2">' . $month_5 . '</th>
            </tr>
            <tr>
                <th style="text-align:center;">PROD PLAN</th>
                <th style="text-align:center;">TOTAL UMH</th>
                <th style="text-align:center;">PROD PLAN</th>
                <th style="text-align:center;">TOTAL UMH</th>
                <th style="text-align:center;">PROD PLAN</th>
                <th style="text-align:center;">TOTAL UMH</th>
                <th style="text-align:center;">PROD PLAN</th>
                <th style="text-align:center;">TOTAL UMH</th>
                <th style="text-align:center;">PROD PLAN</th>
                <th style="text-align:center;">TOTAL UMH</th>
                <th style="text-align:center;">PROD PLAN</th>
                <th style="text-align:center;">TOTAL UMH</th>
            </tr>';

            $no = 1;
            $grandTotal = 0;
            $totalUmh_1 = 0;
            $totalUmh_2 = 0;
            $totalUmh_3 = 0;
            $totalUmh_4 = 0;
            $totalUmh_5 = 0;
            $totalUmh_6 = 0;
            foreach ($records as $record) {
                $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $record['product_no']]);

                if ($record['status'] == "OK") {
                    $background = "style='background-color: #D6FFCF;'";
                } else {
                    $background = "style='background-color: #FFCFCF;'";
                }

                $totalUmh_1 += $record['month_1_total'];
                $totalUmh_2 += $record['month_2_total'];
                $totalUmh_3 += $record['month_3_total'];
                $totalUmh_4 += $record['month_4_total'];
                $totalUmh_5 += $record['month_5_total'];
                $totalUmh_6 += $record['month_6_total'];
                $grandTotal += round($record['month_1_total'] + $record['month_2_total'] + $record['month_3_total'] + $record['month_4_total'] + $record['month_5_total'] + $record['month_6_total'], 2);
                $html .= '  <tr ' . $background . '>
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record['product_no'] . '</td>
                                <td>' . $record['product_name'] . '</td>
                                <td style="text-align:center;">' . @$cct->circuit_no . '</td>
                                <td style="text-align:center;">' . $record['month_1_umh'] . '</td>
                                <td style="text-align:right;">' . $record['month_1_prodplan'] . '</td>
                                <td style="text-align:right;">' . $record['month_1_total'] . '</td>
                                <td style="text-align:right;">' . $record['month_2_prodplan'] . '</td>
                                <td style="text-align:right;">' . $record['month_2_total'] . '</td>
                                <td style="text-align:right;">' . $record['month_3_prodplan'] . '</td>
                                <td style="text-align:right;">' . $record['month_3_total'] . '</td>
                                <td style="text-align:right;">' . $record['month_4_prodplan'] . '</td>
                                <td style="text-align:right;">' . $record['month_4_total'] . '</td>
                                <td style="text-align:right;">' . $record['month_5_prodplan'] . '</td>
                                <td style="text-align:right;">' . $record['month_5_total'] . '</td>
                                <td style="text-align:right;">' . $record['month_6_prodplan'] . '</td>
                                <td style="text-align:right;">' . $record['month_6_total'] . '</td>
                                <td style="text-align:right;">' . ($record['month_1_total'] + $record['month_2_total'] + $record['month_3_total'] + $record['month_4_total'] + $record['month_5_total'] + $record['month_6_total']) . '</td>
                                <td style="text-align:right;">' . round(($record['month_1_total'] + $record['month_2_total'] + $record['month_3_total'] + $record['month_4_total'] + $record['month_5_total'] + $record['month_6_total']) / 6, 2) . '</td>
                            </tr>';
                $no++;
            }
            $html .= '  <tr>
                            <th style="text-align:right;"></th>
                            <th style="text-align:right;"></th>
                            <th style="text-align:right;"></th>
                            <th style="text-align:right;"></th>
                            <th style="text-align:right;"></th>
                            <th style="text-align:right;">TOTAL UMH ' . $month_0 . '</th>
                            <th style="text-align:right;">' . $totalUmh_1 . '</th>
                            <th style="text-align:right;">TOTAL UMH ' . $month_1 . '</th>
                            <th style="text-align:right;">' . $totalUmh_2 . '</th>
                            <th style="text-align:right;">TOTAL UMH ' . $month_2 . '</th>
                            <th style="text-align:right;">' . $totalUmh_3 . '</th>
                            <th style="text-align:right;">TOTAL UMH ' . $month_3 . '</th>
                            <th style="text-align:right;">' . $totalUmh_4 . '</th>
                            <th style="text-align:right;">TOTAL UMH ' . $month_4 . '</th>
                            <th style="text-align:right;">' . $totalUmh_5 . '</th>
                            <th style="text-align:right;">TOTAL UMH ' . $month_5 . '</th>
                            <th style="text-align:right;">' . $totalUmh_6 . '</th>
                            <th style="text-align:right;">' . $grandTotal . '</th>
                            <th style="text-align:right;"></th>
                        </tr>';

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}

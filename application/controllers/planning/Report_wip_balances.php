<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Report_wip_balances extends CI_Controller
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
            $this->load->view('planning/report_wip_balances');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_wip_balances_$format.xls");
        }

        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));

            $period_start = date("Y-m-d", strtotime($filter_year."-".$filter_month."-01"));
            $period_finish = date("Y-m-d", strtotime("+31 day", strtotime($filter_year."-".$filter_month."-01")));
            $period_start2 = date("Y-m-d", strtotime($filter_year."-".$filter_month."-01"));
            $period_finish2 = date("Y-m-d", strtotime("+31 day", strtotime($filter_year."-".$filter_month."-01")));
            $monthCurrent = date("F", strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
            
            // die($period_start);

            $header = "";
            $content = "";
            while (strtotime($period_start) < strtotime($period_finish)) {
                $monthName = date('d, D', strtotime($period_start));
                $header .= '<th colspan="3" style="text-align:center;">'.$monthName.'</th>';
                $content .= '<th style="text-align:center;">Need</th><th style="text-align:center;">Supply</th><th style="text-align:center;">Bal</th>';

                $period_start = date("Y-m-d", strtotime("+1 day", strtotime($period_start)));
            }

            $query = $this->db->query("SELECT * FROM mst_item_raw WHERE pfm_id = '$filter_product_family' limit 10");
            $records = $query->result_array();

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 250%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style>
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
                                        <small>REPORT WIP BALANCE</small><br><br>
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
                            <th rowspan="2" style="text-align:center;">Total WO <br>('.$monthCurrent.')</th>
                            <th rowspan="2" style="text-align:center;">Supply <br>('.$monthCurrent.')</th>
                            <th rowspan="2" style="text-align:center;">Current<br>WP</th>
                            <th rowspan="2" style="text-align:center;">Last<br>Supply</th>
                            '.$header.'
                        </tr>
                        <tr>
                            '.$content.'
                        </tr>';
            $no = 1;
            $content = "";
            foreach ($records as $record) {
                $item_id = $record['item_id'];

                while (strtotime($period_start2) < strtotime($period_finish2)) {
                    $monthName = date('d, D', strtotime($period_start2));
                    $content .= '<td style="text-align:center;"></td>
                                <td style="text-align:center;"></td>
                                <td style="text-align:center;"></td>';

                    $period_start2 = date("Y-m-d", strtotime("+1 day", strtotime($period_start2)));
                }

                $html .= '<tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td>' . $record['item_id'] . '</td>
                            <td>' . $record['item_name'] . '</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            '.$content.'
                        </tr>';
                $no++;
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}

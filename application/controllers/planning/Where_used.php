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

        if ($this->input->get()) {
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));

            $query = $this->db->query("SELECT a.component_id, d.item_name as component_name, a.item_id, b.item_name, a.qty, c.name as customer_name
                FROM mst_bom a
                LEFT JOIN mst_item b ON a.item_id = b.item_id
                LEFT JOIN mst_customer c ON b.id_customer = c.number
                LEFT JOIN mst_item_raw d ON a.component_id = d.item_id
                WHERE b.pfm_id = '06' and a.component_id like '%$filter_item_id%' ORDER BY a.component_id ASC");
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
                    <br><br><br>
                    <center>
                        <b style="font-size:16px;">WHERE USED</b><br><small>Part No '.$filter_item_id.'</small><br><br>
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
                        </tr>';
            $no = 1;
            foreach ($records as $record) {
                $html .= '<tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td>' . $record['component_id'] . '</td>
                            <td>' . $record['component_name'] . '</td>
                            <td>' . $record['item_id'] . '</td>
                            <td>' . $record['item_name'] . '</td>
                            <td>' . $record['qty'] . '</td>
                            <td>' . $record['customer_name'] . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';
        echo $html;
    }
}

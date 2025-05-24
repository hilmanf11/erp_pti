<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_umh_recap extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Machine Type', 'required|min_length[2]|max_length[30]|is_unique[mst_umh_recap.number]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_umh_recap');
        } else {
            redirect('error_access');
        }
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            //Select Query
            $records = $this->api->query("SELECT a.item_id, a.item_name, d.circuit_no,
            (CASE WHEN b.circuit_no is null or b.circuit_no = 0 THEN 'UNFILLED' ELSE 'FILLED' END) as pp,
            (CASE WHEN c.circuit_no is null or c.circuit_no = 0 THEN 'UNFILLED' ELSE 'FILLED' END) as havcp
            FROM mst_item a 
            LEFT JOIN (SELECT product_no, COUNT(product_no) as circuit_no FROM mst_umh GROUP BY product_no) b ON a.item_id = b.product_no 
            LEFT JOIN (SELECT product_no, COUNT(product_no) as circuit_no FROM mst_umh_havcp GROUP BY product_no) c ON a.item_id = c.product_no 
            LEFT JOIN wip_mst_wos_cct d ON a.item_id = d.mstwos_assyno
            WHERE a.id_customer = '$filter_customer' and a.item_id like '%$filter_product_no%'
            GROUP BY a.item_id, a.item_name");

            die(json_encode($records));
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_umh_recap_$format.xls");
        }

        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $records = $this->api->query("SELECT a.item_id, a.item_name, d.circuit_no,
        (CASE WHEN b.circuit_no is null or b.circuit_no = 0 THEN 'UNFILLED' ELSE 'FILLED' END) as pp,
        (CASE WHEN c.circuit_no is null or c.circuit_no = 0 THEN 'UNFILLED' ELSE 'FILLED' END) as havcp
        FROM mst_item a 
        LEFT JOIN (SELECT product_no, COUNT(product_no) as circuit_no FROM mst_umh GROUP BY product_no) b ON a.item_id = b.product_no 
        LEFT JOIN (SELECT product_no, COUNT(product_no) as circuit_no FROM mst_umh_havcp GROUP BY product_no) c ON a.item_id = c.product_no 
        LEFT JOIN wip_mst_wos_cct d ON a.item_id = d.mstwos_assyno
        WHERE a.id_customer = '$filter_customer' and a.item_id like '%$filter_product_no%'
        GROUP BY a.item_id, a.item_name");

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER UNIT MAN HOUR (RECAP)</small>
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
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>CCT</th>
                <th>PP</th>
                <th>HAVCP</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data->item_id . '</td>
                            <td>' . $data->item_name . '</td>
                            <td>' . $data->circuit_no . '</td>
                            <td>' . $data->pp . '</td>
                            <td>' . $data->havcp . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

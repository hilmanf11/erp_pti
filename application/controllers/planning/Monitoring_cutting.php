<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoring_cutting extends CI_Controller
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
            $this->load->view('planning/monitoring_cutting');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=monitoring_cutting_$format.xls");
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
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $firstDate = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));

        $this->db->select("a.*");
        $this->db->from('generate_mpp a');
        $this->db->join('mst_line b', 'a.line_no = b.line_no');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.product_no');
        $this->db->limit(2);
        $records = $this->db->get()->result_array();

        //Setting Header
        $styles = "";
        $wp = '<tr>';
        $wp_content = "";
        $cut = '<tr>';
        $cut_no = 0;
        $cut_content = "";
        $date = '<tr>';
        $content = '';
        $contenttest = '';
        $no = 1;

        while (strtotime($firstDate) <= strtotime($endDate)) {
            if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                $styles = "";
                $wp_content = "W/P";
                $cut_no += 1;
                $cut_content = $cut_no;
            } else {
                $styles = 'background:#FFC2C2;';
                $wp_content = "";
                $cut_no += 0;
                $cut_content = "";
            }

            //Setting Header
            $wp .= '<th width="30" style="text-align:center; ' . $styles . '">' . $wp_content . '</th>';
            $cut .= '<th width="30" style="text-align:center; ' . $styles . '">' . $cut_content .'#####'.'</th>';
            $date .= '<th width="30" style="text-align:center; ' . $styles . '">' . $firstDate .'####'. '</th>';

            $firstDate = date("d M", strtotime("+1 day", strtotime($firstDate)));
        }
        $wp .= '<th width="30" style="text-align:center;" rowspan="3">OPEN</th>
        <th width="30" style="text-align:center;" rowspan="3">CLOSE</th>
        <th width="30" style="text-align:center;" rowspan="3">CLOSE-DELAY</th>
        <th width="30" style="text-align:center;" rowspan="3">ALL</th>
        <th width="30" style="text-align:center;" rowspan="3">CLOSE %</th>
        <th width="30" style="text-align:center;" rowspan="3">CLOSE %</th>';
      
        $wp .= '</tr>';
        $cut .= '</tr>';
        $date .= '</tr>';

        foreach ($records as $record) {
            $day = 1;
            $i = 0;
            $firstDate2 = date('01 M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M', strtotime($filter_year . "-" . $filter_month . "-01"));
            $period = $filter_year . $filter_month;

            $this->dummy->select('item_id, wp, SUM(qty) as qty');
            $this->dummy->from("serial_barcode_packing");
            $this->dummy->where("mpp_periode", $period);
            $this->dummy->where("item_id", $record['product_no']);
            $this->dummy->where("sts_in", true);
            $this->dummy->group_by("wp, item_id");
            $this->dummy->order_by("wp", "asc");
            $this->dummy->limit(2);
            $packings = $this->dummy->get()->result_array();

            $content .= "<tr>
                         <td rowspan='1'>" . $no . "</td>
                         <td rowspan='1'>" . $record['product_no'] .'dasdksadj'."</td>
                         <td rowspan='1'>" . $record['product_name'] . "</td>
                         <td rowspan='1'>" . $record['circuit_no'] . "</td>
                         <td rowspan='1'>" . $record['prod_plan'] . "</td>";

            $content_packing = "";
            $content_ost = "";
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $field_day = "date_".$day;

                if(@$packings[$i]['qty'] == ""){
                    $qty_packing = 0;
                }else{
                    $qty_packing = @$packings[$i]['qty'];
                }

                if (is_numeric(@$record[$field_day])) {
                	$date_qty = @$record[$field_day];
                }else{
                	$date_qty = 0;
                }

                //Setting Content
                if (@$record[$field_day] == "W") {
                    $content .= "<td style='background:#FFC2C2;'>0</td>";
                    // $content_packing .= "<td style='background:#FFC2C2;'>0</td>";
                    // $content_ost .= "<td style='background:#FFC2C2;'>0</td>";
                } else {
                    $content .= "<td>" . $record[$field_day] .'###'."</td>";
                    // $content_packing .= "<td>" . @$qty_packing . "</td>";
                    // $content_ost .= "<td>" . (@$date_qty - $qty_packing) .'akaka'."</td>";
                    $i++;
                }

                $firstDate2 = date("d M", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
            }

            $content .= "<td>##</td>";
            $content .= "<td>##</td>";
            $content .= "<td>##</td>";
            $content .= "<td>##</td>";
            $content .= "<td>##</td>";
            $content .= "<td>##</td>";
//sini aja
            $content .= '</tr>';
            // $content .= "<tr><td>ACT QTY</td>" . $content_packing . "</tr>";
            // $content .= "<tr><td>OST QTY</td>" . $content_ost . "</tr>";
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
                            <small>MPP REALIZATION</small>
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
        <th style="text-align:center;" rowspan="4" width="20">NO</th>
        <th style="text-align:center;" width="200" rowspan="4">CUSTOMER</th>
        <th style="text-align:center;" width="150" rowspan="4">ASSY NO</th>
        <th style="text-align:center;" width="50" rowspan="4">ASSY NAME</th>
        <th style="text-align:center;" width="50" rowspan="4">OS OLD MPP</th>
    ';

// Concatenate dynamic parts
$html .= $wp . $cut . $date . $content . $contenttest;
// $html .= '
//         <th style="text-align:center;" rowspan="4" width="20">OPEN</th>
// </tr>
// </table>';
// var_dump('damn44'.htmlspecialchars($html));
// Continue building the table with the additional rows

        echo $html;
        // var_dump('jamal55'.htmlspecialchars($html));
        die();
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_wip extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        // $this->load->model('banshu');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_wip');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM mst_wip WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            //Select Query
            $this->db->select('a.item_id, a.item_name, b.*, c.circuit_no, (b.pp + b.plant_1 + b.plant_2 + b.plant_3 + b.plant_4 + b.plant_5 + b.plant_6 + b.plant_7 + b.plant_8 + b.plant_9 + b.plant_10 + b.rqa + b.hav) as total_wip, d.name as created_name, e.name as updated_name');
            $this->db->from('mst_item a');
            $this->db->join('mst_wip b', "a.item_id = b.product_no and b.p_month = '$filter_month' and b.p_year = '$filter_year' and b.revision = '$filter_revision'", 'left');
            $this->db->join('wip_mst_wos_cct c', 'a.item_id = c.mstwos_assyno', 'left');
            $this->db->join('user d', 'b.created_by = d.username', 'left');
            $this->db->join('user e', 'b.updated_by = e.username', 'left');
            if ($filter_product_no != "") {
                $this->db->where('a.item_id', $filter_product_no);
            }
            $this->db->where('a.stscode_id', '01');
            $this->db->where('a.pfm_id', '06');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $item = $this->api->read('mst_item', [], ["item_id" => $post['product_no']]);
                //Get CCT
                $items = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => base64_encode($post['product_no'])]);
                $wip = $this->api->reads('mst_wip', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "product_no" => $post['product_no']
                ]);

                if (count($wip) > 0) {
                    echo json_encode(array("title" => "Available", "message" => "Stock WIP in Product No " . $post['product_no'] . " is duplicate", "theme" => "error"));
                } else {
                    $dataFinal = array_merge($post, array(
                        "product_name" => @$item->item_name,
                        "circuit_no" => @$items->circuit_no
                    ));
                    $send   = $this->api->create('mst_wip', $dataFinal, "WIP", "mst_wip");
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->api->update('mst_wip', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_wip', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        // $send = $this->api->delete('mst_wip', [
        //     "p_month" => $data->val(2, 3), 
        //     "p_year" => $data->val(2, 4), 
        //     "revision" => $data->val(3, 3)
        // ]);

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'product_no' => $data->val($i, 2),
                'pp' => $data->val($i, 3),
                'plant_1' => $data->val($i, 4),
                'plant_2' => $data->val($i, 5),
                'plant_3' => $data->val($i, 6),
                'plant_4' => $data->val($i, 7),
                'plant_5' => $data->val($i, 8),
                'plant_6' => $data->val($i, 9),
                'plant_7' => $data->val($i, 10),
                'plant_8' => $data->val($i, 11),
                'plant_9' => $data->val($i, 12),
                'plant_10' => $data->val($i, 13),
                'rqa' => $data->val($i, 14),
                'hav' => $data->val($i, 15)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');

            //Cek Items
            $item = $this->api->read('mst_item', [], ["item_id" => $data['product_no']]);

            //Get CCT
            $items = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $data['product_no']]);
            $wip = $this->api->reads('mst_wip', [], [
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                "product_no" => $data['product_no']
            ]);

            if (count($wip) > 0) {
                $send = $this->api->update('mst_wip', [
                    "p_month" => $data['p_month'],
                    "p_year" => $data['p_year'],
                    "revision" => $data['revision'],
                    "product_no" => $data['product_no']], $data);

                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Update Successfully", "theme" => "success"));
            } elseif ($data['p_month'] == "" || $data['p_year'] == "" || $data['revision'] == "") {
                echo json_encode(array("title" => "Empty", "message" => "Period or Revision Cannot Empty", "theme" => "error"));
            } else {
                $dataFinal = array_merge($data, array(
                    "product_name" => @$item->item_name,
                    "circuit_no" => @$items->circuit_no
                ));
                $send   = $this->api->create('mst_wip', $dataFinal, "WIP", "WIP");
                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Saved Successfully", "theme" => "success"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mst_wip.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mst_wip.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mst_wip.txt";

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
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_wip_$format.xls");
        }

        if ($this->input->get()) {

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            //Select Query
            $this->db->select('d.item_id, d.item_name, a.*, e.circuit_no, (a.pp + a.plant_1 + a.plant_2 + a.plant_3 + a.plant_4 + a.plant_5 + a.plant_6 + a.plant_7 + a.plant_8 + a.plant_9 + a.plant_10 + a.rqa + a.hav) as total_wip, b.name as created_name, c.name as updated_name');
            $this->db->from('mst_item d');
            $this->db->join("mst_wip a", "d.item_id = a.product_no and a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision'", 'left');
            $this->db->join('user b', 'a.created_by = b.username', 'left');
            $this->db->join('user c', 'a.updated_by = c.username', 'left');
            $this->db->join('wip_mst_wos_cct e', 'e.mstwos_assyno = d.item_id', 'left');
            if ($filter_product_no != "") {
                $this->db->where('d.item_id', $filter_product_no);
            }
            $this->db->where('d.stscode_id', '01');
            $this->db->where('d.pfm_id', '06');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; }</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER WIP</small>
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
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;" rowspan="2" width="20">No</th>
                <th style="text-align:center;" rowspan="2">Product No</th>
                <th style="text-align:center;" rowspan="2">Product Name</th>
                <th style="text-align:center;" rowspan="2">CCT</th>
                <th style="text-align:center;" colspan="13">Stock WIP</th>
                <th style="text-align:center;" rowspan="2">Total</th>
            </tr>
            <tr>
                <th style="text-align:center;">PP</th>
                <th style="text-align:center;">Plant 1</th>
                <th style="text-align:center;">Plant 2</th>
                <th style="text-align:center;">Plant 3</th>
                <th style="text-align:center;">Plant 4</th>
                <th style="text-align:center;">Plant 5</th>
                <th style="text-align:center;">Plant 6</th>
                <th style="text-align:center;">Plant 7</th>
                <th style="text-align:center;">Plant 8</th>
                <th style="text-align:center;">Plant 9</th>
                <th style="text-align:center;">Plant 10</th>
                <th style="text-align:center;">Repair QA</th>
                <th style="text-align:center;">HAV</th>
            </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td>' . $data['pp'] . '</td>
                            <td>' . $data['plant_1'] . '</td>
                            <td>' . $data['plant_2'] . '</td>
                            <td>' . $data['plant_3'] . '</td>
                            <td>' . $data['plant_4'] . '</td>
                            <td>' . $data['plant_5'] . '</td>
                            <td>' . $data['plant_6'] . '</td>
                            <td>' . $data['plant_7'] . '</td>
                            <td>' . $data['plant_8'] . '</td>
                            <td>' . $data['plant_9'] . '</td>
                            <td>' . $data['plant_10'] . '</td>
                            <td>' . $data['rqa'] . '</td>
                            <td>' . $data['hav'] . '</td>
                            <td>' . $data['total_wip'] . '</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}

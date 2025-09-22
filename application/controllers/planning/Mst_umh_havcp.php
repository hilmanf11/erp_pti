<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_umh_havcp extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            //Process Main Number
            $this->db->select('COUNT(a.number) as total, a.number, a.name');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->where('a.deleted_is', 0);
            $this->db->where('a.name !=', 'PP');
            $this->db->group_by('a.number');
            $this->db->order_by('b.flag', 'asc');
            $processMainNumber = $this->db->get()->result_array();

            //Process Sub Number
            $this->db->select('COUNT(b.number) as total, b.number, b.name');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->where('a.deleted_is', 0);
            $this->db->where('a.name !=', 'PP');
            $this->db->group_by('b.number');
            $this->db->order_by('b.flag', 'asc');
            $processSubNumber = $this->db->get()->result_array();

            //Process Main Number
            $this->db->select('c.number, c.name');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->where('a.deleted_is', 0);
            $this->db->where('a.name !=', 'PP');
            $this->db->group_by('c.number');
            $this->db->order_by('b.flag', 'asc');
            $processNumber = $this->db->get()->result_array();

            $no = 1;
            foreach ($processNumber as $process) {
                $arrProcess[] = array(
                    "number" => $process['number'],
                    "name" => $process['name']
                );
                $no++;
            }

            $data['mainProcess'] = @$processMainNumber;
            $data['subProcess'] = @$processSubNumber;
            $data['process'] = !empty($arrProcess) ? $arrProcess : [];

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_umh_havcp');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('mst_umh_havcp', [], ["number" => $number]);

            echo json_encode($processSub);
        }
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

            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $ex = explode("_", $id);
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            if ($id == 0) {
                //Select Query
                $this->db->select('*, SUM(cycle_time) as total_umh');
                $this->db->from('mst_umh_havcp');
                $this->db->where('deleted_is', 0);
                if($filter_line_no != ""){
                    $this->db->where('line_no', $filter_line_no);
                }
                $this->db->like('product_no', $filter_product_no);
                $this->db->group_by('line_no');
                $this->db->order_by('line_no', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {
                    $line_no = $record['line_no'];
                    $line_name = $record['line_name'];
                    $product_no = $record['product_no'];

                    $arr = array(
                        "id" => "1" . "_" . $line_no,
                        "state" => "closed",
                        "line_no" => $line_no,
                        "line_name" => $line_name,
                        "circuit_no" => "-",
                        "total" => $record['total_umh'],
                        "total_umh" => round($record['total_umh'] / 3600, 3),
                    );

                    //Process Number
                    $this->db->select('d.process_number, SUM(d.cycle_time) as cycle_time');
                    $this->db->from('mst_process_main a');
                    $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                    $this->db->join('mst_process c', 'b.number = c.process_sub_number');
                    $this->db->join('mst_umh_havcp d', 'c.number = d.process_number');
                    $this->db->where('d.deleted_is', 0);
                    $this->db->where('d.line_no', $line_no);
                    $this->db->where('a.name !=', 'PP');
                    $this->db->group_by('d.process_number');
                    $this->db->group_by('d.line_no');
                    $this->db->order_by('b.flag', 'asc');
                    $processNumber = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($processNumber as $process) {
                        $arrProcess += array(
                            $process['process_number'] => $process['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge($arr, $arrProcess);
                }
            } else {
                $explode = explode("_", $id);
                //Select Query
                $query = $this->db->query("SELECT z.line_no, z.product_no, sum(cycle_time) as total_umh, count(circuit_no) as total_cct from 
                    (select line_no, product_no, circuit_no, SUM(cycle_time) as cycle_time from mst_umh_havcp group by product_no, circuit_no) z 
                    WHERE z.line_no = '$explode[1]' and z.product_no like '%$filter_product_no%'
                    GROUP by z.product_no 
                    ORDER BY z.product_no asc");
                $records = $query->result_array();

                foreach ($records as $record) {
                    $line_no = $record['line_no'];
                    $product_no = $record['product_no'];

                    $arr = array(
                        "line_no" => $product_no,
                        "line_name" => $product_no,
                        "product_no" => $product_no,
                        "total" => $record['total_umh'],
                        "total_umh" => round($record['total_umh'] / 3600, 3),
                    );

                    //Process Number
                    $this->db->select('d.process_number, SUM(d.cycle_time) as cycle_time');
                    $this->db->from('mst_process_main a');
                    $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                    $this->db->join('mst_process c', 'b.number = c.process_sub_number');
                    $this->db->join('mst_umh_havcp d', 'c.number = d.process_number');
                    $this->db->where('d.deleted_is', 0);
                    $this->db->where('d.line_no', $line_no);
                    $this->db->where('d.product_no', $product_no);
                    $this->db->where('a.name !=', 'PP');
                    $this->db->group_by('d.process_number');
                    $this->db->group_by('d.product_no');
                    $this->db->group_by('d.line_no');
                    $this->db->order_by('b.flag', 'asc');
                    $processNumber = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($processNumber as $process) {
                        $arrProcess += array(
                            $process['process_number'] => $process['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge($arr, $arrProcess);
                }
            }

            //Mapping Data
            // $result['total'] = $totalRows;
            $result = !empty($arrFinal) ? $arrFinal : [];
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $item = $this->api->read('mst_item', [], ["item_id" => $post['product_no']]);
                $customer = $this->api->read('mst_customer', [], ["number" => @$item->id_customer], "", "name", "asc");
                $line = $this->api->read('mst_item_line', [], ["item_id" => $post['product_no']], "", "line_no", "asc");
                $lineName = $this->api->read('mst_line', [], ["line_no" => @$line->line_no], "", "line_no", "asc");

                $umh = $this->api->reads('mst_umh_havcp', [], [
                    "line_no" => @$line->line_no,
                    "product_no" => $post['product_no'],
                    "process_main_number" => $post['process_main_number'],
                    "process_sub_number" => $post['process_sub_number'],
                    "process_number" => $post['process_number']
                ]);

                $dataFinal = array_merge($post, array(
                    "line_no" => @$line->line_no,
                    "line_name" => @$lineName->remarks,
                    "customer_id" => @$customer->number,
                    "customer_name" => @$customer->name
                ));

                if (count($umh) > 0) {
                    echo json_encode(array("title" => "Available", "message" => "Unit Man Hour is duplicate", "theme" => "error"));
                } else {
                    $send   = $this->api->create('mst_umh_havcp', $dataFinal, "UMHCP", "mst_umh_havcp");
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_umh_havcp', $data);
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

        $this->db->select('a.process_main_number, a.process_sub_number, a.number as process_number');
        $this->db->from('mst_process a');
        $this->db->join('mst_process_main b', 'a.process_main_number = b.number');
        $this->db->join('mst_process_sub c', 'a.process_sub_number = c.number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('b.name !=', 'PP');
        $this->db->group_by('a.number');
        $this->db->order_by('c.flag', 'asc');
        $processNumber = $this->db->get()->result_array();

        for ($i = 6; $i <= $total_row; $i++) {
            $datas = array(
                'product_no' => $data->val($i, 2)
            );

            $arrProcess = array();
            $no = 0;
            foreach ($processNumber as $process) {
                $arrProcess = array(
                    "process_main_number" => $process['process_main_number'],
                    "process_sub_number" => $process['process_sub_number'],
                    "process_number" => $process['process_number'],
                    "cycle_time" => $data->val($i, 3 + $no)
                );
                $no++;

                $arrFinal[] = array_merge($datas, $arrProcess);
            }
        }

        $arrFinal['total'] = count($arrFinal);
        echo json_encode($arrFinal);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');

            $item = $this->api->read('mst_item', [], ["item_id" => $data['product_no']]);
            $customer = $this->api->read('mst_customer', [], ["number" => @$item->id_customer], "", "name", "asc");
            $line = $this->api->read('mst_item_line', [], ["item_id" => $data['product_no']], "", "line_no", "asc");
            $lineName = $this->api->read('mst_line', [], ["line_no" => @$line->line_no], "", "line_no", "asc");
            $processMain = $this->api->read('mst_process_main', [], ["number" => $data['process_main_number']]);
            $processSub = $this->api->read('mst_process_sub', [], ["number" => $data['process_sub_number']]);
            $process = $this->api->read('mst_process', [], ["number" => $data['process_number']]);
            $umh = $this->api->reads('mst_umh_havcp', [], [
                "product_no" => $data['product_no'],
                "process_main_number" => $data['process_main_number'],
                "process_sub_number" => $data['process_sub_number'],
                "process_number" => $data['process_number']
            ]);

            if (empty($item->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (empty($processMain->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Main Process Code " . $data['process_main_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($processSub->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Sub Process Code " . $data['process_sub_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Code " . $data['process_number'] . " Not Found", "theme" => "error"));
            } elseif (count($umh) > 0) {
                $dataFinal = array_merge($data, array(
                    "line_no" => @$line->line_no,
                    "line_name" => @$lineName->remarks,
                    "customer_id" => @$customer->number,
                    "customer_name" => @$customer->name
                ));

                $delete = $this->api->delete('mst_umh_havcp', [
                    "product_no" => $data['product_no'],
                    "process_main_number" => $data['process_main_number'],
                    "process_sub_number" => $data['process_sub_number'],
                    "process_number" => $data['process_number']]);

                if($delete){
                    $dataFinal = array_merge($data, array(
                        "line_no" => @$line->line_no,
                        "line_name" => @$lineName->remarks,
                        "customer_id" => @$customer->number,
                        "customer_name" => @$customer->name
                    ));

                    $send   = $this->api->create('mst_umh_havcp', $dataFinal, "UMHCP", "UMHCP");
                    echo $send;
                }

            } else {
                $dataFinal = array_merge($data, array(
                    "line_no" => @$line->line_no,
                    "line_name" => @$lineName->remarks,
                    "customer_id" => @$customer->number,
                    "customer_name" => @$customer->name
                ));

                $send   = $this->api->create('mst_umh_havcp', $dataFinal, "UMHCP", "UMHCP");
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mst_umh_havcp.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mst_umh_havcp.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mst_umh_havcp.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function downloadTemplate()
    {
        $format  = date("Ymd");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=tmp_umh_havcp.xls");

        //Process Main Number
        $this->db->select('COUNT(a.number) as total, a.number, a.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('a.name !=', 'PP');
        $this->db->group_by('a.number');
        $this->db->order_by('b.flag', 'asc');
        $processMainNumber = $this->db->get()->result_array();

        //Process Sub Number
        $this->db->select('COUNT(b.number) as total, b.number, b.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('a.name !=', 'PP');
        $this->db->group_by('b.number');
        $this->db->order_by('b.flag', 'asc');
        $processSubNumber = $this->db->get()->result_array();

        //Process Main Number
        $this->db->select('c.number, c.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('a.name !=', 'PP');
        $this->db->group_by('c.number');
        $this->db->order_by('b.flag', 'asc');
        $processNumber = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center style="margin:0;">
            <b style="font-size:20px;">TEMPLATE UPLOAD UNIT MAN HOUR (HAVCP)</b>
        </center>
        <i>if an error occurs when uploading please SAVE AS, or save file new</i>
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;" rowspan="3" width="50">No</th>
                <th style="text-align:center;" rowspan="3"><b>Product No</b></th>';
        foreach ($processMainNumber as $main) {
            $html .= '<th style="text-align:center;" colspan="' . $main['total'] . '"> ' . $main['name'] . '</th>';
        }

        $html .= '</tr><tr>';

        foreach ($processSubNumber as $sub) {
            $html .= '<th style="text-align:center;" colspan="' . $sub['total'] . '"> ' . $sub['name'] . '</th>';
        }

        $html .= '</tr><tr>';

        foreach ($processNumber as $proces) {
            $html .= '<th style="text-align:center;"> ' . $proces['name'] . '</th>';
        }

        $html .= '</tr></table>';

        echo $html;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_umh_havcp_$format.xls");
        }

        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Process Main Number
        $this->db->select('COUNT(a.number) as total, a.number, a.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('a.name !=', 'PP');
        $this->db->group_by('a.number');
        $this->db->order_by('b.flag', 'asc');
        $processMainNumber = $this->db->get()->result_array();

        //Process Sub Number
        $this->db->select('COUNT(b.number) as total, b.number, b.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('a.name !=', 'PP');
        $this->db->group_by('b.number');
        $this->db->order_by('b.flag', 'asc');
        $processSubNumber = $this->db->get()->result_array();

        //Process Main Number
        $this->db->select('c.number, c.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->where('a.name !=', 'PP');
        $this->db->group_by('c.number');
        $this->db->order_by('b.flag', 'asc');
        $processNumber = $this->db->get()->result_array();

        //Select Query
        $this->db->select('*, SUM(cycle_time) as total_umh');
        $this->db->from('mst_umh_havcp');
        $this->db->where('deleted_is', 0);
        $this->db->like('line_no', $filter_line_no);
        $this->db->like('product_no', $filter_product_no);
        $this->db->group_by('product_no');
        $this->db->order_by('line_no', 'asc');
        $this->db->order_by('customer_id', 'asc');
        $this->db->order_by('product_no', 'asc');
        $records = $this->db->get()->result_array();

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
                            <small>MASTER UNIT MAN HOUR (HAVCP)</small>
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
                <th rowspan="3" style="text-align:center;"width="20">No</th>
                <th rowspan="3" style="text-align:center;">Product No</th>';
        foreach ($processMainNumber as $main) {
            $html .= '<th style="text-align:center;" colspan="' . $main['total'] . '"> ' . $main['name'] . '</th>';
        }

        $html .= '<th style="text-align:center;" rowspan="3">Total HAVCP</th>
                </tr><tr>';

        foreach ($processSubNumber as $sub) {
            $html .= '<th style="text-align:center;" colspan="' . $sub['total'] . '"> ' . $sub['name'] . '</th>';
        }

        $html .= '</tr><tr>';

        foreach ($processNumber as $proces) {
            $html .= '<th style="text-align:center;"> ' . $proces['name'] . '</th>';
        }

        $html .= '</tr>';

        $no = 1;
        $arrFinal = array();
        foreach ($records as $record) {
            $line_no = $record['line_no'];
            $customer_id = $record['customer_id'];
            $customer_name = $record['customer_name'];
            $product_no = $record['product_no'];

            $arr = array(
                "line_no" => $line_no,
                "customer_id" => $customer_id,
                "customer_name" => $customer_name,
                "product_no" => $product_no,
                "remarks" => $record['remarks'],
                "total_umh" => $record['total_umh'],
            );

            //Process Main Number
            $this->db->select('process_number, SUM(cycle_time) as cycle_time');
            $this->db->from('mst_umh_havcp');
            $this->db->where('deleted_is', 0);
            $this->db->where('line_no', $line_no);
            $this->db->where('customer_id', $customer_id);
            $this->db->where('product_no', $product_no);
            $this->db->group_by('process_number');
            $this->db->order_by('process_number', 'asc');
            $processNumber2 = $this->db->get()->result_array();

            $arrProcess = array();
            foreach ($processNumber2 as $process) {
                $arrProcess += array(
                    $process['process_number'] => $process['cycle_time']
                );
            }

            $arrFinal[] = array_merge($arr, $arrProcess);
        }

        foreach ($arrFinal as $final) {
            $html .= '  <tr>
                        <td style="text-align:center;">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $final['product_no'] . '</td>';
            foreach ($processNumber as $proses) {
                $number = $proses['number'];
                $html .= '<td style="text-align:center;"> ' . @$final["$number"] . '</td>';
            }
            $html .= '  <td style="text-align:center;">' . $final['total_umh'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

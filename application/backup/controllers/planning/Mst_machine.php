<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_machine extends CI_Controller
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
        $this->form_validation->set_rules('machine_serial', 'Machine Serial', 'required|min_length[2]|max_length[30]|is_unique[mst_machine.machine_serial]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_machine');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->reads('mst_machine', ["machine_code" => $post]);
        echo json_encode($send);
    }

    public function autonumber()
    {
        $date       = date("Ym");
        $sqlGetID   = $this->db->query("SELECT max(`machine_code`) as kode FROM mst_machine");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $autoID = sprintf("%04s", $urutan + 1);
        }

        echo  "MC-" . $autoID;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Filter
            $filter_machine_type = $this->input->get('filter_machine_type');
            $filter_machine_name = $this->input->get('filter_machine_name');
            $filter_process_main_number = $this->input->get('filter_process_main_number');
            $filter_process_sub_number = $this->input->get('filter_process_sub_number');

            //Select Query
            $this->db->select('a.*, b.name as process_main_name, c.name as process_sub_name, e.category, f.name as created_name, g.name as updated_name');
            $this->db->from('mst_machine a');
            $this->db->join('mst_process_main b', 'a.process_main_number = b.number', 'left');
            $this->db->join('mst_process_sub c', 'a.process_sub_number = c.number', 'left');
            $this->db->join('mst_machine_type e', 'a.machine_type_number = e.number', 'left');
            $this->db->join('user f', 'a.created_by = f.username', 'left');
            $this->db->join('user g', 'a.updated_by = g.username', 'left');
            $this->db->where('a.deleted_is', 0);
            $this->db->like('a.machine_type_number', $filter_machine_type);
            $this->db->like('a.machine_name', $filter_machine_name);
            $this->db->like('b.description', $filter_process_main_number);
            $this->db->like('a.process_sub_number', $filter_process_sub_number);
            $this->db->order_by('a.machine_code', 'ASC');
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
                $upload = $this->api->upload('picture', ['png', 'jpg', 'jpeg'], 'assets/image/machine/');
                $postFinal = array_merge($post, array("picture_1" => $upload));
                $send   = $this->api->create('mst_machine', $postFinal, "MC", "mst_machine");
                echo $send;
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
            $upload = $this->api->upload('picture', ['png', 'jpg', 'jpeg'], 'assets/image/machine/', ["id" => $id], "mst_machine", "picture_1");
            $postFinal = array_merge($post, array("picture_1" => $upload));
            $send = $this->api->update('mst_machine', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_machine', $data);
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

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'machine_serial' => $data->val($i, 2),
                'bei_code_new' => $data->val($i, 3),
                'bei_code_old' => $data->val($i, 4),
                'location' => $data->val($i, 5),
                'room' => $data->val($i, 6),
                'machine_name' => $data->val($i, 7),
                'machine_type_number' => $data->val($i, 8),
                'process_main_number' => $data->val($i, 9),
                'process_sub_number' => $data->val($i, 10),
                'maker' => $data->val($i, 11),
                'year' => $data->val($i, 12),
                'power' => $data->val($i, 13),
                'uom' => $data->val($i, 14),
                'operator_qty' => $data->val($i, 15),
                'voltage' => $data->val($i, 16),
                'air_consumption' => $data->val($i, 17),
                'purchase_leadtime' => $data->val($i, 18),
                'space' => $data->val($i, 19)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mst_machine.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mst_machine.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mst_machine.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');

            $sqlGetID   = $this->db->query("SELECT max(`machine_code`) as kode FROM mst_machine");
            $rowID      = $sqlGetID->row();
            $kode       = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%04s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -4);
                $autoID = sprintf("%04s", $urutan + 1);
            }

            $autonumber = "MC-" . $autoID;
            //Cek Process Number
            $process_main = $this->api->read('mst_process_main', [], ["number" => $data['process_main_number']]);
            $process_sub = $this->api->read('mst_process_sub', [], ["number" => $data['process_sub_number']]);
            //Cek Machine Type
            $machine_type = $this->api->read('mst_machine_type', [], ["number" => $data['machine_type_number']]);

            if (empty($process_main->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Main Process " . $data['process_main_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process_sub->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Sub Process " . $data['process_sub_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($machine_type->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Machine Type " . $data['machine_type_number'] . " Not Found", "theme" => "error"));
            } else {
                $dataMerge = array_merge($data, ["machine_code" => $autonumber]);
                $send   = $this->api->create('mst_machine', $dataMerge, "MC", "MC");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_machine_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter
        $filter_machine_type = $this->input->get('filter_machine_type');
        $filter_machine_name = $this->input->get('filter_machine_name');
        $filter_process_main_number = $this->input->get('filter_process_main_number');
        $filter_process_sub_number = $this->input->get('filter_process_sub_number');

        //Select Query
        $this->db->select('a.*, b.name as process_main_name, c.name as process_sub_name, e.category');
        $this->db->from('mst_machine a');
        $this->db->join('mst_process_main b', 'a.process_main_number = b.number', 'left');
        $this->db->join('mst_process_sub c', 'a.process_sub_number = c.number', 'left');
        $this->db->join('mst_machine_type e', 'a.machine_type_number = e.number', 'left');
        $this->db->where('a.deleted_is', 0);
        $this->db->like('a.machine_type_number', $filter_machine_type);
        $this->db->like('a.machine_name', $filter_machine_name);
        $this->db->like('b.description', $filter_process_main_number);
        $this->db->like('a.process_sub_number', $filter_process_sub_number);
        $this->db->order_by('a.machine_code', 'ASC');
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
                            <small>MASTER MACHINE</small>
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
                <th>Main Process</th>
                <th>Sub Process</th>
                <th>Machine Code</th>
                <th>Serial No</th>
                <th>BEI Code (New)</th>
                <th>BEI Code (Old)</th>
                <th>Location</th>
                <th>Room</th>
                <th>Machine Name</th>
                <th>Kind ID</th>
                <th>Kind Machine</th>
                <th>Operator</th>
                <th>Voltage</th>
                <th>Air Consumption</th>
                <th>Purchase Leadtime</th>
                <th>Space (M2)</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['process_main_name'] . '</td>
                            <td>' . $data['process_sub_name'] . '</td>
                            <td>' . $data['machine_code'] . '</td>
                            <td>' . $data['machine_serial'] . '</td>
                            <td>' . $data['bei_code_new'] . '</td>
                            <td>' . $data['bei_code_old'] . '</td>
                            <td>' . $data['location'] . '</td>
                            <td>' . $data['room'] . '</td>
                            <td>' . $data['machine_name'] . '</td>
                            <td>' . $data['machine_type_number'] . '</td>
                            <td>' . $data['category'] . '</td>
                            <td>' . $data['operator_qty'] . '</td>
                            <td>' . $data['voltage'] . '</td>
                            <td>' . $data['air_consumption'] . '</td>
                            <td>' . $data['purchase_leadtime'] . '</td>
                            <td>' . $data['space'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

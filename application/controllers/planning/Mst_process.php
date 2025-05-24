<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_process extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Process Code', 'required|min_length[5]|max_length[30]|is_unique[mst_process.number]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_process');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $process = $this->api->reads('mst_process', [], ["process_sub_number" => $number]);

            echo json_encode($process);
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as process_main_name, c.name as process_sub_name, d.name as created_name, e.name as updated_name');
            $this->db->from('mst_process a');
            $this->db->join('mst_process_main b', 'a.process_main_number = b.number');
            $this->db->join('mst_process_sub c', 'a.process_sub_number = c.number');
            $this->db->join('user d', 'a.created_by = d.username', 'left');
            $this->db->join('user e', 'a.updated_by = e.username', 'left');
            $this->db->where('a.deleted_is', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "number") {
                        $this->db->like("a.number", $filter->value);
                    } elseif ($filter->field == "name") {
                        $this->db->like("a.name", $filter->value);
                    } elseif ($filter->field == "description") {
                        $this->db->like("a.description", $filter->value);
                    } elseif ($filter->field == "trans_date") {
                        $this->db->like("a.trans_date", $filter->value);
                    } elseif ($filter->field == "process_main_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "process_sub_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "efficiency") {
                        $this->db->like("a.efficiency", $filter->value);
                    } elseif ($filter->field == "total_man_power") {
                        $this->db->like("a.total_man_power", $filter->value);
                    } elseif ($filter->field == "created_by") {
                        $this->db->like("a.created_by", $filter->value);
                    } elseif ($filter->field == "created_date") {
                        $this->db->like("a.created_date", $filter->value);
                    } elseif ($filter->field == "updated_by") {
                        $this->db->like("a.updated_by", $filter->value);
                    } elseif ($filter->field == "updated_date") {
                        $this->db->like("a.updated_date", $filter->value);
                    }
                }
            }
            $this->db->order_by('b.number', 'ASC');
            $this->db->order_by('a.number', 'ASC');
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
                $send   = $this->api->create('mst_process', $post, "PMS", "mst_process");
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
            $send = $this->api->update('mst_process', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_process', $data);
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
                'trans_date' => date("Y-m-d"),
                'process_main_number' => $data->val($i, 2),
                'process_sub_number' => $data->val($i, 3),
                'number' => $data->val($i, 4),
                'name' => $data->val($i, 5),
                'efficiency' => $data->val($i, 6),
                'total_man_power' => $data->val($i, 7),
                'description' => $data->val($i, 8)
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

            $process_main = $this->api->read('mst_process_main', [], ["number" => $data['process_main_number']]);
            $process_sub = $this->api->read('mst_process_sub', [], ["number" => $data['process_sub_number']]);
            $process = $this->api->reads('mst_process', [], ["number" => $data['number']]);

            if (empty($process_main->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Main " . $data['process_main_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process_sub->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process Sub " . $data['process_sub_number'] . " Not Found", "theme" => "error"));
            } elseif (count($process) > 0) {
                echo json_encode(array("title" => "Duplicated", "message" => "Process Code " . $data['number'] . " has been Duplicated", "theme" => "error"));
            } else {
                $send = $this->api->create('mst_process', $data, "PMS", "PMS");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_process_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as process_main_name, c.name as process_sub_name');
        $this->db->from('mst_process a');
        $this->db->join('mst_process_main b', 'a.process_main_number = b.number');
        $this->db->join('mst_process_sub c', 'a.process_sub_number = c.number');
        $this->db->where('a.deleted_is', 0);
        $this->db->order_by('a.number', 'asc');
        $this->db->order_by('b.number', 'asc');

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
                            <small>MASTER PROCESS</small>
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
                <th>Trans Date</th>
                <th>Main Process</th>
                <th>Sub Process</th>
                <th>Process Code</th>
                <th>Process Name</th>
                <th>Efficiency (%)</th>
                <th>Man Power</th>
                <th>Description</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['process_main_name'] . '</td>
                            <td>' . $data['process_sub_name'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['name'] . '</td>
                            <td>' . $data['efficiency'] . '</td>
                            <td>' . $data['total_man_power'] . '</td>
                            <td>' . $data['description'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

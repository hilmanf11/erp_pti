<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_equipment_menu extends CI_Controller
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
        $this->form_validation->set_rules('equipment_code', 'Equipment Code', 'required|min_length[5]|max_length[30]|is_unique[mst_equipment_menu.equipment_code]');
        $this->form_validation->set_rules('terminal_left', 'Terminal Left', 'required|min_length[5]|max_length[30]|is_unique[mst_equipment_menu.terminal_left]');
        $this->form_validation->set_rules('terminal_right', 'Terminal Right', 'required|min_length[5]|max_length[30]|is_unique[mst_equipment_menu.terminal_right]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_equipment_menu');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $machineMenus = $this->api->reads('mst_equipment_menu');
        echo json_encode($machineMenus);
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
            $this->db->select('a.*, b.description, c.name as created_name, d.name as updated_name');
            $this->db->from('mst_equipment_menu a');
            $this->db->join('mst_equipment b', 'a.equipment_code = b.equipment_code');
            $this->db->join('user c', 'a.created_by = c.username', 'left');
            $this->db->join('user d', 'a.updated_by = d.username', 'left');
            $this->db->where('a.deleted_is', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "equipment_code") {
                        $this->db->like("a.equipment_code", $filter->value);
                    } elseif ($filter->field == "description") {
                        $this->db->like("b.description", $filter->value);
                    } elseif ($filter->field == "terminal_left") {
                        $this->db->like("a.terminal_left", $filter->value);
                    } elseif ($filter->field == "terminal_right") {
                        $this->db->like("a.terminal_right", $filter->value);
                    } elseif ($filter->field == "toonage") {
                        $this->db->like("a.toonage", $filter->value);
                    }
                }
            }
            $this->db->order_by('a.equipment_code', 'ASC');
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
                $send   = $this->api->create('mst_equipment_menu', $post, "MM", "mst_equipment_menu");
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
            $send = $this->api->update('mst_equipment_menu', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_equipment_menu', $data);
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
                'equipment_code' => $data->val($i, 2),
                'toonage' => $data->val($i, 3),
                'terminal_left' => $data->val($i, 4),
                'terminal_right' => $data->val($i, 5)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');

            //Cek Process Main Number
            $machine = $this->api->read('mst_equipment', [], ["equipment_code" => $data['equipment_code']]);
            //Cek Items
            $terminalLeft = $this->api->read('mst_item', [], ["item_id" => $data['terminal_left']]);
            $terminalRight = $this->api->read('mst_item', [], ["item_id" => $data['terminal_right']]);

            if (empty($machine->equipment_code)) {
                echo json_encode(array("title" => "Not Found", "message" => "Equipment Code " . $data['equipment_code'] . " Not Found", "theme" => "error"));
            } elseif (empty($terminalLeft->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Terminal Left " . $data['item_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($terminalRight->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Terminal Right " . $data['item_id'] . " Not Found", "theme" => "error"));
            } else {
                $send   = $this->api->create('mst_equipment_menu', $data, "MM", "MM");
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mst_equipment_menu.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mst_equipment_menu.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mst_equipment_menu.txt";

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
            header("Content-Disposition: attachment; filename=mst_equipment_menu_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.description');
        $this->db->from('mst_equipment_menu a');
        $this->db->join('mst_equipment b', 'a.equipment_code = b.equipment_code');
        $this->db->where('a.deleted_is', 0);
        $this->db->order_by('a.equipment_code', 'asc');

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
                            <small>MASTER EQUIPMENT MENU</small>
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
                <th>Equipment Code</th>
                <th>Equipment Name</th>
                <th>Toonage</th>
                <th>Terminal Left</th>
                <th>Terminal Right</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['equipment_code'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td>' . $data['toonage'] . '</td>
                            <td>' . $data['terminal_left'] . '</td>
                            <td>' . $data['terminal_right'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

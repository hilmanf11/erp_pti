<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_applicator_terminal extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Applicator No', 'required|min_length[2]|max_length[30]|is_unique[mst_applicator_terminal.number]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_applicator_terminal');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('mst_applicator_terminal', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $id = $_POST['id'];
            if ($id === "0") {
                //Select Query
                $this->db->distinct('type');
                $this->db->select('type');
                $this->db->from('mst_applicator_terminal');
                $this->db->where('deleted_is', 0);
                $this->db->order_by('type', 'ASC');
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['type'],
                        "type" => $record['type'],
                        "state" => "closed"
                    );
                }
            } else {
                //Select Query
                $this->db->select('a.*, b.name as created_name, c.name as updated_name');
                $this->db->from('mst_applicator_terminal a');
                $this->db->join('user b', 'a.created_by = b.username', 'left');
                $this->db->join('user c', 'a.updated_by = c.username', 'left');
                $this->db->where('a.deleted_is', 0);
                $this->db->where('a.type', $id);
                $this->db->order_by('number', 'ASC');
                $this->db->order_by('bei_code', 'ASC');
                $arr = $this->db->get()->result_array();
            }
            $result = !empty($arr) ? $arr : [];
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->api->create('mst_applicator_terminal', $post, "AT", "mst_applicator_terminal");
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
            $send = $this->api->update('mst_applicator_terminal', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_applicator_terminal', $data);
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
                'bei_code' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'name' => $data->val($i, 4),
                'type' => $data->val($i, 5),
                'feeding_type' => $data->val($i, 6),
                'working_type' => $data->val($i, 7),
                'maker' => $data->val($i, 8),
                'toonage' => $data->val($i, 9),
                'item_id' => $data->val($i, 10),
                'remarks' => $data->val($i, 11)
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
            $items = $this->api->read('mst_item', [], ["item_id" => $data['item_id']]);
            $types = $this->api->read('mst_applicator_type', [], ["number" => $data['type']]);

            if (empty($items->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item ID " . $data['item_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($types)) {
                echo json_encode(array("title" => "Not Found", "message" => "Applicator Type " . $data['type'] . " Not Found", "theme" => "error"));
            } else {
                $send   = $this->api->create('mst_applicator_terminal', $data, "AT", "AT");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_applicator_terminal_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('mst_applicator_terminal');
        $this->db->where('deleted_is', 0);
        $this->db->order_by('number', 'asc');
        $this->db->order_by('item_id', 'asc');
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
                            <small>MASTER TERMINAL APPLICATOR</small>
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
                <th>BEI Code</th>
                <th>Applicator No</th>
                <th>Applicator Name</th>
                <th>Feeding Type</th>
                <th>Working Type</th>
                <th>Maker</th>
                <th>Toonage</th>
                <th>Terminal No</th>
                <th>Terminal Name</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['bei_code'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['name'] . '</td>
                            <td>' . $data['feeding_type'] . '</td>
                            <td>' . $data['working_type'] . '</td>
                            <td>' . $data['maker'] . '</td>
                            <td>' . $data['toonage'] . '</td>
                            <td>' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

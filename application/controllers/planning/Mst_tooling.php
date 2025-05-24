<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_tooling extends CI_Controller
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
        $this->form_validation->set_rules('serial_no', 'Serial No', 'required|min_length[2]|max_length[30]|is_unique[mst_tooling.serial_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_tooling');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();
        $send = $this->api->reads('mst_tooling', ["description" => $post, "process_main_number" => @$get['process_main'], "process_sub_number" => @$get['process_sub'], "process_number" => @$get['process']]);
        echo json_encode($send);
    }

    public function autonumber()
    {
        $date       = date("Ym");
        $sqlGetID   = $this->db->query("SELECT max(`tooling_code`) as kode FROM mst_tooling");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $autoID = sprintf("%04s", $urutan + 1);
        }

        echo  "TL-" . $autoID;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $id = $_POST['id'];
            if ($id === "0") {
                //Select Query
                $this->db->select('a.tooling_type_number, b.name as tooling_type_name, SUM(a.qty) as qty');
                $this->db->from('mst_tooling a');
                $this->db->join('mst_tooling_type b', 'a.tooling_type_number = b.number');
                $this->db->join('user c', 'a.created_by = c.username', 'left');
                $this->db->join('user d', 'a.updated_by = d.username', 'left');
                $this->db->where('a.deleted_is', 0);
                $this->db->group_by('a.tooling_type_number');
                $this->db->order_by('b.name', 'ASC');
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['tooling_type_number'],
                        "tooling_type_name" => $record['tooling_type_name'],
                        "state" => "closed",
                        "qty" => $record['qty'],
                    );
                }
            } else {
                //Select Query
                $this->db->select('a.*, a.description as tooling_type_name, c.name as created_name, d.name as updated_name');
                $this->db->from('mst_tooling a');
                $this->db->join('mst_tooling_type b', 'a.tooling_type_number = b.number');
                $this->db->join('user c', 'a.created_by = c.username', 'left');
                $this->db->join('user d', 'a.updated_by = d.username', 'left');
                $this->db->where('a.deleted_is', 0);
                $this->db->where('a.tooling_type_number', $id);
                $this->db->order_by('a.tooling_code', 'ASC');
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
                $upload = $this->api->upload('picture', ['png', 'jpg', 'jpeg'], 'assets/image/tooling/');
                $postFinal = array_merge($post, array("picture" => $upload));
                $send   = $this->api->create('mst_tooling', $postFinal, "TL", "mst_tooling");
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
            $upload = $this->api->upload('picture', ['png', 'jpg', 'jpeg'], 'assets/image/tooling/', ["id" => $id], "mst_tooling", "picture");
            $postFinal = array_merge($post, array("picture" => $upload));
            $send = $this->api->update('mst_tooling', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_tooling', $data);
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
                'serial_no' => $data->val($i, 2),
                'description' => $data->val($i, 3),
                'tooling_type_number' => $data->val($i, 4),
                'qty' => $data->val($i, 5)
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

            $sqlGetID   = $this->db->query("SELECT max(`tooling_code`) as kode FROM mst_tooling");
            $rowID      = $sqlGetID->row();
            $kode       = $rowID->kode;

            if ($kode == NULL) {
                $autoID = sprintf("%04s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -4);
                $autoID = sprintf("%04s", $urutan + 1);
            }

            $autonumber = "TL-" . $autoID;

            //Cek Tooling Type
            $tooling_type = $this->api->read('mst_tooling_type', [], ["number" => $data['tooling_type_number']]);
            //Cek Items
            $terminalLeft = $this->api->read('mst_item', [], ["item_id" => $data['terminal_left']]);
            $terminalRight = $this->api->read('mst_item', [], ["item_id" => $data['terminal_right']]);

            if (empty($tooling_type->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Tooling Type " . $data['tooling_type_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($terminalLeft->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Terminal Left " . $data['item_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($terminalRight->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Terminal Right " . $data['item_id'] . " Not Found", "theme" => "error"));
            } else {
                $dataMerge = array_merge($data, ["tooling_code" => $autonumber]);
                $send   = $this->api->create('mst_tooling', $dataMerge, "TL", "TL");
                echo $send;
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mst_tooling.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mst_tooling.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mst_tooling.txt";

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
            header("Content-Disposition: attachment; filename=mst_tooling_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select('a.*, b.name as tooling_type_name, c.name as created_name, d.name as updated_name');
        $this->db->from('mst_tooling a');
        $this->db->join('mst_tooling_type b', 'a.tooling_type_number = b.number');
        $this->db->join('user c', 'a.created_by = c.username', 'left');
        $this->db->join('user d', 'a.updated_by = d.username', 'left');
        $this->db->where('a.deleted_is', 0);
        $this->db->order_by('a.tooling_code', 'ASC');
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
                            <small>MASTER TOOLING</small>
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
                <th>Tooling Code</th>
                <th>Serial No</th>
                <th>Description</th>
                <th>Type</th>
                <th>Qty</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['tooling_code'] . '</td>
                            <td>' . $data['serial_no'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td>' . $data['tooling_type_number'] . '</td>
                            <td>' . $data['qty'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

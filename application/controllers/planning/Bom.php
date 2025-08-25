<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Bom extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[mst_bom.item_id]');
        $this->form_validation->set_rules('component_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[mst_bom.component_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/bom');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->reads('mst_bom', ["item_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);
            $filter_customer = @base64_decode($get['filter_customer']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.item_name, c.name as customer_name');
            $this->db->from('mst_bom a');
            $this->db->join('mst_item b', 'a.item_id = b.item_id');
            $this->db->join('mst_customer c', 'b.id_customer = c.number', 'left');
            $this->db->like('a.item_id', $filter_item_fg_id);
            $this->db->like('a.component_id', $filter_item_rm_id);
            $this->db->like('c.number', $filter_customer);
            $this->db->group_by('a.item_id');
            $this->db->order_by('a.item_id', 'DESC');
            // $this->db->order_by('b.number', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $item_id = base64_decode($this->input->get('item_id'));
            $filter_item_rm_id = base64_decode($this->input->get('filter_item_rm_id'));

            $this->db->select('a.*, b.item_name as component_name, c.name as uom');
            $this->db->from('mst_bom a');
            $this->db->join('mst_item_raw b', 'a.component_id = b.item_id');
            $this->db->join('uom c', 'b.um_id = c.number');
            $this->db->where('a.item_id', $item_id);
            $this->db->like('a.component_id', $filter_item_rm_id);
            $this->db->order_by('a.component_id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $item_id = base64_decode($this->input->get('item_id'));

            $this->db->select('*');
            $this->db->from('mst_bom');
            $this->db->where('item_id', $item_id);
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $mst_bom = $this->api->read("mst_bom", [], ["item_id" => $post['item_id'], "component_id" => $post['component_id']]);
            if (@$mst_bom->item_fg_id != "") {
                $send = $this->api->update('mst_bom', ["item_id" => $post['item_id'], "component_id" => $post['component_id']], $post);
            } else {
                $send = $this->api->create('mst_bom', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_bom', $data);
        echo $send;
    }

    //UPLOAD DATA
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
                //excel
                'item_id' => $data->val($i, 2),
                'component_id' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/mst_bom.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/mst_bom.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/mst_bom.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $item_fg = $this->api->read('mst_item', [], ["item_id" => $data['item_id']]);
            $item_rm = $this->api->read('mst_item_raw', [], ["item_id" => $data['component_id']]);
            $mst_bom = $this->api->read('mst_bom', [], ["item_id" => $data['item_id'], "component_id" => $data['component_id']]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No" . $data['item_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No" . $data['component_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($mst_bom->component_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Part No" . $data['component_id'] . " is Duplicate Data in Product No " . $data['item_id'], "theme" => "error"));
            } else {
                $send = $this->api->create('mst_bom', $data);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_bom_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);
        $filter_customer = @base64_decode($get['filter_customer']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.item_name, c.name as customer_name');
        $this->db->from('mst_bom a');
        $this->db->join('mst_item b', 'a.item_id = b.item_id');
        $this->db->join('mst_customer c', 'b.id_customer = c.number', 'left');
        $this->db->like('a.item_id', $filter_item_fg_id);
        $this->db->like('a.component_id', $filter_item_rm_id);
        $this->db->like('c.number', $filter_customer);
        // $this->db->group_by('a.item_id');
        $this->db->order_by('a.item_id', 'DESC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#mst_bom {border-collapse: collapse;width: 100%;font-size: 12px;}#mst_bom td, #mst_bom th {border: 1px solid #ddd;padding: 2px;}#mst_bom tr:nth-child(even){background-color: #f2f2f2;}#mst_bom tr:hover {background-color: #ddd;}#mst_bom th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>MASTER BILL OF MATERIAL</h3>
            </div>
        </center>
        
        <table id="mst_bom" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer</th>
                <th>Product No</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Qty</th>
                <th>Rout ID</th>
                <th>Maker</th>
                <th>Document No</th>
                <th>Revision</th>
                <th>Date</th>
                <th>User</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['component_id'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['rout_id'] . '</td>
                    <td>' . $data['maker'] . '</td>
                    <td>' . $data['document_no'] . '</td>
                    <td>' . $data['revision'] . '</td>
                    <td>' . $data['created_date'] . '</td>
                    <td>' . $data['created_by'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

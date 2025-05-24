<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Mst_customer_item extends CI_Controller
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
        $this->form_validation->set_rules('customer_no', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[mst_customer_item.customer_no]');
        $this->form_validation->set_rules('item_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[mst_customer_item.item_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_customer_item');
        } else {
            redirect('error_access');
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_customer_no = @base64_decode($get['filter_customer_no']);
            $filter_item_id = @base64_decode($get['filter_item_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as customer_name');
            $this->db->from('mst_customer_item a');
            $this->db->join('mst_customer b', 'a.customer_no = b.number');
            $this->db->like('a.customer_no', $filter_customer_no);
            $this->db->like('a.item_id', $filter_item_id);
            $this->db->group_by('b.name');
            $this->db->order_by('b.name', 'ASC');
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
            $number = base64_decode($this->input->get('number'));
            $filter_customer_no = base64_decode($this->input->get('filter_customer_no'));

            $this->db->select('a.*, b.name as customer_name, c.item_name');
            $this->db->from('mst_customer_item a');
            $this->db->join('mst_customer b', 'a.customer_no = b.number');
            $this->db->join('mst_item c', 'a.item_id = c.item_id');
            $this->db->where('b.number', $number);
            $this->db->like('a.customer_no', $filter_customer_no);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $customer_no = base64_decode($this->input->get('customer_no'));

            $this->db->select('a.*, b.item_name, c.name as customer_name');
            $this->db->from('mst_customer_item a');
            $this->db->join('mst_item b', 'a.item_id = b.item_id');
            $this->db->join('mst_customer c', 'a.customer_no = c.number');
            $this->db->where('a.customer_no', $customer_no);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLE HISTORY PRICE
    public function datatableHistories()
    {
        if ($this->input->get()) {
            $customer_no = base64_decode($this->input->get('customer_no'));
            $item_id = base64_decode($this->input->get('item_id'));

            $this->db->select('*');
            $this->db->from('mst_customer_item_history');
            $this->db->where('customer_no', $customer_no);
            $this->db->where('item_id', $item_id);
            $this->db->order_by('valid_date', 'DESC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $mst_customer_item = $this->api->read("mst_customer_item", [], ["customer_no" => $post['customer_no'], "item_id" => $post['item_id']]);
            $mst_customer_item_history = $this->api->read("mst_customer_item_history", [], ["customer_no" => $post['customer_no'], "item_id" => $post['item_id'], "price" => $post['price']]);
           
            if (@$mst_customer_item->customer_no != "") {
                $send = $this->api->update('mst_customer_item', ["customer_no" => $post['customer_no'], "item_id" => $post['item_id']], $post);
                if (@$mst_customer_item_history->customer_no == "") {
                    $send2 = $this->api->create('mst_customer_item_history', $post);
                }
            } else {
                $send = $this->api->create('mst_customer_item', $post, "CSTI", "CSTI");
                $send2 = $this->api->create('mst_customer_item_history', $post);
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
        $send = $this->api->delete('mst_customer_item', $data);
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
                'customer_no' => $data->val($i, 2),
                'item_id' => $data->val($i, 3),
                'price' => $data->val($i, 4),
                'valid_date' => $data->val($i, 5),
                'remark' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/mst_customer_item.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/mst_customer_item.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/mst_customer_item.txt";
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
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $mst_customer_item = $this->api->read('mst_customer_item', [], ["customer_no" => $data['customer_no'], "item_id" => $data['item_id']]);

            if (!empty($mst_customer_item->customer_no)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Customer " . $data['customer_no'] . " is Duplicate Data", "theme" => "error"));
            } elseif (!empty($mst_customer_item->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "customer_no" => $data['customer_no'],
                    "item_id" => $data['item_id'],
                    "price" => $data['price'],
                    "valid_date" => $data['valid_date'],
                    "remark" => $data['remark'],
                );
                $send   = $this->api->create('mst_customer_item', $dataFinal);
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
            header("Content-Disposition: attachment; filename=mst_customer_item_$format.xls");
        }

        $get = $this->input->get();
        $filter_customer_no = @base64_decode($get['filter_customer_no']);
        $filter_item_id = @base64_decode($get['filter_item_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, c.item_name, b.name as customer_name');
        $this->db->from('mst_customer_item a');
        $this->db->join('mst_customer b', 'a.customer_no = b.number');
        $this->db->join('mst_item c', 'a.item_id = c.item_id');
        $this->db->like('a.customer_no', $filter_customer_no);
        $this->db->like('a.item_id', $filter_item_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#mst_customer_item {border-collapse: collapse;width: 100%;font-size: 12px;}#mst_customer_item td, #mst_customer_item th {border: 1px solid #ddd;padding: 2px;}#mst_customer_item tr:nth-child(even){background-color: #f2f2f2;}#mst_customer_item tr:hover {background-color: #ddd;}#mst_customer_item th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                        <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
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
                <h3>MASTER CUSTOMER ITEM</h3>
            </div>
        </center>
        
        <table id="mst_customer_item" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Valid Date</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['customer_no'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['remark'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

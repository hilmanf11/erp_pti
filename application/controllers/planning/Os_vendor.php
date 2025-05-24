<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Os_vendor extends CI_Controller

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

        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[os_vendor.product_no]');

    }



    public function index()

    {

        if (empty($this->session->api_key)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);

            $this->load->view('planning/os_vendor');

        } else {

            redirect('error_access');

        }

    }



    public function readRevision(){

        $month = $this->input->get("month");

        $year = $this->input->get("year");



        $data = $this->api->query("SELECT DISTINCT revision FROM os_vendor WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");

        echo json_encode($data);

    }



    public function readProductFamily(){

        $this->dummy = $this->load->database('dummy', TRUE);

        $query = $this->dummy->query("SELECT * FROM r_prodfam WHERE prod_group = 'Raw Material' and pfm_id != '06' ORDER BY pfm_id ASC");

        $records = $query->result_array();



        echo json_encode($records);

    }



    public function readProducts($pfm_id)

    {

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->dummy = $this->load->database('dummy', TRUE);

        $query = $this->dummy->query("SELECT * FROM mst_item WHERE pfm_id = '$pfm_id' and item_id LIKE '%$post%' ORDER BY item_id ASC");

        $records = $query->result_array();



        echo json_encode($records);

    }



    public function datatables()

    {

        $this->dummy = $this->load->database('dummy', TRUE);

        if ($this->input->get()) {

            //Filter Data

            $filter_month = base64_decode($this->input->get('filter_month'));

            $filter_year = base64_decode($this->input->get('filter_year'));

            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));

            $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));

            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));



            $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));



            $this->dummy->select("b.item_id, b.item_name, c.pfm_name, 

            (CASE WHEN (d.poc_reqqty - a.por_receiveqty) < 0 THEN 0 ELSE (d.poc_reqqty - a.por_receiveqty) END) as stock");

            $this->dummy->from("mst_item b");

            $this->dummy->join("(SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE por_date <= '$filter_cutoff' GROUP BY item_id) a", "a.item_id = b.item_id", 'left');

            $this->dummy->join('r_prodfam c', "b.pfm_id = c.pfm_id and c.prod_group = 'Raw Material'");

            $this->dummy->join("(SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where poc_date <= '$filter_cutoff' GROUP BY item_id) d", "b.item_id = d.item_id", 'left');

            $this->dummy->where('b.stscode_id', "01");
            
            $this->dummy->where("(CASE WHEN (d.poc_reqqty - a.por_receiveqty) < 0 THEN 0 ELSE (d.poc_reqqty - a.por_receiveqty) END) > 0");

            $this->dummy->like('b.pfm_id', $filter_product_family);

            $this->dummy->like('b.item_id', $filter_product_no);

            $this->dummy->group_by('b.item_id');

            $this->dummy->group_by('b.item_name');

            $this->dummy->group_by('c.pfm_name');

            $this->dummy->group_by('a.por_receiveqty');

            $this->dummy->group_by('d.poc_reqqty');

            $this->dummy->order_by('c.pfm_name', 'asc');

            $this->dummy->order_by('(d.poc_reqqty  - a.por_receiveqty)', 'asc');

            $items = $this->dummy->get()->result_array();



            $arr = array();

            foreach ($items as $item) {

                $item_id = $item['item_id'];

                $item_name = $item['item_name'];

                $pfm_name = $item['pfm_name'];

                $qty = $item['stock'];



                $os_vendor = $this->api->read('os_vendor', [], [

                    "product_no" => $item_id,

                    "p_month" => $filter_month,

                    "p_year" => $filter_year,

                    "revision" => $filter_revision

                ]);



                if ($os_vendor) {

                    if($os_vendor->status == "EMPTY"){

                        $status = "GENERATE";

                    }else{

                        $status = $os_vendor->status;

                    }



                    $arr[] = array(

                        "id" => $os_vendor->id,

                        "p_month" => $filter_month,

                        "p_year" => $filter_year,

                        "revision" => $filter_revision,

                        "cutoff" => $cutoff,

                        "product_no" => $item_id,

                        "product_name" => $item_name,

                        "product_family" => $pfm_name,

                        "qty" => round($qty),

                        "actual" => round($os_vendor->actual),

                        "status" => $status,

                        "created_by" => $os_vendor->created_by,

                        "created_date" => $os_vendor->created_date,

                        "updated_by" => $os_vendor->updated_by,

                        "updated_date" => $os_vendor->updated_date,

                    );

                } else {

                    $arr[] = array(

                        "id" => "",

                        "p_month" => $filter_month,

                        "p_year" => $filter_year,

                        "revision" => $filter_revision,

                        "cutoff" => $cutoff,

                        "product_no" => $item_id,

                        "product_name" => $item_name,

                        "product_family" => $pfm_name,

                        "qty" => round($qty),

                        "actual" => round($qty),

                        "status" => "EMPTY",

                    );

                }

            }



            echo json_encode($arr);

        }

    }



    public function create()

    {

        if ($this->input->post()) {

            $post = $this->input->post('data');



            $os_vendor = $this->api->reads('os_vendor', [], [

                "p_month" => $post['p_month'],

                "p_year" => $post['p_year'],

                "revision" => $post['revision'],

                "product_no" => trim($post['product_no'])

            ]);



            if($post['product_no'] != ""){

                if (count($os_vendor) > 0) {

                    $send = $this->api->update('os_vendor', [

                        "p_month" => $post['p_month'],

                        "p_year" => $post['p_year'],

                        "revision" => $post['revision'],

                        "product_no" => trim($post['product_no'])

                    ], $post);



                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));

                } else {

                    $send   = $this->api->create('os_vendor', $post, "RMS", "os_vendor");



                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Saved Successfully", "theme" => "success"));

                }

            }else{

                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Cannot Saved", "theme" => "error"));

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

            $send = $this->api->update('os_vendor', ["id" => $id], $post);

            echo $send;

        } else {

            show_error("Cannot Process your request");

        }

    }



    public function delete()

    {

        $data = $this->input->post();

        $send = $this->api->delete('os_vendor', $data);

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



        $this->api->delete('os_vendor', [

            "p_month" => $data->val(2, 3), 

            "p_year" => $data->val(2, 4), 

            "revision" => $data->val(3, 3),

            "cutoff" => $data->val(4, 3),

        ]);



        for ($i = 6; $i <= $total_row; $i++) {

            $datas[] = array(

                'p_month' => $data->val(2, 3),

                'p_year' => $data->val(2, 4),

                'revision' => $data->val(3, 3),

                "cutoff" => $data->val(4, 3),

                'product_no' => $data->val($i, 2),

                'product_name' => $data->val($i, 3),

                'qty' => $data->val($i, 4),

                'status' => "UPLOAD"

            );

        }



        $datas['total'] = count($datas);

        echo json_encode($datas);



        unlink($_FILES['file_upload']['name']);

    }



    public function uploadcreate()

    {

        if ($this->input->post()) {

            $post = $this->input->post('data');

            $p_month = $post['p_month'];

            $p_year = $post['p_year'];

            $revision = $post['revision'];

            $cutoff = $post['cutoff'];



            $os_vendor = $this->api->reads('os_vendor', [], [

                "p_month" => $post['p_month'],

                "p_year" => $post['p_year'],

                "revision" => $post['revision'],

                "product_no" => trim($post['product_no'])

            ]);



            $postFinal = array(

                "p_month" => $p_month,

                "p_year" => $p_year,

                "revision" => $revision,

                "cutoff" => $cutoff,

                "product_no" => trim($post['product_no']),

                "product_name" => $post['product_name'],

                "product_family" => @$os_vendor[0]->product_family,

                "actual" => $post['qty'],

                "status" => $post['status']

            );



            if($post['product_no'] != ""){

                if (count($os_vendor) > 0) {

                    $this->api->update('os_vendor', [

                        "p_month" => $post['p_month'],

                        "p_year" => $post['p_year'],

                        "revision" => $post['revision'],

                        "product_no" => trim($post['product_no'])

                    ], $postFinal);



                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));

                } else {

                    $this->api->create('os_vendor', $postFinal, "RMS", "os_vendor");

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Created Successfully", "theme" => "success"));

                }

            }else{

                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Saved Failed", "theme" => "error"));

            }

        }

    }



    public function uploadclearFailed()

    {

        @unlink('excel/failed/os_vendor.txt');

    }



    public function uploadcreateFailed()

    {

        if ($this->input->post()) {

            $message = $this->input->post('message');

            $textFailed = fopen('excel/failed/os_vendor.txt', 'a');

            fwrite($textFailed, $message . "\n");

            fclose($textFailed);

        }

    }



    public function uploadDownloadFailed()

    {

        $file = "excel/failed/os_vendor.txt";



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

        $this->dummy = $this->load->database('dummy', TRUE);

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=outstanding_po_$format.xls");

        }



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $filter_month = base64_decode($this->input->get('filter_month'));

        $filter_year = base64_decode($this->input->get('filter_year'));

        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));

        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));

        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));



        $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));



        $this->dummy->select("b.item_id, b.item_name, c.pfm_name, 

        (CASE WHEN (d.poc_reqqty - a.por_receiveqty) < 0 THEN 0 ELSE (d.poc_reqqty - a.por_receiveqty) END) as stock");

        $this->dummy->from("mst_item b");

        $this->dummy->join("(SELECT item_id, SUM(por_receiveqty) as por_receiveqty FROM por WHERE por_date <= '$filter_cutoff' GROUP BY item_id) a", "a.item_id = b.item_id", 'left');

        $this->dummy->join('r_prodfam c', "b.pfm_id = c.pfm_id and c.prod_group = 'Raw Material'");

        $this->dummy->join("(SELECT item_id, SUM(poc_reqqty) as poc_reqqty from poc where poc_date <= '$filter_cutoff' GROUP BY item_id) d", "b.item_id = d.item_id", 'left');

        $this->dummy->where('b.stscode_id', "01");

        $this->dummy->like('b.pfm_id', $filter_product_family);

        $this->dummy->like('b.item_id', $filter_product_no);

        $this->dummy->group_by('b.item_id');

        $this->dummy->group_by('b.item_name');

        $this->dummy->group_by('c.pfm_name');

        $this->dummy->group_by('a.por_receiveqty');

        $this->dummy->group_by('d.poc_reqqty');

        $this->dummy->order_by('c.pfm_name', 'asc');

        $this->dummy->order_by('(d.poc_reqqty  - a.por_receiveqty)', 'asc');

        $items = $this->dummy->get()->result_array();



        $records = array();

        foreach ($items as $item) {

            $item_id = $item['item_id'];

            $item_name = $item['item_name'];

            $pfm_name = $item['pfm_name'];

            $qty = $item['stock'];



            $os_vendor = $this->api->read('os_vendor', [], [

                "product_no" => $item_id,

                "p_month" => $filter_month,

                "p_year" => $filter_year,

                "revision" => $filter_revision

            ]);



            if ($os_vendor) {

                if($os_vendor->status == "EMPTY"){

                    $status = "GENERATE";

                }else{

                    $status = $os_vendor->status;

                }



                $records[] = array(

                    "p_month" => $filter_month,

                    "p_year" => $filter_year,

                    "revision" => $filter_revision,

                    "cutoff" => $cutoff,

                    "product_no" => $item_id,

                    "product_name" => $item_name,

                    "product_family" => $pfm_name,

                    "qty" => $qty,

                    "actual" => $os_vendor->actual,

                    "status" => $status,

                );

            } else {

                $records[] = array(

                    "p_month" => $filter_month,

                    "p_year" => $filter_year,

                    "revision" => $filter_revision,

                    "cutoff" => $cutoff,

                    "product_no" => $item_id,

                    "product_name" => $item_name,

                    "product_family" => $pfm_name,

                    "qty" => $qty,

                    "actual" => $qty,

                    "status" => "EMPTY",

                );

            }

        }



        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>

        <table style="font-size: 12px;">

            <tr>

                <td width="80">MONTH</td>

                <td width="10">:</td>

                <td>' . $filter_month . '<td>

            </tr>

            <tr>

                <td width="80">YEAR</td>

                <td width="10">:</td>

                <td>' . $filter_year . '<td>

            </tr>

            <tr>

                <td width="80">REVISION</td>

                <td width="10">:</td>

                <td>' . $filter_revision . '<td>

            </tr>

        </table>

        <table id="customers" border="1">

            <tr>

                <th width="20">No</th>

                <th>Product No</th>

                <th>Product Name</th>

                <th>Product Family</th>

                <th>Stock</th>

                <th>Actual</th>

                <th>Status</th>

            </tr>';

        $no = 1;

        foreach ($records as $data) {

            $html .= '  <tr>

                            <td>' . $no . '</td>

                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>

                            <td>' . $data['product_name'] . '</td>

                            <td>' . $data['product_family'] . '</td>

                            <td>' . $data['qty'] . '</td>

                            <td>' . $data['actual'] . '</td>

                            <td>' . $data['status'] . '</td>

                        </tr>';

            $no++;

        }



        $html .= '</table></body></html>';

        echo $html;

    }

}


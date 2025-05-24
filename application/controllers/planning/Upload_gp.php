<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Upload_gp extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[fg_stock.item_id]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/upload_gp');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('upload_gp', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM upload_gp WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
    }

    public function readProductFamily(){
        $query = $this->db->query("SELECT number as pfm_id, name as pfm_name FROM mst_item_family WHERE number != '06' ORDER BY number ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readProducts($pfm_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $query = $this->db->query("SELECT * FROM mst_item_raw WHERE pfm_id = '$pfm_id' and item_id LIKE '%$post%' ORDER BY item_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function datatables()
    {
        // $this->gp = $this->load->database('gp', TRUE);
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            $filter_plant = trim(base64_decode($this->input->get('filter_plant')));
            $filter_status = trim(base64_decode($this->input->get('filter_status')));

            $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));

            $this->db->select('a.*, b.item_supplier, c.name as plant_name, d.name as supplier_name, d.origin, e.name as maker_name, e.type as maker_type, f.name as product_family');
            $this->db->from('supplier_items a');
            $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
            $this->db->join('plants c', 'a.plant = c.number');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join('mst_maker e', 'a.maker_code = e.number');
            $this->db->join('mst_item_family f', 'b.pfm_id = f.number');
            $this->db->where('a.plant', $filter_plant);
            if($filter_product_family != ""){
                $this->db->where('b.pfm_id', $filter_product_family);
            }
            if($filter_product_no != ""){
                $this->db->where('a.item_id', $filter_product_no);
            }
            $this->db->order_by('a.item_id', 'asc');
            $items = $this->db->get()->result_array();

            $arr = array();
            foreach ($items as $item) {
                $item_id = $item['item_id'];

                $upload_gp = $this->api->read('upload_gp', [], [
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "item_id" => $item_id,
                    "supplier_id" => $item['supplier_id'],
                    "plant" => $item['plant'],
                ]);

                if ($upload_gp) {
                    if($upload_gp->status == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $upload_gp->status;
                    }

                    if($filter_status == "GENERATE"){
                        $arr[] = array(
                            "p_month" => $filter_month,
                            "p_year" => $filter_year,
                            "revision" => $filter_revision,
                            "plant" => $item['plant'],
                            "plant_name" => $item['plant_name'],
                            "item_id" => $item_id,
                            "item_supplier" => $item['item_supplier'],
                            "supplier_id" => $item['supplier_id'],
                            "supplier_name" => $item['supplier_name'],
                            "product_family" => $item['product_family'],
                            "origin" => $item['origin'],
                            "maker_name" => $item['maker_name'],
                            "maker_type" => $item['maker_type'],
                            "mpq" => $upload_gp->mpq,
                            "moq" => $upload_gp->moq,
                            "share_order" => $upload_gp->share_order,
                            "leadtime" => $upload_gp->leadtime,
                            "currency" => $item['currency'],
                            "price" => $upload_gp->price,
                            "status" => $status,
                            "created_by" => $upload_gp->created_by,
                            "created_date" => $upload_gp->created_date,
                            "updated_by" => $upload_gp->updated_by,
                            "updated_date" => $upload_gp->updated_date,
                        );
                    }elseif($filter_status == ""){
                        $arr[] = array(
                            "p_month" => $filter_month,
                            "p_year" => $filter_year,
                            "revision" => $filter_revision,
                            "plant" => $item['plant'],
                            "plant_name" => $item['plant_name'],
                            "item_id" => $item_id,
                            "item_supplier" => $item['item_supplier'],
                            "supplier_id" => $item['supplier_id'],
                            "supplier_name" => $item['supplier_name'],
                            "product_family" => $item['product_family'],
                            "origin" => $item['origin'],
                            "maker_name" => $item['maker_name'],
                            "maker_type" => $item['maker_type'],
                            "mpq" => $item['mpq'],
                            "moq" => $item['moq'],
                            "share_order" => $item['share_order'],
                            "leadtime" => $item['leadtime'],
                            "currency" => $item['currency'],
                            "price" => $item['price'],
                            "status" => $status,
                            "created_by" => $upload_gp->created_by,
                            "created_date" => $upload_gp->created_date,
                            "updated_by" => $upload_gp->updated_by,
                            "updated_date" => $upload_gp->updated_date,
                        );
                    }
                } else {
                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "plant" => $item['plant'],
                        "plant_name" => $item['plant_name'],
                        "item_id" => $item_id,
                        "item_supplier" => $item['item_supplier'],
                        "supplier_id" => $item['supplier_id'],
                        "supplier_name" => $item['supplier_name'],
                        "product_family" => $item['product_family'],
                        "origin" => $item['origin'],
                        "maker_name" => $item['maker_name'],
                        "maker_type" => $item['maker_type'],
                        "mpq" => $item['mpq'],
                        "moq" => $item['moq'],
                        "share_order" => $item['share_order'],
                        "leadtime" => $item['leadtime'],
                        "currency" => $item['currency'],
                        "price" => $item['price'],
                        "status" => "EMPTY",
                    );
                }
            }

            die(json_encode($arr));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $upload_gp = $this->api->read('upload_gp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_id" => trim($post['item_id']),
                "supplier_id" => trim($post['supplier_id']),
                "plant" => trim($post['plant']),
            ]);

            if($post['item_id'] != ""){
                if ($upload_gp) {
                    $this->api->update('upload_gp', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_id" => trim($post['item_id']),
                        "supplier_id" => trim($post['supplier_id']),
                        "plant" => trim($post['plant']),
                    ], [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "plant" => $post['plant'],
                        "item_id" => $post['item_id'],
                        "supplier_id" => $post['supplier_id'],
                        "mpq" => $post['mpq'],
                        "moq" => $post['moq'],
                        "share_order" => $post['share_order'],
                        "leadtime" => $post['leadtime'],
                        "currency" => $post['currency'],
                        "price" => $post['price'],
                        "status" => $post['status'],
                    ]);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $send = $this->api->create('upload_gp', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "plant" => $post['plant'],
                        "item_id" => $post['item_id'],
                        "supplier_id" => $post['supplier_id'],
                        "mpq" => $post['mpq'],
                        "moq" => $post['moq'],
                        "share_order" => $post['share_order'],
                        "leadtime" => $post['leadtime'],
                        "currency" => $post['currency'],
                        "price" => $post['price'],
                        "status" => $post['status'],
                    ], "RMS", "upload_gp");

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['item_id'] . " Cannot Saved", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('upload_gp', $data);
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

        $send = $this->api->delete('fg_stock', [
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
                'item_id' => $data->val($i, 2),
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

            $upload_gp = $this->api->reads('upload_gp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_id" => trim($post['item_id'])
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "item_id" => trim($post['item_id']),
                "product_name" => $post['item_name'],
                "product_family" => @$upload_gp[0]->product_family,
                "actual" => $post['qty'],
                "status" => $post['status']
            );

            if($post['item_id'] != ""){
                if (count($upload_gp) > 0) {
                    $send   = $this->api->update('upload_gp', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "item_id" => trim($post['item_id'])
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Updated Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Product No " . $post['item_id'] . " Not Generate", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['item_id'] . " Saved Failed", "theme" => "error"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/fg_stock.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/fg_stock.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/fg_stock.txt";

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
        $this->pg = $this->load->database('pg', TRUE);
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=procurement_BEI_$format.xls");
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
        $filter_plant = trim(base64_decode($this->input->get('filter_plant')));

        $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));

        $this->db->select('a.*, b.item_name, b.item_supplier, c.name as product_family, d.name as supplier_name, d.origin, f.name as maker_name, f.type as maker_type');
        $this->db->from('upload_gp a');
        $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.supplier_id = e.supplier_id and a.item_id = e.item_id and a.plant = e.plant');
        $this->db->join('mst_maker f', 'e.maker_code = f.number');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.plant', $filter_plant);
        if($filter_product_family != ""){
            $this->db->where('b.pfm_id', $filter_product_family);
        }
        if($filter_product_no != ""){
            $this->db->where('a.item_id', $filter_product_no);
        }
        $this->db->order_by('a.item_id', 'asc');
        $records = $this->db->get()->result();
        

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <h2>PROCUREMENT BEI</h2>
        </center>
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
        <br>
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Plant</th>
                <th>Part No</th>
                <th>Part No Vendor</th>
                <th>Part Name</th>
                <th>Product Family</th>
                <th>Supplier</th>
                <th>Origin</th>
                <th>Maker</th>
                <th>Maker Type</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Share Order</th>
                <th>Leadtime</th>
                <th>Currency</th>
                <th>Price</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data->plant . '</td>
                            <td style="mso-number-format:\@;">' . $data->item_id . '</td>
                            <td style="mso-number-format:\@;">' . $data->item_supplier . '</td>
                            <td style="mso-number-format:\@;">' . $data->item_name . '</td>
                            <td>' . $data->product_family . '</td>
                            <td>' . $data->supplier_name . '</td>
                            <td>' . $data->origin . '</td>
                            <td>' . $data->maker_name . '</td>
                            <td>' . $data->maker_type . '</td>
                            <td>' . $data->mpq . '</td>
                            <td>' . $data->moq . '</td>
                            <td>' . $data->share_order . '</td>
                            <td>' . $data->leadtime . '</td>
                            <td>' . $data->currency . '</td>
                            <td>' . $data->price . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

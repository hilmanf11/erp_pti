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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[fg_stock.product_no]');
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
        $this->gp = $this->load->database('gp', TRUE);
        $query = $this->gp->query("SELECT * FROM commodities WHERE category_id = '1' ORDER BY name ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readProducts($pfm_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->gp = $this->load->database('gp', TRUE);
        $query = $this->gp->query("SELECT maker_part FROM database_parts WHERE commodity_id = '$pfm_id' and maker_part LIKE '%$post%' ORDER BY maker_part ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    function callAPI($method, $url, $data){
        $curl = curl_init();
        switch ($method){
           case "POST":
              curl_setopt($curl, CURLOPT_POST, 1);
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              break;
           case "PUT":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
              break;
           default:
              if ($data)
                 $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        //    'APIKEY: 111111111111111111111',
           'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
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
            $filter_product_no = $this->input->get('filter_product_no');
            $filter_plant = trim(base64_decode($this->input->get('filter_plant')));
            $filter_status = trim(base64_decode($this->input->get('filter_status')));

            $cutoff =  date('Y-m-d', strtotime('-1 day', strtotime($filter_cutoff)));

            $url = "https://gpai.aeconsys.com/api/share-order?plant=$filter_plant&productFamily=$filter_product_family&productNo=$filter_product_no";

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            //    'APIKEY: 111111111111111111111',
                'Content-Type: application/json',
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // EXECUTE:
            $result = curl_exec($curl);
            if(!$result){
                die("Connection Failure");
            }
            curl_close($curl);

            $gpai = json_decode($result, true);
            $items = $gpai['data'];
            $arr = array();

            foreach ($items as $item) {
                $item_id = $item['maker_part'];
                $item_name = "";
                $pfm_name = $item['product_family_name'];

                // $percentage = ($item['landed_cost_percentage'] + $item['import_duty_percentage'] + $item['bod_policy_percentage']);
                // $percentagePrice = (($item['price'] * $percentage) / 100);
                // $mkt_price = round($item['price'] + $percentagePrice, 6);

                if(empty($item['plant_code'])){
                    $plant = "ALL";
                    $plant_name = "ALL PLANT";
                }else{
                    $plant = $item['plant_code'];
                    $plant_name = $item['plant_name'];
                }

                $upload_gp = $this->api->read('upload_gp', [], [
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "product_no" => $item_id,
                    "supplier_no" => $item['supplier_code'],
                    "plant" => $plant,
                    "price" => $item['price'],
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
                            "plant" => $plant,
                            "plant_name" => $plant_name,
                            "product_no" => $item_id,
                            "supplier_part_code" => $upload_gp->supplier_part_code,
                            "product_family_no" => $item['product_family_no'],
                            "product_family" => $pfm_name,
                            "maker" => $item['maker_code'],
                            "maker_name" => $item['maker_name'],
                            "supplier_no" => $item['supplier_code'],
                            "supplier_name" => $item['supplier_name'],
                            "country_origin" => $item['country_origin'],
                            "supplier_type" => $item['maker_type'],
                            "mpq" => $item['mpq'],
                            "moq" => $item['moq'],
                            "share_order" => $item['percentage'],
                            "leadtime" => $item['lead_time'],
                            "currency" => $item['currency'],
                            "price" => $item['price'],
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
                            "plant" => $plant,
                            "plant_name" => $plant_name,
                            "product_no" => $item_id,
                            "supplier_part_code" => $upload_gp->supplier_part_code,
                            "product_family_no" => $item['product_family_no'],
                            "product_family" => $pfm_name,
                            "maker" => $item['maker_code'],
                            "maker_name" => $item['maker_name'],
                            "supplier_no" => $item['supplier_code'],
                            "supplier_name" => $item['supplier_name'],
                            "country_origin" => $item['country_origin'],
                            "supplier_type" => $item['maker_type'],
                            "mpq" => $item['mpq'],
                            "moq" => $item['moq'],
                            "share_order" => $item['percentage'],
                            "leadtime" => $item['lead_time'],
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
                    // if($filter_status == "EMPTY"){
                        $mst_item = $this->api->read("mst_item_raw", [], ["item_gp" => $item_id]);

                        $arr[] = array(
                            "p_month" => $filter_month,
                            "p_year" => $filter_year,
                            "revision" => $filter_revision,
                            "plant" => $plant,
                            "plant_name" => $plant_name,
                            "product_no" => $item_id,
                            "supplier_part_code" => @$mst_item->item_supplier,
                            "product_family_no" => $item['product_family_no'],
                            "product_family" => $pfm_name,
                            "maker" => $item['maker_code'],
                            "maker_name" => $item['maker_name'],
                            "supplier_no" => $item['supplier_code'],
                            "supplier_name" => $item['supplier_name'],
                            "country_origin" => $item['country_origin'],
                            "supplier_type" => $item['maker_type'],
                            "mpq" => $item['mpq'],
                            "moq" => $item['moq'],
                            "share_order" => $item['percentage'],
                            "leadtime" => $item['lead_time'],
                            "currency" => $item['currency'],
                            "price" => $item['price'],
                            "status" => "EMPTY",
                        );
                    // }
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
                "product_no" => trim($post['product_no']),
                "supplier_no" => trim($post['supplier_no']),
                "plant" => trim($post['plant'])
            ]);

            if($post['product_no'] != ""){
                if ($upload_gp) {
                    $this->api->update('upload_gp', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => trim($post['product_no']),
                        "supplier_no" => trim($post['supplier_no']),
                        "plant" => trim($post['plant'])
                    ], [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "plant" => $post['plant'],
                        "plant_name" => $post['plant_name'],
                        "product_no" => $post['product_no'],
                        "supplier_part_code" => $post['supplier_part_code'],
                        "product_family" => $post['product_family'],
                        // "maker" => $post['maker'],
                        // "maker_name" => $post['maker_name'],
                        "supplier_no" => $post['supplier_no'],
                        "supplier_name" => $post['supplier_name'],
                        "country_origin" => $post['country_origin'],
                        "supplier_type" => $post['supplier_type'],
                        "mpq" => $post['mpq'],
                        "moq" => $post['moq'],
                        // "reference" => $post['reference'],
                        "share_order" => $post['share_order'],
                        "leadtime" => $post['leadtime'],
                        "currency" => $post['currency'],
                        "price" => $post['price'],
                        "status" => $post['status'],
                    ]);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $send = $this->api->create('upload_gp', $post, "RMS", "upload_gp");

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Cannot Saved", "theme" => "error"));
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

            $upload_gp = $this->api->reads('upload_gp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['item_id']),
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
                        "product_no" => trim($post['product_no'])
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Not Generate", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Saved Failed", "theme" => "error"));
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

        $this->db->select('*');
        $this->db->from('upload_gp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->where('plant', $filter_plant);
        if($filter_product_family != ""){
            $this->db->where('product_family_no', $filter_product_family);
        }
        if($filter_product_no != ""){
            $this->db->where('product_no', $filter_product_no);
        }
        $this->db->order_by('product_no', 'asc');
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
                <th>Product No</th>
                <th>Product Family</th>
                <th>Maker</th>
                <th>Supplier</th>
                <th>Origin</th>
                <th>Type</th>
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
                            <td style="mso-number-format:\@;">' . $data->product_no . '</td>
                            <td>' . $data->product_family . '</td>
                            <td>' . $data->maker_name . '</td>
                            <td>' . $data->supplier_name . '</td>
                            <td>' . $data->country_origin . '</td>
                            <td>' . $data->supplier_type . '</td>
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

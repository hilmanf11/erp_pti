<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Marketing_price extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/marketing_price');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->reads('supplier_items', ["name" => $post]);
        echo json_encode($send);
    }

    public function readVendor()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT DISTINCT item_supplier FROM supplier_items WHERE item_supplier != '' ORDER BY item_supplier ASC");
        echo json_encode($send);
    }

    public function readShareOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT DISTINCT share_order FROM supplier_items ORDER BY share_order ASC");
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT a.item_id, a.item_name FROM mst_item_raw a WHERE a.item_id LIKE '%$post%' GROUP BY a.item_id");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_plant  = base64_decode($this->input->get('filter_plant'));
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_check = $this->input->get('filter_check');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select('a.*, g.valid_date, g.price, g.currency, c.item_id, c.item_name, c.item_supplier, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number');
        $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id)) g", 'a.id = g.supplier_item_id', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_id FROM supplier_items GROUP BY item_id HAVING COUNT(*) > 1) h", 'a.item_id = h.item_id');
        }
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_category != "") {
            $this->db->like('c.pfm_id', $filter_category);
        }
        $this->db->like('a.supplier_id', $filter_suppliers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('f.number', $filter_maker);
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        } else {
            $this->db->where("a.share_order > 0");
        }
        if ($filter_check == "false") {
            $this->db->order_by('b.name', 'ASC');
        }
        $this->db->order_by('a.item_id', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $arr = [];
        foreach ($records as $record) {
            if ($record['type'] == 'IMPORT') {
                $assurance = $this->api->read('mst_assurance', [], ["type" => 'IMPORT']);
                $transportation = $this->api->read('mst_transportation', [], ["type" => 'IMPORT']);
            } else {
                $assurance = $this->api->read('mst_assurance', [], ["type" => 'LOCAL']);
                $transportation = $this->api->read('mst_transportation', [], ["type" => 'LOCAL']);
            }

            $mst_import_duty = $this->api->read('mst_import_duty', [], ["plant" => $record['plant'], "supplier_id" => $record['supplier_id'], "item_id" => $record['item_id']]);
            if (!empty(@$mst_import_duty->percentage)) {
                $import_duty_price = $record['price'] * (@$mst_import_duty->percentage / 100);
            } else {
                $import_duty_price = 0;
            }

            $assurance_price = $record['price'] * @$assurance->percentage;
            $transportation_price = $record['price'] * @$transportation->percentage;
            $total_price = $record['price'] + $assurance_price + $transportation_price + $import_duty_price;
            $arr[] = array(
                "plant" => $record['plant'],
                "supplier_name" => $record['supplier_name'],
                "item_supplier" => $record['item_supplier'],
                "item_id" => $record['item_id'],
                "item_name" => $record['item_name'],
                "type" => $record['type'],
                "mpq" => $record['mpq'],
                "moq" => $record['moq'],
                "leadtime" => $record['leadtime'],
                "maker_name" => $record['maker_name'],
                "maker_type" => $record['maker_type'],
                "price" => $record['price'],
                "currency" => $record['currency'],
                "assurance_price" => $assurance_price,
                "transportation_price" => $transportation_price,
                "import_duty_percentage" => @$mst_import_duty->percentage,
                "import_duty_price" => $import_duty_price,
                "total_price" => $total_price,
            );
        }
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);

        echo json_encode($result);
    }

    public function datatableHistory()
    {
        $id = base64_decode($this->input->get('id'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select('a.*, c.item_id, c.item_name, c.item_supplier, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        $this->db->from('supplier_items_log a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number');
        $this->db->where('a.supplier_item_id', $id);
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.item_id', 'ASC');
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

    public function datatablePrice()
    {
        $id = base64_decode($this->input->get('id'));

        $this->db->select('a.*');
        $this->db->from('supplier_item_prices a');
        $this->db->where('supplier_item_id', $id);
        $this->db->order_by('valid_date', 'DESC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);
    }

    public function datatablePriceLogs()
    {
        $id = base64_decode($this->input->get('supplier_item_id'));

        $this->db->select('a.*');
        $this->db->from('supplier_item_prices_log a');
        $this->db->where('supplier_item_id', $id);
        $this->db->order_by('created_date', 'DESC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $supplier_items = $this->api->read('supplier_items', [], ["supplier_id" => $post['supplier_id'], "item_id" => $post['item_id'], "plant" => $post['plant']]);
                if (!$supplier_items) {
                    $send = $this->api->create('supplier_items', $post, "SPI", "supplier_items");
                    echo $send;
                } else {
                    show_error("Duplicate Data");
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createPrice()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $start_date = $post['start_date'];
            $valid_date = $post['valid_date'];
            $supplier_item_id = $post['supplier_item_id'];
            $check = $this->api->query("SELECT * FROM supplier_item_prices WHERE supplier_item_id = '$supplier_item_id' and valid_date > '$start_date'");

            if ($start_date > $valid_date) {
                show_error("Start date and valid date not valid");
            } else {
                if (count($check) > 0) {
                    show_error("Start date must be bigger previous valid date");
                } else {
                    $send = $this->api->create('supplier_item_prices', $post, "SPI", "supplier_items");
                    echo $send;
                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $read = $this->api->read("supplier_items", ["id" => $id]);

            $send = $this->api->update('supplier_items', ["id" => $id], $post);
            if ($send) {
                $history = array(
                    "supplier_item_id" => $id,
                    "plant" => $read->plant,
                    "supplier_id" => $read->supplier_id,
                    "item_id" => $read->item_id,
                    "item_supplier" => $read->item_supplier,
                    "maker_code" => $read->maker_code,
                    "maker_type" => $read->maker_type,
                    "mpq" => $read->mpq,
                    "moq" => $read->moq,
                    "price" => $read->price,
                    "currency" => $read->currency,
                    "landed_cost" => $read->landed_cost,
                    "import_duty" => $read->import_duty,
                    "share_order" => $read->share_order,
                    "leadtime" => $read->leadtime,
                    "remarks" => $read->remarks,
                );

                $this->api->create("supplier_items_log", $history, "SPL", "supplier_items_log");
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updatePrice()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $start_date = $post['start_date'];
            $valid_date = $post['valid_date'];

            $price = $this->api->read('supplier_item_prices', [], ["id" => $id]);

            if ($start_date > $valid_date) {
                show_error("Start date and valid date not valid");
            } else {
                $send = $this->api->update('supplier_item_prices', ["id" => $id], $post);
                $history = array(
                    "supplier_item_id" => $id,
                    "price" => @$price->price,
                    "currency" => @$price->currency,
                    "start_date" => @$price->start_date,
                    "valid_date" => @$price->valid_date,
                    "remarks" => @$price->remarks,
                );

                // die(json_encode($history));
                $this->api->create("supplier_item_prices_log", $history, "JUTA", "supplier_items_log");
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $this->api->delete('supplier_items_log', ["supplier_item_id" => $data['id']]);
        $this->api->delete('supplier_item_prices', ["supplier_item_id" => $data['id']]);
        $this->api->delete('supplier_item_prices_log', ["supplier_item_id" => $data['id']]);

        $send = $this->api->delete('supplier_items', $data);
        echo $send;
    }

    public function deletePrice()
    {
        $data = $this->input->post();
        $send = $this->api->delete('supplier_item_prices', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        // error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'plant' => $data->val($i, 2),
                'item_id' => $data->val($i, 3),
                'supplier_number' => $data->val($i, 4),
                'coo_name' => $data->val($i, 5),
                'mpq' => $data->val($i, 6),
                'moq' => $data->val($i, 7),
                'currency' => $data->val($i, 8),
                'price' => $data->val($i, 9),
                'maker_code' => $data->val($i, 10),
                'maker_type' => $data->val($i, 11),
                'share_order' => $data->val($i, 12),
                'valid_date' => $data->val($i, 13),
                'leadtime' => $data->val($i, 14),
                'landed_cost' => $data->val($i, 15),
                'import_duty' => $data->val($i, 16),
                'remarks' => $data->val($i, 17),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/supplier_items.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/supplier_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/supplier_items.txt";
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
            //Cek Process Number
            $item = $this->api->read('mst_item_raw', [], ["item_id" => $data['item_id']]);
            $plant = $this->api->read('plants', [], ["number" => $data['plant']]);
            $supplier = $this->api->read('suppliers', [], ["number" => $data['supplier_number']]);
            $maker = $this->api->read('mst_maker', [], ["number" => $data['maker_code']]);
            $supplier_items = $this->api->read('supplier_items', [], ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']]);

            if (empty($item->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Vendor " . $data['supplier_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($plant->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Plant " . $data['plant'] . " Not Found", "theme" => "error"));
            } elseif (empty($maker->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Maker " . $data['maker_code'] . " Not Found", "theme" => "error"));
            } elseif (empty($data['currency'])) {
                echo json_encode(array("title" => "Not Found", "message" => "Currency Still Empty", "theme" => "error"));
            } elseif (!empty($supplier_items->item_id)) {
                $postFinal = array(
                    "item_id" => $item->item_id,
                    "supplier_id" => $supplier->id,
                    "coo_name" => $data['coo_name'],
                    "plant" => $data['plant'],
                    "maker_code" => $data['maker_code'],
                    "maker_type" => $data['maker_code'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "price" => $data['price'],
                    "currency" => $data['currency'],
                    "landed_cost" => $data['landed_cost'],
                    "import_duty" => $data['import_duty'],
                    "share_order" => $data['share_order'],
                    "valid_date" => $data['valid_date'],
                    "leadtime" => $data['leadtime'],
                    "remarks" => $data['remarks']
                );

                $send = $this->api->update('supplier_items', ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']], $postFinal);
                echo $send;
            } else {
                $postFinal = array(
                    "item_id" => $item->item_id,
                    "supplier_id" => $supplier->id,
                    "coo_name" => $data['coo_name'],
                    "plant" => $data['plant'],
                    "maker_code" => $data['maker_code'],
                    "maker_type" => $data['maker_code'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "price" => $data['price'],
                    "currency" => $data['currency'],
                    "landed_cost" => $data['landed_cost'],
                    "import_duty" => $data['import_duty'],
                    "share_order" => $data['share_order'],
                    "valid_date" => $data['valid_date'],
                    "leadtime" => $data['leadtime'],
                    "remarks" => $data['remarks']
                );
                $send   = $this->api->create('supplier_items', $postFinal, "SPI", "supplier_items");
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
            header("Content-Disposition: attachment; filename=supplier_items_$format.xls");
        }

        $filter_plant  = base64_decode($this->input->get('filter_plant'));
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_check = $this->input->get('filter_check');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, g.valid_date, g.price, g.currency, c.item_id, c.item_name, c.item_supplier, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number');
        $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id)) g", 'a.id = g.supplier_item_id', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_id FROM supplier_items GROUP BY item_id HAVING COUNT(*) > 1) h", 'a.item_id = h.item_id');
        }
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_category != "") {
            $this->db->like('c.pfm_id', $filter_category);
        }
        $this->db->like('a.supplier_id', $filter_suppliers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('f.number', $filter_maker);
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        }
        if ($filter_check == "false") {
            $this->db->order_by('b.name', 'ASC');
        }
        $this->db->order_by('a.item_id', 'ASC');
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
                            <small>MASTER SUPPLIER ITEM</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Plant</th>
                    <th>Vendor ID</th>
                    <th>Vendor Name</th>
                    <th>Part Vendor</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Vendor Type</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>Leadtime (Days)</th>
                    <th>Maker</th>
                    <th>Maker Type</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Insurance</th>
                    <th>Transportation</th>
                    <th>Import Duty</th>
                    <th>Total Price</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['type'] == 'IMPORT') {
                $assurance = $this->api->read('mst_assurance', [], ["type" => 'IMPORT']);
                $transportation = $this->api->read('mst_transportation', [], ["type" => 'IMPORT']);
            } else {
                $assurance = $this->api->read('mst_assurance', [], ["type" => 'LOCAL']);
                $transportation = $this->api->read('mst_transportation', [], ["type" => 'LOCAL']);
            }

            $mst_import_duty = $this->api->read('mst_import_duty', [], ["plant" => $data['plant'], "supplier_id" => $data['supplier_id'], "item_id" => $data['item_id']]);
            if (!empty(@$mst_import_duty->percentage)) {
                $import_duty_price = $data['price'] * (@$mst_import_duty->percentage / 100);
            } else {
                $import_duty_price = 0;
            }
            $assurance_price = $data['price'] * @$assurance->percentage;
            $transportation_price = $data['price'] * @$transportation->percentage;
            $total_price = $data['price'] + $assurance_price + $transportation_price + $import_duty_price;
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['plant'] . '</td>
                        <td>' . $data['supplier_number'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['moq'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['maker_name'] . '</td>
                        <td>' . $data['maker_type'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        
                        <td>' . $assurance_price . '</td>
                        <td>' . $transportation_price . '</td>
                        <td>' . $import_duty_price . '</td>
                        <td>' . $total_price . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

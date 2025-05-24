<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_confirm extends CI_Controller
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
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Item ID', 'required|min_length[2]|max_length[30]|is_unique[purchase_confirm.item_id]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/delivery_confirm');
        } else {
            redirect('error_access');
        }
    }

    public function readSupplier(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $data = $this->api->query("SELECT DISTINCT a.supplier_id, b.number, b.name FROM purchase_orders a JOIN suppliers b ON a.supplier_id = b.id WHERE a.status = 0 ORDER BY b.name asc");
        echo json_encode($data);
    }

    public function readProductFamily($supplier_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($supplier_id);

        $data = $this->api->query("SELECT DISTINCT product_family_name FROM purchase_orders WHERE supplier_id = '$supplier_id' and `status` = 0 ORDER BY product_family_name asc");
        echo json_encode($data);
    }

    public function readPo($supplier_id, $product_family){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = base64_decode($supplier_id);
        $product_family = base64_decode($product_family);

        $data = $this->api->query("SELECT DISTINCT purchase_order_no, purchase_order_date FROM purchase_orders WHERE supplier_id = '$supplier_id' and product_family_name = '$product_family' and status = 0 ORDER BY product_family_name asc");
        echo json_encode($data);
    }

    public function readProducts($po_no){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $po_no = base64_decode($po_no);

        $data = $this->api->query("SELECT a.*, COALESCE(SUM(b.delivery), 0) as delivery FROM purchase_orders a LEFT JOIN purchase_confirm b ON a.item_id = b.item_id and a.purchase_order_no = b.po_no WHERE a.purchase_order_no = '$po_no' and a.status = 0 and a.item_id LIKE '%$post%' GROUP BY a.item_id ORDER BY a.item_id asc");
        echo json_encode($data);
    }

    public function readFilterPo($supplier_id, $product_family){
        $supplier_id = base64_decode($supplier_id);
        $product_family = base64_decode($product_family);

        $data = $this->api->query("SELECT DISTINCT po_no FROM purchase_confirm WHERE supplier_id = '$supplier_id' and product_family = '$product_family' ORDER BY po_no asc");
        echo json_encode($data);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_status = base64_decode($this->input->get('filter_status'));
            $filter_po_no = base64_decode($this->input->get('filter_po_no'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('purchase_confirm a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.po_no', $filter_po_no);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.item_id', $filter_item_id);
            $this->db->order_by('a.supplier_id', 'ASC');
            $this->db->order_by('a.po_no', 'ASC');
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
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $item_id = $post['item_id'];
            $po_no = $post['po_no'];

            $purchase_confirm = $this->api->query("SELECT item_id, po_no, SUM(delivery) as delivery FROM purchase_confirm WHERE item_id = '$item_id' and po_no = '$po_no'");
            $purchase_confirm2 = $this->api->reads("purchase_confirm", [], ["item_id" => $item_id, "po_no" => $po_no, "etd" => $post['etd']]);

            if(count($purchase_confirm2) > 0){
                show_error("Duplicate Data, ETD must be different");
            }else{
                if($post['qty'] == (@$purchase_confirm[0]->delivery + $post['delivery'])){
                    $send = $this->api->create('purchase_confirm', $post, "PCF", "JUTA");
                    $this->db->update('purchase_orders', ["status" => 1], ["purchase_order_no" => $post['po_no'], "item_id" => $post['item_id']]);
                    echo $send;
                }elseif($post['qty'] > (@$purchase_confirm[0]->delivery + $post['delivery'])){
                    $send = $this->api->create('purchase_confirm', $post, "PCF", "JUTA");
                    echo $send;
                }else{
                    show_error("Cannot save because Delivery > Qty PO");
                }
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
            $send = $this->api->update('purchase_confirm', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('purchase_confirm', $data);
        $this->db->update('purchase_orders', ["status" => 0], ["purchase_order_no" => $data['po_no'], "item_id" => $data['item_id']]);
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
                'po_no' => $data->val($i, 2),
                'item_id' => $data->val($i, 3),
                'etd' => $data->val($i, 4),
                'eta' => $data->val($i, 5),
                'delivery' => $data->val($i, 6),
                'document_no' => $data->val($i, 7),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            $item_id = $data['item_id'];
            $po_no = $data['po_no'];

            $purchase_orders = $this->api->read("purchase_orders", [], ["item_id" => $item_id, "purchase_order_no" => $po_no]);
            $purchase_confirm = $this->api->query("SELECT item_id, po_no, SUM(delivery) as delivery FROM purchase_confirm WHERE item_id = '$item_id' and po_no = '$po_no'");
            $purchase_confirm2 = $this->api->reads("purchase_confirm", [], ["item_id" => $item_id, "po_no" => $po_no, "etd" => $data['etd']]);

            if(!empty($purchase_orders)){
                if(count($purchase_confirm2) > 0){
                    echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Data, Part No ".$item_id." ETD must be different", "theme" => "error"));
                }else{
                    $postFinal = array(
                        "po_no" => @$po_no,
                        "po_date" => @$purchase_orders->purchase_order_date,
                        "item_id" => $item_id,
                        "item_name" => @$purchase_orders->item_name,
                        "product_family" => @$purchase_orders->product_family_name,
                        "supplier_id" => @$purchase_orders->supplier_id,
                        "qty" => @$purchase_orders->qty,
                        "uom" => @$purchase_orders->uom,
                        "eta" => $data['eta'],
                        "etd" => $data['etd'],
                        "delivery" => $data['delivery'],
                        "document_no" => $data['document_no'],
                    );
                    
                    if($purchase_orders->qty == (@$purchase_confirm[0]->delivery + $data['delivery'])){
                        $send = $this->api->create('purchase_confirm', $postFinal, "PCF", "JUTA");
                        $this->db->update('purchase_orders', ["status" => 1], ["purchase_order_no" => $data['po_no'], "item_id" => $data['item_id']]);
                        echo $send;
                    }elseif($purchase_orders->qty > (@$purchase_confirm[0]->delivery + $data['delivery'])){
                        $send = $this->api->create('purchase_confirm', $postFinal, "PCF", "JUTA");
                        echo $send;
                    }else{
                        echo json_encode(array("title" => "Error", "message" => "Cannot save because Part No ".$item_id." Qty Delivery > Qty PO", "theme" => "error"));
                    }
                }
            }else{
                $this->dummy = $this->load->database('dummy', TRUE);

                $this->dummy->select('a.*, b.item_name, c.ven_name, d.um_name, e.pfm_name');
                $this->dummy->from('poc a');
                $this->dummy->join('mst_item b', 'a.item_id = b.item_id');
                $this->dummy->join('r_vendor c', 'a.ven_id = c.ven_id');
                $this->dummy->join('r_unit_measure d', 'b.um_id = d.um_id');
                $this->dummy->join('r_prodfam e', 'b.pfm_id = e.pfm_id');
                $this->dummy->where('a.poc_id', $po_no);
                $this->dummy->where('a.item_id', $item_id);
                $poc = $this->dummy->get()->row();
                
                if(!empty($poc->poc_id)){
                    $postFinal = array(
                        "po_no" => @$po_no,
                        "po_date" => @$poc->poc_date,
                        "item_id" => $item_id,
                        "item_name" => @$poc->item_name,
                        "product_family" => @$poc->pfm_name,
                        "supplier_id" => @$poc->ven_id,
                        "qty" => @$poc->poc_reqqty,
                        "uom" => @$poc->um_name,
                        "eta" => $data['eta'],
                        "etd" => $data['etd'],
                        "delivery" => $data['delivery'],
                        "document_no" => $data['document_no'],
                    );

                    $send = $this->api->create('purchase_confirm', $postFinal, "PCF", "JUTA");
                    echo $send;
                }else{
                    echo json_encode(array("title" => "Error", "message" => "Purchase Order ".$po_no." in MRP POC is not found", "theme" => "error"));
                }
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=schedule_delivery_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_status = base64_decode($this->input->get('filter_status'));
        $filter_po_no = base64_decode($this->input->get('filter_po_no'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));

        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_confirm a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.item_id', $filter_item_id);
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.po_no', 'ASC');
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
                            <small>DELIVERY CONFIRMATION</small>
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
                <th>PO No</th>
                <th>Supplier Name</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Plan Delivery</th>
                <th>ETD</th>
                <th>ETA</th>
                <th>Document No</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['po_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['delivery'] . '</td>
                            <td>' . $data['etd'] . '</td>
                            <td>' . $data['eta'] . '</td>
                            <td>' . $data['document_no'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
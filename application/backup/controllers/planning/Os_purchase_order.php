<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Os_purchase_order extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/os_purchase_order');
        } else {
            redirect('error_access');
        }
    }

    public function readVendors(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $query = $this->db->query("SELECT DISTINCT (CASE WHEN askara = 'T' THEN '006' ELSE `number` END) as number, (CASE WHEN askara = 'T' THEN 'PT. ASKARA INTERNAL' ELSE `name` END) as `name` from supplier WHERE `name` LIKE '%$post%' ORDER BY `name` ASC");
        $records = $query->result_object();
        echo json_encode($records);
    }

    public function readProdfam($ven_name){
        $ven_name = base64_decode($ven_name);
        $this->dummy = $this->load->database('dummy', TRUE);

        $query = $this->dummy->query("SELECT DISTINCT c.pfm_id, UPPER(c.pfm_name) as pfm_name 
            FROM por a 
            JOIN mst_item b ON a.item_id = b.item_id 
            JOIN r_prodfam c ON b.pfm_id = c.pfm_id
            JOIN r_vendor d ON a.ven_id = d.ven_id 
            WHERE d.ven_id LIKE '%$ven_name%' ORDER BY pfm_name asc");
        $records = $query->result_object();
        echo json_encode($records);
    }

    public function readProducts($ven_id, $pfm_id){
        $ven_id = base64_decode($ven_id);

        $this->dummy = $this->load->database('dummy', TRUE);

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        if($ven_id == "006"){
            $datas = $this->api->reads("supplier", [], ["askara" => "T"]);
            $supplier = array();
            foreach ($datas as $data) {
                array_push($supplier, $data->number);
            }
        }else{
            $supplier = [$ven_id];
        }

        //Select Query
        $this->dummy->select("b.item_id, b.item_name");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->where_in('a.ven_id', $supplier);
        $this->dummy->like('b.item_id', $post);
        $this->dummy->like('b.pfm_id', $pfm_id);
        $this->dummy->group_by('b.item_id');
        $this->dummy->order_by('b.item_id', 'asc');
        $records = $this->dummy->get()->result_array();

        echo json_encode($records);
    }

    public function readPo(){
        $ven_id = base64_decode($this->input->get('ven_id'));
        $pfm_id = base64_decode($this->input->get('pfm_id'));
        $item_id = base64_decode($this->input->get('item_id'));
        $po_status = base64_decode($this->input->get('po_status'));

        $this->dummy = $this->load->database('dummy', TRUE);

        if($ven_id == "006"){
            $datas = $this->api->reads("supplier", [], ["askara" => "T"]);
            $supplier = array();
            foreach ($datas as $data) {
                array_push($supplier, $data->number);
            }
        }else{
            $supplier = [$ven_id];
        }

        //Select Query
        $this->dummy->select("a.por_pocid");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->where_in('a.ven_id', $supplier);
        $this->dummy->like('b.item_id', $item_id);
        $this->dummy->like('b.pfm_id', $pfm_id);
        $this->dummy->group_by('a.por_pocid');
        $this->dummy->order_by('a.por_pocid', 'asc');
        //Get Data Array
        $records = $this->dummy->get()->result_array();

        echo json_encode($records);
    }

    public function datatableDetails($item_id, $ven_id, $po_status){
        $this->dummy = $this->load->database('dummy', TRUE);
        $item_id = base64_decode($item_id);
        $ven_id = base64_decode($ven_id);
        $po_status = base64_decode($po_status);

        if($ven_id == "006"){
            $datas = $this->api->reads("supplier", [], ["askara" => "T"]);
            $supplier = array();
            foreach ($datas as $data) {
                array_push($supplier, $data->number);
            }
        }else{
            $supplier = [$ven_id];
        }

        //Select Query
        $this->dummy->select("d.ven_name, f.por_pocreqqty, 
            a.por_pocid, SUM(a.por_receiveqty) as por_receiveqty, (f.por_pocreqqty - SUM(a.por_receiveqty)) as outstanding, 
            (CASE WHEN (f.por_pocreqqty - SUM(a.por_receiveqty)) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status, e.poc_date, e.poc_reqdate");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
        $this->dummy->join('r_vendor d', 'a.ven_id = d.ven_id');
        $this->dummy->join('poc e', 'e.poc_id = a.por_pocid and a.item_id = e.item_id', 'left');
        $this->dummy->join("(SELECT por_pocid, item_id, por_pocreqqty FROM por GROUP BY por_pocid, item_id, por_pocreqqty) f", 'a.por_pocid = f.por_pocid and a.item_id = f.item_id', 'left');
        $this->dummy->where_in('a.ven_id', $supplier);
        $this->dummy->where('b.item_id', $item_id);
        if($po_status == "Open"){
            $this->dummy->having('(f.por_pocreqqty - SUM(a.por_receiveqty)) >', 0);
        }elseif($po_status == "Closed"){
            $this->dummy->having('(f.por_pocreqqty - SUM(a.por_receiveqty)) =', 0);
        }elseif($po_status == "Over"){
            $this->dummy->having('(f.por_pocreqqty - SUM(a.por_receiveqty)) <', 0);
        }
        $this->dummy->group_by('d.ven_name');
        $this->dummy->group_by('a.por_pocid');
        $this->dummy->group_by('b.item_id');
        $this->dummy->group_by('e.poc_date');
        $this->dummy->group_by('e.poc_reqdate');
        $this->dummy->group_by('f.por_pocreqqty');
        $this->dummy->order_by('b.item_id', 'asc');
        //Total Data
        $totalRows = $this->dummy->count_all_results('', false);
        //Get Data Array
        $records = $this->dummy->get()->result_array();

        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatables()
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Filter Data
            $vendor_id = base64_decode($this->input->get('vendor_id'));
            $product_family = base64_decode($this->input->get('product_family'));
            $item_id = base64_decode($this->input->get('item_id'));
            $po_no = trim(base64_decode($this->input->get('po_no')));
            $po_status = trim(base64_decode($this->input->get('po_status')));
            $trans_type = trim(base64_decode($this->input->get('trans_type')));

            if($vendor_id == "006"){
                $datas = $this->api->reads("supplier", [], ["askara" => "T"]);
                $supplier = array();
                foreach ($datas as $data) {
                    array_push($supplier, $data->number);
                }
            }else{
                $supplier = [$vendor_id];
            }

            //Select Query
            $this->dummy->select("b.item_minoq as mpq, b.item_muloq as moq, b.item_id, b.item_name, c.pfm_name, b.um_id, SUM(f.por_pocreqqty) as ost, (CASE WHEN SUM(f.por_pocreqqty) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status");
            $this->dummy->from('mst_item b');
            $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
            $this->dummy->join('r_vendor d', 'b.ven_id = d.ven_id');
            $this->dummy->join("(SELECT por_pocid, item_id, (por_pocreqqty - SUM(por_receiveqty)) as por_pocreqqty FROM por WHERE por_pocid LIKE '%$po_no%' GROUP BY por_pocid, item_id, por_pocreqqty HAVING (por_pocreqqty - SUM(por_receiveqty)) > 0) f", 'b.item_id = f.item_id', 'left');
            $this->dummy->where_in('b.ven_id', $supplier);
            $this->dummy->like('b.pfm_id', $product_family);
            $this->dummy->like('b.item_id', $item_id);
            if($po_status == "Open"){
                $this->dummy->having('SUM(f.por_pocreqqty) >', 0);
            }elseif($po_status == "Closed"){
                $this->dummy->having('SUM(f.por_pocreqqty) =', 0);
            }elseif($po_status == "Over"){
                $this->dummy->having('SUM(f.por_pocreqqty) <', 0);
            }
            $this->dummy->group_by('b.item_id');
            $this->dummy->group_by('b.item_name');
            $this->dummy->group_by('b.item_minoq');
            $this->dummy->group_by('b.item_muloq');
            $this->dummy->group_by('c.pfm_name');
            $this->dummy->group_by('b.um_id');
            $this->dummy->order_by('b.item_id', 'asc');
            //Total Data
            $totalRows = $this->dummy->count_all_results('', false);
            //Limit 1 - 10
            $this->dummy->limit($rows, $offset);
            //Get Data Array
            $records = $this->dummy->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function print($option = "")
    {
        $this->dummy = $this->load->database('dummy', TRUE);

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=os_purchase_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $vendor_id = base64_decode($this->input->get('vendor_id'));
        $product_family = base64_decode($this->input->get('product_family'));
        $item_id = base64_decode($this->input->get('item_id'));
        $po_no = trim(base64_decode($this->input->get('po_no')));
        $po_status = trim(base64_decode($this->input->get('po_status')));
        $trans_type = trim(base64_decode($this->input->get('trans_type')));

        if($vendor_id == "006"){
            $datas = $this->api->reads("supplier", [], ["askara" => "T"]);
            $supplier = array();
            foreach ($datas as $data) {
                array_push($supplier, $data->number);
            }

            $supplier_name = "PT. ASKARA INTERNAL";
        }else{
            $supplier = [$vendor_id];
            $data = $this->api->read('supplier', [], ["number" => $vendor_id]);
            $supplier_name = @$data->name;
        }

        //Select Query
        $this->dummy->select("d.ven_id, d.ven_name, f.por_pocreqqty, b.item_id, b.item_name, c.pfm_name, b.um_id,
            a.por_pocid, SUM(a.por_receiveqty) as por_receiveqty, (f.por_pocreqqty - SUM(a.por_receiveqty)) as outstanding, 
            (CASE WHEN (f.por_pocreqqty - SUM(a.por_receiveqty)) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status, e.poc_date, e.poc_reqdate");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
        $this->dummy->join('r_vendor d', 'a.ven_id = d.ven_id');
        $this->dummy->join('poc e', 'e.poc_id = a.por_pocid and a.item_id = e.item_id', 'left');
        $this->dummy->join("(SELECT por_pocid, item_id, por_pocreqqty FROM por GROUP BY por_pocid, item_id, por_pocreqqty) f", 'a.por_pocid = f.por_pocid and a.item_id = f.item_id', 'left');
        $this->dummy->where_in('a.ven_id', $supplier);
        $this->dummy->like('b.item_id', $item_id);
        $this->dummy->like('b.pfm_id', $product_family);
        $this->dummy->like('a.por_pocid', $po_no);
        if($po_status == "Open"){
            $this->dummy->having('(f.por_pocreqqty - SUM(a.por_receiveqty)) >', 0);
        }elseif($po_status == "Closed"){
            $this->dummy->having('(f.por_pocreqqty - SUM(a.por_receiveqty)) =', 0);
        }elseif($po_status == "Over"){
            $this->dummy->having('(f.por_pocreqqty - SUM(a.por_receiveqty)) <', 0);
        }
        $this->dummy->group_by('d.ven_id');
        $this->dummy->group_by('d.ven_name');
        $this->dummy->group_by('a.por_pocid');
        $this->dummy->group_by('b.item_id');
        $this->dummy->group_by('b.item_name');
        $this->dummy->group_by('c.pfm_name');
        $this->dummy->group_by('b.um_id');
        $this->dummy->group_by('e.poc_date');
        $this->dummy->group_by('e.poc_reqdate');
        $this->dummy->group_by('f.por_pocreqqty');
        $this->dummy->order_by('b.item_id', 'asc');
        //Get Data Array
        $records = $this->dummy->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <style> .str{ mso-number-format:\@; } </style>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>OUTSTANDING PURCHASE ORDER <b>'.$supplier_name.'</b></small>
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
                <th>Item Id</th>
                <th>Item Name</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>PO No</th>
                <th>PO Date</th>
                <th>Request Date</th>
                <th>Request Qty</th>
                <th>Receive Qty</th>
                <th>Outstanding</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . @$data['item_id'] . '</td>
                            <td>' . @$data['item_name'] . '</td>
                            <td>' . @$data['pfm_name'] . '</td>
                            <td>' . @$data['um_id'] . '</td>
                            <td>' . @$data['por_pocid'] . '</td>
                            <td>' . @$data['poc_date'] . '</td>
                            <td>' . @$data['poc_reqdate'] . '</td>
                            <td>' . @$data['por_pocreqqty'] . '</td>
                            <td>' . @$data['por_receiveqty'] . '</td>
                            <td>' . @$data['outstanding'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

}
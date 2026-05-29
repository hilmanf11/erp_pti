<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_items_log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/supplier_items_log');
        } else {
            redirect('error_access');
        }
    }

    public function datatables()
    {
        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to  = base64_decode($this->input->get('filter_to'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_part_no = base64_decode($this->input->get('filter_part_no'));

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
        if($filter_from != "" && $filter_to != ""){
            $this->db->where("a.created_date between '$filter_from' and '$filter_to'");
        }
        if($filter_product_family != ""){
            $this->db->like('c.pfm_id', $filter_product_family);
        }
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.item_id', $filter_part_no);
        $this->db->order_by('a.item_id', 'ASC');
        $this->db->order_by('a.created_date', 'DESC');
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=vendor_contract_historical_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from  = base64_decode($this->input->get('filter_from'));
        $filter_to  = base64_decode($this->input->get('filter_to'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_product_family = base64_decode($this->input->get('filter_product_family'));
        $filter_part_no = base64_decode($this->input->get('filter_part_no'));

        //Select Query
        $this->db->select('a.*, c.item_id, c.item_name, c.item_supplier, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        $this->db->from('supplier_items_log a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number');
        if($filter_from != "" && $filter_to != ""){
            $this->db->where("a.created_date between '$filter_from' and '$filter_to'");
        }
        if($filter_product_family != ""){
            $this->db->like('c.pfm_id', $filter_product_family);
        }
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.item_id', $filter_part_no);
        $this->db->order_by('a.item_id', 'ASC');
        $this->db->order_by('a.created_date', 'DESC');
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
                            <small>SUPPLIER ITEM HISTORICAL</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br>

        <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Created Date</th>
                    <th>Created By</th>
                    <th>Part No</th>
                    <th>Part Name</th>
                    <th>Part Vendor</th>
                    <th>Product Family</th>
                    <th>COO Name</th>
                    <th>Vendor Name</th>
                    <th>Vendor Type</th>
                    <th>Vendor Origin</th>
                    <th>Maker</th>
                    <th>Maker Type</th>
                    <th>Maker Origin</th>
                    <th>Landed Cost</th>
                    <th>Import Duty</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>UoM</th>
                    <th>Leadtime (Days)</th>
                    <th>Share Order</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['created_date'] . '</td>
                        <td>' . $data['created_by'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                        <td>' . $data['pfm_name'] . '</td>
                        <td>' . $data['coo_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['origin'] . '</td>
                        <td>' . $data['maker_name'] . '</td>
                        <td>' . $data['maker_type'] . '</td>
                        <td>' . $data['maker_origin'] . '</td>
                        <td>' . $data['landed_cost'] . '</td>
                        <td>' . $data['import_duty'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['share_order'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

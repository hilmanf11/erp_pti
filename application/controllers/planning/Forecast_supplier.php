<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Forecast_supplier extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->load->library('Ciqrcode');
    }
    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/forecast_supplier');
        } else {
            redirect('error_access');
        }
    }
    function readPoSearch()
    {
        $supplier_id = base64_decode($this->input->get('supplier_id'));
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $datas = $this->api->query("SELECT DISTINCT purchase_order_no FROM purchase_orders WHERE supplier_id = '$supplier_id'");
        echo json_encode($datas);
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $item_id = trim(base64_decode($this->input->get('item_id')));
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));
            //Select Query
            $this->db->select('a.*');
            $this->db->from('forecast_supplier a');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('a.share_order >', 0);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $item_id);
            $this->db->like('a.vendor_id', $filter_supplier);
            $this->db->group_by('a.id');
            $this->db->order_by('a.vendor_name', 'asc');
            $this->db->order_by('a.product_family', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    // public function datatables()
    // {
    //     if ($this->input->get()) {
    //         $filter_month = base64_decode($this->input->get('filter_month'));
    //         $filter_year = base64_decode($this->input->get('filter_year'));
    //         $filter_revision = base64_decode($this->input->get('filter_revision'));
    //         $filter_product_family = base64_decode($this->input->get('filter_product_family'));
    //         $item_id = trim(base64_decode($this->input->get('item_id')));
    //         $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
    //         $filter_po = trim(base64_decode($this->input->get('filter_po')));
    //         //Select Query
    //         $this->db->select('a.*,
    //                 ((a.need_1 + a.need_2 + a.need_3 + a.need_4) / 4) as need_avg,
    //                 b.number as vendor_id, 
    //                 b.name as vendor_name, 
    //                 b.type as vendor_type, 
    //                 b.origin as vendor_origin, 
    //                 c.share_order,
    //                 d.name as maker_name, 
    //                 d.type as maker_type');
    //         $this->db->from('generate_mrp_finals a');
    //         $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
    //         $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
    //         $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
    //         $this->db->where('a.p_month', $filter_month);
    //         $this->db->where('a.p_year', $filter_year);
    //         $this->db->where('a.revision', $filter_revision);
    //         $this->db->where('c.share_order >', 0);
    //         $this->db->like('a.product_family', $filter_product_family);
    //         $this->db->like('a.part_no', $item_id);
    //         $this->db->like('c.supplier_id', $filter_supplier);
    //         $this->db->group_by('a.id');
    //         $this->db->order_by('b.name', 'asc');
    //         $this->db->order_by('a.product_family', 'asc');
    //         //Total Data
    //         $totalRows = $this->db->count_all_results('', false);
    //         $records = $this->db->get()->result_array();
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $item_id = trim(base64_decode($this->input->get('item_id')));
            //Select Query
            $this->db->select('a.*,
                    ((a.need_1 + a.need_2 + a.need_3 + a.need_4) / 4) as need_avg,
                    b.id as vendor_id, 
                    b.name as vendor_name, 
                    b.type as vendor_type, 
                    b.origin as vendor_origin, 
                    c.share_order,
                    d.name as maker_name, 
                    d.type as maker_type');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
            $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
            $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->where('c.share_order >', 0);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $item_id);
            $this->db->group_by('a.id');
            $this->db->order_by('b.name', 'asc');
            $this->db->order_by('a.product_family', 'asc');
            // $this->db->limit(2);
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
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
            // $post = $post['data'];

            $forecast_supplier = $this->api->read("forecast_supplier", [], ["p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision'], "part_no" => $post['part_no']]);
            if (@$forecast_supplier->part_no != "") {
                // $send = $this->api->update('forecast_supplier', ["item_id" => $post['item_id'], "component_id" => $post['component_id']], $post);
                $send = $this->api->update('forecast_supplier', ["id" => @$forecast_supplier->id], $post);
            } else {
                $send = $this->api->create('forecast_supplier', $post, "MRP", "JUTA");
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
        $send = $this->api->delete('forecast_supplier', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_supplier_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $item_id = trim(base64_decode($this->input->get('item_id')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        $fc_4 = $this->readMonths(date('m', strtotime('+4 month', strtotime($periode))));

        //Select Query
        // $this->db->select('a.*,
        //     ((a.need_1 + a.need_2 + a.need_3 + a.need_4) / 4) as need_avg,
        //     b.number as vendor_id, 
        //     b.name as vendor_name, 
        //     b.type as vendor_type, 
        //     b.origin as vendor_origin, 
        //     c.share_order,
        //     d.name as maker_name, 
        //     d.type as maker_type');
        // $this->db->from('generate_mrp_finals a');
        // $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        // $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
        // $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
        // $this->db->where('a.p_month', $filter_month);
        // $this->db->where('a.p_year', $filter_year);
        // $this->db->where('a.revision', $filter_revision);
        // $this->db->where('c.share_order >', 0);
        // $this->db->like('a.product_family', $filter_product_family);
        // $this->db->like('a.part_no', $item_id);
        // $this->db->like('c.supplier_id', $filter_supplier);
        // $this->db->group_by('a.id');
        // $this->db->order_by('b.name', 'asc');
        // $this->db->order_by('a.product_family', 'asc');
        // $records = $this->db->get()->result_array();

        $this->db->select('a.*');
        $this->db->from('forecast_supplier a');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.share_order >', 0);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $item_id);
        $this->db->like('a.vendor_id', $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
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
                            <small>FORECAST SUPPLIER</small>
                        </td>
                    </tr>
                </table>
            </div>
        </center>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Part No</th>
                <th rowspan="2" style="text-align:center;">Part Name</th>
                <th rowspan="2" style="text-align:center;">Product Family</th>
                <th rowspan="2" style="text-align:center;">Vendor Name</th>
                <th rowspan="2" style="text-align:center;">Vendor Type</th>
                <th rowspan="2" style="text-align:center;">Origin</th>
                <th rowspan="2" style="text-align:center;">Leadtime</th>
                <th rowspan="2" style="text-align:center;">Share Order</th>
                <th rowspan="2" style="text-align:center;">MPQ</th>
                <th rowspan="2" style="text-align:center;">MOQ</th>
                <th rowspan="2" style="text-align:center;">Maker Name</th>
                <th rowspan="2" style="text-align:center;">Maker Type</th>
                <th colspan="4" style="text-align:center;">Workorder</th>
                <th rowspan="2" style="text-align:center;">Stock WHS</th>
                <th rowspan="2" style="text-align:center;">OS PO</th>
                <th colspan="9" style="text-align:center;">Forecast</th>
            </tr>
            <tr>
                <th>' . $wo_1 . '</th>
                <th>' . $wo_2 . '</th>
                <th>' . $wo_3 . '</th>
                <th>Xbar</th>
                <th>' . $fc_1 . '</th>
                <th>Sugest PO</th>
                <th>' . $fc_2 . '</th>
                <th>Sugest PO</th>
                <th>' . $fc_3 . '</th>
                <th>Sugest PO</th>
                <th>' . $fc_4 . '</th>
                <th>Sugest PO</th>
                <th>Xbar</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_family'] . '</td>
                            <td>' . $data['vendor_name'] . '</td>
                            <td>' . $data['vendor_type'] . '</td>
                            <td>' . $data['vendor_origin'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['leadtime'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['share_order'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['mpq'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['moq'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td>' . $data['issued_1'] . '</td>
                            <td>' . $data['issued_2'] . '</td>
                            <td>' . $data['issued_3'] . '</td>
                            <td>' . $data['issued_avg'] . '</td>
                            <td>' . $data['total_stock'] . '</td>
                            <td>' . $data['qty_vendor'] . '</td>
                            <td>' . $data['need_1'] . '</td>
                            <td>' . $data['balance_1'] . '</td>
                            <td>' . $data['need_2'] . '</td>
                            <td>' . $data['balance_2'] . '</td>
                            <td>' . $data['need_3'] . '</td>
                            <td>' . $data['balance_3'] . '</td>
                            <td>' . $data['need_4'] . '</td>
                            <td>' . $data['balance_4'] . '</td>
                            <td>' . $data['need_avg'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printPreview($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=preview_forecast_supplier_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        $fc_4 = $this->readMonths(date('m', strtotime('+4 month', strtotime($periode))));

        //Select Query
        $this->db->select('a.*,
            ((a.need_1 + a.need_2 + a.need_3 + a.need_4) / 4) as need_avg,
            b.number as vendor_id, 
            b.name as vendor_name, 
            b.type as vendor_type, 
            b.origin as vendor_origin, 
            c.share_order,
            d.name as maker_name, 
            d.type as maker_type');
        $this->db->from('generate_mrp_finals a');
        $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        $this->db->join('suppliers b', 'c.supplier_id = b.id', 'left');
        $this->db->join('mst_maker d', 'c.maker_code = d.number', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('c.share_order >', 0);
        $this->db->group_by('a.id');
        $this->db->order_by('b.name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
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
                            <small>FORECAST SUPPLIER</small>
                        </td>
                    </tr>
                </table>
            </div>
        </center>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">PERIOD</td>
                <td width="20">:</td>
                <td>' . $filter_month . '/' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="20">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Part No</th>
                <th rowspan="2" style="text-align:center;">Part Name</th>
                <th rowspan="2" style="text-align:center;">Product Family</th>
                <th rowspan="2" style="text-align:center;">Vendor Name</th>
                <th rowspan="2" style="text-align:center;">Vendor Type</th>
                <th rowspan="2" style="text-align:center;">Origin</th>
                <th rowspan="2" style="text-align:center;">Leadtime</th>
                <th rowspan="2" style="text-align:center;">Share Order</th>
                <th rowspan="2" style="text-align:center;">MPQ</th>
                <th rowspan="2" style="text-align:center;">MOQ</th>
                <th rowspan="2" style="text-align:center;">Maker Name</th>
                <th rowspan="2" style="text-align:center;">Maker Type</th>
                <th colspan="4" style="text-align:center;">Workorder</th>
                <th rowspan="2" style="text-align:center;">Stock WHS</th>
                <th rowspan="2" style="text-align:center;">OS PO</th>
                <th colspan="9" style="text-align:center;">Forecast</th>
            </tr>
            <tr>
                <th>' . $wo_1 . '</th>
                <th>' . $wo_2 . '</th>
                <th>' . $wo_3 . '</th>
                <th>Xbar</th>
                <th>' . $fc_1 . '</th>
                <th>Sugest PO</th>
                <th>' . $fc_2 . '</th>
                <th>Sugest PO</th>
                <th>' . $fc_3 . '</th>
                <th>Sugest PO</th>
                <th>' . $fc_4 . '</th>
                <th>Sugest PO</th>
                <th>Xbar</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_family'] . '</td>
                            <td>' . $data['vendor_name'] . '</td>
                            <td>' . $data['vendor_type'] . '</td>
                            <td>' . $data['vendor_origin'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['leadtime'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['share_order'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['mpq'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['moq'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td>' . $data['issued_1'] . '</td>
                            <td>' . $data['issued_2'] . '</td>
                            <td>' . $data['issued_3'] . '</td>
                            <td>' . $data['issued_avg'] . '</td>
                            <td>' . $data['total_stock'] . '</td>
                            <td>' . $data['qty_vendor'] . '</td>
                            <td>' . $data['need_1'] . '</td>
                            <td>' . $data['balance_1'] . '</td>
                            <td>' . $data['need_2'] . '</td>
                            <td>' . $data['balance_2'] . '</td>
                            <td>' . $data['need_3'] . '</td>
                            <td>' . $data['balance_3'] . '</td>
                            <td>' . $data['need_4'] . '</td>
                            <td>' . $data['balance_4'] . '</td>
                            <td>' . $data['need_avg'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function readMonths($month)
    {
        $months = array('01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR', '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG', '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC');
        $month = $months[$month];
        return $month;
    }

    function printForecastCheckApproved()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $datas = $this->api->query("SELECT distinct approved_to FROM forecast_supplier WHERE p_month='$filter_month' and p_year='$filter_year' and revision='$filter_revision' and vendor_id='$filter_supplier' and (approved_to <> '' or approved_to is not null)");

        // die($this->db->last_query());
        if (@$datas[0]->approved_to == "") {
            echo "Approved";
        } else {
            echo "Checking";
        }
    }

    public function printForecast($option = "")
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $item_id = trim(base64_decode($this->input->get('item_id')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        $supplier = $this->db->get_where('suppliers', ['id' => $filter_supplier])->row();

        $period_header = strtoupper(date("F", strtotime($filter_year . "-" . $filter_month . "-01")));
        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $periode_from = date('F', strtotime('+1 month', strtotime($periode)));
        $periode_to = date('F Y', strtotime('+3 month', strtotime($periode)));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        //Select Query
        $this->db->select('a.*,
            c.item_supplier,
            e.class,
            g.name as uom,
            h.name as plant_name');
        $this->db->from('forecast_supplier a');
        $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        $this->db->join('generate_mrp_abcclass e', 'a.part_no = e.part_no and a.p_month = e.p_month and a.p_year = e.p_year and a.revision = e.revision', 'left');
        $this->db->join('mst_item_raw f', 'a.part_no = f.item_id', 'left');
        $this->db->join('uom g', 'g.number = f.um_id', 'left');
        $this->db->join('plants h', 'c.plant = h.number', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('c.share_order >', 0);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $item_id);
        $this->db->like('c.supplier_id', $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
        $records = $this->db->get()->result_array();

        $approval = $this->api->read('approval', [], ["table_name" => "forecast_supplier"]);
        $user_1 = $this->api->read('user', [], ["username" => $approval->user_approval_1]);
        $user_2 = $this->api->read('user', [], ["username" => $approval->user_approval_2]);

        $qr_user_1 = "";
        $qr_user_2 = "";

        if (@$records[0]['approved_to'] == "") {
            $hidden = "";
        } else {
            $hidden = "hidden";
        }

        $qr_user_1 = "src='" . base_url('assets/image/qrcode/' . @$user_1->name . '.png') . "'";
        $qr_user_2 = "src='" . base_url('assets/image/qrcode/' . @$user_2->name . '.png') . "'";

        $this->createQrcode(@$user_1->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_2->name, "assets/image/qrcode/");
        $media = "@media screen {
                        .print {
                            display: none !important;
                        }
                    }
                    @media print {
                        .noprint {
                            display: none !important;
                        }
                    }";
        $media2 = ' <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P to Print</h1>
                        </center>
                    </div>';
        $html = '<html>
                    <head>
                        <title>' . @$supplier->name . ' (' . $periode_from . '-' . $periode_to . '_Plant ' . @$records[0]['plant_name'] . ')</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    
                    <style>
                        * {
                        box-sizing: border-box;
                        }
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                        border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}
                        ' . $media . '
                        .column {
                        float: left;
                        width: 50%;
                        border: 1px black solid;
                        }

                        /* Clear floats after the columns */
                        .row:after {
                        content: "";
                        display: table;
                        clear: both;
                        }
                    </style>
                    <body>
                        ' . $media2 . '
                        <div class="print">';
        $html .= '<center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>FORECAST</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . '
            </div>
            <br><br>
            <div style="float: center; font-size: 16px; text-align: center;">
                <h3>FORECAST ' . $period_header . ' ' . $filter_year . '</h3>
            </div>
        </center>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center; background:#6ba7e1">NO</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NAME</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PRODUCT FAMILY</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO. VENDOR</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER TYPE</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">CLASS<br>A/B/C</th>
                <th colspan="3" style="text-align:center; background:#6ba7e1">WORKORDER</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">XBAR</th>
                <th colspan="3" style="text-align:center; background:#6ba7e1">FORECAST</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">UOM</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">SUPPLIER\'S CAPACITY</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">CONFIRMATION OF<br> SUPPLIER\'S ABILITY</th>
            </tr>
            <tr>
                <th style="text-align:center; background:#6ba7e1">' . $wo_1 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $wo_2 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $wo_3 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_1 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_2 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_3 . '</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_family'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td align="center">' . $data['class'] . '</td>
                            <td align="right">' . $data['issued_1'] . '</td>
                            <td align="right">' . $data['issued_2'] . '</td>
                            <td align="right">' . $data['issued_3'] . '</td>
                            <td align="right">' . $data['issued_avg'] . '</td>
                            <td align="right">' . $data['need_1'] . '</td>
                            <td align="right">' . $data['need_2'] . '</td>
                            <td align="right">' . $data['need_3'] . '</td>
                            <td align="center">' . $data['uom'] . '</td>
                            <td align="center"></td>
                            <td align="center"></td>
                        </tr>';
            $no++;
        }
        $html .= '</table>';
        $html .= '<br><span style="font-size:12px"><b>Note :</b><br>
                    1. Angka pada kolom forecast merupakan estimasi quantity open PO oleh BEI pada bulan tersebut <i>(The number in the forecast column is the estimated quantity of open PO by BEI in that month).</i><br>
                    2. Data Forecast berdasarkan hasil dari Generate MRP bulan berjalan <i>(Forecast data is based on the results of the current month\'s MRP Generate).</i><br>
                    </span>';
        $html .= '<br><center><table width="85%" border="0" style="font-size:12px;">
                    <tr>
                        <td align="center" width="250px"><b>' . @$supplier->name . '</b></td>
                        <td align="center" colspan="2"><b>PT. ' . @$config->name . '</b></td>
                    </tr>
                    <tr>
                        <td align="center"><b>ACCEPTED BY,</td>
                        <td align="center"><b>CONFIRM,</td>
                        <td align="center"><b>CHECKED,</td>
                    </tr>
                    <tr>
                        <td align="center" style="height:80px; width:120px;"></td>
                        <td align="center" style="height:80px; width:120px;"' . $hidden . '><img ' . $qr_user_2 . ' width="80"/></td>
                        <td align="center" style="height:80px; width:120px;"' . $hidden . '><img ' . $qr_user_1 . ' width="80"/></td>
                    </tr>
                    <tr>
                        <td align="center">________________________</td>
                        <td align="center"><b><u>' . @$user_2->name . '</u><br>' . @$user_2->position . '</b></td>
                        <td align="center"><b><u>' . @$user_1->name . '</u><br>' . @$user_1->position . '</b></td>
                    </tr>
        
        </table></center>';
        $html .= '</body></html>';
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    public function print2($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_supplier_2_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $item_id = trim(base64_decode($this->input->get('item_id')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));

        $period_header = strtoupper(date("F", strtotime($filter_year . "-" . $filter_month . "-01")));
        $periode = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
        $wo_1 = $this->readMonths(date('m', strtotime('-3 month', strtotime($periode)))) . '-' . date('y', strtotime('-3 month', strtotime($periode)));
        $wo_2 = $this->readMonths(date('m', strtotime('-2 month', strtotime($periode)))) . '-' . date('y', strtotime('-2 month', strtotime($periode)));
        $wo_3 = $this->readMonths(date('m', strtotime('-1 month', strtotime($periode)))) . '-' . date('y', strtotime('-1 month', strtotime($periode)));
        $fc_1 = $this->readMonths(date('m', strtotime('+1 month', strtotime($periode))));
        $fc_2 = $this->readMonths(date('m', strtotime('+2 month', strtotime($periode))));
        $fc_3 = $this->readMonths(date('m', strtotime('+3 month', strtotime($periode))));
        //Select Query
        $this->db->select('a.*,
            c.item_supplier,
            e.class,
            g.name as uom');
        $this->db->from('forecast_supplier a');
        $this->db->join('supplier_items c', 'a.part_no = c.item_id', 'left');
        $this->db->join('generate_mrp_abcclass e', 'a.part_no = e.part_no and a.p_month = e.p_month and a.p_year = e.p_year and a.revision = e.revision', 'left');
        $this->db->join('mst_item_raw f', 'a.part_no = f.item_id', 'left');
        $this->db->join('uom g', 'g.number = f.um_id', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.share_order >', 0);
        $this->db->like('a.product_family', $filter_product_family);
        $this->db->like('a.part_no', $item_id);
        $this->db->like('a.vendor_id', $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.product_family', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html>
                    <head>
                        <title>Forecast Supplier</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    
                    <style>
                        * {
                        box-sizing: border-box;
                        }
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                        border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}
                        .column {
                        float: left;
                        width: 50%;
                        border: 1px black solid;
                        }

                        /* Clear floats after the columns */
                        .row:after {
                        content: "";
                        display: table;
                        clear: both;
                        }
                    </style>
                    <body>';
        $html .= '<center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>FORECAST</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . '
            </div>
            <br><br>
            <div style="float: center; font-size: 16px; text-align: center;">
                <h3>FORECAST ' . $period_header . ' ' . $filter_year . '</h3>
            </div>
        </center>
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center; background:#6ba7e1">NO</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NAME</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PRODUCT FAMILY</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">PART NO. VENDOR</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">MAKER TYPE</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">CLASS<br>A/B/C</th>
                <th colspan="3" style="text-align:center; background:#6ba7e1">WORKORDER</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">XBAR</th>
                <th colspan="3" style="text-align:center; background:#6ba7e1">FORECAST</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">UOM</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">SUPPLIER\'S CAPACITY</th>
                <th rowspan="2" style="text-align:center; background:#6ba7e1">CONFIRMATION OF<br> SUPPLIER\'S ABILITY</th>
            </tr>
            <tr>
                <th style="text-align:center; background:#6ba7e1">' . $wo_1 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $wo_2 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $wo_3 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_1 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_2 . '</th>
                <th style="text-align:center; background:#6ba7e1">' . $fc_3 . '</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= ' <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['part_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_family'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td align="center">' . $data['class'] . '</td>
                            <td align="right">' . $data['issued_1'] . '</td>
                            <td align="right">' . $data['issued_2'] . '</td>
                            <td align="right">' . $data['issued_3'] . '</td>
                            <td align="right">' . $data['issued_avg'] . '</td>
                            <td align="right">' . $data['need_1'] . '</td>
                            <td align="right">' . $data['need_2'] . '</td>
                            <td align="right">' . $data['need_3'] . '</td>
                            <td align="center">' . $data['uom'] . '</td>
                            <td align="center"></td>
                            <td align="center"></td>
                        </tr>';
            $no++;
        }
        $html .= '</table>';
        $html .= '</body></html>';
        echo $html;
    }
}

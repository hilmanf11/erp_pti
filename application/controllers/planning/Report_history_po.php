<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_history_po extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/report_history_po');
        } else {
            redirect('error_access');
        }
    }

    public function readPr()
    {
        $get = $this->input->get();
        $filter_month = $get['filter_month'];
        $filter_year = $get['filter_month'];
        $filter_revision = $get['filter_month'];
        $filter_supplier = base64_decode($get['filter_supplier']);

        $send = $this->api->query("SELECT DISTINCT purchase_request_no FROM purchase_requests WHERE supplier_id='$filter_supplier'");
        echo json_encode($send);
    }

    public function readPo()
    {
        $get = $this->input->get();
        $filter_month = $get['filter_month'];
        $filter_year = $get['filter_month'];
        $filter_revision = $get['filter_month'];
        $filter_supplier = base64_decode($get['filter_supplier']);

        $send = $this->api->query("SELECT DISTINCT purchase_order_no FROM purchase_orders WHERE supplier_id='$filter_supplier'");
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_pr = base64_decode($this->input->get('filter_pr'));
            $filter_po = base64_decode($this->input->get('filter_po'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            //Select Query
            $this->db->select('a.part_no, a.part_name, a.product_family, 
                a.created_by as generate_by, 
                a.created_date as generate_date, 
                a.purchase_order as total_need,
                b.created_by as pr_by,
                b.created_date as pr_date,
                b.purchase_request as total_pr,
                b.purchase_request_no,
                b.reason as reason_pr,
                d.name as pr_supplier_name,
                b.approved_by as pr_approved_by,
                b.approved_date as pr_approved_date,
                b.approved_to as pr_approved_to,
                c.created_by as po_by,
                c.created_date as po_date,
                c.qty as total_po,
                c.purchase_order_no,
                c.reason as reason_po,
                e.name as po_supplier_name,
                c.approved_by as po_approved_by,
                c.approved_date as po_approved_date,
                c.approved_to as po_approved_to,
                c.email_date,
                c.confirm_date,
                c.status');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('purchase_requests b', 'a.part_no = b.part_no and a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision', 'left');
            $this->db->join('purchase_orders c', 'a.part_no = c.item_id and a.p_month = c.p_month and a.p_year = c.p_year and a.revision = c.revision', 'left');
            $this->db->join('suppliers d', 'b.supplier_id = d.id', 'left');
            $this->db->join('suppliers e', 'c.supplier_id = e.id', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_supplier != ""){
                $this->db->like('c.supplier_id', $filter_supplier);
            }
            if($filter_pr != ""){
                $this->db->like('b.purchase_request_no', $filter_pr);
            }
            if($filter_po != ""){
                $this->db->like('c.purchase_order_no', $filter_po);
            }
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_product_no);
            $this->db->group_by('a.part_no');
            $this->db->group_by('c.supplier_id');
            $this->db->order_by('a.product_family', 'asc');
            $this->db->order_by('a.purchase_order', 'desc');

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

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=po_historycal_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));
            $filter_pr = base64_decode($this->input->get('filter_pr'));
            $filter_po = base64_decode($this->input->get('filter_po'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            // Select Query
            $this->db->select('a.part_no, a.part_name, a.product_family, 
                a.created_by as generate_by, 
                a.created_date as generate_date, 
                a.purchase_order as total_need,
                b.created_by as pr_by,
                b.created_date as pr_date,
                b.purchase_request as total_pr,
                b.purchase_request_no,
                b.reason as reason_pr,
                d.name as pr_supplier_name,
                b.approved_by as pr_approved_by,
                b.approved_date as pr_approved_date,
                b.approved_to as pr_approved_to,
                c.created_by as po_by,
                c.created_date as po_date,
                c.qty as total_po,
                c.purchase_order_no,
                c.reason as reason_po,
                e.name as po_supplier_name,
                c.approved_by as po_approved_by,
                c.approved_date as po_approved_date,
                c.approved_to as po_approved_to,
                c.email_date,
                c.confirm_date,
                c.status');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('purchase_requests b', 'a.part_no = b.part_no and a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision', 'left');
            $this->db->join('purchase_orders c', 'a.part_no = c.item_id and a.p_month = c.p_month and a.p_year = c.p_year and a.revision = c.revision', 'left');
            $this->db->join('suppliers d', 'b.supplier_id = d.id', 'left');
            $this->db->join('suppliers e', 'c.supplier_id = e.id', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_supplier != ""){
                $this->db->like('c.supplier_id', $filter_supplier);
            }
            if($filter_pr != ""){
                $this->db->like('b.purchase_request_no', $filter_pr);
            }
            if($filter_po != ""){
                $this->db->like('c.purchase_order_no', $filter_po);
            }
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_product_no);
            $this->db->group_by('a.part_no');
            $this->db->group_by('c.supplier_id');
            $this->db->order_by('a.product_family', 'asc');
            $this->db->order_by('a.purchase_order', 'desc');
            // $this->db->limit(50);
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
            .box-green{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #D6FFCF;
            }

            .box-red{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #FFCFCF;
            }
            </style>
        <body>
        <center>
            <div style="font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>HISTORICAL PURCHASE REPORT<br>
                            <br>
                            PERIOD <b>' . $filter_year . '/' . $filter_month . '</b>
                            REVISION <b>' . $filter_revision . '</b>
                            <br><br>
                            Print Date ' . date("d M Y H:m:s") . ' <br>
                            Print By ' . $this->session->username . '</small>  
                        </td>
                    </tr>
                </table>
            </div>
            <br><br>
        </center>
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;" rowspan="2" width="20">NO</th>
                <th style="text-align:center;" rowspan="2">Part No</th>
                <th style="text-align:center;" rowspan="2">Part Name</th>
                <th style="text-align:center;" rowspan="2">Product Family</th>
                <th style="text-align:center;" colspan="2">MRP</th>
                <th style="text-align:center;" colspan="5">Purchase Request</th>
                <th style="text-align:center;" colspan="7">Purchase Order</th>
            </tr>
            <tr>
                <th style="text-align:center;">Generate Date</th>
                <th style="text-align:center;">Total Need</th>
                <th style="text-align:center;">Create Date</th>
                <th style="text-align:center;">PR No</th>
                <th style="text-align:center;">Supplier</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Reason</th>
                <th style="text-align:center;">Create Date</th>
                <th style="text-align:center;">PO No</th>
                <th style="text-align:center;">Supplier</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Reason</th>
                <th style="text-align:center;">Send Date</th>
                <th style="text-align:center;">Confirm Date</th>
            </tr>';

            $no = 1;
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record['part_no'] . '</td>
                                <td>' . $record['part_name'] . '</td>
                                <td>' . $record['product_family'] . '</td>
                                <td>' . $record['generate_date'] . '</td>
                                <td style="text-align:right;">' . $record['total_need'] . '</td>
                                <td>' . $record['pr_date'] . '</td>
                                <td>' . $record['purchase_request_no'] . '</td>
                                <td>' . $record['pr_supplier_name'] . '</td>
                                <td style="text-align:right;">' . $record['total_pr'] . '</td>
                                <td>' . $record['reason_pr'] . '</td>
                                <td>' . $record['po_date'] . '</td>
                                <td>' . $record['purchase_order_no'] . '</td>
                                <td>' . $record['po_supplier_name'] . '</td>
                                <td style="text-align:right;">' . $record['total_po'] . '</td>
                                <td>' . $record['reason_po'] . '</td>
                                <td>' . $record['email_date'] . '</td>
                                <td></td>
                            </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}

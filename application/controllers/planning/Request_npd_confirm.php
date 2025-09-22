<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Request_npd_confirm extends CI_Controller
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
        $this->load->library('dompdf_gen');
        $this->load->library('email');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Part No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/request_npd_confirm');
        } else {
            redirect('error_access');
        }
    }

    public function readParts($supplier){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier = base64_decode($supplier);
        
        $query = $this->db->query("SELECT b.item_id, b.item_name, a.mpq, a.moq, a.share_order, a.leadtime 
            FROM supplier_items a 
            JOIN mst_item_raw b ON a.item_id = b.item_id
            WHERE a.supplier_id = '$supplier' and b.item_id like '%$post%' and a.share_order > 0
            ORDER BY b.item_id ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_request_no = base64_decode($this->input->get('filter_request_no'));
            $filter_request_by = trim(base64_decode($this->input->get('filter_request_by')));
            $filter_item_npd_id = trim(base64_decode($this->input->get('filter_item_npd_id')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, SUM(a.qty) as total_qty, count(a.id) as total_data, count(b.id) as total_status, count(c.id) as total_confirm');
            $this->db->from('request_npd a');
            $this->db->join("(select * from request_npd where status = 0) b", 'a.id = b.id', 'left');
            $this->db->join("(select * from request_npd where confirm != '') c", 'a.id = c.id', 'left');
            $this->db->where("(a.approved_to is null or a.approved_to = '')");
            if ($filter_from != "" and $filter_to != "") {
                $this->db->where("a.request_date >=", $filter_from);
                $this->db->where("a.request_date <=", $filter_to);
            }
            $this->db->like('a.request_no', $filter_request_no);
            $this->db->like('a.request_by', $filter_request_by);
            $this->db->like('a.item_npd_id', $filter_item_npd_id);
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', "desc");
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

    public function datatableDetails($request_no){
        $request_no = base64_decode($request_no);
        $filter_item_npd_id = trim(base64_decode($this->input->get('filter_item_npd_id')));

        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('request_npd a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left');
        $this->db->where('a.request_no', $request_no);
        $this->db->where("(a.approved_to is null or a.approved_to = '')");
        $this->db->like('a.item_npd_id', $filter_item_npd_id);
        $this->db->group_by('a.id');
        $this->db->order_by('a.item_npd_id');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $total_qty = 0;
        foreach ($records as $record) {
            $total_qty += $record['qty'];
            array_push($data, $record);
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $data,
            'footer' => array([
                "item_npd_id" => "Grand Total",
                "qty" => $total_qty,
            ])
        ]);
        echo json_encode($result);
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            if($post['confirm'] != ""){
                $send = $this->db->update('request_npd', array_merge($post, ["confirm_date" => date("Y-m-d H:i:s"), "confirm_by" => $this->session->name]), ["id" => $post['id']]);
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("YmdHis");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_request_no = base64_decode($this->input->get('filter_request_no'));
        $filter_request_by = trim(base64_decode($this->input->get('filter_request_by')));
        $filter_item_npd_id = trim(base64_decode($this->input->get('filter_item_npd_id')));

        //Select Query
        $this->db->select('a.*');
        $this->db->from('request_npd a');
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where("a.request_date >=", $filter_from);
            $this->db->where("a.request_date <=", $filter_to);
        }
        $this->db->like('a.request_no', $filter_request_no);
        $this->db->like('a.request_by', $filter_request_by);
        $this->db->like('a.item_npd_id', $filter_item_npd_id);
        $this->db->order_by('a.request_no', "desc");
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
                            <small>PART REQUEST NPD</small>
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
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Request No</th>
                <th style="text-align:center;">Request Date</th>
                <th style="text-align:center;">Request By</th>
                <th style="text-align:center;">Part NPD No</th>
                <th style="text-align:center;">Part NPD Name</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Requirement<br>Date</th>
                <th style="text-align:center;">Confirm</th>
                <th style="text-align:center;">Confirm Date</th>
                <th style="text-align:center;">Confirm Reason</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Supplier Name</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['request_no'] . '</td>
                            <td>' . $data['request_date'] . '</td>
                            <td>' . $data['request_by'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_npd_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_npd_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['requirement_date'] . '</td>
                            <td style="text-align:center;">' . $data['confirm'] . '</td>
                            <td style="text-align:center;">' . $data['confirm_date'] . '</td>
                            <td style="text-align:left;">' . $data['confirm_reason'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['supplier_id'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
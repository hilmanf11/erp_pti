<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_orders extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[sales_order.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/sales_orders');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->api->reads("sales_orders", ["number" => $post], [], "", "created_date", "desc", ["number"]);
        echo json_encode($sales_orders);
    }

    public function readProducts($customer_no)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT a.*, b.item_name
            FROM mst_customer_item a 
            JOIN mst_item b ON a.item_id = b.item_id
            WHERE a.customer_no = '$customer_no' and b.item_id LIKE '%$post%'");
        echo json_encode($send);
    }

    public function number($customer_no, $sales_order_date)
    {
        $datenow    = "SO" . $customer_no . date("ym", strtotime(base64_decode($sales_order_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM sales_orders WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        echo $datenow . $autoID;
    }

    public function datatables($details = "")
    {
        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_number = base64_decode($this->input->get('filter_number'));
        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as customer_name, c.item_name');
            $this->db->from('sales_orders a');
            $this->db->join('mst_customer b', 'a.customer_no = b.number');
            $this->db->join('mst_item c', 'a.item_id = c.item_id');
            $this->db->where('a.deleted_is', 0);
            $this->db->like('a.customer_no', $filter_customers);
            $this->db->like('a.item_id', $filter_items);
            $this->db->like('a.number', $filter_number);
            // $this->db->like('b.type', $filter_type);
            $this->db->order_by('a.status_is', 'ASC');
            $this->db->group_by('a.number');
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $number = base64_decode($this->input->get('number'));
            $this->db->select('a.*, b.name as customer_name, c.item_name');
            $this->db->from('sales_orders a');
            $this->db->join('mst_customer b', 'a.customer_no = b.number');
            $this->db->join('mst_item c', 'a.item_id = c.item_id');
            $this->db->where('a.deleted_is', 0);
            $this->db->like('a.number', $number);
            $this->db->like('a.customer_no', $filter_customers);
            $this->db->like('a.item_id', $filter_items);
            // $this->db->like('b.type', $filter_type);
            $this->db->order_by('a.status_is', 'ASC');
            $totalRows = $this->db->count_all_results('', false);
        }
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('a.*, b.item_name');
            $this->db->from('sales_orders a');
            $this->db->join('mst_item b', 'a.item_id = b.item_id');
            $this->db->where('a.number', $number);
            $this->db->order_by('b.item_id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $sales_orders = $this->api->read("sales_orders", [], ["number" => $post['number'], "item_id" => $post['item_id']]);
            if (!empty($sales_orders)) {
                $send = $this->api->update('sales_orders', ["number" => $post['number'], "item_id" => $post['item_id']], $post);
            } else {
                $send = $this->api->create('sales_orders', $post, "SO", "SO");
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
        $send = $this->api->delete('sales_orders', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_customer_item_$format.xls");
        }

        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_number = base64_decode($this->input->get('filter_number'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, c.item_name, b.name as customer_name');
        $this->db->from('sales_orders a');
        $this->db->join('mst_customer b', 'a.customer_no = b.number');
        $this->db->join('mst_item c', 'a.item_id = c.item_id');
        $this->db->like('a.customer_no', $filter_customers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('a.number', $filter_number);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#mst_customer_item {border-collapse: collapse;width: 100%;font-size: 12px;}#mst_customer_item td, #mst_customer_item th {border: 1px solid #ddd;padding: 2px;}#mst_customer_item tr:nth-child(even){background-color: #f2f2f2;}#mst_customer_item tr:hover {background-color: #ddd;}#mst_customer_item th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                        <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>SALES ORDER</h3>
            </div>
        </center>
        
        <table id="mst_customer_item" border="1">
            <tr>
                <th width="20">No</th>
                <th>SO No</th>
                <th>SO Date</th>
                <th>Customer PO</th>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Currency</th>
                <th>Total</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['customer_po'] . '</td>
                    <td>' . $data['customer_no'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['total'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

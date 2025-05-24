<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_delivery extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/schedule_delivery');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $process = $this->api->reads('schedule_delivery', [], ["process_sub_number" => $number]);

            echo json_encode($process);
        }
    }

    public function readSalesOrder($customer_no)
    {
        $send = $this->api->query("SELECT DISTINCT `number`
            FROM sales_orders
            WHERE customer_no = '$customer_no' ORDER BY `number` DESC");
        echo json_encode($send);
    }

    public function readProducts($sales_order_no)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT a.*, b.item_name
            FROM sales_orders a 
            JOIN mst_item b ON a.item_id = b.item_id
            WHERE a.number = '$sales_order_no' and b.item_id LIKE '%$post%'");
        echo json_encode($send);
    }

    public function readSchedule($month, $year){
        $firstDate = date("Y-m-01", strtotime(date("$year-$month-01")));
        $endDate = date("Y-m-t", strtotime(date("$year-$month-01")));

        $tgl = 1;
        $form_input = "";
        while (strtotime($firstDate) <= strtotime($endDate)) {
            $form_input .= ' <div style="float: left; width: 14%;">
                                <div class="fitem">
                                    <span style="width:40%; display:inline-block;"> Date '.$tgl.'</span>
                                    <input type="number" style="width:50%;" name="qty_'.$tgl.'" id="qty_'.$tgl.'" value="0" onChange="hitung(this)">
                                </div>
                            </div>';
            $tgl++;
            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        }

        echo '  <div style="float: left; width: 14%;" hidden>
                    <div class="fitem">
                        <span style="width:40%; display:inline-block;"> Last Date</span>
                        <input style="width:50%;" id="last_date" value="'.($tgl-1).'">
                    </div>
                </div>';

        echo $form_input;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer = base64_decode($this->input->get('filter_customer'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('*');
            $this->db->from('schedule_delivery');
            $this->db->where('deleted_is', 0);
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('revision', $filter_revision);
            $this->db->like('product_no', $filter_product_no);
            $this->db->like('customer_no', $filter_customer);
            $this->db->order_by('customer_name', 'ASC');
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
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->api->create('schedule_delivery', $post, "SCD", "schedule_delivery");
                echo $send;
            } else {
                show_error(validation_errors());
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
            $send = $this->api->update('schedule_delivery', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('schedule_delivery', $data);
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

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'sales_order_no' => $data->val($i, 2),
                'customer_no' => $data->val($i, 3),
                'customer_name' => $data->val($i, 4),
                'product_no' => $data->val($i, 5),
                'product_name' => $data->val($i, 6),
                'qty' => $data->val($i, 7),
                'qty_1' => $data->val($i, 8),
                'qty_2' => $data->val($i, 9),
                'qty_3' => $data->val($i, 10),
                'qty_4' => $data->val($i, 11),
                'qty_5' => $data->val($i, 12),
                'qty_6' => $data->val($i, 13),
                'qty_7' => $data->val($i, 14),
                'qty_8' => $data->val($i, 15),
                'qty_9' => $data->val($i, 16),
                'qty_10' => $data->val($i, 17),
                'qty_11' => $data->val($i, 18),
                'qty_12' => $data->val($i, 19),
                'qty_13' => $data->val($i, 20),
                'qty_14' => $data->val($i, 21),
                'qty_15' => $data->val($i, 22),
                'qty_16' => $data->val($i, 23),
                'qty_17' => $data->val($i, 24),
                'qty_18' => $data->val($i, 25),
                'qty_19' => $data->val($i, 26),
                'qty_20' => $data->val($i, 27),
                'qty_21' => $data->val($i, 28),
                'qty_22' => $data->val($i, 29),
                'qty_23' => $data->val($i, 30),
                'qty_24' => $data->val($i, 31),
                'qty_25' => $data->val($i, 32),
                'qty_26' => $data->val($i, 33),
                'qty_27' => $data->val($i, 34),
                'qty_28' => $data->val($i, 35),
                'qty_29' => $data->val($i, 36),
                'qty_30' => $data->val($i, 37),
                'qty_31' => $data->val($i, 38),
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

            $customer = $this->api->read('mst_customer', [], ["number" => $data['customer_no']]);
            $item = $this->api->read('mst_item', [], ["item_id" => $data['product_no']]);
            $schedule_delivery = $this->api->reads('schedule_delivery', [], ["product_no" => $data['product_no'], "p_month" => $data['p_month'], "p_year" => $data['p_year'], "revision" => $data['revision']]);
            
            if (empty($customer->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['customer_no'] . " Not Found", "theme" => "error"));
            } elseif (empty($item->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item ID " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (count($schedule_delivery) > 0) {
                echo json_encode(array("title" => "Duplicated", "message" => "Item ID " . $data['product_no'] . " has been Duplicated", "theme" => "error"));
            } else {
                $send = $this->api->create('schedule_delivery', $data, "SCD", "SCD");
                echo $send;
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

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        //Select Query
        $this->db->select('*');
        $this->db->from('schedule_delivery');
        $this->db->where('deleted_is', 0);
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->like('product_no', $filter_product_no);
        $this->db->like('customer_no', $filter_customer);
        $this->db->order_by('customer_name', 'ASC');
        //Get Data Array
        $records = $this->db->get()->result_array();

        $firstDate = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
        $endDate = date("Y-m-t", strtotime(date("$filter_year-$filter_month-01")));
        $firstDate2 = date("Y-m-01", strtotime(date("$filter_year-$filter_month-01")));
        $endDate2 = date("Y-m-t", strtotime(date("$filter_year-$filter_month-01")));

        $tgl = 1;
        $header = "";
        while (strtotime($firstDate) <= strtotime($endDate)) {
            $header .= "<th>Date ".$tgl."</th>";
            $tgl++;
            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
        }

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
                            <small>SCHEDULE DELIVERY</small>
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
                <th>Period</th>
                <th>Revision</th>
                <th>Customer No</th>
                <th>Customer Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                '.$header.'
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            $tgl = 1;
            $headerIsi = "";
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $headerIsi .= "<td>".$data["qty_".$tgl]."</td>";
                $tgl++;
                $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
            }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['p_year'] . $data['p_month'] . '</td>
                            <td>' . $data['revision'] . '</td>
                            <td>' . $data['customer_no'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            '.$headerIsi.'
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

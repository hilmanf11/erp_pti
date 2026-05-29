<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_forecast extends CI_Controller
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
            $data['nik'] = $this->api->read('user', [], ['id' => $this->session->id])->number;

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_forecast');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
    	$month = $this->input->get("month");
    	$year = $this->input->get("year");

    	$data = $this->api->query("SELECT DISTINCT revision FROM mst_forecast WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
    	echo json_encode($data);
    }

    public function readProducts(){
    	$post = isset($_POST['q']) ? $_POST['q'] : "";

    	$p_month = $this->input->get('p_month');
    	$p_year = $this->input->get('p_year');
    	$revision = $this->input->get('revision');

        $q = "SELECT a.product_no, a.product_name, COALESCE(b.nickname, '') AS cust_nick, COALESCE(b.name, '') AS cust_name ".
        "FROM mst_forecast a LEFT JOIN mst_customer b ON a.customer_id = b.number ".
        "WHERE a.product_no LIKE '%$post%' AND a.p_month = '$p_month' AND a.p_year = '$p_year' AND a.revision = '$revision'";
    	$data = $this->api->query($q);

    	echo json_encode($data);
    }

    public function readMonths()
    {
        $month = base64_decode($this->input->post('month'));
        $year = base64_decode($this->input->post('year'));

        $start = strtotime($year . "-" . $month . "-01");
        $end =  strtotime(date('Y-m-d', strtotime('+12 month', strtotime($year . "-" . $month . "-01"))));

        while ($start < $end) {
            $monthName[] = date('M Y', $start);
            $start = strtotime("+1 month", $start);
        }

        die(json_encode($monthName));
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
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $customers = [];
            if (empty($filter_customer_id)) {
                $nik = $this->api->read("user", [], ["id" => $this->session->id])->number;
                $this->db->distinct()->select("customer")->from("setting_customer")->where(["user_id" => $nik]);
                $wheres = explode(',', $this->db->get()->row()->customer);
                $this->db->distinct()->select("TRIM(b.number) AS number")
                ->from("mst_line a")->join("mst_customer b", "a.customer_code = b.nickname")
                ->where_in("a.line_no", $wheres);
                foreach ($this->db->get()->result() as $index => $row)
                    { array_push($customers, $row->number); }
            }

            //Select Query
            $this->db->select('e.*, a.item_id, a.item_name, d.circuit_no, b.name as created_name, c.name as updated_name, f.name as customer_name');
            $this->db->from('mst_item a');
            $this->db->join('mst_forecast e', "a.item_id = e.product_no and e.p_month = '$filter_month' and e.p_year = '$filter_year' and e.revision = '$filter_revision'", 'left');
            $this->db->join('user b', 'e.created_by = b.username', 'left');
            $this->db->join('user c', 'e.updated_by = c.username', 'left');
            $this->db->join('wip_mst_wos_cct d', 'a.item_id = d.mstwos_assyno', 'left');
            $this->db->join('mst_customer f', 'a.id_customer = f.number', 'left');
            if ($filter_customer_id != 'all') {
                if (empty($customers)) { $this->db->where('e.customer_id', $filter_customer_id); }
                else { $this->db->where_in('e.customer_id', $customers); }
            }
            if ($filter_product_no != "") {
                $this->db->where('a.item_id', $filter_product_no);
            }
            $this->db->where('a.stscode_id', '01');
            $this->db->where("(month_1 > 0 or month_2 > 0 or month_3 > 0 or month_4 > 0 or month_5 > 0 or month_6 > 0 or month_7 > 0 or month_8 > 0 or month_9 > 0 or month_10 > 0)");
            $this->db->where('a.pfm_id', '06');
            $this->db->order_by('f.name');
            // die($this->db->get_compiled_select());
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
                $post = $this->input->post();
                $item = $this->api->read('mst_item', [], ["item_id" => $post['product_no']]);
                $customer = $this->api->read('mst_customer', [], ["number" => @$item->id_customer], "", "name", "asc");
                $forecasts = $this->api->reads('mst_forecast', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "product_no" => $post['product_no']
                ]);

                $nik = $this->api->read('user', [], ['id' => $this->session->id])->number;
                $this->db->distinct()->select("TRIM(b.customer_code) AS nick")
                ->from("setting_customer a")->join("mst_line b", "FIND_IN_SET(b.line_no, a.customer)")
                ->where(["user_id" => $nik])->order_by("nick");
                $nicks = [];
                foreach ($this->db->get()->result() as $index => $row) { array_push($nicks, $row->nick); }
                // die(var_dump($customer->nickname));
                if (count($forecasts) > 0) {
                    show_error("Duplicated Data");
                } elseif (!in_array($customer->nickname, $nicks)) {
                    show_error("You Not Allowed to Entry This Customer, ".$customer->name);
                } else {
                    $dataFinal = array_merge($post, array(
                        "customer_id" => @$customer->number,
                        "customer_name" => @$customer->name
                    ));

                    $send = $this->api->create('mst_forecast', $dataFinal, "FR", "mst_forecast");
                    echo $send;
                }
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
        	$p_month = $this->input->get('p_month');
        	$p_year = $this->input->get('p_year');
        	$revision = $this->input->get('revision');
            $product_no = base64_decode($this->input->get('product_no'));

            $post = $this->input->post();
            $send = $this->api->update('mst_forecast', ["p_month" => $p_month, "p_year" => $p_year, "revision" => $revision, "product_no" => $product_no], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_forecast', $data);
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

        // $send = $this->api->delete('mst_forecast', [
        //     "p_month" => $data->val(2, 3), 
        //     "p_year" => $data->val(2, 4), 
        //     "revision" => $data->val(3, 3)
        // ]);

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'product_no' => $data->val($i, 2),
                'month_1' => $data->val($i, 3),
                'month_2' => $data->val($i, 4),
                'month_3' => $data->val($i, 5),
                'month_4' => $data->val($i, 6),
                'month_5' => $data->val($i, 7),
                'month_6' => $data->val($i, 8),
                'month_7' => $data->val($i, 9),
                'month_8' => $data->val($i, 10),
                'month_9' => $data->val($i, 11),
                'month_10' => $data->val($i, 12),
                'month_11' => $data->val($i, 13),
                'month_12' => $data->val($i, 14)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');

            //Cek Data
            $item = $this->api->read('mst_item', [], ["item_id" => $data['product_no']]);
            $customer = $this->api->read('mst_customer', [], ["number" => @$item->id_customer], "", "name", "asc");
            $forecasts = $this->api->reads('mst_forecast', [], [
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                "product_no" => $data['product_no']
            ]);

            $nik = $this->api->read('user', [], ['id' => $this->session->id])->number;
            $this->db->distinct()->select("TRIM(b.customer_code) AS nick")
            ->from("setting_customer a")->join("mst_line b", "FIND_IN_SET(b.line_no, a.customer)")
            ->where(["user_id" => $nik])->order_by("nick");
            $nicks = [];
            foreach ($this->db->get()->result() as $index => $row) { array_push($nicks, $row->nick); }
            // die(var_dump($customer->nickname));
            if (empty($item->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (!in_array($customer->nickname, $nicks)) {
                echo json_encode(array("title" => "Failed", "message" => "You Not Allowed to Entry This Customer, ".$customer->name, "theme" => "error"));
            } elseif (count($forecasts) > 0) {
            	$send = $this->api->update('mst_forecast', [
	                "p_month" => $data['p_month'],
	                "p_year" => $data['p_year'],
	                "revision" => $data['revision'],
	                "product_no" => $data['product_no']], $data);

                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Update Successfully", "theme" => "success"));
            } else {
                $dataFinal = array_merge($data, array(
                    "product_name" => @$item->item_name,
                    "customer_id" => @$customer->number,
                    "customer_name" => @$customer->name
                ));

                $send   = $this->api->create('mst_forecast', $dataFinal, "FR", "FR");
                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Saved Successfully", "theme" => "success"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mst_forecast.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mst_forecast.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mst_forecast.txt";

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
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=mst_forecast_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_6 =  date('M Y', strtotime('+6 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_7 =  date('M Y', strtotime('+7 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_8 =  date('M Y', strtotime('+8 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_9 =  date('M Y', strtotime('+9 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_10 =  date('M Y', strtotime('+10 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_11 =  date('M Y', strtotime('+11 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $customers = [];
            if (empty($filter_customer_id)) {
                $nik = $this->api->read("user", [], ["id" => $this->session->id])->number;
                $this->db->distinct()->select("customer")->from("setting_customer")->where(["user_id" => $nik]);
                $wheres = explode(',', $this->db->get()->row()->customer);
                $this->db->distinct()->select("TRIM(customer_code) AS nick")->from("mst_line")->where_in("line_no", $wheres);
                foreach ($this->db->get()->result() as $index => $row)
                    { array_push($customers, $row->nick); }
            }

            //Select Query
            $this->db->select('e.*, a.item_id, a.item_name, d.circuit_no, b.name as created_name, c.name as updated_name, f.name as customer_name');
            $this->db->from('mst_item a');
            $this->db->join('mst_forecast e', "a.item_id = e.product_no and e.p_month = '$filter_month' and e.p_year = '$filter_year' and e.revision = '$filter_revision'", 'left');
            $this->db->join('user b', 'a.created_by = b.username', 'left');
            $this->db->join('user c', 'a.updated_by = c.username', 'left');
            $this->db->join('wip_mst_wos_cct d', 'a.item_id = d.mstwos_assyno', 'left');
            $this->db->join('mst_customer f', 'a.id_customer = f.number', 'left');
            if ($filter_customer_id != 'all') {
                if (empty($customers)) { $this->db->where('e.customer_id', $filter_customer_id); }
                else { $this->db->where_in('f.nickname', $customers); }
            }
            if ($filter_product_no != "") {
                $this->db->where('a.item_id', $filter_product_no);
            }
            $this->db->where('a.stscode_id', '01');
            $this->db->where('a.pfm_id', '06');
            $this->db->order_by('f.name');
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
                                <small>MASTER FORECAST</small>
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
                <th style="text-align:center;" rowspan="2" width="20">No</th>
                <th style="text-align:center;" rowspan="2">Customer</th>
                <th style="text-align:center;" rowspan="2">Product No</th>
                <th style="text-align:center;" rowspan="2">Product Name</th>
                <th style="text-align:center;" rowspan="2">CCT</th>
                <th style="text-align:center;" colspan="12">Month</th>
            </tr>
            <tr>
                <th style="text-align:center;">' . $month_0 . '</th>
                <th style="text-align:center;">' . $month_1 . '</th>
                <th style="text-align:center;">' . $month_2 . '</th>
                <th style="text-align:center;">' . $month_3 . '</th>
                <th style="text-align:center;">' . $month_4 . '</th>
                <th style="text-align:center;">' . $month_5 . '</th>
                <th style="text-align:center;">' . $month_6 . '</th>
                <th style="text-align:center;">' . $month_7 . '</th>
                <th style="text-align:center;">' . $month_8 . '</th>
                <th style="text-align:center;">' . $month_9 . '</th>
                <th style="text-align:center;">' . $month_10 . '</th>
                <th style="text-align:center;">' . $month_11 . '</th>
            </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td style="text-align:center;">' . $data['month_1'] . '</td>
                            <td style="text-align:center;">' . $data['month_2'] . '</td>
                            <td style="text-align:center;">' . $data['month_3'] . '</td>
                            <td style="text-align:center;">' . $data['month_4'] . '</td>
                            <td style="text-align:center;">' . $data['month_5'] . '</td>
                            <td style="text-align:center;">' . $data['month_6'] . '</td>
                            <td style="text-align:center;">' . $data['month_7'] . '</td>
                            <td style="text-align:center;">' . $data['month_8'] . '</td>
                            <td style="text-align:center;">' . $data['month_9'] . '</td>
                            <td style="text-align:center;">' . $data['month_10'] . '</td>
                            <td style="text-align:center;">' . $data['month_11'] . '</td>
                            <td style="text-align:center;">' . $data['month_12'] . '</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}

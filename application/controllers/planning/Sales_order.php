<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order extends CI_Controller
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
            $this->load->view('planning/sales_order');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM sales_order WHERE p_month = '$month' and p_year='$year'");
        echo json_encode($data);
    }

    public function readMonths()
    {
        $month = base64_decode($this->input->post('month'));
        $year = base64_decode($this->input->post('year'));

        $start = strtotime($year . "-" . $month . "-01");
        $end =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($year . "-" . $month . "-01"))));

        while ($start > $end) {
            $monthName[] = date('M Y', $start);
            $start = strtotime("-1 month", $start);
        }

        die(json_encode($monthName));
    }

    public function datatables()
    {
        $this->pg = $this->load->database('pg2', TRUE);
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer = trim(base64_decode($this->input->get('filter_customer_id')));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $planConfig = $this->api->read('planning_config');
            if ($planConfig->cutoff_current == "on") {
                $startMonth = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
                $endMonth =  date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-28"));
            } else {
                $startMonth =  date('Y-m-d', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-" . $planConfig->cutoff_day_from)));
                $endMonth = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-" . $planConfig->cutoff_day_to));
            }

            $monthStart = strtotime($startMonth);
            $monthEnd =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($startMonth))));

            $monthStart2 = strtotime($endMonth);
            $monthEnd2 =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($endMonth))));

            while ($monthStart > $monthEnd) {
                $months = date('Y-m-d', $monthStart);
                $arrayMonth[] = $months;
                $monthStart = strtotime("-1 month", $monthStart);
            }

            while ($monthStart2 > $monthEnd2) {
                $months2 = date('Y-m-d', $monthStart2);
                $arrayMonth2[] = $months2;
                $monthStart2 = strtotime("-1 month", $monthStart2);
            }

            $items = $this->api->reads("mst_item", ["item_id" => $filter_product_no, "id_customer" => $filter_customer], ["stscode_id" => "01", "pfm_id" => "06"]);
            foreach ($items as $item) {
                $item_id = $item->item_id;
                $item_name = $item->item_name;
                $cust_id = $item->id_customer;

                $query = $this->pg->query("SELECT a.item_id, a.item_name, a.cust_id, b.cust_name, 
                    coalesce(month_1.qty_1,0) as qty_1,
                    coalesce(month_2.qty_2,0) as qty_2,
                    coalesce(month_3.qty_3,0) as qty_3,
                    coalesce(month_4.qty_4,0) as qty_4,
                    coalesce(month_5.qty_5,0) as qty_5,
                    coalesce(month_6.qty_6,0) as qty_6,
                    round((coalesce(month_1.qty_1,0) + coalesce(month_2.qty_2,0) + coalesce(month_3.qty_3,0) + coalesce(month_4.qty_4,0) + coalesce(month_5.qty_5,0) + coalesce(month_6.qty_6,0))/ 6) as xbar 
                    FROM mst_item a 
                    LEFT JOIN
                    (SELECT a.item_id, b.item_name, c.cust_id, c.cust_name, SUM(a.soc_reqqty) as qty_1 FROM soc a 
                    JOIN mst_item b on a.item_id = b.item_id 
                    LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                    WHERE a.soc_date between '$arrayMonth[0]' and '$arrayMonth2[0]'
                    GROUP BY a.item_id, b.item_name, c.cust_id, c.cust_name) as month_1 ON a.item_id = month_1.item_id
                    LEFT JOIN 
                    (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_2 FROM soc a 
                    JOIN mst_item b on a.item_id = b.item_id 
                    LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                    WHERE a.soc_date between '$arrayMonth[1]' and '$arrayMonth2[1]'
                    GROUP BY a.item_id, b.item_name, c.cust_name) as month_2 on a.item_id = month_2.item_id
                    LEFT JOIN
                    (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_3 FROM soc a 
                    JOIN mst_item b on a.item_id = b.item_id 
                    LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                    WHERE a.soc_date between '$arrayMonth[2]' and '$arrayMonth2[2]'
                    GROUP BY a.item_id, b.item_name, c.cust_name) as month_3 on a.item_id = month_3.item_id
                    LEFT JOIN
                    (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_4 FROM soc a 
                    JOIN mst_item b on a.item_id = b.item_id 
                    LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                    WHERE a.soc_date between '$arrayMonth[3]' and '$arrayMonth2[3]'
                    GROUP BY a.item_id, b.item_name, c.cust_name) as month_4 on a.item_id = month_4.item_id
                    LEFT JOIN
                    (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_5 FROM soc a 
                    JOIN mst_item b on a.item_id = b.item_id 
                    LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                    WHERE a.soc_date between '$arrayMonth[4]' and '$arrayMonth2[4]'
                    GROUP BY a.item_id, b.item_name, c.cust_name) as month_5 on a.item_id = month_5.item_id
                    LEFT JOIN
                    (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_6 FROM soc a 
                    JOIN mst_item b on a.item_id = b.item_id 
                    LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                    WHERE a.soc_date between '$arrayMonth[5]' and '$arrayMonth2[5]'
                    GROUP BY a.item_id, b.item_name, c.cust_name) as month_6 on a.item_id = month_6.item_id
                    LEFT JOIN r_customer b ON a.cust_id = b.cust_id
                    WHERE a.stscode_id = '01' and a.pfm_id = '06' and a.item_id = '$item_id'
                    ORDER BY a.cust_id asc");
                $record = $query->row();

                if ($record) {
                    $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $record->item_id]);
                    $arr[] = array(
                        "item_id" => $record->item_id,
                        "item_name" => $record->item_name,
                        "cust_id" => $record->cust_id,
                        "cust_name" => $record->cust_name,
                        "circuit_no" => @$cct->circuit_no,
                        "qty_1" => $record->qty_1,
                        "qty_2" => $record->qty_2,
                        "qty_3" => $record->qty_3,
                        "qty_4" => $record->qty_4,
                        "qty_5" => $record->qty_5,
                        "qty_6" => $record->qty_6,
                        "xbar" => $record->xbar
                    );
                } else {
                    $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $item_id]);
                    $customer = $this->api->read("mst_customer", [], ["number" => $cust_id]);
                    $arr[] = array(
                        "item_id" => $item_id,
                        "item_name" => $item_name,
                        "cust_id" => $cust_id,
                        "cust_name" => @$customer->name,
                        "circuit_no" => @$cct->circuit_no,
                        "qty_1" => 0,
                        "qty_2" => 0,
                        "qty_3" => 0,
                        "qty_4" => 0,
                        "qty_5" => 0,
                        "qty_6" => 0,
                        "xbar" => 0
                    );
                }
            }

            //Mapping Data
            $result['total'] = count($arr);
            $result = array_merge($result, array("rows" => $arr));
            echo json_encode($result);
        }
    }

    public function datatables_uploaded()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_customer = trim(base64_decode($this->input->get('filter_customer_id')));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $filters = json_decode($this->input->post('filterRules'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*');
            $this->db->from('sales_order a');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->like('a.product_no', $filter_product_no);
            if(@count($filters) > 0){
                foreach ($filters as $filter) {
                    if($filter->field == "p_month"){
                        $this->db->like("a.p_month", $filter->value);
                    }elseif($filter->field == "p_year"){
                        $this->db->like("a.p_year", $filter->value);
                    }elseif($filter->field == "revision"){
                        $this->db->like("a.revision", $filter->value);
                    }elseif($filter->field == "customer_name"){
                        $this->db->like("a.customer_name", $filter->value);
                    }elseif($filter->field == "product_no"){
                        $this->db->like("a.product_no", $filter->value);
                    }elseif($filter->field == "product_name"){
                        $this->db->like("a.product_name", $filter->value);
                    }elseif($filter->field == "circuit_no"){
                        $this->db->like("a.circuit_no", $filter->value);
                    }elseif($filter->field == "qty_1"){
                        $this->db->like("a.qty_1", $filter->value);
                    }elseif($filter->field == "qty_2"){
                        $this->db->like("a.qty_2", $filter->value);
                    }elseif($filter->field == "qty_3"){
                        $this->db->like("a.qty_3", $filter->value);
                    }elseif($filter->field == "qty_4"){
                        $this->db->like("a.qty_4", $filter->value);
                    }elseif($filter->field == "qty_5"){
                        $this->db->like("a.qty_5", $filter->value);
                    }elseif($filter->field == "qty_6"){
                        $this->db->like("a.qty_6", $filter->value);
                    }elseif($filter->field == "xbar"){
                        $this->db->like("a.xbar", $filter->value);
                    }
                }
            }
            $this->db->group_by('a.id');
            $this->db->order_by('a.customer_name', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result['rows'] = $records;
            echo json_encode($result);
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('sales_order', $data);
        echo $send;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $p_month = $this->input->post('p_month');
            $p_year = $this->input->post('p_year');
            $revision = $this->input->post('revision');
            $salesOrder = $this->api->reads('sales_order', [], [
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['item_id']),
                "customer_id" => $post['cust_id']
            ]);

            $postFinal = array(
                "trans_date" => date("Y-m-d"),
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "customer_id" => $post['cust_id'],
                "customer_name" => $post['cust_name'],
                "product_no" => trim($post['item_id']),
                "product_name" => $post['item_name'],
                "circuit_no" => $post['circuit_no'],
                "qty_1" => $post['qty_1'],
                "qty_2" => $post['qty_2'],
                "qty_3" => $post['qty_3'],
                "qty_4" => $post['qty_4'],
                "qty_5" => $post['qty_5'],
                "qty_6" => $post['qty_6'],
                "xbar" => $post['xbar']
            );

            if (count($salesOrder) > 0) {
                $send   = $this->api->update('sales_order', [
                    "p_month" => $p_month,
                    "p_year" => $p_year,
                    "revision" => $revision,
                    "product_no" => trim($post['item_id']),
                ], $postFinal);
                echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Updated Successfully", "theme" => "success"));
            } else {
                $send   = $this->api->create('sales_order', $postFinal, "SO", "sales_order");
                echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Saved Successfully", "theme" => "success"));
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
            $send = $this->api->update('sales_order', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
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
        
        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'customer_id' => $data->val($i, 2),
                'customer_name' => $data->val($i, 3),
                'product_no' => $data->val($i, 4),
                'product_name' => $data->val($i, 5),
                'circuit_no' => $data->val($i, 6),
                'qty_1' => $data->val($i, 7),
                'qty_2' => $data->val($i, 8),
                'qty_3' => $data->val($i, 9),
                'qty_4' => $data->val($i, 10),
                'qty_5' => $data->val($i, 11),
                'qty_6' => $data->val($i, 12),
                'xbar' => $data->val($i, 13),
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

            //Cek Data
            $item = $this->api->read('mst_item', [], ["item_id" => $data['product_no']]);
            $sales_order = $this->api->reads('sales_order', [], [
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                "product_no" => $data['product_no']
            ]);

            if (empty($item->item_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (count($sales_order) > 0) {
                $this->api->update('sales_order', [
                    "p_month" => $data['p_month'],
                    "p_year" => $data['p_year'],
                    "revision" => $data['revision'],
                    "product_no" => $data['product_no']
                ], $data);

                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Updated Successfully", "theme" => "success"));
            } else {
                $this->api->create('sales_order', $data, "SO", "SO");
                echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Saved Successfully", "theme" => "success"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/sales_order.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/sales_order.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/sales_order.txt";

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
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = trim(base64_decode($this->input->get('filter_customer_id')));
        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

        $planConfig = $this->api->read('planning_config');
        if ($planConfig->cutoff_current == "on") {
            $startMonth = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-01"));
            $endMonth =  date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-28"));
        } else {
            $startMonth =  date('Y-m-d', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-" . $planConfig->cutoff_day_from)));
            $endMonth = date("Y-m-d", strtotime($filter_year . "-" . $filter_month . "-" . $planConfig->cutoff_day_to));
        }

        $monthStart = strtotime($startMonth);
        $monthEnd =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($startMonth))));

        $monthStart2 = strtotime($endMonth);
        $monthEnd2 =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($endMonth))));

        while ($monthStart > $monthEnd) {
            $months = date('Y-m-d', $monthStart);
            $arrayMonth[] = $months;
            $monthStart = strtotime("-1 month", $monthStart);
        }

        while ($monthStart2 > $monthEnd2) {
            $months2 = date('Y-m-d', $monthStart2);
            $arrayMonth2[] = $months2;
            $monthStart2 = strtotime("-1 month", $monthStart2);
        }

        $items = $this->api->reads("mst_item", ["item_id" => $filter_product_no, "id_customer" => $filter_customer], ["stscode_id" => "01", "pfm_id" => "06"]);
        foreach ($items as $item) {
            $item_id = $item->item_id;
            $item_name = $item->item_name;
            $cust_id = $item->id_customer;

            $query = $this->pg->query("SELECT a.item_id, a.item_name, a.cust_id, b.cust_name, 
                coalesce(month_1.qty_1,0) as qty_1,
                coalesce(month_2.qty_2,0) as qty_2,
                coalesce(month_3.qty_3,0) as qty_3,
                coalesce(month_4.qty_4,0) as qty_4,
                coalesce(month_5.qty_5,0) as qty_5,
                coalesce(month_6.qty_6,0) as qty_6,
                round((coalesce(month_1.qty_1,0) + coalesce(month_2.qty_2,0) + coalesce(month_3.qty_3,0) + coalesce(month_4.qty_4,0) + coalesce(month_5.qty_5,0) + coalesce(month_6.qty_6,0))/ 6) as xbar 
                FROM mst_item a 
                LEFT JOIN
                (SELECT a.item_id, b.item_name, c.cust_id, c.cust_name, SUM(a.soc_reqqty) as qty_1 FROM soc a 
                JOIN mst_item b on a.item_id = b.item_id 
                LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                WHERE a.soc_date between '$arrayMonth[0]' and '$arrayMonth2[0]'
                GROUP BY a.item_id, b.item_name, c.cust_id, c.cust_name) as month_1 ON a.item_id = month_1.item_id
                LEFT JOIN 
                (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_2 FROM soc a 
                JOIN mst_item b on a.item_id = b.item_id 
                LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                WHERE a.soc_date between '$arrayMonth[1]' and '$arrayMonth2[1]'
                GROUP BY a.item_id, b.item_name, c.cust_name) as month_2 on a.item_id = month_2.item_id
                LEFT JOIN
                (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_3 FROM soc a 
                JOIN mst_item b on a.item_id = b.item_id 
                LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                WHERE a.soc_date between '$arrayMonth[2]' and '$arrayMonth2[2]'
                GROUP BY a.item_id, b.item_name, c.cust_name) as month_3 on a.item_id = month_3.item_id
                LEFT JOIN
                (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_4 FROM soc a 
                JOIN mst_item b on a.item_id = b.item_id 
                LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                WHERE a.soc_date between '$arrayMonth[3]' and '$arrayMonth2[3]'
                GROUP BY a.item_id, b.item_name, c.cust_name) as month_4 on a.item_id = month_4.item_id
                LEFT JOIN
                (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_5 FROM soc a 
                JOIN mst_item b on a.item_id = b.item_id 
                LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                WHERE a.soc_date between '$arrayMonth[4]' and '$arrayMonth2[4]'
                GROUP BY a.item_id, b.item_name, c.cust_name) as month_5 on a.item_id = month_5.item_id
                LEFT JOIN
                (SELECT a.item_id, b.item_name, c.cust_name, SUM(a.soc_reqqty) as qty_6 FROM soc a 
                JOIN mst_item b on a.item_id = b.item_id 
                LEFT JOIN r_customer c on b.cust_id = c.cust_id 
                WHERE a.soc_date between '$arrayMonth[5]' and '$arrayMonth2[5]'
                GROUP BY a.item_id, b.item_name, c.cust_name) as month_6 on a.item_id = month_6.item_id
                LEFT JOIN r_customer b ON a.cust_id = b.cust_id
                WHERE a.stscode_id = '01' and a.pfm_id = '06' and a.item_id = '$item_id'
                ORDER BY a.cust_id asc");
            $record = $query->row();

            if ($record) {
                $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $record->item_id]);
                $records[] = array(
                    "item_id" => $record->item_id,
                    "item_name" => $record->item_name,
                    "cust_id" => $record->cust_id,
                    "cust_name" => $record->cust_name,
                    "circuit_no" => @$cct->circuit_no,
                    "qty_1" => $record->qty_1,
                    "qty_2" => $record->qty_2,
                    "qty_3" => $record->qty_3,
                    "qty_4" => $record->qty_4,
                    "qty_5" => $record->qty_5,
                    "qty_6" => $record->qty_6,
                    "xbar" => $record->xbar
                );
            } else {
                $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $item_id]);
                $customer = $this->api->read("mst_customer", [], ["number" => $cust_id]);
                $records[] = array(
                    "item_id" => $item_id,
                    "item_name" => $item_name,
                    "cust_id" => $cust_id,
                    "cust_name" => @$customer->name,
                    "circuit_no" => @$cct->circuit_no,
                    "qty_1" => 0,
                    "qty_2" => 0,
                    "qty_3" => 0,
                    "qty_4" => 0,
                    "qty_5" => 0,
                    "qty_6" => 0,
                    "xbar" => 0
                );
            }
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
                            <small>MASTER SALES ORDER</small>
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
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Customer No</th>
                <th rowspan="2" style="text-align:center;">Customer Name</th>
                <th rowspan="2" style="text-align:center;">Product No</th>
                <th rowspan="2" style="text-align:center;">Product Name</th>
                <th rowspan="2" style="text-align:center;">CCT</th>
                <th colspan="6" style="text-align:center;">Period</th>
                <th rowspan="2" style="text-align:center;">Xbar</th>
            </tr><tr>';

        $start = strtotime($filter_year . "-" . $filter_month . "-01");
        $end =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($filter_year . "-" . $filter_month . "-01"))));

        while ($start > $end) {
            $monthName = date('M Y', $start);
            $html .= '<th style="text-align:center;">' . $monthName . '</th>';
            $start = strtotime("-1 month", $start);
        }

        $no = 1;
        foreach ($records as $data) {
            $html .= '  </tr><tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['cust_id'] . '</td>
                            <td>' . $data['cust_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td style="text-align:right;">' . $data['qty_1'] . '</td>
                            <td style="text-align:right;">' . $data['qty_2'] . '</td>
                            <td style="text-align:right;">' . $data['qty_3'] . '</td>
                            <td style="text-align:right;">' . $data['qty_4'] . '</td>
                            <td style="text-align:right;">' . $data['qty_5'] . '</td>
                            <td style="text-align:right;">' . $data['qty_6'] . '</td>
                            <td style="text-align:right;">' . $data['xbar'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function uploadedPrint($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer = trim(base64_decode($this->input->get('filter_customer_id')));
        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

        //Select Query
        $this->db->select('a.*, c.name as approved_by, b.updated_date as approved_date');
        $this->db->from('sales_order a');
        $this->db->join('notification b', 'a.id = b.table_id', 'left');
        $this->db->join('user c', 'b.id_user_from = c.username', 'left');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
        }
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.id');
        $this->db->order_by('a.customer_name', 'asc');
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
                            <small>MASTER SALES ORDER</small>
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
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Customer</th>
                <th rowspan="2" style="text-align:center;">Product No</th>
                <th rowspan="2" style="text-align:center;">Product Name</th>
                <th rowspan="2" style="text-align:center;">CCT</th>
                <th colspan="6" style="text-align:center;">Period</th>
                <th rowspan="2" style="text-align:center;">Xbar</th>
            </tr><tr>';

        $start = strtotime($filter_year . "-" . $filter_month . "-01");
        $end =  strtotime(date('Y-m-d', strtotime('-6 month', strtotime($filter_year . "-" . $filter_month . "-01"))));

        while ($start > $end) {
            $monthName = date('M Y', $start);
            $html .= '<th style="text-align:center;">' . $monthName . '</th>';
            $start = strtotime("-1 month", $start);
        }

        $no = 1;
        foreach ($records as $data) {
            $html .= '  </tr><tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td style="text-align:right;">' . $data['qty_1'] . '</td>
                            <td style="text-align:right;">' . $data['qty_2'] . '</td>
                            <td style="text-align:right;">' . $data['qty_3'] . '</td>
                            <td style="text-align:right;">' . $data['qty_4'] . '</td>
                            <td style="text-align:right;">' . $data['qty_5'] . '</td>
                            <td style="text-align:right;">' . $data['qty_6'] . '</td>
                            <td style="text-align:right;">' . $data['xbar'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

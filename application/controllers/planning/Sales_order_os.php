<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order_os extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[sales_order_os.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['nik'] = $this->api->read('user', [], ['id' => $this->session->id])->number;

            $this->load->view('template/header', $data);
            $this->load->view('planning/sales_order_os');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM sales_order_os WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
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

            // $items = $this->api->reads("mst_item", ["item_id" => $filter_product_no, "id_customer" => $filter_customer], ["stscode_id" => "01", "pfm_id" => "06"]);
            $this->db->distinct()->select("*")->from("mst_item")
            ->like(["item_id" => $filter_product_no])
            ->where(["stscode_id" => "01", "pfm_id" => "06"])
            ->where_in("id_customer", $customers);
            foreach ($this->db->get()->result() as $item) {
                $item_id = $item->item_id;
                $item_name = $item->item_name;
                $cust_id = $item->id_customer;

                //Select Query
                $query = $this->pg->query("SELECT a.item_id, a.cust_id, b.item_name, 
                    coalesce(sum(a.soc_reqqty), 0) as total_soc,
                    coalesce(sum(c.qty_sod), 0) as total_sod,
                    (coalesce(sum(a.soc_reqqty), 0) - coalesce(sum(c.qty_sod), 0)) as total_so
                from mst_item b
                left join soc a on a.item_id = b.item_id 
                left join (select sod_socid, sum(sod_scanqty) as qty_sod, item_id from sod group by sod_socid, item_id) c on a.soc_id = c.sod_socid and a.item_id = c.item_id 
                where 1=1 and b.stscode_id = '01' and b.pfm_id = '06' and a.soc_date >= '2021-01-01' and a.soc_date <= '$startMonth' and a.item_id = '$item_id'
                GROUP BY a.item_id, a.cust_id, b.item_name");
                $record = $query->row();

                if ($record) {
                    $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $record->item_id]);
                    $customer = $this->api->read("mst_customer", [], ["number" => $record->cust_id]);
                    $arr[] = array(
                        "item_id" => $record->item_id,
                        "item_name" => $record->item_name,
                        "cust_id" => $record->cust_id,
                        "cust_name" => @$customer->name,
                        "circuit_no" => @$cct->circuit_no,
                        "total_soc" => $record->total_soc,
                        "total_sod" => $record->total_sod,
                        "total_so" => $record->total_so
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
                        "total_soc" => 0,
                        "total_sod" => 0,
                        "total_so" => 0
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
            $this->db->select('a.*');
            $this->db->from('sales_order_os a');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
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
                    }elseif($filter->field == "qty"){
                        $this->db->like("a.qty", $filter->value);
                    }
                }
            }
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->like('a.product_no', $filter_product_no);
            $this->db->where_in("a.customer_id", $customers);
            $this->db->group_by('a.id');
            $this->db->order_by('a.trans_date', 'desc');
            // die($this->db->get_compiled_select());

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

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $p_month = $this->input->post('p_month');
            $p_year = $this->input->post('p_year');
            $revision = $this->input->post('revision');
            $salesOrder = $this->api->reads('sales_order_os', [], [
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['item_id']),
                "customer_id" => $post['cust_id']
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "customer_id" => $post['cust_id'],
                "customer_name" => $post['cust_name'],
                "product_no" => trim($post['item_id']),
                "product_name" => $post['item_name'],
                "circuit_no" => $post['circuit_no'],
                "qty" => $post['total_so']
            );

            if($post['item_id'] != ""){
                if (count($salesOrder) > 0) {
                    $send   = $this->api->update('sales_order_os', [
                        "p_month" => $p_month,
                        "p_year" => $p_year,
                        "revision" => $revision,
                        "customer_id" => $post['cust_id'],
                        "customer_name" => $post['cust_name'],
                        "product_no" => trim($post['item_id']),
                        "product_name" => $post['item_name']
                    ], $postFinal);
                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $send   = $this->api->create('sales_order_os', $postFinal, "SOO", "sales_order_os");
                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['item_id'] . " Cannot Saved", "theme" => "error"));
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
            $send = $this->api->update('sales_order_os', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('sales_order_os', $data);
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
                'cust_id' => $data->val($i, 2),
                'cust_name' => $data->val($i, 3),
                'item_id' => $data->val($i, 4),
                'item_name' => $data->val($i, 5),
                'circuit_no' => $data->val($i, 6),
                'total_so' => $data->val($i, 7),
                'status' => "OPEN"
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $p_month = $post['p_month'];
            $p_year = $post['p_year'];
            $revision = $post['revision'];
            $salesOrder = $this->api->reads('sales_order_os', [], [
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['item_id']),
                "customer_id" => $post['cust_id']
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "customer_id" => $post['cust_id'],
                "customer_name" => $post['cust_name'],
                "product_no" => trim($post['item_id']),
                "product_name" => $post['item_name'],
                "circuit_no" => $post['circuit_no'],
                "qty" => $post['total_so']
            );

            if($post['item_id'] != ""){
                $customer = $this->api->read('mst_customer', [], ['number' => $post['cust_id']]);
                $nik = $this->api->read('user', [], ['id' => $this->session->id])->number;
                $this->db->distinct()->select("TRIM(b.customer_code) AS nick")
                ->from("setting_customer a")->join("mst_line b", "FIND_IN_SET(b.line_no, a.customer)")
                ->where(["user_id" => $nik])->order_by("nick");
                $nicks = [];
                foreach ($this->db->get()->result() as $index => $row) { array_push($nicks, $row->nick); }
                // die(var_dump($customer->nickname));
                if (!in_array($customer->nickname, $nicks)) {
                    echo json_encode(array("title" => "Failed", "message" => "You Not Allowed to Entry This Customer, ".$customer->name, "theme" => "error"));
                } elseif (count($salesOrder) > 0) {
                    $this->api->update('sales_order_os', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => $post['item_id']
                    ], $postFinal);
    
                    echo json_encode(array("title" => "Good Job", "message" => "Product No" . $post['item_id'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $this->api->create('sales_order_os', $postFinal, "SOO", "sales_order_os");
                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['item_id'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['item_id'] . " Saved Failed", "theme" => "error"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/os_sales_order.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/os_sales_order.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/os_sales_order.txt";

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
    	$this->pg = $this->load->database('pg2', TRUE);
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_order_os_$format.xls");
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

        // $items = $this->api->reads("mst_item", ["item_id" => $filter_product_no, "id_customer" => $filter_customer], ["stscode_id" => "01", "pfm_id" => "06"]);
        // foreach ($items as $item) {
        $this->db->distinct()->select("*")->from("mst_item")
        ->like(["item_id" => $filter_product_no])
        ->where(["stscode_id" => "01", "pfm_id" => "06"])
        ->where_in("id_customer", $customers);
        foreach ($this->db->get()->result() as $item) {
            $item_id = $item->item_id;
            $item_name = $item->item_name;
            $cust_id = $item->id_customer;

            //Select Query
            $query = $this->pg->query("SELECT a.item_id, a.cust_id, b.item_name, 
                    coalesce(sum(a.soc_reqqty), 0) as total_soc,
                    coalesce(sum(c.qty_sod), 0) as total_sod,
                    (coalesce(sum(a.soc_reqqty), 0) - coalesce(sum(c.qty_sod), 0)) as total_so
                from mst_item b
                left join soc a on a.item_id = b.item_id 
                left join (select sod_socid, sum(sod_scanqty) as qty_sod, item_id from sod group by sod_socid, item_id) c on a.soc_id = c.sod_socid and a.item_id = c.item_id 
                where 1=1 and b.stscode_id = '01' and b.pfm_id = '06' and a.soc_date >= '2021-01-01' and a.soc_date <= '$startMonth' and a.item_id = '$item_id'
                GROUP BY a.item_id, a.cust_id, b.item_name");
            $record = $query->row();

            if ($record) {
                $cct = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => $record->item_id]);
                $customer = $this->api->read("mst_customer", [], ["number" => $record->cust_id]);
                $records[] = array(
                    "item_id" => $record->item_id,
                    "item_name" => $record->item_name,
                    "cust_id" => $record->cust_id,
                    "cust_name" => @$customer->name,
                    "circuit_no" => @$cct->circuit_no,
                    "total_soc" => $record->total_soc,
                    "total_sod" => $record->total_sod,
                    "total_so" => $record->total_so
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
                    "total_soc" => 0,
                    "total_sod" => 0,
                    "total_so" => 0
                );
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">MONTH</td>
                <td width="10">:</td>
                <td>' . $filter_month . '<td>
            </tr>
            <tr>
                <td width="80">YEAR</td>
                <td width="10">:</td>
                <td>' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="10">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>CCT</th>
                <th>Qty</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['cust_id'] . '</td>
                            <td>' . $data['cust_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td>' . $data['total_so'] . '</td>
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
            header("Content-Disposition: attachment; filename=sales_order_os_$format.xls");
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
        $this->db->select('*');
        $this->db->from('sales_order_os');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $this->db->like('customer_id', $filter_customer);
        $this->db->like('product_no', $filter_product_no);
        $this->db->group_by('id');
        $this->db->order_by('trans_date', 'desc');
        // die($this->db->get_compiled_select());
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
                            <small>MASTER SALES ORDER OUTSTANDING</small>
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
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">Customer</th>
                <th style="text-align:center;">Product No</th>
                <th style="text-align:center;">Product Name</th>
                <th style="text-align:center;">CCT</th>
                <th style="text-align:center;">Qty</th>
            </tr><tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  </tr><tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Os_supply extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[fg_stock.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/os_supply');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('os_supply', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM os_supply WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
    }

    public function readProductFamily(){
        $this->pg = $this->load->database('pg', TRUE);
        $query = $this->pg->query("SELECT * FROM r_prodfam WHERE prod_group = 'Raw Material' and pfm_id != '05' ORDER BY pfm_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readProducts($pfm_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->pg = $this->load->database('pg', TRUE);
        $query = $this->pg->query("SELECT * FROM mst_item WHERE pfm_id = '$pfm_id' and stscode_id = '01' and item_id LIKE '%$post%' ORDER BY item_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function datatables()
    {
        $this->pg = $this->load->database('pg', TRUE);
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            $cutoff =  date('Y-m-01', strtotime("-1 month", strtotime($filter_cutoff)));

            $itemq = $this->pg->query("SELECT b.item_id, c.item_name, d.pfm_name, COALESCE(SUM(b.qty_need) - SUM(b.qty_scan), 0) as stock
                from wip_trx_mpp a 
                join (SELECT item_id, kanbanrm_woc_id, qty_need, SUM(qty_scan) as qty_scan FROM serial_detail_kanbanrm GROUP BY item_id, kanbanrm_woc_id, qty_need) b ON a.temp_woc_id = b.kanbanrm_woc_id
                join mst_item c on b.item_id = c.item_id and c.stscode_id = '01'
                join r_prodfam d on c.pfm_id = d.pfm_id and d.prod_group = 'Raw Material' and d.pfm_id != '05'
                where a.wp_date between '$cutoff' and '$filter_cutoff' and d.pfm_id LIKE '%$filter_product_family%' and c.item_id LIKE '%$filter_product_no%'
                group by b.item_id, c.item_name, d.pfm_name
                having COALESCE(SUM(b.qty_need) - SUM(b.qty_scan), 0) > 0
                order by d.pfm_name, COALESCE(SUM(b.qty_need) - SUM(b.qty_scan), 0) asc
            ");
            $items = $itemq->result_array();
            $arr = array();

            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $item_name = $item['item_name'];
                $pfm_name = $item['pfm_name'];
                $qty = abs($item['stock']);

                $os_supply = $this->api->read('os_supply', [], [
                    "product_no" => $item_id,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision
                ]);

                if ($os_supply) {
                    if($os_supply->status == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $os_supply->status;
                    }

                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $filter_cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($qty),
                        "status" => $status,
                    );
                } else {
                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $filter_cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($qty),
                        "status" => "EMPTY",
                    );
                }
            }

            echo json_encode($arr);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $os_supply = $this->api->reads('os_supply', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            if($post['product_no'] != ""){
                if (count($os_supply) > 0) {
                    $send   = $this->api->update('os_supply', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => trim($post['product_no'])
                    ], $post);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $send   = $this->api->create('os_supply', $post, "RMS", "os_supply");

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Cannot Saved", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('os_supply', $data);
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

        $send = $this->api->delete('fg_stock', [
            "p_month" => $data->val(2, 3), 
            "p_year" => $data->val(2, 4), 
            "revision" => $data->val(3, 3),
            "cutoff" => $data->val(4, 3),
        ]);

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                "cutoff" => $data->val(4, 3),
                'product_no' => $data->val($i, 2),
                'product_name' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
                'status' => "UPLOAD"
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
            $cutoff = $post['cutoff'];

            $os_supply = $this->api->reads('os_supply', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['item_id']),
                "product_name" => $post['item_name'],
                "product_family" => @$os_supply[0]->product_family,
                "actual" => $post['qty'],
                "status" => $post['status']
            );

            if($post['item_id'] != ""){
                if (count($os_supply) > 0) {
                    $send   = $this->api->update('os_supply', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => trim($post['product_no'])
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Not Generate", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Saved Failed", "theme" => "error"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/fg_stock.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/fg_stock.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/fg_stock.txt";

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
        $this->pg = $this->load->database('pg', TRUE);
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=os_supply_rm_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

        // $cutoff =  date('Y-m-01', strtotime('-1 month', strtotime($filter_cutoff)));
        $cutoff =  date('Y-m-01', strtotime($filter_cutoff));

        $itemq = $this->pg->query("SELECT b.item_id, c.item_name, d.pfm_name, COALESCE(SUM(b.qty_need) - SUM(b.qty_scan), 0) as stock
            from wip_trx_mpp a 
            join (SELECT item_id, kanbanrm_woc_id, qty_need, SUM(qty_scan) as qty_scan FROM serial_detail_kanbanrm GROUP BY item_id, kanbanrm_woc_id, qty_need) b ON a.temp_woc_id = b.kanbanrm_woc_id
            join mst_item c on b.item_id = c.item_id and c.stscode_id = '01'
            join r_prodfam d on c.pfm_id = d.pfm_id and d.prod_group = 'Raw Material' and d.pfm_id != '05'
            where a.wp_date between '$cutoff' and '$filter_cutoff' and d.pfm_id LIKE '%$filter_product_family%' and c.item_id LIKE '%$filter_product_no%'
            group by b.item_id, c.item_name, d.pfm_name
            having COALESCE(SUM(b.qty_need) - SUM(b.qty_scan), 0) > 0
            order by d.pfm_name, COALESCE(SUM(b.qty_need) - SUM(b.qty_scan), 0) asc
        ");
        $items = $itemq->result_array();
        $records = array();

        foreach ($items as $item) {
            $item_id = $item['item_id'];
            $item_name = $item['item_name'];
            $pfm_name = $item['pfm_name'];
            $qty = abs($item['stock']);

            $os_supply = $this->api->read('os_supply', [], [
                "product_no" => $item_id,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            if ($os_supply) {
                if($os_supply->status == "EMPTY"){
                    $status = "GENERATE";
                }else{
                    $status = $os_supply->status;
                }

                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => $qty,
                    "status" => $status,
                );
            } else {
                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => $qty,
                    "status" => "EMPTY",
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
                <th>Product No</th>
                <th>Product Name</th>
                <th>Product Family</th>
                <th>Stock</th>
                <th>Actual</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['actual'] . '</td>
                            <td>' . $data['status'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

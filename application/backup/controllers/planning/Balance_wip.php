<?php
defined('BASEPATH') or exit('No direct script access allowed');

class balance_wip extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[balance_wip.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/balance_wip');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('balance_wip', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM balance_wip WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
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
            $cutoff =  date('Y-m-01', strtotime('-1 month', strtotime($filter_cutoff)));

            $itemq = $this->pg->query("SELECT b.item_id, c.item_name, d.pfm_name, COALESCE(SUM(b.qty_scan) - SUM(b.qty_need), 0) as stock
                from wip_trx_mpp a 
                join serial_detail_kanbanrm b ON a.temp_woc_id = b.kanbanrm_woc_id
                join mst_item c on b.item_id = c.item_id and c.stscode_id = '01'
                join r_prodfam d on c.pfm_id = d.pfm_id and d.prod_group = 'Raw Material' and d.pfm_id != '05'
                where a.wp_date between '$cutoff' and '$filter_cutoff' and d.pfm_id LIKE '%$filter_product_family%' and c.item_id LIKE '%$filter_product_no%'
                group by b.item_id, c.item_name, d.pfm_name
                having COALESCE(SUM(b.qty_scan) - SUM(b.qty_need), 0) > 0
                order by d.pfm_name, COALESCE(SUM(b.qty_scan) - SUM(b.qty_need), 0) asc
            ");
            $items = $itemq->result_array();
            $arr = array();

            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $item_name = $item['item_name'];
                $pfm_name = $item['pfm_name'];
                $qty = abs($item['stock']);

                $balance_wip = $this->api->read('balance_wip', [], [
                    "product_no" => $item_id,
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision
                ]);

                if ($balance_wip) {
                    if($balance_wip->status == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $balance_wip->status;
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

            $balance_wip = $this->api->reads('balance_wip', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            if($post['product_no'] != ""){
                if (count($balance_wip) > 0) {
                    $send   = $this->api->update('balance_wip', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => trim($post['product_no'])
                    ], $post);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $send   = $this->api->create('balance_wip', $post, "RMS", "balance_wip");

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
        $send = $this->api->delete('balance_wip', $data);
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

        $send = $this->api->delete('balance_wip', [
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

            $balance_wip = $this->api->reads('balance_wip', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['product_no']),
                "product_name" => $post['product_name'],
                "product_family" => @$balance_wip[0]->product_family,
                "qty" => $post['qty'],
                "status" => $post['status']
            );

            if($post['product_no'] != ""){
                if (count($balance_wip) > 0) {
                    $send   = $this->api->update('balance_wip', [
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
        @unlink('excel/failed/balance_wip.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/balance_wip.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/balance_wip.txt";

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
            header("Content-Disposition: attachment; filename=balance_wip_$format.xls");
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

        $cutoff =  date('Y-m-01', strtotime('-1 month', strtotime($filter_cutoff)));

        $itemq = $this->pg->query("SELECT b.item_id, c.item_name, d.pfm_name, COALESCE(SUM(b.qty_scan) - SUM(b.qty_need), 0) as stock
            from wip_trx_mpp a 
            join serial_detail_kanbanrm b ON a.temp_woc_id = b.kanbanrm_woc_id
            join mst_item c on b.item_id = c.item_id and c.stscode_id = '01'
            join r_prodfam d on c.pfm_id = d.pfm_id and d.prod_group = 'Raw Material' and d.pfm_id != '05'
            where a.wp_date between '$cutoff' and '$filter_cutoff' and d.pfm_id LIKE '%$filter_product_family%' and c.item_id LIKE '%$filter_product_no%'
            group by b.item_id, c.item_name, d.pfm_name
            having COALESCE(SUM(b.qty_scan) - SUM(b.qty_need), 0) > 0
            order by d.pfm_name, COALESCE(SUM(b.qty_scan) - SUM(b.qty_need), 0) asc
        ");
        $items = $itemq->result_array();
        $records = array();

        foreach ($items as $item) {
            $item_id = $item['item_id'];
            $item_name = $item['item_name'];
            $pfm_name = $item['pfm_name'];
            $qty = abs($item['stock']);

            $balance_wip = $this->api->read('balance_wip', [], [
                "product_no" => $item_id,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            if ($balance_wip) {
                if($balance_wip->status == "EMPTY"){
                    $status = "GENERATE";
                }else{
                    $status = $balance_wip->status;
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
                <th>Balance WIP</th>
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
                            <td>' . $data['status'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

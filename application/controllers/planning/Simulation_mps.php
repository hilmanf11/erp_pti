<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Simulation_mps extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('planning/simulation_mps');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily(){
        $prodfam = $this->api->query("SELECT * FROM mst_item_family WHERE `number` != '06'");
        echo json_encode($prodfam);
    }

    public function readProducts($pfm_id){
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $items = $this->api->query("SELECT * FROM mst_item_raw WHERE pfm_id = '$pfm_id' and item_id like '%$post%'");
        echo json_encode($items);
    }

    public function getData(){
        $post = $this->input->post();

        $this->db->select("a.*, b.item_name, c.name as product_family");
        $this->db->from('mst_bom a');
        $this->db->join('mst_item_raw b', 'a.component_id = b.item_id');
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->where('a.item_id', $post['product_no']);
        $this->db->order_by('a.item_id');
        $this->db->group_by('a.component_id');
        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();

        $data = array();
        foreach ($records as $record) {
            $data[] = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "cutoff" => $post['cutoff'],
                "item_id" => $record['component_id'],
                "item_name" => $record['item_name'],
                "product_family" => $record['product_family'],
                "qty" => $record['qty'],
                "qtyperassy" => ($post['qty'] * $record['qty']),
            );
        }

        $data['total'] = $totalRows;
        die(json_encode($data));
    }

    public function getDataMpp($month, $year, $revision, $item_id){
        $query = $this->db->query("SELECT need_1 FROM generate_mrp_finals WHERE p_month = '$month' and p_year = '$year' and revision = '$revision' and part_no = '$item_id'");
        $records = $query->row();

        return empty($records) ? 0 : $records->need_1;
    }

    public function getDataMrp($month, $year, $revision, $item_id){
        $query = $this->db->query("SELECT need_2 FROM generate_mrp_finals WHERE p_month = '$month' and p_year = '$year' and revision = '$revision' and part_no = '$item_id'");
        $records = $query->row();

        return empty($records) ? 0 : $records->need_2;
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
            $filter_item_family = base64_decode($this->input->get('filter_item_family'));
            $filter_item_id = trim(base64_decode($this->input->get('filter_item_id')));
            $filter_status = trim(base64_decode($this->input->get('filter_status')));

            $period = date("Y-m-d", strtotime($filter_year."-".$filter_month."-01"));
            $month_nx = date("F", strtotime("1 month", strtotime($period)));
            $month = date("F", strtotime($filter_year."-".$filter_month."-01"));
            $month_bf = date("F", strtotime("-1 month", strtotime($period)));

            //Select Query
            $this->db->select('a.*, (CASE WHEN a.balance > 0 THEN 0 ELSE a.balance END) as sugest_order, ((a.stock_whs + a.eta_total)-(a.os_wo + a.os_supply + total_need)) as balance2');
            $this->db->from('simulation_mps a');
            $this->db->join('mst_item_raw b', "b.item_id = a.item_no");
            $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if ($filter_item_id != "") {
                $this->db->where('b.item_id', $filter_item_id);
            }
            if ($filter_item_family != "") {
                $this->db->where('c.number', $filter_item_family);
            }
            if($filter_status == "OK"){
                $this->db->where('a.balance > 0');
            }elseif($filter_status == "SHORTAGE"){
                $this->db->where('a.balance < 0');
            }
            $this->db->order_by('b.item_id', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $period = [
                "t_ossupply_3" => $month_nx,
                "t_ossupply_2" => $month,
                "t_ossupply_1" => $month_bf,
            ];

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records, "period" => $period]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            //Database Server MRP
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $item_id = $post['item_id'];
            $cutoff = $post['cutoff'];
            $period_start = $post['p_year']."-".$post['p_month']."-01";
            $period_end = date("Y-m-t", strtotime($post['p_year']."-".$post['p_month']."-01"));
            $p_month_bf = date("m", strtotime($cutoff));
            $p_month = $post['p_month'];
            $p_year = $post['p_year'];

            $cutoff_be =  date('Y-m-01', strtotime($cutoff));
            $cutoff_bf =  date('Y-m-01', strtotime("-1 month", strtotime($cutoff)));
            $filter_start = $cutoff;
            $filter_end = date("Y-m-t", strtotime($filter_start));

            // $maxRev = $this->api->query("SELECT MAX(revision) as rev FROM generate_mpp WHERE p_month = '$p_month' and p_year = '$p_year'");
            $revision = $post['revision'];
            // $revision = $maxRev[0]->rev;

            //Stock Warehouse
            $ith = $this->dummy->query("SELECT sum(ith_qty) as qty from ith where ith_item_id = '$item_id' and ith_date <= '$cutoff'");
            $ithRow = $ith->row();

            //Pengambilan OS Supply Bulan Sebelumya
            $query_os_supply = $this->dummy->query("SELECT z.item_id, z.item_name, z.pfm_name, COALESCE(SUM(z.stock), 0) as stock from (
                select a.temp_woc_id, a.periode, a.wp, d.item_id, d.item_name, e.pfm_name, a.qty, b.bom_qty_perassy, (a.qty * b.bom_qty_perassy) as hasil, COALESCE(SUM(c.qty_need), 0) as qty_need, SUM(c.qty_scan) as qty_scan, COALESCE((a.qty * b.bom_qty_perassy) - COALESCE(SUM(c.qty_scan), 0), 0) as stock
                from wip_trx_mpp a
                join mst_bom b ON a.assy_no = b.bom_par_item
                left join (SELECT item_id, kanbanrm_woc_id, SUM(qty_need) as qty_need, SUM(qty_scan) as qty_scan FROM serial_detail_kanbanrm GROUP BY item_id, kanbanrm_woc_id, qty_need) c ON a.temp_woc_id = c.kanbanrm_woc_id and c.item_id = b.bom_com_item
                join mst_item d on b.bom_com_item = d.item_id and d.stscode_id = '01'
                join r_prodfam e on d.pfm_id = e.pfm_id and e.prod_group = 'Raw Material' and e.pfm_id != '05'
                where b.bom_com_item = '$item_id' and a.wp_date between '$cutoff_bf' and '$filter_end'
                group by a.temp_woc_id, a.periode, a.wp, a.qty, b.bom_qty_perassy, d.item_id, d.item_name, e.pfm_name) z
                GROUP BY z.item_id, z.item_name, z.pfm_name
                having COALESCE(SUM(z.stock), 0) > 0     
            ");
            $data_os_supply = $query_os_supply->row();
            $os_supply = empty(@$data_os_supply->stock) ? 0 : @$data_os_supply->stock;

            //Supply
            $query_os_supply2 = $this->dummy->query("SELECT SUM(ith_qty) as supply from ith where ith_form like '%OU%' and ith_date between '$cutoff_be' and '$cutoff' and ith_item_id = '$item_id'");
            $data_os_supply2 = $query_os_supply2->row();
            $os_supply2 = empty(@$data_os_supply2->supply) ? 0 : abs(@$data_os_supply2->supply);

            //Pengambilan OS MPP / OS WO Bulan Sebelumya
            if($p_month > $p_month_bf){
                $query_os_mpp = $this->dummy->query("SELECT b.bom_com_item, c.item_name, d.pfm_name, SUM(a.total * b.bom_qty_perassy) as total from
                    (select assy_no, SUM(qty) as total from wip_trx_mpp WHERE wp_date between '$filter_start' and '$filter_end' GROUP BY assy_no ORDER BY SUM(qty) asc) a
                    join mst_bom b on a.assy_no = b.bom_par_item
                    join mst_item c on b.bom_com_item = c.item_id
                    join r_prodfam d on c.pfm_id = d.pfm_id
                    where b.bom_com_item = '$item_id'
                    group by b.bom_com_item, c.item_name, d.pfm_name
                    order by d.pfm_name asc, SUM(a.total * b.bom_qty_perassy) desc
                ");
                $data_os_mpp = $query_os_mpp->row();
                
                $os_wo = empty(@$data_os_mpp->total) ? 0 : @$data_os_mpp->total;
            }else{
                $os_wo = 0;
            }

            //ETA Qty
            // $queryEta = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$item_id' and por_date between '$period_start' and '$period_end'");
            $queryEta = $this->dummy->query("SELECT SUM(ith_qty) as total 
                from ith 
                where (ith_form in ('IN - SJP','IN - SJ R/M','IN - BPM') and ith_date between '$period_start' and '$period_end' and ith_item_id = '$item_id') 
                or (ith_form = 'IN - TRANSFER' and whs_id = 'WHS001' and ith_date between '$period_start' and '$period_end' and ith_item_id = '$item_id')");
            $totalEta = $queryEta->row();
            
            // $balance_wip = $this->api->read("balance_wip", [], ["product_no" => $item_id, "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            $stock_whs = round(@$ithRow->qty);
            $etaTotal = empty(@$totalEta->total) ? 0 : @$totalEta->total;
            
            // $balance_wip = empty(@$balance_wip->qty) ? 0 : @$balance_wip->qty;
            $totalNeed = $this->getDataMpp($p_month, $p_year, $revision, $item_id);
            $totalNeed2 = $this->getDataMrp($p_month, $p_year, $revision, $item_id);
            $total_need = ($totalNeed - $os_supply2);
            $freestock = (($stock_whs + $etaTotal) - ($os_wo + $os_supply + $total_need + $post['qtyperassy']));
            $balance = ($freestock - $totalNeed2);

            $mps = $this->api->reads('simulation_mps', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "item_no" => $item_id
            ]);

            if (count($mps) > 0) {
                if($freestock <= 0){
                    $freestock = 0;
                }

                $dataFinal = array(
                    "qtyperassy" => ($mps[0]->qtyperassy + $post['qtyperassy']),
                    "qty" => $post['qty'],
                    "stock_whs" => $stock_whs,
                    "eta_total" => $etaTotal,
                    "os_wo" => $os_wo,
                    "os_supply" => $os_supply,
                    "os_supply2" => $os_supply2,
                    "need_1" => $totalNeed,
                    "need_2" => $totalNeed2,
                    "total_need" => $total_need,
                    "freestock" => $freestock,
                    "balance" => $balance,
                    "status" => "INPUT"
                );

                $this->db->update('simulation_mps', $dataFinal, ["p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $post['revision'],
                "cutoff" => $post['cutoff'],
                "item_no" => $item_id]);

                echo json_encode(array("title" => "Good Job", "message" => "Part No " . $item_id . " for Qty (".$post['qtyperassy'].") Updated", "theme" => "success"));
            } else {

                if($freestock <= 0){
                    $freestock = 0;
                }

                $dataFinal = array(
                    "p_month" => $p_month,
                    "p_year" => $p_year,
                    "revision" => $post['revision'],
                    "cutoff" => $post['cutoff'],
                    "item_no" => $item_id,
                    "item_name" => $post['item_name'],
                    "product_family" => $post['product_family'],
                    "qtyperassy" => $post['qtyperassy'],
                    "qty" => $post['qty'],
                    "stock_whs" => $stock_whs,
                    "eta_total" => $etaTotal,
                    "os_wo" => $os_wo,
                    "os_supply" => $os_supply,
                    "os_supply2" => $os_supply2,
                    "need_1" => $totalNeed,
                    "need_2" => $totalNeed2,
                    "total_need" => $total_need,
                    "freestock" => $freestock,
                    "balance" => $balance,
                    "status" => "INPUT"
                );

                $this->api->create('simulation_mps', $dataFinal, "SM", "simulation_mps");
                echo json_encode(array("title" => "Good Job", "message" => "Part No " . $item_id . " for Qty (".$post['qtyperassy'].") Created", "theme" => "success"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('simulation_mps', $data);
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

        $this->api->delete('simulation_mps', [
            "p_month" => $data->val(2, 3),
            "p_year" => $data->val(2, 4),
            "revision" => $data->val(3, 3),
        ]);

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'cutoff' => $data->val(4, 3),
                'product_no' => $data->val($i, 2),
                'qty' => $data->val($i, 3),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/simulation_mps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/simulation_mps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/simulation_mps.txt";

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
            header("Content-Disposition: attachment; filename=simulation_mps_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = trim(base64_decode($this->input->get('filter_item_id')));

        //Select Query
        $this->db->select('a.*, (CASE WHEN a.balance > 0 THEN 0 ELSE a.balance END) as sugest_order, ((a.stock_whs + a.eta_total)-(a.os_wo + a.os_supply + total_need)) as balance2');
        $this->db->from('simulation_mps a');
        $this->db->join('mst_item_raw b', "b.item_id = a.item_no");
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        if ($filter_item_id != "") {
            $this->db->where('b.item_id', $filter_item_id);
        }
        if ($filter_item_family != "") {
            $this->db->where('c.number', $filter_item_family);
        }
        $this->db->order_by('b.item_id', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; }</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER SIMULATION MPS</small>
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
                <th style="text-align:center;" width="20">No</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Stock WHS<br> < '.$records[0]['cutoff'].'</th>
                <th style="text-align:center;">OS WO<br>(M-1)</th>
                <th style="text-align:center;">OS Supply<br>(M-1)</th>
                <th style="text-align:center;">ETA<br>(M)</th>
                <th style="text-align:center;">Supply<br>(M)</th>
                <th style="text-align:center;">Need<br>(M)</th>
                <th style="text-align:center;">Total Need<br>(M)</th>
                <th style="text-align:center;">Balance</th>
                <th style="text-align:center;">Need<br>Breakdown</th>
                <th style="text-align:center;">Free Stock<br>(M)</th>
                <th style="text-align:center;">Need<br>(M+1)</th>
                <th style="text-align:center;">Suggest Order</th>
                <th style="text-align:center;">OTW</th>
                <th style="text-align:center;">ETA</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['balance'] > 0){
                $sugest_order = 0;
            }else{
                $sugest_order = $data['balance'];
            }

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['stock_whs'] . '</td>
                            <td>' . $data['os_wo'] . '</td>
                            <td>' . $data['os_supply'] . '</td>
                            <td>' . $data['eta_total'] . '</td>
                            <td>' . $data['os_supply2'] . '</td>
                            <td>' . $data['need_1'] . '</td>
                            <td>' . $data['total_need'] . '</td>
                            <td>' . $data['balance2'] . '</td>
                            <td>' . $data['qtyperassy'] . '</td>
                            <td>' . $data['freestock'] . '</td>
                            <td>' . $data['need_2'] . '</td>
                            <td>' . $sugest_order . '</td>
                            <td>' . $data['otw'] . '</td>
                            <td>' . $data['eta'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

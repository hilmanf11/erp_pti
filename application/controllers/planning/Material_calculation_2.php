<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Material_calculation_2 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[mpp_realization.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/material_calculation_2');
        } else {
            redirect('error_access');
        }
    }

    public function cutoffDate(){
        $year = $this->input->post('year');
        $month = $this->input->post('month');

        echo date("Y-m-d", strtotime("-1 days", strtotime($year."-".$month."-01")));
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

    public function getDataMpp(){
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $this->db->select("*");
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->group_by('product_no');
        $records = $this->db->get()->result_array();

        echo json_decode($records);
    }

    public function datatables()
    {
        //Database Server MRP
        $this->dummy = $this->load->database('dummy', TRUE);
        
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
            $filter_suppliers = base64_decode($this->input->get('filter_suppliers'));
            $filter_item_id = base64_decode($this->input->get('filter_item_id'));
            $filter_status = $this->input->get('filter_status');
            $filter_date = base64_decode($this->input->get('filter_date'));

            $period_start = $filter_year."-".$filter_month."-01";
            $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-01"));

            $this->db->select("a.*, c.item_name as product_name, b.name as item_family, d.need_2, d.qty_rm, f.name as supplier_name");
            $this->db->from('generate_item_calculation a');
            $this->db->join('mst_item_raw c', 'a.product_no = c.item_id');
            $this->db->join('mst_item_family b', 'c.pfm_id = b.number');
            $this->db->join('generate_mrp_finals d', 'a.product_no = d.part_no and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
            $this->db->join('supplier_items e', 'a.product_no = e.item_id and e.share_order > 0', 'left');
            $this->db->join('suppliers f', 'e.supplier_id = f.id', 'left');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_suppliers != ""){
                $this->db->where('f.id', $filter_suppliers);
            }
            if($filter_item_family != ""){
                $this->db->where('c.pfm_id', $filter_item_family);
            }
            $this->db->like('a.product_no', $filter_item_id);
            $this->db->order_by('b.number');
            $this->db->group_by('a.product_no');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            // $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $dataFinal = array();
            foreach ($records as $record) {
                $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

                $item_id = $record['product_no'];
                // $ith = $this->dummy->query("SELECT sum(ith_qty) as qty from ith where ith_item_id = '$item_id' and ith_date <= '$filter_date'");
                // $ithRow = $ith->row();

                $queryEta = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$item_id' and por_docno not like '%PEMUTIHAN%' and por_date between '$period_start' and '$period_end'");
                $totalEta = $queryEta->row();
                $etaTotal = empty(@$totalEta->total) ? 0 : @$totalEta->total;

                // $stock_hand = (@$ithRow->qty - $record['os_supply'] - $record['os_wo']);
                $stock_hand = (@$record['qty_rm'] - $record['os_supply'] - $record['os_wo']);
                $balances = ($stock_hand + $etaTotal - $record['total']);

                $day = 1;
                $balance = $stock_hand;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $working_date = date("Y-m-d", strtotime($firstDate));
                    
                    //ETA
                    $delivery_schedule = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$item_id' and por_docno not like '%PEMUTIHAN%' and por_date = '$working_date'");
                    $totalRow = $delivery_schedule->row();
                    $etaqty = empty(@$totalRow->total) ? 0 : @$totalRow->total;
                    
                    //Supply
                    $ithSupply = $this->dummy->query("SELECT SUM(ith_qty) as supply from ith where ith_form like '%OU%' and ith_date = '$working_date' and ith_item_id = '$item_id'");
                    $ithSupplyRow = $ithSupply->row();
    
                    $date_qty = $record["date_".$day];
                    $balance += round($etaqty - $date_qty);
                    
                    $arr["eta_".$day] = (int) $etaqty;
                    $arr["date_".$day] = round($date_qty);
                    $arr["supply_".$day] = abs(round(@$ithSupplyRow->supply));
                    $arr["bal_".$day] = round($balance);
    
                    $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
                    $day++;
                }

                $ospo = $this->dummy->query("SELECT SUM(z.balance) as balance FROM (
                    SELECT a.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
                    FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc WHERE item_id = '$item_id' GROUP BY item_id, poc_id, poc_reqdate) b 
                    LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por WHERE item_id = '$item_id' GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
                    WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0
                    ORDER BY b.poc_reqdate ASC) z");
                $ospoRow = $ospo->row();

                if($balances > 0){
                    $free_stock = $balances;
                }else{
                    $free_stock = 0;
                }

                if($filter_status == "OK"){
                    if($free_stock > 0){
                        $dataFinal[] = array_merge($arr, [
                            "id" => $record['id'],
                            "product_no" => $item_id,
                            "product_name" => $record['product_name'],
                            "product_family_name" => $record['item_family'],
                            "supplier_name" => $record['supplier_name'],
                            // "stock_whs" => round(@$ithRow->qty),
                            "stock_whs" => $record['qty_rm'],
                            "os_supply" => $record['os_supply'],
                            "os_wo" => $record['os_wo'],
                            "stock_hand" => $stock_hand,
                            "eta" => $etaTotal,
                            "total" => round($record['total']),
                            "total_bal" => round($balances),
                            "free_stock" => round($free_stock),
                            "next_month_need1" => round($record['need_2'] / 4),
                            "next_month_need2" => round($record['need_2'] / 4),
                            "next_month_need3" => round($record['need_2'] / 4),
                            "next_month_need4" => round($record['need_2'] / 4),
                            "next_month_bal1" => ($balance - round($record['need_2'] / 4)),
                            "next_month_bal2" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                            "next_month_bal3" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                            "next_month_bal4" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                            "balance_wip" => $record['balance_wip'],
                            "os_po" => @$ospoRow->balance,
                        ]);
                    }
                }elseif($filter_status == "SHORTAGE"){
                    if($free_stock == 0){
                        $dataFinal[] = array_merge($arr, [
                            "id" => $record['id'],
                            "product_no" => $item_id,
                            "product_name" => $record['product_name'],
                            "product_family_name" => $record['item_family'],
                            "supplier_name" => $record['supplier_name'],
                            // "stock_whs" => round(@$ithRow->qty),
                            "stock_whs" => $record['qty_rm'],
                            "os_supply" => $record['os_supply'],
                            "os_wo" => $record['os_wo'],
                            "stock_hand" => $stock_hand,
                            "eta" => $etaTotal,
                            "total" => round($record['total']),
                            "total_bal" => round($balances),
                            "free_stock" => round($free_stock),
                            "next_month_need1" => round($record['need_2'] / 4),
                            "next_month_need2" => round($record['need_2'] / 4),
                            "next_month_need3" => round($record['need_2'] / 4),
                            "next_month_need4" => round($record['need_2'] / 4),
                            "next_month_bal1" => ($balance - round($record['need_2'] / 4)),
                            "next_month_bal2" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                            "next_month_bal3" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                            "next_month_bal4" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                            "balance_wip" => $record['balance_wip'],
                            "os_po" => @$ospoRow->balance,
                        ]);
                    }
                }else{
                    $dataFinal[] = array_merge($arr, [
                        "id" => $record['id'],
                        "product_no" => $item_id,
                        "product_name" => $record['product_name'],
                        "product_family_name" => $record['item_family'],
                        "supplier_name" => $record['supplier_name'],
                        // "stock_whs" => round(@$ithRow->qty),
                        "stock_whs" => $record['qty_rm'],
                        "os_supply" => $record['os_supply'],
                        "os_wo" => $record['os_wo'],
                        "stock_hand" => $stock_hand,
                        "eta" => $etaTotal,
                        "total" => round($record['total']),
                        "total_bal" => round($balances),
                        "free_stock" => round($free_stock),
                        "next_month_need1" => round($record['need_2'] / 4),
                        "next_month_need2" => round($record['need_2'] / 4),
                        "next_month_need3" => round($record['need_2'] / 4),
                        "next_month_need4" => round($record['need_2'] / 4),
                        "next_month_bal1" => ($balance - round($record['need_2'] / 4)),
                        "next_month_bal2" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                        "next_month_bal3" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                        "next_month_bal4" => ($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)),
                        "balance_wip" => $record['balance_wip'],
                        "os_po" => @$ospoRow->balance,
                    ]);
                }
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $dataFinal]);
            echo json_encode($result);
        }
    }

    public function getData(){
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));

        $query = $this->db->query("SELECT z.p_month, z.p_year, z.revision, z.component_id as product_no, 
            SUM(total_date_1) as date_1,
            SUM(total_date_2) as date_2,
            SUM(total_date_3) as date_3,
            SUM(total_date_4) as date_4,
            SUM(total_date_5) as date_5,
            SUM(total_date_6) as date_6,
            SUM(total_date_7) as date_7,
            SUM(total_date_8) as date_8,
            SUM(total_date_9) as date_9,
            SUM(total_date_10) as date_10,
            SUM(total_date_11) as date_11,
            SUM(total_date_12) as date_12,
            SUM(total_date_13) as date_13,
            SUM(total_date_14) as date_14,
            SUM(total_date_15) as date_15,
            SUM(total_date_16) as date_16,
            SUM(total_date_17) as date_17,
            SUM(total_date_18) as date_18,
            SUM(total_date_19) as date_19,
            SUM(total_date_20) as date_20,
            SUM(total_date_21) as date_21,
            SUM(total_date_22) as date_22,
            SUM(total_date_23) as date_23,
            SUM(total_date_24) as date_24,
            SUM(total_date_25) as date_25,
            SUM(total_date_26) as date_26,
            SUM(total_date_27) as date_27,
            SUM(total_date_28) as date_28,
            SUM(total_date_29) as date_29,
            SUM(total_date_30) as date_30,
            SUM(total_date_31) as date_31
        FROM (
            SELECT a.p_month, a.p_year, a.revision, a.product_no, b.component_id,
            (a.date_1 * b.qty) as total_date_1,
            (a.date_2 * b.qty) as total_date_2,
            (a.date_3 * b.qty) as total_date_3,
            (a.date_4 * b.qty) as total_date_4,
            (a.date_5 * b.qty) as total_date_5,
            (a.date_6 * b.qty) as total_date_6,
            (a.date_7 * b.qty) as total_date_7,
            (a.date_8 * b.qty) as total_date_8,
            (a.date_9 * b.qty) as total_date_9,
            (a.date_10 * b.qty) as total_date_10,
            (a.date_11 * b.qty) as total_date_11,
            (a.date_12 * b.qty) as total_date_12,
            (a.date_13 * b.qty) as total_date_13,
            (a.date_14 * b.qty) as total_date_14,
            (a.date_15 * b.qty) as total_date_15,
            (a.date_16 * b.qty) as total_date_16,
            (a.date_17 * b.qty) as total_date_17,
            (a.date_18 * b.qty) as total_date_18,
            (a.date_19 * b.qty) as total_date_19,
            (a.date_20 * b.qty) as total_date_20,
            (a.date_21 * b.qty) as total_date_21,
            (a.date_22 * b.qty) as total_date_22,
            (a.date_23 * b.qty) as total_date_23,
            (a.date_24 * b.qty) as total_date_24,
            (a.date_25 * b.qty) as total_date_25,
            (a.date_26 * b.qty) as total_date_26,
            (a.date_27 * b.qty) as total_date_27,
            (a.date_28 * b.qty) as total_date_28,
            (a.date_29 * b.qty) as total_date_29,
            (a.date_30 * b.qty) as total_date_30,
            (a.date_31 * b.qty) as total_date_31
        FROM generate_mpp a
        JOIN mst_bom b ON a.product_no = b.item_id
        WHERE a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision' and b.component_id like '%$filter_item_id%'
        GROUP BY a.product_no, b.component_id
        ORDER BY a.product_no, b.component_id asc) z
        GROUP BY z.component_id");
        $records = $query->result_array();

        if($filter_item_id != ""){
            $this->db->delete('generate_item_calculation', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "product_no" => $filter_item_id]);
        }else{
            $this->db->delete('generate_item_calculation', ["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);
        }
        
        echo json_encode($records);
    }

    public function create(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');
            $cutoff = $this->input->post('cutoff');
            $cutoff_start = date("Y-m-01", strtotime("-1 month", strtotime($cutoff)));
            $cutoff_end = date("Y-m-t", strtotime("-1 month", strtotime($cutoff)));
            $product_no = $post['product_no'];

            $os_supply = $this->api->read("os_supply", [], ["product_no" => $post['product_no'], "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            $os_mpp = $this->api->read("os_mpp", [], ["product_no" => $post['product_no'], "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            $balance_wip = $this->api->read("balance_wip", [], ["product_no" => $post['product_no'], "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision']]);
            
            $eta = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$product_no' and por_date between '$cutoff_start' and '$cutoff_end'");
            $etaRow = $eta->row();

            $postFinal = array_merge($post, array(
                "os_supply" => empty(@$os_supply->actual) ? 0 : @$os_supply->actual,
                "os_wo" => empty(@$os_mpp->actual) ? 0 : @$os_mpp->actual,
                "balance_wip" => empty(@$balance_wip->qty) ? 0 : @$balance_wip->qty,
                "eta" => empty(@$etaRow->total) ? 0 : @$etaRow->total,
                "total" => (($post['date_1'] + $post['date_2'] + $post['date_3'] + $post['date_4'] + $post['date_5'] + $post['date_6'] + 
                    $post['date_7'] + $post['date_8'] + $post['date_9'] + $post['date_10'] + $post['date_11'] + $post['date_12'] + 
                    $post['date_13'] + $post['date_14'] + $post['date_15'] + $post['date_16'] + $post['date_17'] + $post['date_18'] + 
                    $post['date_19'] + $post['date_20'] + $post['date_21'] + $post['date_22'] + $post['date_23'] + $post['date_24'] + 
                    $post['date_25'] + $post['date_26'] + $post['date_27'] + $post['date_28'] + $post['date_29'] + $post['date_30'] + 
                    $post['date_31'])),
                "balance" => (($post['date_1'] + $post['date_2'] + $post['date_3'] + $post['date_4'] + $post['date_5'] + $post['date_6'] + 
                    $post['date_7'] + $post['date_8'] + $post['date_9'] + $post['date_10'] + $post['date_11'] + $post['date_12'] + 
                    $post['date_13'] + $post['date_14'] + $post['date_15'] + $post['date_16'] + $post['date_17'] + $post['date_18'] + 
                    $post['date_19'] + $post['date_20'] + $post['date_21'] + $post['date_22'] + $post['date_23'] + $post['date_24'] + 
                    $post['date_25'] + $post['date_26'] + $post['date_27'] + $post['date_28'] + $post['date_29'] + $post['date_30'] + 
                    $post['date_31']))
            ));

            if ($this->api->create('generate_item_calculation', $postFinal, "IC", "IC")) {
                echo json_encode(array("title" => "Good Job", "message" => $post['product_no'] . " | Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Error", "message" => $post['product_no'] . " | Data Unsaved", "theme" => "error"));
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=material_calculation_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Database Server MRP
        $this->dummy = $this->load->database('dummy', TRUE);

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_suppliers = base64_decode($this->input->get('filter_suppliers'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));
        $filter_status = $this->input->get('filter_status');
        $filter_date = base64_decode($this->input->get('filter_date'));

        $firstDate = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

        $period_start = $filter_year."-".$filter_month."-01";
        $period_end = date("Y-m-t", strtotime($filter_year."-".$filter_month."-01"));
        
        $next_month = date("F", strtotime("+1 month", strtotime($filter_year."-".$filter_month."-01")));

        $this->db->select("a.*, c.item_name as product_name, b.name as item_family, d.need_2, f.name as supplier_name");
        $this->db->from('generate_item_calculation a');
        $this->db->join('mst_item_raw c', 'a.product_no = c.item_id');
        $this->db->join('mst_item_family b', 'c.pfm_id = b.number');
        $this->db->join('generate_mrp_finals d', 'a.product_no = d.part_no and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
        $this->db->join('supplier_items e', 'a.product_no = e.item_id and e.share_order > 0', 'left');
        $this->db->join('suppliers f', 'e.supplier_id = f.id', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        if($filter_suppliers != ""){
            $this->db->where('f.id', $filter_suppliers);
        }
        if($filter_item_family != ""){
            $this->db->where('c.pfm_id', $filter_item_family);
        }
        $this->db->like('a.product_no', $filter_item_id);
        $this->db->order_by('b.number');
        $this->db->group_by('a.product_no');
        $records = $this->db->get()->result_array();

        //Setting Header
        $styles = "";
        $cut = '<tr>';
        $cut_no = 0;
        $cut_content = "";
        $date = '<tr>';
        $no = 1;
        $colspan = 0;

        while (strtotime($firstDate) <= strtotime($endDate)) {
            $working_date = date("Y-m-d", strtotime($firstDate));

            $this->db->select('remarks');
            $this->db->from('working_calendar');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', strtotime($firstDate)) !== '0') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $styles = 'background:#FFD974;';
                    $cut_no += 0;
                    $cut_content = "";
                } else{
                    $styles = "";
                    $cut_no += 1;
                    $cut_content = "W/P ". $cut_no;
                }
            } else {
                $styles = 'background:#FFD974;';
                $cut_no += 0;
                $cut_content = "";
            }

            //Setting Header
            $cut .= '<th width="50" colspan="4" style="text-align:center; ' . $styles . '">' . $cut_content . ' ('.$firstDate.')</th>';
            $date .= '<th width="50" style="text-align:center; ' . $styles . '">ETA</th>
                      <th width="50" style="text-align:center; ' . $styles . '">NEED</th>
                      <th width="50" style="text-align:center; ' . $styles . '">SUPPLY</th>
                      <th width="50" style="text-align:center; ' . $styles . '">BAL</th>';

            $colspan++;
            $firstDate = date("d M Y", strtotime("+1 day", strtotime($firstDate)));
        }

        $date .= '  <th width="50" style="text-align:center;">WEEK 1</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 2</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 3</th>
                    <th width="50" style="text-align:center;">BALANCE</th>
                    <th width="50" style="text-align:center;">WEEK 4</th>
                    <th width="50" style="text-align:center;">BALANCE</th>';

        //Setting Header
        $cut .= '</tr>';
        $date .= '</tr>';

        $content2 = "";
        foreach ($records as $record) {
            $content = '';
            $day = 1;
            $i = 0;
            $firstDate2 = date('01 M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('t M Y', strtotime($filter_year . "-" . $filter_month . "-01"));

            $item_id = $record['product_no'];
            $ith = $this->dummy->query("SELECT sum(ith_qty) as qty from ith where ith_item_id = '$item_id' and ith_date <= '$filter_date'");
            $ithRow = $ith->row();

            $queryEta = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$item_id' and por_docno not like '%PEMUTIHAN%' and por_date between '$period_start' and '$period_end'");
            $totalEta = $queryEta->row();
            $etaTotal = empty(@$totalEta->total) ? 0 : @$totalEta->total;

            $stock_hand = (@$ithRow->qty - $record['os_supply'] - $record['os_wo']);
            $balances = ($stock_hand + $etaTotal - $record['total']);

            if($balances > 0){
                $free_stock = $balances;
                $styles = "";
            }else{
                $free_stock = 0;
                $styles = 'background:#FFC2C2;';
            }

            $content .= "<tr>
                        <td>" . $no . "</td>
                        <td style='mso-number-format:\@;'>" . $record['product_no'] . "</td>
                        <td>" . $record['product_name'] . "</td>
                        <td>" . $record['supplier_name'] . "</td>
                        <td>" . $record['item_family'] . "</td>
                        <td style='text-align:right;'>" . round(@$ithRow->qty) . "</td>
                        <td style='text-align:right;'>" . round($record['os_supply']) . "</td>
                        <td style='text-align:right;'>" . round($record['os_wo']) . "</td>
                        <td style='text-align:right;'>" . round($stock_hand) . "</td>
                        <td style='text-align:right;'>" . round($etaTotal) . "</td>
                        <td style='text-align:right;'>" . round($record['total']) . "</td>
                        <td style='text-align:right; ".$styles."'>". number_format(@$balances) ."</td>";
            
            //ini di ambil dari total ETA yang harian
            //$record['eta']
            $wp = "";
            $styles2 = "";
            $balance = @$stock_hand;
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date2 = date("Y-m-d", strtotime($firstDate2));

                // $delivery_schedule = $this->api->read("delivery_schedules", [], ["part_no" => $item_id, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "delivey_date_1" => $working_date2]);
                // $qty_delivery_schedule = empty($delivery_schedule->delivey_qty_1) ? 0 : $delivery_schedule->delivey_qty_1;

                $delivery_schedule = $this->dummy->query("SELECT SUM(por_receiveqty) as total from por where item_id = '$item_id' and por_docno not like '%PEMUTIHAN%' and por_date = '$working_date2'");
                $totalRow = $delivery_schedule->row();
                $qty_delivery_schedule = empty(@$totalRow->total) ? 0 : @$totalRow->total;
                
                $ithSupply = $this->dummy->query("SELECT SUM(ith_qty) as supply from ith where ith_form like '%OU%' and ith_date = '$working_date2' and ith_item_id = '$item_id'");
                $ithSupplyRow = $ithSupply->row();

                $date_qty = $record["date_".$day];
                $balance += ($qty_delivery_schedule - $date_qty);

                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $working_date2);
                $holiday = $this->db->get()->row();

                if (date('w', strtotime($firstDate2)) !== '0') {
                    if (@$holiday->remarks != null or @$holiday->remarks != ""){
                        $styles2 = 'background:#FFD974;';
                    }else{
                        if($balance < 0){
                            $styles2 = 'background:#FFC2C2;';
                        }else{
                            $styles2 = '';
                        }
                    }
                } else {
                    $styles2 = 'background:#FFD974;';
                }

                $content .= "<td style='text-align:right; ".$styles2."'>".@$qty_delivery_schedule."</td>
                            <td style='text-align:right; ".$styles2."'>".round($date_qty)."</td>
                            <td style='text-align:right; ".$styles2."'>".abs(round(@$ithSupplyRow->supply))."</td>
                            <td style='text-align:right; ".$styles2."'>".round($balance)."</td>";

                $firstDate2 = date("d M Y", strtotime("+1 day", strtotime($firstDate2)));
                $day++;
            }

            $ospo = $this->dummy->query("SELECT SUM(z.balance) as balance FROM (
                SELECT a.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
                FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc WHERE item_id = '$item_id' GROUP BY item_id, poc_id, poc_reqdate) b 
                LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por WHERE item_id = '$item_id' GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
                WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0
                ORDER BY b.poc_reqdate ASC) z");
            $ospoRow = $ospo->row();

            // die("SELECT a.item_id, a.request, b.receive, (a.request - b.receive) as balance 
            // FROM (select item_id, SUM(por_pocreqqty) as request from por GROUP BY por_pocid, item_id) a 
            // JOIN (select item_id, sum(por_receiveqty) as receive from por GROUP BY item_id) b ON a.item_id = b.item_id
            // WHERE a.item_id = '$item_id' and (a.request - b.receive) >= 0");

            $content .= "   <td style='text-align:right; ".$styles."'>" . round($free_stock) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['need_2'] / 4) . "</td>
                            <td style='text-align:right;'>" . round($balance - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4) - round($record['need_2'] / 4)) . "</td>
                            <td style='text-align:right;'>" . round($record['balance_wip']) . "</td>
                            <td style='text-align:right;'>". round(@$ospoRow->balance) ."</td>
                        </tr>";

            if($filter_status == "OK"){
                if($free_stock > 0){
                    $content2 .= $content;
                    $no++;
                }
            }elseif($filter_status == "SHORTAGE"){
                if($free_stock == 0){
                    $content2 .= $content;
                    $no++;
                }
            }else{
                $content2 .= $content;
                $no++;
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MATERIAL CALCULATION</small>
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

        <table id="customers" border="1" style="width:520%;">
            <tr>
                <th style="text-align:center;" rowspan="3" width="20">NO</th>
                <th style="text-align:center;" width="150" rowspan="3">PRODUCT NO</th>
                <th style="text-align:center;" width="80" rowspan="3">PRODUCT NAME</th>
                <th style="text-align:center;" width="80" rowspan="3">SUPPLIER</th>
                <th style="text-align:center;" width="80" rowspan="3">PRODUCT FAMILY</th>
                <th style="text-align:center;" width="50" rowspan="3">STOCK WHS</th>
                <th style="text-align:center;" width="50" rowspan="3">OS SUPPLY</th>
                <th style="text-align:center;" width="50" rowspan="3">OS WO</th>
                <th style="text-align:center;" width="50" rowspan="3">ON HAND<br>STOCK</th>
                <th style="text-align:center;" width="50" rowspan="3">ETA</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL NEED</th>
                <th style="text-align:center;" width="50" rowspan="3">TOTAL BAL</th>
                <th style="text-align:center;" width="50" colspan="'.($colspan * 4).'">QUANTITY NEED FROM WORKORDER</th>
                <th style="text-align:center;" width="50" rowspan="3">FREE STOCK</th>
                <th style="text-align:center;" width="50" colspan="8" rowspan="2">'.$next_month.'</th>
                <th style="text-align:center;" width="50" rowspan="3">Balance WIP</th>
                <th style="text-align:center;" width="50" rowspan="3">OS PO</th>
            </tr>' . $cut . $date . $content2;
        echo $html;
    }

    public function print_detail(){
        $format  = date("Ymd");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=material_calculation_detail_$format.xls");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_item_family = base64_decode($this->input->get('filter_item_family'));
        $filter_suppliers = base64_decode($this->input->get('filter_suppliers'));
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));
        $filter_status = $this->input->get('filter_status');
        $filter_date = base64_decode($this->input->get('filter_date'));

        $this->db->select("a.*, c.item_name as product_name, b.name as item_family, f.name as supplier_name, d.assy_no, d.customer_name, ROUND(d.total, 2) as need");
        $this->db->from('generate_item_calculation a');
        $this->db->join('mst_item_raw c', 'a.product_no = c.item_id');
        $this->db->join('mst_item_family b', 'c.pfm_id = b.number');
        $this->db->join('supplier_items e', 'a.product_no = e.item_id and e.share_order > 0', 'left');
        $this->db->join('suppliers f', 'e.supplier_id = f.id', 'left');
        $this->db->join("(SELECT z.p_month, z.p_year, z.revision, z.product_no as assy_no, z.component_id, z.customer_name, (
            SUM(total_date_1) +
            SUM(total_date_2) +
            SUM(total_date_3) +
            SUM(total_date_4) +
            SUM(total_date_5) +
            SUM(total_date_6) +
            SUM(total_date_7) +
            SUM(total_date_8) +
            SUM(total_date_9) +
            SUM(total_date_10) +
            SUM(total_date_11) +
            SUM(total_date_12) +
            SUM(total_date_13) +
            SUM(total_date_14) +
            SUM(total_date_15) +
            SUM(total_date_16) +
            SUM(total_date_17) +
            SUM(total_date_18) +
            SUM(total_date_19) +
            SUM(total_date_20) +
            SUM(total_date_21) +
            SUM(total_date_22) +
            SUM(total_date_23) +
            SUM(total_date_24) +
            SUM(total_date_25) +
            SUM(total_date_26) +
            SUM(total_date_27) +
            SUM(total_date_28) +
            SUM(total_date_29) +
            SUM(total_date_30) +
            SUM(total_date_31)) as total
        FROM (
            SELECT a.p_month, a.p_year, a.revision, a.product_no, b.component_id, c.name as customer_name,
            (a.date_1 * b.qty) as total_date_1,
            (a.date_2 * b.qty) as total_date_2,
            (a.date_3 * b.qty) as total_date_3,
            (a.date_4 * b.qty) as total_date_4,
            (a.date_5 * b.qty) as total_date_5,
            (a.date_6 * b.qty) as total_date_6,
            (a.date_7 * b.qty) as total_date_7,
            (a.date_8 * b.qty) as total_date_8,
            (a.date_9 * b.qty) as total_date_9,
            (a.date_10 * b.qty) as total_date_10,
            (a.date_11 * b.qty) as total_date_11,
            (a.date_12 * b.qty) as total_date_12,
            (a.date_13 * b.qty) as total_date_13,
            (a.date_14 * b.qty) as total_date_14,
            (a.date_15 * b.qty) as total_date_15,
            (a.date_16 * b.qty) as total_date_16,
            (a.date_17 * b.qty) as total_date_17,
            (a.date_18 * b.qty) as total_date_18,
            (a.date_19 * b.qty) as total_date_19,
            (a.date_20 * b.qty) as total_date_20,
            (a.date_21 * b.qty) as total_date_21,
            (a.date_22 * b.qty) as total_date_22,
            (a.date_23 * b.qty) as total_date_23,
            (a.date_24 * b.qty) as total_date_24,
            (a.date_25 * b.qty) as total_date_25,
            (a.date_26 * b.qty) as total_date_26,
            (a.date_27 * b.qty) as total_date_27,
            (a.date_28 * b.qty) as total_date_28,
            (a.date_29 * b.qty) as total_date_29,
            (a.date_30 * b.qty) as total_date_30,
            (a.date_31 * b.qty) as total_date_31
        FROM generate_mpp a
        JOIN mst_bom b ON a.product_no = b.item_id
        LEFT JOIN mst_customer c ON a.customer_id = c.number
        WHERE a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision'
        GROUP BY a.product_no, b.component_id, b.rout_id
        ORDER BY a.product_no, b.component_id asc) z
        GROUP BY z.product_no, z.component_id) d", 'd.component_id = a.product_no', 'left');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        if($filter_suppliers != ""){
            $this->db->where('f.id', $filter_suppliers);
        }
        if($filter_item_family != ""){
            $this->db->where('c.pfm_id', $filter_item_family);
        }
        $this->db->like('a.product_no', $filter_item_id);
        $this->db->order_by('b.number');
        $this->db->order_by('a.product_no');
        $this->db->order_by('d.assy_no');
        $this->db->group_by('a.product_no');
        $this->db->group_by('d.assy_no');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} .str{ mso-number-format:\@; } </style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MATERIAL CALCULATION DETAIL</small>
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

        <table id="customers" border="1" style="width:100%;">
            <tr>
                <th style="text-align:center;" width="20">NO</th>
                <th style="text-align:center;" width="150">PART NO</th>
                <th style="text-align:center;" width="80">PART NAME</th>
                <th style="text-align:center;" width="80">PRODUCT FAMILY</th>
                <th style="text-align:center;" width="80">SUPPLIER</th>
                <th style="text-align:center;" width="150">PRODUCT NO</th>
                <th style="text-align:center;" width="150">CUSTOMER</th>
                <th style="text-align:center;" width="50">TOTAL NEED</th>
            </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['product_name'] . '</td>
                                <td>' . $data['item_family'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['assy_no'] . '</td>
                                <td>' . $data['customer_name'] . '</td>
                                <td>' . $data['need'] . '</td>
                            </tr>';
                $no++;
            }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function printOsPo(){
        $this->dummy = $this->load->database('dummy', TRUE);
        $product_no = base64_decode($this->input->get("product_no"));

        $ospo = $this->dummy->query("SELECT b.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
            FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc WHERE item_id = '$product_no' GROUP BY item_id, poc_id, poc_reqdate) b 
            LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por WHERE item_id = '$product_no' GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
            WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0");
        $records = $ospo->result_array();

        $balance = 0;
        $request = 0;
        $receive = 0;
        foreach ($records as $record) {
            $request += $record['request'];
            $receive += $record['receive'];
            $balance += $record['balance'];
        }

        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["item_id" => "Grand Total", "balance" => $balance, "receive" => $receive, "request" => $request, "plan" => 0]]])));
    }

    public function printBalance(){
        $product_no = base64_decode($this->input->get("product_no"));
        $filter_month = base64_decode($this->input->get("filter_month"));
        $filter_year = base64_decode($this->input->get("filter_year"));
        $filter_revision = base64_decode($this->input->get("filter_revision"));

        $ospo = $this->db->query("SELECT z.p_month, z.p_year, z.revision, z.product_no, z.customer_name, (
            SUM(total_date_1) +
            SUM(total_date_2) +
            SUM(total_date_3) +
            SUM(total_date_4) +
            SUM(total_date_5) +
            SUM(total_date_6) +
            SUM(total_date_7) +
            SUM(total_date_8) +
            SUM(total_date_9) +
            SUM(total_date_10) +
            SUM(total_date_11) +
            SUM(total_date_12) +
            SUM(total_date_13) +
            SUM(total_date_14) +
            SUM(total_date_15) +
            SUM(total_date_16) +
            SUM(total_date_17) +
            SUM(total_date_18) +
            SUM(total_date_19) +
            SUM(total_date_20) +
            SUM(total_date_21) +
            SUM(total_date_22) +
            SUM(total_date_23) +
            SUM(total_date_24) +
            SUM(total_date_25) +
            SUM(total_date_26) +
            SUM(total_date_27) +
            SUM(total_date_28) +
            SUM(total_date_29) +
            SUM(total_date_30) +
            SUM(total_date_31)) as total
        FROM (
            SELECT a.p_month, a.p_year, a.revision, a.product_no, b.component_id, c.name as customer_name,
            (a.date_1 * b.qty) as total_date_1,
            (a.date_2 * b.qty) as total_date_2,
            (a.date_3 * b.qty) as total_date_3,
            (a.date_4 * b.qty) as total_date_4,
            (a.date_5 * b.qty) as total_date_5,
            (a.date_6 * b.qty) as total_date_6,
            (a.date_7 * b.qty) as total_date_7,
            (a.date_8 * b.qty) as total_date_8,
            (a.date_9 * b.qty) as total_date_9,
            (a.date_10 * b.qty) as total_date_10,
            (a.date_11 * b.qty) as total_date_11,
            (a.date_12 * b.qty) as total_date_12,
            (a.date_13 * b.qty) as total_date_13,
            (a.date_14 * b.qty) as total_date_14,
            (a.date_15 * b.qty) as total_date_15,
            (a.date_16 * b.qty) as total_date_16,
            (a.date_17 * b.qty) as total_date_17,
            (a.date_18 * b.qty) as total_date_18,
            (a.date_19 * b.qty) as total_date_19,
            (a.date_20 * b.qty) as total_date_20,
            (a.date_21 * b.qty) as total_date_21,
            (a.date_22 * b.qty) as total_date_22,
            (a.date_23 * b.qty) as total_date_23,
            (a.date_24 * b.qty) as total_date_24,
            (a.date_25 * b.qty) as total_date_25,
            (a.date_26 * b.qty) as total_date_26,
            (a.date_27 * b.qty) as total_date_27,
            (a.date_28 * b.qty) as total_date_28,
            (a.date_29 * b.qty) as total_date_29,
            (a.date_30 * b.qty) as total_date_30,
            (a.date_31 * b.qty) as total_date_31
        FROM generate_mpp a
        JOIN mst_bom b ON a.product_no = b.item_id
        LEFT JOIN mst_customer c ON a.customer_id = c.number
        WHERE a.p_month = '$filter_month' and a.p_year = '$filter_year' and a.revision = '$filter_revision' and b.component_id = '$product_no'
        GROUP BY a.product_no, b.component_id, b.rout_id
        ORDER BY a.product_no, b.component_id asc) z
        GROUP BY z.product_no");
        $records = $ospo->result_array();

        $balance = 0;
        foreach ($records as $record) {
            $balance += $record['total'];
        }

        die(json_encode(array_merge(array("rows" => $records), ["footer" => [["product_no" => "Grand Total", "total" => $balance]]])));
    }
}

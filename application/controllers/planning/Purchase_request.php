<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_request extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->pg = $this->load->database('pg', TRUE);
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/purchase_request');
        } else {
            redirect('error_access');
        }
    }

    public function readSupplier(){
        $query = $this->db->query("SELECT id as supplier_id, `number` as supplier_no, `name` as supplier_name FROM suppliers ORDER BY `name` ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readProductFamily($supplier){
        $supplier = base64_decode($supplier);
        $query = $this->db->query("SELECT DISTINCT `number` as product_family_no, `name` as product_family 
            FROM mst_item_family a 
            JOIN mst_item_raw b ON a.number = b.pfm_id
            JOIN supplier_items c ON b.item_id = c.item_id
            WHERE `number` != '06' and c.supplier_id = '$supplier'");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readParts($supplier, $prodfam){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier = base64_decode($supplier);
        $prodfam = base64_decode($prodfam);
        
        $query = $this->db->query("SELECT b.item_id, b.item_name, a.mpq, a.moq, a.share_order, a.leadtime 
            FROM supplier_items a 
            JOIN mst_item_raw b ON a.item_id = b.item_id
            WHERE a.supplier_id = '$supplier' and b.pfm_id = '$prodfam' and b.item_id like '%$post%' and a.share_order > 0
            ORDER BY b.item_id ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readPr($supplier_id, $prodfam){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $supplier_id = base64_decode($supplier_id);
        $prodfam = base64_decode($prodfam);
        
        $query = $this->db->query("SELECT a.purchase_request_no, b.name as supplier_name
            FROM purchase_requests a 
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.supplier_id = '$supplier_id' and a.product_family = '$prodfam' and a.purchase_request_no like '%$post%'
            GROUP BY a.purchase_request_no
            ORDER BY a.purchase_request_no ASC
        ");
        $records = $query->result_array();

        echo json_encode($records);
    }

    function forecast($item_id, $month, $year, $revision){
        $q_forecast = $this->db->query("SELECT a.component_id, c.item_name, SUM(a.qty) as qty, 
            (SUM(a.qty) * b.month_1) as m1, 
            (SUM(a.qty) * b.month_2) as m2, 
            (SUM(a.qty) * b.month_3) as m3,
            (SUM(a.qty) * b.month_4) as m4,
            (SUM(a.qty) * b.month_5) as m5,
            (SUM(a.qty) * b.month_6) as m6,
            (SUM(a.qty) * b.month_7) as m7,
            (SUM(a.qty) * b.month_8) as m8,
            (SUM(a.qty) * b.month_9) as m9,
            (SUM(a.qty) * b.month_10) as m10,
            (SUM(a.qty) * b.month_11) as m11,
            (SUM(a.qty) * b.month_12) as m12
            FROM mst_bom a
            JOIN mst_forecast b ON a.item_id = b.product_no
            JOIN mst_item_raw c ON a.component_id = c.item_id
            WHERE b.p_month = '$month' and b.p_year = '$year' and b.revision = '$revision' 
            and (b.month_1 > 0 or b.month_2 > 0 or b.month_3 > 0) and a.component_id = '$item_id'
            GROUP BY a.component_id");
        $forecast = $q_forecast->row();

        return $forecast;
    }

    public function readMonths()
    {
        $month = $this->input->get('month');
        $year = $this->input->get('year');

        $start = strtotime($year . "-" . $month . "-01");
        $begin =  strtotime(date('Y-m-d', strtotime('+4 month', $start)));
        $end =  strtotime(date('Y-m-d', strtotime('-3 month', $start)));

        $monthName = array();
        while ($end < $begin) {
            $monthName[] = date('M Y', $end);
            $end = strtotime("+1 month", $end);
        }

        die(json_encode($monthName));
    }

    public function purchaseRequestNo($code, $add = ""){

        if($add != ""){
            $code = base64_decode($code);
        }

        $sqlGetID	= $this->db->query("SELECT max(purchase_request_no) as kode FROM purchase_requests WHERE purchase_request_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;
        
        if ($kode == NULL) {
            $autoID = sprintf("%02s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -2);
            $urutan++;
            $autoID	= sprintf("%02s", $urutan);
        }

        if($add == ""){
            return $code.$autoID;
        }else{
            echo $code.$autoID;
        }
    }

    public function getDataMrp()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));

            if($filter_product_family != ""){
                if($filter_product_no != ""){
                    $array_pfm = array("product_family" => $filter_product_family, "part_no" => $filter_product_no);
                }else{
                    $array_pfm = array("product_family" => $filter_product_family);
                }
            }else{
                $array_pfm = array();
            }

            $this->api->delete("purchase_requests", array_merge(["p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision, "type" => "REGULAR"], $array_pfm));

            if(date('Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01"))) == $filter_year){
                $p_year_min = $filter_month;
                $p_month_min = $filter_year;
            }else{
                $p_year_min =  date('Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
                $p_month_min =  date('m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            }

            $p1_min = "m".date('n', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p2_min = "m".date('n', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p3_min = "m".date('n', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p1_plus = "m".date('n', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p2_plus = "m".date('n', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $p3_plus = "m".date('n', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $forecast_rev = $this->db->query("SELECT DISTINCT revision FROM `mst_forecast` WHERE p_month = '$filter_month' and p_year = '$filter_year' ORDER BY revision DESC LIMIT 1");
            $forecast_row = $forecast_rev->row();

            $forecast_rev_min = $this->db->query("SELECT DISTINCT revision FROM `mst_forecast` WHERE p_month = '$p_month_min' and p_year = '$p_year_min' ORDER BY revision DESC LIMIT 1");
            $forecast_row_min = $forecast_rev_min->row();

            //Select Query
            $this->db->select('c.mpq, c.moq, c.leadtime, a.total_need, a.purchase_order, b.item_id, b.item_name, b.pfm_id, a.product_family as prodfam, c.share_order, c.supplier_id, e.number as supplier_no, d.class, d.composition, d.safety_stock, d.safety');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('mst_item_raw b', 'a.part_no = b.item_id');
            $this->db->join('supplier_items c', 'b.item_id = c.item_id and c.share_order > 0');
            $this->db->join('generate_mrp_abcclass d', 'a.part_no = d.part_no and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
            $this->db->join('suppliers e', 'c.supplier_id = e.id');
            // $this->db->join('mst_item_family d', 'd.number = b.pfm_id');
            $this->db->where('(a.purchase_order + d.safety_stock) >', 0);
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_product_family != ""){
                $this->db->where('a.product_family', $filter_product_family);
            }
            if($filter_product_no != ""){
                $this->db->where('b.item_id', $filter_product_no);
            }
            $this->db->group_by('c.supplier_id');
            $this->db->group_by('a.part_no');
            $this->db->order_by('c.supplier_id');
            $this->db->order_by('a.product_family');
            $mpsDetails = $this->db->get()->result_array();

            $arr = array();
            $end_supplier = "";
            $end_product_family = "";
            $end_purchase_request_no = "";
            foreach ($mpsDetails as $generate) {
                $supplier_no = $generate['supplier_no'];
                $item_id = $generate['item_id'];

                if($supplier_no == $end_supplier && $generate['pfm_id'] == $end_product_family){
                    $purchase_request_no = $end_purchase_request_no;
                    $no++;
                }else{
                    $purchase_request_no = $this->purchaseRequestNo("PRMC-IND".@trim($supplier_no.date("Ym")). "-" .trim($generate['pfm_id']));
                    $no = 1;
                }

                $forecast_min = $this->forecast($item_id, $filter_month, $filter_year, @$forecast_row_min->revision);
                $forecast_plus = $this->forecast($item_id, $filter_month, $filter_year, @$forecast_row->revision);
                
                if(round($generate['total_need']) > 0 && $generate['moq'] > 0){
                    $final = ($generate['total_need'] + $generate['safety_stock']);
                    $purchase_order = (ceil($final / $generate['moq']) * $generate['moq']);
                }else{
                    $purchase_order = 0;
                }

                // if((round($generate['total_need'] - $generate['safety_stock'])) > 0 || $generate['moq'] == 0){
                //     $purchase_order = 0;
                // }else{
                //     $final = ($generate['total_need'] - $generate['safety_stock']);
                //     $purchase_order = abs(ceil($final / $generate['moq']) * $generate['moq']);
                // }

                $split = ($purchase_order * ($generate['share_order'] / 100));
                if($split > 0){
                    $purchase_request = (ceil($split / $generate['moq']) * $generate['moq']);
                }else{
                    $purchase_request = 0;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "purchase_request_no" => $purchase_request_no,
                    "purchase_request_date" => date("Y-m-d"),
                    "supplier_id" => $generate['supplier_id'],
                    "part_no" => $generate['item_id'],
                    "part_name" => $generate['item_name'],
                    "product_family" => $generate['prodfam'],
                    "mpq" => $generate['mpq'],
                    "moq" => $generate['moq'],
                    "leadtime" => $generate['leadtime'],
                    "share_order" => $generate['share_order'],
                    "total_need" => ($generate['total_need'] + $generate['safety_stock']),
                    "purchase_order" => $purchase_order,
                    "purchase_request" => $purchase_request,
                    "forecast_m1" => @$forecast_min->$p1_min,
                    "forecast_m2" => @$forecast_min->$p2_min,
                    "forecast_m3" => @$forecast_min->$p3_min,
                    "forecast_plus_1" => @$forecast_plus->$p1_plus,
                    "forecast_plus_2" => @$forecast_plus->$p2_plus,
                    "forecast_plus_3" => @$forecast_plus->$p3_plus,
                    "forecast_avg" => round((@$forecast_min->$p1_min + @$forecast_min->$p2_min + @$forecast_min->$p3_min + @$forecast_plus->$p1_plus + @$forecast_plus->$p2_plus + @$forecast_plus->$p3_plus) / 6)
                );

                $end_supplier = $supplier_no;
                $end_product_family = $generate['pfm_id'];
                $end_purchase_request_no = $purchase_request_no;
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name, SUM(a.purchase_request) as total_pr');
            $this->db->from("(select * from purchase_requests order by approved_to desc) a");
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join("(SELECT purchase_request_no, MAX(approved_to) AS max_approved FROM purchase_requests GROUP BY purchase_request_no) c", "a.purchase_request_no = c.purchase_request_no AND (a.approved_to = c.max_approved OR (a.approved_to = '' AND c.max_approved = ''))", 'left');
            $this->db->where("a.type", "REGULAR");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_part_no);
            $this->db->group_by('a.purchase_request_no');
            $this->db->order_by('a.purchase_request_no', 'ASC');
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

    public function datatableDetails($purchase_request_no, $revision)
    {
        $purchase_request_no = base64_decode($purchase_request_no);
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_month = trim(base64_decode($this->input->get('filter_month')));
        $filter_year = trim(base64_decode($this->input->get('filter_year')));

        $ending = date("Y-m-t", strtotime($filter_year . "-" . $filter_month . "-01"));
        $po_date_1 = date('Y-m', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $po_date_2 = date('Y-m', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $po_date_3 = date('Y-m', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_requests a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
        $this->db->where('a.revision', $revision);
        $this->db->where('a.purchase_request_no', $purchase_request_no);
        $this->db->like('a.part_no', $filter_part_no);
        $this->db->order_by('a.part_no', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $productTotals = [];
        foreach ($records as $record) {
            $product = $record['part_no'];
            $shareOrder = $record['share_order'];
            $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
        }

        $finals = array();
        $total_m1 = 0;
        $total_m2 = 0;
        $total_m3 = 0;
        $total_avg = 0;
        $total_prodplan_1 = 0;
        $total_prodplan_2 = 0;
        $total_prodplan_3 = 0;
        $total_forecast_m1 = 0;
        $total_forecast_m2 = 0;
        $total_forecast_m3 = 0;
        $total_forecast_plus_1 = 0;
        $total_forecast_plus_2 = 0;
        $total_forecast_plus_3 = 0;
        $total_avg_forecast = 0;
        $total_purchase_request = 0;
        $total_purchase_order = 0;
        $total_need = 0;
        foreach ($records as $data) {
            $item_id = $data['part_no'];
            $leadtime = date('Y-m-01', strtotime('+'.$data['leadtime'].' days', strtotime($ending)));

            //Purchase History
            $qpo1 = $this->db->query("SELECT qty FROM purchase_orders WHERE `purchase_order_date` LIKE '%$po_date_1%' and item_id = '$item_id' and revision = '$revision' and p_month = '$filter_month' and p_year = '$filter_year'");
            $rpo1 = $qpo1->row();

            $qpo2 = $this->db->query("SELECT qty FROM purchase_orders WHERE `purchase_order_date` LIKE '%$po_date_2%' and item_id = '$item_id' and revision = '$revision' and p_month = '$filter_month' and p_year = '$filter_year'");
            $rpo2 = $qpo2->row();

            $qpo3 = $this->db->query("SELECT qty FROM purchase_orders WHERE `purchase_order_date` LIKE '%$po_date_3%' and item_id = '$item_id' and revision = '$revision' and p_month = '$filter_month' and p_year = '$filter_year'");
            $rpo3 = $qpo3->row();
            
            $m1 = date('Y-m-01', strtotime($leadtime));
            $m2 = date('Y-m-01', strtotime('+1 month', strtotime($leadtime)));
            $m3 = date('Y-m-01', strtotime('+2 month', strtotime($leadtime)));
            $m11 = "m".date('n', strtotime($leadtime));
            $m22 = "m".date('n', strtotime('+1 month', strtotime($leadtime)));
            $m33 = "m".date('n', strtotime('+2 month', strtotime($leadtime)));

            $qmps1 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m1' and product_no = '$item_id' and revision = '$revision' and p_month = '$filter_month' and p_year = '$filter_year'");
            $rpms1 = $qmps1->row();

            $qmps2 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m2' and product_no = '$item_id' and revision = '$revision' and p_month = '$filter_month' and p_year = '$filter_year'");
            $rpms2 = $qmps2->row();

            $qmps3 = $this->db->query("SELECT qty FROM generate_mrp WHERE `period` = '$m3' and product_no = '$item_id' and revision = '$revision' and p_month = '$filter_month' and p_year = '$filter_year'");
            $rpms3 = $qmps3->row();

            $forecast = $this->forecast($item_id, $filter_month, $filter_year, @$revision);

            if(@$rpms1->qty > 0){
                $prod1 = @$rpms1->qty;
            }else{
                $prod1 = @$forecast->$m11;
            }

            if(@$rpms2->qty > 0){
                $prod2 = @$rpms2->qty;
            }else{
                $prod2 = @$forecast->$m22;
            }

            if(@$rpms3->qty > 0){
                $prod3 = @$rpms3->qty;
            }else{
                $prod3 = @$forecast->$m33;
            }

            $class = ($productTotals[$data['part_no']] >= 100) ? 'red' : '';
            $finals[] = array_merge($data, [
                "color" => $class,
                "m1" => empty(@$rpo1->qty) ? 0 : @$rpo1->qty,
                "m2" => empty(@$rpo2->qty) ? 0 : @$rpo2->qty,
                "m3" => empty(@$rpo3->qty) ? 0 : @$rpo3->qty,
                "avg" => empty((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty)) ? 0 : ((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty) / 3),
                "prodplan_1" => $prod1,
                "prodplan_2" => $prod2,
                "prodplan_3" => $prod3,
                "avg_forecast" => round(($data['forecast_m1'] + $data['forecast_m2'] + $data['forecast_m3'] + $data['forecast_plus_1'] + $data['forecast_plus_2'] + $data['forecast_plus_3'] + $prod1 + $prod2 + $prod3) / 9)
            ]);

            $total_m1 += empty(@$rpo1->qty) ? 0 : @$rpo1->qty;
            $total_m2 += empty(@$rpo2->qty) ? 0 : @$rpo2->qty;
            $total_m3 += empty(@$rpo3->qty) ? 0 : @$rpo3->qty;
            $total_avg += empty((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty)) ? 0 : ((@$rpo1->qty + @$rpo2->qty + @$rpo3->qty) / 3);
            $total_prodplan_1 += $prod1;
            $total_prodplan_2 += $prod2;
            $total_prodplan_3 += $prod3;
            $total_forecast_m1 += $data['forecast_m1'];
            $total_forecast_m2 += $data['forecast_m2'];
            $total_forecast_m3 += $data['forecast_m3'];
            $total_forecast_plus_1 += $data['forecast_plus_1'];
            $total_forecast_plus_2 += $data['forecast_plus_2'];
            $total_forecast_plus_3 += $data['forecast_plus_3'];
            $total_avg_forecast += (($data['forecast_m1'] + $data['forecast_m2'] + $data['forecast_m3'] + $data['forecast_plus_1'] + $data['forecast_plus_2'] + $data['forecast_plus_3'] + $prod1 + $prod2 + $prod3) / 9);
            $total_purchase_request += $data['purchase_request'];
            $total_purchase_order += $data['purchase_order'];
            $total_need += $data['total_need'];
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
        'rows' => $finals, 
        'footer' => array([
                "part_no" => "Grand Total",
                "purchase_request" => $total_purchase_request,
                "purchase_order" => $total_purchase_order,
                "total_need" => $total_need,
                "m1" => $total_m1,
                "m2" => $total_m2,
                "m3" => $total_m3,
                "avg" => $total_avg,
                "prodplan_1" => $total_prodplan_1,
                "prodplan_2" => $total_prodplan_2,
                "prodplan_3" => $total_prodplan_3,
                "forecast_m1" => $total_forecast_m1,
                "forecast_m2" => $total_forecast_m2,
                "forecast_m3" => $total_forecast_m3,
                "forecast_plus_1" => $total_forecast_plus_1,
                "forecast_plus_2" => $total_forecast_plus_2,
                "forecast_plus_3" => $total_forecast_plus_3,
                "avg_forecast" => $total_avg_forecast
            ])
        ]);
        echo json_encode($result);
    }

    public function datatableConfirm(){
        //Select Query
        $this->db->select('a.*, b.name as supplier_name, SUM(a.qty) as total_qty');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.status_edit', 1);
        $this->db->group_by('a.purchase_request_no');
        $this->db->order_by('a.updated_date');
        $this->db->order_by('a.created_date');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $records
        ]);
        echo json_encode($result);
    }

    public function createAdds()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $records = $this->api->reads('purchase_requests', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "part_no" => $post['part_no'],
                "supplier_id" => $post['supplier_id'],
                "purchase_request_no" => $post['purchase_request_no']
            ]);

            if(count($records) > 0){
                show_error("Duplicate Item ID");
            }else{
                $send = $this->api->create('purchase_requests', $post, "PR", "JUTA");
                echo $send;
            }
        }
    }

    public function updateConfirm(){
        if ($this->input->post()) {
            $purchase_request_no = $this->input->post('purchase_request_no');
            $item_id = $this->input->post('item_id');
            
            $this->db->update('purchase_orders', ["status_edit" => 0], ["purchase_request_no" => $purchase_request_no, "item_id" => $item_id]);
            
            echo json_encode(array("message" => "success"));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            // $records = $this->api->reads('purchase_requests', [], [
            //     "p_month" => $post['p_month'],
            //     "p_year" => $post['p_year'],
            //     "revision" => $post['revision'],
            //     "part_no" => $post['part_no'],
            //     "supplier_name" => $post['supplier_name']
            // ]);

            // if (count($records) > 0) {
            //     $send = $this->api->update('purchase_requests', [
            //         "p_month" => $post['p_month'],
            //         "p_year" => $post['p_year'],
            //         "revision" => $post['revision'],
            //         "part_no" => $post['part_no'],
            //         "supplier_name" => $post['supplier_name']
            //     ], $post);

            //     echo $send;
            // } else {
                $send = $this->api->create('purchase_requests', $post, "PR", "JUTA");
                echo $send;
            // }
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $send = $this->api->update('purchase_requests', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('purchase_requests', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=purchase_requests_$format.xls");
            }

            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));

            $month_0 =  date('M Y', strtotime($filter_year . "-" . $filter_month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_3 =  date('M Y', strtotime('-3 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_2 =  date('M Y', strtotime('-2 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $month_min_1 =  date('M Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.type', "REGULAR");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_part_no);
            $this->db->order_by('a.part_no', 'ASC');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
            .box-green{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #D6FFCF;
            }

            .box-red{
                height: 10px;
                width: 10px;
                margin: 10px;
                font-size: 10px;
                background-color: #FFCFCF;
            }
            </style>
            <body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>PURCHASE REQUEST</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                    <br><br><br>
                </center>

                <table id="customers" border="1">
                    <tr>
                        <th rowspan="2" style="text-align:center;" width="20">No</th>
                        <th rowspan="2" style="text-align:center;">PERIOD</th>
                        <th rowspan="2" style="text-align:center;">REVISION</th>
                        <th rowspan="2" style="text-align:center;">VENDOR</th>
                        <th rowspan="2" style="text-align:center;">ITEM NO</th>
                        <th rowspan="2" style="text-align:center;">ITEM NAME</th>
                        <th rowspan="2" style="text-align:center;">PRODFAM</th>
                        <th rowspan="2" style="text-align:center;">SHARE<br>ORDER</th>
                        <th rowspan="2" style="text-align:center;">MPQ</th>
                        <th rowspan="2" style="text-align:center;">MOQ</th>
                        <th rowspan="2" style="text-align:center;">LEADTIME</th>
                        <th rowspan="2" style="text-align:center;">TOTAL NEED</th>
                        <th rowspan="2" style="text-align:center;">MRP RESULT</th>
                        <th rowspan="2" style="text-align:center;">PURCHASE REQUEST</th>
                        <th colspan="3" style="text-align:center;">PURCHASE HISTORY</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th colspan="6" style="text-align:center;">FORECAST SUPPLIER</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th rowspan="2" style="text-align:center;">REASON</th>
                    </tr>
                    <tr>
                        <th style="text-align:center;">'.$month_min_3.'</th>
                        <th style="text-align:center;">'.$month_min_2.'</th>
                        <th style="text-align:center;">'.$month_min_1.'</th>
                        <th style="text-align:center;">'.$month_min_3.'</th>
                        <th style="text-align:center;">'.$month_min_2.'</th>
                        <th style="text-align:center;">'.$month_min_1.'</th>
                        <th style="text-align:center;">'.$month_1.'</th>
                        <th style="text-align:center;">'.$month_2.'</th>
                        <th style="text-align:center;">'.$month_3.'</th>
                    </tr>';

                    $no = 1;

                    $productTotals = [];
                    foreach ($records as $record) {
                        $product = $record['part_no'];
                        $shareOrder = $record['share_order'];
                        $productTotals[$product] = isset($productTotals[$product]) ? $productTotals[$product] + $shareOrder : $shareOrder;
                    }
                    foreach ($records as $record) {
                        $class = ($productTotals[$record['part_no']] >= 100) ? 'red' : '';
                        if($class == ""){
                            $style = "style='background:#FFD2D2;'";
                        }else{
                            $style = "";
                        }

                        $html .= "  <tr ".$style.">
                                        <td>".$no."</td>
                                        <td>".$record['p_year'] . $record['p_month']."</td>
                                        <td>".$record['revision']."</td>
                                        <td>".$record['supplier_name']."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['part_no'])."</td>
                                        <td>".$record['part_name']."</td>
                                        <td>".$record['product_family']."</td>
                                        <td>".$record['share_order']."</td>
                                        <td>".$record['mpq']."</td>
                                        <td>".$record['moq']."</td>
                                        <td>".$record['leadtime']."</td>
                                        <td style='text-align:right;'>".round($record['total_need'])."</td>
                                        <td style='text-align:right;'>".round($record['purchase_order'])."</td>
                                        <td style='text-align:right;'>".round($record['purchase_request'])."</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>0</td>
                                        <td style='text-align:right;'>".round($record['forecast_m3'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_m2'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_m1'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_1'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_2'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_plus_3'])."</td>
                                        <td style='text-align:right;'>".round($record['forecast_avg'])."</td>
                                        <td style='text-align:right;'>".round($record['reason'])."</td>
                                    </tr>";
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
?>
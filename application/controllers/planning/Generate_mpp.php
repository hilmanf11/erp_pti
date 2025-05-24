<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mpp extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        //$this->load->model('banshu');
        //$this->pg = $this->load->database('pg', TRUE);
        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mpp.product_no]');
    }

    public function index(){
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mpp');
        } else {
            redirect('error_access');
        }
    }

    public function readRevisions($month, $year){
        $this->db->select('revision');
        $this->db->from('generate_mps');
        $this->db->where('p_month', $month);
        $this->db->where('p_year', $year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        die(json_encode($revisions));
    }

    public function datatableNotMps(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $this->db->select('a.prod_plan, a.product_no, a.product_name, b.name as customer_name');
        $this->db->from('generate_mps_detail a');
        $this->db->join('mst_customer b', 'a.customer_id = b.number');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.prod_plan >', 0);
        $this->db->group_by('a.product_no');
        $this->db->order_by('b.name', 'asc');
        $records = $this->db->get()->result_array();

        $data = array();
        foreach ($records as $record) {
            $this->db->select('*');
            $this->db->from('generate_mpp');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('product_no', $record['product_no']);
            $this->db->group_by('revision');
            $this->db->order_by('revision', 'desc');
            $mpp = $this->db->get()->row();

            if(empty($mpp->product_no)){
                $data[] = array(
                    "product_no" => $record['product_no'],
                    "product_name" => $record['product_name'],
                    "customer_name" => $record['customer_name'],
                    "prod_plan" => $record['prod_plan'],
                );
            }
        }

        die(json_encode($data));
    }

    public function datatables(){
        $this->dummy = $this->load->database('dummy', TRUE);

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, e.item_alias, e.capacity, e.lot, (a.date_1 + a.date_2 + a.date_3 + a.date_4 + a.date_5 + a.date_6 + a.date_7 + a.date_8 + a.date_9 + a.date_10 + a.date_11 + a.date_12 + a.date_13 + a.date_14 + a.date_15 + a.date_16 + a.date_17 + a.date_18 + a.date_19 + a.date_20 + a.date_21 + a.date_22 + a.date_23 + a.date_24 + a.date_25 + a.date_26 + a.date_27 + a.date_28 + a.date_29 + a.date_30 + a.date_31) as floating,  b.name as customer_name, d.prod_plan as mpsprod, f.circuit_no as cct');
        $this->db->from('generate_mpp a');
        $this->db->join('mst_customer b', 'a.customer_id = b.number');
        $this->db->join('generate_mps c', "a.p_month = c.p_month and a.p_year = c.p_year and c.revision = '$filter_revision' and a.product_no = c.product_no");
        $this->db->join("(SELECT * FROM generate_mps_detail ORDER BY ltpp_month2 ASC) d", "d.p_month = '$filter_month' and d.p_year = '$filter_year' and d.revision = '$filter_revision' and a.product_no = d.product_no");
        $this->db->join("mst_item e", "a.product_no = e.item_id");
        $this->db->join("wip_mst_wos_cct f", "e.item_id = f.mstwos_assyno", "left");
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $revisions->revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.product_no', 'ASC');
        $this->db->order_by('a.product_no', 'ASC');

        //Total Data
        $totalRows = $this->db->count_all_results('', false);

        //Limit 1 - 10
        $this->db->limit($rows, $offset);

        //Get Data Array
        $records = $this->db->get()->result_array();

        foreach ($records as $record) {
            $periode = $record['p_year'] . $record['p_month'];
            $revision = $record['revision'];
            $assy_no = $record['product_no'];
            $line = $record['line_no'];

            $firstDate = date('Y-m-01', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
            $endDate = date('Y-m-t', strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));

            $no = 1;
            $arr = array();
            $arr_date = array();
            while (strtotime($firstDate) <= strtotime($endDate)) {
                $working_date = date('Y-m-d', strtotime($firstDate));
                $day = date('j', strtotime($firstDate));

                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                $this->dummy->select('a.*');
                $this->dummy->from("wip_trx_mpp a");
                $this->dummy->join("wip_trx_wds b", "a.serial_mpp = b.serial_mpp");
                $this->dummy->where("a.periode", $periode);
                $this->dummy->where("a.assy_no", $assy_no);
                $this->dummy->where("a.line", $line);
                $this->dummy->where("a.wp_date", $working_date);
                $wip_trx_mpp = $this->dummy->get()->result_array();

                if(count($wip_trx_mpp) > 0){
                    $status_wds = "F";
                }else{
                    $status_wds = $record["date_".$day];
                }

                $arr = array("wds_".$no => $status_wds, "log_".$no => json_encode($wip_trx_mpp));
                $arr_date = array_merge($arr, $arr_date);

                $no++;
                $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
            }
            $finals[] = array_merge($arr_date, $record);
        }
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => @$finals]);
        echo json_encode($result);
    }

    function getdata(){
        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $ltppMonth = $filter_year . "-" . $filter_month . "-01";
        $hkw = 0;
        $ltppMonth = $filter_year . "-" . $filter_month . "-01";
        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $start = strtotime(date('Y-m-01', $monthStart));
        $finish = strtotime(date('Y-m-t', $monthStart));
        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
            $working_date = date('Y-m-d', $z);

            $this->db->select('remarks');
            $this->db->from('working_calendar');
            $this->db->where('working_date', $working_date);
            $holiday = $this->db->get()->row();

            if (date('w', $z) !== '0') {
                if (@$holiday->remarks != null or @$holiday->remarks != "") {
                    $hkw += 0;
                } else {
                    $hkw += 1;
                }
            } else {
                $hkw += 0;
            }
        }

        $this->db->select("a.product_no, a.product_name, a.circuit_no, a.prod_plan, a.line_no, b.lot, b.id_customer");
        $this->db->from('generate_mps_detail a');
        $this->db->join('mst_item b', 'a.product_no = b.item_id');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->where('a.ltpp_month2', $ltppMonth);
        $this->db->where('a.prod_plan > 0');
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by("a.product_no");
        $this->db->order_by("a.product_no", "asc");
        $recordDetails = $this->db->get()->result_array();

        $mpp = array();
        foreach ($recordDetails as $detail) {
            $rows = array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => 0,
                "line_no" => $detail['line_no'],
                "customer_id" => $detail['id_customer'],
                "product_no" => $detail['product_no'],
                "product_name" => $detail['product_name'],
                "circuit_no" => $detail['circuit_no'],
                "prod_plan" => $detail['prod_plan'],
            );
            $prodplan = $detail['prod_plan'];
            $prodplanHkw = ($prodplan / $hkw);
            $lots = @(ceil($prodplanHkw / $detail['lot']) * $detail['lot']);
            $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
            $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
            $no = 1;
            while (strtotime($firstDate2) <= strtotime($endDate2)) {
                $working_date = date('Y-m-d', strtotime($firstDate2));

                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                if ($prodplan >= $lots) {
                    $qty = is_nan($lots) ? 0 : $lots;
                } elseif ($prodplan < 0) {
                    $qty = 0;
                } else {
                    $qty = $prodplan;
                }

                if (date('w', strtotime($firstDate2)) !== '0') {
                    if (@$holiday->remarks != null or @$holiday->remarks != "") {
                        $rows = array_merge($rows, array("date_".$no => "W"));
                    } else {
                        $rows = array_merge($rows, array("date_".$no => "$qty"));
                        $prodplan = ($prodplan - $lots);
                    }
                } else {
                    $rows = array_merge($rows, array("date_".$no => "W"));
                }

                $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));
                $no++;
            }
            $mpp[] = $rows;
        }
        echo json_encode($mpp);
    }
    
    public function push_data(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $this->db->select('a.*, b.line_nick');
        $this->db->from('generate_mpp a');
        $this->db->join('mst_line b', 'a.line_no = b.line_no');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        //$this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->order_by('a.line_no', 'ASC');
        $this->db->order_by('a.product_no', 'ASC');
        $totalRows = $this->db->count_all_results('', false);

        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $this->dummy = $this->load->database('dummy', TRUE);
            $this->pg2 = $this->load->database('pg2', TRUE);

            $periode = $filter_year . $filter_month;

            // if($filter_line_no == "" && $filter_product_no == ""){
            //     $this->dummy->query("DELETE FROM wip_trx_mpp where periode = '$periode' and rev = '$filter_revision'");
            //     $this->db->query("DELETE FROM generate_mpp_detail where periode = '$periode' and rev = '$filter_revision'");
            // }

            $mpp_final = array();
            $last_line_no = "";
            $last_assy_no = "";
            $serial_no = 1;
            $pos_assy = 1;
            foreach ($records as $record) {
                $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
                $no = 1;
                $wp = 1;
                $alfabet = "z";
                $wp_weekend = 0;
                $mpp = array();

                $line_no = $record['line_no'];
                $assy_no = $record['product_no'];

                $array_assy[] = array(
                    "assy_no" => $assy_no
                );

                $this->dummy->select('max(pos_assy::int) as pos_assy, max(serial_mpp) as serial');
                $this->dummy->from("wip_trx_mpp");
                $this->dummy->where("periode", $periode);
                // $this->dummy->where("rev", $filter_revision);
                $this->dummy->where("line", $record['line_no']);
                //$this->dummy->where("assy_no", $record['product_no']);
                //$this->dummy->group_by('assy_no');
                $wip_trx_mpp = $this->dummy->get()->row();

                $this->dummy->select('pos_assy');
                $this->dummy->from("wip_trx_mpp");
                $this->dummy->where("periode", $periode);
                $this->dummy->where("line", $record['line_no']);
                $this->dummy->where("assy_no", $record['product_no']);
                // $this->dummy->order_by("pos_assy", "desc");
                $wip_trx_mpp_pos_assy = $this->dummy->get()->result_array();
                $count_wip_mpp = count($wip_trx_mpp_pos_assy);

                if($wip_trx_mpp){
                    if($last_line_no != $line_no){
                        $serial_no = substr($wip_trx_mpp->serial, 9) + 1;
                    }
                }else{
                    if($last_line_no != $line_no){
                        $serial_no = 1;
                    }
                }

                //Jika by assy no lebih dari 1 
                if($count_wip_mpp > 0){
                    $pos_assy = rtrim(@$wip_trx_mpp_pos_assy[0]['pos_assy']);
                }else{
                    if(!empty($wip_trx_mpp->pos_assy)){
                        // if($count_wip_mpp == 0){
                        //     $pos_assy = rtrim(@$wip_trx_mpp->pos_assy + 1);
                        // }else{
                        //     $pos_assy = rtrim(@$wip_trx_mpp->pos_assy);
                        // }
                        $pos_assy = rtrim(@$wip_trx_mpp->pos_assy + 1);
                    }else{
                        if($last_line_no != $line_no){
                            $pos_assy = 1;
                        }
                    }
                }

                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $woc = date('my', strtotime($firstDate));
                    $serial = date('ym', strtotime($firstDate));
                    $working_date = date('Y-m-d', strtotime($firstDate));
                    $wp_date = date('d', strtotime($firstDate));
                    $serial_tgl = "01";

                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date);
                    $holiday = $this->db->get()->row();

                    if (date('w', strtotime($firstDate)) !== '0' && date('w', strtotime($firstDate)) !== '6') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            if($record["date_".$no] != "W" && $record["date_".$no] != "" && $record["date_".$no] != "0"){
                                if($alfabet == "z"){
                                    $alfabets = "A";
                                }elseif($alfabet == "A"){
                                    $alfabets = "B";
                                }elseif($alfabet == "B"){
                                    $alfabets = "C";
                                }elseif($alfabet == "C"){
                                    $alfabets = "D";
                                }elseif($alfabet == "D"){
                                    $alfabets = "E";
                                }elseif($alfabet == "E"){
                                    $alfabets = "F";
                                }elseif($alfabet == "F"){
                                    $alfabets = "G";
                                }elseif($alfabet == "G"){
                                    $alfabets = "H";
                                }elseif($alfabet == "H"){
                                    $alfabets = "I";
                                }elseif($alfabet == "I"){
                                    $alfabets = "J";
                                }elseif($alfabet == "J"){
                                    $alfabets = "K";
                                }elseif($alfabet == "K"){
                                    $alfabets = "L";
                                }elseif($alfabet == "L"){
                                    $alfabets = "M";
                                }elseif($alfabet == "M"){
                                    $alfabets = "N";
                                }elseif($alfabet == "N"){
                                    $alfabets = "O";
                                }else{  
                                    $alfabets = "";
                                }

                                $wpp = sprintf("%02s", $wp);
                                $rows[] = array(
                                    "serial_mpp" => "1".$serial.$record['line_no'].$serial_tgl."2".sprintf("%05s", $serial_no),
                                    "periode" => $filter_year . $filter_month,
                                    "assy_no" => $record['product_no'],
                                    "rev" => $filter_revision2,
                                    "color" => "2",
                                    "line" => $record['line_no'],
                                    "qty1" => "0",
                                    "rev1" => "0",
                                    "qty2" => "0",
                                    "rev2" => "0",
                                    "qty3" => "0",
                                    "rev3" => "0",
                                    "wp_date" => $working_date,
                                    "trx_user" => "1",
                                    "plant" => "2",
                                    "trx_date" => date("Y-m-d H:i:s"),
                                    "pos_assy" => sprintf("%02s", $pos_assy),
                                    "temp_woc_id" => sprintf("%02s", $pos_assy) . "W/P".$wpp.$alfabets.$woc."-".$record['line_nick'],
                                    "status_dt" => "f",
                                    "set_wp" => "f",
                                    "wp" => $wpp.$alfabets.$filter_month,
                                    "qty" => empty($record["date_".$no]) ? 0 : $record["date_".$no],
                                    "wp_id" => $wpp.$alfabets,
                                );

                                $alfabet = $alfabets;
                                $serial_no++;
                            }else{
                                $wpp = sprintf("%02s", $wp);

                                if($alfabet == "z"){
                                    $alfabets = "A";
                                }elseif($alfabet == "A"){
                                    $alfabets = "B";
                                }elseif($alfabet == "B"){
                                    $alfabets = "C";
                                }elseif($alfabet == "C"){
                                    $alfabets = "D";
                                }elseif($alfabet == "D"){
                                    $alfabets = "E";
                                }elseif($alfabet == "E"){
                                    $alfabets = "F";
                                }elseif($alfabet == "F"){
                                    $alfabets = "G";
                                }elseif($alfabet == "G"){
                                    $alfabets = "H";
                                }elseif($alfabet == "H"){
                                    $alfabets = "I";
                                }elseif($alfabet == "I"){
                                    $alfabets = "J";
                                }elseif($alfabet == "J"){
                                    $alfabets = "K";
                                }elseif($alfabet == "K"){
                                    $alfabets = "L";
                                }elseif($alfabet == "L"){
                                    $alfabets = "M";
                                }elseif($alfabet == "M"){
                                    $alfabets = "N";
                                }elseif($alfabet == "N"){
                                    $alfabets = "O";
                                }else{  
                                    $alfabets = "";
                                }

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_mpp");
                                $this->dummy->where("periode", $filter_year . $filter_month);
                                $this->dummy->where("line", $record['line_no']);
                                $this->dummy->where("assy_no", $record['product_no']);
                                $this->dummy->where("wp", $wpp.$alfabets.$filter_month);
                                $wip_trx_mpp = $this->dummy->get()->row();

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_wds");
                                $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
                                $wip_trx_wds = $this->dummy->get()->result_array();

                                if(count($wip_trx_wds) == 0){
                                    if($wip_trx_mpp){
                                        $this->dummy->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp.$alfabets.$filter_month,
                                        ));

                                        $this->pg2->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp.$alfabets.$filter_month,
                                        ));
                                    }
                                }

                                $alfabet = $alfabets;
                            }
                        } else {
                            if($record["date_".$no] != "W" && $record["date_".$no] != "" && $record["date_".$no] != "0"){
                                $wpp = sprintf("%02s", $wp);

                                $rows[] = array(
                                    "serial_mpp" => "1".$serial.$record['line_no'].$serial_tgl."2".sprintf("%05s", $serial_no),
                                    "periode" => $filter_year . $filter_month,
                                    "assy_no" => $record['product_no'],
                                    "rev" => $filter_revision2,
                                    "color" => "2",
                                    "line" => $record['line_no'],
                                    "qty1" => "0",
                                    "rev1" => "0",
                                    "qty2" => "0",
                                    "rev2" => "0",
                                    "qty3" => "0",
                                    "rev3" => "0",
                                    "wp_date" => $working_date,
                                    "trx_user" => "1",
                                    "plant" => "2",
                                    "trx_date" => date("Y-m-d H:i:s"),
                                    "pos_assy" => sprintf("%02s", $pos_assy),
                                    "temp_woc_id" => sprintf("%02s", $pos_assy) . "W/P".$wpp.$woc."-".$record['line_nick'],
                                    "status_dt" => "f",
                                    "set_wp" => "f",
                                    "wp" => $wpp."/".$filter_month,
                                    "qty" => empty($record["date_".$no]) ? 0 : $record["date_".$no],
                                    "wp_id" => $wpp,
                                );

                                $alfabet = "z";
                                $serial_no++;
                                $wp++;
                            }else{
                                $wpp = sprintf("%02s", $wp);

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_mpp");
                                $this->dummy->where("periode", $filter_year . $filter_month);
                                $this->dummy->where("line", $record['line_no']);
                                $this->dummy->where("assy_no", $record['product_no']);
                                $this->dummy->where("wp", $wpp."/".$filter_month);
                                $wip_trx_mpp = $this->dummy->get()->row();

                                $this->dummy->select('*');
                                $this->dummy->from("wip_trx_wds");
                                $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
                                $wip_trx_wds = $this->dummy->get()->result_array();

                                if(count($wip_trx_wds) == 0){
                                    if($wip_trx_mpp){
                                        $this->dummy->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp."/".$filter_month,
                                        ));

                                        $this->pg2->delete('wip_trx_mpp', array(
                                            'periode' => $filter_year . $filter_month,
                                            'line' => $record['line_no'],
                                            'assy_no' => $record['product_no'],
                                            'wp' => $wpp."/".$filter_month,
                                        ));
                                    }
                                }
                                // $serial_no++;
                                $wp++;
                            }
                        }
                    } else {
                        if($record["date_".$no] != "W" && $record["date_".$no] != "" && $record["date_".$no] != "0"){
                            $wpp = sprintf("%02s", $wp);

                            if($alfabet == "z"){
                                $alfabets = "A";
                            }elseif($alfabet == "A"){
                                $alfabets = "B";
                            }elseif($alfabet == "B"){
                                $alfabets = "C";
                            }elseif($alfabet == "C"){
                                $alfabets = "D";
                            }elseif($alfabet == "D"){
                                $alfabets = "E";
                            }elseif($alfabet == "E"){
                                $alfabets = "F";
                            }elseif($alfabet == "F"){
                                $alfabets = "G";
                            }elseif($alfabet == "G"){
                                $alfabets = "H";
                            }elseif($alfabet == "H"){
                                $alfabets = "I";
                            }elseif($alfabet == "I"){
                                $alfabets = "J";
                            }elseif($alfabet == "J"){
                                $alfabets = "K";
                            }elseif($alfabet == "K"){
                                $alfabets = "L";
                            }elseif($alfabet == "L"){
                                $alfabets = "M";
                            }elseif($alfabet == "M"){
                                $alfabets = "N";
                            }elseif($alfabet == "N"){
                                $alfabets = "O";
                            }else{  
                                $alfabets = "";
                            }

                            $rows[] = array(
                                "serial_mpp" => "1".$serial.$record['line_no'].$serial_tgl."2".sprintf("%05s", $serial_no),
                                "periode" => $filter_year . $filter_month,
                                "assy_no" => $record['product_no'],
                                "rev" => $filter_revision2,
                                "color" => "2",
                                "line" => $record['line_no'],
                                "qty1" => "0",
                                "rev1" => "0",
                                "qty2" => "0",
                                "rev2" => "0",
                                "qty3" => "0",
                                "rev3" => "0",
                                "wp_date" => $working_date,
                                "trx_user" => "1",
                                "plant" => "2",
                                "trx_date" => date("Y-m-d H:i:s"),
                                "pos_assy" => sprintf("%02s", $pos_assy),
                                "temp_woc_id" => sprintf("%02s", $pos_assy) . "W/P".$wpp.$alfabets.$woc."-".$record['line_nick'],
                                "status_dt" => "f",
                                "set_wp" => "f",
                                "wp" => $wpp.$alfabets.$filter_month,
                                "qty" => empty($record["date_".$no]) ? 0 : $record["date_".$no],
                                "wp_id" => $wpp.$alfabets,
                            );

                            $alfabet = $alfabets;
                            $serial_no++;
                        }else{
                            $wpp = sprintf("%02s", $wp);

                            if($alfabet == "z"){
                                $alfabets = "A";
                            }elseif($alfabet == "A"){
                                $alfabets = "B";
                            }elseif($alfabet == "B"){
                                $alfabets = "C";
                            }elseif($alfabet == "C"){
                                $alfabets = "D";
                            }elseif($alfabet == "D"){
                                $alfabets = "E";
                            }elseif($alfabet == "E"){
                                $alfabets = "F";
                            }elseif($alfabet == "F"){
                                $alfabets = "G";
                            }elseif($alfabet == "G"){
                                $alfabets = "H";
                            }elseif($alfabet == "H"){
                                $alfabets = "I";
                            }elseif($alfabet == "I"){
                                $alfabets = "J";
                            }elseif($alfabet == "J"){
                                $alfabets = "K";
                            }elseif($alfabet == "K"){
                                $alfabets = "L";
                            }elseif($alfabet == "L"){
                                $alfabets = "M";
                            }elseif($alfabet == "M"){
                                $alfabets = "N";
                            }elseif($alfabet == "N"){
                                $alfabets = "O";
                            }else{  
                                $alfabets = "";
                            }

                            $this->dummy->select('*');
                            $this->dummy->from("wip_trx_mpp");
                            $this->dummy->where("periode", $filter_year . $filter_month);
                            $this->dummy->where("line", $record['line_no']);
                            $this->dummy->where("assy_no", $record['product_no']);
                            $this->dummy->where("wp", $wpp.$alfabets.$filter_month);
                            $wip_trx_mpp = $this->dummy->get()->row();

                            $this->dummy->select('*');
                            $this->dummy->from("wip_trx_wds");
                            $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
                            $wip_trx_wds = $this->dummy->get()->result_array();

                            if(count($wip_trx_wds) == 0){
                                if($wip_trx_mpp){
                                    $this->dummy->delete('wip_trx_mpp', array(
                                        'periode' => $filter_year . $filter_month,
                                        'line' => $record['line_no'],
                                        'assy_no' => $record['product_no'],
                                        'wp' => $wpp.$alfabets.$filter_month,
                                    ));

                                    $this->pg2->delete('wip_trx_mpp', array(
                                        'periode' => $filter_year . $filter_month,
                                        'line' => $record['line_no'],
                                        'assy_no' => $record['product_no'],
                                        'wp' => $wpp.$alfabets.$filter_month,
                                    ));
                                }
                            }

                            $alfabet = $alfabets;
                        }
                    }

                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $no++;
                }

                $last_assy_no = $assy_no;
                $last_line_no = $line_no;
                $pos_assy++;
            }

            //Nanti ini buat delete per assy jika tidak ada di wip_trx_mpp
            // $this->dummy->where("periode", $periode);
            // $this->dummy->where("line", $filter_line_no);
            // $this->dummy->where_not_in('assy_no', $excluded_ids);
            // $this->dummy->delete('wip_trx_mpp');

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_mpp_generate(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();
        $filter_revision = $revisions->revision;

        $this->db->select('a.*');
        $this->db->from('(SELECT * FROM generate_mpp order by prod_plan desc) a');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.line_no');
        $this->db->order_by('a.line_no', 'ASC');
        $totalRows = $this->db->count_all_results('', false);

        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $this->dummy = $this->load->database('dummy', TRUE);
            $periode = $filter_year . $filter_month;

            foreach ($records as $record) {
                $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));
                $line = $record['line_no'];

                $no = 1;
                $hk = 0;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    if($record["date_".$no] != "W"){
                        if($record["date_".$no] != 0){
                            $hk += 1;
                        }else{
                            $hk += 0;
                        }
                    }else{
                        $hk += 0;
                    }

                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $no++;
                }

                $planConfig = $this->api->read('planning_config');
                $doc_mpp = date('Ym', strtotime($filter_year . "-" . $filter_month ."-". $planConfig->wp_day_from));
                $wp_dt_fr = date('Y-m-d', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month ."-". $planConfig->wp_day_from)));
                $wp_dt_to = date('Y-m-d', strtotime($filter_year . "-" . $filter_month ."-". $planConfig->wp_day_to));

                $rows[] = array(
                    "doc_mpp" => $doc_mpp . $record['line_no'],
                    "rev" => $filter_revision2,
                    "period" => $filter_year . $filter_month,
                    "line" => $record['line_no'],
                    "wp_dt_fr" => $wp_dt_fr,
                    "wp_dt_to" => $wp_dt_to,
                    "total_wp" => $hk,
                    "user_update" => $this->session->name,
                    "time_update" => date("Y-m-d H:i:s"),
                    "mpp_notes" => "",
                );
            }

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_plan_schedule(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $this->db->select('product_no, SUM(date_1 + date_2 + date_3 + date_4 + date_5 + date_6 + date_7 + date_8 + date_9 + date_10 + 
            date_11 + date_12 + date_13 + date_14 + date_15 + date_16 + date_17 + date_18 + date_19 + date_20 +
            date_21 + date_22 + date_23 + date_24 + date_25 + date_26 + date_27 + date_28 + date_29 + date_30 + date_31) as qty');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $filter_revision);
        $this->db->like('line_no', $filter_line_no);
        $this->db->like('product_no', $filter_product_no);
        $this->db->group_by('product_no');
        $this->db->order_by('product_no', 'ASC');

        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $this->dummy = $this->load->database('dummy', TRUE);
            $periode = $filter_year . $filter_month;

            foreach ($records as $record) {
                $product_no = $record['product_no'];

                $this->dummy->query("DELETE FROM plansys_schedule_detail where period = '$periode' and assy_no = '$product_no'");
                $this->dummy->query("DELETE FROM plansys_schedule where period = '$periode' and assy_no = '$product_no'");

                $rows[] = array(
                    "period" => $periode,
                    "date_period" => $filter_year . "-" . $filter_month . "-01",
                    "assy_no" => $record['product_no'],
                    "total_qty" => $record['qty'],
                    "input_user" => $this->session->name,
                    "input_time" => date("Y-m-d H:i:s")
                );
            }

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_plan_schedule_detail(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $this->db->select('a.*');
        $this->db->from('generate_mpp a');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.product_no');
        $this->db->order_by('a.product_no', 'ASC');
        $totalRows = $this->db->count_all_results('', false);

        $records = $this->db->get()->result_array();

        if(count($records) > 0){
            $periode = $filter_year . $filter_month;
            foreach ($records as $record) {
                $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
                $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

                $no = 1;
                while (strtotime($firstDate) <= strtotime($endDate)) {
                    $qty = $record["date_".$no];
                    $date_schedule = date('Y-m-d', strtotime($firstDate));

                    if($record["date_".$no] != "W"){
                        if($qty > 0){
                            $rows[] = array(
                                "period" => $periode,
                                "date_period" => $filter_year . "-" . $filter_month . "-01",
                                "assy_no" => $record['product_no'],
                                "date_schedule" => $date_schedule,
                                "qty" => $qty,
                            );
                        }
                    }

                    // }else{

                    //     $rows[] = array(

                    //         "period" => $periode,

                    //         "date_period" => $filter_year . "-" . $filter_month . "-01",

                    //         "assy_no" => $record['product_no'],

                    //         "date_schedule" => $date_schedule,

                    //         "qty" => "0",

                    //     );

                    // }

                    $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));
                    $no++;
                }
            }

            $rows['total'] = count($rows);
            die(json_encode($rows));
        }else{
            echo json_encode(array("title" => "Not Ready", "message" => "Data MPP Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_create(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            // $this->pg2 = $this->load->database('pg2', TRUE);

            $post = $this->input->post('data');

            // $this->dummy->select('*');
            // $this->dummy->from("mst_item");
            // $this->dummy->where("item_id", $post['assy_no']);
            // $mst_item = $this->dummy->get()->result_array();

            $this->dummy->select('*');
            $this->dummy->from("wip_trx_mpp");
            $this->dummy->where("periode", $post['periode']);
            $this->dummy->where("line", $post['line']);
            $this->dummy->where("assy_no", $post['assy_no']);
            $this->dummy->where("wp", $post['wp']);
            $wip_trx_mpp = $this->dummy->get()->row();

            $this->dummy->select('*');
            $this->dummy->from("wip_trx_wds");
            $this->dummy->where("serial_mpp", @$wip_trx_mpp->serial_mpp);
            $wip_trx_wds = $this->dummy->get()->result_array();

            //if(count($mst_item) > 0){
            if(count($wip_trx_wds) == 0){
                if($wip_trx_mpp){
                    $this->dummy->where("periode", $post['periode']);
                    $this->dummy->where("line", $post['line']);
                    $this->dummy->where("assy_no", $post['assy_no']);
                    $this->dummy->where("wp", $post['wp']);
                    if ($this->dummy->update('wip_trx_mpp', ["qty" => $post['qty'], "rev" => $post['rev']])) {
                        echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | Data Update Successfully", "theme" => "success"));

                        // $this->pg2->where("periode", $post['periode']);
                        // $this->pg2->where("line", $post['line']);
                        // $this->pg2->where("assy_no", $post['assy_no']);
                        // $this->pg2->where("wp", $post['wp']);
                        // $this->pg2->update('wip_trx_mpp', ["qty" => $post['qty'], "rev" => $post['rev']]);
                    } else {
                        echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Data Unsaved", "theme" => "error"));
                    }
                }else{
                    if ($this->dummy->insert('wip_trx_mpp', $post)) {
                        // $this->api->create('generate_mpp_detail', $post, "MPPD", "MPPD");
                        echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | Data Saved Successfully", "theme" => "success"));
                        // $this->pg2->insert('wip_trx_mpp', $post);
                    } else {
                        echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Data Unsaved", "theme" => "error"));
                    }
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => $post['serial_mpp'] . " | Already in WDS", "theme" => "error"));
            }
            // }else{

            //     echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Not Found in mst_item", "theme" => "error"));

            // }
        }
    }

    public function push_data_create_mpp_generate(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mpp_generate");
            $this->dummy->where("doc_mpp", $post['doc_mpp']);
            $mpp_generate = $this->dummy->get()->result_array();

            if(count($mpp_generate) <= 0){
                if ($this->dummy->insert('mpp_generate', $post)) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['doc_mpp'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['doc_mpp'] . " | Data Unsaved", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Duplicate", "message" => $post['doc_mpp'] . " | Duplicate Doc MPP in table mpp_generate", "theme" => "error"));
            }
        }
    }

    public function push_data_create_plan_schedule(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", $post['assy_no']);
            $mst_item = $this->dummy->get()->result_array();

            if(count($mst_item) > 0){
                if ($this->dummy->insert('plansys_schedule', $post)) {
                    echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Data Unsaved", "theme" => "error"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | Not Found in mst_item", "theme" => "error"));
            }
        }
    }

    public function push_data_create_plan_schedule_detail(){
        if ($this->input->post()) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $post = $this->input->post('data');

            $this->dummy->select('*');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", $post['assy_no']);
            $mst_item = $this->dummy->get()->result_array();

            $this->dummy->select('*');
            $this->dummy->from("plansys_schedule_detail");
            $this->dummy->where("period", $post['period']);
            $this->dummy->where("assy_no", $post['assy_no']);
            $this->dummy->where("date_schedule", $post['date_schedule']);
            $plansys_schedule_detail = $this->dummy->get()->result_array();

            if(count($mst_item) > 0){
                if(count($plansys_schedule_detail) > 0){
                    echo json_encode(array("title" => "Duplicate", "message" => $post['assy_no'] . " | ".$post['period']." | Duplicate in table plansys_schedule_detail", "theme" => "error"));
                }else{
                    if ($this->dummy->insert('plansys_schedule_detail', $post)) {
                        echo json_encode(array("title" => "Good Job", "message" => $post['assy_no'] . " | ".$post['date_schedule']." | Success", "theme" => "success"));
                    } else {
                        echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | ".$post['date_schedule']." | Unsaved", "theme" => "error"));
                    }
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => $post['assy_no'] . " | ".$post['date_schedule']." | Not Found", "theme" => "error"));
            }
        }
    }



    public function create()
    {
        if ($this->input->post('data')) {
            $post = $this->input->post('data');
            $read = $this->api->read("generate_mpp", [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => $post['product_no']
            ]);

            if ($read) {
                $send = $this->api->update('generate_mpp', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "product_no" => $post['product_no']
                ], $post);
            } else {
                $send = $this->api->create('generate_mpp', $post, "MPP", "MPP");
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->api->update('generate_mpp', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/generate_mpp.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/generate_mpp.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/generate_mpp.txt";

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
            header("Content-Disposition: attachment; filename=generate_mpp_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision2 = base64_decode($this->input->get('filter_revision'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        $this->db->select('revision');
        $this->db->from('generate_mpp');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $revisions = $this->db->get()->row();

        $filter_revision = $revisions->revision;

        $firstDate = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));
        $endDate = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        //MPS
        $this->db->select("a.line_no, b.remarks");
        $this->db->from('generate_mpp a');
        $this->db->join('mst_line b', 'a.line_no = b.line_no');
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where('a.revision', $filter_revision);
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->group_by('a.line_no');
        $records = $this->db->get()->result_array();

        //Total UMH CUtting
        $qTotalCutting = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity
            FROM `generate_loadcap_line` 
            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 
            and process_sub_number IN ('CUT100','CUT200','CUT300','CUT400','CUT500')");

        //Total UMH Crimping
        $qTotalCrimping = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity
            FROM `generate_loadcap_line` 
            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 
            and process_sub_number IN ('MCR000')");

        //Total UMH Joint
        $qTotalJoint = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('JNT000')");



        //Total UMH Joint

        $qTotalSemiAutoCutting = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('SAC000')");



        //Total UMH Stripping

        $qTotalStripping = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('STR000')");



        //Total UMH HF SEALER

        $qTotalHfSealer = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('HFS000')");



        //Total UMH Joint Tapping

        $qTotalJointTapping = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('JTP000')");



        //Total UMH Welding Joint

        $qTotalWeldingJoint = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('WJT000')");



        //Total UMH Spot Welding

        $qTotalSpotWelding = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('SWL000')");



        //Total UMH Solder

        $qTotalSolder = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('SLD000')");



        //Total UMH JST

        $qTotalJst = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('JST000')");



        //Total Twist

        $qTotalTwist = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('TWS000')");



        //Total UMH Hot Stamp

        $qTotalHotStamp = $this->api->query("SELECT SUM(month_1) as total, SUM(capacity_1) as total_capacity

            FROM `generate_loadcap_line` 

            WHERE p_month = '$filter_month' and p_year = '$filter_year' and revision = '$filter_revision' 

            and process_sub_number IN ('HST000')");



        $hkw = 0;

        $ltppMonth = $filter_year . "-" . $filter_month . "-01";

        $monthStart = strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01"));

        $start = strtotime(date('Y-m-01', $monthStart));

        $finish = strtotime(date('Y-m-t', $monthStart));

        for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {

            $working_date = date('Y-m-d', $z);



            $this->db->select('remarks');

            $this->db->from('working_calendar');

            $this->db->where('working_date', $working_date);

            $holiday = $this->db->get()->row();



            if (date('w', $z) !== '0') {

                if (@$holiday->remarks != null or @$holiday->remarks != "") {

                    $hkw += 0;

                } else {

                    $hkw += 1;

                }

            } else {

                $hkw += 0;

            }

        }



        //Setting Header

        $styles = "";

        $wp = '<tr><th width="50" style="text-align:center;"></th>';

        $wp_content = "";

        $cut = '<tr><th width="50" style="text-align:center;">CUT</th>';

        $cut_no = 0;

        $cut_content = "";

        $joint = '<tr><th width="50" style="text-align:center;">JOINT</th>';

        $joint_no = 0;

        $joint_hkw = ($hkw + 1);

        $joint_content = "";

        $joint_day = 2;

        $wc = '<tr><th width="50" style="text-align:center;">W/C</th>';

        $wc_no = 0;

        $wc_hkw = ($hkw - 2);

        $wc_content = "";

        $wc_day = 3;

        $hav = '<tr><th width="50" style="text-align:center;">HAV</th>';

        $hav_no = 0;

        $hav_hkw = ($hkw - 3);

        $hav_content = "";

        $hav_day = 4;

        $cp = '<tr><th width="50" style="text-align:center;">CP</th>';

        $cp_no = 0;

        $cp_hkw = ($hkw - 4);

        $cp_content = "";

        $cp_day = 5;

        $fg = '<tr><th width="50" style="text-align:center;">F/G</th>';

        $fg_no = 0;

        $fg_hkw = ($hkw - 5);

        $fg_content = "";

        $fg_day = 6;

        $qty = '<tr><th width="50" style="text-align:center;">PRODPLAN</th>';



        while (strtotime($firstDate) <= strtotime($endDate)) {

            $working_date = date('Y-m-d', strtotime($firstDate));



            $this->db->select('remarks');

            $this->db->from('working_calendar');

            $this->db->where('working_date', $working_date);

            $holiday = $this->db->get()->row();



            if (date('w', strtotime($firstDate)) !== '0') {

                if (@$holiday->remarks != null or @$holiday->remarks != "") {

                    $styles = 'background:#FFC2C2;';

                    $wp_content = "";

                    $cut_no += 0;

                    $cut_content = "A";

                    $joint_no += 0;

                    $joint_hkw += 0;

                    $joint_content = "A";

                    $joint_day += 1;

                    $wc_no += 0;

                    $wc_hkw += 0;

                    $wc_content = "A";

                    $wc_day += 1;

                    $hav_no += 0;

                    $hav_hkw += 0;

                    $hav_content = "A";

                    $hav_day += 1;

                    $cp_no += 0;

                    $cp_hkw += 0;

                    $cp_content = "A";

                    $cp_day += 1;

                    $fg_no += 0;

                    $fg_hkw += 0;

                    $fg_content = "A";

                    $fg_day += 1;

                } else {

                    $styles = "";

                    $wp_content = "W/P";

                    $cut_no += 1;

                    $cut_content = $cut_no;



                    if (strtotime($firstDate) >= strtotime(date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-" . $joint_day)))) {

                        $joint_no += 1;

                    } else {

                        $joint_no += 0;

                    }



                    if($joint_no == 0){

                        $joint_hkw -= 1;

                    }else{

                        $joint_hkw = $joint_no;

                    }



                    $joint_content = $joint_hkw;



                    if (strtotime($firstDate) >= strtotime(date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-" . $wc_day)))) {

                        $wc_no += 1;

                    } else {

                        $wc_no += 0;

                    }



                    if($wc_no == 0){

                        $wc_hkw += 1;

                    }else{

                        $wc_hkw = $wc_no;

                    }



                    $wc_content = $wc_hkw;



                    if (strtotime($firstDate) >= strtotime(date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-" . $hav_day)))) {

                        $hav_no += 1;

                    } else {

                        $hav_no += 0;

                    }



                    if($hav_no == 0){

                        $hav_hkw += 1;

                    }else{

                        $hav_hkw = $hav_no;

                    }

                    

                    $hav_content = $hav_hkw;



                    if (strtotime($firstDate) >= strtotime(date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-" . $cp_day)))) {

                        $cp_no += 1;

                    } else {

                        $cp_no += 0;

                    }



                    if($cp_no == 0){

                        $cp_hkw += 1;

                    }else{

                        $cp_hkw = $cp_no;

                    }



                    $cp_content = $cp_hkw;



                    if (strtotime($firstDate) >= strtotime(date('Y-m-d', strtotime($filter_year . "-" . $filter_month . "-" . $fg_day)))) {

                        $fg_no += 1;

                    } else {

                        $fg_no += 0;

                    }



                    if($fg_no == 0){

                        $fg_hkw += 1;

                    }else{

                        $fg_hkw = $fg_no;

                    }



                    $fg_content = $fg_hkw;

                }

            } else {

                $styles = 'background:#FFC2C2;';



                if(date('w', strtotime($working_date)) === '0'){

                    $wp_week = "B";

                }



                $wp_content = "";

                $cut_no += 0;

                $cut_content = $wp_week;

                $joint_no += 0;

                $joint_content = $wp_week;

                $joint_day += 1;

                $wc_no += 0;

                $wc_content = $wp_week;

                $wc_day += 1;

                $hav_no += 0;

                $hav_content = $wp_week;

                $hav_day += 1;

                $cp_no += 0;

                $cp_content = $wp_week;

                $cp_day += 1;

                $fg_no += 0;

                $fg_content = $wp_week;

                $fg_day += 1;

            }



            //Setting Header

            $wp .= '<th width="30" style="text-align:center; ' . $styles . '">' . $wp_content . '</th>';

            $cut .= '<th width="30" style="text-align:center; ' . $styles . '">' . $cut_content . '</th>';

            $joint .= '<th width="30" style="text-align:center; ' . $styles . '">' . $joint_content . '</th>';

            $wc .= '<th width="30" style="text-align:center; ' . $styles . '">' . $wc_content . '</th>';

            $hav .= '<th width="30" style="text-align:center; ' . $styles . '">' . $hav_content . '</th>';

            $cp .= '<th width="30" style="text-align:center; ' . $styles . '">' . $cp_content . '</th>';

            $fg .= '<th width="30" style="text-align:center; ' . $styles . '">' . $fg_content . '</th>';

            $qty .= '<th width="30" style="text-align:center; ' . $styles . '">' . $firstDate . '</th>';



            $firstDate = date("Y-m-d", strtotime("+1 day", strtotime($firstDate)));

        }



        //Setting Header

        $wp .= '</tr>';

        $cut .= '</tr>';

        $joint .= '</tr>';

        $wc .= '</tr>';

        $hav .= '</tr>';

        $cp .= '</tr>';

        $fg .= '</tr>';

        $qty .= '</tr>';



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

        <small>GENERATE MAN POWER SCHEDULE</small>

        </td>

        </tr>

        </table>

        </div>

        <div style="float: right; font-size: 12px; text-align: right;">

        Print Date ' . date("Y-m-d Y H:m:s") . ' <br>

        Print By ' . $this->session->username . '  

        </div>

        </center>

        <br>

        <table id="customers" border="1">

        <tr>

        <th style="text-align:center;">DISETUJUI</th>

        <th style="text-align:center;">DIKETAHUI</th>

        <th style="text-align:center;">DIPERIKSA</th>

        <th style="text-align:center;">DIBUAT</th>

        </tr>

        <tr>

        <th style="height:70px;"></th>

        <th style="height:70px;"></th>

        <th style="height:70px;"></th>

        <th style="height:70px;"></th>

        </tr>

        <tr>

        <th style="text-align:center; height:20px;"></th>

        <th style="text-align:center; height:20px;"></th>

        <th style="text-align:center; height:20px;"></th>

        <th style="text-align:center; height:20px;">'.$this->session->name.'</th>

        </tr>

        </table>

        <br>

        <table id="customers" border="1">

        <tr>

        <th style="text-align:center;" rowspan="9" width="50">NO</th>

        <th style="text-align:center;" width="200" rowspan="9">PRODUCT NO</th>

        <th style="text-align:center;" width="200" rowspan="9">PRODUCT ALIAS</th>

        <th style="text-align:center;" width="150" rowspan="9">PRODUCT NAME</th>

        <th style="text-align:center;" width="80" rowspan="9">CUT</th>

        <th style="text-align:center;" width="80" rowspan="9">CRP</th>

        <th style="text-align:center;" width="80" rowspan="9">JNT</th>

        <th style="text-align:center;" width="80" rowspan="9">SAC</th>

        <th style="text-align:center;" width="80" rowspan="9">STR</th>

        <th style="text-align:center;" width="80" rowspan="9">HFS</th>

        <th style="text-align:center;" width="80" rowspan="9">JTP</th>

        <th style="text-align:center;" width="80" rowspan="9">WJT</th>

        <th style="text-align:center;" width="80" rowspan="9">SWL</th>

        <th style="text-align:center;" width="80" rowspan="9">SLD</th>

        <th style="text-align:center;" width="80" rowspan="9">JST</th>

        <th style="text-align:center;" width="80" rowspan="9">TWS</th>

        <th style="text-align:center;" width="80" rowspan="9">HST</th>

        </tr>' . $wp . $cut . $joint . $wc . $hav . $cp . $fg . $qty;

        $no = 1;



        $total_mpp_prodplan = 0;



        $total_cct_prodplan = 0;

        $total_cct_prodplan_crp = 0;

        $total_cct_prodplan_jnt = 0;

        $total_cct_prodplan_sac = 0;

        $total_cct_prodplan_str = 0;

        $total_cct_prodplan_hfs = 0;

        $total_cct_prodplan_jtp = 0;

        $total_cct_prodplan_wjt = 0;

        $total_cct_prodplan_swl = 0;

        $total_cct_prodplan_sld = 0;

        $total_cct_prodplan_jst = 0;

        $total_cct_prodplan_tws = 0;

        $total_cct_prodplan_hst = 0;



        $total_cct_cutting = 0;

        $total_cct_crimping = 0;

        $total_cct_joint = 0;

        $total_cct_sac = 0;

        $total_cct_str = 0;

        $total_cct_hfs = 0;

        $total_cct_jtp = 0;

        $total_cct_wjt = 0;

        $total_cct_swl = 0;

        $total_cct_sld = 0;

        $total_cct_jst = 0;

        $total_cct_tws = 0;

        $total_cct_hst = 0;



        //Total MPP

        $total_mpp_date_1 = 0;

        $total_mpp_date_2 = 0;

        $total_mpp_date_3 = 0;

        $total_mpp_date_4 = 0;

        $total_mpp_date_5 = 0;

        $total_mpp_date_6 = 0;

        $total_mpp_date_7 = 0;

        $total_mpp_date_8 = 0;

        $total_mpp_date_9 = 0;

        $total_mpp_date_10 = 0;

        $total_mpp_date_11 = 0;

        $total_mpp_date_12 = 0;

        $total_mpp_date_13 = 0;

        $total_mpp_date_14 = 0;

        $total_mpp_date_15 = 0;

        $total_mpp_date_16 = 0;

        $total_mpp_date_17 = 0;

        $total_mpp_date_18 = 0;

        $total_mpp_date_19 = 0;

        $total_mpp_date_20 = 0;

        $total_mpp_date_21 = 0;

        $total_mpp_date_22 = 0;

        $total_mpp_date_23 = 0;

        $total_mpp_date_24 = 0;

        $total_mpp_date_25 = 0;

        $total_mpp_date_26 = 0;

        $total_mpp_date_27 = 0;

        $total_mpp_date_28 = 0;

        $total_mpp_date_29 = 0;

        $total_mpp_date_30 = 0;

        $total_mpp_date_31 = 0;



        //Total Cutting

        $total_cutting_date_1 = 0;

        $total_cutting_date_2 = 0;

        $total_cutting_date_3 = 0;

        $total_cutting_date_4 = 0;

        $total_cutting_date_5 = 0;

        $total_cutting_date_6 = 0;

        $total_cutting_date_7 = 0;

        $total_cutting_date_8 = 0;

        $total_cutting_date_9 = 0;

        $total_cutting_date_10 = 0;

        $total_cutting_date_11 = 0;

        $total_cutting_date_12 = 0;

        $total_cutting_date_13 = 0;

        $total_cutting_date_14 = 0;

        $total_cutting_date_15 = 0;

        $total_cutting_date_16 = 0;

        $total_cutting_date_17 = 0;

        $total_cutting_date_18 = 0;

        $total_cutting_date_19 = 0;

        $total_cutting_date_20 = 0;

        $total_cutting_date_21 = 0;

        $total_cutting_date_22 = 0;

        $total_cutting_date_23 = 0;

        $total_cutting_date_24 = 0;

        $total_cutting_date_25 = 0;

        $total_cutting_date_26 = 0;

        $total_cutting_date_27 = 0;

        $total_cutting_date_28 = 0;

        $total_cutting_date_29 = 0;

        $total_cutting_date_30 = 0;

        $total_cutting_date_31 = 0;



        //Total Crimping

        $total_crimping_date_1 = 0;

        $total_crimping_date_2 = 0;

        $total_crimping_date_3 = 0;

        $total_crimping_date_4 = 0;

        $total_crimping_date_5 = 0;

        $total_crimping_date_6 = 0;

        $total_crimping_date_7 = 0;

        $total_crimping_date_8 = 0;

        $total_crimping_date_9 = 0;

        $total_crimping_date_10 = 0;

        $total_crimping_date_11 = 0;

        $total_crimping_date_12 = 0;

        $total_crimping_date_13 = 0;

        $total_crimping_date_14 = 0;

        $total_crimping_date_15 = 0;

        $total_crimping_date_16 = 0;

        $total_crimping_date_17 = 0;

        $total_crimping_date_18 = 0;

        $total_crimping_date_19 = 0;

        $total_crimping_date_20 = 0;

        $total_crimping_date_21 = 0;

        $total_crimping_date_22 = 0;

        $total_crimping_date_23 = 0;

        $total_crimping_date_24 = 0;

        $total_crimping_date_25 = 0;

        $total_crimping_date_26 = 0;

        $total_crimping_date_27 = 0;

        $total_crimping_date_28 = 0;

        $total_crimping_date_29 = 0;

        $total_crimping_date_30 = 0;

        $total_crimping_date_31 = 0;



        //Total Joint

        $total_joint_date_1 = 0;

        $total_joint_date_2 = 0;

        $total_joint_date_3 = 0;

        $total_joint_date_4 = 0;

        $total_joint_date_5 = 0;

        $total_joint_date_6 = 0;

        $total_joint_date_7 = 0;

        $total_joint_date_8 = 0;

        $total_joint_date_9 = 0;

        $total_joint_date_10 = 0;

        $total_joint_date_11 = 0;

        $total_joint_date_12 = 0;

        $total_joint_date_13 = 0;

        $total_joint_date_14 = 0;

        $total_joint_date_15 = 0;

        $total_joint_date_16 = 0;

        $total_joint_date_17 = 0;

        $total_joint_date_18 = 0;

        $total_joint_date_19 = 0;

        $total_joint_date_20 = 0;

        $total_joint_date_21 = 0;

        $total_joint_date_22 = 0;

        $total_joint_date_23 = 0;

        $total_joint_date_24 = 0;

        $total_joint_date_25 = 0;

        $total_joint_date_26 = 0;

        $total_joint_date_27 = 0;

        $total_joint_date_28 = 0;

        $total_joint_date_29 = 0;

        $total_joint_date_30 = 0;

        $total_joint_date_31 = 0;



        //Total Semi Auto Cutting

        $total_sac_date_1 = 0;

        $total_sac_date_2 = 0;

        $total_sac_date_3 = 0;

        $total_sac_date_4 = 0;

        $total_sac_date_5 = 0;

        $total_sac_date_6 = 0;

        $total_sac_date_7 = 0;

        $total_sac_date_8 = 0;

        $total_sac_date_9 = 0;

        $total_sac_date_10 = 0;

        $total_sac_date_11 = 0;

        $total_sac_date_12 = 0;

        $total_sac_date_13 = 0;

        $total_sac_date_14 = 0;

        $total_sac_date_15 = 0;

        $total_sac_date_16 = 0;

        $total_sac_date_17 = 0;

        $total_sac_date_18 = 0;

        $total_sac_date_19 = 0;

        $total_sac_date_20 = 0;

        $total_sac_date_21 = 0;

        $total_sac_date_22 = 0;

        $total_sac_date_23 = 0;

        $total_sac_date_24 = 0;

        $total_sac_date_25 = 0;

        $total_sac_date_26 = 0;

        $total_sac_date_27 = 0;

        $total_sac_date_28 = 0;

        $total_sac_date_29 = 0;

        $total_sac_date_30 = 0;

        $total_sac_date_31 = 0;



        //Total Stripping

        $total_str_date_1 = 0;

        $total_str_date_2 = 0;

        $total_str_date_3 = 0;

        $total_str_date_4 = 0;

        $total_str_date_5 = 0;

        $total_str_date_6 = 0;

        $total_str_date_7 = 0;

        $total_str_date_8 = 0;

        $total_str_date_9 = 0;

        $total_str_date_10 = 0;

        $total_str_date_11 = 0;

        $total_str_date_12 = 0;

        $total_str_date_13 = 0;

        $total_str_date_14 = 0;

        $total_str_date_15 = 0;

        $total_str_date_16 = 0;

        $total_str_date_17 = 0;

        $total_str_date_18 = 0;

        $total_str_date_19 = 0;

        $total_str_date_20 = 0;

        $total_str_date_21 = 0;

        $total_str_date_22 = 0;

        $total_str_date_23 = 0;

        $total_str_date_24 = 0;

        $total_str_date_25 = 0;

        $total_str_date_26 = 0;

        $total_str_date_27 = 0;

        $total_str_date_28 = 0;

        $total_str_date_29 = 0;

        $total_str_date_30 = 0;

        $total_str_date_31 = 0;



        //Total HF Sealer

        $total_hfs_date_1 = 0;

        $total_hfs_date_2 = 0;

        $total_hfs_date_3 = 0;

        $total_hfs_date_4 = 0;

        $total_hfs_date_5 = 0;

        $total_hfs_date_6 = 0;

        $total_hfs_date_7 = 0;

        $total_hfs_date_8 = 0;

        $total_hfs_date_9 = 0;

        $total_hfs_date_10 = 0;

        $total_hfs_date_11 = 0;

        $total_hfs_date_12 = 0;

        $total_hfs_date_13 = 0;

        $total_hfs_date_14 = 0;

        $total_hfs_date_15 = 0;

        $total_hfs_date_16 = 0;

        $total_hfs_date_17 = 0;

        $total_hfs_date_18 = 0;

        $total_hfs_date_19 = 0;

        $total_hfs_date_20 = 0;

        $total_hfs_date_21 = 0;

        $total_hfs_date_22 = 0;

        $total_hfs_date_23 = 0;

        $total_hfs_date_24 = 0;

        $total_hfs_date_25 = 0;

        $total_hfs_date_26 = 0;

        $total_hfs_date_27 = 0;

        $total_hfs_date_28 = 0;

        $total_hfs_date_29 = 0;

        $total_hfs_date_30 = 0;

        $total_hfs_date_31 = 0;



        //Total Joint Tapping

        $total_jtp_date_1 = 0;

        $total_jtp_date_2 = 0;

        $total_jtp_date_3 = 0;

        $total_jtp_date_4 = 0;

        $total_jtp_date_5 = 0;

        $total_jtp_date_6 = 0;

        $total_jtp_date_7 = 0;

        $total_jtp_date_8 = 0;

        $total_jtp_date_9 = 0;

        $total_jtp_date_10 = 0;

        $total_jtp_date_11 = 0;

        $total_jtp_date_12 = 0;

        $total_jtp_date_13 = 0;

        $total_jtp_date_14 = 0;

        $total_jtp_date_15 = 0;

        $total_jtp_date_16 = 0;

        $total_jtp_date_17 = 0;

        $total_jtp_date_18 = 0;

        $total_jtp_date_19 = 0;

        $total_jtp_date_20 = 0;

        $total_jtp_date_21 = 0;

        $total_jtp_date_22 = 0;

        $total_jtp_date_23 = 0;

        $total_jtp_date_24 = 0;

        $total_jtp_date_25 = 0;

        $total_jtp_date_26 = 0;

        $total_jtp_date_27 = 0;

        $total_jtp_date_28 = 0;

        $total_jtp_date_29 = 0;

        $total_jtp_date_30 = 0;

        $total_jtp_date_31 = 0;



        //Total Welding Joint

        $total_wjt_date_1 = 0;

        $total_wjt_date_2 = 0;

        $total_wjt_date_3 = 0;

        $total_wjt_date_4 = 0;

        $total_wjt_date_5 = 0;

        $total_wjt_date_6 = 0;

        $total_wjt_date_7 = 0;

        $total_wjt_date_8 = 0;

        $total_wjt_date_9 = 0;

        $total_wjt_date_10 = 0;

        $total_wjt_date_11 = 0;

        $total_wjt_date_12 = 0;

        $total_wjt_date_13 = 0;

        $total_wjt_date_14 = 0;

        $total_wjt_date_15 = 0;

        $total_wjt_date_16 = 0;

        $total_wjt_date_17 = 0;

        $total_wjt_date_18 = 0;

        $total_wjt_date_19 = 0;

        $total_wjt_date_20 = 0;

        $total_wjt_date_21 = 0;

        $total_wjt_date_22 = 0;

        $total_wjt_date_23 = 0;

        $total_wjt_date_24 = 0;

        $total_wjt_date_25 = 0;

        $total_wjt_date_26 = 0;

        $total_wjt_date_27 = 0;

        $total_wjt_date_28 = 0;

        $total_wjt_date_29 = 0;

        $total_wjt_date_30 = 0;

        $total_wjt_date_31 = 0;



        //Total Spot Welding

        $total_swl_date_1 = 0;

        $total_swl_date_2 = 0;

        $total_swl_date_3 = 0;

        $total_swl_date_4 = 0;

        $total_swl_date_5 = 0;

        $total_swl_date_6 = 0;

        $total_swl_date_7 = 0;

        $total_swl_date_8 = 0;

        $total_swl_date_9 = 0;

        $total_swl_date_10 = 0;

        $total_swl_date_11 = 0;

        $total_swl_date_12 = 0;

        $total_swl_date_13 = 0;

        $total_swl_date_14 = 0;

        $total_swl_date_15 = 0;

        $total_swl_date_16 = 0;

        $total_swl_date_17 = 0;

        $total_swl_date_18 = 0;

        $total_swl_date_19 = 0;

        $total_swl_date_20 = 0;

        $total_swl_date_21 = 0;

        $total_swl_date_22 = 0;

        $total_swl_date_23 = 0;

        $total_swl_date_24 = 0;

        $total_swl_date_25 = 0;

        $total_swl_date_26 = 0;

        $total_swl_date_27 = 0;

        $total_swl_date_28 = 0;

        $total_swl_date_29 = 0;

        $total_swl_date_30 = 0;

        $total_swl_date_31 = 0;



        //Total Solder

        $total_sld_date_1 = 0;

        $total_sld_date_2 = 0;

        $total_sld_date_3 = 0;

        $total_sld_date_4 = 0;

        $total_sld_date_5 = 0;

        $total_sld_date_6 = 0;

        $total_sld_date_7 = 0;

        $total_sld_date_8 = 0;

        $total_sld_date_9 = 0;

        $total_sld_date_10 = 0;

        $total_sld_date_11 = 0;

        $total_sld_date_12 = 0;

        $total_sld_date_13 = 0;

        $total_sld_date_14 = 0;

        $total_sld_date_15 = 0;

        $total_sld_date_16 = 0;

        $total_sld_date_17 = 0;

        $total_sld_date_18 = 0;

        $total_sld_date_19 = 0;

        $total_sld_date_20 = 0;

        $total_sld_date_21 = 0;

        $total_sld_date_22 = 0;

        $total_sld_date_23 = 0;

        $total_sld_date_24 = 0;

        $total_sld_date_25 = 0;

        $total_sld_date_26 = 0;

        $total_sld_date_27 = 0;

        $total_sld_date_28 = 0;

        $total_sld_date_29 = 0;

        $total_sld_date_30 = 0;

        $total_sld_date_31 = 0;



        //Total JST

        $total_jst_date_1 = 0;

        $total_jst_date_2 = 0;

        $total_jst_date_3 = 0;

        $total_jst_date_4 = 0;

        $total_jst_date_5 = 0;

        $total_jst_date_6 = 0;

        $total_jst_date_7 = 0;

        $total_jst_date_8 = 0;

        $total_jst_date_9 = 0;

        $total_jst_date_10 = 0;

        $total_jst_date_11 = 0;

        $total_jst_date_12 = 0;

        $total_jst_date_13 = 0;

        $total_jst_date_14 = 0;

        $total_jst_date_15 = 0;

        $total_jst_date_16 = 0;

        $total_jst_date_17 = 0;

        $total_jst_date_18 = 0;

        $total_jst_date_19 = 0;

        $total_jst_date_20 = 0;

        $total_jst_date_21 = 0;

        $total_jst_date_22 = 0;

        $total_jst_date_23 = 0;

        $total_jst_date_24 = 0;

        $total_jst_date_25 = 0;

        $total_jst_date_26 = 0;

        $total_jst_date_27 = 0;

        $total_jst_date_28 = 0;

        $total_jst_date_29 = 0;

        $total_jst_date_30 = 0;

        $total_jst_date_31 = 0;



        //Total TWS

        $total_tws_date_1 = 0;

        $total_tws_date_2 = 0;

        $total_tws_date_3 = 0;

        $total_tws_date_4 = 0;

        $total_tws_date_5 = 0;

        $total_tws_date_6 = 0;

        $total_tws_date_7 = 0;

        $total_tws_date_8 = 0;

        $total_tws_date_9 = 0;

        $total_tws_date_10 = 0;

        $total_tws_date_11 = 0;

        $total_tws_date_12 = 0;

        $total_tws_date_13 = 0;

        $total_tws_date_14 = 0;

        $total_tws_date_15 = 0;

        $total_tws_date_16 = 0;

        $total_tws_date_17 = 0;

        $total_tws_date_18 = 0;

        $total_tws_date_19 = 0;

        $total_tws_date_20 = 0;

        $total_tws_date_21 = 0;

        $total_tws_date_22 = 0;

        $total_tws_date_23 = 0;

        $total_tws_date_24 = 0;

        $total_tws_date_25 = 0;

        $total_tws_date_26 = 0;

        $total_tws_date_27 = 0;

        $total_tws_date_28 = 0;

        $total_tws_date_29 = 0;

        $total_tws_date_30 = 0;

        $total_tws_date_31 = 0;



        //Total Hot Stamp

        $total_hst_date_1 = 0;

        $total_hst_date_2 = 0;

        $total_hst_date_3 = 0;

        $total_hst_date_4 = 0;

        $total_hst_date_5 = 0;

        $total_hst_date_6 = 0;

        $total_hst_date_7 = 0;

        $total_hst_date_8 = 0;

        $total_hst_date_9 = 0;

        $total_hst_date_10 = 0;

        $total_hst_date_11 = 0;

        $total_hst_date_12 = 0;

        $total_hst_date_13 = 0;

        $total_hst_date_14 = 0;

        $total_hst_date_15 = 0;

        $total_hst_date_16 = 0;

        $total_hst_date_17 = 0;

        $total_hst_date_18 = 0;

        $total_hst_date_19 = 0;

        $total_hst_date_20 = 0;

        $total_hst_date_21 = 0;

        $total_hst_date_22 = 0;

        $total_hst_date_23 = 0;

        $total_hst_date_24 = 0;

        $total_hst_date_25 = 0;

        $total_hst_date_26 = 0;

        $total_hst_date_27 = 0;

        $total_hst_date_28 = 0;

        $total_hst_date_29 = 0;

        $total_hst_date_30 = 0;

        $total_hst_date_31 = 0;

        foreach ($records as $record) {

            $html .= "<tr>

            <th style='text-align:left;' colspan='48'>" . $record['remarks'] . "</th>

            </tr>";



            $line_no = $record['line_no'];

            $this->db->select("a.*, e.item_alias, e.lot, e.capacity, c.circuit_crimping, c.circuit_joint, c.circuit_semiautocutting as circuit_sac, c.circuit_stripping as circuit_str, c.circuit_hfsealer as circuit_hfs, c.circuit_jointtapping as circuit_jtp, c.circuit_weldingjoint as circuit_wjt, c.circuit_spotwelding as circuit_swl, c.circuit_solder as circuit_sld, c.circuit_jst as circuit_jst, c.circuit_twist as circuit_tws, c.circuit_hotstamp as circuit_hst, d.prod_plan as prodplan");

            $this->db->from('generate_mpp a');

            $this->db->join('mst_item b', 'a.product_no = b.item_id');

            $this->db->join('wip_mst_wos_cct c', 'a.product_no = c.mstwos_assyno', 'left');

            $this->db->join("(SELECT * FROM generate_mps_detail ORDER BY ltpp_month2 ASC) d", "d.p_month = '$filter_month' and d.p_year = '$filter_year' and d.revision = '$filter_revision2' and a.product_no = d.product_no");

            $this->db->join("mst_item e", "a.product_no = e.item_id");

            $this->db->where('a.p_month', $filter_month);

            $this->db->where('a.p_year', $filter_year);

            $this->db->where('a.revision', $filter_revision);

            $this->db->where('a.line_no', $line_no);

            $this->db->like('a.product_no', $filter_product_no);

            $this->db->group_by('a.product_no');

            $recordDetails = $this->db->get()->result_array();



            foreach ($recordDetails as $detail) {

                $total_mpp_prodplan += $detail['prodplan'];

                $total_cct_cutting += $detail['circuit_no'];

                $total_cct_prodplan += ($detail['prodplan'] * $detail['circuit_no']);



                $total_cct_crimping += $detail['circuit_crimping'];

                $total_cct_prodplan_crp += ($detail['prodplan'] * $detail['circuit_crimping']);



                $total_cct_joint += $detail['circuit_joint'];

                $total_cct_prodplan_jnt += ($detail['prodplan'] * $detail['circuit_joint']);



                $total_cct_sac += $detail['circuit_sac'];

                $total_cct_prodplan_sac += ($detail['prodplan'] * $detail['circuit_sac']);



                $total_cct_str += $detail['circuit_str'];

                $total_cct_prodplan_str += ($detail['prodplan'] * $detail['circuit_str']);



                $total_cct_hfs += $detail['circuit_hfs'];

                $total_cct_prodplan_hfs += ($detail['prodplan'] * $detail['circuit_hfs']);



                $total_cct_jtp += $detail['circuit_jtp'];

                $total_cct_prodplan_jtp += ($detail['prodplan'] * $detail['circuit_jtp']);



                $total_cct_wjt += $detail['circuit_wjt'];

                $total_cct_prodplan_wjt += ($detail['prodplan'] * $detail['circuit_wjt']);



                $total_cct_swl += $detail['circuit_swl'];

                $total_cct_prodplan_swl += ($detail['prodplan'] * $detail['circuit_swl']);



                $total_cct_sld += $detail['circuit_sld'];

                $total_cct_prodplan_sld += ($detail['prodplan'] * $detail['circuit_sld']);



                $total_cct_jst += $detail['circuit_jst'];

                $total_cct_prodplan_jst += ($detail['prodplan'] * $detail['circuit_jst']);



                $total_cct_tws += $detail['circuit_tws'];

                $total_cct_prodplan_tws += ($detail['prodplan'] * $detail['circuit_tws']);



                $total_cct_hst += $detail['circuit_hst'];

                $total_cct_prodplan_hst += ($detail['prodplan'] * $detail['circuit_hst']);



                $html .= "<tr>

                <td>" . $no . "</td>

                <td style='mso-number-format:\@;'>" . $detail['product_no'] . "</td>

                <td style='mso-number-format:\@;'>" . $detail['item_alias'] . "</td>

                <td>" . $detail['product_name'] . "</td>

                <td>" . $detail['circuit_no'] . "</td>

                <td>" . $detail['circuit_crimping'] . "</td>

                <td>" . $detail['circuit_joint'] . "</td>

                <td>" . $detail['circuit_sac'] . "</td>

                <td>" . $detail['circuit_str'] . "</td>

                <td>" . $detail['circuit_hfs'] . "</td>

                <td>" . $detail['circuit_jtp'] . "</td>

                <td>" . $detail['circuit_wjt'] . "</td>

                <td>" . $detail['circuit_swl'] . "</td>

                <td>" . $detail['circuit_sld'] . "</td>

                <td>" . $detail['circuit_jst'] . "</td>

                <td>" . $detail['circuit_tws'] . "</td>

                <td>" . $detail['circuit_hst'] . "</td>

                <td>" . $detail['prodplan'] . "</td>";



                //Detail Data

                $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

                $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

                $day = 1;

                $total_mpp_day = 0;

                while (strtotime($firstDate2) <= strtotime($endDate2)) {

                    $field_day = "date_".$day;



                    if (@$detail[$field_day] == "W") {

                        if($detail[$field_day] == "W"){

                            $qtyField = "";

                        }else{

                            $qtyField = $detail[$field_day];

                        }



                        $html .= "<td style='background:#FFC2C2;'>" . $qtyField . "</td>";

                    } else {

                        $html .= "<td>" . $detail[$field_day] . "</td>";

                    }



                    $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

                    $day++;

                }



                

                //Total MPP Day

                $total_mpp_date_1 += is_numeric($detail["date_1"]) ? $detail["date_1"] : 0;

                $total_mpp_date_2 += is_numeric($detail["date_2"]) ? $detail["date_2"] : 0;

                $total_mpp_date_3 += is_numeric($detail["date_3"]) ? $detail["date_3"] : 0;

                $total_mpp_date_4 += is_numeric($detail["date_4"]) ? $detail["date_4"] : 0;

                $total_mpp_date_5 += is_numeric($detail["date_5"]) ? $detail["date_5"] : 0;

                $total_mpp_date_6 += is_numeric($detail["date_6"]) ? $detail["date_6"] : 0;

                $total_mpp_date_7 += is_numeric($detail["date_7"]) ? $detail["date_7"] : 0;

                $total_mpp_date_8 += is_numeric($detail["date_8"]) ? $detail["date_8"] : 0;

                $total_mpp_date_9 += is_numeric($detail["date_9"]) ? $detail["date_9"] : 0;

                $total_mpp_date_10 += is_numeric($detail["date_10"]) ? $detail["date_10"] : 0;

                $total_mpp_date_11 += is_numeric($detail["date_11"]) ? $detail["date_11"] : 0;

                $total_mpp_date_12 += is_numeric($detail["date_12"]) ? $detail["date_12"] : 0;

                $total_mpp_date_13 += is_numeric($detail["date_13"]) ? $detail["date_13"] : 0;

                $total_mpp_date_14 += is_numeric($detail["date_14"]) ? $detail["date_14"] : 0;

                $total_mpp_date_15 += is_numeric($detail["date_15"]) ? $detail["date_15"] : 0;

                $total_mpp_date_16 += is_numeric($detail["date_16"]) ? $detail["date_16"] : 0;

                $total_mpp_date_17 += is_numeric($detail["date_17"]) ? $detail["date_17"] : 0;

                $total_mpp_date_18 += is_numeric($detail["date_18"]) ? $detail["date_18"] : 0;

                $total_mpp_date_19 += is_numeric($detail["date_19"]) ? $detail["date_19"] : 0;

                $total_mpp_date_20 += is_numeric($detail["date_20"]) ? $detail["date_20"] : 0;

                $total_mpp_date_21 += is_numeric($detail["date_21"]) ? $detail["date_21"] : 0;

                $total_mpp_date_22 += is_numeric($detail["date_22"]) ? $detail["date_22"] : 0;

                $total_mpp_date_23 += is_numeric($detail["date_23"]) ? $detail["date_23"] : 0;

                $total_mpp_date_24 += is_numeric($detail["date_24"]) ? $detail["date_24"] : 0;

                $total_mpp_date_25 += is_numeric($detail["date_25"]) ? $detail["date_25"] : 0;

                $total_mpp_date_26 += is_numeric($detail["date_26"]) ? $detail["date_26"] : 0;

                $total_mpp_date_27 += is_numeric($detail["date_27"]) ? $detail["date_27"] : 0;

                $total_mpp_date_28 += is_numeric($detail["date_28"]) ? $detail["date_28"] : 0;

                $total_mpp_date_29 += is_numeric($detail["date_29"]) ? $detail["date_29"] : 0;

                $total_mpp_date_30 += is_numeric($detail["date_30"]) ? $detail["date_30"] : 0;

                $total_mpp_date_31 += is_numeric($detail["date_31"]) ? $detail["date_31"] : 0;



                //Total Cutting Day

                $total_cutting_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_no']) : 0;

                $total_cutting_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_no']) : 0;

                $total_cutting_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_no']) : 0;

                $total_cutting_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_no']) : 0;

                $total_cutting_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_no']) : 0;

                $total_cutting_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_no']) : 0;

                $total_cutting_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_no']) : 0;

                $total_cutting_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_no']) : 0;

                $total_cutting_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_no']) : 0;

                $total_cutting_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_no']) : 0;

                $total_cutting_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_no']) : 0;

                $total_cutting_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_no']) : 0;

                $total_cutting_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_no']) : 0;

                $total_cutting_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_no']) : 0;

                $total_cutting_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_no']) : 0;

                $total_cutting_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_no']) : 0;

                $total_cutting_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_no']) : 0;

                $total_cutting_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_no']) : 0;

                $total_cutting_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_no']) : 0;

                $total_cutting_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_no']) : 0;

                $total_cutting_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_no']) : 0;

                $total_cutting_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_no']) : 0;

                $total_cutting_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_no']) : 0;

                $total_cutting_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_no']) : 0;

                $total_cutting_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_no']) : 0;

                $total_cutting_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_no']) : 0;

                $total_cutting_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_no']) : 0;

                $total_cutting_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_no']) : 0;

                $total_cutting_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_no']) : 0;

                $total_cutting_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_no']) : 0;

                $total_cutting_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_no']) : 0;



                //Total Crimping Day

                $total_crimping_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_crimping']) : 0;

                $total_crimping_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_crimping']) : 0;



                //Total Joint Day

                $total_joint_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_joint']) : 0;

                $total_joint_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_joint']) : 0;

                $total_joint_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_joint']) : 0;

                $total_joint_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_joint']) : 0;

                $total_joint_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_joint']) : 0;

                $total_joint_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_joint']) : 0;

                $total_joint_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_joint']) : 0;

                $total_joint_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_joint']) : 0;

                $total_joint_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_joint']) : 0;

                $total_joint_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_joint']) : 0;

                $total_joint_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_joint']) : 0;

                $total_joint_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_joint']) : 0;

                $total_joint_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_joint']) : 0;

                $total_joint_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_joint']) : 0;

                $total_joint_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_joint']) : 0;

                $total_joint_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_joint']) : 0;

                $total_joint_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_joint']) : 0;

                $total_joint_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_joint']) : 0;

                $total_joint_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_joint']) : 0;

                $total_joint_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_joint']) : 0;

                $total_joint_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_joint']) : 0;

                $total_joint_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_joint']) : 0;

                $total_joint_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_joint']) : 0;

                $total_joint_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_joint']) : 0;

                $total_joint_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_joint']) : 0;

                $total_joint_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_joint']) : 0;

                $total_joint_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_joint']) : 0;

                $total_joint_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_joint']) : 0;

                $total_joint_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_joint']) : 0;

                $total_joint_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_joint']) : 0;

                $total_joint_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_joint']) : 0;



                //Total Semi Auto Cutting Day

                $total_sac_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_sac']) : 0;

                $total_sac_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_sac']) : 0;

                $total_sac_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_sac']) : 0;

                $total_sac_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_sac']) : 0;

                $total_sac_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_sac']) : 0;

                $total_sac_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_sac']) : 0;

                $total_sac_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_sac']) : 0;

                $total_sac_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_sac']) : 0;

                $total_sac_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_sac']) : 0;

                $total_sac_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_sac']) : 0;

                $total_sac_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_sac']) : 0;

                $total_sac_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_sac']) : 0;

                $total_sac_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_sac']) : 0;

                $total_sac_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_sac']) : 0;

                $total_sac_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_sac']) : 0;

                $total_sac_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_sac']) : 0;

                $total_sac_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_sac']) : 0;

                $total_sac_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_sac']) : 0;

                $total_sac_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_sac']) : 0;

                $total_sac_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_sac']) : 0;

                $total_sac_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_sac']) : 0;

                $total_sac_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_sac']) : 0;

                $total_sac_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_sac']) : 0;

                $total_sac_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_sac']) : 0;

                $total_sac_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_sac']) : 0;

                $total_sac_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_sac']) : 0;

                $total_sac_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_sac']) : 0;

                $total_sac_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_sac']) : 0;

                $total_sac_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_sac']) : 0;

                $total_sac_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_sac']) : 0;

                $total_sac_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_sac']) : 0;



                //Total Stripping Day

                $total_str_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_str']) : 0;

                $total_str_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_str']) : 0;

                $total_str_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_str']) : 0;

                $total_str_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_str']) : 0;

                $total_str_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_str']) : 0;

                $total_str_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_str']) : 0;

                $total_str_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_str']) : 0;

                $total_str_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_str']) : 0;

                $total_str_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_str']) : 0;

                $total_str_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_str']) : 0;

                $total_str_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_str']) : 0;

                $total_str_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_str']) : 0;

                $total_str_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_str']) : 0;

                $total_str_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_str']) : 0;

                $total_str_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_str']) : 0;

                $total_str_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_str']) : 0;

                $total_str_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_str']) : 0;

                $total_str_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_str']) : 0;

                $total_str_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_str']) : 0;

                $total_str_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_str']) : 0;

                $total_str_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_str']) : 0;

                $total_str_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_str']) : 0;

                $total_str_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_str']) : 0;

                $total_str_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_str']) : 0;

                $total_str_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_str']) : 0;

                $total_str_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_str']) : 0;

                $total_str_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_str']) : 0;

                $total_str_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_str']) : 0;

                $total_str_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_str']) : 0;

                $total_str_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_str']) : 0;

                $total_str_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_str']) : 0;



                //Total HF Sealer Day

                $total_hfs_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_hfs']) : 0;

                $total_hfs_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_hfs']) : 0;



                //Joint Tapping

                $total_jtp_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_jtp']) : 0;

                $total_jtp_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_jtp']) : 0;



                //Welding Joint

                $total_wjt_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_wjt']) : 0;

                $total_wjt_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_wjt']) : 0;



                //Spot Welding

                $total_swl_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_swl']) : 0;

                $total_swl_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_swl']) : 0;

                $total_swl_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_swl']) : 0;

                $total_swl_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_swl']) : 0;

                $total_swl_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_swl']) : 0;

                $total_swl_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_swl']) : 0;

                $total_swl_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_swl']) : 0;

                $total_swl_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_swl']) : 0;

                $total_swl_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_swl']) : 0;

                $total_swl_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_swl']) : 0;

                $total_swl_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_swl']) : 0;

                $total_swl_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_swl']) : 0;

                $total_swl_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_swl']) : 0;

                $total_swl_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_swl']) : 0;

                $total_swl_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_swl']) : 0;

                $total_swl_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_swl']) : 0;

                $total_swl_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_swl']) : 0;

                $total_swl_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_swl']) : 0;

                $total_swl_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_swl']) : 0;

                $total_swl_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_swl']) : 0;

                $total_swl_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_swl']) : 0;

                $total_swl_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_swl']) : 0;

                $total_swl_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_swl']) : 0;

                $total_swl_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_swl']) : 0;

                $total_swl_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_swl']) : 0;

                $total_swl_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_swl']) : 0;

                $total_swl_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_swl']) : 0;

                $total_swl_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_swl']) : 0;

                $total_swl_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_swl']) : 0;

                $total_swl_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_swl']) : 0;

                $total_swl_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_swl']) : 0;



                //Solder

                $total_sld_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_sld']) : 0;

                $total_sld_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_sld']) : 0;

                $total_sld_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_sld']) : 0;

                $total_sld_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_sld']) : 0;

                $total_sld_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_sld']) : 0;

                $total_sld_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_sld']) : 0;

                $total_sld_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_sld']) : 0;

                $total_sld_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_sld']) : 0;

                $total_sld_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_sld']) : 0;

                $total_sld_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_sld']) : 0;

                $total_sld_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_sld']) : 0;

                $total_sld_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_sld']) : 0;

                $total_sld_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_sld']) : 0;

                $total_sld_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_sld']) : 0;

                $total_sld_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_sld']) : 0;

                $total_sld_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_sld']) : 0;

                $total_sld_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_sld']) : 0;

                $total_sld_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_sld']) : 0;

                $total_sld_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_sld']) : 0;

                $total_sld_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_sld']) : 0;

                $total_sld_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_sld']) : 0;

                $total_sld_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_sld']) : 0;

                $total_sld_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_sld']) : 0;

                $total_sld_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_sld']) : 0;

                $total_sld_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_sld']) : 0;

                $total_sld_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_sld']) : 0;

                $total_sld_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_sld']) : 0;

                $total_sld_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_sld']) : 0;

                $total_sld_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_sld']) : 0;

                $total_sld_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_sld']) : 0;

                $total_sld_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_sld']) : 0;



                //JST

                $total_jst_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_jst']) : 0;

                $total_jst_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_jst']) : 0;

                $total_jst_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_jst']) : 0;

                $total_jst_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_jst']) : 0;

                $total_jst_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_jst']) : 0;

                $total_jst_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_jst']) : 0;

                $total_jst_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_jst']) : 0;

                $total_jst_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_jst']) : 0;

                $total_jst_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_jst']) : 0;

                $total_jst_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_jst']) : 0;

                $total_jst_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_jst']) : 0;

                $total_jst_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_jst']) : 0;

                $total_jst_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_jst']) : 0;

                $total_jst_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_jst']) : 0;

                $total_jst_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_jst']) : 0;

                $total_jst_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_jst']) : 0;

                $total_jst_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_jst']) : 0;

                $total_jst_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_jst']) : 0;

                $total_jst_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_jst']) : 0;

                $total_jst_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_jst']) : 0;

                $total_jst_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_jst']) : 0;

                $total_jst_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_jst']) : 0;

                $total_jst_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_jst']) : 0;

                $total_jst_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_jst']) : 0;

                $total_jst_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_jst']) : 0;

                $total_jst_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_jst']) : 0;

                $total_jst_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_jst']) : 0;

                $total_jst_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_jst']) : 0;

                $total_jst_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_jst']) : 0;

                $total_jst_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_jst']) : 0;

                $total_jst_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_jst']) : 0;



                //TWIST

                $total_tws_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_tws']) : 0;

                $total_tws_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_tws']) : 0;

                $total_tws_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_tws']) : 0;

                $total_tws_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_tws']) : 0;

                $total_tws_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_tws']) : 0;

                $total_tws_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_tws']) : 0;

                $total_tws_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_tws']) : 0;

                $total_tws_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_tws']) : 0;

                $total_tws_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_tws']) : 0;

                $total_tws_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_tws']) : 0;

                $total_tws_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_tws']) : 0;

                $total_tws_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_tws']) : 0;

                $total_tws_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_tws']) : 0;

                $total_tws_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_tws']) : 0;

                $total_tws_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_tws']) : 0;

                $total_tws_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_tws']) : 0;

                $total_tws_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_tws']) : 0;

                $total_tws_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_tws']) : 0;

                $total_tws_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_tws']) : 0;

                $total_tws_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_tws']) : 0;

                $total_tws_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_tws']) : 0;

                $total_tws_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_tws']) : 0;

                $total_tws_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_tws']) : 0;

                $total_tws_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_tws']) : 0;

                $total_tws_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_tws']) : 0;

                $total_tws_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_tws']) : 0;

                $total_tws_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_tws']) : 0;

                $total_tws_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_tws']) : 0;

                $total_tws_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_tws']) : 0;

                $total_tws_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_tws']) : 0;

                $total_tws_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_tws']) : 0;



                //HOT STAMP

                $total_hst_date_1 += is_numeric($detail["date_1"]) ? ($detail["date_1"] * $detail['circuit_hst']) : 0;

                $total_hst_date_2 += is_numeric($detail["date_2"]) ? ($detail["date_2"] * $detail['circuit_hst']) : 0;

                $total_hst_date_3 += is_numeric($detail["date_3"]) ? ($detail["date_3"] * $detail['circuit_hst']) : 0;

                $total_hst_date_4 += is_numeric($detail["date_4"]) ? ($detail["date_4"] * $detail['circuit_hst']) : 0;

                $total_hst_date_5 += is_numeric($detail["date_5"]) ? ($detail["date_5"] * $detail['circuit_hst']) : 0;

                $total_hst_date_6 += is_numeric($detail["date_6"]) ? ($detail["date_6"] * $detail['circuit_hst']) : 0;

                $total_hst_date_7 += is_numeric($detail["date_7"]) ? ($detail["date_7"] * $detail['circuit_hst']) : 0;

                $total_hst_date_8 += is_numeric($detail["date_8"]) ? ($detail["date_8"] * $detail['circuit_hst']) : 0;

                $total_hst_date_9 += is_numeric($detail["date_9"]) ? ($detail["date_9"] * $detail['circuit_hst']) : 0;

                $total_hst_date_10 += is_numeric($detail["date_10"]) ? ($detail["date_10"] * $detail['circuit_hst']) : 0;

                $total_hst_date_11 += is_numeric($detail["date_11"]) ? ($detail["date_11"] * $detail['circuit_hst']) : 0;

                $total_hst_date_12 += is_numeric($detail["date_12"]) ? ($detail["date_12"] * $detail['circuit_hst']) : 0;

                $total_hst_date_13 += is_numeric($detail["date_13"]) ? ($detail["date_13"] * $detail['circuit_hst']) : 0;

                $total_hst_date_14 += is_numeric($detail["date_14"]) ? ($detail["date_14"] * $detail['circuit_hst']) : 0;

                $total_hst_date_15 += is_numeric($detail["date_15"]) ? ($detail["date_15"] * $detail['circuit_hst']) : 0;

                $total_hst_date_16 += is_numeric($detail["date_16"]) ? ($detail["date_16"] * $detail['circuit_hst']) : 0;

                $total_hst_date_17 += is_numeric($detail["date_17"]) ? ($detail["date_17"] * $detail['circuit_hst']) : 0;

                $total_hst_date_18 += is_numeric($detail["date_18"]) ? ($detail["date_18"] * $detail['circuit_hst']) : 0;

                $total_hst_date_19 += is_numeric($detail["date_19"]) ? ($detail["date_19"] * $detail['circuit_hst']) : 0;

                $total_hst_date_20 += is_numeric($detail["date_20"]) ? ($detail["date_20"] * $detail['circuit_hst']) : 0;

                $total_hst_date_21 += is_numeric($detail["date_21"]) ? ($detail["date_21"] * $detail['circuit_hst']) : 0;

                $total_hst_date_22 += is_numeric($detail["date_22"]) ? ($detail["date_22"] * $detail['circuit_hst']) : 0;

                $total_hst_date_23 += is_numeric($detail["date_23"]) ? ($detail["date_23"] * $detail['circuit_hst']) : 0;

                $total_hst_date_24 += is_numeric($detail["date_24"]) ? ($detail["date_24"] * $detail['circuit_hst']) : 0;

                $total_hst_date_25 += is_numeric($detail["date_25"]) ? ($detail["date_25"] * $detail['circuit_hst']) : 0;

                $total_hst_date_26 += is_numeric($detail["date_26"]) ? ($detail["date_26"] * $detail['circuit_hst']) : 0;

                $total_hst_date_27 += is_numeric($detail["date_27"]) ? ($detail["date_27"] * $detail['circuit_hst']) : 0;

                $total_hst_date_28 += is_numeric($detail["date_28"]) ? ($detail["date_28"] * $detail['circuit_hst']) : 0;

                $total_hst_date_29 += is_numeric($detail["date_29"]) ? ($detail["date_29"] * $detail['circuit_hst']) : 0;

                $total_hst_date_30 += is_numeric($detail["date_30"]) ? ($detail["date_30"] * $detail['circuit_hst']) : 0;

                $total_hst_date_31 += is_numeric($detail["date_31"]) ? ($detail["date_31"] * $detail['circuit_hst']) : 0;

                $no++;

            }

            $html .= "</tr>";

        }



        $arr_total_mpp = array(

            "date_1" => $total_mpp_date_1,

            "date_2" => $total_mpp_date_2,

            "date_3" => $total_mpp_date_3,

            "date_4" => $total_mpp_date_4,

            "date_5" => $total_mpp_date_5,

            "date_6" => $total_mpp_date_6,

            "date_7" => $total_mpp_date_7,

            "date_8" => $total_mpp_date_8,

            "date_9" => $total_mpp_date_9,

            "date_10" => $total_mpp_date_10,

            "date_11" => $total_mpp_date_11,

            "date_12" => $total_mpp_date_12,

            "date_13" => $total_mpp_date_13,

            "date_14" => $total_mpp_date_14,

            "date_15" => $total_mpp_date_15,

            "date_16" => $total_mpp_date_16,

            "date_17" => $total_mpp_date_17,

            "date_18" => $total_mpp_date_18,

            "date_19" => $total_mpp_date_19,

            "date_20" => $total_mpp_date_20,

            "date_21" => $total_mpp_date_21,

            "date_22" => $total_mpp_date_22,

            "date_23" => $total_mpp_date_23,

            "date_24" => $total_mpp_date_24,

            "date_25" => $total_mpp_date_25,

            "date_26" => $total_mpp_date_26,

            "date_27" => $total_mpp_date_27,

            "date_28" => $total_mpp_date_28,

            "date_29" => $total_mpp_date_29,

            "date_30" => $total_mpp_date_30,

            "date_31" => $total_mpp_date_31

        );



        $arr_total_cutting = array(

            "date_1" => $total_cutting_date_1,

            "date_2" => $total_cutting_date_2,

            "date_3" => $total_cutting_date_3,

            "date_4" => $total_cutting_date_4,

            "date_5" => $total_cutting_date_5,

            "date_6" => $total_cutting_date_6,

            "date_7" => $total_cutting_date_7,

            "date_8" => $total_cutting_date_8,

            "date_9" => $total_cutting_date_9,

            "date_10" => $total_cutting_date_10,

            "date_11" => $total_cutting_date_11,

            "date_12" => $total_cutting_date_12,

            "date_13" => $total_cutting_date_13,

            "date_14" => $total_cutting_date_14,

            "date_15" => $total_cutting_date_15,

            "date_16" => $total_cutting_date_16,

            "date_17" => $total_cutting_date_17,

            "date_18" => $total_cutting_date_18,

            "date_19" => $total_cutting_date_19,

            "date_20" => $total_cutting_date_20,

            "date_21" => $total_cutting_date_21,

            "date_22" => $total_cutting_date_22,

            "date_23" => $total_cutting_date_23,

            "date_24" => $total_cutting_date_24,

            "date_25" => $total_cutting_date_25,

            "date_26" => $total_cutting_date_26,

            "date_27" => $total_cutting_date_27,

            "date_28" => $total_cutting_date_28,

            "date_29" => $total_cutting_date_29,

            "date_30" => $total_cutting_date_30,

            "date_31" => $total_cutting_date_31

        );



        $arr_total_crimping = array(

            "date_1" => $total_crimping_date_1,

            "date_2" => $total_crimping_date_2,

            "date_3" => $total_crimping_date_3,

            "date_4" => $total_crimping_date_4,

            "date_5" => $total_crimping_date_5,

            "date_6" => $total_crimping_date_6,

            "date_7" => $total_crimping_date_7,

            "date_8" => $total_crimping_date_8,

            "date_9" => $total_crimping_date_9,

            "date_10" => $total_crimping_date_10,

            "date_11" => $total_crimping_date_11,

            "date_12" => $total_crimping_date_12,

            "date_13" => $total_crimping_date_13,

            "date_14" => $total_crimping_date_14,

            "date_15" => $total_crimping_date_15,

            "date_16" => $total_crimping_date_16,

            "date_17" => $total_crimping_date_17,

            "date_18" => $total_crimping_date_18,

            "date_19" => $total_crimping_date_19,

            "date_20" => $total_crimping_date_20,

            "date_21" => $total_crimping_date_21,

            "date_22" => $total_crimping_date_22,

            "date_23" => $total_crimping_date_23,

            "date_24" => $total_crimping_date_24,

            "date_25" => $total_crimping_date_25,

            "date_26" => $total_crimping_date_26,

            "date_27" => $total_crimping_date_27,

            "date_28" => $total_crimping_date_28,

            "date_29" => $total_crimping_date_29,

            "date_30" => $total_crimping_date_30,

            "date_31" => $total_crimping_date_31

        );



        $arr_total_joint = array(

            "date_1" => $total_joint_date_1,

            "date_2" => $total_joint_date_2,

            "date_3" => $total_joint_date_3,

            "date_4" => $total_joint_date_4,

            "date_5" => $total_joint_date_5,

            "date_6" => $total_joint_date_6,

            "date_7" => $total_joint_date_7,

            "date_8" => $total_joint_date_8,

            "date_9" => $total_joint_date_9,

            "date_10" => $total_joint_date_10,

            "date_11" => $total_joint_date_11,

            "date_12" => $total_joint_date_12,

            "date_13" => $total_joint_date_13,

            "date_14" => $total_joint_date_14,

            "date_15" => $total_joint_date_15,

            "date_16" => $total_joint_date_16,

            "date_17" => $total_joint_date_17,

            "date_18" => $total_joint_date_18,

            "date_19" => $total_joint_date_19,

            "date_20" => $total_joint_date_20,

            "date_21" => $total_joint_date_21,

            "date_22" => $total_joint_date_22,

            "date_23" => $total_joint_date_23,

            "date_24" => $total_joint_date_24,

            "date_25" => $total_joint_date_25,

            "date_26" => $total_joint_date_26,

            "date_27" => $total_joint_date_27,

            "date_28" => $total_joint_date_28,

            "date_29" => $total_joint_date_29,

            "date_30" => $total_joint_date_30,

            "date_31" => $total_joint_date_31

        );



        $arr_total_sac = array(

            "date_1" => $total_sac_date_1,

            "date_2" => $total_sac_date_2,

            "date_3" => $total_sac_date_3,

            "date_4" => $total_sac_date_4,

            "date_5" => $total_sac_date_5,

            "date_6" => $total_sac_date_6,

            "date_7" => $total_sac_date_7,

            "date_8" => $total_sac_date_8,

            "date_9" => $total_sac_date_9,

            "date_10" => $total_sac_date_10,

            "date_11" => $total_sac_date_11,

            "date_12" => $total_sac_date_12,

            "date_13" => $total_sac_date_13,

            "date_14" => $total_sac_date_14,

            "date_15" => $total_sac_date_15,

            "date_16" => $total_sac_date_16,

            "date_17" => $total_sac_date_17,

            "date_18" => $total_sac_date_18,

            "date_19" => $total_sac_date_19,

            "date_20" => $total_sac_date_20,

            "date_21" => $total_sac_date_21,

            "date_22" => $total_sac_date_22,

            "date_23" => $total_sac_date_23,

            "date_24" => $total_sac_date_24,

            "date_25" => $total_sac_date_25,

            "date_26" => $total_sac_date_26,

            "date_27" => $total_sac_date_27,

            "date_28" => $total_sac_date_28,

            "date_29" => $total_sac_date_29,

            "date_30" => $total_sac_date_30,

            "date_31" => $total_sac_date_31

        );



        $arr_total_str = array(

            "date_1" => $total_str_date_1,

            "date_2" => $total_str_date_2,

            "date_3" => $total_str_date_3,

            "date_4" => $total_str_date_4,

            "date_5" => $total_str_date_5,

            "date_6" => $total_str_date_6,

            "date_7" => $total_str_date_7,

            "date_8" => $total_str_date_8,

            "date_9" => $total_str_date_9,

            "date_10" => $total_str_date_10,

            "date_11" => $total_str_date_11,

            "date_12" => $total_str_date_12,

            "date_13" => $total_str_date_13,

            "date_14" => $total_str_date_14,

            "date_15" => $total_str_date_15,

            "date_16" => $total_str_date_16,

            "date_17" => $total_str_date_17,

            "date_18" => $total_str_date_18,

            "date_19" => $total_str_date_19,

            "date_20" => $total_str_date_20,

            "date_21" => $total_str_date_21,

            "date_22" => $total_str_date_22,

            "date_23" => $total_str_date_23,

            "date_24" => $total_str_date_24,

            "date_25" => $total_str_date_25,

            "date_26" => $total_str_date_26,

            "date_27" => $total_str_date_27,

            "date_28" => $total_str_date_28,

            "date_29" => $total_str_date_29,

            "date_30" => $total_str_date_30,

            "date_31" => $total_str_date_31

        );



        $arr_total_hfs = array(

            "date_1" => $total_hfs_date_1,

            "date_2" => $total_hfs_date_2,

            "date_3" => $total_hfs_date_3,

            "date_4" => $total_hfs_date_4,

            "date_5" => $total_hfs_date_5,

            "date_6" => $total_hfs_date_6,

            "date_7" => $total_hfs_date_7,

            "date_8" => $total_hfs_date_8,

            "date_9" => $total_hfs_date_9,

            "date_10" => $total_hfs_date_10,

            "date_11" => $total_hfs_date_11,

            "date_12" => $total_hfs_date_12,

            "date_13" => $total_hfs_date_13,

            "date_14" => $total_hfs_date_14,

            "date_15" => $total_hfs_date_15,

            "date_16" => $total_hfs_date_16,

            "date_17" => $total_hfs_date_17,

            "date_18" => $total_hfs_date_18,

            "date_19" => $total_hfs_date_19,

            "date_20" => $total_hfs_date_20,

            "date_21" => $total_hfs_date_21,

            "date_22" => $total_hfs_date_22,

            "date_23" => $total_hfs_date_23,

            "date_24" => $total_hfs_date_24,

            "date_25" => $total_hfs_date_25,

            "date_26" => $total_hfs_date_26,

            "date_27" => $total_hfs_date_27,

            "date_28" => $total_hfs_date_28,

            "date_29" => $total_hfs_date_29,

            "date_30" => $total_hfs_date_30,

            "date_31" => $total_hfs_date_31

        );



        $arr_total_jtp = array(

            "date_1" => $total_jtp_date_1,

            "date_2" => $total_jtp_date_2,

            "date_3" => $total_jtp_date_3,

            "date_4" => $total_jtp_date_4,

            "date_5" => $total_jtp_date_5,

            "date_6" => $total_jtp_date_6,

            "date_7" => $total_jtp_date_7,

            "date_8" => $total_jtp_date_8,

            "date_9" => $total_jtp_date_9,

            "date_10" => $total_jtp_date_10,

            "date_11" => $total_jtp_date_11,

            "date_12" => $total_jtp_date_12,

            "date_13" => $total_jtp_date_13,

            "date_14" => $total_jtp_date_14,

            "date_15" => $total_jtp_date_15,

            "date_16" => $total_jtp_date_16,

            "date_17" => $total_jtp_date_17,

            "date_18" => $total_jtp_date_18,

            "date_19" => $total_jtp_date_19,

            "date_20" => $total_jtp_date_20,

            "date_21" => $total_jtp_date_21,

            "date_22" => $total_jtp_date_22,

            "date_23" => $total_jtp_date_23,

            "date_24" => $total_jtp_date_24,

            "date_25" => $total_jtp_date_25,

            "date_26" => $total_jtp_date_26,

            "date_27" => $total_jtp_date_27,

            "date_28" => $total_jtp_date_28,

            "date_29" => $total_jtp_date_29,

            "date_30" => $total_jtp_date_30,

            "date_31" => $total_jtp_date_31

        );



        $arr_total_wjt = array(

            "date_1" => $total_wjt_date_1,

            "date_2" => $total_wjt_date_2,

            "date_3" => $total_wjt_date_3,

            "date_4" => $total_wjt_date_4,

            "date_5" => $total_wjt_date_5,

            "date_6" => $total_wjt_date_6,

            "date_7" => $total_wjt_date_7,

            "date_8" => $total_wjt_date_8,

            "date_9" => $total_wjt_date_9,

            "date_10" => $total_wjt_date_10,

            "date_11" => $total_wjt_date_11,

            "date_12" => $total_wjt_date_12,

            "date_13" => $total_wjt_date_13,

            "date_14" => $total_wjt_date_14,

            "date_15" => $total_wjt_date_15,

            "date_16" => $total_wjt_date_16,

            "date_17" => $total_wjt_date_17,

            "date_18" => $total_wjt_date_18,

            "date_19" => $total_wjt_date_19,

            "date_20" => $total_wjt_date_20,

            "date_21" => $total_wjt_date_21,

            "date_22" => $total_wjt_date_22,

            "date_23" => $total_wjt_date_23,

            "date_24" => $total_wjt_date_24,

            "date_25" => $total_wjt_date_25,

            "date_26" => $total_wjt_date_26,

            "date_27" => $total_wjt_date_27,

            "date_28" => $total_wjt_date_28,

            "date_29" => $total_wjt_date_29,

            "date_30" => $total_wjt_date_30,

            "date_31" => $total_wjt_date_31

        );



        $arr_total_swl = array(

            "date_1" => $total_swl_date_1,

            "date_2" => $total_swl_date_2,

            "date_3" => $total_swl_date_3,

            "date_4" => $total_swl_date_4,

            "date_5" => $total_swl_date_5,

            "date_6" => $total_swl_date_6,

            "date_7" => $total_swl_date_7,

            "date_8" => $total_swl_date_8,

            "date_9" => $total_swl_date_9,

            "date_10" => $total_swl_date_10,

            "date_11" => $total_swl_date_11,

            "date_12" => $total_swl_date_12,

            "date_13" => $total_swl_date_13,

            "date_14" => $total_swl_date_14,

            "date_15" => $total_swl_date_15,

            "date_16" => $total_swl_date_16,

            "date_17" => $total_swl_date_17,

            "date_18" => $total_swl_date_18,

            "date_19" => $total_swl_date_19,

            "date_20" => $total_swl_date_20,

            "date_21" => $total_swl_date_21,

            "date_22" => $total_swl_date_22,

            "date_23" => $total_swl_date_23,

            "date_24" => $total_swl_date_24,

            "date_25" => $total_swl_date_25,

            "date_26" => $total_swl_date_26,

            "date_27" => $total_swl_date_27,

            "date_28" => $total_swl_date_28,

            "date_29" => $total_swl_date_29,

            "date_30" => $total_swl_date_30,

            "date_31" => $total_swl_date_31

        );



        $arr_total_sld = array(

            "date_1" => $total_sld_date_1,

            "date_2" => $total_sld_date_2,

            "date_3" => $total_sld_date_3,

            "date_4" => $total_sld_date_4,

            "date_5" => $total_sld_date_5,

            "date_6" => $total_sld_date_6,

            "date_7" => $total_sld_date_7,

            "date_8" => $total_sld_date_8,

            "date_9" => $total_sld_date_9,

            "date_10" => $total_sld_date_10,

            "date_11" => $total_sld_date_11,

            "date_12" => $total_sld_date_12,

            "date_13" => $total_sld_date_13,

            "date_14" => $total_sld_date_14,

            "date_15" => $total_sld_date_15,

            "date_16" => $total_sld_date_16,

            "date_17" => $total_sld_date_17,

            "date_18" => $total_sld_date_18,

            "date_19" => $total_sld_date_19,

            "date_20" => $total_sld_date_20,

            "date_21" => $total_sld_date_21,

            "date_22" => $total_sld_date_22,

            "date_23" => $total_sld_date_23,

            "date_24" => $total_sld_date_24,

            "date_25" => $total_sld_date_25,

            "date_26" => $total_sld_date_26,

            "date_27" => $total_sld_date_27,

            "date_28" => $total_sld_date_28,

            "date_29" => $total_sld_date_29,

            "date_30" => $total_sld_date_30,

            "date_31" => $total_sld_date_31

        );



        $arr_total_jst = array(

            "date_1" => $total_jst_date_1,

            "date_2" => $total_jst_date_2,

            "date_3" => $total_jst_date_3,

            "date_4" => $total_jst_date_4,

            "date_5" => $total_jst_date_5,

            "date_6" => $total_jst_date_6,

            "date_7" => $total_jst_date_7,

            "date_8" => $total_jst_date_8,

            "date_9" => $total_jst_date_9,

            "date_10" => $total_jst_date_10,

            "date_11" => $total_jst_date_11,

            "date_12" => $total_jst_date_12,

            "date_13" => $total_jst_date_13,

            "date_14" => $total_jst_date_14,

            "date_15" => $total_jst_date_15,

            "date_16" => $total_jst_date_16,

            "date_17" => $total_jst_date_17,

            "date_18" => $total_jst_date_18,

            "date_19" => $total_jst_date_19,

            "date_20" => $total_jst_date_20,

            "date_21" => $total_jst_date_21,

            "date_22" => $total_jst_date_22,

            "date_23" => $total_jst_date_23,

            "date_24" => $total_jst_date_24,

            "date_25" => $total_jst_date_25,

            "date_26" => $total_jst_date_26,

            "date_27" => $total_jst_date_27,

            "date_28" => $total_jst_date_28,

            "date_29" => $total_jst_date_29,

            "date_30" => $total_jst_date_30,

            "date_31" => $total_jst_date_31

        );



        $arr_total_tws = array(

            "date_1" => $total_tws_date_1,

            "date_2" => $total_tws_date_2,

            "date_3" => $total_tws_date_3,

            "date_4" => $total_tws_date_4,

            "date_5" => $total_tws_date_5,

            "date_6" => $total_tws_date_6,

            "date_7" => $total_tws_date_7,

            "date_8" => $total_tws_date_8,

            "date_9" => $total_tws_date_9,

            "date_10" => $total_tws_date_10,

            "date_11" => $total_tws_date_11,

            "date_12" => $total_tws_date_12,

            "date_13" => $total_tws_date_13,

            "date_14" => $total_tws_date_14,

            "date_15" => $total_tws_date_15,

            "date_16" => $total_tws_date_16,

            "date_17" => $total_tws_date_17,

            "date_18" => $total_tws_date_18,

            "date_19" => $total_tws_date_19,

            "date_20" => $total_tws_date_20,

            "date_21" => $total_tws_date_21,

            "date_22" => $total_tws_date_22,

            "date_23" => $total_tws_date_23,

            "date_24" => $total_tws_date_24,

            "date_25" => $total_tws_date_25,

            "date_26" => $total_tws_date_26,

            "date_27" => $total_tws_date_27,

            "date_28" => $total_tws_date_28,

            "date_29" => $total_tws_date_29,

            "date_30" => $total_tws_date_30,

            "date_31" => $total_tws_date_31

        );



        $arr_total_hst = array(

            "date_1" => $total_hst_date_1,

            "date_2" => $total_hst_date_2,

            "date_3" => $total_hst_date_3,

            "date_4" => $total_hst_date_4,

            "date_5" => $total_hst_date_5,

            "date_6" => $total_hst_date_6,

            "date_7" => $total_hst_date_7,

            "date_8" => $total_hst_date_8,

            "date_9" => $total_hst_date_9,

            "date_10" => $total_hst_date_10,

            "date_11" => $total_hst_date_11,

            "date_12" => $total_hst_date_12,

            "date_13" => $total_hst_date_13,

            "date_14" => $total_hst_date_14,

            "date_15" => $total_hst_date_15,

            "date_16" => $total_hst_date_16,

            "date_17" => $total_hst_date_17,

            "date_18" => $total_hst_date_18,

            "date_19" => $total_hst_date_19,

            "date_20" => $total_hst_date_20,

            "date_21" => $total_hst_date_21,

            "date_22" => $total_hst_date_22,

            "date_23" => $total_hst_date_23,

            "date_24" => $total_hst_date_24,

            "date_25" => $total_hst_date_25,

            "date_26" => $total_hst_date_26,

            "date_27" => $total_hst_date_27,

            "date_28" => $total_hst_date_28,

            "date_29" => $total_hst_date_29,

            "date_30" => $total_hst_date_30,

            "date_31" => $total_hst_date_31

        );



        //TOTAL MPP

        $html .= "  <tr>

        <th colspan='4' style='text-align:left;'><b>TOTAL MPP</b></th>

        <th colspan='6'>LOADING</th>

        <th colspan='5'>CAPACITY</th>

        <th>% CAP</th>

        <th>PRODPLAN (PCS)</th>

        <th>".number_format($total_mpp_prodplan)."</th>";



        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_mpp[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "  </tr>";



        //TOTAL CUTTING

        $persenCutting = @round(($qTotalCutting[0]->total / $qTotalCutting[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenCutting >= 100){

            $style = "style='background:#FFD8D8;'";

        }



        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL CUTTING (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalCutting[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalCutting[0]->total_capacity)."</td>

        <td>".$persenCutting."</td>

        <td>CUTTING (CCT)</td>

        <td>".number_format($total_cct_prodplan)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_cutting[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        // //TOTAL CRIMPING

        $persenCrimping = @round(($qTotalCrimping[0]->total / $qTotalCrimping[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenCrimping >= 100){

            $style = "style='background:#FFD8D8;'";

        }



        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL CRIMPING (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalCrimping[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalCrimping[0]->total_capacity)."</td>

        <td>".$persenCrimping."</td>

        <td>CRIMPING (CCT)</td>

        <td>".number_format($total_cct_prodplan_crp)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_crimping[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        // //TOTAL JOINT

        $persenJoint = @round(($qTotalJoint[0]->total / $qTotalJoint[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenJoint >= 100){

            $style = "style='background:#FFD8D8;'";

        }



        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL JOINT (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalJoint[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalJoint[0]->total_capacity)."</td>

        <td>".$persenJoint."</td>

        <td>JOINT (CCT)</td>

        <td>".number_format($total_cct_prodplan_jnt)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_joint[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        // //TOTAL Semi auto Cutting

        $persenSemiAutoCutting = @round(($qTotalSemiAutoCutting[0]->total / $qTotalSemiAutoCutting[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenSemiAutoCutting >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL SEMI AUTO CUTTING (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalSemiAutoCutting[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalSemiAutoCutting[0]->total_capacity)."</td>

        <td>".$persenSemiAutoCutting."</td>

        <td>SEMI AUTO CUTTING (CCT)</td>

        <td>".number_format($total_cct_prodplan_sac)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_sac[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL Stripping

        $persenStripping = @round(($qTotalStripping[0]->total / $qTotalStripping[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenStripping >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL STRIPPING (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalStripping[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalStripping[0]->total_capacity)."</td>

        <td>".$persenStripping."</td>

        <td>STRIPPING (CCT)</td>

        <td>".number_format($total_cct_prodplan_str)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_str[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL HF SEALER

        $persenHfSealer = @round(($qTotalHfSealer[0]->total / $qTotalHfSealer[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenHfSealer >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL HF SEALER (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalHfSealer[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalHfSealer[0]->total_capacity)."</td>

        <td>".$persenHfSealer."</td>

        <td>HF SEALER (CCT)</td>

        <td>".number_format($total_cct_prodplan_hfs)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_hfs[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL JOINT TAPPING

        $persenJointTapping = @round(($qTotalJointTapping[0]->total / $qTotalJointTapping[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenJointTapping >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL JOINT TAPPING (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalJointTapping[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalJointTapping[0]->total_capacity)."</td>

        <td>".$persenJointTapping."</td>

        <td>JOINT TAPPING (CCT)</td>

        <td>".number_format($total_cct_prodplan_jtp)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_jtp[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL WELDING JOINT

        $persenWeldingJoint = @round(($qTotalWeldingJoint[0]->total / $qTotalWeldingJoint[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenWeldingJoint >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL WELDING JOINT (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalWeldingJoint[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalWeldingJoint[0]->total_capacity)."</td>

        <td>".@round(($qTotalWeldingJoint[0]->total / $qTotalWeldingJoint[0]->total_capacity) * 100, 2)."</td>

        <td>WELDING JOINT (CCT)</td>

        <td>".number_format($total_cct_prodplan_wjt)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_wjt[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL SPOT Welding

        $persenSpotWelding = @round(($qTotalSpotWelding[0]->total / $qTotalSpotWelding[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenSpotWelding >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL SPOT WELDING (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalSpotWelding[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalSpotWelding[0]->total_capacity)."</td>

        <td>".$persenSpotWelding."</td>

        <td>SPOT WELDING (CCT)</td>

        <td>".number_format($total_cct_prodplan_swl)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_swl[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL SOLDER

        $persenSolder = @round(($qTotalSolder[0]->total / $qTotalSolder[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenSolder >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL SOLDER (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalSolder[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalSolder[0]->total_capacity)."</td>

        <td>".$persenSolder."</td>

        <td>SOLDER (CCT)</td>

        <td>".number_format($total_cct_prodplan_sld)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_sld[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL JST

        $persenJst = @round(($qTotalJst[0]->total / $qTotalJst[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenJst >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL JST (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalJst[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalJst[0]->total_capacity)."</td>

        <td>".$persenJst."</td>

        <td>JST (CCT)</td>

        <td>".number_format($total_cct_prodplan_jst)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_jst[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL TWIST

        $persenTwist = @round(($qTotalTwist[0]->total / $qTotalTwist[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenTwist >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL TWIST (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalTwist[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalTwist[0]->total_capacity)."</td>

        <td>".$persenTwist."</td>

        <td>TWIST (CCT)</td>

        <td>".number_format($total_cct_prodplan_tws)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_tws[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        //TOTAL HOT STAMP

        $persenHotStamp = @round(($qTotalHotStamp[0]->total / $qTotalHotStamp[0]->total_capacity) * 100, 2);

        $style = ""; 

        if($persenHotStamp >= 100){

            $style = "style='background:#FFD8D8;'";

        }

        $html .= "  <tr ".$style.">

        <td colspan='4' style='text-align:left;'><b>TOTAL HOT STAMP (UMH)</b></td>

        <td colspan='6'>".number_format($qTotalHotStamp[0]->total)."</td>

        <td colspan='5'>".number_format($qTotalHotStamp[0]->total_capacity)."</td>

        <td>".$persenHotStamp."</td>

        <td>HOT STAMP (CCT)</td>

        <td>".number_format($total_cct_prodplan_hst)."</td>";

        $firstDate2 = date('Y-m-01', strtotime($filter_year . "-" . $filter_month . "-01"));

        $endDate2 = date('Y-m-t', strtotime($filter_year . "-" . $filter_month . "-01"));

        $day = 1;

        while (strtotime($firstDate2) <= strtotime($endDate2)) {

            $field_day = "date_".$day;

            $html .= "<th>".number_format($arr_total_hst[$field_day])."</th>";

            $firstDate2 = date("Y-m-d", strtotime("+1 day", strtotime($firstDate2)));

            $day++;

        }

        $html .= "</tr>";



        $html .= "</table>";

        echo $html;

    }

}


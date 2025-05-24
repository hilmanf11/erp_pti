<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mps extends CI_Controller
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
        //$this->pg = $this->load->database('pg', TRUE);

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mps');
        } else {
            redirect('error_access');
        }
    }

    public function ltpp_no($p_month, $p_year)
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        $datenow    = $p_year. $p_month;
        $sqlGetID   = $this->dummy->query("SELECT max(ltpp_doc) as kode FROM ltpp_header 
            WHERE ltpp_doc like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }

        return $datenow . "/LTPP/" . $autoID;
    }

    public function getData()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            //Configuration Planning
            $this->db->select('*');
            $this->db->from("planning_config");
            $config = $this->db->get()->row();

            //Select Query
            $this->db->select('a.item_id, a.item_name, a.item_manldtm, d.customer_id, c.circuit_no, a.st_sparepart,
                COALESCE(c.pp, 0) as pp, 
                COALESCE(c.plant_1, 0) as plant_1, 
                COALESCE(c.plant_2, 0) as plant_2, 
                COALESCE(c.plant_3, 0) as plant_3, 
                COALESCE(c.plant_4, 0) as plant_4, 
                COALESCE(c.plant_5, 0) as plant_5, 
                COALESCE(c.plant_6, 0) as plant_6, 
                COALESCE(c.plant_7, 0) as plant_7, 
                COALESCE(c.plant_8, 0) as plant_8, 
                COALESCE(c.plant_9, 0) as plant_9, 
                COALESCE(c.plant_10, 0) as plant_10, 
                COALESCE(c.rqa, 0) as rqa, 
                COALESCE(c.hav, 0) as hav, 
                COALESCE(c.pgd, 0) as pgd,
                COALESCE(c.pp + c.plant_1 + c.plant_2 + c.plant_3 + c.plant_4 + c.plant_5 + c.plant_6 + c.plant_7 + c.plant_8 + c.plant_9 + c.plant_10 + c.rqa + c.hav + c.pgd, 0) as total_wip, 
                COALESCE(d.qty, 0) as fg, 
                COALESCE(e.os_mpp, 0) as os_mpp, 
                COALESCE(f.qty, 0) as os_sales_order');
            $this->db->from('mst_item a');
            $this->db->join('mst_item_line b', 'a.item_id = b.item_id');
            $this->db->join('mst_wip c', "a.item_id = c.product_no and c.p_month = '" . $filter_month . "' and c.p_year = '" . $filter_year . "' and c.revision = '" . $filter_revision . "'", "left");
            $this->db->join('fg_stock d', "a.item_id = d.product_no and d.p_month = '" . $filter_month . "' and d.p_year = '" . $filter_year . "' and d.revision = '" . $filter_revision . "'", "left");
            $this->db->join('mpp_os e', "a.item_id = e.product_no and e.p_month = '" . $filter_month . "' and e.p_year = '" . $filter_year . "' and e.revision = '" . $filter_revision . "'", "left");
            $this->db->join('sales_order_os f', "a.item_id = f.product_no and f.p_month = '" . $filter_month . "' and f.p_year = '" . $filter_year . "' and f.revision = '" . $filter_revision . "'", "left");
            if ($filter_line_no != "") {
                $this->db->where('b.line_no', $filter_line_no);
            }
            if ($filter_product_no != "") {
                $this->db->where('a.item_id', $filter_product_no);
            }
            $this->db->where('a.pfm_id', '06');
            $this->db->where('a.stscode_id', '01');
            $this->db->order_by('a.item_id', 'asc');
            $records = $this->db->get()->result_array();

            foreach ($records as $data) {
                $totalStock = ($data['total_wip'] + $data['fg'] + $data['os_mpp']);
                if ($data['fg'] == null) {
                    $fg = "0";
                } else {
                    $fg = $data['fg'];
                }

                $i = 1;
                $beginBalance = 0;
                $forecast = 0;
                $deliveryRate = 0;
                $ito = 0;
                $safetyStock = 0;
                $prodPlan = 0;
                $arrMonth = array();

                $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
                $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

                $dataForecast = $this->api->read('mst_forecast', [], ["product_no" => $data['item_id'], "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);
                $lines = $this->api->read('mst_item_line', [], ["item_id" => $data['item_id']]);

                $this->db->where('mstwos_assyno', $data['item_id']);
                $circuits = $this->db->count_all_results('wip_mst_wos');

                while ($monthStart < $monthEnd) {
                    $monthName = date('F Y', $monthStart);
                    $monthName2 = date('Y-m-01', $monthStart);
                    // $monthsPlus = strtolower(date('F', strtotime('+1 month', $monthStart)));
                    $start = strtotime(date('Y-m-01', $monthStart));
                    $finish = strtotime(date('Y-m-t', $monthStart));

                    $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
                    $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));

                    //HKW 1
                    $hkw = 0;
                    for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                        $working_date = date('Y-m-d', $z);

                        $this->db->select('remarks');
                        $this->db->from('working_calendar');
                        $this->db->where('working_date', $working_date);
                        $holiday = $this->db->get()->row();

                        if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                            if (@$holiday->remarks != null or @$holiday->remarks != "") {
                                $hkw += 0;
                            } else {
                                $hkw += 1;
                            }
                        } else {
                            $hkw += 0;
                        }
                    }

                    //HKW 2
                    $hkw2 = 0;
                    for ($x = $start2; $x <= $finish2; $x += (60 * 60 * 24)) {
                        $working_date2 = date('Y-m-d', $x);

                        $this->db->select('remarks');
                        $this->db->from('working_calendar');
                        $this->db->where('working_date', $working_date2);
                        $holiday2 = $this->db->get()->row();

                        if (date('w', $x) !== '0') {
                            if (@$holiday2->remarks != null or @$holiday2->remarks != "") {
                                $hkw2 += 0;
                            } else {
                                $hkw2 += 1;
                            }
                        } else {
                            $hkw2 += 0;
                        }
                    }

                    //Bulan Pertama - Keenam
                    if ($i == 1) {
                        $beginBalance = $totalStock - $data['os_sales_order'];
                        $forecast = @round($dataForecast->month_1);
                        $deliveryRate = @round($forecast / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);

                        if($data['st_sparepart'] == "t"){
                            $safetyStock = 0;
                        }else{
                            $safetyStock = @round(((@$data['item_manldtm'] + $config->fg_ss) / $hkw2) * $dataForecast->month_2);
                        }
                        
                        $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                    } else if ($i == 2) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecast = @round($dataForecast->month_2);
                        $deliveryRate = @round($forecast / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);

                        if($data['st_sparepart'] == "t"){
                            $safetyStock = 0;
                        }else{
                            $safetyStock = @round(((@$data['item_manldtm'] + $config->fg_ss) / $hkw2) * $dataForecast->month_3);
                        }

                        $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                    } elseif ($i == 3) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecast = @round($dataForecast->month_3);
                        $deliveryRate = @round($forecast / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);

                        if($data['st_sparepart'] == "t"){
                            $safetyStock = 0;
                        }else{
                            $safetyStock = @round(((@$data['item_manldtm'] + $config->fg_ss) / $hkw2) * $dataForecast->month_4);
                        }

                        $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                    } elseif ($i == 4) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecast = @round($dataForecast->month_4);
                        $deliveryRate = @round($forecast / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);

                        if($data['st_sparepart'] == "t"){
                            $safetyStock = 0;
                        }else{
                            $safetyStock = @round(((@$data['item_manldtm'] + $config->fg_ss) / $hkw2) * $dataForecast->month_5);
                        }

                        $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                    } elseif ($i == 5) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecast = @round($dataForecast->month_5);
                        $deliveryRate = @round($forecast / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);

                        if($data['st_sparepart'] == "t"){
                            $safetyStock = 0;
                        }else{
                            $safetyStock = @round(((@$data['item_manldtm'] + $config->fg_ss) / $hkw2) * $dataForecast->month_6);
                        }

                        $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                    } elseif ($i == 6) {
                        $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                        $forecast = @round($dataForecast->month_6);
                        $deliveryRate = @round($forecast / $hkw);
                        $ito = @round($beginBalance / $deliveryRate);

                        if($data['st_sparepart'] == "t"){
                            $safetyStock = 0;
                        }else{
                            $safetyStock = @round(((@$data['item_manldtm'] + $config->fg_ss) / $hkw2) * $dataForecast->month_6);
                        }

                        $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                    }

                    if ($prodPlan <= 0) {
                        $prodPlanFinal = 0;
                    } else {
                        $prodPlanFinal = $prodPlan;
                    }

                    $arrMonth[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "line_no" => @$lines->line_no,
                        "customer_id" => $data['customer_id'],
                        "product_no" => $data['item_id'],
                        "product_name" => $data['item_name'],
                        "circuit_no" => $circuits,
                        "ltpp_month" => strtoupper($monthName),
                        "ltpp_month2" => $monthName2,
                        "hkw" => "$hkw2",
                        "begin_balance" => "$beginBalance",
                        "ito" => "$ito",
                        "forecast" => "$forecast",
                        "delivery_rate" => "$deliveryRate",
                        "safety_stock" => "$safetyStock",
                        "prod_plan" => "$prodPlanFinal"

                    );

                    $monthStart = strtotime("+1 month", $monthStart);
                    $i++;

                    $beginBalance = $beginBalance;
                    $forecast = $forecast;
                    $deliveryRate = $deliveryRate;
                    $ito = $ito;
                    $safetyStock = $safetyStock;
                    $prodPlan = $prodPlanFinal;
                    $balance = (($prodPlan + $beginBalance) - $forecast);
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "line_no" => @$lines->line_no,
                    "customer_id" => $data['customer_id'],
                    "product_no" => $data['item_id'],
                    "product_name" => $data['item_name'],
                    "circuit_no" => $circuits,
                    "wip_month" => strtoupper($monthBack),
                    "pp" => $data['pp'],
                    "plant_1" => $data['plant_1'],
                    "plant_2" => $data['plant_2'],
                    "plant_3" => $data['plant_3'],
                    "plant_4" => $data['plant_4'],
                    "plant_5" => $data['plant_5'],
                    "plant_6" => $data['plant_6'],
                    "plant_7" => $data['plant_7'],
                    "plant_8" => $data['plant_8'],
                    "plant_9" => $data['plant_9'],
                    "plant_10" => $data['plant_10'],
                    "rqa" => $data['rqa'],
                    "hav" => $data['hav'],
                    "fg" => $fg,
                    "os_mpp" => $data['os_mpp'],
                    "total_stock" => "$totalStock",
                    "os_sales_order" => $data['os_sales_order'],
                    "balance" => "$balance",
                    "details" => $arrMonth
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getdatacopy(){
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        $monthBack = date('F Y', strtotime('-1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        //Configuration Planning
        $this->db->select('*');
        $this->db->from("planning_config");
        $config = $this->db->get()->row();

        // $maxRev = $this->api->query("SELECT max(revision) as revision 
        //     FROM generate_mps 
        //     WHERE p_month = '$filter_month' 
        //     and p_year = '$filter_year'");
        
        $revision = ($filter_revision + 1);

        $generate_mps = $this->api->reads("generate_mps", [
            "line_no" => $filter_line_no,
            "product_no" => $filter_product_no
        ], [
            "p_month" => $filter_month, 
            "p_year" => $filter_year, 
            "revision" => $filter_revision
        ]);

        $arr = array();
        foreach ($generate_mps as $mps) {
            $mst_wip = $this->api->read("mst_wip", [], ["product_no" => $mps->product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $revision]);
            $fg_stock = $this->api->read("fg_stock", [], ["product_no" => $mps->product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $revision]);
            $mpp_os = $this->api->read("mpp_os", [], ["product_no" => $mps->product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $revision]);
            $sales_order_os = $this->api->read("sales_order_os", [], ["product_no" => $mps->product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $revision]);
            $forecast_data = $this->api->read("mst_forecast", [], ["product_no" => $mps->product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $revision]);
            if(empty($forecast_data)){
                $dataForecast = $this->api->read("mst_forecast", [], ["product_no" => $mps->product_no, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);
            }else{
                $dataForecast = $forecast_data;
            }
            
            $mst_item = $this->api->read("mst_item", [], ["item_id" => $mps->product_no]);

            if(empty($mst_wip)){
                $pp = $mps->pp;
                $plant_1 = $mps->plant_1;
                $plant_2 = $mps->plant_2;
                $plant_3 = $mps->plant_3;
                $plant_4 = $mps->plant_4;
                $plant_5 = $mps->plant_5;
                $plant_6 = $mps->plant_6;
                $plant_7 = $mps->plant_7;
                $plant_8 = $mps->plant_8;
                $plant_9 = $mps->plant_9;
                $plant_10 = $mps->plant_10;
                $rqa = $mps->rqa;
                $hav = $mps->hav;
                $pgd = 0;
            }else{
                $pp = @$mst_wip->pp;
                $plant_1 = @$mst_wip->plant_1;
                $plant_2 = @$mst_wip->plant_2;
                $plant_3 = @$mst_wip->plant_3;
                $plant_4 = @$mst_wip->plant_4;
                $plant_5 = @$mst_wip->plant_5;
                $plant_6 = @$mst_wip->plant_6;
                $plant_7 = @$mst_wip->plant_7;
                $plant_8 = @$mst_wip->plant_8;
                $plant_9 = @$mst_wip->plant_9;
                $plant_10 = @$mst_wip->plant_10;
                $rqa = @$mst_wip->rqa;
                $hav = @$mst_wip->hav;
                $pgd = @$mst_wip->pgd;
            }

            if(empty($fg_stock)){
                $fg = $mps->fg;
            }else{
                $fg = $fg_stock->qty;
            }

            if(empty($mpp_os)){
                $os_mpp = $mps->os_mpp;
            }else{
                $os_mpp = $mst_wip->os_mpp;
            }

            if(empty($sales_order_os)){
                $os_sales_order = $mps->os_sales_order;
            }else{
                $os_sales_order = $sales_order_os->qty;
            }

            $total_wip = ($pp + $plant_1 + $plant_2 + $plant_3 + $plant_4 + $plant_5 + $plant_6 + $plant_7 + $plant_8 + $plant_9 + $plant_10 + $rqa + $hav + $pgd);
            $totalStock = ($total_wip + $fg + $os_mpp);
            $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
            $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

            $i = 1;
            $beginBalance = 0;
            $forecast = 0;
            $deliveryRate = 0;
            $ito = 0;
            $safetyStock = 0;
            $prodPlan = 0;
            $arrMonth = array();

            while ($monthStart < $monthEnd) {
                $monthName = date('F Y', $monthStart);
                $monthName2 = date('Y-m-01', $monthStart);
                // $monthsPlus = strtolower(date('F', strtotime('+1 month', $monthStart)));
                $start = strtotime(date('Y-m-01', $monthStart));
                $finish = strtotime(date('Y-m-t', $monthStart));

                $start2 = strtotime(date('Y-m-01', strtotime('+1 month', $monthStart)));
                $finish2 = strtotime(date('Y-m-t', strtotime('+1 month', $monthStart)));

                //HKW 1
                $hkw = 0;
                for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $z);

                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date);
                    $holiday = $this->db->get()->row();

                    if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            $hkw += 0;
                        } else {
                            $hkw += 1;
                        }
                    } else {
                        $hkw += 0;
                    }
                }

                //HKW 2
                $hkw2 = 0;
                for ($x = $start2; $x <= $finish2; $x += (60 * 60 * 24)) {
                    $working_date2 = date('Y-m-d', $x);

                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date2);
                    $holiday2 = $this->db->get()->row();

                    if (date('w', $x) !== '0') {
                        if (@$holiday2->remarks != null or @$holiday2->remarks != "") {
                            $hkw2 += 0;
                        } else {
                            $hkw2 += 1;
                        }
                    } else {
                        $hkw2 += 0;
                    }
                }

                //Bulan Pertama - Keenam
                if ($i == 1) {
                    $beginBalance = $totalStock - $os_sales_order;
                    $forecast = @round($dataForecast->month_1);
                    $deliveryRate = @round($forecast / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);

                    if($mst_item->st_sparepart == "t"){
                        $safetyStock = 0;
                    }else{
                        $safetyStock = @round(((@$mst_item->item_manldtm + $config->fg_ss) / $hkw2) * $dataForecast->month_2);
                    }

                    $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                } else if ($i == 2) {
                    $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                    $forecast = @round($dataForecast->month_2);
                    $deliveryRate = @round($forecast / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);

                    if($mst_item->st_sparepart == "t"){
                        $safetyStock = 0;
                    }else{
                        $safetyStock = @round(((@$mst_item->item_manldtm + $config->fg_ss) / $hkw2) * $dataForecast->month_3);
                    }

                    $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                } elseif ($i == 3) {
                    $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                    $forecast = @round($dataForecast->month_3);
                    $deliveryRate = @round($forecast / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);

                    if($mst_item->st_sparepart == "t"){
                        $safetyStock = 0;
                    }else{
                        $safetyStock = @round(((@$mst_item->item_manldtm + $config->fg_ss) / $hkw2) * $dataForecast->month_4);
                    }

                    $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                } elseif ($i == 4) {
                    $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                    $forecast = @round($dataForecast->month_4);
                    $deliveryRate = @round($forecast / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);

                    if($mst_item->st_sparepart == "t"){
                        $safetyStock = 0;
                    }else{
                        $safetyStock = @round(((@$mst_item->item_manldtm + $config->fg_ss) / $hkw2) * $dataForecast->month_5);
                    }

                    $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                } elseif ($i == 5) {
                    $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                    $forecast = @round($dataForecast->month_5);
                    $deliveryRate = @round($forecast / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);

                    if($mst_item->st_sparepart == "t"){
                        $safetyStock = 0;
                    }else{
                        $safetyStock = @round(((@$mst_item->item_manldtm + $config->fg_ss) / $hkw2) * $dataForecast->month_6);
                    }

                    $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                } elseif ($i == 6) {
                    $beginBalance = (($prodPlan + $beginBalance) - $forecast);
                    $forecast = @round($dataForecast->month_6);
                    $deliveryRate = @round($forecast / $hkw);
                    $ito = @round($beginBalance / $deliveryRate);

                    if($mst_item->st_sparepart == "t"){
                        $safetyStock = 0;
                    }else{
                        $safetyStock = @round(((@$mst_item->item_manldtm + $config->fg_ss) / $hkw2) * $dataForecast->month_6);
                    }

                    $prodPlan = @round(($forecast + $safetyStock) - $beginBalance);
                }

                if ($prodPlan <= 0) {
                    $prodPlanFinal = 0;
                } else {
                    $prodPlanFinal = $prodPlan;
                }

                $arrMonth[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $revision,
                    "line_no" => $mps->line_no,
                    "customer_id" => $mps->customer_id,
                    "product_no" => $mps->product_no,
                    "product_name" => $mps->product_name,
                    "circuit_no" => $mps->circuit_no,
                    "ltpp_month" => strtoupper($monthName),
                    "ltpp_month2" => $monthName2,
                    "hkw" => "$hkw2",
                    "begin_balance" => "$beginBalance",
                    "ito" => "$ito",
                    "forecast" => "$forecast",
                    "delivery_rate" => "$deliveryRate",
                    "safety_stock" => "$safetyStock",
                    "prod_plan" => "$prodPlanFinal"

                );

                $monthStart = strtotime("+1 month", $monthStart);
                $i++;

                $beginBalance = $beginBalance;
                $forecast = $forecast;
                $deliveryRate = $deliveryRate;
                $ito = $ito;
                $safetyStock = $safetyStock;
                $prodPlan = $prodPlanFinal;
                $balance = (($prodPlan + $beginBalance) - $forecast);
            }
            
            $arr[] = array(
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $revision,
                "line_no" => $mps->line_no,
                "customer_id" => $mps->customer_id,
                "product_no" => $mps->product_no,
                "product_name" => $mps->product_name,
                "circuit_no" => $mps->circuit_no,
                "wip_month" => $mps->wip_month,
                "pp" => $pp,
                "plant_1" => $plant_1,
                "plant_2" => $plant_2,
                "plant_3" => $plant_3,
                "plant_4" => $plant_4,
                "plant_5" => $plant_5,
                "plant_6" => $plant_6,
                "plant_7" => $plant_7,
                "plant_8" => $plant_8,
                "plant_9" => $plant_9,
                "plant_10" => $plant_10,
                "rqa" => $rqa,
                "hav" => $hav,
                "fg" => $fg,
                "os_mpp" => $os_mpp,
                "total_stock" => $totalStock,
                "os_sales_order" => $os_sales_order,
                "balance" => $balance,
                "details" => $arrMonth
            );
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function revision()
    {
        $filter_month = $this->input->post('filter_month');
        $filter_year = $this->input->post('filter_year');

        $this->db->select('revision');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('deleted_is', 0);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $this->db->limit(1);
        $record = $this->db->get()->row();
        echo @$record->revision ? $record->revision : 0;
    }

    public function checkForecast()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mst_forecast');
        $this->db->where("(approved_to = '' or approved_to is null)");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkFg()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('fg_stock');
        $this->db->where("(approved_to = '' or approved_to is null)");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOs()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_order');
        $this->db->where("(approved_to = '' or approved_to is null)");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOstSo()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_order_os');
        $this->db->where("(approved_to = '' or approved_to is null)");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkStockWip()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mst_wip');
        $this->db->where("(approved_to = '' or approved_to is null)");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOstMpp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('mpp_os');
        $this->db->where("(approved_to = '' or approved_to is null)");
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('revision', $filter_revision);
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkCalendar()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
        $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

        $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
        $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

        $html = "";
        $no = 1;
        while ($monthStart < $monthEnd) {
            $monthName = date('m/Y', $monthStart);
            $start = strtotime(date('Y-m-01', $monthStart));
            $finish = strtotime(date('Y-m-t', $monthStart));

            //HKW 1
            $hkw = 0;
            for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $z);

                $this->db->select('remarks');
                $this->db->from('working_calendar');
                $this->db->where('working_date', $working_date);
                $holiday = $this->db->get()->row();

                if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                    if (@$holiday->remarks != null or @$holiday->remarks != "") {
                        $hkw += 0;
                    } else {
                        $hkw += 1;
                    }
                } else {
                    $hkw += 0;
                }
            }

            $html .= '  <div style="margin:20px;">
                            HKW ' . $no . ' : ' . $monthName . ' : <b>' . $hkw . '</b>
                        </div>';

            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        echo $html;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $postDetails = $post['details'];

            $items = $this->api->read("mst_item", [], ["item_id" => $post['product_no']]);
            if(@$items->lot == 0){
                $lot = 1;
            }else{
                $lot = @$items->lot;
            }

            foreach ($postDetails as $postDetail) {
                $generateMpsDetails = $this->api->reads('generate_mps_detail', [], [
                    "p_month" => $postDetail['p_month'],
                    "p_year" => $postDetail['p_year'],
                    "revision" => $postDetail['revision'],
                    "product_no" => $postDetail['product_no'],
                    "ltpp_month" => $postDetail['ltpp_month']
                ]);

                $postFinalDetail = array(
                    "p_month" => $postDetail['p_month'],
                    "p_year" => $postDetail['p_year'],
                    "revision" => $postDetail['revision'],
                    "line_no" => $postDetail['line_no'],
                    "customer_id" => $postDetail['customer_id'],
                    "product_no" => $postDetail['product_no'],
                    "product_name" => $postDetail['product_name'],
                    "circuit_no" => $postDetail['circuit_no'],
                    "ltpp_month" => $postDetail['ltpp_month'],
                    "ltpp_month2" => $postDetail['ltpp_month2'],
                    "hkw" => $postDetail['hkw'],
                    "begin_balance" => $postDetail['begin_balance'],
                    "ito" => $postDetail['ito'],
                    "forecast" => $postDetail['forecast'],
                    "delivery_rate" => $postDetail['delivery_rate'],
                    "safety_stock" => $postDetail['safety_stock'],
                    "need" => $postDetail['prod_plan'],
                    "prod_plan" => $postDetail['prod_plan'],
                    // "prod_plan" => (ceil($postDetail['prod_plan'] / @$lot) * @$lot)
                );

                if (count($generateMpsDetails) > 0) {
                    $send = $this->api->update('generate_mps_detail', [
                        "p_month" => $postDetail['p_month'],
                        "p_year" => $postDetail['p_year'],
                        "revision" => $postDetail['revision'],
                        "product_no" => $postDetail['product_no'],
                        "ltpp_month" => $postDetail['ltpp_month']
                    ], $postFinalDetail);
                } else {
                    $this->api->create('generate_mps_detail', $postFinalDetail, "MPSD", "generate_mps");
                }
            }

            $generateMps = $this->api->reads('generate_mps', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => $post['product_no'],
                "wip_month" => $post['wip_month']
            ]);

            $postFinal = array(
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "line_no" => $post['line_no'],
                "customer_id" => $post['customer_id'],
                "product_no" => $post['product_no'],
                "product_name" => $post['product_name'],
                "circuit_no" => $post['circuit_no'],
                "wip_month" => $post['wip_month'],
                "pp" => $post['pp'],
                "plant_1" => $post['plant_1'],
                "plant_2" => $post['plant_2'],
                "plant_3" => $post['plant_3'],
                "plant_4" => $post['plant_4'],
                "plant_5" => $post['plant_5'],
                "plant_6" => $post['plant_6'],
                "plant_7" => $post['plant_7'],
                "plant_8" => $post['plant_8'],
                "plant_9" => $post['plant_9'],
                "plant_10" => $post['plant_10'],
                "rqa" => $post['rqa'],
                "hav" => $post['hav'],
                "fg" => $post['fg'],
                "os_mpp" => $post['os_mpp'],
                "os_sales_order" => $post['os_sales_order'],
                "total_stock" => $post['total_stock'],
                "balance" => $post['balance']
            );

            if (count($generateMps) > 0) {
                $send   = $this->api->update('generate_mps', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "product_no" => $post['product_no'],
                    "wip_month" => $post['wip_month']
                ], $postFinal);
                $result = json_decode($send);

                if ($result->theme == "success") {
                    echo json_encode(array("title" => "Good Job", "message" => $post['product_no'] . " | " . $post['product_name'] . " | Data Updated Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['product_no'] . " | " . $post['product_name'] . " | Data Unupdate", "theme" => "error"));
                }
            } else {
                $send = $this->api->create('generate_mps', $postFinal, "MPS", "generate_mps");
                $result = json_decode($send);

                if ($result->theme == "success") {
                    echo json_encode(array("title" => "Good Job", "message" => $post['product_no'] . " | " . $post['product_name'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $post['product_no'] . " | " . $post['product_name'] . " | Data Unsaved", "theme" => "error"));
                }
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/generate_mps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/generate_mps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/generate_mps.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function monthName($id)
    {
        if ($id == "01") {
            return "JANUARY";
        } elseif ($id == "02") {
            return "FEBRUARY";
        } elseif ($id == "03") {
            return "MARCH";
        } elseif ($id == "04") {
            return "APRIL";
        } elseif ($id == "05") {
            return "MAY";
        } elseif ($id == "06") {
            return "JUNE";
        } elseif ($id == "07") {
            return "JULY";
        } elseif ($id == "08") {
            return "AUGUST";
        } elseif ($id == "09") {
            return "SEPTEMBER";
        } elseif ($id == "10") {
            return "OCTOBER";
        } elseif ($id == "11") {
            return "NOVEMBER";
        } elseif ($id == "12") {
            return "DECEMBER";
        }
    }

    public function push_data_check(){
    	//Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Line No
        $this->db->select('*');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('line_no', $filter_line_no);
        $this->db->like('customer_id', $filter_customer_id);
        $this->db->like('revision', $filter_revision);
        $this->db->like('product_no', $filter_product_no);
        $this->db->where('deleted_is', 0);
        $records = $this->db->get()->result_array();

        if(count($records) > 0){
        	echo json_encode(array("title" => "Success", "message" => "Data Already", "theme" => "success"));
        }else{
        	echo json_encode(array("title" => "Not Ready", "message" => "Data MPS Not Found, Please Generate First", "theme" => "error"));
        }
    }

    public function push_data_header(){
        if($this->input->get()){
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            //Perhitungan HKW
            $varBackYear = date('Y', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));
            $varBackMonth = date('m', strtotime('+1 month', strtotime($filter_year . "-" . $filter_month . "-01")));

            $monthStart = strtotime($filter_year . "-" . $filter_month . "-01");
            $monthEnd =  strtotime(date('Y-m-d', strtotime('+5 month', strtotime($varBackYear . "-" . $varBackMonth . "-01"))));

            $no = 1;
            while ($monthStart < $monthEnd) {
                $start = strtotime(date('Y-m-01', $monthStart));
                $finish = strtotime(date('Y-m-t', $monthStart));

                //HKW 1
                $hkw = 0;
                for ($z = $start; $z <= $finish; $z += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $z);

                    $this->db->select('remarks');
                    $this->db->from('working_calendar');
                    $this->db->where('working_date', $working_date);
                    $holiday = $this->db->get()->row();

                    if (date('w', $z) !== '0' && date('w', $z) !== '6') {
                        if (@$holiday->remarks != null or @$holiday->remarks != "") {
                            $hkw += 0;
                        } else {
                            $hkw += 1;
                        }
                    } else {
                        $hkw += 0;
                    }
                }

                $arrHkw[] = array(
                    "hkw_".$no => $hkw
                );

                $no++;
                $monthStart = strtotime("+1 month", $monthStart);
            }

            $this->dummy = $this->load->database('dummy', TRUE);

            $this->dummy->select('*');
            $this->dummy->from("ltpp_header");
            $this->dummy->where("period", $filter_year . $filter_month);
            $this->dummy->where("rev", $filter_revision);
            $ltpp_header = $this->dummy->get()->result_array();

            // if(count($ltpp_header) > 0){
            // 	$this->dummy->delete("ltpp_generate", ["period" => $filter_year . $filter_month, "rev" => $filter_revision]);
            //     $this->dummy->delete("ltpp_generate_rev", ["period" => $filter_year . $filter_month, "rev" => $filter_revision]);
            //     $this->dummy->delete("ltpp_header", ["period" => $filter_year . $filter_month, "rev" => $filter_revision]);
            // }

            $arrData = array(
                "ltpp_doc" => $this->ltpp_no($filter_month, $filter_year),
                "period" => $filter_year . $filter_month,
                "date_period" => $filter_year ."-".$filter_month."-01",
                "lt" => "8",
                "hkw_1" => $arrHkw[0]['hkw_1'],
                "hkw_2" => $arrHkw[1]['hkw_2'],
                "hkw_3" => $arrHkw[2]['hkw_3'],
                "hkw_4" => $arrHkw[3]['hkw_4'],
                "hkw_5" => $arrHkw[4]['hkw_5'],
                "hkw_6" => $arrHkw[5]['hkw_6'],
                "rev" => $filter_revision,
                "fc_m4" => "0",
                "upload_user" => $this->session->username,
                "upload_time" => date("Y-m-d H:i:s"),
                "status" => "",
                "approval_sts" => "",
                "remark" => "",
            );

            if(count($ltpp_header) == 0){
                $this->dummy->insert("ltpp_header", $arrData);
                die(json_encode($arrData));
            }else{
                die(json_encode($ltpp_header));
            }
        }
    }

    public function push_data(){
        if($this->input->get()){
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));

            $this->db->select('a.*');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_detail b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.product_no = b.product_no');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->where("(a.pp > 0 or a.plant_1 > 0 or a.plant_2 > 0 or a.plant_3 > 0 or a.plant_4 > 0 or a.plant_5 > 0 or a.plant_6 > 0 or a.plant_7 > 0 or a.plant_8 > 0 or a.plant_9 > 0 or a.plant_10 > 0 or a.rqa > 0 or a.hav > 0 or a.fg > 0 or a.os_mpp > 0 or a.os_sales_order > 0 or a.total_stock > 0 or a.balance > 0 or b.begin_balance > 0 or b.ito > 0 or b.forecast > 0 or b.delivery_rate > 0 or b.safety_stock > 0 or b.prod_plan > 0)");
            $this->db->where('a.deleted_is', 0);
            $this->db->like('a.line_no', $filter_line_no);
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.product_no', $filter_product_no);
            $this->db->group_by('a.product_no');
            $records = $this->db->get()->result_array();

            $jsonData = array();
            foreach ($records as $data) {
                $this->db->select('a.*, b.xbar, c.st_sparepart');
                $this->db->from('generate_mps_detail a');
                $this->db->join('sales_order b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.product_no = b.product_no', 'left');
                $this->db->join('mst_item c', 'a.product_no = c.item_id');
                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                $this->db->where('a.line_no', $data['line_no']);
                $this->db->where('a.product_no', $data['product_no']);
                $this->db->where('a.deleted_is', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                $jsonDataDetail = array();
                foreach ($details2 as $detail2) {
                    if ($nodetail == 1) {
                        if ($detail2['xbar'] == "") {
                            $xbarQty = 0;
                        } else {
                            $xbarQty = $detail2['xbar'];
                        }
                        $xbar2 = $xbarQty;
                    } else {
                        $xbar2 = 0;
                    }

                    if ($detail2['st_sparepart'] == "t") {
                        $safety_stock = 0;
                    } else {
                        $safety_stock = $detail2['safety_stock'];
                    }

                    $jsonDataDetail[] = array(
                        "begin_balance_".$nodetail => $detail2['begin_balance'],
                        "ito_".$nodetail => $detail2['ito'],
                        "xbar_".$nodetail => $xbar2,
                        "forecast_".$nodetail => $detail2['forecast'],
                        "delivery_rate_".$nodetail => $detail2['delivery_rate'],
                        "safety_stock_".$nodetail => $safety_stock,
                        "prod_plan_".$nodetail => $detail2['prod_plan']
                    );

                    $nodetail++;
                }

                $jsonData[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "line_no" => $data['line_no'],
                    "product_no" => trim($data['product_no']),
                    "product_name" => trim($data['product_name']),
                    "circuit_no" => $data['circuit_no'],
                    "pp" => $data['pp'],
                    "p1" => $data['plant_1'],
                    "p2" => $data['plant_2'],
                    "p3" => $data['plant_3'],
                    "p4" => $data['plant_4'],
                    "p5" => $data['plant_5'],
                    "p6" => $data['plant_6'],
                    "p7" => $data['plant_7'],
                    "p8" => $data['plant_8'],
                    "p9" => $data['plant_9'],
                    "p10" => $data['plant_10'],
                    "stock_tf" => ($data['plant_1'] + $data['plant_2'] + $data['plant_3'] + $data['plant_4'] + $data['plant_5'] + $data['plant_6'] + $data['plant_7'] + $data['plant_8'] + $data['plant_9'] + $data['plant_10']),
                    "rqa" => $data['rqa'],
                    "hav" => $data['hav'],
                    "fg" => $data['fg'],
                    "os_mpp" => $data['os_mpp'],
                    "total_stock" => $data['total_stock'],
                    "os_sales_order" => $data['os_sales_order'],
                    "balance" => $data['balance'],
                    "details" => $jsonDataDetail
                );
            }

            $jsonData['total'] = count($jsonData);
            die(json_encode($jsonData));
        }
    }

    public function push_data_create(){
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $header = $this->input->post('header');

            $sales_order = $this->api->read('sales_order', [], [
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                "product_no" => $data['product_no']
            ]);

            $this->dummy = $this->load->database('dummy', TRUE);

            $this->dummy->select('COALESCE(lotqty, 0) as lotqty');
            $this->dummy->from("mst_item");
            $this->dummy->where("item_id", trim($data['product_no']));
            $mst_item = $this->dummy->get()->row();

            $this->dummy->select('*');
            $this->dummy->from("ltpp_generate");
            $this->dummy->where("period", $header[0]['period']);
            $this->dummy->where("assy_no", $data['product_no']);
            $this->dummy->where("rev", $header[0]['rev']);
            $ltpp_generate = $this->dummy->get()->result_array();

            if(count($ltpp_generate) > 0){
                $ltpp_doc = $ltpp_generate[0]['ltpp_doc'];
            }else{
                $ltpp_doc = $header[0]['ltpp_doc'];
            }

            if(@is_nan(ceil($data['details'][0]['prod_plan_1'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_1 = 0;
            }else{
            	$prod_plan_1 = @(ceil($data['details'][0]['prod_plan_1'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][1]['prod_plan_2'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_2 = 0;
            }else{
            	$prod_plan_2 = @(ceil($data['details'][1]['prod_plan_2'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][2]['prod_plan_3'] / $mst_item->lotqty) * $mst_item->lotqty) ){
            	$prod_plan_3 = 0;
            }else{
            	$prod_plan_3 = @(ceil($data['details'][2]['prod_plan_3'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][3]['prod_plan_4'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_4 = 0;
            }else{
            	$prod_plan_4 = @(ceil($data['details'][3]['prod_plan_4'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][4]['prod_plan_5'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_5 = 0;
            }else{
            	$prod_plan_5 = @(ceil($data['details'][4]['prod_plan_5'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            if(@is_nan(ceil($data['details'][5]['prod_plan_6'] / $mst_item->lotqty) * $mst_item->lotqty)){
            	$prod_plan_6 = 0;
            }else{
            	$prod_plan_6 = @(ceil($data['details'][5]['prod_plan_6'] / $mst_item->lotqty) * $mst_item->lotqty);;
            }

            $postFinal = array(
                "ltpp_doc" => $ltpp_doc,
                "period" => $header[0]['period'],
                "date_period" => $header[0]['date_period'],
                "lt" => $header[0]['lt'],
                "hkw_1" => $header[0]['hkw_1'],
                "hkw_2" => $header[0]['hkw_2'],
                "hkw_3" => $header[0]['hkw_3'],
                "hkw_4" => $header[0]['hkw_4'],
                "hkw_5" => $header[0]['hkw_5'],
                "hkw_6" => $header[0]['hkw_6'],
                "fc_m4" => $header[0]['fc_m4'],
                "rev" => $header[0]['rev'],
                "assy_no" => trim($data['product_no']),
                "item_name" => trim($data['product_name']),
                "cct" => $data['circuit_no'],
                "pp" => $data['pp'],
                "p1" => $data['p1'],
                "p2" => $data['p2'],
                "p3" => $data['p3'],
                "p4" => $data['p4'],
                "p5" => $data['p5'],
                "p6" => $data['p6'],
                "p7" => $data['p7'],
                "p8" => $data['p8'],
                "p9" => $data['p9'],
                "p10" => $data['p10'],
                "rep_qa" => $data['rqa'],
                "hav" => $data['hav'],
                "fg" => $data['fg'],
                "ost_mpp" => $data['os_mpp'],
                "t_stock" => $data['total_stock'],
                "fc1" => $data['details'][0]['forecast_1'],
                "fc2" => $data['details'][1]['forecast_2'],
                "fc3" => $data['details'][2]['forecast_3'],
                "fc4" => $data['details'][3]['forecast_4'],
                "fc5" => $data['details'][4]['forecast_5'],
                "fc6" => $data['details'][5]['forecast_6'],
                "t_fc" => ($data['details'][0]['forecast_1'] + $data['details'][1]['forecast_2'] + $data['details'][2]['forecast_3'] + $data['details'][3]['forecast_4'] + $data['details'][4]['forecast_5'] + $data['details'][5]['forecast_6']),
                "so_0" => @$sales_order->qty_1,
                "so_1" => @$sales_order->qty_2,
                "so_2" => @$sales_order->qty_3,
                "so_3" => @$sales_order->qty_4,
                "so_4" => @$sales_order->qty_5,
                "so_5" => @$sales_order->qty_6,
                "so_6" => "0.0000",
                "bal_1" => $data['details'][0]['begin_balance_1'],
                "bal_2" => $data['details'][1]['begin_balance_2'],
                "bal_3" => $data['details'][2]['begin_balance_3'],
                "bal_4" => $data['details'][3]['begin_balance_4'],
                "bal_5" => $data['details'][4]['begin_balance_5'],
                "bal_6" => $data['details'][5]['begin_balance_6'],
                "ito_1" => $data['details'][0]['ito_1'],
                "ito_2" => $data['details'][1]['ito_2'],
                "ito_3" => $data['details'][2]['ito_3'],
                "ito_4" => $data['details'][3]['ito_4'],
                "ito_5" => $data['details'][4]['ito_5'],
                "ito_6" => $data['details'][5]['ito_6'],
                "del_rate_1" => $data['details'][0]['delivery_rate_1'],
                "del_rate_2" => $data['details'][1]['delivery_rate_2'],
                "del_rate_3" => $data['details'][2]['delivery_rate_3'],
                "del_rate_4" => $data['details'][3]['delivery_rate_4'],
                "del_rate_5" => $data['details'][4]['delivery_rate_5'],
                "del_rate_6" => $data['details'][5]['delivery_rate_6'],
                "s_stock_1" => $data['details'][0]['safety_stock_1'],
                "s_stock_2" => $data['details'][1]['safety_stock_2'],
                "s_stock_3" => $data['details'][2]['safety_stock_3'],
                "s_stock_4" => $data['details'][3]['safety_stock_4'],
                "s_stock_5" => $data['details'][4]['safety_stock_5'],
                "s_stock_6" => $data['details'][5]['safety_stock_6'],
                // "prod_plan_1" => $prod_plan_1,
                // "prod_plan_2" => $prod_plan_2,
                // "prod_plan_3" => $prod_plan_3,
                // "prod_plan_4" => $prod_plan_4,
                // "prod_plan_5" => $prod_plan_5,
                // "prod_plan_6" => $prod_plan_6,
                "prod_plan_1" => $data['details'][0]['prod_plan_1'],
                "prod_plan_2" => $data['details'][1]['prod_plan_2'],
                "prod_plan_3" => $data['details'][2]['prod_plan_3'],
                "prod_plan_4" => $data['details'][3]['prod_plan_4'],
                "prod_plan_5" => $data['details'][4]['prod_plan_5'],
                "prod_plan_6" => $data['details'][5]['prod_plan_6'],
                "need_1" => $data['details'][0]['prod_plan_1'],
                "need_2" => $data['details'][1]['prod_plan_2'],
                "need_3" => $data['details'][2]['prod_plan_3'],
                "need_4" => $data['details'][3]['prod_plan_4'],
                "need_5" => $data['details'][4]['prod_plan_5'],
                "need_6" => $data['details'][5]['prod_plan_6'],
                "bal_end" => $data['balance'],
                "ltpp_date" => date("Y-m-d"),
                "notes" => "",
            );

            $postFinal_rev = array(
                "ltpp_doc" => $ltpp_doc,
                "period" => $header[0]['period'],
                "date_period" => $header[0]['date_period'],
                "lt" => $header[0]['lt'],
                "hkw_1" => $header[0]['hkw_1'],
                "hkw_2" => $header[0]['hkw_2'],
                "hkw_3" => $header[0]['hkw_3'],
                "hkw_4" => $header[0]['hkw_4'],
                "hkw_5" => $header[0]['hkw_5'],
                "hkw_6" => $header[0]['hkw_6'],
                "fc_m4" => $header[0]['fc_m4'],
                "rev" => $header[0]['rev'],
                "assy_no" => trim($data['product_no']),
                "item_name" => trim($data['product_name']),
                "cct" => $data['circuit_no'],
                "pp" => $data['pp'],
                "p1" => $data['p1'],
                "p2" => $data['p2'],
                "p3" => $data['p3'],
                "p4" => $data['p4'],
                "p5" => $data['p5'],
                "p6" => $data['p6'],
                "p7" => $data['p7'],
                "p8" => $data['p8'],
                "p9" => $data['p9'],
                "p10" => $data['p10'],
                "rep_qa" => $data['rqa'],
                "hav" => $data['hav'],
                "fg" => $data['fg'],
                "ost_mpp" => $data['os_mpp'],
                "t_stock" => $data['total_stock'],
                "fc1" => $data['details'][0]['forecast_1'],
                "fc2" => $data['details'][1]['forecast_2'],
                "fc3" => $data['details'][2]['forecast_3'],
                "fc4" => $data['details'][3]['forecast_4'],
                "fc5" => $data['details'][4]['forecast_5'],
                "fc6" => $data['details'][5]['forecast_6'],
                "t_fc" => ($data['details'][0]['forecast_1'] + $data['details'][1]['forecast_2'] + $data['details'][2]['forecast_3'] + $data['details'][3]['forecast_4'] + $data['details'][4]['forecast_5'] + $data['details'][5]['forecast_6']),
                "so_0" => @$sales_order->qty_1,
                "so_1" => @$sales_order->qty_2,
                "so_2" => @$sales_order->qty_3,
                "so_3" => @$sales_order->qty_4,
                "so_4" => @$sales_order->qty_5,
                "so_5" => @$sales_order->qty_6,
                "so_6" => "0.0000",
                "bal_1" => $data['details'][0]['begin_balance_1'],
                "bal_2" => $data['details'][1]['begin_balance_2'],
                "bal_3" => $data['details'][2]['begin_balance_3'],
                "bal_4" => $data['details'][3]['begin_balance_4'],
                "bal_5" => $data['details'][4]['begin_balance_5'],
                "bal_6" => $data['details'][5]['begin_balance_6'],
                "ito_1" => $data['details'][0]['ito_1'],
                "ito_2" => $data['details'][1]['ito_2'],
                "ito_3" => $data['details'][2]['ito_3'],
                "ito_4" => $data['details'][3]['ito_4'],
                "ito_5" => $data['details'][4]['ito_5'],
                "ito_6" => $data['details'][5]['ito_6'],
                "del_rate_1" => $data['details'][0]['delivery_rate_1'],
                "del_rate_2" => $data['details'][1]['delivery_rate_2'],
                "del_rate_3" => $data['details'][2]['delivery_rate_3'],
                "del_rate_4" => $data['details'][3]['delivery_rate_4'],
                "del_rate_5" => $data['details'][4]['delivery_rate_5'],
                "del_rate_6" => $data['details'][5]['delivery_rate_6'],
                "s_stock_1" => $data['details'][0]['safety_stock_1'],
                "s_stock_2" => $data['details'][1]['safety_stock_2'],
                "s_stock_3" => $data['details'][2]['safety_stock_3'],
                "s_stock_4" => $data['details'][3]['safety_stock_4'],
                "s_stock_5" => $data['details'][4]['safety_stock_5'],
                "s_stock_6" => $data['details'][5]['safety_stock_6'],
                // "prod_plan_1" => $prod_plan_1,
                // "prod_plan_2" => $prod_plan_2,
                // "prod_plan_3" => $prod_plan_3,
                // "prod_plan_4" => $prod_plan_4,
                // "prod_plan_5" => $prod_plan_5,
                // "prod_plan_6" => $prod_plan_6,
                "prod_plan_1" => $data['details'][0]['prod_plan_1'],
                "prod_plan_2" => $data['details'][1]['prod_plan_2'],
                "prod_plan_3" => $data['details'][2]['prod_plan_3'],
                "prod_plan_4" => $data['details'][3]['prod_plan_4'],
                "prod_plan_5" => $data['details'][4]['prod_plan_5'],
                "prod_plan_6" => $data['details'][5]['prod_plan_6'],
                "need_1" => $data['details'][0]['prod_plan_1'],
                "need_2" => $data['details'][1]['prod_plan_2'],
                "need_3" => $data['details'][2]['prod_plan_3'],
                "need_4" => $data['details'][3]['prod_plan_4'],
                "need_5" => $data['details'][4]['prod_plan_5'],
                "need_6" => $data['details'][5]['prod_plan_6'],
                "bal_end" => $data['balance'],
                "ltpp_date" => date("Y-m-d"),
                "notes" => "",
                "revisi_update" => "1",
                "update_date" => date("Y-m-d H:i:s"),
                "update_user" => $this->session->username,
            );

            if (count($ltpp_generate) > 0) {
                $this->dummy->where([
                    "period" => $header[0]['period'],
                    "assy_no" => $data['product_no'],
                    "rev" => $header[0]['rev']
                ]);

                if($this->dummy->update('ltpp_generate', $postFinal)){
                    $this->dummy->update('ltpp_generate_rev', $postFinal_rev, [
                        "period" => $header[0]['period'],
                        "assy_no" => $data['product_no'],
                        "rev" => $header[0]['rev']
                    ]);
                    echo json_encode(array("title" => "Good Job", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Updated Successfully", "theme" => "success"));
                }else{
                    echo json_encode(array("title" => "Error", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Unupdate", "theme" => "error"));
                }
            } else {
                if ($this->dummy->insert('ltpp_generate', $postFinal)) {
                    $this->dummy->insert('ltpp_generate_rev', $postFinal_rev);
                    echo json_encode(array("title" => "Good Job", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Saved Successfully", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => $data['product_no'] . " | " . $data['product_name'] . " | Data Unsaved", "theme" => "error"));
                }
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_mps_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Line No
        $this->db->select('a.line_no, b.remarks');
        $this->db->from('generate_mps a');
        $this->db->join('mst_line b', 'a.line_no = b.line_no', 'left');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
        }
        $this->db->like('a.line_no', $filter_line_no);
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.revision', $filter_revision);
        $this->db->like('a.product_no', $filter_product_no);
        $this->db->where('a.deleted_is', 0);
        $this->db->group_by('a.line_no');
        $this->db->order_by('a.line_no', 'desc');
        $lines = $this->db->get()->result_array();

        //Select Full
        $this->db->select('*');
        $this->db->from('generate_mps');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->like('line_no', $filter_line_no);
        $this->db->like('customer_id', $filter_customer_id);
        $this->db->like('revision', $filter_revision);
        $this->db->like('product_no', $filter_product_no);
        $this->db->where('deleted_is', 0);
        $headers = $this->db->get()->result_array();

        $header = "";
        $headerDetails = "";

        foreach ($headers as $record) {
            $this->db->select('ltpp_month');
            $this->db->from('generate_mps_detail');
            $this->db->where('p_month', $record['p_month']);
            $this->db->where('p_year', $record['p_year']);
            $this->db->where('revision', $record['revision']);
            $this->db->where('line_no', $record['line_no']);
            $this->db->where('product_no', $record['product_no']);
            $this->db->where('deleted_is', 0);
            $this->db->group_by('ltpp_month');
            $this->db->order_by('id');
            $details = $this->db->get()->result_array();

            $header = "";
            $headerDetails = "";
            $no = 1;
            foreach ($details as $detail) {
                if ($no == 1) {
                    $xbar = '<th style="text-align:center;" rowspan="2">XBAR</th>';
                    $colspan = '8';
                } else {
                    $xbar = "";
                    $colspan = '7';
                }
                $header .= '<th style="text-align:center;" colspan="' . $colspan . '" width="50">' . $detail['ltpp_month'] . '</th>';
                $headerDetails .= ' <th style="text-align:center;" rowspan="2">BALANCE <br> AWAL</th>
                                    <th style="text-align:center;" rowspan="2">ITO</th>
                                    ' . $xbar . '
                                    <th style="text-align:center;" rowspan="2">FC</th>
                                    <th style="text-align:center;" rowspan="2">DELIVERY <br> RATE</th>
                                    <th style="text-align:center;" rowspan="2">SAFETY <br> STOCK</th>
                                    <th style="text-align:center;" rowspan="2">NEED</th>
                                    <th style="text-align:center;" rowspan="2">PROD <br> PLAN</th>';
                $no++;
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 10px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <table style="width: 100%;">
            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
            </td>
            <td style="font-size: 14px; text-align: left; margin:2px;">
                <b>' . $config->name . '</b><br>
                <small>PPC DEPARTEMENT</small>
                <br><br>
                <b>MASTER PRODUCTION SCHEDULE</b>
                <p style="font-size:12px; margin:0;">PERIOD ' . $this->monthName($filter_month) . ' ' . $filter_year . '</p>
                <p style="font-size:12px; margin:0;">REVISION ' . $filter_revision . '</p>
                <p style="font-size:12px; margin:0;">PRINT DATE ' . date("d M Y H:m:s") . '</p>
                <p style="font-size:12px; margin:0;">PRINT BY ' . $this->session->username . '</p>
            </td>
        </table><br>
        <table id="customers" border="1">
            <tr>
                <th style="text-align:center;">DISETUJUI</th>
                <th style="text-align:center;">DIKETAHUI</th>
                <th style="text-align:center;">DIPERIKSA</th>
                <th style="text-align:center;">DIBUAT</th>
                <td width="100">DOC NO</td>
                <td style="text-align:center;" width="5">:</td>
                <td>'.date("Ymd").'/LTPP/001</td>
                <th colspan="2" style="text-align:center;">FRM-PPC-0007-00</th>
            </tr>
            <tr>
                <th rowspan="5" style="height:70px;"></th>
                <th rowspan="5" style="height:70px;"></th>
                <th rowspan="5" style="height:70px;"></th>
                <th rowspan="5" style="height:70px;"></th>
                <td>DATE</td>
                <td style="text-align:center;">:</td>
                <td></td>
                <th width="100" style="text-align:center;">REV NO</th>
                <th style="text-align:center;">REMARKS</th>
            </tr>
            <tr>
                <td>DISTRIBUTION</td>
                <td style="text-align:center;">:</td>
                <td></td>
                <th style="text-align:center;">1</th>
                <th></th>
            </tr>
            <tr>
                <td>1. ENGINEERING</td>
                <td></td>
                <td>5. MAINTENANCE</td>
                <th style="text-align:center;">2</th>
                <th></th>
            </tr>
            <tr>
                <td>2. PRODUKSI</td>
                <td></td>
                <td>6. PLASMA</td>
                <th style="text-align:center;">3</th>
                <th></th>
            </tr>
            <tr>
                <td>3. QA</td>
                <td></td>
                <td>7. MATERIAL CONTROL</td>
                <th style="text-align:center;">4</th>
                <th></th>
            </tr>
            <tr>
                <th style="text-align:center; height:20px;"></th>
                <th style="text-align:center; height:20px;"></th>
                <th style="text-align:center; height:20px;"></th>
                <th style="text-align:center; height:20px;">'.$this->session->name.'</th>
                <td>WAREHOUSE</td>
                <td></td>
                <td></td>
                <th style="text-align:center;">5</th>
                <th></th>
            </tr>
        </table>
        <br>
        <table id="customers" border="1" style="width:4000px;">';


        $html .= '<tr>
                <th style="text-align:center;" rowspan="3" width="50">No</th>
                <th style="text-align:center;" rowspan="3" width="200">PRODUCT NO</th>
                <th style="text-align:center;" rowspan="3" width="200">PRODUCT NAME</th>
                <th style="text-align:center;" rowspan="3" width="100">CCT</th>
                <th style="text-align:center;" colspan="4">' . @$headers[0]['wip_month'] . '</th>
                <th style="text-align:center;" rowspan="3" width="50">FG</th>
                <th style="text-align:center;" rowspan="3" width="50">OST<br>MPP</th>
                <th style="text-align:center;" rowspan="3" width="50">TOTAL<br>STOCK</th>
                <th style="text-align:center;" rowspan="3" width="50">OST<br>SO</th>';
        $html .= $header;
        $html .= '
                <th style="text-align:center;" rowspan="3" width="50">BAL</th>
            </tr>
            <tr>
                <th style="text-align:center;" colspan="4">STOCK WIP</th>';
        $html .= $headerDetails;
        $html .= '</tr>
            <tr>
                <th style="text-align:center;" width="50">PP</th>
                <th style="text-align:center;" width="50">STOCK<br>TF</th>
                <th style="text-align:center;" width="50">REP. QA</th>
                <th style="text-align:center;" width="50">HAV</th>
            </tr>';

        foreach ($lines as $line) {
            if ($line['remarks'] == "") {
                $line_name = "No Line";
            } else {
                $line_name = $line['remarks'];
            }

            $html .= '  <tr>
                            <th colspan="100" style="text-align:left;"><b>' . $line_name . '</b></th>
                        </tr>';
            //Select Full
            $this->db->select('a.*');
            $this->db->from('generate_mps a');
            $this->db->join('generate_mps_detail b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.product_no = b.product_no');
            if ($filter_month != "" or $filter_year != "") {
                $this->db->where('a.p_month', $filter_month);
                $this->db->where('a.p_year', $filter_year);
            }
            $this->db->where('a.deleted_is', 0);
            $this->db->where('a.line_no', $line['line_no']);
            $this->db->where("(a.pp > 0 or a.plant_1 > 0 or a.plant_2 > 0 or a.plant_3 > 0 or a.plant_4 > 0 or a.plant_5 > 0 or a.plant_6 > 0 or a.plant_7 > 0 or a.plant_8 > 0 or a.plant_9 > 0 or a.plant_10 > 0 or a.rqa > 0 or a.hav > 0 or a.fg > 0 or a.os_mpp > 0 or a.os_sales_order > 0 or a.total_stock > 0 or a.balance > 0 or b.begin_balance > 0 or b.ito > 0 or b.forecast > 0 or b.delivery_rate > 0 or b.safety_stock > 0 or b.prod_plan > 0)");
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.revision', $filter_revision);
            $this->db->like('a.product_no', $filter_product_no);
            $this->db->group_by('a.product_no');
            $records = $this->db->get()->result_array();

            $no = 1;
            foreach ($records as $data) {

                $cct = $this->api->read('wip_mst_wos_cct', [], ["mstwos_assyno" => $data['product_no']]);

                $html .= '  <tr>
                            <td style="text-align:center;">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td style="text-align:center;">' . @$cct->circuit_no . '</td>
                            <td style="text-align:center;">' . $data['pp'] . '</td>
                            <td style="text-align:center;">' . ($data['plant_1'] + $data['plant_2'] + $data['plant_3'] + $data['plant_4'] + $data['plant_5'] + $data['plant_6'] + $data['plant_7'] + $data['plant_8'] + $data['plant_9'] + $data['plant_10']) . '</td>
                            <td style="text-align:center;">' . $data['rqa'] . '</td>
                            <td style="text-align:center;">' . $data['hav'] . '</td>
                            <td style="text-align:center;">' . $data['fg'] . '</td>
                            <td style="text-align:center;">' . $data['os_mpp'] . '</td>
                            <td style="text-align:center;">' . $data['total_stock'] . '</td>
                            <td style="text-align:center;">' . $data['os_sales_order'] . '</td>';

                $this->db->select('a.*, b.xbar, c.st_sparepart');
                $this->db->from('generate_mps_detail a');
                $this->db->join('sales_order b', 'a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision and a.product_no = b.product_no', 'left');
                $this->db->join('mst_item c', 'a.product_no = c.item_id');
                $this->db->where('a.p_month', $data['p_month']);
                $this->db->where('a.p_year', $data['p_year']);
                $this->db->where('a.revision', $data['revision']);
                $this->db->where('a.line_no', $data['line_no']);
                $this->db->where('a.product_no', $data['product_no']);
                $this->db->where('a.deleted_is', 0);
                $this->db->group_by('a.ltpp_month');
                $this->db->order_by('a.id');
                $details2 = $this->db->get()->result_array();

                $nodetail = 1;
                foreach ($details2 as $detail2) {
                    if ($nodetail == 1) {
                        if ($detail2['xbar'] == "") {
                            $xbarQty = 0;
                        } else {
                            $xbarQty = $detail2['xbar'];
                        }
                        $xbar2 = '<td style="text-align:center;">' . $xbarQty . '</td>';
                    } else {
                        $xbar2 = "";
                    }

                    if ($detail2['st_sparepart'] == "t") {
                        $safety_stock = 0;
                    } else {
                        $safety_stock = $detail2['safety_stock'];
                    }

                    $html .= '  <td style="text-align:center;">' . $detail2['begin_balance'] . '</td>
                            <td style="text-align:center;">' . $detail2['ito'] . '</td>
                            ' . $xbar2 . '
                            <td style="text-align:center;">' . $detail2['forecast'] . '</td>
                            <td style="text-align:center;">' . $detail2['delivery_rate'] . '</td>
                            <td style="text-align:center;">' . $safety_stock . '</td>
                            <td style="text-align:center;">' . $detail2['need'] . '</td>
                            <td style="text-align:center;">' . $detail2['prod_plan'] . '</td>';
                    $nodetail++;
                }
                $html .= '<td style="text-align:center;">' . $data['balance'] . '</td>
            </tr>';
                $no++;
            }
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

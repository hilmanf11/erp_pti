<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mrp extends CI_Controller
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
            $this->load->view('planning/generate_mrp');
        } else {
            redirect('error_access');
        }
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->pg = $this->load->database('pg', TRUE);
        $query = $this->pg->query("SELECT * FROM mst_item WHERE pfm_id = '06' and stscode_id = '01' and item_id LIKE '%$post%' ORDER BY item_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readProductFamily(){
        $this->pg = $this->load->database('pg', TRUE);
        $query = $this->pg->query("SELECT * FROM r_prodfam WHERE prod_group = 'Raw Material' and pfm_id != '05' ORDER BY pfm_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readParts($pfm_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        if($pfm_id == ""){
            $where = "";
        }else{
            $where = "and pfm_id = '$pfm_id'";
        }

        $this->pg = $this->load->database('pg', TRUE);
        $query = $this->pg->query("SELECT * FROM mst_item WHERE item_id LIKE '%$post%' and stscode_id = '01' and pfm_id not in ('06','05') $where ORDER BY pfm_id, item_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function checkMps()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('generate_mps_detail');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        // $this->db->like('revision', $filter_revision);
        $this->db->group_by('product_no');
        $records = $this->db->get()->result_array();

        if (count($records) > 0) {
            echo json_encode(array("theme" => "success"));
        } else {
            echo json_encode(array("theme" => "error"));
        }
    }

    public function checkOspo()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('os_vendor');
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

    public function checkWip()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('balance_wip');
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

    public function checkMpp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('os_mpp');
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

    public function checkRm()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('rm_stock');
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

    public function checkGp()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('upload_gp');
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

    public function checkSupply()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        //Select Query
        $this->db->select('*');
        $this->db->from('os_supply');
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

    public function getDataMps()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));

            $mpsRev = $this->api->query("SELECT max(revision) as rev FROM generate_mps_detail WHERE p_month='$filter_month' and p_year='$filter_year'");

            //Select Query
            $this->db->select('b.p_month, b.p_year, b.revision, 
                a.component_id as product_no, 
                c.item_name as product_name, 
                d.name as product_family, 
                e.name as uom, 
                b.ltpp_month2 as period, 
                SUM(b.prod_plan) as prodplan,
                a.qty as qpa, 
                ROUND(SUM(b.prod_plan * a.qty), 2) as qty');
            $this->db->from('mst_bom a');
            $this->db->join('generate_mps_detail b', 'a.item_id = b.product_no');
            $this->db->join('mst_item_raw c', 'a.component_id = c.item_id');
            $this->db->join('mst_item_family d', 'd.number = c.pfm_id', 'left');
            $this->db->join('uom e', 'e.number = c.um_id', 'left');
            $this->db->where('b.p_month', $filter_month);
            $this->db->where('b.p_year', $filter_year);
            $this->db->where('b.revision', @$mpsRev[0]->rev);
            // $this->db->where('product_no', 'ZYM024-081C');
            // $this->db->where('prod_plan >', 0);
            if($filter_product_family != ""){
                $this->db->where('d.name', $filter_product_family);
            }
            if($filter_product_family != ""){
                $this->db->where('c.item_id', $filter_part_no);
            }
            $this->db->group_by('b.ltpp_month2');
            $this->db->group_by('a.component_id');
            // $this->db->having('SUM(b.prod_plan * a.qty) > 0');
            $this->db->order_by('a.component_id', 'asc');
            $this->db->order_by('b.ltpp_month2', 'asc');
            $mpsDetails = $this->db->get()->result_array();
            
            $mpsData = array();
            foreach ($mpsDetails as $mpsDetail) {
                $mpsData[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "period" => $mpsDetail['period'],
                    "prodplan" => $mpsDetail['prodplan'],
                    "product_no" => $mpsDetail['product_no'],
                    "product_name" => $mpsDetail['product_name'],
                    "product_family" => $mpsDetail['product_family'],
                    "uom" => $mpsDetail['uom'],
                    "qty" => $mpsDetail['qty'],
                    "qpa" => $mpsDetail['qpa'],
                );
            }

            $mpsData['total'] = @count($mpsData);
            die(json_encode($mpsData));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getDataMrp()
    {
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));

            //Select Query
            $this->db->select('a.product_no, a.product_name, a.product_family,
                coalesce(b.mpq, 0) as mpq, 
                coalesce(b.moq, 0) as moq, 
                coalesce(b.leadtime, 0) as leadtime,
                b.supplier_type,
                coalesce(c.actual, 0) as qty_rm, 
                coalesce(d.qty, 0) as qty_wip, 
                coalesce(e.actual, 0) as qty_vendor,
                (coalesce(c.actual, 0) + coalesce(d.qty, 0) + coalesce(e.actual, 0)) as total_stock, 
                coalesce(f.actual, 0) as qty_supply, 
                coalesce(g.actual, 0) as qty_wo, 
                (coalesce(f.actual, 0) + coalesce(g.actual, 0)) as total_wo');
            $this->db->from('generate_mrp a');
            $this->db->join('mst_item_raw h', 'a.product_no = h.item_id');
            $this->db->join('(select * from upload_gp order by leadtime desc) b', "b.product_no like h.item_gp and a.p_month = b.p_month and a.p_year = b.p_year and a.revision = b.revision", 'left');
            $this->db->join('rm_stock c', 'a.product_no = c.product_no and a.p_month = c.p_month and a.p_year = c.p_year and a.revision = c.revision', 'left');
            $this->db->join('balance_wip d', 'a.product_no = d.product_no and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
            $this->db->join('os_vendor e', 'a.product_no = e.product_no and a.p_month = e.p_month and a.p_year = e.p_year and a.revision = e.revision', 'left');
            $this->db->join('os_supply f', 'a.product_no = f.product_no and a.p_month = f.p_month and a.p_year = f.p_year and a.revision = f.revision', 'left');
            $this->db->join('os_mpp g', 'a.product_no = g.product_no and a.p_month = g.p_month and a.p_year = g.p_year and a.revision = g.revision', 'left');
            //$this->db->where('assy_no', 'ZYM024-081C');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_product_family != ""){
                $this->db->where('a.product_family', $filter_product_family);
            }
            if($filter_part_no != ""){
                $this->db->where('a.product_no', $filter_part_no);
            }
            $this->db->group_by('a.product_no');
            $this->db->order_by('a.product_no', 'asc');
            $this->db->order_by('a.product_no', 'asc');
            $generates = $this->db->get()->result_array();

            foreach ($generates as $generate) {

                $this->db->select('product_no, period, SUM(qty) as need');
                $this->db->from('generate_mrp');
                $this->db->where('product_no', $generate['product_no']);
                $this->db->where('p_month', $filter_month);
                $this->db->where('p_year', $filter_year);
                $this->db->where('revision', $filter_revision);
                $this->db->group_by('product_no');
                $this->db->group_by('period');
                $this->db->order_by('period', 'asc');
                $periods = $this->db->get()->result_array();

                $no = 1;
                $need_1 = 0;
                $need_2 = 0;
                $need_3 = 0;
                $need_4 = 0;
                $need_5 = 0;
                $need_6 = 0;

                $balance_1 = 0;
                $balance_2 = 0;
                $balance_3 = 0;
                $balance_4 = 0;
                $balance_5 = 0;
                $balance_6 = 0;
                foreach ($periods as $period) {
                    $product_no = $generate['product_no'];
                    
                    if($no == 1){
                        if($filter_revision == 0){
                            $need_1 += $period['need'];
                        }else{
                            $need_1 += 0;
                        }
                        
                        $no = 2;
                    }elseif($no == 2){
                        $need_2 += $period['need'];
                        $no = 3;
                    }elseif($no == 3){
                        $need_3 += $period['need'];
                        $no = 4;
                    }elseif($no == 4){
                        $need_4 += $period['need'];
                        $no = 5;
                    }elseif($no == 5){
                        $need_5 += $period['need'];
                        $no = 6;
                    }elseif($no == 6){
                        $need_6 += $period['need'];
                        $no = 1;
                    }
                }

                $balance_1 = ($generate['total_stock'] - ($generate['total_wo'] + $need_1));
                $balance_2 = ($balance_1 - $need_2);
                $balance_3 = ($balance_2 - $need_3);
                $balance_4 = ($balance_3 - $need_4);
                $balance_5 = ($balance_4 - $need_5);
                $balance_6 = ($balance_5 - $need_6);
                
                if($generate['leadtime'] <= 30){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_1 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_1);
                        }
                    }else{
                        if($balance_2 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_2);
                        }
                    }
                }elseif($generate['leadtime'] > 30 && $generate['leadtime'] <= 60){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_2 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_2);
                        }
                    }else{
                        if($balance_3 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_3);
                        }
                    }
                }elseif($generate['leadtime'] > 60 && $generate['leadtime'] <= 90){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_3 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_3);
                        }
                    }else{
                        if($balance_4 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_4);
                        }
                    }
                }elseif($generate['leadtime'] > 90 && $generate['leadtime'] <= 120){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_4 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_4);
                        }
                    }else{
                        if($balance_5 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_5);
                        }
                    }
                }elseif($generate['leadtime'] > 120 and $generate['leadtime'] <= 150){
                    if($generate['supplier_type'] == "LOCAL"){
                        if($balance_5 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_5);
                        }
                    }else{
                        if($balance_6 > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($balance_6);
                        }
                    }
                }else{
                    if($balance_6 > 0){
                        $total_need = 0;
                    }else{
                        $total_need = abs($balance_6);
                    }
                }

                $leadtimeMonth = ceil($generate['leadtime'] / 30);

                switch ($leadtimeMonth) {
                    case 6:
                        $avg_need = (($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 7:
                        $avg_need = (($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 8:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 2);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 9:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 3);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 10:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 4);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 11:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 5);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    case 12:
                        $avg_need = ((($need_1 + $need_2 + $need_3 + $need_4 + $need_5 + $need_6) / 6) * 6);
                        $avg_balance = ($balance_6 - $avg_need);
                        if($avg_balance > 0){
                            $total_need = 0;
                        }else{
                            $total_need = abs($avg_balance);
                        }
                    break;
                    default:
                        $avg_need = 0;
                        $avg_balance = ($balance_6 - $avg_need);
                }

                if($total_need > 0 && $generate['moq'] > 0 && $generate['mpq'] > 0){
                    if($total_need > $generate['moq']){
                        $purchase_order = (ceil($total_need / $generate['mpq']) * $generate['mpq']);
                    }else{
                        $purchase_order = (ceil($total_need / $generate['moq']) * $generate['moq']);
                    }
                }else{
                    $purchase_order = 0;
                }

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "part_no" => $generate['product_no'],
                    "part_name" => $generate['product_name'],
                    "product_family" => $generate['product_family'],
                    "mpq" => $generate['mpq'],
                    "moq" => $generate['moq'],
                    "leadtime" => $generate['leadtime'],
                    "qty_rm" => $generate['qty_rm'],
                    "qty_wip" => $generate['qty_wip'],
                    "qty_vendor" => $generate['qty_vendor'],
                    "total_stock" => $generate['total_stock'],
                    "qty_supply" => $generate['qty_supply'],
                    "qty_wo" => $generate['qty_wo'],
                    "total_wo" => $generate['total_wo'],
                    "need_1" => $need_1,
                    "need_2" => $need_2,
                    "need_3" => $need_3,
                    "need_4" => $need_4,
                    "need_5" => $need_5,
                    "need_6" => $need_6,
                    "balance_1" => $balance_1,
                    "balance_2" => $balance_2,
                    "balance_3" => $balance_3,
                    "balance_4" => $balance_4,
                    "balance_5" => $balance_5,
                    "balance_6" => $balance_6,
                    "avg_need" => $avg_need,
                    "avg_balance" => $avg_balance,
                    "total_need" => $total_need,
                    "purchase_order" => $purchase_order,
                );
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function getDataMrpFinals(){
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));
            $filter_part_no = base64_decode($this->input->get('filter_part_no'));

            //Select Query
            $this->db->select('*');
            $this->db->from('generate_mrp_finals');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('revision', $filter_revision);
            if($filter_product_family != ""){
                $this->db->where('product_family', $filter_product_family);
            }
            if($filter_part_no != ""){
                $this->db->where('part_no', $filter_part_no);
            }
            $this->db->order_by('product_family', 'asc');
            $this->db->order_by('total_need', 'asc');
            $generates = $this->db->get()->result_array();

            $total_need = 0;
            $product_family = "";
            $arr = array();
            foreach ($generates as $generate) {
                if($generate['product_family'] != $product_family){
                    $total_need = $generate['total_need'];
                }else{
                    $total_need += $generate['total_need'];
                }

                $this->db->select('product_family, SUM(total_need) as total');
                $this->db->from('generate_mrp_finals');
                $this->db->where('p_month', $filter_month);
                $this->db->where('p_year', $filter_year);
                $this->db->where('revision', $filter_revision);
                $this->db->where('product_family', $generate['product_family']);
                $this->db->group_by('product_family');
                $mrp = $this->db->get()->row();

                $composition = round(($total_need / @$mrp->total) * 100);

                $this->db->select('*');
                $this->db->from('safety_stock');
                $this->db->where("start <= '$composition' and ending >= '$composition'");
                $safety_stock = $this->db->get()->row();

                $arr[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "part_no" => $generate['part_no'],
                    "part_name" => $generate['part_name'],
                    "product_family" => $generate['product_family'],
                    "need" => $total_need,
                    "total" => @$mrp->total,
                    "composition" => $composition,
                    "class" => $safety_stock->number,
                    "safety" => $safety_stock->safety,
                    "safety_stock" => round($generate['total_need'] * (@$safety_stock->safety / 100)),
                );

                $product_family = $generate['product_family'];
            }

            $arr['total'] = @count($arr);
            die(json_encode($arr));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->api->reads('generate_mrp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "period" => $post['period'],
                "product_no" => $post['product_no']
            ]);

            if (count($records) > 0) {
                $send = $this->api->update('generate_mrp', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "period" => $post['period'],
                    "product_no" => $post['product_no']
                ], $post);

                echo $send;
            } else {
                $send = $this->api->create('generate_mrp', $post, "MRPD", "JUTA");
                echo $send;
            }
        }
    }

    public function createMrp()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $part_no = $post['part_no'];

            //Select Query
            $this->db->select('*');
            $this->db->from('generate_mrp_finals');
            $this->db->where('p_month', $post['p_month']);
            $this->db->where('p_year', $post['p_year']);
            $this->db->where('revision', $post['revision']);
            $this->db->where('part_no', $post['part_no']);
            $records = $this->db->get()->result_array();

            $this->pg = $this->load->database('pg', TRUE);

            $period_1 = date("Ym", strtotime("-1 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            $period_2 = date("Ym", strtotime("-2 month", strtotime($post['p_year']."-".$post['p_month']."-01")));
            $period_3 = date("Ym", strtotime("-3 month", strtotime($post['p_year']."-".$post['p_month']."-01")));

            // $qissued = $this->pg->query("SELECT b.item_id, SUM(b.qty_need) as need from wip_trx_mpp a
            //     join serial_detail_kanbanrm b ON a.temp_woc_id = b.kanbanrm_woc_id
            //     where a.periode IN ('$period_1','$period_2','$period_3') and b.item_id = '$part_no'
            //     group by b.item_id, a.periode
            //     order by b.item_id, a.periode");

            $qissued = $this->pg->query("SELECT z.periode, z.bom_com_item, SUM(z.need) as need FROM(
                SELECT b.bom_com_item, a.periode, (a.qty * b.bom_qty_perassy) as need from wip_trx_mpp a
                JOIN mst_bom b ON b.bom_par_item = a.assy_no
                where a.periode IN ('$period_1','$period_2','$period_3') and b.bom_com_item = '$part_no'
                order by b.bom_com_item, a.periode) z GROUP BY z.periode, z.bom_com_item ORDER by z.periode asc");
            $missued = $qissued->result_array();

            //Select Query
            $this->db->select('*');
            $this->db->from('generate_mrp_finals');
            $this->db->where('p_month', $post['p_month']);
            $this->db->where('p_year', $post['p_year']);
            $this->db->where('revision', $post['revision']);
            $this->db->where('part_no', $post['part_no']);
            $records = $this->db->get()->result_array();

            $postFinal = array_merge($post, array(
                "issued_1" => @$missued[0]['need'],
                "issued_2" => @$missued[1]['need'],
                "issued_3" => @$missued[2]['need'],
                "issued_avg" => ((@$missued[0]['need'] + @$missued[1]['need'] + @$missued[2]['need']) / 3),
            ));

            if (count($records) > 0) {
                $send = $this->api->update('generate_mrp_finals', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "part_no" => $post['part_no']
                ], $postFinal);

                echo $send;
            } else {
                $send = $this->api->create('generate_mrp_finals', $postFinal, "MRP", "JUTA");
                echo $send;
            }
        }
    }

    public function createAbc()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $records = $this->api->reads('generate_mrp_abcclass', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "part_no" => $post['part_no']
            ]);

            if (count($records) > 0) {
                $send = $this->api->update('generate_mrp_abcclass', [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "part_no" => $post['part_no']
                ], $post);

                echo $send;
            } else {
                
                $send = $this->api->create('generate_mrp_abcclass', $post, "ABC", "JUTA");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($this->input->get()) {
            if ($option == "excel") {
                $format  = date("Ymd");
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=mrp_$format.xls");
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

            $period_1 = date("F Y", strtotime($filter_year."-".$filter_month."-01"));
            $period_2 = date("F Y",  strtotime("1 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_3 = date("F Y",  strtotime("2 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_4 = date("F Y",  strtotime("3 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_5 = date("F Y",  strtotime("4 month", strtotime($filter_year."-".$filter_month."-01")));
            $period_6 = date("F Y",  strtotime("5 month", strtotime($filter_year."-".$filter_month."-01")));

            $need_1 = date("m/Y",  strtotime("-1 month", strtotime($filter_year."-".$filter_month."-01")));
            $need_2 = date("m/Y",  strtotime("-2 month", strtotime($filter_year."-".$filter_month."-01")));
            $need_3 = date("m/Y",  strtotime("-3 month", strtotime($filter_year."-".$filter_month."-01")));

            $this->db->select('a.*, c.class, c.composition, c.safety_stock, c.safety, d.maker_name, d.supplier_type');
            $this->db->from('generate_mrp_finals a');
            $this->db->join('mst_item_family b', 'a.product_family = b.name', 'left');
            $this->db->join('generate_mrp_abcclass c', 'a.part_no = c.part_no and a.p_month = c.p_month and a.p_year = c.p_year and a.revision = c.revision', 'left');
            $this->db->join('upload_gp d', 'a.part_no = d.product_no and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
            // $this->db->where('a.purchase_order >', 0);
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.part_no', $filter_part_no);
            // $this->db->where('leadtime >', 0);
            $this->db->order_by('b.number', 'asc');
            $this->db->order_by('c.class', 'asc');
            // $this->db->limit(10);
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 200%;font-size: 11px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;} 
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
                                    <small>GENERATE MRP</small>
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
                        <th rowspan="2" style="text-align:center;">PART NO</th>
                        <th rowspan="2" style="text-align:center;">PART NAME</th>
                        <th rowspan="2" style="text-align:center;">PRODUCT FAMILY</th>
                        <th rowspan="2" style="text-align:center;">MAKER</th>
                        <th rowspan="2" style="text-align:center;">MAKER TYPE</th>
                        <th rowspan="2" style="text-align:center;">CLASS<br>A/B/C</th>
                        <th rowspan="2" style="text-align:center;">PO L/T</th>
                        <th rowspan="2" style="text-align:center;">MPQ</th>
                        <th rowspan="2" style="text-align:center;">MOQ</th>
                        <th colspan="3" style="text-align:center;">WORKORDER</th>
                        <th rowspan="2" style="text-align:center;">AVG</th>
                        <th colspan="3" style="text-align:center;">STOCK RAW MATERIAL</th>
                        <th rowspan="2" style="text-align:center;">TOTAL<br>STOCK</th>
                        <th rowspan="2" style="text-align:center;">OS<br>SUPPLY</th>
                        <th rowspan="2" style="text-align:center;">OS<br>WORKORDER</th>
                        <th rowspan="2" style="text-align:center;">TOTAL OS</th>
                        <th colspan="2" style="text-align:center;">'.$period_1.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_2.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_3.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_4.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_5.'</th>
                        <th colspan="2" style="text-align:center;">'.$period_6.'</th>
                        <th colspan="2" style="text-align:center;">> 6 Month</th>
                        <th rowspan="2" style="text-align:center;">TOTAL<br>NEED</th>
                        <th rowspan="2" style="text-align:center;">SAFETY<br>STOCK</th>
                        <th rowspan="2" style="text-align:center;">MRP<br>RESULT</th>
                    </tr>
                    <tr>
                        <th style="text-align:center;">'.$need_3.'</th>
                        <th style="text-align:center;">'.$need_2.'</th>
                        <th style="text-align:center;">'.$need_1.'</th>
                        <th style="text-align:center;">WHS</th>
                        <th style="text-align:center;">WIP</th>
                        <th style="text-align:center;">OS SUPPLIER</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                        <th style="text-align:center;">NEED</th>
                        <th style="text-align:center;">BAL</th>
                    </tr>';

                    $no = 1;
                    foreach ($records as $record) {
                        if($record['balance_1'] > 0){ $style_1 = ""; }else{ $style_1 = "color:red;"; }
                        if($record['balance_2'] > 0){ $style_2 = ""; }else{ $style_2 = "color:red;"; }
                        if($record['balance_3'] > 0){ $style_3 = ""; }else{ $style_3 = "color:red;"; }
                        if($record['balance_4'] > 0){ $style_4 = ""; }else{ $style_4 = "color:red;"; }
                        if($record['balance_5'] > 0){ $style_5 = ""; }else{ $style_5 = "color:red;"; }
                        if($record['balance_6'] > 0){ $style_6 = ""; }else{ $style_6 = "color:red;"; }
                        if($record['avg_balance'] > 0){ $style_7 = ""; }else{ $style_7 = "color:red;"; }

                        if(round($record['total_need'] + $record['safety_stock']) > 0 && $record['moq'] > 0){
                            $final = ($record['total_need'] + $record['safety_stock']);
                            if($final > $record['moq']){
                                $mrp_result = (ceil($final / $record['mpq']) * $record['mpq']);
                            }else{
                                $mrp_result = (ceil($final / $record['moq']) * $record['moq']);
                            }
                        }else{
                            $mrp_result = 0;
                        }
                        
                        $html .= "  <tr>
                                        <td>".$no."</td>
                                        <td style='mso-number-format:\@;'>".trim($record['part_no'])."</td>
                                        <td>".$record['part_name']."</td>
                                        <td>".$record['product_family']."</td>
                                        <td>".$record['maker_name']."</td>
                                        <td>".$record['supplier_type']."</td>
                                        <td>".$record['class']."</td>
                                        <td>".$record['leadtime']."</td>
                                        <td>".$record['mpq']."</td>
                                        <td>".$record['moq']."</td>
                                        <td style='text-align:right;'>".round($record['issued_1'])."</td>
                                        <td style='text-align:right;'>".round($record['issued_2'])."</td>
                                        <td style='text-align:right;'>".round($record['issued_3'])."</td>
                                        <td style='text-align:right;'>".round($record['issued_avg'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_rm'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_wip'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_vendor'])."</td>
                                        <td style='text-align:right;'>".round($record['total_stock'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_supply'])."</td>
                                        <td style='text-align:right;'>".round($record['qty_wo'])."</td>
                                        <td style='text-align:right;'>".round($record['total_wo'])."</td>
                                        <td style='text-align:right;'>".round($record['need_1'])."</td>
                                        <td style='text-align:right;$style_1'>".round($record['balance_1'])."</td>
                                        <td style='text-align:right;'>".round($record['need_2'])."</td>
                                        <td style='text-align:right;$style_2'>".round($record['balance_2'])."</td>
                                        <td style='text-align:right;'>".round($record['need_3'])."</td>
                                        <td style='text-align:right;$style_3'>".round($record['balance_3'])."</td>
                                        <td style='text-align:right;'>".round($record['need_4'])."</td>
                                        <td style='text-align:right;$style_4'>".round($record['balance_4'])."</td>
                                        <td style='text-align:right;'>".round($record['need_5'])."</td>
                                        <td style='text-align:right;$style_5'>".round($record['balance_5'])."</td>
                                        <td style='text-align:right;'>".round($record['need_6'])."</td>
                                        <td style='text-align:right;$style_6'>".round($record['balance_6'])."</td>
                                        <td style='text-align:right;'>".round($record['avg_need'])."</td>
                                        <td style='text-align:right;$style_7'>".round($record['avg_balance'])."</td>
                                        <td style='text-align:right;'>".round($record['total_need'])."</td>
                                        <td style='text-align:right;'>".round($record['safety_stock'])."</td>
                                        <td style='text-align:right;'>". round($mrp_result) ."</td>
                                    </tr>";
                        $no++;
                    }

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');



class Mps_vs_mpp extends CI_Controller

{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');

        function numberDecimalFormat($value) { 
            if ($value === null || !isset($value)) {
                $value = 0;
            }
            return number_format((float)$value, 2, '.', '');
        }
    }



    public function index()

    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/mps_vs_mpp');
        } else {
            redirect('error_access');
        }
    }



    public function datatables()

    {

        if ($this->input->post()) {
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_balance = base64_decode($this->input->get('filter_balance'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select('a.p_month, a.p_year, COALESCE(NULLIF(b.name, ""), "LAINNYA") as customer,a.product_no, a.product_name, 
                SUM(CASE WHEN a.revision = 0 THEN a.prod_plan ELSE 0 END) as revision_0, 
                SUM(CASE WHEN a.revision = 1 THEN a.prod_plan ELSE 0 END) as revision_1, 
                SUM(CASE WHEN a.revision = 2 THEN a.prod_plan ELSE 0 END) as revision_2,
                (CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                     WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                     WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                     else 0 END) as bal_prod_plan, 
                     (
                        (
                            SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END)
                            -
                            SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END)
                        )
                        /
                        SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END)
                    )*100 
                    as rev_vs_1,  (
                        (
                            SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END)
                            -
                            SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END)
                        )
                        /
                        SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END)
                    )*100  as rev_vs_2');

            $this->db->select('((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
            (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
            (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
            (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
            (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
            (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
            (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
            (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
            (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
            (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
            (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
            (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
            (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
            (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
            (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
            (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
            (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
            (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
            (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
            (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
            (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
            (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
            (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
            (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
            (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
            (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
            (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
            (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
            (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
            (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
            (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END)) AS total_mpp');

            $this->db->select('((CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                    WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                    WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                    else 0 END) - ((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
            (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
            (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
            (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
            (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
            (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
            (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
            (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
            (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
            (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
            (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
            (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
            (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
            (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
            (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
            (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
            (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
            (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
            (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
            (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
            (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
            (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
            (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
            (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
            (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
            (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
            (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
            (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
            (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
            (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
            (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END))) as balance');

            $this->db->select('((
            ((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
            (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
            (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
            (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
            (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
            (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
            (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
            (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
            (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
            (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
            (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
            (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
            (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
            (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
            (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
            (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
            (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
            (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
            (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
            (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
            (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
            (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
            (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
            (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
            (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
            (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
            (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
            (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
            (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
            (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
            (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END)) /
            (CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
            WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
            WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
            else 0 END)) * 100) as persen_mpp');

            $this->db->select('(100 - ((
                ((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
                (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
                (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
                (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
                (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
                (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
                (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
                (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
                (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
                (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
                (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
                (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
                (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
                (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
                (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
                (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
                (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
                (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
                (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
                (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
                (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
                (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
                (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
                (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
                (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
                (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
                (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
                (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
                (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
                (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
                (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END)) /
                (CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                else 0 END)) * 100)) as persen_dev');

            $this->db->from('generate_mps_detail as a');
            $this->db->join('mst_item d', 'a.product_no = d.item_id');
            $this->db->join('mst_customer as b', "d.id_customer = b.number", 'left');
            $this->db->join('generate_mpp as c', "a.product_no = c.product_no and a.p_month = c.p_month and a.p_year = c.p_year", "left");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where(empty($filter_customer_id)?'1':'a.customer_id', empty($filter_customer_id)?'1':$filter_customer_id);
            $this->db->where(empty($filter_product_no)?'1':'a.product_no', empty($filter_product_no)?'1':$filter_product_no);
            $this->db->where('a.ltpp_month2', "$filter_year-$filter_month-01");
            $this->db->group_by('a.customer_id, a.product_no, a.p_month, a.p_year');  
        if ($filter_balance == 'P') {
            $this->db->having('balance >=', 0);
        } elseif ($filter_balance == 'M') {
            $this->db->having('balance <', 0);
        }
            $this->db->order_by('balance', 'desc');
            // die($this->db->get_compiled_select());

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            // $result['rev_vs_1'] = $result['revision_1'] == 0 || $result['revision'] == 0 ? 0 : ($result['revision_1']/$result['revision'])*100;

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);

            echo json_encode($result);

        }
        //ini tutup

    }


    public function print($option = "")

    {

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mps_vs_mpp_$format.xls");
        }



        //Config

        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_balance = base64_decode($this->input->get('filter_balance'));
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        $month =$months[$filter_month];
        //Select Query
        $this->db->select('a.p_month, a.p_year, COALESCE(NULLIF(b.name, ""), "LAINNYA") as customer,a.product_no, a.product_name, 
                SUM(CASE WHEN a.revision = 0 THEN a.prod_plan ELSE 0 END) as revision_0, 
                SUM(CASE WHEN a.revision = 1 THEN a.prod_plan ELSE 0 END) as revision_1, 
                SUM(CASE WHEN a.revision = 2 THEN a.prod_plan ELSE 0 END) as revision_2,
                (CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                     WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                     WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                     else 0 END) as bal_prod_plan, 
                     (
                        (
                            SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END)
                            -
                            SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END)
                        )
                        /
                        SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END)
                    )*100 
                    as rev_vs_1,  (
                        (
                            SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END)
                            -
                            SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END)
                        )
                        /
                        SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END)
                    )*100  as rev_vs_2');
        
        $this->db->select('((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
                     (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
                     (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
                     (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
                     (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
                     (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
                     (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
                     (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
                     (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
                     (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
                     (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
                     (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
                     (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
                     (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
                     (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
                     (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
                     (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
                     (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
                     (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
                     (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
                     (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
                     (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
                     (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
                     (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
                     (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
                     (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
                     (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
                     (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
                     (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
                     (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
                     (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END)) AS total_mpp');
                    
        $this->db->select('((CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                 WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                 WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                 else 0 END) - ((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
                (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
                (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
                (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
                (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
                (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
                (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
                (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
                (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
                (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
                (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
                (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
                (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
                (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
                (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
                (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
                (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
                (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
                (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
                (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
                (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
                (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
                (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
                (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
                (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
                (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
                (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
                (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
                (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
                (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
                (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END))) as balance');

        $this->db->select('((
                ((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
                (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
                (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
                (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
                (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
                (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
                (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
                (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
                (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
                (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
                (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
                (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
                (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
                (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
                (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
                (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
                (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
                (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
                (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
                (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
                (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
                (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
                (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
                (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
                (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
                (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
                (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
                (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
                (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
                (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
                (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END)) /
                (CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                else 0 END)) * 100) as persen_mpp');

        $this->db->select('(100 - ((
                ((CASE WHEN c.date_1 = "W" THEN 0 ELSE CAST(c.date_1 AS UNSIGNED) END) +
                (CASE WHEN c.date_2 = "W" THEN 0 ELSE CAST(c.date_2 AS UNSIGNED) END) +
                (CASE WHEN c.date_3 = "W" THEN 0 ELSE CAST(c.date_3 AS UNSIGNED) END) +
                (CASE WHEN c.date_4 = "W" THEN 0 ELSE CAST(c.date_4 AS UNSIGNED) END) +
                (CASE WHEN c.date_5 = "W" THEN 0 ELSE CAST(c.date_5 AS UNSIGNED) END) +
                (CASE WHEN c.date_6 = "W" THEN 0 ELSE CAST(c.date_6 AS UNSIGNED) END) +
                (CASE WHEN c.date_7 = "W" THEN 0 ELSE CAST(c.date_7 AS UNSIGNED) END) +
                (CASE WHEN c.date_8 = "W" THEN 0 ELSE CAST(c.date_8 AS UNSIGNED) END) +
                (CASE WHEN c.date_9 = "W" THEN 0 ELSE CAST(c.date_9 AS UNSIGNED) END) +
                (CASE WHEN c.date_10 = "W" THEN 0 ELSE CAST(c.date_10 AS UNSIGNED) END) +
                (CASE WHEN c.date_11 = "W" THEN 0 ELSE CAST(c.date_11 AS UNSIGNED) END) +
                (CASE WHEN c.date_12 = "W" THEN 0 ELSE CAST(c.date_12 AS UNSIGNED) END) +
                (CASE WHEN c.date_13 = "W" THEN 0 ELSE CAST(c.date_13 AS UNSIGNED) END) +
                (CASE WHEN c.date_14 = "W" THEN 0 ELSE CAST(c.date_14 AS UNSIGNED) END) +
                (CASE WHEN c.date_15 = "W" THEN 0 ELSE CAST(c.date_15 AS UNSIGNED) END) +
                (CASE WHEN c.date_16 = "W" THEN 0 ELSE CAST(c.date_16 AS UNSIGNED) END) +
                (CASE WHEN c.date_17 = "W" THEN 0 ELSE CAST(c.date_17 AS UNSIGNED) END) +
                (CASE WHEN c.date_18 = "W" THEN 0 ELSE CAST(c.date_18 AS UNSIGNED) END) +
                (CASE WHEN c.date_19 = "W" THEN 0 ELSE CAST(c.date_19 AS UNSIGNED) END) +
                (CASE WHEN c.date_20 = "W" THEN 0 ELSE CAST(c.date_20 AS UNSIGNED) END) +
                (CASE WHEN c.date_21 = "W" THEN 0 ELSE CAST(c.date_21 AS UNSIGNED) END) +
                (CASE WHEN c.date_22 = "W" THEN 0 ELSE CAST(c.date_22 AS UNSIGNED) END) +
                (CASE WHEN c.date_23 = "W" THEN 0 ELSE CAST(c.date_23 AS UNSIGNED) END) +
                (CASE WHEN c.date_24 = "W" THEN 0 ELSE CAST(c.date_24 AS UNSIGNED) END) +
                (CASE WHEN c.date_25 = "W" THEN 0 ELSE CAST(c.date_25 AS UNSIGNED) END) +
                (CASE WHEN c.date_26 = "W" THEN 0 ELSE CAST(c.date_26 AS UNSIGNED) END) +
                (CASE WHEN c.date_27 = "W" THEN 0 ELSE CAST(c.date_27 AS UNSIGNED) END) +
                (CASE WHEN c.date_28 = "W" THEN 0 ELSE CAST(c.date_28 AS UNSIGNED) END) +
                (CASE WHEN c.date_29 = "W" THEN 0 ELSE CAST(c.date_29 AS UNSIGNED) END) +
                (CASE WHEN c.date_30 = "W" THEN 0 ELSE CAST(c.date_30 AS UNSIGNED) END) +
                (CASE WHEN c.date_31 = "W" THEN 0 ELSE CAST(c.date_31 AS UNSIGNED) END)) /
                (CASE WHEN MAX(a.revision) = 0 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                WHEN MAX(a.revision) = 1 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 1 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                WHEN MAX(a.revision) = 2 THEN SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END) + (SUM(CASE WHEN a.revision = 2 THEN a.prod_plan else 0 END) - SUM(CASE WHEN a.revision = 0 THEN a.prod_plan else 0 END))
                else 0 END)) * 100)) as persen_dev');
        $this->db->from('generate_mps_detail as a');
        $this->db->join('mst_item d', 'a.product_no = d.item_id');
        $this->db->join('mst_customer as b', "d.id_customer = b.number", 'left');
        $this->db->join('generate_mpp as c', "a.product_no = c.product_no and a.p_month = c.p_month and a.p_year = c.p_year", "left");
        $this->db->where('a.p_month', $filter_month);
        $this->db->where('a.p_year', $filter_year);
        $this->db->where(empty($filter_customer_id)?'1':'a.customer_id', empty($filter_customer_id)?'1':$filter_customer_id);
        $this->db->where(empty($filter_product_no)?'1':'a.product_no', empty($filter_product_no)?'1':$filter_product_no);
        $this->db->where('a.ltpp_month2', "$filter_year-$filter_month-01");
        $this->db->group_by('a.customer_id, a.product_no, a.p_month, a.p_year');
        if ($filter_balance == 'P') {
        $this->db->having('balance >=', 0);
        } elseif ($filter_balance == 'M') {
        $this->db->having('balance <', 0);
        }
        $this->db->order_by('balance', 'desc');
        // $query = $this->db->get_compiled_select();

        // echo $query;
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
                            <small>MPS vs MPP</small>
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
        </table>

        

        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20" style="text-align:center;">No</th>
                <th rowspan="2" style="text-align:center;">Customer</th>
                <th rowspan="2" style="text-align:center;">Product No</th>
                <th rowspan="2" style="text-align:center;">Product Name</th>
                <th colspan="3" style="text-align:center;">Production Plan</th>
                <th rowspan="2" style="text-align:center;">Rev 0 VS Rev 1</th>
                <th rowspan="2" style="text-align:center;">Rev 0 VS Rev 2</th>
                <th rowspan="2" style="text-align:center;">Total MPS</th>
                <th rowspan="2" style="text-align:center;">Total MPP</th>
                <th rowspan="2" style="text-align:center;">Balance</th>
                <th rowspan="2" style="text-align:center;">Balance</th>
                <th rowspan="2" style="text-align:center;">%MPP</th>                
                <th rowspan="2" style="text-align:center;">%Dev</th>
            </tr>
            <tr>
                <th style="text-align:center;">Rev 0</th>
                <th style="text-align:center;">Rev 1</th>
                <th style="text-align:center;">Rev 2</th>
            </tr>';


        $no = 1;

        foreach ($records as $data) {
            if(floatval($data['rev_vs_1']) >= 20){
                $style = "background:red;color:white;";
            }elseif(floatval($data['rev_vs_1']) < 0){
                $style = "background:orange;color:white;";
            }else{
                $style = "";
            }

            if(floatval($data['persen_mpp']) >= 20){
                $style2 = "background:red;color:white;";
            }elseif(floatval($data['persen_mpp']) < 0){
                $style2 = "background:orange;color:white;";
            }else{
                $style2 = "";
            }

            if(floatval($data['persen_dev']) >= 20){
                $style3 = "background:red;color:white;";
            }elseif(floatval($data['persen_dev']) < 0){
                $style3 = "background:orange;color:white;";
            }else{
                $style3 = "";
            }
           
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_name'] . '</td>
                            <td style="text-align:right;">' . $data['revision_0'].'</td>
                            <td style="text-align:right;">' . $data['revision_1'].'</td>
                            <td style="text-align:right;">' . $data['revision_2'].'</td>
                            <td style="text-align:right; '.$style.'">' . number_format(floatval($data['rev_vs_1']), 2) . "%" . '</td>
                            <td style="text-align:right;">' . number_format(floatval($data['rev_vs_2']), 2) . "%" . '</td>
                            <td style="text-align:right;">' . $data['bal_prod_plan'] . '</td>
                            <td style="text-align:right;">' . $data['total_mpp'] . '</td>
                            <td style="text-align:right;">' . $data['balance'] . '</td>
                            <td style="text-align:right;">' . $data['balance'] . '</td>
                            <td style="text-align:right; '.$style2.'">' . numberDecimalFormat($data['persen_mpp']) . '</td>
                            <td style="text-align:right; '.$style3.'">' . numberDecimalFormat($data['persen_dev']) . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';

        echo $html;

    }

}


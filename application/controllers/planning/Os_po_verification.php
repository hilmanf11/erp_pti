<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Os_po_verification extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->load->library('email');
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
            $this->load->view('planning/os_po_verification');
        } else {
            redirect('error_access');
        }
    }

    public function readVendors(){
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $query = $this->db->query("SELECT DISTINCT (CASE WHEN askara = 'T' THEN '006' ELSE `number` END) as number, (CASE WHEN askara = 'T' THEN 'PT. ASKARA INTERNAL' ELSE `name` END) as `name` from supplier WHERE `name` LIKE '%$post%' ORDER BY `name` ASC");
        $records = $query->result_object();
        echo json_encode($records);
    }

    public function readProdfam(){
        $this->dummy = $this->load->database('dummy', TRUE);

        $query = $this->dummy->query("SELECT DISTINCT c.pfm_id, UPPER(c.pfm_name) as pfm_name FROM por a JOIN mst_item b ON a.item_id = b.item_id JOIN r_prodfam c ON b.pfm_id = c.pfm_id ORDER BY pfm_name asc");
        $records = $query->result_object();
        echo json_encode($records);
    }

    public function readVerificationPo(){
        $this->dummy = $this->load->database('dummy', TRUE);

        if($this->input->get()){
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_vendor_id = base64_decode($this->input->get('filter_vendor_id'));
            $filter_product_family = base64_decode($this->input->get('filter_product_family'));

            $this->dummy->select("d.ven_id, d.ven_name, d.ven_cp, d.ven_address, d.ven_email, c.pfm_id, c.pfm_name, a.item_id, b.item_name, a.por_pocreqqty, a.por_pocid, a.por_pocreqdate, SUM(a.por_receiveqty) as por_receiveqty, (a.por_pocreqqty - SUM(a.por_receiveqty)) as ost, e.poc_date");
            $this->dummy->from('por a');
            $this->dummy->join('mst_item b', "a.item_id = b.item_id");
            $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
            $this->dummy->join('r_vendor d', 'a.ven_id = d.ven_id');
            $this->dummy->join('poc e', 'e.poc_id = a.por_pocid and a.item_id = e.item_id', 'left');
            // if($filter_from != ""){
            //     $this->dummy->where('a.por_pocreqdate >=', $filter_from);
            // }
            $this->dummy->like('a.ven_id', $filter_vendor_id);
            $this->dummy->like('b.pfm_id', $filter_product_family);
            $this->dummy->having('(a.por_pocreqqty - SUM(a.por_receiveqty)) >', 0);
            $this->dummy->group_by('d.ven_id');
            $this->dummy->group_by('d.ven_name');
            $this->dummy->group_by('d.ven_cp');
            $this->dummy->group_by('d.ven_address');
            $this->dummy->group_by('d.ven_email');
            $this->dummy->group_by('c.pfm_id');
            $this->dummy->group_by('c.pfm_name');
            $this->dummy->group_by('a.item_id');
            $this->dummy->group_by('b.item_name');
            $this->dummy->group_by('a.por_pocreqqty');
            $this->dummy->group_by('a.por_pocid');
            $this->dummy->group_by('a.por_pocreqdate');
            $this->dummy->group_by('e.poc_date');
            $this->dummy->order_by('a.item_id', 'asc');
            $this->dummy->order_by('a.por_pocid', 'asc');
            $records = $this->dummy->get()->result_array();

            die(json_encode($records));
        }
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

            //Filter Data
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_vendor_id = base64_decode($this->input->get('filter_vendor_id'));
            $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));

            //Select Query
            $this->db->select("a.*, (CASE WHEN b.id_user = '' THEN 'APPROVE' ELSE 'NOT APPROVE' END) as send");
            $this->db->from('po_verification a');
            $this->db->join('notification b', 'a.id = b.table_id', 'left');
            //$this->db->where('b.id_user', '');
            // if($filter_from != ""){
            //     $this->db->where('a.poc_date <=', $filter_from);
            // }
            $this->db->like('a.ven_id', $filter_vendor_id);
            $this->db->like('a.pfm_id', $filter_product_family);
            //$this->db->having('(a.por_pocreqqty - SUM(a.por_receiveqty)) >', 0);
            $this->db->group_by('a.ven_id');
            $this->db->group_by('a.pfm_id');
            $this->db->order_by('a.ven_name', 'asc');
            $this->db->order_by('a.pfm_name', 'asc');
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

    public function datatableDetails()
    {
        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_vendor_id = base64_decode($this->input->get('filter_vendor_id'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));

        //Select Query
        $this->db->select("a.*, b.id_user");
        $this->db->from('po_verification a');
        $this->db->join('notification b', 'a.id = b.table_id', 'left');
        //$this->db->where('b.id_user', '');
        // if($filter_from != ""){
        //     $this->db->where('a.poc_date <=', $filter_from);
        // }
        $this->db->where('a.ven_id', $filter_vendor_id);
        $this->db->where('a.pfm_id', $filter_product_family);
        $this->db->order_by('a.poc_date', 'asc');
        //Get Data Array
        $records = $this->db->get()->result_array();

        $html = '<html>
                    <head>
                        <title>Print Out Mail</title>
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                    </style>
                    <body>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>OUTSTANDING PURCHASE ORDER CONFIRMATION</h3>
                                </center>
                                <div style="float:left; width:100%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="200">SYSTEM CUTOFF</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime($records[0]['cutoff_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="200">TO</td>
                                            <td width="10">:</td>
                                            <td><b>' . @strtoupper($records[0]['ven_cp']) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="200">VENDOR</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['ven_id'] . ' - '.@strtoupper($records[0]['ven_name']).'</b></td>
                                        </tr>
                                        <tr>
                                            <td width="200">ADDRESS</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['ven_address'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>NO</th>
                                        <th>PO NO</th>
                                        <th>PO DATE</th>
                                        <th>LEADTIME</th>
                                        <th>PRODUCT NO</th>
                                        <th>PRODUCT NAME</th>
                                        <th>QTY</th>
                                        <th>ETD</th>
                                        <th>ETA</th>
                                        <th>DELIVERY</th>
                                        <th>OS PO</th>
                                    </tr>';
                                $no = 1;
                                foreach ($records as $record) {
                                    if($record['id_user'] != ""){
                                        $style = "style='background:red;color:white;'";
                                    }else{
                                        $style = "";
                                    }

                                    $this->dummy = $this->load->database('dummy', TRUE);
                                    $this->dummy->select("item_prcldtm");
                                    $this->dummy->from("mst_item");
                                    $this->dummy->where('item_id', $record['product_no']);
                                    $item = $this->dummy->get()->row();

                                    $html .= '  <tr>
                                                    <td style="text-align:center">' . $no . '</td>
                                                    <td>' . $record['doc_no'] . '</td>
                                                    <td>' . $record['poc_date'] . '</td>
                                                    <td>' . $item->item_prcldtm . '</td>
                                                    <td>' . $record['product_no'] . '</td>
                                                    <td>' . $record['product_name'] . '</td>
                                                    <td style="text-align:right;">' . @number_format(($record['qty'])) . '</td>
                                                    <td></td>
                                                    <td>' . $record['eta'] . '</td>
                                                    <td style="text-align:right;">' . @number_format($record['delivery']) . '</td>
                                                    <td style="text-align:right;">' . @number_format($record['os_po']) . '</td>
                                                </tr>';
                                    $no++;
                                }
        $html .= "</table><p>PLEASE CONFIRM BEFORE <b>".date("d F Y", strtotime('-7 days', strtotime($records[0]['eta'])))."</b></p></div></div></body>";
        die($html);
    }

    public function sendMail()
    {

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_vendor_id = base64_decode($this->input->get('filter_vendor_id'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));

        //Select Query
        $this->db->select("a.*, b.id_user");
        $this->db->from('po_verification a');
        $this->db->join('notification b', 'a.id = b.table_id', 'left');
        $this->db->where('b.id_user', '');
        // if($filter_from != ""){
        //     $this->db->where('a.poc_date <=', $filter_from);
        // }
        $this->db->where('a.ven_id', $filter_vendor_id);
        $this->db->where('a.pfm_id', $filter_product_family);
        $this->db->order_by('a.poc_date', 'asc');
        //Get Data Array
        $records = $this->db->get()->result_array();

        $config = array(
            'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
            'smtp_host' => 'aeconsys.com',
            'smtp_port' => 465,
            'smtp_user' => 'no-reply@aeconsys.com',
            'smtp_pass' => 'Hilman1196',
            'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
            'mailtype' => 'html', //plaintext 'text' mails or 'html'
            'smtp_timeout' => '4', //in seconds
            'charset' => 'iso-8859-1',
            'wordwrap' => TRUE
        );

        $this->email->initialize($config);
        $this->email->from('no-reply@aeconsys.com', 'BANSHU ELECTRIC INDONESIA');
        $this->email->to(@$records[0]['ven_email']);
        $this->email->subject('Outstanding PO Verification');

        $html = '<html>
                    <head>
                        <title>Print Out Mail</title>
                    </head>
                    <body style="font-family: Arial, Helvetica, sans-serif;">
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>OUTSTANDING PURCHASE ORDER CONFIRMATION</h3>
                                </center>
                                <div style="float:left; width:100%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="200">SYSTEM CUTOFF</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y", strtotime($records[0]['cutoff_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="200">TO</td>
                                            <td width="10">:</td>
                                            <td><b>' . @strtoupper($records[0]['ven_cp']) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="200">VENDOR</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['ven_id'] . ' - '.@strtoupper($records[0]['ven_name']).'</b></td>
                                        </tr>
                                        <tr>
                                            <td width="200">ADDRESS</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['ven_address'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table style="border-collapse: collapse; width: 100%; font-size: 12px;" border="1">
                                    <tr>
                                        <th>NO</th>
                                        <th>PO NO</th>
                                        <th>PO DATE</th>
                                        <th>LEADTIME</th>
                                        <th>PRODUCT NO</th>
                                        <th>PRODUCT NAME</th>
                                        <th>QTY</th>
                                        <th>ETD</th>
                                        <th>ETA</th>
                                        <th>DELIVERY</th>
                                        <th>OS PO</th>
                                    </tr>';
                                $no = 1;
                                foreach ($records as $record) {
                                    
                                    $this->dummy = $this->load->database('dummy', TRUE);
                                    $this->dummy->select("item_prcldtm");
                                    $this->dummy->from("mst_item");
                                    $this->dummy->where('item_id', $record['product_no']);
                                    $item = $this->dummy->get()->row();

                                    $html .= '  <tr>
                                                    <td style="text-align:center">' . $no . '</td>
                                                    <td>' . $record['doc_no'] . '</td>
                                                    <td>' . $record['poc_date'] . '</td>
                                                    <td>' . $item->item_prcldtm . '</td>
                                                    <td>' . $record['product_no'] . '</td>
                                                    <td>' . $record['product_name'] . '</td>
                                                    <td style="text-align:right;">' . @number_format(($record['qty'])) . '</td>
                                                    <td></td>
                                                    <td>' . $record['eta'] . '</td>
                                                    <td style="text-align:right;">' . @number_format($record['delivery']) . '</td>
                                                    <td style="text-align:right;">' . @number_format($record['os_po']) . '</td>
                                                </tr>';
                                    $no++;
                                }
        $html .= "</table><p>PLEASE CONFIRM BEFORE <b>".date("d F Y", strtotime('-7 days', strtotime($records[0]['eta'])))."</b></p></div></div></body>";

        $this->email->message($html);
        $email = $this->email->send();

        if (!filter_var(@$records[0]['ven_email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array("title" => "Not Registered", "message" => "Email not register in Vendor List", "theme" => "error"));
        } elseif ($email) {
            $this->api->update('po_verification', [
                "ven_id" => $filter_vendor_id,
                "pfm_id" => $filter_product_family
            ], ["mail_send" => 1, "mail_date" => date("Y-m-d")]);

            echo json_encode(array("title" => "Delivered", "message" => "Email PO Verification successfully sent to " . @$records[0]['ven_email'], "theme" => "success"));
        } else {
            echo json_encode(array("title" => "Failed", "message" => "Email PO Verification unseccessfull sent to " . @$records[0]['ven_email'], "theme" => "error"));
        }
    }

    function create(){
        $post = $this->input->post('data');
        $cutoff_date = $this->input->post('cutoff_date');

        $po_verification = $this->api->reads("po_verification", [], [
            "ven_id" => $post['ven_id'],
            "product_no" => $post['item_id']
        ]);

        $postFinal = array(
            "cutoff_date" => $cutoff_date,
            "eta" => $post['por_pocreqdate'],
            "poc_date" => $post['poc_date'],
            "doc_no" => $post['por_pocid'],
            "ven_id" => $post['ven_id'],
            "ven_name" => $post['ven_name'],
            "ven_cp" => $post['ven_cp'],
            "ven_address" => $post['ven_address'],
            "ven_email" => $post['ven_email'],
            "pfm_id" => $post['pfm_id'],
            "pfm_name" => $post['pfm_name'],
            "product_no" => $post['item_id'],
            "product_name" => $post['item_name'],
            "qty" => $post['por_pocreqqty'],
            "delivery" => $post['por_receiveqty'],
            "os_po" => $post['ost'],
        );

        if(count($po_verification) > 0){
            $send = $this->api->update('po_verification', ["ven_id" => $post['ven_id'], "product_no" => $post['item_id']], $postFinal);
            echo $send;
        }else{
            $send = $this->api->create('po_verification', $postFinal, "POV", "po_Verification");
            echo $send;
        }
    }
}
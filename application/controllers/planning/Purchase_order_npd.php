<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_npd extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->load->library('Ciqrcode');
        $this->load->library('dompdf_gen');
        $this->load->library('email');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Part No', 'required|min_length[2]|max_length[50]|is_unique[generate_mps.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/purchase_order_npd');
        } else {
            redirect('error_access');
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
            $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
            $filter_po = trim(base64_decode($this->input->get('filter_po')));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, SUM(a.qty) as total_qty, SUM(a.total) as total_amount');
            $this->db->from('purchase_order_npd a');
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            $this->db->like('a.product_family', $filter_product_family);
            $this->db->like('a.item_id', $filter_part_no);
            $this->db->like('a.supplier_name', $filter_supplier);
            $this->db->like('a.purchase_order_no', $filter_po);
            $this->db->group_by('a.purchase_order_no');
            $this->db->order_by('a.supplier_name');
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

    public function datatableDetails($purchase_order_no, $revision){
        $purchase_order_no = base64_decode($purchase_order_no);
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));

        //Select Query
        $this->db->select('a.*');
        $this->db->from('purchase_order_npd a');
        $this->db->where('a.purchase_order_no', $purchase_order_no);
        $this->db->where('a.revision', $revision);
        $this->db->like('a.item_id', $filter_part_no);
        $this->db->order_by('a.item_id');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $total_qty = 0;
        $total_amount = 0;
        foreach ($records as $record) {
            $total_qty += $record['qty'];
            $total_amount += $record['total'];
            array_push($data, $record);
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, [
            'rows' => $data,
            'footer' => array([
                "item_id" => "Grand Total",
                "qty" => $total_qty,
                "price" => ($total_amount / $total_qty),
                "total" => $total_amount
            ])
        ]);
        echo json_encode($result);
    }

    public function purchaseOrderNo($code){
        $code       = base64_decode($code);
        $sqlGetID	= $this->db->query("SELECT max(purchase_order_no) as kode FROM purchase_order_npd WHERE purchase_order_no LIKE '%$code%'");
        $rowID      = $sqlGetID->row();
        $kode 		= $rowID->kode;

        $urutan = (int) substr($kode, -2);
        $urutan++;
        $autoID	= sprintf("%02s", $urutan);

        die(json_encode(array("name" => $code . $autoID)));
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->api->create('purchase_order_npd', $post, "PO", "JUTA");
            echo $send;
        }
    }

    public function updateDetails(){
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));

            $post = $this->input->post();
            $total = round($post['qty'] * $post['price'], 4);
            // $revision = $post['revision'] + 1;
            $revision = $post['revision'];
            $databf = $this->api->read("purchase_order_npd", [], ["id" => $id]);

            $send = $this->db->update('purchase_order_npd', [
                    "purchase_order_no" => $post['purchase_order_no'], 
                    "supplier_id" => $post['supplier_id'], 
                    "qty" => $post['qty'], 
                    "price" => $post['price'], 
                    "currency" => $post['currency'], 
                    "total" => $total, 
                    "reason" => $post['reason'], 
                    "revision" => $revision,
                    "status_edit" => 1,
                    "log" => json_encode(["qty" => $databf->qty, "total" => $databf->total, "revision" => $databf->revision, "log" => $databf->log, "reason" => $databf->reason])
                ], ["id" => $id]
            );

            if($send){
                echo json_encode(array("title" => "Good Job", "message" => "Update data Successfully", "theme" => "success"));
            }else{
                echo json_encode(array("title" => "Failed", "message" => "Update data Failed", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('purchase_order_npd', $data);
        echo $send;
    }

    public function print_po($po_no, $export = "")
    {
        $purchase_order_npd_total = $this->api->reads('purchase_order_npd', [], ["purchase_order_no" => base64_decode($po_no)]);
        $purchase_order_npd = $this->api->read('purchase_order_npd', [], ["purchase_order_no" => base64_decode($po_no)], "", "revision", "desc");
        $approval = $this->api->read('approval', [], ["table_name" => "purchase_order_npd"]);

        $user_3 = $this->api->read('user', [], ["username" => @$approval->user_approval_2]);
        $user_4 = $this->api->read('user', [], ["username" => @$approval->user_approval_3]);
        
        $qr_user_3 = "";
        $qr_user_4 = "";
        
        if($purchase_order_npd->approved == 3){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
        }elseif($purchase_order_npd->approved == 4){
            $qr_user_3 = "src='" .base_url('assets/image/qrcode/' . @$user_3->name . '.png')."'";
            $qr_user_4 = "src='" .base_url('assets/image/qrcode/' . @$user_4->name . '.png')."'";
        }

        $config = $this->db->get('config')->row();

        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_order_npd_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_order_npd->purchase_order_no, "assets/image/qrcode/");

        $this->createQrcode(@$user_3->name, "assets/image/qrcode/");
        $this->createQrcode(@$user_4->name, "assets/image/qrcode/");

        if($export == ""){
            $media = "@media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }";
            $media2 = ' <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>';
        }else{
            $media = ".print {
                        zoom: 0.5;
                    }";
            $media2 = "";
        }

        $html = '<html>
                    <head>
                        <title>' . $purchase_order_npd->purchase_order_no . '</title>
                        <link rel="icon" href="' . base_url('assets/image/config/' . $config->favicon) . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        .customers {
                            border-collapse: collapse;
                            font-size: 11px;
                        }
                        .customers td, .customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        .customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        '.$media.'
                    </style>
                    <body>
                        '.$media2.'
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*');
            $this->db->from('purchase_order_npd a');
            $this->db->where('a.purchase_order_no', base64_decode($po_no));
            $this->db->where('a.qty >', 0);
            $this->db->order_by('a.item_id');
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();

            if ($purchase_order_npd->updated_date != null) {
                $revision_date = $purchase_order_npd->updated_date;
            } else {
                $revision_date = $purchase_order_npd->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . base_url('assets/image/config/' . $config->favicon) . '" width="40" />
                                </th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_order_npd->purchase_order_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100"></td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>PURCHASE ORDER</u></h3>
                                    <small>NO : ' . @$purchase_order_npd->purchase_order_no . '</small>
                                </center>
                                <table style="width:100%; font-size:11px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Supplier</td>
                                        <td width="10">:</td>
                                        <td width="73%">
                                            <b>' . @$purchase_order_npd->supplier_name . '</b>
                                        </td>
                                        <td style="text-align:left; padding-right: 20px;" rowspan="7">
                                            PO Date: <b>' . date("d F Y", strtotime($purchase_order_npd->purchase_order_date)) . '</b><br>
                                            Revision: <b>' . $purchase_order_npd->revision . '</b><br>
                                            Revision Date: <b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                            Payment Terms: <b>' . $purchase_order_npd->payment_term . ' Days</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Page</td>
                                        <td width="10">:</td>
                                        <td><b>' . $hal  . '</b> of <b> ' . $page . '</b></td>
                                    </tr>
                                </table>
                                <table class="customers" style="width:100%;">
                                    <tr>
                                        <th rowspan="2" style="text-align:center;">No</th>
                                        <th rowspan="2" style="text-align:center;">Part No</th>
                                        <th rowspan="2" style="text-align:center;">Part No Vendor</th>
                                        <th rowspan="2" style="text-align:center;">Part Name</th>
                                        <th rowspan="2" style="text-align:center;">Qty</th>
                                        <th rowspan="2" style="text-align:center;">Unit</th>
                                        <th rowspan="2" style="text-align:center;">Unit<br>Price</th>
                                        <th rowspan="2" style="text-align:center;">Currency</th>
                                        <th rowspan="2" style="text-align:center;">Amount</th>
                                        <th colspan="3" style="text-align:center;">Forecast</th>
                                    </tr>
                                    <tr>
                                        <th style="text-align:center;">M1</th>
                                        <th style="text-align:center;">M2</th>
                                        <th style="text-align:center;">M3</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $item_id = $record['item_id'];
                $revision = $record['revision'];
                $ending = date("Y-m-t", strtotime($record['p_year'] . "-" . $record['p_month'] . "-01"));
                $leadtime = date('Y-m-01', strtotime('+'.$record['leadtime'].' days', strtotime($ending)));

                $subtotal += ($record['total']);
                if ($record['currency'] == "USD") {
                    $digits = 4;
                } elseif ($record['currency'] == "JPY") {
                    $digits = 2;
                } else {
                    $digits = 0;
                }
                
                $html .= '  <tr>
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_id'] . '</td>
                                <td>' . $record['item_vendor'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty']) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['price'], $digits) . '</td>
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format($record['total'], $digits) . '</td>
                                <td style="text-align:right;">' . number_format($record['forecast_1']) . '</td>
                                <td style="text-align:right;">' . number_format($record['forecast_2']) . '</td>
                                <td style="text-align:right;">' . number_format($record['forecast_3']) . '</td>
                            </tr>';
                $row++;
                $no++;
            }

            if (($i + 1) == $page) {
                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="6" rowspan="5">
                                    <b>Note :</b> <br>';
                $html .= '      </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Sub Total</th>
                                <th style="text-align:right;">' . number_format($subtotal, $digits) . '</th>
                                <th style="text-align:left" colspan="3" rowspan="4"></th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">VAT (' . @$purchase_order_npd->vat . '%)</th>
                                <th style="text-align:right;">' . number_format(round((@$subtotal * @$purchase_order_npd->vat) / 100), $digits) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Down Payment</th>
                                <th style="text-align:right;">-</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Total Amount</th>
                                <th style="text-align:right;">' . number_format($subtotal + ((@$subtotal * @$purchase_order_npd->vat) / 100), $digits) . '</th>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }
            
            if (($i + 1) == $page) {
                $html .= '  <div style="width:100%; display: grid; grid-template-columns: auto auto auto;">
                                <div style="width:50%;">
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th width="200" style="text-align:center;">Supplier Approval</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:center;">(...................................)</th>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width:50%; position: absolute; right: 50px;">
                                    <table style="margin-top:20px; font-size:12px;">
                                        <tr>
                                            <th colspan="2" width="800" style="text-align:center;">Approved By</th>
                                        </tr>
                                        <tr>
                                            <th style="height:80px;"><img '.$qr_user_3.' width="80"/></th>
                                            <th style="height:80px;"><img '.$qr_user_4.' width="80"/></th>
                                        </tr>
                                        <tr>
                                            <th style="height:20px; text-align:center;">'.@$user_3->name.'<br>'.@$user_3->position.'</th>
                                            <th style="height:20px; text-align:center;">'.@$user_4->name.'<br>'.@$user_4->position.'</th>
                                        </tr>
                                    </table>
                                    <br>
                                    <i style="font-size:8px;">Electronic Auto Generating Approval No Need Signature</i>
                                </div>
                            </div>

                        <br>
                        <b style="font-size:12px;">Term & Condition</b>
                        <br>
                        <table style="width:100%; font-size:12px; margin-top:20px;">
                            <tr>
                                <td width="20">1.</td>
                                <td>Please Acknowledge us in one week of time upon Receipt of PO <br>email to : takao.masahiko@tb-banshu.com, procurement01@banshu-ind.com, procurement02@banshu-ind.com</td>
                            </tr>
                            <tr>
                                <td width="20">2.</td>
                                <td>We will buy the next month confirmed FC in this PO</td>
                            </tr>
                            <tr>
                                <td width="20">3.</td>
                                <td>The PO quantity and forecast quantity is as per our customer demand</td>
                            </tr>
                        </table><br>
                        PLEASE PROCESS OUR PO FORECAST
                    </div>
                </div>';
            }else{
                $html .= "</div></div>";
            }

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;

            // <img '.$qr_user_4.' width="80"/>
        }
        
        if($export == ""){
            $html .= '<script>window.print()</script>';
            die($html);
        }else{
            // Load HTML content
            $this->dompdf_gen->loadHtml($html);

            // (Optional) Set paper size and orientation
            $this->dompdf_gen->setPaper('A4', 'potrait');

            // Render the HTML as PDF
            $this->dompdf_gen->render();

            // Output the generated PDF (1 = download and 0 = preview)
            // $this->dompdf_gen->stream("sample.pdf", array("Attachment" => 0));

            $filePath = FCPATH . 'assets/po/'.base64_decode($po_no).'.pdf'; // FCPATH ensures the path is relative to the project root
            $this->dompdf_gen->save($filePath);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("YmdHis");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $filter_part_no = trim(base64_decode($this->input->get('filter_part_no')));
        $filter_supplier = trim(base64_decode($this->input->get('filter_supplier')));
        $filter_po = trim(base64_decode($this->input->get('filter_po')));

        //Select Query
        $this->db->select('a.*');
        $this->db->from('purchase_order_npd a');
        $this->db->like('a.p_month', $filter_month);
        $this->db->like('a.p_year', $filter_year);
        $this->db->like('a.revision', $filter_revision);
        if($filter_product_family != ""){
            $this->db->where('a.product_family_name', $filter_product_family);
        }
        if($filter_part_no != ""){
            $this->db->where('a.item_id', $filter_part_no);
        }
        if($filter_supplier != ""){
            $this->db->where('a.supplier_name', $filter_supplier);
        }
        if($filter_po != ""){
            $this->db->where('a.purchase_order_no', $filter_po);
        }
        $this->db->order_by('a.supplier_name', 'asc');
        $this->db->order_by('a.item_id', 'asc');
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
                            <small>PURCHASE ORDER NPD</small>
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
                <th width="20" style="text-align:center;">No</th>
                <th style="text-align:center;">PO No</th>
                <th style="text-align:center;">PO Date</th>
                <th style="text-align:center;">Part No</th>
                <th style="text-align:center;">Part Name</th>
                <th style="text-align:center;">Part Supplier</th>
                <th style="text-align:center;">Product Family</th>
                <th style="text-align:center;">Supplier Name</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Price</th>
                <th style="text-align:center;">Total</th>
                <th style="text-align:center;">Currency</th>
                <th style="text-align:center;">Uom</th>
                <th style="text-align:center;">Leadtime</th>
                <th style="text-align:center;">Forecast 1</th>
                <th style="text-align:center;">Forecast 2</th>
                <th style="text-align:center;">Forecast 3</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['purchase_order_no'] . '</td>
                            <td>' . $data['purchase_order_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_vendor'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="text-align:right;">' . $data['qty'] . '</td>
                            <td style="text-align:right;">' . round($data['price'], 2) . '</td>
                            <td style="text-align:right;">' . round($data['total'], 2) . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td style="text-align:right;">' . $data['forecast_1'] . '</td>
                            <td style="text-align:right;">' . $data['forecast_2'] . '</td>
                            <td style="text-align:right;">' . $data['forecast_3'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
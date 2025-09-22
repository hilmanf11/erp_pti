<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class purchase_returns extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('api');
        //Validasi Form
        $this->form_validation->set_rules('item_no', 'Item No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/purchase_returns');
        } else {
            redirect('error_access');
        }
        // redirect('maintenance');
    }

    public function readItems()
    {
        if ($this->input->get()) {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $po_no = base64_decode($this->input->get('po_no'));

            $this->db->select('a.supplier_id, a.item_id, b.name as supplier_name, a.item_name, a.qty');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.purchase_order_no', $po_no);
            $this->db->like('a.item_id', $post);
            $this->db->order_by('a.item_id', 'asc');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function readReturnNo()
    {
        $records = $this->api->query("SELECT a.return_no, a.return_date, a.return_name, a.po_no, a.supplier_id, b.name as supplier_name 
            FROM purchase_returns a
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.status_is = 0 GROUP BY a.return_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function return_no($number)
    {
        $datenow    = $number."/".date("m/y");
        $sqlGetID   = $this->db->query("SELECT max(return_no) as kode FROM purchase_returns WHERE return_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, 0, 4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $autoID."/".$datenow;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.id, a.return_no, a.return_date, a.return_name, sum(a.qty) as qty, a.status_is');
            $this->db->from('purchase_returns a');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->like('a.return_no', $filter_return_no);
            $this->db->group_by('a.return_no');
            $this->db->order_by('a.return_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => "h".$record['id'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "qty" => $record['qty'],
                    "status" => $record['status_is'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $id = substr($id, 1);
            $return = $this->api->read("purchase_returns", [], ["id" => $id]);

            $this->db->select('a.*,
                d.name as supplier_name,
                c.name as uom');
            $this->db->from('purchase_returns a');
            $this->db->join('mst_item_raw b', 'a.item_no = b.item_id');
            $this->db->join('uom c', 'b.um_id = c.number', 'left');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->where('a.return_no', $return->return_no);
            $this->db->order_by('d.name', 'ASC');
            $records = $this->db->get()->result_array();

            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['id'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "item_number" => $record['item_no'],
                    "item_name" => $record['item_name'],
                    "supplier_name" => $record['supplier_name'],
                    "remarks" => $record['remarks'],
                    "uom" => $record['uom'],
                    "qty" => $record['qty'],
                    "status" => $record['status_is'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                );//"description" => $record['description'],
            }
            echo json_encode($arr);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $purchase_returns = $this->api->read('purchase_returns', [], ["return_no" => $post['return_no'], "item_no" => $post['item_no']]);

                if (@$purchase_returns->id != "") {
                    echo json_encode(array("title" => "Duplicated", "message" => "Item No " . $post['item_no'] . " Data Duplicated", "theme" => "error"));
                } else {
                    $send   = $this->api->create('purchase_returns', $post, "PRTN", "PRTN");
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->api->update('purchase_returns', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('purchase_returns', $data);
        echo $send;
    }

    public function print_return($return_no)
    {
        $return_no_2 = base64_decode($return_no);

        $config = $this->db->get('config')->row();

        $this->db->select('supplier_id');
        $this->db->from('purchase_returns');
        $this->db->where('return_no', $return_no_2);
        $this->db->group_by('supplier_id');
        $suppliers = $this->db->get()->result_array();

        //Config Page
        // $rows = 10;
        // $page = ceil(count($purchase_returns) / $rows);
        //Generate QRcode
        $this->createQrcode($return_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $return_no_2 . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                        <h1>Press CTRL + P for Print</h1>
                        <p>Display pages for 10 rows</p>
                        <p>Paper Size A4, Layout Landscape</p>
                        <p>Margin Default, Scale 98</p>
                    </center></div><div class="print">';
        //Loop Page
        $no = 1;
        $page = 1;
        foreach ($suppliers as $supplier) {
            $this->db->select('a.*,
                c.name as uom, 
                d.number as supplier_number, 
                d.name as supplier_name,
                d.type, 
                d.address,
                d.attention, 
                d.telp');
                // b.specification,
            $this->db->from('purchase_returns a');
            $this->db->join('mst_item_raw b', 'a.item_no = b.item_id');
            $this->db->join('uom c', 'b.um_id = c.number', 'left');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->where('a.return_no', $return_no_2);
            $this->db->where('a.supplier_id', $supplier['supplier_id']);
            $this->db->order_by('a.item_no', 'asc');
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . base_url("assets/image/config/". $config->favicon) . '" width="50" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;>
                                    <div style="border:1px solid black; padding:10px;">FRM-PPC-006-00</div>
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>DELIVERY NOTE</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150"><b>Office</b> : <br>
                                                M-Gold Tower 18th Floor<br>
                                                Jl. KH. Noer Ali Bekasi Selatan<br>
                                                17145 Indonesia <br>
                                                Phone : +62-21 2806-7102 / 7103<br>
                                                Fax : +62-21 2806-7101
                                            </td>
                                            <td width="150"><b>Factory</b> : <br>
                                                Gunungsari, Kec Pagaden, Kab Subang, Jawa Barat 41251<br>
                                                Phone : (0264) 204444<br>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:20%;">
                                    <br>
                                </div>
                                <div style="float:left; width:20%;">
                                    <center>
                                        <img src="' . base_url('assets/image/qrcode/' . $return_no . '.png') . '" width="100"/>
                                        <br>
                                        <b style="font-size:10px;">'.$return_no_2.'</b>
                                    </center>
                                    <br><br>
                                </div>
                                <table id="customers" style="width:60%;">
                                    <tr>
                                        <th>Delivery Date</th>
                                        <th>PO Number</th>
                                        <th>Plat No</th>
                                    </tr>
                                    <tr>
                                        <td>'.@$records[0]['return_date'].'</td>
                                        <td>'.@$records[0]['po_no'].'</td>
                                        <td>'.@$records[0]['plat_no'].'</td>
                                    </tr>
                                </table>
                                <br>
                                <div style="width:95%; font-size:12px; padding:10px; border: 1px solid black;">
                                    <b>Ship To : </b><br>
                                    '.$records[0]['supplier_name'].'<br>
                                    '.$records[0]['address'].'
                                </div>
                                <br>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="100">Item No</th>
                                        <th width="100">Item Name</th>
                                        <th width="60">UoM</th>
                                        <th width="60">Qty</th>
                                        <th width="200">Note</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_no'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            $html .= '</div></div>';
            $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                            <table id="customers" style="margin-top:10px; font-size:10px;">
                                <tr>
                                    <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                </tr>
                                <tr>
                                    <th width="200" style="text-align:center;">CUSTOMER STAMP & SIGNATURE</th>
                                    <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                                    <th width="200" style="text-align:center;">DELIVER CONTROL</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;"></th>
                                </tr>
                            </table>
                        </div>';

            if ($page != count($suppliers)) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_returns_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, d.name as uom, c.name as supplier_name');
        $this->db->from('purchase_returns a');
        $this->db->join('mst_item_raw b', 'a.item_no = b.item_id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('uom d', 'b.um_id = d.number');
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.return_date >=', $filter_from);
            $this->db->where('a.return_date <=', $filter_to);
        }
        $this->db->like('a.return_no', $filter_return_no);
        $this->db->order_by('a.return_no', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  base_url("assets/image/config/". $config->favicon) . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>RETURN MATERIAL FROM PRODUCTION TO WAREHOUSE</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Return No</th>
                <th>Return Date</th>
                <th>Return Name</th>
                <th>Supplier Name</th>
                <th>Item No</th>
                <th>Item Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status_is'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['return_no'] . '</td>
                        <td>' . $data['return_date'] . '</td>
                        <td>' . $data['return_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_no'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

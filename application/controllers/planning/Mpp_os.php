<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mpp_os extends CI_Controller
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
            $data['nik'] = $this->api->read('user', [], ['id' => $this->session->id])->number;

            $this->load->view('template/header', $data);
            $this->load->view('planning/mpp_os');
        } else {
            redirect('error_access');
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM mpp_os WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
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
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $customers = [];
            if (empty($filter_customer_id)) {
                $nik = $this->api->read("user", [], ["id" => $this->session->id])->number;
                $this->db->distinct()->select("customer")->from("setting_customer")->where(["user_id" => $nik]);
                $wheres = explode(',', $this->db->get()->row()->customer);
                $this->db->distinct()->select("TRIM(b.number) AS number")
                ->from("mst_line a")->join("mst_customer b", "a.customer_code = b.nickname")
                ->where_in("a.line_no", $wheres);
                foreach ($this->db->get()->result() as $index => $row)
                    { array_push($customers, $row->number); }
            }

            //Select Query
            $this->db->select('a.item_id, a.item_name, b.*, c.circuit_no, d.name as customer_name');
            $this->db->from('mst_item a');
            $this->db->join('mpp_os b', "a.item_id = b.product_no and b.p_month = '$filter_month' and b.p_year = '$filter_year' and b.revision = '$filter_revision'", 'left');
            $this->db->join('wip_mst_wos_cct c', 'a.item_id = c.mstwos_assyno', 'left');
            $this->db->join('mst_customer d', 'a.id_customer = d.number', 'left');
            if ($filter_customer_id != 'all') {
                if (empty($customers)) { $this->db->where('a.id_customer', $filter_customer_id); }
                else { $this->db->where_in('a.id_customer', $customers); }
            }
            if ($filter_customer_id != "") {
                $this->db->where('a.id_customer', $filter_customer_id);
            }
            if ($filter_product_no != "") {
                $this->db->like('a.item_id', $filter_product_no);
            }
            $this->db->order_by('d.name', 'asc');
            // die($this->db->get_compiled_select());

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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $item = $this->api->read('mst_item', [], ["item_id" => $post['product_no']]);
                $customer = $this->api->read('mst_customer', [], ["number" => @$item->id_customer], "", "name", "asc");
                $line = $this->api->read('mst_item_line', [], ["item_id" => $post['product_no']], "", "line_no", "asc");
                $lineName = $this->api->read('mst_line', [], ["line_no" => @$line->line_no], "", "line_no", "asc");
                $mpp = $this->api->reads('mpp_os', [], [
                    "p_month" => $post['p_month'],
                    "p_year" => $post['p_year'],
                    "revision" => $post['revision'],
                    "product_no" => $post['product_no']
                ]);

                $nik = $this->api->read('user', [], ['id' => $this->session->id])->number;
                $this->db->distinct()->select("TRIM(b.customer_code) AS nick")
                ->from("setting_customer a")->join("mst_line b", "FIND_IN_SET(b.line_no, a.customer)")
                ->where(["user_id" => $nik])->order_by("nick");
                $nicks = [];
                foreach ($this->db->get()->result() as $index => $row) { array_push($nicks, $row->nick); }
                // die(var_dump($customer->nickname));
                if (!in_array($customer->nickname, $nicks)) {
                    echo json_encode(array("title" => "Failed", "message" => "You Not Allowed to Entry This Customer, ".$customer->name, "theme" => "error"));
                } elseif (count($mpp) > 0) {
                    echo json_encode(array("title" => "Available", "message" => "OS MPP in Product No " . $post['product_no'] . " is duplicate", "theme" => "error"));
                } else {
                    $dataFinal = array_merge($post, array(
                        "line_no" => @$line->line_no,
                        "line_name" => @$lineName->remarks,
                        "customer_id" => @$customer->number,
                        "customer_name" => @$customer->name
                    ));

                    $send   = $this->api->create('mpp_os', $dataFinal, "MPPOS", "mpp_os");
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
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->api->update('mpp_os', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mpp_os', $data);
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

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                'product_no' => $data->val($i, 2),
                'os_mpp' => $data->val($i, 3),
                'status' => $data->val($i, 4)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Data
            $item = $this->api->read('mst_item', [], ["item_id" => $data['product_no']]);

            //Get CCT
            $items = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => base64_encode($data['product_no'])]);

            $line = $this->api->read('mst_item_line', [], ["item_id" => $data['product_no']], "", "line_no", "asc");
            $lineName = $this->api->read('mst_line', [], ["line_no" => @$line->line_no], "", "line_no", "asc");
            $mpp = $this->api->reads('mpp_os', [], [
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                "product_no" => $data['product_no']
            ]);

            if (empty($item)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_no'] . " Not Found", "theme" => "error"));
            } else {
                $customer = $this->api->read('mst_customer', [], ["number" => @$item->id_customer], "", "name", "asc");
                $nik = $this->api->read('user', [], ['id' => $this->session->id])->number;
                $this->db->distinct()->select("TRIM(b.customer_code) AS nick")
                ->from("setting_customer a")->join("mst_line b", "FIND_IN_SET(b.line_no, a.customer)")
                ->where(["user_id" => $nik])->order_by("nick");
                $nicks = [];
                foreach ($this->db->get()->result() as $index => $row) { array_push($nicks, $row->nick); }
                // die(var_dump($customer->nickname));
                if (!in_array($customer->nickname, $nicks)) {
                    echo json_encode(array("title" => "Failed", "message" => "You Not Allowed to Entry This Customer, ".$customer->name, "theme" => "error"));
                } elseif (count($mpp) > 0) {
                    $this->api->update('mpp_os', [
                        "p_month" => $data['p_month'],
                        "p_year" => $data['p_year'],
                        "revision" => $data['revision'],
                        "product_no" => $data['product_no']
                    ], ["os_mpp" => $data['os_mpp']]);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Updated Successfully", "theme" => "success"));
                } elseif ($data['p_month'] == "" || $data['p_year'] == "" || $data['revision'] == "") {
                    echo json_encode(array("title" => "Empty", "message" => "Period or Revision Cannot Empty", "theme" => "error"));
                } else {
                    $dataFinal = array_merge($data, array(
                        "product_name" => @$item->item_name,
                        "circuit_no" => @$items->circuit_no,
                        "line_name" => @$lineName->remarks,
                        "customer_id" => @$customer->number,
                        "customer_name" => @$customer->name
                    ));

                    $send   = $this->api->create('mpp_os', $dataFinal, "MPPOS", "MPPOS");
                    echo json_encode(array("title" => "Good Job", "message" => "Product No" . $data['product_no'] . " Saved Successfully", "theme" => "success"));
                }
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/mpp_os.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/mpp_os.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/mpp_os.txt";

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
            header("Content-Disposition: attachment; filename=mpp_os_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));
        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

        $customers = [];
        if (empty($filter_customer_id)) {
            $nik = $this->api->read("user", [], ["id" => $this->session->id])->number;
            $this->db->distinct()->select("customer")->from("setting_customer")->where(["user_id" => $nik]);
            $wheres = explode(',', $this->db->get()->row()->customer);
            $this->db->distinct()->select("TRIM(b.number) AS number")
            ->from("mst_line a")->join("mst_customer b", "a.customer_code = b.nickname")
            ->where_in("a.line_no", $wheres);
            foreach ($this->db->get()->result() as $index => $row)
                { array_push($customers, $row->number); }
        }

        //Select Query
        $this->db->select('a.item_id, a.item_name, b.*, c.circuit_no, d.name as customer_name');
        $this->db->from('mst_item a');
        $this->db->join('mpp_os b', "a.item_id = b.product_no and b.p_month = '$filter_month' and b.p_year = '$filter_year' and b.revision = '$filter_revision'", 'left');
        $this->db->join('wip_mst_wos_cct c', 'a.item_id = c.mstwos_assyno', 'left');
        $this->db->join('mst_customer d', 'a.id_customer = d.number', 'left');
        if ($filter_customer_id != 'all') {
            if (empty($customers)) { $this->db->where('a.id_customer', $filter_customer_id); }
            else { $this->db->where_in('a.id_customer', $customers); }
        }
        if ($filter_product_no != "") {
            $this->db->like('a.item_id', $filter_product_no);
        }
        $this->db->order_by('d.name', 'asc');
        // die($this->db->get_compiled_select());
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
                            <small>MASTER OUTSTANDING MPP</small>
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
                <th style="text-align:center;">Customer</th>
                <th style="text-align:center;">Product No</th>
                <th style="text-align:center;">Product Name</th>
                <th style="text-align:center;">CCT</th>
                <th style="text-align:center;">OS MPP</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['circuit_no'] . '</td>
                            <td>' . $data['os_mpp'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LME_prices extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['cols'] = [];

            $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
                ->from('lme_ranges')->order_by('id');
            foreach ($this->db->get()->result_object() as $key => $value) {
                $data['cols']['lme'.($key+1)] = $value->ranges;
            }
            $data['colspan'] = count($data['cols']);

            $this->load->view('template/header', $data);
            $this->load->view('planning/lme_prices');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $lme_prices = $this->api->reads('lme_prices');
        echo json_encode($lme_prices);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(a.item_id) AS item")
        ->from("supplier_items a")->join("mst_item_raw b", "a.item_id = b.item_id")
        ->where("b.pfm_id", "01");
        if (!empty($post)) { $this->db->like('a.item_id', $post); }
        $this->db->order_by("item");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readMakers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(a.maker_code) AS code, TRIM(c.name) AS maker")
        ->from("supplier_items a")
            ->join("mst_item_raw b", "a.item_id = b.item_id")
            ->join("mst_maker c","a.maker_code = c.number")
        ->where("b.pfm_id", "01");
        if (!empty($post)) { $this->db->group_start()
            ->like("a.maker_code", $post)
            ->or_like("c.name", $post)
        ->group_end(); }
        $this->db->order_by("maker");
        $get = $this->db->get()->result_object();
        // die($this->db->last_query());

        echo json_encode($get);
    }

    public function readLMERanges()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
        ->from("lme_ranges");
        if (!empty($post)) { $this->db->like("CONCAT(lme_start,'-',lme_end)", $post); }
        $this->db->order_by("id");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readPartNoBEI()
    {
        $maker = isset($_POST['maker']) ? base64_decode($_POST['maker']) : "";
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(a.item_id) AS item")
        ->from("supplier_items a")->join("mst_item_raw b", "a.item_id = b.item_id")
            ->where("b.pfm_id", "01");
        if (!empty($maker)) { $this->db->where("a.maker_code", $maker); }
        if (!empty($post)) { $this->db->like("a.item_id", $post); }
        $this->db->order_by("item");
        $get = $this->db->get()->result_object();
        // die($this->db->last_query());

        echo json_encode($get);
    }

    public function readItemsSupplier()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(c.number) AS id, TRIM(c.name) AS name")
        ->from("supplier_items a")
            ->join("mst_item_raw b", "a.item_id = b.item_id")
            ->join("suppliers c", "a.supplier_id = c.id")
        ->where("b.pfm_id", "01");
        if (!empty($post)) { $this->db->group_start()
            ->like("c.number", $post)
            ->or_like("c.name", $post)
        ->group_end(); }
        $this->db->order_by("name");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function historys()
    {
        $id = base64_decode($_POST['id']);
        $result = ['span' => [], 'data' => []];
        $ranges = [];

        $this->db->select(
             "TRIM(a.id) AS id,"
            ."TRIM(a.data_lme) AS prices,"
            ."TRIM(a.maker_code) AS maker_code,"
            ."TRIM(a.ven_id) AS ven_id,"
            ."TRIM(a.item_id) AS part_no,"
            ."COALESCE(TRIM(d.name), '') AS supplier,"
            ."COALESCE(TRIM(f.name), '') AS maker_name,"
            ."a.created_date AS u_date,"
            ."TRIM(b.name) AS u_by"
        )->from('lme_price_logs a')
            ->join('user b', 'a.created_by = b.username', 'left')
            ->join('suppliers d', 'a.ven_id = d.number', 'left')
            ->join('supplier_items e', 'a.item_id = e.item_id', 'left')
            ->join('mst_maker f', 'a.maker_code = f.number', 'left')
            ->join('mst_item_raw g', 'a.item_id = g.item_id', 'left')
        ->where("price_id", $id)->order_by("id", 'DESC');
        $get = $this->db->get()->result_object();
        foreach ($get as $index => $column) {
            $range = json_decode($column->prices);
            foreach ($range as $key => $value) {
                $idx = intval(explode('-', $value->range)[0]);
                if (!isset($ranges[$idx])) { $ranges[$idx] = $value->range; }
            }
            array_push($result['data'], json_decode(json_encode($column), true));
        }
        sort($ranges);

        foreach ($ranges as $key => $value) {
            array_push($result['span'], [
                'field' => 'lme_'.$key,
                'title' => $value,
                'width' => '100',
                'halign' => 'center'
            ]);
        }

        foreach ($result['data'] as $index => $column) {
            $range = json_decode($column['prices'], true);
            foreach ($ranges as $key => $value) {
                $idx = array_search($value, array_column($range, 'range'));
                $result['data'][$index]['lme_'.$key] = (gettype($idx) == 'boolean') ? 0 : $range[$idx]['price'];
            }
        }

        echo json_encode($result);
    }

    public function datatables()
    {
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        $f_lme_range = base64_decode($this->input->get('f_lme_range'));
        $f_supplier = base64_decode($this->input->get('f_supplier'));
        $f_part_no = base64_decode($this->input->get('f_part_no'));
        $f_maker = base64_decode($this->input->get('f_maker'));

        $get = $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
        ->from("lme_ranges")->order_by("id")->get()->result_object();
        $ranges = [];
        $cols = "";
        foreach ($get as $key => $value) {
            $id = "lme".($key + 1);
            $cols .= "0 AS $id,";
            $ranges[$value->ranges] = $id;
        }

        //Select Query
        $this->db->select(
             "TRIM(a.id) AS id,"
            ."TRIM(a.data_lme) AS prices,"
            ."TRIM(a.maker_code) AS maker_code,"
            ."TRIM(a.ven_id) AS ven_id,"
            ."TRIM(a.item_id) AS part_no,"
            ."COALESCE(TRIM(d.name), '') AS supplier,"
            ."COALESCE(TRIM(f.name), '') AS maker_name,"
            .$cols
            ."COALESCE(a.updated_date, a.created_date) AS u_date,"
            ."COALESCE(TRIM(c.name), TRIM(b.name)) AS u_by"
        )->from('lme_prices a')
            ->join('user b', 'a.created_by = b.username', 'left')
            ->join('user c', 'a.updated_by = c.username', 'left')
            ->join('suppliers d', 'a.ven_id = d.number', 'left')
            ->join('supplier_items e', 'a.item_id = e.item_id', 'left')
            ->join('mst_maker f', 'a.maker_code = f.number', 'left')
            ->join('mst_item_raw g', 'a.item_id = g.item_id', 'left');
        if (!empty($f_lme_range)) { $this->db->like("a.data_lme", $f_lme_range); }
        if (!empty($f_supplier)) { $this->db->where("a.ven_id", $f_supplier); }
        if (!empty($f_part_no)) { $this->db->where("a.item_id", $f_part_no); }
        if (!empty($f_maker)) { $this->db->where("a.maker_code", $f_maker); }
        $this->db->order_by('id');
        $result = $this->db->get()->result_array();

        foreach ($result as $key => $value) {
            foreach (json_decode($value['prices']) as $key2 => $value2) {
                if (isset($ranges[$value2->range]))
                    { $result[$key][$ranges[$value2->range]] = floatval($value2->price); }
            }
        }

        echo json_encode(array('total' => count($result), 'rows' => array_splice($result, $offset, $rows)));
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $maker = $post['maker'];
            $part_no = $post['part_no'];
            $supp_id = $post['supp_id'];
            // die(var_dump($post));

            $exists = $this->db->where('maker_code', $maker)->where('ven_id', $supp_id)->where('item_id', $part_no)
            ->get('lme_prices')->row();
            if ($exists) { show_error("Data already available!"); }
            else {
                $send = $this->api->create('lme_prices', [
                    'maker_code' => $maker,
                    'ven_id' => $supp_id,
                    'item_id' => $part_no,
                    'currency' => 'USD',
                    'data_lme' => $post['datas']
                ], "LMEP", "lme_prices");
                $get = json_decode($send, true);
                if ($get['theme'] == 'success') {
                    $this->api->create('lme_price_logs', [
                        'price_id' => $get['id'],
                        'maker_code' => $maker,
                        'ven_id' => $supp_id,
                        'item_id' => $part_no,
                        'currency' => 'USD',
                        'data_lme' => $post['datas']
                    ], "LMEH", "lme_price_logs");
                }
                echo $send;
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
            $data = [];

            $get = $this->db->select(
                 "TRIM(maker_code) AS maker,"
                ."TRIM(item_id) AS part_no,"
                ."TRIM(ven_id) AS supp_id,"
                ."TRIM(data_lme) AS datas"
            )->from('lme_prices')->where('id', $id)->get()->row();
            // die($this->db->last_query());
            // die(var_dump($get));
            $maker = $get->maker;
            $part_no = $get->part_no;
            $supp_id = $get->supp_id;
            $datas = json_decode($get->datas);

            $merge = array_merge($datas, json_decode($post['datas']));
            // die(var_dump($merge));
            foreach ($merge as $key => $value)
                { $data[$value->range] = $value; }
            $data = json_encode(array_values($data));

            $send = $this->api->update('lme_prices', ["id" => $id], ['data_lme' => $data]);
            $get = json_decode($send, true);
            if ($get['theme'] == 'success') {
                $this->api->create('lme_price_logs', [
                    'price_id' => $get['id'],
                    'maker_code' => $maker,
                    'ven_id' => $supp_id,
                    'item_id' => $part_no,
                    'currency' => 'USD',
                    'data_lme' => $data
                ], "LMEH", "lme_price_logs");
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('lme_prices', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $datas = [];
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);

        $data->setOutputEncoding('UTF-8');
        $data = $data->sheets[0]['cells'];
        unset($data[1]);
        // die(var_dump($data));

        foreach ($data as $key => $value) {
            array_push($datas, [
                'item' => $value[1],
                'maker' => $value[2],
                'supp' => $value[3],
                'range' => $value[4],
                'price' => number_format($value[5], 4, '.', '')
            ]);
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $result = ['title' => '', 'message' => '', 'theme' => 'error'];
            $act = '';
            $data = $this->input->post('data');
            $range = $data['range'];
            $maker = $data['maker'];
            $supp = $data['supp'];
            $item = $data['item'];
            $price = $data['price'];
            $range_arr = explode('-', $range);

            $makers = $this->api->read('mst_maker', [], ['number' => $maker]);
            $suppliers = $this->api->read('suppliers', [], ['number' => $supp]);
            // $items = $this->api->read('mst_item_raw', [], ['item_id' => $item]);
            $ranges = $this->api->read('lme_ranges', [], [
                "lme_start" => $range_arr[0],
                "lme_end" => $range_arr[1]
            ]);

            // if (empty($items)) {
            //     $result['title'] = 'Undefine';
            //     $result['message'] = "Item ID $item no found!";
            // } else
            if (empty($makers)) {
                $result['title'] = 'Undefine';
                $result['message'] = "Maker Code $maker no found!";
            } elseif (empty($suppliers)) {
                $result['title'] = 'Undefine';
                $result['message'] = "Supplier Code $supp no found!";
            } elseif (empty($ranges)) {
                $result['title'] = 'Undefine';
                $result['message'] = "LME Range $range no found!";
            } else {
                $id = 'in';
                $exists = $this->db->where('maker_code', $maker)->where('ven_id', $supp)->where('item_id', $item)
                ->get('lme_prices')->row();
                if ($exists) { $id = $exists->id; }
            }

            if (!empty($result['title'])) { echo json_encode($result); }
            else {
                $datas = json_decode(json_encode([['range' => $range, 'price' => $price]]));
                $price_id = $id;

                if ($id == 'in') {
                    $lme = json_encode($datas);
                    $data = [
                        'maker_code' => $maker,
                        'ven_id' => $supp,
                        'item_id' => $item,
                        'currency' => 'USD',
                        'data_lme' => $lme
                    ];

                    $send = $this->api->create('lme_prices', $data, "LMEP", "lme_prices");
                    $price_id = $this->api->read("lme_prices", [], $data)->id;
                } else {
                    $this->db->select("TRIM(data_lme) AS datas")->from('lme_prices')->where('id', $id);
                    // die($this->db->last_query());
                    $get = json_decode($this->db->get()->row()->datas);

                    $lme = [];
                    $merge = array_merge($get, $datas);
                    foreach ($merge as $key => $value) { $lme[$value->range] = $value; }
                    $lme = json_encode(array_values($lme));

                    $send = $this->api->update('lme_prices', ["id" => $id], ['data_lme' => $lme]);
                }
                $get = json_decode($send, true);
                if ($get['theme'] == 'success') {
                    $this->api->create('lme_price_logs', [
                        'price_id' => $price_id,
                        'maker_code' => $maker,
                        'ven_id' => $supp,
                        'item_id' => $item,
                        'currency' => 'USD',
                        'data_lme' => $lme
                    ], "LMEH", "lme_price_logs");
                }

                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Y-m-d H:i:s");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=LME Range $format.xls");
        }

        $get = $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
        ->from("lme_ranges")->order_by("id")->get()->result_object();
        $ranges = [];
        $cols = "";
        $ths = "";
        foreach ($get as $key => $value) {
            $id = "lme".($key + 1);
            $cols .= "0 AS $id,";
            $ths .= '<th>'.$value->ranges.'</th>';
            $ranges[$value->ranges] = $id;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
        $fullname = $this->db->get()->row()->name;

        $this->db->select(
             "TRIM(a.id) AS id,"
            ."TRIM(a.data_lme) AS prices,"
            ."TRIM(a.maker_code) AS maker_code,"
            ."TRIM(a.ven_id) AS ven_id,"
            ."TRIM(a.item_id) AS part_no,"
            ."COALESCE(TRIM(d.name), '') AS supplier,"
            ."COALESCE(TRIM(f.name), '') AS maker_name,"
            .$cols
            ."COALESCE(a.updated_date, a.created_date) AS u_date,"
            ."COALESCE(TRIM(c.name), TRIM(b.name)) AS u_by"
        )->from('lme_prices a')
            ->join('user b', 'a.created_by = b.username', 'left')
            ->join('user c', 'a.updated_by = c.username', 'left')
            ->join('suppliers d', 'a.ven_id = d.number', 'left')
            ->join('supplier_items e', 'a.item_id = e.item_id', 'left')
            ->join('mst_maker f', 'a.maker_code = f.number', 'left')
            ->join('mst_item_raw g', 'a.item_id = g.item_id', 'left')
        ->order_by('id');

        $records = $this->db->get()->result_array();
        foreach ($records as $key => $value) {
            foreach (json_decode($value['prices']) as $key2 => $value2) {
                if (isset($ranges[$value2->range]))
                    { $records[$key][$ranges[$value2->range]] = floatval($value2->price); }
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#lme_prices {border-collapse: collapse;width: 100%;font-size: 12px;}#lme_prices td, #lme_prices th {border: 1px solid #ddd;padding: 2px;}#lme_prices tr:nth-child(even){background-color: #f2f2f2;}#lme_prices tr:hover {background-color: #ddd;}#lme_prices th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>Setup Price LME</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $fullname . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="lme_prices" border="1">
            <tr>
                <th width="20" rowspan="2">No</th>
                <th rowspan="2">Item ID</th>
                <th rowspan="2">Supplier</th>
                <th rowspan="2">WIRE Name Maker</th>
                <th rowspan="2">Part No</th>
                <th colspan="'.count($ranges).'">Unit Packing MOQ Price  & LME Range (USD/MTR)</th>
                <th rowspan="2">Last Date</th>
                <th rowspan="2">By</th>
            </tr>
            <tr>
                '.$ths.'
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                <td align="center">' . $no . '</td>
                <td>' . $data['part_no'] . '</td>
                <td>' . $data['supplier'] . '</td>
                <td>' . $data['maker_name'] . '</td>
                <td>' . $data['part_no'] . '</td>';
            foreach ($ranges as $key => $value) { $html .= '<td>' . $data[$value] . '</td>'; }
            $html .= '<td align="center">' . $data['u_date'] . '</td>
                <td>' . $data['u_by'] . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Os_purchase_order extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('planning/os_purchase_order');
        } else {
            redirect('error_access');
        }
    }
    public function readVendors()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->dummy = $this->load->database('dummy', TRUE);
        $query = $this->dummy->query("SELECT * FROM r_vendor WHERE ven_name like '%$post%' ORDER BY ven_name asc");
        $records = $query->result_object();
        echo json_encode($records);
    }
    public function readProdfam($ven_id = "")
    {
        $ven_id = base64_decode($ven_id);
        $this->dummy = $this->load->database('dummy', TRUE);
        $query = $this->dummy->query("SELECT DISTINCT c.pfm_id, UPPER(c.pfm_name) as pfm_name 
            FROM por a 
            JOIN mst_item b ON a.item_id = b.item_id 
            JOIN r_prodfam c ON b.pfm_id = c.pfm_id
            JOIN r_vendor d ON a.ven_id = d.ven_id 
            WHERE d.ven_id LIKE '%$ven_id%' ORDER BY pfm_name asc");
        $records = $query->result_object();
        echo json_encode($records);
    }
    public function readProducts($ven_id, $pfm_id)
    {
        $ven_id = base64_decode($ven_id);
        $this->dummy = $this->load->database('dummy', TRUE);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        //Select Query
        $this->dummy->select("b.item_id, b.item_name");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->where('a.ven_id', $ven_id);
        $this->dummy->where('b.pfm_id', $pfm_id);
        $this->dummy->like('b.item_id', $post);
        $this->dummy->group_by('b.item_id');
        $this->dummy->order_by('b.item_id', 'asc');
        $records = $this->dummy->get()->result_array();
        echo json_encode($records);
    }
    public function readPo()
    {
        $ven_id = base64_decode($this->input->get('ven_id'));
        $pfm_id = base64_decode($this->input->get('pfm_id'));
        $item_id = base64_decode($this->input->get('item_id'));
        // $po_status = base64_decode($this->input->get('po_status'));
        $this->dummy = $this->load->database('dummy', TRUE);
        //Select Query
        $this->dummy->select("a.por_pocid");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->where('a.ven_id', $ven_id);
        $this->dummy->where('b.item_id', $item_id);
        $this->dummy->where('b.pfm_id', $pfm_id);
        $this->dummy->group_by('a.por_pocid');
        $this->dummy->order_by('a.por_pocid', 'asc');
        //Get Data Array
        $records = $this->dummy->get()->result_array();
        echo json_encode($records);
    }
    public function datatableDetails($item_id, $ven_id, $po_status)
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        $item_id = base64_decode($item_id);
        $ven_id = base64_decode($ven_id);
        $po_status = base64_decode($po_status);
        //Select Query
        $this->dummy->select("coalesce(e.por_receiveqty, 0) as por_receiveqty, 
            a.poc_id, coalesce(a.poc_reqqty, 0) as poc_reqqty, (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) as outstanding, 
            (CASE WHEN (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status, a.poc_date, a.poc_reqdate");
        $this->dummy->from('(SELECT item_id, poc_id, poc_date, poc_reqdate, ven_id, SUM(poc_reqqty) as poc_reqqty from poc GROUP BY item_id, poc_id, poc_date, poc_reqdate, ven_id) a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
        $this->dummy->join('(SELECT item_id, por_pocid, SUM(por_receiveqty) as por_receiveqty FROM por GROUP BY item_id, por_pocid) e', 'a.poc_id = e.por_pocid and a.item_id = e.item_id', 'left');
        $this->dummy->where('a.ven_id', $ven_id);
        $this->dummy->where('b.item_id', $item_id);
        if ($po_status == "Open") {
            $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) >', 0);
        } elseif ($po_status == "Closed") {
            $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) =', 0);
        } elseif ($po_status == "Over") {
            $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) <', 0);
        }
        $this->dummy->order_by('b.item_id', 'asc');
        //Total Data
        $totalRows = $this->dummy->count_all_results('', false);
        //Get Data Array
        $records = $this->dummy->get()->result_array();
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatables()
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Filter Data
            $vendor_id = base64_decode($this->input->get('vendor_id'));
            $product_family = base64_decode($this->input->get('product_family'));
            $item_id = base64_decode($this->input->get('item_id'));
            $po_no = trim(base64_decode($this->input->get('po_no')));
            $po_status = trim(base64_decode($this->input->get('po_status')));
            $trans_type = trim(base64_decode($this->input->get('trans_type')));
            $filter_from = trim(base64_decode($this->input->get('filter_from')));
            $filter_to = trim(base64_decode($this->input->get('filter_to')));
            //Select Query
            $this->dummy->select("b.ven_id, d.ven_name, b.item_minoq as mpq, b.item_muloq as moq, b.item_id, b.item_name, c.pfm_name, b.um_id, coalesce(e.por_receiveqty, 0) as por_receiveqty, 
                a.poc_id, coalesce(a.poc_reqqty, 0) as poc_reqqty, (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) as outstanding, 
                (CASE WHEN (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status, a.poc_date, a.poc_reqdate");
            $this->dummy->from('(SELECT item_id, poc_id, poc_date, poc_reqdate, ven_id, SUM(poc_reqqty) as poc_reqqty from poc GROUP BY item_id, poc_id, poc_date, poc_reqdate, ven_id) a');
            $this->dummy->join('mst_item b', "a.item_id = b.item_id");
            $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
            $this->dummy->join('r_vendor d', 'a.ven_id = d.ven_id');
            $this->dummy->join('(SELECT item_id, por_pocid, SUM(por_receiveqty) as por_receiveqty FROM por GROUP BY item_id, por_pocid) e', 'a.poc_id = e.por_pocid and a.item_id = e.item_id', 'left');
            if ($vendor_id != "") {
                $this->dummy->where('a.ven_id', $vendor_id);
            }
            if ($product_family != "") {
                $this->dummy->where('b.pfm_id', $product_family);
            }
            if ($item_id != "") {
                $this->dummy->where('b.item_id', $item_id);
            }
            if ($po_no != "") {
                $this->dummy->where('a.poc_id', $po_no);
            }
            if ($po_status == "Open") {
                $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) >', 0);
            } elseif ($po_status == "Closed") {
                $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) =', 0);
            } elseif ($po_status == "Over") {
                $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) <', 0);
            }
            if ($filter_from != "" or $filter_to != "") {
                $this->dummy->where('a.poc_date >=', $filter_from);
                $this->dummy->where('a.poc_date <=', $filter_to);
            }
            $this->dummy->order_by('a.poc_date', 'asc');
            $this->dummy->order_by('b.item_id', 'asc');
            //Total Data
            $totalRows = $this->dummy->count_all_results('', false);
            //Limit 1 - 10
            $this->dummy->limit($rows, $offset);
            //Get Data Array
            $records = $this->dummy->get()->result_array();
            //Mapping Data
            $arr = [];
            foreach ($records as $record) {
                $mst_item_raw = $this->api->read('mst_item_raw', [], ["item_id" => trim($record['item_id'])]);
                $purchase_confirm = $this->api->query("SELECT item_id, po_no, coalesce(SUM(delivery),0) as qty FROM purchase_confirm WHERE item_id = '" . trim($record['item_id']) . "' and po_no = '" . trim($record['poc_id']) . "'");
                // if (!empty($purchase_confirm)) {
                $bal_otw = $purchase_confirm[0]->qty;
                // } else {
                //     $bal_otw = 0;
                // }
                $arr[] = array_merge($record, [
                    'item_supplier' => @$mst_item_raw->item_supplier,
                    'bal_otw' => $bal_otw,
                ]);
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $arr]);
            echo json_encode($result);
        }
    }

    public function datatableReceipt($poc_id, $item_id)
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        $item_id = base64_decode($item_id);
        $poc_id = base64_decode($poc_id);
        //Select Query
        $this->dummy->select("a.*, b.item_id, b.item_name, d.full_name");
        $this->dummy->from('por a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->join('tbn_users c', "a.por_emp = c.username", 'left');
        $this->dummy->join('tbn_man_power d', "c.nik = d.nik", 'left');
        $this->dummy->where('a.por_pocid', $poc_id);
        $this->dummy->where('a.item_id', $item_id);
        $this->dummy->order_by('a.item_id', 'asc');
        //Total Data
        $totalRows = $this->dummy->count_all_results('', false);
        //Get Data Array
        $records = $this->dummy->get()->result_array();
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }


    public function datatableOtw($poc_id, $item_id)
    {
        $item_id = base64_decode($item_id);
        $poc_id = base64_decode($poc_id);
        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_confirm a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.po_no', $poc_id);
        $this->db->where('a.item_id', $item_id);
        $this->db->order_by('a.supplier_id', 'ASC');
        $this->db->order_by('a.po_no', 'ASC');
        $this->db->order_by('a.item_id', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $arr = [];
        foreach ($records as $record) {
            $this->dummy = $this->load->database('dummy', TRUE);
            $this->dummy->select("sum(por_receiveqty) as por_receive");
            $this->dummy->from("por");
            $this->dummy->where('por_pocid', $record['po_no']);
            $this->dummy->where('item_id', $record['item_id']);
            $this->dummy->where('por_invno', $record['document_no']);
            $por = $this->dummy->get()->row();
            $balance = $record['qty'] - @$por->por_receive;
            $arr[] = array_merge($record, [
                'receive' => @$por->por_receive,
                'balance' => $balance
            ]);
        }
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $arr]);
        echo json_encode($result);
    }

    // public function datatables()
    // {
    //     $this->dummy = $this->load->database('dummy', TRUE);
    //     if ($this->input->get()) {
    //         $page = $this->input->post('page');
    //         $rows = $this->input->post('rows');
    //         //Pagination 1-10
    //         $page   = isset($page) ? intval($page) : 1;
    //         $rows   = isset($rows) ? intval($rows) : 10;
    //         $offset = ($page - 1) * $rows;
    //         $result = array();
    //         //Filter Data
    //         $vendor_id = base64_decode($this->input->get('vendor_id'));
    //         $product_family = base64_decode($this->input->get('product_family'));
    //         $item_id = base64_decode($this->input->get('item_id'));
    //         $po_no = trim(base64_decode($this->input->get('po_no')));
    //         $po_status = trim(base64_decode($this->input->get('po_status')));
    //         $trans_type = trim(base64_decode($this->input->get('trans_type')));
    //         //Select Query
    //         $this->dummy->select("b.ven_id, b.item_minoq as mpq, b.item_muloq as moq, b.item_id, b.item_name, c.pfm_name, b.um_id, 
    //             g.poc_reqqty,
    //             g.por_receiveqty,
    //             g.ost, 
    //             (CASE WHEN g.ost > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status");
    //         $this->dummy->from('mst_item b');
    //         $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
    //         $this->dummy->join('r_vendor d', 'b.ven_id = d.ven_id');
    //         $this->dummy->join("(SELECT z.item_id, SUM(z.request) as poc_reqqty, SUM(z.receive) as por_receiveqty, SUM(z.balance) as ost FROM (
    //                 SELECT b.item_id, b.poc_id, b.poc_reqdate, COALESCE(b.request, 0) as request, COALESCE(a.receive, 0) as receive, (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) as balance
    //                 FROM (select item_id, poc_id, poc_reqdate, sum(poc_reqqty) as request from poc GROUP BY item_id, poc_id, poc_reqdate) b 
    //                 LEFT JOIN (select item_id, por_pocid, SUM(por_receiveqty) as receive from por GROUP BY por_pocid, item_id) a ON a.item_id = b.item_id and a.por_pocid = b.poc_id
    //                 WHERE (COALESCE(b.request, 0) - COALESCE(a.receive, 0)) > 0
    //                 ORDER BY b.poc_reqdate ASC) z GROUP BY z.item_id) g", "b.item_id = g.item_id", 'left');
    //         // $this->dummy->join("(SELECT item_id, ven_id, poc_id, SUM(poc_reqqty) as poc_reqqty from poc where poc_id LIKE '%$po_no%' GROUP BY item_id, ven_id, poc_id) g", "b.item_id = g.item_id and d.ven_id = g.ven_id");
    //         // $this->dummy->join("(SELECT item_id, por_pocid, SUM(por_receiveqty) as por_receiveqty FROM por WHERE por_pocid LIKE '%$po_no%' GROUP BY item_id, por_pocid) f", 'b.item_id = f.item_id and g.poc_id = f.por_pocid', 'left');
    //         $this->dummy->like('b.ven_id', $vendor_id);
    //         $this->dummy->like('b.pfm_id', $product_family);
    //         $this->dummy->like('b.item_id', $item_id);
    //         // if($po_status == "Open"){
    //         //     $this->dummy->where('g.ost >', 0);
    //         // }elseif($po_status == "Closed"){
    //         //     $this->dummy->where('g.ost =', 0);
    //         // }elseif($po_status == "Over"){
    //         //     $this->dummy->where('g.ost <', 0);
    //         // }
    //         // $this->dummy->group_by('b.item_id');
    //         // $this->dummy->group_by('b.item_name');
    //         // $this->dummy->group_by('c.pfm_name');
    //         // $this->dummy->group_by('b.um_id');
    //         // $this->dummy->group_by('b.item_minoq');
    //         // $this->dummy->group_by('b.item_muloq');
    //         $this->dummy->order_by('b.item_id', 'asc');
    //         //Total Data
    //         $totalRows = $this->dummy->count_all_results('', false);
    //         //Limit 1 - 10
    //         $this->dummy->limit($rows, $offset);
    //         //Get Data Array
    //         $records = $this->dummy->get()->result_array();
    //         //Mapping Data
    //         $result['total'] = $totalRows;
    //         $result = array_merge($result, ['rows' => $records]);
    //         echo json_encode($result);
    //     }
    // }
    public function csv()
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        header("Content-type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=os_purchase_orders_" . time() . ".csv");
        $output = fopen("php://output", "w");
        fputcsv($output, array(
            "Item No",
            "Item Name",
            "Product Family",
            "Supplier",
            "Purchase Order",
            "PO Date",
            "Req Date",
            "MOQ",
            "MPQ",
            "Request",
            "Receipt",
            "Outstanding",
            "Status"
        ));
        //Select Query
        $this->dummy->select("b.ven_id, d.ven_name, b.item_minoq as mpq, b.item_muloq as moq, b.item_id, b.item_name, c.pfm_name, b.um_id, coalesce(e.por_receiveqty, 0) as por_receiveqty, 
            a.poc_id, coalesce(a.poc_reqqty, 0) as poc_reqqty, (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) as outstanding, 
            (CASE WHEN (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status, a.poc_date, a.poc_reqdate");
        $this->dummy->from('(SELECT item_id, poc_id, poc_date, poc_reqdate, ven_id, SUM(poc_reqqty) as poc_reqqty from poc GROUP BY item_id, poc_id, poc_date, poc_reqdate, ven_id) a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
        $this->dummy->join('r_vendor d', 'a.ven_id = d.ven_id');
        $this->dummy->join('(SELECT item_id, por_pocid, SUM(por_receiveqty) as por_receiveqty FROM por GROUP BY item_id, por_pocid) e', 'a.poc_id = e.por_pocid and a.item_id = e.item_id', 'left');
        $this->dummy->order_by('a.poc_date', 'asc');
        $this->dummy->order_by('b.item_id', 'asc');
        $records = $this->dummy->get()->result_array();
        $no = 1;
        foreach ($records as $data) {
            $data = array(
                "'" . $data['item_id'],
                $data['item_name'],
                $data['pfm_name'],
                $data['ven_name'],
                $data['poc_id'],
                $data['poc_date'],
                $data['poc_reqdate'],
                $data['mpq'],
                $data['moq'],
                $data['poc_reqqty'],
                $data['por_receiveqty'],
                $data['outstanding'],
                $data['status'],
            );
            fputcsv($output, $data);
            $no++;
        }
    }
    public function print($option = "")
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=os_purchase_order_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $vendor_id = base64_decode($this->input->get('vendor_id'));
        $product_family = base64_decode($this->input->get('product_family'));
        $item_id = base64_decode($this->input->get('item_id'));
        $po_no = trim(base64_decode($this->input->get('po_no')));
        $po_status = trim(base64_decode($this->input->get('po_status')));
        $trans_type = trim(base64_decode($this->input->get('trans_type')));
        $filter_from = trim(base64_decode($this->input->get('filter_from')));
        $filter_to = trim(base64_decode($this->input->get('filter_to')));

        //Select Query
        $this->dummy->select("b.ven_id, d.ven_name, b.item_minoq as mpq, b.item_muloq as moq, b.item_id, b.item_name, c.pfm_name, b.um_id, coalesce(e.por_receiveqty, 0) as por_receiveqty, 
            a.poc_id, coalesce(a.poc_reqqty, 0) as poc_reqqty, (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) as outstanding, 
            (CASE WHEN (coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) > 0 THEN 'OPEN' ELSE 'CLOSED' END) as status, a.poc_date, a.poc_reqdate");
        $this->dummy->from('(SELECT item_id, poc_id, poc_date, poc_reqdate, ven_id, SUM(poc_reqqty) as poc_reqqty from poc GROUP BY item_id, poc_id, poc_date, poc_reqdate, ven_id) a');
        $this->dummy->join('mst_item b', "a.item_id = b.item_id");
        $this->dummy->join('r_prodfam c', 'b.pfm_id = c.pfm_id');
        $this->dummy->join('r_vendor d', 'a.ven_id = d.ven_id');
        $this->dummy->join('(SELECT item_id, por_pocid, SUM(por_receiveqty) as por_receiveqty FROM por GROUP BY item_id, por_pocid) e', 'a.poc_id = e.por_pocid and a.item_id = e.item_id', 'left');
        if ($vendor_id != "") {
            $this->dummy->where('a.ven_id', $vendor_id);
        }
        if ($product_family != "") {
            $this->dummy->where('b.pfm_id', $product_family);
        }
        if ($item_id != "") {
            $this->dummy->where('b.item_id', $item_id);
        }
        if ($po_no != "") {
            $this->dummy->where('a.poc_id', $po_no);
        }
        if ($po_status == "Open") {
            $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) >', 0);
        } elseif ($po_status == "Closed") {
            $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) =', 0);
        } elseif ($po_status == "Over") {
            $this->dummy->where('(coalesce(a.poc_reqqty, 0) - coalesce(e.por_receiveqty, 0)) <', 0);
        }
        if ($filter_from != "" or $filter_to != "") {
            $this->dummy->where('a.poc_date >=', $filter_from);
            $this->dummy->where('a.poc_date <=', $filter_to);
        }
        $this->dummy->order_by('a.poc_date', 'asc');
        $this->dummy->order_by('b.item_id', 'asc');
        //Get Data Array
        $records = $this->dummy->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <style> .str{ mso-number-format:\@; } </style>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>OUTSTANDING PURCHASE ORDER</small>
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
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Supplier Name</th>
                <th>Item Id</th>
                <th>Item Supplier</th>
                <th>Item Name</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>PO No</th>
                <th>PO Date</th>
                <th>Request Date</th>
                <th>PO Qty</th>
                <th>Receive Qty</th>
                <th>On The Way</th>
                <th>Outstanding</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $mst_item_raw = $this->api->read('mst_item_raw', [], ["item_id" => trim($data['item_id'])]);
            $purchase_confirm = $this->api->query("SELECT item_id, po_no, SUM(delivery) as qty FROM purchase_confirm WHERE item_id = '" . trim($data['item_id']) . "' and po_no = '" . trim($data['poc_id']) . "'");
            // if (!empty($purchase_confirm)) {
            //     $bal_otw = 0;
            // } else {
            $bal_otw = $purchase_confirm[0]->qty;
            // }
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . @$data['ven_name'] . '</td>
                            <td style="mso-number-format:\@;">' . @$data['item_id'] . '</td>
                            <td style="mso-number-format:\@;">' . @$mst_item_raw->item_supplier . '</td>
                            <td>' . @$data['item_name'] . '</td>
                            <td>' . @$data['pfm_name'] . '</td>
                            <td>' . @$data['um_id'] . '</td>
                            <td>' . @$data['poc_id'] . '</td>
                            <td>' . @$data['poc_date'] . '</td>
                            <td>' . @$data['poc_reqdate'] . '</td>
                            <td>' . @$data['poc_reqqty'] . '</td>
                            <td>' . @$data['por_receiveqty'] . '</td>
                            <td>' . $bal_otw . '</td>
                            <td>' . @$data['outstanding'] . '</td>
                            <td>' . @$data['status'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

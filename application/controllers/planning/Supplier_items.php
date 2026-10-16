<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supplier_items extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/supplier_items');
        } else {
            redirect('error_access');
        }
    }

    public function indexButton($api_key)
    {
        echo base64_encode($this->getbutton('MNU-202403000002', $api_key));
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->reads('supplier_items', ["item_id" => $post]);
        echo json_encode($send);
    }

    public function readVendor()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT DISTINCT item_supplier FROM supplier_items WHERE item_supplier != '' ORDER BY item_supplier ASC");
        echo json_encode($send);
    }

    public function readShareOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT DISTINCT share_order FROM supplier_items ORDER BY share_order ASC");
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT a.item_id, a.item_name FROM mst_item_raw a WHERE a.item_id LIKE '%$post%' GROUP BY a.item_id");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_plant  = base64_decode($this->input->get('filter_plant'));
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_check = $this->input->get('filter_check');
        $filter_quotation = base64_decode($this->input->get('filter_quotation'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        $no_limit = !empty($this->input->get("no_limit"));
        $user_vendor = base64_decode($this->input->get('user'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select("a.*, g.start_date, g.valid_date, g.price, g.currency,c.item_id, c.item_name, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, m.uom_to as uom_convertion, f.name as maker_name, f.type as maker_type, f.origin as maker_origin, CASE WHEN a.created_by = '$user_vendor' THEN COALESCE(TRIM(h.name), '') ELSE '' END AS create_by, CASE WHEN a.created_by = '$user_vendor' THEN DATE(a.created_date) ELSE '' END AS create_date, c.um_id, i.name AS incoterm, 
        case when g.currency = 'IDR' then 1 else k.middle end as exchange_rate , 
        j.country_name, j.total_ddp,
        coalesce((g.price * (case when g.currency = 'IDR' then 1 else k.middle end))+ (g.price * (case when g.currency = 'IDR' then 1 else k.middle end) * COALESCE(j.total_ddp, 0)), 0) AS price_procurement
        ");
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number', 'left');
        $this->db->join("(SELECT supplier_item_id, price, currency, start_date, valid_date, exchange_rate FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id) GROUP BY supplier_item_id) g", 'a.id = g.supplier_item_id', 'left');
        $this->db->join("user h", "a.created_by = h.username", "LEFT");
        // $this->db->join("exchange_rates k", 'k.start_date <= g.valid_date AND k.end_date >= g.valid_date and k.currency = g.currency', 'left');
        $this->db->join("exchange_rates k", 'k.start_date = g.start_date and k.currency = g.currency', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_id FROM supplier_items GROUP BY item_id HAVING COUNT(*) > 1) i", 'a.item_id = i.item_id');
        }
        $this->db->join("incoterms i", "a.incoterm_code = i.number", "LEFT");
        $this->db->join("(
                        select ja.*, jb.name as country_name from setup_incoterms ja 
                        left join country_of_origins jb on ja.country_number = jb.number
                        ) j", "a.incoterm_code = j.incoterm_number and b.origin=j.country_name", "LEFT");
        $this->db->join('convertions m', 'a.item_id = m.item_id', 'left');

        // $this->db->join("(".
        //     "SELECT DISTINCT MAX(id) AS max, supplier_id, item_id FROM supplier_items ".
        //     "WHERE COALESCE(approved_to, '') = '' GROUP BY supplier_id, item_id".
        // ") j", "a.id = j.max AND a.supplier_id = j.supplier_id");
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_category != "") {
            $this->db->like('c.pfm_id', $filter_category);
        }
        $this->db->like('a.supplier_id', $filter_suppliers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('f.number', $filter_maker);
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        }
        if ($filter_quotation != "") {
            $this->db->where("a.quotation_number", $filter_quotation);
        }
        // die(var_dump(!empty($this->input->get('vendor'))));
        if (!empty($this->input->get('vendor'))) {
            $q = str_replace("`a`.*", "a.id, 'vendor' AS act, aa.status, aa.reason, a.plant, a.quotation_number, a.item_supplier, a.coo_name, a.spq, a.mpq, a.moq, a.lme_range, a.leadtime, a.share_order, a.safety_stock, a.calculate, a.provide, a.remarks, aa.approved, aa.approved_to, aa.approved_by, aa.approved_date, a.created_by, a.created_date, a.updated_by, a.updated_date, '' AS data_prices", $this->db->get_compiled_select());
            $q1 = str_replace("aa.approved, aa.approved_to, aa.approved_by, aa.approved_date", "NULL AS approved, NULL AS approved_to, NULL AS approved_by, NULL AS approved_date", $q);
            $q1 = str_replace("'vendor'", "'supplier'", $q1);
            $q1 = str_replace("aa.status", "a.status", $q1);
            $q1 = str_replace("aa.reason", "NULL AS reason", $q1);
            $q1 = str_replace("a.lme_range", "'' AS lme_range", $q1);
            $q2 = str_replace("supplier_items", "temp_vendor_contract", $q);
            $q2 = str_replace("`g`.`price`", "`a`.`price`", $q2);
            $q2 = str_replace("`g`.`currency`", "`a`.`currency`", $q2);
            $q2 = str_replace("`g`.`valid_date`", "`a`.`valid_date`", $q2);
            $q2 = str_replace("`g`.`start_date`", "`a`.`start_date`", $q2);
            $q2 = str_replace("WHERE `a`", " JOIN (" .
                "SELECT MAX(id) AS id, status, reason, approved, approved_to, approved_by, approved_date FROM temp_vendor_contract GROUP BY item_id, supplier_id"
                . ") aa ON a.id = aa.id WHERE a", $q2);
            $q = "SELECT * FROM ($q2 UNION $q1) AS unionTable ";
            if ($filter_status != "") {
                $where_status = [];
                if ($filter_status == 4) {
                    $where_status[] = "((status = 1 AND act = 'vendor') OR (status = 0 AND act = 'supplier'))";
                } elseif ($filter_status == 3) {
                    $where_status[] = "status = 2";
                } elseif ($filter_status == 2) {
                    $where_status[] = "approved != 1";
                } elseif ($filter_status == 1) {
                    $where_status[] = "act = 'vendor'";
                    $where_status[] = "approved = 1";
                    $where_status[] = "status = 0";
                }
                $q .= "WHERE " . implode(" AND ", $where_status) . " ";
            }
            $q .= "GROUP BY item_id ORDER BY ";
            if ($filter_check == "false") {
                $q .= "supplier_name, ";
            }
            $q .= "item_id";
            // die($q);
            $totalRows = $this->db->query($q)->num_rows();
            $q .= " LIMIT $rows OFFSET $offset";
            // die($q);
            $records = $this->db->query($q)->result_array();
        } else {
            $this->db->order_by("price_procurement");
            if ($filter_check == "false") {
                $this->db->order_by('supplier_name');
            }
            $this->db->order_by("a.item_id");
            $totalRows = $this->db->count_all_results('', false);
            if (!$no_limit) {
                $this->db->limit($rows, $offset);
            }
            $records = $this->db->get()->result_array();

            $sortedRecords = $records;
            usort($sortedRecords, function ($a, $b) {
                $cmp = strcmp($a['item_id'], $b['item_id']);
                if ($cmp !== 0) {
                    return $cmp;
                }

                $plantCmp = strcmp((string) $a['plant'], (string) $b['plant']);
                if ($plantCmp !== 0) {
                    return $plantCmp;
                }

                return $a['price_procurement'] <=> $b['price_procurement'];
            });

            $cheaperMap = [];
            $lastGroupKey = null;
            $cheaperRank = 0;
            foreach ($sortedRecords as $row) {
                $groupKey = $row['item_id'] . '|' . $row['plant'];
                if ($lastGroupKey !== $groupKey) {
                    $lastGroupKey = $groupKey;
                    $cheaperRank = 1;
                } else {
                    $cheaperRank++;
                }
                $lookupKey = $row['supplier_id'] . '|' . $row['item_id'] . '|' . $row['plant'];
                $cheaperMap[$lookupKey] = 'CHEAPER ' . $cheaperRank;
            }

            foreach ($records as $key => $value) {
                $lookupKey = $value['supplier_id'] . '|' . $value['item_id'] . '|' . $value['plant'];
                $records[$key]['cheaper'] = isset($cheaperMap[$lookupKey]) ? $cheaperMap[$lookupKey] : '';
            }
        }

        // die($this->db->last_query());
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);
    }

    public function datatableHistory()
    {
        $id = base64_decode($this->input->get('id'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $this->db->select('a.*, c.item_id, c.item_name, c.item_supplier, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type,d.name as uom, g.uom_to as uom_convertion, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        $this->db->from('supplier_items_log a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number', 'left');
        $this->db->join('convertions g', 'a.item_id = g.item_id', 'left');
        $this->db->where('a.supplier_item_id', $id);
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.item_id', 'ASC');
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

    public function datatablePrice()
    {
        $id = base64_decode($this->input->get('id'));

        $this->db->select('a.*, b.quotation_number, b.quotation_date');
        $this->db->from('supplier_item_prices a');
        $this->db->join('supplier_items b', 'a.supplier_item_id = b.id');
        $this->db->where('supplier_item_id', $id);
        $this->db->order_by('valid_date', 'DESC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);
    }

    public function datatablePriceLogs()
    {
        $id = base64_decode($this->input->get('supplier_item_id'));

        $this->db->select('a.*');
        $this->db->from('supplier_item_prices_log a');
        $this->db->where('supplier_item_id', $id);
        $this->db->order_by('created_date', 'DESC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        echo json_encode($result);
    }

    public function datatableQuotation()
    {
        $act = $this->input->post('act');
        $result = [];

        if ($act == 'parent') {
            $item = base64_decode($this->input->post('item'));
            $supplier = $this->input->post('supplier_id');

            $this->db->distinct()->select(
                "TRIM(supplier_item_id) AS id, " .
                    "TRIM(quotation_number) AS quotation, " .
                    "TRIM(created_by) AS c_by, " .
                    "MAX(DATE_FORMAT(created_date, '%Y-%m-%d')) AS c_date, " .
                    "TRIM(currency) AS currency, " .
                    "start_date, valid_date"
            )->from("supplier_items_log")
                ->where("supplier_id", $supplier)
                ->where("item_id", $item)
                ->group_by("quotation_number")->order_by("quotation", "DESC");
        } elseif ($act == 'expand') {
            $quotation = base64_decode($this->input->post('quotation'));
            $id = base64_decode($this->input->post('id'));

            if (empty($quotation)) {
                $quotation = null;
            }

            $this->db->distinct()->select(
                "TRIM(quotation_number) AS quotation, " .
                    "spq, mpq, moq, leadtime, price, created_date"
            )->from("supplier_items_log")
                ->where("quotation_number", $quotation)
                ->where("supplier_item_id", $id)
                ->order_by("created_date", "DESC");
        } elseif ($act == 'vendor') {
            $item = base64_decode($this->input->post('item'));
            $supplier = $this->input->post('supplier_id');

            $this->db->distinct()->select(
                "TRIM(quotation_number) AS quotation, " .
                    "spq, mpq, moq, leadtime, price, created_date, " .
                    "TRIM(created_by) AS c_by, " .
                    "MAX(DATE_FORMAT(created_date, '%Y-%m-%d')) AS c_date, " .
                    "TRIM(currency) AS currency, " .
                    "start_date, valid_date, '' AS reason"
            )->from("supplier_items_log")->where(["supplier_id" => $supplier, "item_id" => $item]);
            $q1 = $this->db->get_compiled_select();
            $q2 = str_replace("supplier_items_log", "temp_vendor_contract", $q1);
            $q2 = str_replace("'' AS reason", "reason", $q2);
            $result = $this->db->query("SELECT * FROM ($q2 UNION $q2) AS x ORDER BY created_date DESC")->result();
        }

        if (!empty($act) && $act != 'vendor') {
            // die($this->db->get_compiled_select());
            $result = $this->db->get()->result();
        }
        echo json_encode($result);
    }

    public function findQuotation()
    {
        $quotation = base64_decode($this->input->post('quotation'));
        $item = base64_decode($this->input->post('item'));
        $supplier = $this->input->post('supplier');
        // die(var_dump($this->input->post()));
        $this->db->where("supplier_id", $supplier)->where("quotation_number", $quotation)->where("item_id", $item);
        $count = $this->db->from("supplier_items")->count_all_results();
        // die($this->db->get_compiled_select());
        echo json_encode(["count" => $count]);
    }

    public function getQuotation()
    {
        $supplier = $this->input->post('supplier');
        $item = base64_decode($this->input->post('item'));

        $this->db->distinct()->select("TRIM(quotation_number) AS quotation")->from("supplier_items")
            ->where("supplier_id", $supplier)->where("item_id", $item)->order_by("quotation");
        // die($this->db->get_compiled_select());
        echo json_encode($this->db->get()->result());
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                if (isset($post['is_api'])) {
                    $this->session->set_userdata('username', $post['is_api']);
                    unset($post['is_api']);
                }

                $supplier_items = $this->api->read('supplier_items', [], ["supplier_id" => $post['supplier_id'], "item_id" => $post['item_id'], "plant" => $post['plant'], "quotation_number" => $post["quotation_number"]]);
                if (!$supplier_items) {
                    $attachment = $this->api->upload('attachment', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items/', [], "supplier_items", "attachment");
                    $attachment2 = $this->api->upload('attachment2', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items2/', [], "supplier_items", "attachment2");
                    $attachment3 = $this->api->upload('attachment3', ["jpg", "png", "jpeg", "pdf", "xls", "xlsx", "docx"], 'assets/documents/supplier_items3/', [], "supplier_items", "attachment3");
                    $postFinal = array_merge($post, ["attachment" => $attachment, "attachment2" => $attachment2, "attachment3" => $attachment3]);

                    $send = $this->api->create('supplier_items', $postFinal, "SPI", "supplier_items");
                    echo $send;
                } else {
                    show_error("Duplicate Data or this item still checking in approval");
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createPrice()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            if (isset($post['is_api'])) {
                $this->session->set_userdata('username', $post['is_api']);
                unset($post['is_api']);
            }

            // if (isset($post['quotation_number'])) { $post['quotation_date'] = date('Y-m-d H:i:s'); }

            $start_date = $post['start_date'];
            $valid_date = $post['valid_date'];
            $supplier_item_id = $post['supplier_item_id'];
            $check = $this->api->query("SELECT * FROM supplier_item_prices WHERE supplier_item_id = '$supplier_item_id' and valid_date > '$start_date'");

            if ($start_date > $valid_date) {
                show_error("Start date and valid date not valid");
            } else {
                if (count($check) > 0) {
                    show_error("Start date must be bigger previous valid date");
                } else {
                    $send = $this->api->create('supplier_item_prices', $post, "SPI", "supplier_items");
                    echo $send;
                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();

            if (isset($post['is_api'])) {
                $this->session->set_userdata('username', $post['is_api']);
                unset($post['is_api']);
            }

            // if (isset($post['quotation_number'])) { $post['quotation_date'] = date('Y-m-d H:i:s'); }
            // die(json_encode($post));

            $read = $this->api->read("supplier_items", ["id" => $id]);

            $send = $this->api->update('supplier_items', ["id" => $id], $post);
            // die(var_dump($send));
            if ($send) {
                $history = array(
                    "supplier_item_id" => $id,
                    "plant" => $read->plant,
                    "supplier_id" => $read->supplier_id,
                    "item_id" => $read->item_id,
                    "item_supplier" => $read->item_supplier,
                    "maker_code" => $read->maker_code,
                    "maker_type" => $read->maker_type,
                    "spq" => $read->spq,
                    "mpq" => $read->mpq,
                    "moq" => $read->moq,
                    "price" => $read->price,
                    "currency" => $read->currency,
                    "landed_cost" => $read->landed_cost,
                    "import_duty" => $read->import_duty,
                    "share_order" => $read->share_order,
                    "leadtime" => $read->leadtime,
                    "remarks" => $read->remarks,
                    // "quotation_number" => $post['quotation_number'],
                    // "quotation_date" => $post['quotation_date'],
                );

                $send = $this->api->create("supplier_items_log", $history, "SPL", "supplier_items_log");
                // die(var_dump($send));
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updatePrice()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            if (isset($post['is_api'])) {
                $this->session->set_userdata('username', $post['is_api']);
                unset($post['is_api']);
            }
            $start_date = $post['start_date'];
            $valid_date = $post['valid_date'];

            $price = $this->api->read('supplier_item_prices', [], ["id" => $id]);

            if ($start_date > $valid_date) {
                show_error("Start date and valid date not valid");
            } else {
                $send = $this->api->update('supplier_item_prices', ["id" => $id], $post);
                $history = array(
                    "supplier_item_id" => $price->supplier_item_id,
                    "price" => @$price->price,
                    "currency" => @$price->currency,
                    "start_date" => @$price->start_date,
                    "valid_date" => @$price->valid_date,
                    "remarks" => @$price->remarks,
                );

                // die(json_encode($history));
                $this->api->create("supplier_item_prices_log", $history, "JUTA", "supplier_items_log");
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        if (isset($data['is_api'])) {
            $this->session->set_userdata('username', $data['is_api']);
            unset($data['is_api']);
        }
        $this->api->delete('supplier_items_log', ["supplier_item_id" => $data['id']]);
        $this->api->delete('supplier_item_prices', ["supplier_item_id" => $data['id']]);
        $this->api->delete('supplier_item_prices_log', ["supplier_item_id" => $data['id']]);

        $send = $this->api->delete('supplier_items', $data);
        echo $send;
    }

    public function deletePrice()
    {
        $data = $this->input->post();
        if (isset($data['is_api'])) {
            $this->session->set_userdata('username', $data['is_api']);
            unset($data['is_api']);
        }
        $send = $this->api->delete('supplier_item_prices', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        // error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'plant' => $data->val($i, 2),
                'item_id' => $data->val($i, 3),
                'item_supplier' => $data->val($i, 4),
                'supplier_number' => $data->val($i, 5),
                'coo_name' => $data->val($i, 6),
                'spq' => $data->val($i, 7),
                'mpq' => $data->val($i, 8),
                'moq' => $data->val($i, 9),
                'currency' => $data->val($i, 10),
                'price' => $data->val($i, 11),
                'maker_code' => $data->val($i, 12),
                'maker_type' => $data->val($i, 13),
                'share_order' => $data->val($i, 14),
                'start_date' => $data->val($i, 15),
                'valid_date' => $data->val($i, 16),
                'leadtime' => $data->val($i, 17),
                'landed_cost' => $data->val($i, 18),
                'import_duty' => $data->val($i, 19),
                'remarks' => $data->val($i, 20),
                'quotation_number' => $data->val($i, 21),
                'quotation_date' => $data->val($i, 22),
                'incoterm_code' => $data->val($i, 23),
                'action' => $data->val($i, 24)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/vendor_contracts.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/vendor_contracts.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/vendor_contracts.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    function isValidDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            if (isset($data['is_api'])) {
                $this->session->set_userdata('username', $data['is_api']);
                unset($data['is_api']);
            }
            // if (isset($data['quotation_number'])) { $data['quotation_date'] = date('Y-m-d H:i:s'); }
            //Cek Process Number
            $item = $this->api->read('mst_item_raw', [], ["item_id" => $data['item_id']]);
            $plant = $this->api->read('plants', [], ["number" => $data['plant']]);
            $supplier = $this->api->read('suppliers', [], ["number" => $data['supplier_number']]);
            $maker = $this->api->read('mst_maker', [], ["number" => $data['maker_code']]);
            $incoterm = $this->api->read('incoterms', [], ["number" => $data['incoterm_code']]);
            // $supplier_items = $this->api->read('supplier_items', [], ["status" => 0, "item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant'], "quotation_number" => $data['quotation_number'] ?? '']);
            $supplier_items = $this->api->read('supplier_items', [], ["status" => 0, "item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']]);
            $supplier_item_prices = $this->api->read('supplier_item_prices', [], ["supplier_item_id" => @$supplier_items->id]);

            if ($data['action'] == "") {
                die(json_encode(array("title" => "Action", "message" => "Action Still Empty please fill NEW or UPDATE", "theme" => "error")));
            }

            if (empty($supplier->id)) {
                die(json_encode(array("title" => "Not Found", "message" => "Vendor " . $data['supplier_number'] . " Not Found", "theme" => "error")));
            }

            if (empty($data['quotation_number'])) {
                die(json_encode(array("title" => "Not Found", "message" => "Quotation Number " . $data['quotation_number'] . " Not Found", "theme" => "error")));
            }

            if (empty($item->item_id)) {
                die(json_encode(array("title" => "Not Found", "message" => "Part No " . $data['item_id'] . " Not Found", "theme" => "error")));
            }

            if (empty($plant->number)) {
                die(json_encode(array("title" => "Not Found", "message" => "Plant " . $data['plant'] . " Not Found", "theme" => "error")));
            }

            if (strtolower($data['action']) == "new") {
                if (empty($maker->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Maker " . $data['maker_code'] . " Not Found", "theme" => "error"));
                } elseif ($data['price'] <= 0) {
                    echo json_encode(array("title" => "Failed", "message" => "Price Cannot Input 0", "theme" => "error"));
                } elseif (empty($data['currency'])) {
                    echo json_encode(array("title" => "Not Found", "message" => "Currency Still Empty", "theme" => "error"));
                } elseif (!empty($supplier_items->item_id)) {
                    echo json_encode(array("title" => "Duplicate", "message" => "Supplier " . $data['supplier_number'] . " and Part No " . $data['item_id'] . " Has been Created", "theme" => "error"));
                } elseif (empty($incoterm->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Incoterm " . $data['incoterm_code'] . " Not Found", "theme" => "error"));
                } else {
                    if ($this->isValidDate($data['start_date'])) {
                        //
                    } else {
                        die(json_encode(array("title" => "Not Found", "message" => "Format Date " . $data['start_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                    }

                    if ($this->isValidDate($data['valid_date'])) {
                        $supplier_item_prices = $this->api->read('supplier_item_prices', [], ["supplier_item_id" => @$supplier_items->id, "valid_date" => $data['valid_date']]);
                        if (!empty($supplier_item_prices->valid_date)) {
                            die(json_encode(array("title" => "Failed", "message" => "Valid date " . $data['valid_date'] . " with part number " . $data['item_id'] . " available", "theme" => "error")));
                        }
                    } else {
                        die(json_encode(array("title" => "Not Found", "message" => "Format Date " . $data['valid_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                    }
                    if ($this->isValidDate($data['quotation_date'])) {
                        //
                    } else {
                        die(json_encode(array("title" => "Not Found", "message" => "Format Date " . $data['quotation_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                    }
                    $postFinal = array(
                        "item_id" => $item->item_id,
                        "item_supplier" => $data['item_supplier'],
                        "supplier_id" => $supplier->id,
                        "coo_name" => $data['coo_name'],
                        "plant" => $data['plant'],
                        "maker_code" => $data['maker_code'],
                        "maker_type" => $data['maker_code'],
                        "spq" => $data['spq'],
                        "mpq" => $data['mpq'],
                        "moq" => $data['moq'],
                        "price" => $data['price'],
                        "currency" => $data['currency'],
                        "landed_cost" => $data['landed_cost'],
                        "import_duty" => $data['import_duty'],
                        "share_order" => $data['share_order'],
                        "start_date" => $data['start_date'],
                        "valid_date" => $data['valid_date'],
                        "leadtime" => $data['leadtime'],
                        "remarks" => $data['remarks'],
                        "quotation_number" => $data['quotation_number'],
                        "quotation_date" => $data['quotation_date'],
                        "incoterm_code" => $data['incoterm_code'],
                    );
                    $send = $this->api->create('supplier_items', $postFinal, "SPI", "supplier_items");
                    // $supplier_item = $this->api->read('supplier_items', [], ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant'], 'quotation_number' => $data['quotation_number']]);
                    $supplier_item = $this->api->read('supplier_items', [], ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']]);
                    $prices = array(
                        "supplier_item_id" => $supplier_item->id,
                        "price" => $data['price'],
                        "currency" => $data['currency'],
                        "start_date" => date("Y-m-d"),
                        "start_date" => $data['start_date'],
                        "valid_date" => $data['valid_date'],
                        "remarks" => $data['remarks']
                    );

                    $this->api->create('supplier_item_prices', $prices, "SPI", "supplier_item_prices");
                    echo $send;
                }
            } elseif (strtolower($data['action']) == "update") {
                if (empty($supplier_items->item_id)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Supplier " . $data['supplier_number'] . " and Part No " . $data['item_id'] . " Data Not Found in Supplier Items", "theme" => "error"));
                } else {
                    $maker_code = empty($data['maker_code']) ? @$supplier_items->maker_code : @$data['maker_code'];
                    $spq = ($data['spq'] == '') ? @$supplier_items->spq : @$data['spq'];
                    $mpq = ($data['mpq'] == '') ? @$supplier_items->mpq : @$data['mpq'];
                    $moq = ($data['moq'] == '') ? @$supplier_items->moq : @$data['moq'];
                    $item_supplier = empty($data['item_supplier']) ? @$supplier_items->item_supplier : @$data['item_supplier'];
                    $coo_name = empty($data['coo_name']) ? @$supplier_items->coo_name : @$data['coo_name'];
                    $landed_cost = empty($data['landed_cost']) ? @$supplier_items->landed_cost : @$data['landed_cost'];
                    $import_duty = empty($data['import_duty']) ? @$supplier_items->import_duty : @$data['import_duty'];
                    $share_order = ($data['share_order'] == '') ? @$supplier_items->share_order : @$data['share_order'];
                    $leadtime = ($data['leadtime'] == '') ? @$supplier_items->leadtime : @$data['leadtime'];
                    $currency = empty($data['currency']) ? @$supplier_items->currency : @$data['currency'];
                    $price =  ($data['price'] == '')  ? @$supplier_items->price : @$data['price'];
                    $start_date = empty($data['start_date']) ? @$supplier_items->start_date : @$data['start_date'];
                    $valid_date = empty($data['valid_date']) ? @$supplier_items->valid_date : @$data['valid_date'];
                    $remarks = empty($data['remarks']) ? @$supplier_items->remarks : @$data['remarks'];
                    $quotation_number = ($data['quotation_number'] == '') ? @$supplier_items->quotation_number : @$data['quotation_number'];
                    $quotation_date = ($data['quotation_date'] == '') ? @$supplier_items->quotation_date : @$data['quotation_date'];
                    $incoterm_code = ($data['incoterm_code'] == '') ? @$supplier_items->incoterm_code : @$data['incoterm_code'];

                    if (empty($data['start_date'])) {
                        //
                    } else {
                        if ($this->isValidDate($data['start_date'])) {
                        } else {
                            die(json_encode(array("title" => "Not Found", "message" => "Start Date " . $data['start_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                        }
                    }

                    if (empty($data['valid_date'])) {
                        //
                    } else {
                        if ($this->isValidDate($data['valid_date'])) {
                        } else {
                            die(json_encode(array("title" => "Not Found", "message" => "Valid Date " . $data['valid_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                        }
                    }

                    if (empty($data['quotation_date'])) {
                        //
                    } else {
                        if ($this->isValidDate($data['quotation_date'])) {
                        } else {
                            die(json_encode(array("title" => "Not Found", "message" => "Format Date " . $data['quotation_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
                        }
                    }

                    $postFinal = array(
                        "plant" => $data['plant'],
                        "item_id" => $item->item_id,
                        "item_supplier" => $item_supplier,
                        "supplier_id" => $supplier->id,
                        "coo_name" => $coo_name,
                        "maker_code" => $maker_code,
                        "maker_type" => $maker_code,
                        "spq" => $spq,
                        "mpq" => $mpq,
                        "moq" => $moq,
                        "price" => $price,
                        "currency" => $currency,
                        "landed_cost" => $landed_cost,
                        "import_duty" => $import_duty,
                        "share_order" => $share_order,
                        "start_date" => $start_date,
                        "valid_date" => $valid_date,
                        "leadtime" => $leadtime,
                        "remarks" => $remarks,
                        "quotation_number" => $quotation_number,
                        "quotation_date" => $quotation_date,
                        "incoterm_code" => $incoterm_code,
                    );
                    $prices = array(
                        "supplier_item_id" => $supplier_items->id,
                        "price" => empty(@$supplier_item_prices->price) ? $price : @$supplier_item_prices->price,
                        "currency" => empty(@$supplier_item_prices->currency) ? $currency : @$supplier_item_prices->currency,
                        "start_date" => empty(@$supplier_item_prices->start_date) ? $start_date : @$supplier_item_prices->start_date,
                        "valid_date" => empty(@$supplier_item_prices->valid_date) ? $valid_date : @$supplier_item_prices->valid_date,
                        "remarks" => empty(@$supplier_item_prices->remarks) ? $remarks : @$supplier_item_prices->remarks,
                    );

                    $pricesNew = array(
                        "supplier_item_id" => $supplier_items->id,
                        "price" => empty($data['price']) ? @$supplier_item_prices->price : $data['price'],
                        "currency" => empty($data['currency']) ? @$supplier_item_prices->currency : $data['currency'],
                        "start_date" => empty($data['start_date']) ? @$supplier_item_prices->start_date : $data['start_date'],
                        "valid_date" => empty($data['valid_date']) ? @$supplier_item_prices->valid_date : $data['valid_date'],
                        "remarks" => empty($data['remarks']) ? @$supplier_item_prices->remarks : $data['remarks'],
                    );

                    if ($data['price'] != '' || !empty($data['valid_date'])) {
                        // if ($data['price'] != @$supplier_item_prices->price || $data['valid_date'] != @$supplier_item_prices->valid_date) {
                        if (empty(@$supplier_item_prices->price)) {
                            $this->api->create("supplier_item_prices", $prices, "SPI", "supplier_item_prices");
                        } else {
                            $this->api->update('supplier_item_prices', ["supplier_item_id" => $supplier_items->id], $pricesNew);
                        }

                        $this->api->create("supplier_item_prices_log", $prices, "JUTA", "supplier_item_prices_log");
                        // }
                    }

                    $send = $this->api->update('supplier_items', ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant']], $postFinal);
                    $this->api->create("supplier_items_log", array_merge($postFinal, ["supplier_item_id" => $supplier_items->id]), "SPL", "supplier_items_log");
                    echo $send;
                }
            } else {
                die(json_encode(array("title" => "Unknown", "message" => "Action " . $data['action'], "theme" => "error")));
            }
        }
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            $name = "vendor_contracts";
            if (!empty($this->input->get('vendor'))) {
                $name = "quotation";
            }
            $name .= "_$format";
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=$name.xls");
        }

        // $filter_plant  = base64_decode($this->input->get('filter_plant'));
        // $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        // $filter_category  = base64_decode($this->input->get('filter_category'));
        // $filter_maker  = base64_decode($this->input->get('filter_maker'));
        // $filter_items = base64_decode($this->input->get('filter_items'));
        // $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        // $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        // $filter_check = $this->input->get('filter_check');

        // $this->db->select('a.*, g.valid_date, g.price, g.currency, c.item_id, c.item_name, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, f.name as maker_name, f.type as maker_type, f.origin as maker_origin');
        // $this->db->from('supplier_items a');
        // $this->db->join('suppliers b', 'b.id = a.supplier_id');
        // $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        // $this->db->join('uom d', 'c.um_id = d.number', 'left');
        // $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        // $this->db->join('mst_maker f', 'a.maker_code = f.number');
        // $this->db->join("(SELECT supplier_item_id, price, currency, valid_date FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id) GROUP BY supplier_item_id) g", 'a.id = g.supplier_item_id', 'left');
        // if($filter_check == "true"){
        //     $this->db->join("(SELECT item_id FROM supplier_items GROUP BY item_id HAVING COUNT(*) > 1) h", 'a.item_id = h.item_id');
        // }
        // if($filter_plant != ""){
        //     $this->db->like('a.plant', $filter_plant);
        // }
        // if($filter_category != ""){
        //     $this->db->like('c.pfm_id', $filter_category);
        // }
        // $this->db->like('a.supplier_id', $filter_suppliers);
        // $this->db->like('a.item_id', $filter_items);
        // $this->db->like('f.number', $filter_maker);
        // if($filter_item_vendor != ""){
        //     $this->db->like('a.item_supplier', $filter_item_vendor);
        // }
        // if($filter_share_order != ""){
        //     $this->db->where('a.share_order', $filter_share_order);
        // }
        // if($filter_check == "false"){
        //     $this->db->order_by('b.name', 'ASC');
        // }
        // $this->db->order_by('a.item_id', 'ASC');
        // $records = $this->db->get()->result_array();

        $filter_plant  = base64_decode($this->input->get('filter_plant'));
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_check = $this->input->get('filter_check');
        $filter_quotation = base64_decode($this->input->get('filter_quotation'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        $no_limit = !empty($this->input->get("no_limit"));
        $user_vendor = base64_decode($this->input->get('user'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, g.valid_date, g.price, g.currency, c.item_id, c.item_name, b.number as supplier_number, b.origin, e.name as pfm_name, b.name as supplier_name, b.type, d.name as uom, m.uom_to as uom_convertion, f.name as maker_name, f.type as maker_type, f.origin as maker_origin, CASE WHEN a.created_by = '$user_vendor' THEN COALESCE(TRIM(h.name), '') ELSE '' END AS create_by, CASE WHEN a.created_by = '$user_vendor' THEN DATE(a.created_date) ELSE '' END AS create_date, c.um_id, i.name AS incoterm,
        case when g.currency = 'IDR' then 1 else k.middle end as exchange_rate , 
        j.country_name, j.total_ddp,
        coalesce((g.price * (case when g.currency = 'IDR' then 1 else k.middle end))+ (g.price * (case when g.currency = 'IDR' then 1 else k.middle end) * COALESCE(j.total_ddp, 0)), 0) AS price_procurement
        ");
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('mst_item_raw c', 'c.item_id = a.item_id');
        $this->db->join('uom d', 'c.um_id = d.number', 'left');
        $this->db->join('mst_item_family e', 'c.pfm_id = e.number', 'left');
        $this->db->join('mst_maker f', 'a.maker_code = f.number', 'left');
        $this->db->join("(SELECT supplier_item_id, price, currency, start_date, valid_date, exchange_rate FROM supplier_item_prices WHERE (supplier_item_id, valid_date) IN (SELECT supplier_item_id, MAX(valid_date) FROM supplier_item_prices GROUP BY supplier_item_id) GROUP BY supplier_item_id) g", 'a.id = g.supplier_item_id', 'left');
        $this->db->join("user h", "a.created_by = h.username", "LEFT");
        // $this->db->join("exchange_rates k", 'k.start_date <= g.valid_date AND k.end_date >= g.valid_date and k.currency = g.currency', 'left');
        $this->db->join("exchange_rates k", 'k.start_date = g.start_date and k.currency = g.currency', 'left');
        if ($filter_check == "true") {
            $this->db->join("(SELECT item_id FROM supplier_items GROUP BY item_id HAVING COUNT(*) > 1) i", 'a.item_id = i.item_id');
        }
        $this->db->join("incoterms i", "a.incoterm_code = i.number", "LEFT");
        $this->db->join("(
                    select ja.*, jb.name as country_name from setup_incoterms ja 
                    left join country_of_origins jb on ja.country_number = jb.number
                    ) j", "a.incoterm_code = j.incoterm_number and b.origin=j.country_name", "LEFT");
        $this->db->join('convertions m', 'a.item_id = m.item_id', 'left'); // $this->db->join("(".
        //     "SELECT DISTINCT MAX(id) AS max, supplier_id, item_id FROM supplier_items ".
        //     "WHERE COALESCE(approved_to, '') = '' GROUP BY supplier_id, item_id".
        // ") j", "a.id = j.max AND a.supplier_id = j.supplier_id");
        if ($filter_plant != "") {
            $this->db->like('a.plant', $filter_plant);
        }
        if ($filter_category != "") {
            $this->db->like('c.pfm_id', $filter_category);
        }
        $this->db->like('a.supplier_id', $filter_suppliers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('f.number', $filter_maker);
        if ($filter_item_vendor != "") {
            $this->db->like('a.item_supplier', $filter_item_vendor);
        }
        if ($filter_share_order != "") {
            $this->db->where('a.share_order', $filter_share_order);
        }
        if ($filter_quotation != "") {
            $this->db->where("a.quotation_number", $filter_quotation);
        }
        // die(var_dump(!empty($this->input->get('vendor'))));
        if (!empty($this->input->get('vendor'))) {
            $q = str_replace("`a`.*", "a.id, 'vendor' AS act, aa.status, aa.reason, a.plant, a.quotation_number, a.item_supplier, a.coo_name, a.spq, a.mpq, a.moq, a.lme_range, a.leadtime, a.share_order, a.safety_stock, a.calculate, a.provide, a.remarks, aa.approved, aa.approved_to, aa.approved_by, aa.approved_date, a.created_by, a.created_date, a.updated_by, a.updated_date, '' AS data_prices", $this->db->get_compiled_select());
            $q1 = str_replace("aa.approved, aa.approved_to, aa.approved_by, aa.approved_date", "NULL AS approved, NULL AS approved_to, NULL AS approved_by, NULL AS approved_date", $q);
            $q1 = str_replace("'vendor'", "'supplier'", $q1);
            $q1 = str_replace("aa.status", "a.status", $q1);
            $q1 = str_replace("aa.reason", "NULL AS reason", $q1);
            $q1 = str_replace("a.lme_range", "'' AS lme_range", $q1);
            $q2 = str_replace("supplier_items", "temp_vendor_contract", $q);
            $q2 = str_replace("`g`.`price`", "`a`.`price`", $q2);
            $q2 = str_replace("`g`.`currency`", "`a`.`currency`", $q2);
            $q2 = str_replace("`g`.`valid_date`", "`a`.`valid_date`", $q2);
            $q2 = str_replace("`g`.`start_date`", "`a`.`start_date`", $q2);
            $q2 = str_replace("WHERE `a`", " JOIN (" .
                "SELECT MAX(id) AS id, status, reason, approved, approved_to, approved_by, approved_date FROM temp_vendor_contract GROUP BY item_id, supplier_id"
                . ") aa ON a.id = aa.id WHERE a", $q2);
            $q = "SELECT * FROM ($q2 UNION $q1) AS unionTable ";
            if ($filter_status != "") {
                $where_status = [];
                if ($filter_status == 4) {
                    $where_status[] = "((status = 1 AND act = 'vendor') OR (status = 0 AND act = 'supplier'))";
                } elseif ($filter_status == 3) {
                    $where_status[] = "status = 2";
                } elseif ($filter_status == 2) {
                    $where_status[] = "approved != 1";
                } elseif ($filter_status == 1) {
                    $where_status[] = "act = 'vendor'";
                    $where_status[] = "approved = 1";
                    $where_status[] = "status = 0";
                }
                $q .= "WHERE " . implode(" AND ", $where_status) . " ";
            }
            $q .= "GROUP BY item_id ORDER BY ";
            if ($filter_check == "false") {
                $q .= "supplier_name, ";
            }
            $q .= "item_id";
            // die($q);
            $records = $this->db->query($q)->result_array();

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
                                <small>QUOTATION</small>
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
                        <th width="20" rowspan="2">No</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Part No</th>
                        <th rowspan="2">Part Vendor</th>
                        <th rowspan="2">Part Name</th>
                        <th rowspan="2">UoM</th>
                        <th rowspan="2">Maker</th>
                        <th rowspan="2">Maker Type</th>
                        <th rowspan="2">Maker Origin</th>
                        <th rowspan="2">Last Quotation No</th>
                        <th rowspan="2">SPQ</th>
                        <th rowspan="2">MPQ</th>
                        <th rowspan="2">MOQ</th>
                        <th rowspan="2">Leadtime</th>
                        <th rowspan="2">Currency</th>
                        <th rowspan="2">Incoterm</th>
                        <th rowspan="2">Exchange Rate</th>
                        <th rowspan="2">Price</th>
                        <th colspan="2">Valid Date</th>
                        <th colspan="2">Created</th>
                        <th rowspan="2">Reason</th>
                    </tr>
                    <tr>
                        <th>Start</th>
                        <th>Ending</th>
                        <th>By</th>
                        <th>Date</th>
                    </tr>';
            $no = 1;
            foreach ($records as $data) {
                $col_status = 'Checking';
                if ($data['status'] == '2') {
                    $col_status = 'Disapproved';
                } elseif ($data['approved'] == '1') {
                    $col_status = 'Submission';
                } elseif ($data['approved_to'] == "" || $data['approved_to'] == null) {
                    $col_status = 'Approved';
                }
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $col_status . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td>' . $data['maker_origin'] . '</td>
                            <td>' . $data['quotation_number'] . '</td>
                            <td>' . $data['spq'] . '</td>
                            <td>' . $data['mpq'] . '</td>
                            <td>' . $data['moq'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['incoterm'] . '</td>
                            <td>' . ($data['exchange_rate'] ?? 0) . '</td>
                            <td>' . $data['price'] . '</td>
                            <td>' . $data['reason'] . '</td>
                            <td>' . $data['start_date'] . '</td>
                            <td>' . $data['valid_date'] . '</td>
                            <td>' . $data['create_by'] . '</td>
                            <td>' . $data['create_date'] . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        } else {
            $this->db->order_by("price_procurement");
            if ($filter_check == "false") {
                $this->db->order_by('supplier_name');
            }
            $this->db->order_by("a.item_id");
            $totalRows = $this->db->count_all_results('', false);
            if (!isset($no_limit)) {
                $this->db->limit($rows, $offset);
            }
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
                                <small>MASTER VENDOR CONTRACT</small>
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
                        <th>Approved</th>
                        <th>Approved By</th>
                        <th>Approved Date</th>
                        <th>Plant</th>
                        <th>Vendor ID</th>
                        <th>Vendor Name</th>
                        <th>Quotation</th>
                        <th>Part No. BEI</th>
                        <th>Part Name</th>
                        <th>Part Vendor</th>
                        <th>Vendor Type</th>
                        <th>Vendor Origin</th>
                        <th>COO Name</th>
                        <th>Product Family</th>
                        <th>Maker</th>
                        <th>Maker Type</th>
                        <th>Country Of Origin</th>
                        <th>Landed Cost</th>
                        <th>Import Duty</th>
                        <th>SPQ</th>
                        <th>MPQ</th>
                        <th>MOQ</th>
                        <th>Share Order</th>
                        <th>Provide Customer</th>
                        <th>Leadtime (Days)</th>
                        <th>Safety<br>Stock (%)</th>
                        <th>Calculate MPQ</th>
                        <th>Account COA</th>
                        <th>UoM</th>
                        <th>UoM<br>Convertion</th>
                        <th>Start Date</th>
                        <th>Valid Date</th>
                        <th>Price</th>
                        <th>Currency</th>
                        <th>Exchange Rate</th>
                        <th>Incoterm</th>
                        <th>Total DDP %</th>
                        <th>Procurement Price (IDR)</th>
                        <th>Cheaper</th>
                        <th>Remarks</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Updated By</th>
                <th>Updated Date</th>
                    </tr>';
            $no = 1;

            $sortedRecords = $records;
            usort($sortedRecords, function ($a, $b) {
                $cmp = strcmp($a['item_id'], $b['item_id']);
                if ($cmp !== 0) {
                    return $cmp;
                }

                $plantCmp = strcmp((string) $a['plant'], (string) $b['plant']);
                if ($plantCmp !== 0) {
                    return $plantCmp;
                }

                return $a['price_procurement'] <=> $b['price_procurement'];
            });

            $cheaperMap = [];
            $lastGroupKey = null;
            $cheaperRank = 0;
            foreach ($sortedRecords as $row) {
                $groupKey = $row['item_id'] . '|' . $row['plant'];
                if ($lastGroupKey !== $groupKey) {
                    $lastGroupKey = $groupKey;
                    $cheaperRank = 1;
                } else {
                    $cheaperRank++;
                }
                $lookupKey = $row['supplier_id'] . '|' . $row['item_id'] . '|' . $row['plant'];
                $cheaperMap[$lookupKey] = 'CHEAPER ' . $cheaperRank;
            }

            foreach ($records as $data) {
                if ($data['approved_to'] == "") {
                    $approved = "APPROVED";
                } else {
                    $approved = "CHECKING";
                }
                $lookupKey = $data['supplier_id'] . '|' . $data['item_id'] . '|' . $data['plant'];
                $cheaper = isset($cheaperMap[$lookupKey]) ? $cheaperMap[$lookupKey] : '';
                $html .= '<tr>
                            <td>' . $no . '</td>   <td>' . $approved . '</td>
                            <td>' . $data['approved_by'] . '</td>
                            <td>' . $data['approved_date'] . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['supplier_number'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['quotation_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_supplier'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['origin'] . '</td>
                            <td>' . $data['coo_name'] . '</td>
                            <td>' . $data['pfm_name'] . '</td>
                            <td>' . $data['maker_name'] . '</td>
                            <td>' . $data['maker_type'] . '</td>
                            <td>' . $data['maker_origin'] . '</td>
                            <td>' . $data['landed_cost'] . '</td>
                            <td>' . $data['import_duty'] . '</td>
                            <td>' . $data['spq'] . '</td>
                            <td>' . $data['mpq'] . '</td>
                            <td>' . $data['mpq'] . '</td>
                            <td>' . $data['share_order'] . '</td>
                            <td>' . $data['provide'] . '</td>
                            <td>' . $data['leadtime'] . '</td>
                            <td>' . $data['safety_stock'] . '</td>
                            <td>' . $data['calculate'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['uom_convertion'] . '</td>
                            <td>' . $data['start_date'] . '</td>
                            <td>' . $data['valid_date'] . '</td>
                            <td>' . $data['price'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['exchange_rate'] . '</td>
                            <td>' . $data['incoterm'] . '</td>
                            <td>' . $data['total_ddp'] . '</td>
                            <td>' . $data['price_procurement'] . '</td>
                            <td>' . $cheaper . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                            <td>' . $data['updated_by'] . '</td>
                            <td>' . $data['updated_date'] . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }

    public function excel_price($option = "")
    {
        $format  = date("Ymd");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=vendor_items_price_history_$format.xls");

        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_category  = base64_decode($this->input->get('filter_category'));
        $filter_maker  = base64_decode($this->input->get('filter_maker'));
        $filter_item_rm = base64_decode($this->input->get('filter_item_rm'));
        $filter_item_vendor = base64_decode($this->input->get('filter_item_vendor'));
        $filter_share_order = base64_decode($this->input->get('filter_share_order'));
        $filter_status_price = base64_decode($this->input->get('filter_status_price'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_check = $this->input->get('filter_check');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $sql = $this->db->query("SELECT z.* FROM 
            (SELECT a.item_id, c.item_supplier as item_rm_number, c.item_name as item_rm_name, d.name as item_family_name, a.supplier_id, b.name as supplier_name, b.type, b.currency, a.price, a.valid_date, a.remarks, a.created_by, a.created_date, a.plant, a.start_date
             FROM supplier_items_log a 
             JOIN suppliers b ON a.supplier_id = b.id
             JOIN mst_item_raw c ON a.item_id = c.id
             JOIN mst_item_family d ON c.pfm_id = d.id and d.groups <> 'Finished Good'
             WHERE c.pfm_id like '%$filter_category%' AND a.supplier_id like '%$filter_suppliers%' AND a.item_id like '%$filter_item_rm%' and b.type like '%$filter_type%'
             UNION ALL
             SELECT a.item_id, c.item_supplier as item_rm_number, c.item_name as item_rm_name, d.name as item_family_name, a.supplier_id, b.name as supplier_name, b.type, b.currency, a.price, a.valid_date, a.remarks, (CASE WHEN a.updated_by is null THEN a.created_by ELSE a.updated_by END) as created_by, (CASE WHEN a.updated_date is null THEN a.created_date ELSE a.updated_date END) as created_date, a.plant, a.start_date
             FROM supplier_items a 
             JOIN suppliers b ON a.supplier_id = b.id
             JOIN mst_item_raw c ON a.item_id = c.id
             JOIN mst_item_family d ON c.pfm_id = d.id and d.groups <> 'Finished Good'
             WHERE c.pfm_id like '%$filter_category%' AND a.supplier_id like '%$filter_suppliers%' AND a.item_id like '%$filter_item_rm%' and b.type like '%$filter_type%') z
             GROUP BY z.supplier_id, z.item_rm_number, z.price
             ORDER BY z.supplier_id, z.item_rm_number, z.created_date DESC");
        $records = $sql->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>HISTORY SUPPLIER CONTRACT PRICE RM</h3>
            </div>
        </center>
        
        <table id="supplier_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Plant</th>
                <th>Vendor ID</th>
                <th>Vendor Name</th>
                <th>Original Part No</th>
                <th>Part No Vendor</th>
                <th>Description</th>
                <th>Vendor Type</th>
                <th>Product Family</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Start Date</th>
                <th>Valid Date</th>
                <th>Remarks</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['plant'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['supplier_id'] . '</td>
                    <td>' . $data['supplier_name'] . '</td>
                    <td>' . $data['item_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['start_date'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['remarks'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

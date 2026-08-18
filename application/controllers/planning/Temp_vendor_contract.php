<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Temp_vendor_contract extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->form_validation->set_rules('item_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            redirect('error_access');
        }
    }

    public function findQuotation()
    {
        $quotation = base64_decode($this->input->post('quotation'));
        $item = base64_decode($this->input->post('item'));
        $supplier = $this->input->post('supplier');
        // die(var_dump($this->input->post()));
        $this->db->where([
            "supplier_id" => $supplier,
            "quotation_number" => $quotation,
            "item_id" => $item,
            "status" => 0
        ]);
        $count = $this->db->count_all_results("temp_vendor_contract");
        // die(var_dump($count));
        // die($this->db->get_compiled_select());
        echo json_encode(["count" => $count]);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run()) {
                $post = $this->input->post();
                if (isset($post['is_api'])) {
                    $this->session->set_userdata('username', $post['is_api']);
                    unset($post['is_api']);
                }

                $temp_vendor_contract = $this->api->read('temp_vendor_contract', [], [
                    "status" => 0,
                    "supplier_id" => $post['supplier_id'],
                    "item_id" => $post['item_id'],
                    "plant" => $post['plant'],
                    "quotation_number" => $post["quotation_number"]
                ]);
                if (!$temp_vendor_contract) {
                    $send = $this->api->create('temp_vendor_contract', $post, "SPI", "temp_vendor_contract");
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

    //UPLOAD DATA
    function isValidDate($date, $format = 'Y-m-d') {
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
            $uom = $this->api->read('uom', [], ["number" => $data['uom_code']]);
            $currencies = $this->api->read('currencies', [], ["name" => $data['currency']]);
            $incoterms = $this->api->read('incoterms', [], ["number" => $data['incoterm']]);
            $temp_vendor_contract = $this->api->read('temp_vendor_contract', [], ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id, "plant" => $data['plant'], "quotation_number" => $data['quotation_number'] ?? '']);
            // $supplier_item_prices = $this->api->read('supplier_item_prices', [], ["supplier_item_id" => @$temp_vendor_contract->id]);
            $supplier_items = $this->api->read('supplier_items', [], ["item_id" => @$item->item_id, "supplier_id" => @$supplier->id]);

            if (!empty($temp_vendor_contract)) {
                if (!in_array($temp_vendor_contract->approved, [0,1]) && $temp_vendor_contract->status == 0) {
                    die(json_encode(array("title" => "Failed", "message" => "This Item Still Checking in Approval", "theme" => "error")));
                }
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

            if (empty($uom->number)) {
                die(json_encode(array("title" => "Not Found", "message" => "UoM " . $data['uom_code'] . " Not Found", "theme" => "error")));
            }

            if (empty($maker->number)) {
                die(json_encode(array("title" => "Not Found", "message" => "Maker " . $data['maker_code'] . " Not Found", "theme" => "error")));
            }

            if (empty($supplier_items)) {
                die(json_encode(array("title" => "Not Found", "message" => "Item Not Found in Vendor Contract", "theme" => "error")));
            }

            if (@$supplier_items->maker_code != @$temp_vendor_contract->maker_code) {
                die(json_encode(array("title" => "Failed", "message" => "Invalid Maker Code, Part No " . $data['item_id'], "theme" => "error")));
            }

            if (empty($currencies->number)) {
                die(json_encode(array("title" => "Not Found", "message" => "Currency " . $data['currency'] . " Not Found", "theme" => "error")));
            }

            if (empty($incoterms->number)) {
                die(json_encode(array("title" => "Not Found", "message" => "Incoterms " . $data['incoterm'] . " Not Found", "theme" => "error")));
            }

            if ($data['price'] <= 0) {
                die(json_encode(array("title" => "Failed", "message" => "Price Cannot Input 0", "theme" => "error")));
            }

            if (!$this->isValidDate($data['start_date'])) {
                die(json_encode(array("title" => "Failed", "message" => "Format Date " . $data['start_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
            }

            if (!$this->isValidDate($data['valid_date'])) {
                die(json_encode(array("title" => "Failed", "message" => "Format Date " . $data['valid_date'] . " rejected, use YYYY-MM-DD format and format type is text", "theme" => "error")));
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
                "lme_range" => isset($data['lme_range']) ? $data['lme_range'] : '',
                "currency" => $data['currency'],
                "incoterm_code" => $data['incoterm'],
                "price" => $data['price'],
                "landed_cost" => $data['landed_cost'],
                "import_duty" => $data['import_duty'],
                "share_order" => $data['share_order'],
                "start_date" => $data['start_date'],
                "valid_date" => $data['valid_date'],
                "leadtime" => $data['leadtime'],
                "remarks" => $data['remarks'],
                "quotation_number" => $data['quotation_number'],
                "quotation_date" => $data['quotation_date'],
                "data_prices" => json_encode(array(
                    "currency" => $data['currency'],
                    "exchange_rate" => $data['exchange_rate'],
                    "price" => $data['price'],
                    "start_date" => date("Y-m-d"),
                    "start_date" => $data['start_date'],
                    "valid_date" => $data['valid_date'],
                    "remarks" => $data['remarks']
                ))
            );
            if (empty($temp_vendor_contract->item_id)) {
                $send = $this->api->create('temp_vendor_contract', $postFinal, "SPI", "temp_vendor_contract");
            } else {
                $send = $this->api->update('temp_vendor_contract', ["id" => $temp_vendor_contract->id], $postFinal, "SPI");
            }
            echo $send;
        }
    }
}

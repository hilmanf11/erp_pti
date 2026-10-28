<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Approvals extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function approveall()
    {
        if (empty($this->session->username)) {
            die(json_encode(array("title" => "Expired", "message" => "Session has been expired", "theme" => "error")));
        }

        $approved_to = $this->input->post('approved_to');
        $approved_by = $this->input->post('approved_by');
        $created_by = $this->input->post('created_by');
        $table_name = $this->input->post('table_name');
        $filters = $this->input->post('filters');

        $data = $this->api->read($table_name, [], ["approved_to" => $approved_to, "approved_by" => $approved_by]);
        $approval = $this->api->read('approval', [], ["table_name" => $table_name]);

        if ($data->approved == 1) {
            $users_id = @$approval->user_approval_2;
            $approved = 2;
        } elseif ($data->approved == 2) {
            $users_id = @$approval->user_approval_3;
            $approved = 3;
        } elseif ($data->approved == 3) {
            $users_id = @$approval->user_approval_4;
            $approved = 4;
        } elseif ($data->approved == 4) {
            $users_id = @$approval->user_approval_5;
            $approved = 5;
        } else {
            $users_id = "";
            $approved = 0;
        }

        $values = array(
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => $users_id,
            "approved" => $approved,
        );

        $where = ["approved_to" => $approved_to, "approved_by" => $approved_by];

        if ($table_name == "temp_vendor_contract" && empty($users_id)) {
            $values["status"] = 1;
            $where["status"] = 0;

            foreach ($filters as $key => $value) {
                $where[$key] = $value;
            }

            $send = $this->processApprove("temp_vendor_contract", $where);
        } else {
            $send = true;
        }

        if ($send) {
            $this->db->update($table_name, $values, $where);

            if ($table_name == "new_part_price_procurements") {
                $send = $this->processApprove("new_part_price_procurements", [], json_decode(base64_decode($this->input->post('datas'))));
            }

            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function approve()
    {
        if (empty($this->session->username)) {
            die(json_encode(array("title" => "Expired", "message" => "Session has been expired", "theme" => "error")));
        }

        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $data = $this->api->read($tablename, [], ["id" => $id]);
        $user = $this->api->read('user', [], ["username" => $data->created_by]);
        $approval = $this->api->read('approval', [], ["table_name" => $tablename]);

        if ($data->approved == 1) {
            $users_id = @$approval->user_approval_2;
            $approved = 2;
        } elseif ($data->approved == 2) {
            $users_id = @$approval->user_approval_3;
            $approved = 3;
        } elseif ($data->approved == 3) {
            $users_id = @$approval->user_approval_4;
            $approved = 4;
        } elseif ($data->approved == 4) {
            $users_id = @$approval->user_approval_5;
            $approved = 5;
        } else {
            $users_id = "";
            $approved = 0;
        }

        $values = array(
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => $users_id,
            "approved" => $approved,
        );

        $valueLog = array(
            "table_name" => $tablename,
            "table_id" => $id,
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => $users_id,
            "approved" => $approved,
            "approved_data" => json_encode($data),
            "status" => "APPROVED",
        );

        if (in_array($tablename, [
            "temp_vendor_contract"
        ]) && empty($users_id)) {
            $values["status"] = 1;
            $send = $this->processApprove($tablename, ["id" => $id]);
        }

        $this->db->insert('approval_logs', $valueLog);
        $send = $this->db->update($tablename, $values, ["id" => $id]);
        if ($send) {
            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function processApprove($table, $where, $datas = [])
    {
        $send = true;
        $this->db->trans_start();
        if ($table == "temp_vendor_contract") {
            foreach ($this->api->reads("temp_vendor_contract", [], $where) as $index => $temp) {
                $find = $this->api->read("supplier_items", [], ["item_id" => $temp->item_id, "supplier_id" => $temp->supplier_id]);

                $data = [
                    "plant" => $temp->plant,
                    "supplier_id" => $temp->supplier_id,
                    "item_id" => $temp->item_id,
                    "item_supplier" => $temp->item_supplier,
                    "coo_name" => $temp->coo_name,
                    "maker_code" => $temp->maker_code,
                    "maker_type" => $temp->maker_type,
                    "mpq" => $temp->mpq,
                    "spq" => $temp->spq,
                    "moq" => $temp->moq,
                    "incoterm_code" => $temp->incoterm_code,
                    "price" => $temp->price,
                    "currency" => $temp->currency,
                    "start_date" => $temp->start_date,
                    "valid_date" => $temp->valid_date,
                    "landed_cost" => $temp->landed_cost,
                    "import_duty" => $temp->import_duty,
                    "share_order" => $temp->share_order,
                    "leadtime" => $temp->leadtime,
                    "remarks" => $temp->remarks,
                    "calculate" => $temp->calculate ?? 0,
                    "purchase" => $temp->purchase ?? 0,
                    "safety_stock" => $temp->safety_stock ?? 0,
                    "provide" => $temp->provide,
                    "quotation_number" => $temp->quotation_number,
                    "quotation_date" => $temp->quotation_date
                ];

                if (!empty($find)) {
                    $send = $this->api->update("supplier_items", ["id" => $find->id], $data, "log");
                } else {
                    $send = $this->api->create("supplier_items", $data, "ID", "log", "log");
                }

                $data_prices = json_decode($temp->data_prices, true);
                if (!empty($data_prices) && $send) {
                    $supplier_item = $this->api->read('supplier_items', [], ["item_id" => $temp->item_id, "supplier_id" => $temp->supplier_id, "plant" => $temp->plant, 'quotation_number' => $temp->quotation_number]);
                    $data_prices["supplier_item_id"] = $supplier_item->id;

                    $send = $this->api->create('supplier_item_prices', $data_prices, "SPI", "supplier_item_prices", "log");
                }

                if (!$send) {
                    break;
                }
            }
            // die($this->db->last_query());
        } elseif ($table == "new_part_price_procurements") {
            $fullname = $this->api->read("user", [], ["username" => $this->session->username])->name;
            $this->crm = $this->load->database('crm', TRUE);
            $this->crm->trans_start();

            foreach ($datas as $key => $value) {
                if ($send) {
                    $id = $value->id;
                    $data = [
                        "updated_by_name" => $fullname,
                        "approved_by_name" => $fullname,
                        "currency" => $value->currency,
                        "price" => $value->price,
                        "new_price" => 0,
                        "percentage" => 0,
                        "moq" => $value->moq,
                        "mpq" => $value->mpq,
                        "spq" => $value->spq,
                        "leadtime" => $value->leadtime,
                        "status" => 1,
                        "remarks1" => $value->remarks1,
                        "remarks2" => $value->remarks2,
                        "remarks3" => $value->remarks3,
                        "procurement_respons_date" => $value->procurement_respons_date,
                        "item_id_maker" => $value->item_id_maker,
                        "quot_req" => $value->quot_req,
                        "quotation" => $value->quotation,
                        "incoterms" => $value->incoterms
                    ];
                    $send = $this->api->update("new_part_price_procurements", ["id" => $id], $data);
                    if ($send) {
                        $new_data = $this->api->read("new_part_price_procurements", [], ["id" => $id]);
                        $this->api->create("new_part_price_procurement_history", [
                            "action" => "UPDATE",
                            "descriptions" => json_encode($new_data)
                        ]);
                        $this->crm->where("id", $id)->update("new_part_price_procurements", $new_data);
                    }
                }
            }

            if (!$send) {
                $this->crm->trans_rollback();
            } else {
                $this->crm->trans_commit();
            }
        }

        if (!$send) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }

        return $send;
    }

    public function disapproveall()
    {
        $created_by = $this->input->post('created_by');
        $approved_to = $this->input->post('approved_to');
        $table_name = $this->input->post('table_name');
        $datas = $this->api->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

        foreach ($datas as $data) {
            $id = $data->id;
            $read = $this->api->read($table_name, [], ["id" => $id]);
            $data = json_decode($read->approved_data, false);

            if (empty($data)) {
                if ($table_name == "supplier_items") {
                    // $this->api->delete("supplier_item_prices", ["supplier_item_id" => $id]);
                    $this->api->delete($table_name, ["id" => $id]);
                } else {
                    $this->api->delete($table_name, ["id" => $id]);
                }
            } else {
                $send = $this->db->update($table_name, $data, ["id" => $id]);
            }
        }

        echo json_encode(array("title" => "Disapproved", "message" => "All Data Disapproved Successfully", "theme" => "success"));
    }

    public function disapprove()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $message = $this->input->post('message');
        $reason = $this->input->post('reason');
        $read = $this->api->read($tablename, [], ["id" => $id]);
        $data = json_decode($read->approved_data, false);
        $merge = @array_merge((array) $read, array("disapprove" => $message));

        $valueLog = array(
            "table_name" => $tablename,
            "table_id" => $id,
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => "-",
            "approved" => "-",
            "approved_data" => json_encode($read),
            "status" => "DISAPPROVED",
        );

        /* Default */
        if (empty($data)) {
            if ($tablename == "temp_vendor_contract") {
                $this->api->update("temp_vendor_contract", ["id" => $id], ["status" => 2, "reason" => $reason]);
            } else {
                $this->api->delete($tablename, ["id" => $id]);
            }
        } else {
            $send = $this->db->update($tablename, $data, ["id" => $id]);
        }

        $this->db->insert('approval_logs', $valueLog);
        $this->api->create("notification", [
            "id_user" => $this->session->username,
            "table_name" => $tablename,
            "name" => "Disapprove",
            "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
            "log" => json_encode($merge)
        ]);

        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function approvalCount()
    {
        $users = $this->api->reads('user', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_order = $this->api->reads('sales_order', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_order_os = $this->api->reads('sales_order_os', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $fg_stock = $this->api->reads('fg_stock', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_forecast = $this->api->reads('mst_forecast', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_wip = $this->api->reads('mst_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mpp_os = $this->api->reads('mpp_os', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_umh = $this->api->reads('mst_umh', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_umh_havcp = $this->api->reads('mst_umh_havcp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $rm_stock = $this->api->reads('rm_stock', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $os_vendor = $this->api->reads('os_vendor', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $os_mpp = $this->api->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $os_supply = $this->api->reads('os_supply', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $balance_wip = $this->api->reads('balance_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $upload_gp = $this->api->reads('upload_gp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrp_finals = $this->api->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->api->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->api->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_items = $this->api->reads('supplier_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_item_prices = $this->api->reads('supplier_item_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $simulation_mps = $this->api->reads('simulation_mps', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $forecast_supplier = $this->api->reads('forecast_supplier', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $request_npd = $this->api->reads('request_npd', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $temp_vendor_contract = $this->api->reads('temp_vendor_contract', [], ["approved_to" => $this->session->username, "status" => 0], "", "", "", ["approved_to", "approved_by"]);
        $new_part_price_procurements = $this->api->reads('new_part_price_procurements', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (
            count($users) +
            count($generate_mrp_finals) +
            count($upload_gp) +
            count($balance_wip) +
            count($os_supply) +
            count($os_mpp) +
            count($os_vendor) +
            count($rm_stock) +
            count($mst_umh_havcp) +
            count($mst_umh) +
            count($mpp_os) +
            count($mst_wip) +
            count($mst_forecast) +
            count($fg_stock) +
            count($sales_order_os) +
            count($sales_order) +
            count($purchase_orders) +
            count($purchase_requests) +
            count($supplier_items) +
            count($supplier_item_prices) +
            count($simulation_mps) +
            count($forecast_supplier) +
            count($request_npd) +
            count($temp_vendor_contract) +
            count($new_part_price_procurements)
        ); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        //Users
        $users = $this->api->reads('user', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_order = $this->api->reads('sales_order', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_order_os = $this->api->reads('sales_order_os', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $fg_stock = $this->api->reads('fg_stock', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_forecast = $this->api->reads('mst_forecast', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_wip = $this->api->reads('mst_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mpp_os = $this->api->reads('mpp_os', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_umh = $this->api->reads('mst_umh', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $mst_umh_havcp = $this->api->reads('mst_umh_havcp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $rm_stock = $this->api->reads('rm_stock', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $os_vendor = $this->api->reads('os_vendor', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $os_supply = $this->api->reads('os_supply', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $os_mpp = $this->api->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $balance_wip = $this->api->reads('balance_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $upload_gp = $this->api->reads('upload_gp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrp_finals = $this->api->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->api->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->api->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_items = $this->api->reads('supplier_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_item_prices = $this->api->reads('supplier_item_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $simulation_mps = $this->api->reads('simulation_mps', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $forecast_supplier = $this->api->reads('forecast_supplier', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $request_npd = $this->api->reads('request_npd', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $temp_vendor_contract = $this->api->reads('temp_vendor_contract', [], ["approved_to" => $this->session->username, "status" => 0], "", "", "", ["approved_to", "approved_by"]);
        $new_part_price_procurements = $this->api->reads('new_part_price_procurements', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, $user->created_by, "user");
        }
        foreach ($supplier_items as $supplier_item) {
            $this->approvalMessage($supplier_item->approved_by, $supplier_item->approved_to, $supplier_item->created_by, "supplier_items");
        }
        foreach ($purchase_orders as $po) {
            $this->approvalMessage($po->approved_by, $po->approved_to, $po->created_by, "purchase_orders");
        }
        foreach ($purchase_requests as $pr) {
            $this->approvalMessage($pr->approved_by, $pr->approved_to, $pr->created_by, "purchase_requests");
        }
        foreach ($sales_order as $so) {
            $this->approvalMessage($so->approved_by, $so->approved_to, $so->created_by, "sales_order");
        }
        foreach ($sales_order_os as $soos) {
            $this->approvalMessage($soos->approved_by, $soos->approved_to, $soos->created_by, "sales_order_os");
        }
        foreach ($fg_stock as $fgs) {
            $this->approvalMessage($fgs->approved_by, $fgs->approved_to, $fgs->created_by, "fg_stock");
        }
        foreach ($mst_forecast as $fc) {
            $this->approvalMessage($fc->approved_by, $fc->approved_to, $fc->created_by, "mst_forecast");
        }
        foreach ($mst_wip as $wip) {
            $this->approvalMessage($wip->approved_by, $wip->approved_to, $wip->created_by, "mst_wip");
        }
        foreach ($mpp_os as $mppos) {
            $this->approvalMessage($mppos->approved_by, $mppos->approved_to, $mppos->created_by, "mpp_os");
        }
        foreach ($mst_umh as $umh) {
            $this->approvalMessage($umh->approved_by, $umh->approved_to, $umh->created_by, "mst_umh");
        }
        foreach ($mst_umh_havcp as $havcp) {
            $this->approvalMessage($havcp->approved_by, $havcp->approved_to, $havcp->created_by, "mst_umh_havcp");
        }
        foreach ($rm_stock as $rms) {
            $this->approvalMessage($rms->approved_by, $rms->approved_to, $rms->created_by, "rm_stock");
        }
        foreach ($os_vendor as $osv) {
            $this->approvalMessage($osv->approved_by, $osv->approved_to, $osv->created_by, "os_vendor");
        }
        foreach ($os_supply as $supply) {
            $this->approvalMessage($supply->approved_by, $supply->approved_to, $supply->created_by, "os_supply");
        }
        foreach ($os_mpp as $osmpp) {
            $this->approvalMessage($osmpp->approved_by, $osmpp->approved_to, $osmpp->created_by, "os_mpp");
        }
        foreach ($balance_wip as $bwip) {
            $this->approvalMessage($bwip->approved_by, $bwip->approved_to, $bwip->created_by, "balance_wip");
        }
        foreach ($upload_gp as $ugp) {
            $this->approvalMessage($ugp->approved_by, $ugp->approved_to, $ugp->created_by, "upload_gp");
        }
        foreach ($generate_mrp_finals as $mrp) {
            $this->approvalMessage($mrp->approved_by, $mrp->approved_to, $mrp->created_by, "generate_mrp_finals");
        }
        foreach ($supplier_item_prices as $price) {
            $this->approvalMessage($price->approved_by, $price->approved_to, $price->created_by, "supplier_item_prices");
        }
        foreach ($simulation_mps as $mps) {
            $this->approvalMessage($mps->approved_by, $mps->approved_to, $mps->created_by, "simulation_mps");
        }
        foreach ($forecast_supplier as $fs) {
            $this->approvalMessage($fs->approved_by, $fs->approved_to, $fs->created_by, "forecast_supplier");
        }
        foreach ($request_npd as $npd) {
            $this->approvalMessage($npd->approved_by, $npd->approved_to, $npd->created_by, "request_npd");
        }
        foreach ($temp_vendor_contract as $npd) {
            $this->approvalMessage($npd->approved_by, $npd->approved_to, $npd->created_by, "temp_vendor_contract");
        }
        foreach ($new_part_price_procurements as $npd) {
            $this->approvalMessage($npd->approved_by, $npd->approved_to, $npd->created_by, "new_part_price_procurements");
        }
    }

    public function approvalMessage($approved_by, $approved_to, $created_by, $table)
    {
        $user = $this->api->read('user', [], ["username" => $approved_by]);
        $user_name = @$user->name;
        $name = strtoupper(str_replace("_", " ", $table));
        $date = '';

        if (empty($user->avatar)) {
            $avatar = "default.png";
        } else {
            $avatar = $user->avatar;
        }

        if ($table == 'temp_vendor_contract') {
            $name = 'VMS - Price Quotation';

            $this->db->select("MIN(created_date) AS min, supplier_id")
                ->from($table)->where(["approved_to" => $approved_to]);
            $get = $this->db->get()->row();
            $date = ' on ' . $get->min;

            $this->db->distinct()->select("name")->from("suppliers")->where(["id" => $get->supplier_id]);
            $user_name = $this->db->get()->row()->name;
        }
        if ($table == 'new_part_price_procurements') {
            $create_name = $this->api->read('new_part_price_procurements', [], ["approved_to" => $approved_to]);
            $user_name = $create_name->created_by_name;
        }

        $link = "approvalDetail('$table', '$approved_to', '$created_by', '$approved_by')";
        echo '  <li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td width="30">
                                    <div class="icon-container">
                                        <img src="assets/image/users/' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . $user_name . '</b><br>
                                    <small>Sent a request' . $date . ' to approve data <b>' . $name . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function user($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "user";
            $this->load->view('template/header', $data);
            $this->load->view('approval/user');
        }
    }

    public function sales_order($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sales_order";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sales_order');
        }
    }

    public function sales_order_os($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sales_order_os";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sales_order_os');
        }
    }

    public function fg_stock($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "fg_stock";
            $this->load->view('template/header', $data);
            $this->load->view('approval/fg_stock');
        }
    }

    public function mst_forecast($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "mst_forecast";
            $this->load->view('template/header', $data);
            $this->load->view('approval/mst_forecast');
        }
    }

    public function mst_wip($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "mst_wip";
            $this->load->view('template/header', $data);
            $this->load->view('approval/mst_wip');
        }
    }

    public function mpp_os($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "mpp_os";
            $this->load->view('template/header', $data);
            $this->load->view('approval/mpp_os');
        }
    }

    public function mst_umh($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "mst_umh";
            $this->load->view('template/header', $data);
            $this->load->view('approval/mst_umh');
        }
    }

    public function mst_umh_havcp($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "mst_umh_havcp";
            $this->load->view('template/header', $data);
            $this->load->view('approval/mst_umh_havcp');
        }
    }

    public function rm_stock($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "rm_stock";
            $this->load->view('template/header', $data);
            $this->load->view('approval/rm_stock');
        }
    }

    public function os_vendor($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "os_vendor";
            $this->load->view('template/header', $data);
            $this->load->view('approval/os_vendor');
        }
    }

    public function os_supply($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "os_supply";
            $this->load->view('template/header', $data);
            $this->load->view('approval/os_supply');
        }
    }

    public function os_mpp($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "os_mpp";
            $this->load->view('template/header', $data);
            $this->load->view('approval/os_mpp');
        }
    }

    public function balance_wip($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "balance_wip";
            $this->load->view('template/header', $data);
            $this->load->view('approval/balance_wip');
        }
    }

    public function upload_gp($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "upload_gp";
            $this->load->view('template/header', $data);
            $this->load->view('approval/upload_gp');
        }
    }

    public function generate_mrp_finals($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "generate_mrp_finals";
            $this->load->view('template/header', $data);
            $this->load->view('approval/generate_mrp_finals');
        }
    }

    public function purchase_orders($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_orders";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_orders');
        }
    }

    public function purchase_requests($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_requests";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_requests');
        }
    }

    public function supplier_items($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supplier_items";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supplier_items');
        }
    }

    public function supplier_item_prices($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supplier_item_prices";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supplier_item_prices');
        }
    }

    public function simulation_mps($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "simulation_mps";
            $this->load->view('template/header', $data);
            $this->load->view('approval/simulation_mps');
        }
    }

    public function forecast_supplier($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "forecast_supplier";
            $this->load->view('template/header', $data);
            $this->load->view('approval/forecast_supplier');
        }
    }

    public function request_npd($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "request_npd";
            $this->load->view('template/header', $data);
            $this->load->view('approval/request_npd');
        }
    }

    public function temp_vendor_contract($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "temp_vendor_contract";
            $this->load->view('template/header', $data);
            $this->load->view('approval/temp_vendor_contract');
        }
    }

    public function new_part_price_procurements($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "new_part_price_procurements";
            $this->load->view('template/header', $data);
            $this->load->view('approval/new_part_price_procurements');
        }
    }

    public function approvalUsers($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalSalesOrder($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_order');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalSalesOrderOs($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('sales_order_os');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalFgStock($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('fg_stock');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalForecast($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('mst_forecast');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalRequestNpd($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('request_npd');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalWip($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('mst_wip');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalMppOs($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('mpp_os');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalUmh($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, c.name as process_main_name, d.name as process_sub_name, e.name as process_name');
        $this->db->from('mst_umh a');
        $this->db->join('notification b', 'a.id = b.table_id');
        $this->db->join('mst_process_main c', 'a.process_main_number = c.number');
        $this->db->join('mst_process_sub d', 'a.process_sub_number = d.number');
        $this->db->join('mst_process e', 'a.process_number = e.number');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "process_main_name") {
                    $this->db->like("c.name", $filter->value);
                } elseif ($filter->field == "process_sub_name") {
                    $this->db->like("d.name", $filter->value);
                } elseif ($filter->field == "process_name") {
                    $this->db->like("e.name", $filter->value);
                } else {
                    $this->db->like("a." . $filter->field, $filter->value);
                }
            }
        }
        $this->db->order_by('a.created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalUmhHavcp($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, c.name as process_main_name, d.name as process_sub_name, e.name as process_name');
        $this->db->from('mst_umh_havcp a');
        $this->db->join('mst_process_main c', 'a.process_main_number = c.number');
        $this->db->join('mst_process_sub d', 'a.process_sub_number = d.number');
        $this->db->join('mst_process e', 'a.process_number = e.number');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "process_main_name") {
                    $this->db->like("c.name", $filter->value);
                } elseif ($filter->field == "process_sub_name") {
                    $this->db->like("d.name", $filter->value);
                } elseif ($filter->field == "process_name") {
                    $this->db->like("e.name", $filter->value);
                } else {
                    $this->db->like("a." . $filter->field, $filter->value);
                }
            }
        }
        $this->db->order_by('a.created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalRmStock($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('rm_stock');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalOsVendor($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('os_vendor');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalOsSupply($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('os_supply');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalOsMpp($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('os_mpp');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalBalanceWip($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('balance_wip');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalUploadGp($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, b.item_name, b.item_supplier, c.name as product_family, d.name as supplier_name, d.origin, f.name as maker_name, f.type as maker_type');
        $this->db->from('upload_gp a');
        $this->db->join('mst_item_raw b', 'a.item_id = b.item_id');
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.supplier_id = e.supplier_id and a.item_id = e.item_id and a.plant = e.plant');
        $this->db->join('mst_maker f', 'e.maker_code = f.number');
        $this->db->where('a.approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('a.approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "product_family") {
                    $this->db->like("c.name", $filter->value);
                } elseif ($filter->field == "supplier_name") {
                    $this->db->like("d.name", $filter->value);
                } elseif ($filter->field == "maker_name") {
                    $this->db->like("f.name", $filter->value);
                } elseif ($filter->field == "item_name") {
                    $this->db->like("b.item_name", $filter->value);
                } else {
                    $this->db->like("a." . $filter->field, $filter->value);
                }
            }
        }
        // $this->db->group_by('a.item_id');
        $this->db->order_by('a.created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalGenerateMrpFinals($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('*');
        $this->db->from('generate_mrp_finals');
        $this->db->where('approved_to', $approved_to);
        // $this->db->where('created_by', $created_by);
        $this->db->where('approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalPurchaseOrders($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, b.name as supplier_name, d.name as maker_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('supplier_items c', 'c.supplier_id = b.id and a.item_id = c.item_id');
        $this->db->join('mst_maker d', 'c.maker_code = d.number');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "supplier_name") {
                    $this->db->like("b.name", $filter->value);
                } elseif ($filter->field == "maker_name") {
                    $this->db->like("d.name", $filter->value);
                } else {
                    $this->db->like("a." . $filter->field, $filter->value);
                }
            }
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.purchase_order_no', 'asc');
        $this->db->order_by('a.item_id', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalPurchaseRequests($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_requests a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "supplier_name") {
                    $this->db->like("b.name", $filter->value);
                } else {
                    $this->db->like("a." . $filter->field, $filter->value);
                }
            }
        }
        $this->db->order_by('a.purchase_request_no', 'asc');
        $this->db->order_by('a.part_no', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalSupplierItems($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*, b.name as supplier_name, c.name as maker_name, COALESCE(d.mpq, 0) AS mpq_bf, COALESCE(d.moq, 0) AS moq_bf, COALESCE(d.leadtime, 0) AS leadtime_bf');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('mst_maker c', 'a.maker_code = c.number');
        $this->db->join("(SELECT a.item_id, a.supplier_id, a.mpq, a.moq, a.leadtime 
            FROM supplier_items_log a 
            JOIN (SELECT MAX(id) AS id FROM supplier_items_log GROUP BY item_id, supplier_id) b ON a.id = b.id) d", 'a.item_id = d.item_id AND a.supplier_id = d.supplier_id', "LEFT");
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "supplier_name") {
                    $this->db->like("b.name", $filter->value);
                } elseif ($filter->field == "maker_name") {
                    $this->db->like("c.name", $filter->value);
                } else {
                    $this->db->like("a." . $filter->field, $filter->value);
                }
            }
        }
        // $this->db->group_by('a.id');
        $this->db->order_by('a.created_date', 'desc');
        // die($this->db->get_compiled_select());
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalSupplierItemPrices($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select(
            "a.quotation_number," .
                "a.quotation_date," .
                "a.item_id," .
                "b.name as supplier_name," .
                "c.name as maker_name," .
                "d.*," .
                "COALESCE(e.price, 0) AS price_last," .
                "CASE WHEN COALESCE(e.price, 0) = 0 THEN 0 ELSE (d.price - COALESCE(e.price, 0)) END AS balance_price," .
                "CASE " .
                "WHEN (CASE WHEN COALESCE(e.price, 0) = 0 THEN 0 ELSE (d.price - COALESCE(e.price, 0)) END) > 0 THEN 'Increase'" .
                "WHEN (CASE WHEN COALESCE(e.price, 0) = 0 THEN 0 ELSE (d.price - COALESCE(e.price, 0)) END) < 0 THEN 'Decrease'" .
                "ELSE 'New Price'" .
                "END AS status_price," .
                "e.valid_date AS valid_date_bf"
        );
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('mst_maker c', 'a.maker_code = c.number');
        $this->db->join('supplier_item_prices d', 'a.id = d.supplier_item_id');
        $this->db->join("(" .
            "select d.supplier_item_id, d.price, d.valid_date from supplier_item_prices d" .
            " left join supplier_item_prices d2 on d.supplier_item_id = d2.supplier_item_id and d.id < d2.id" .
            " group by d.id having count(d2.id) = 1" .
            ") e", 'a.id = e.supplier_item_id', "LEFT");
        $this->db->where('d.approved_to', $approved_to);
        $this->db->where('d.approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if ($filter->field == "supplier_name") {
                    $this->db->like("b.name", $filter->value);
                } elseif ($filter->field == "maker_name") {
                    $this->db->like("c.name", $filter->value);
                } elseif ($filter->field == "item_id") {
                    $this->db->like("a.item_id", $filter->value);
                } else {
                    $this->db->like("d." . $filter->field, $filter->value);
                }
            }
        }
        $this->db->order_by('a.created_date', 'desc');
        // die($this->db->get_compiled_select());
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalSimulationMps($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*');
        $this->db->from('simulation_mps a');
        $this->db->join('mst_item_raw b', "b.item_id = a.item_no");
        $this->db->join('mst_item_family c', 'b.pfm_id = c.number');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.balance < 0');
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like("a." . $filter->field, $filter->value);
            }
        }
        $this->db->order_by('c.number', 'asc');
        $this->db->order_by('b.item_id', 'asc');
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

    public function approvalForecastSupplier($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('a.*');
        $this->db->from('forecast_supplier a');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->where('a.share_order >', 0);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like("a." . $filter->field, $filter->value);
            }
        }
        $this->db->order_by('a.vendor_name', 'asc');
        $this->db->order_by('a.part_no', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function approvalTempVendorContract($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = (!empty($this->input->post('sort'))) ? $this->input->post('sort') : 'created_date';
        $order = (!empty($this->input->post('order'))) ? $this->input->post('order') : 'desc';

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $validate_suggestion = [
            "COALESCE(a.mpq, 0) = COALESCE(d.mpq, 0)",
            "COALESCE(a.moq, 0) = COALESCE(d.moq, 0)",
            "COALESCE(a.leadtime, 0) = COALESCE(d.leadtime, 0)",
            "COALESCE(TRIM(a.currency), '') = COALESCE(TRIM(d.currency), '')",
            "COALESCE(a.price, 0) = COALESCE(d.price_last, 0)"
        ];
        $mpq_status = "(CASE WHEN " . $validate_suggestion[0] . " THEN 'OK' ELSE 'CHECK' END)";
        $moq_status = "(CASE WHEN " . $validate_suggestion[1] . " THEN 'OK' ELSE 'CHECK' END)";
        $leadtime_status = "(CASE WHEN " . $validate_suggestion[2] . " THEN 'OK' ELSE 'CHECK' END)";
        $currency_status = "(CASE WHEN " . $validate_suggestion[3] . " THEN 'OK' ELSE 'CHECK' END)";
        $price_status = "(CASE WHEN " . $validate_suggestion[4] . " THEN 'OK' ELSE 'CHECK' END)";
        $suggestion = "(CASE WHEN " . (implode(" AND ", $validate_suggestion)) . " THEN 'OK' ELSE 'CHECK' END)";

        $wheres = ["a.approved_to" => $approved_to, "a.approved_by" => $approved_by, "a.status" => 0];
        $likes = [];
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $key = "a." . $filter->field;
                if ($filter->field == "supplier_name") {
                    $key = "b.name";
                } elseif ($filter->field == "maker_name") {
                    $key = "c.name";
                } elseif ($filter->field == "status_price") {
                    $key = "(CASE " .
                        "WHEN (CASE WHEN COALESCE(d.price_last, 0) = 0 THEN 0 ELSE (a.price - COALESCE(d.price_last, 0)) END) > 0 THEN 'Increase'" .
                        "WHEN (CASE WHEN COALESCE(d.price_last, 0) = 0 THEN 0 ELSE (a.price - COALESCE(d.price_last, 0)) END) < 0 THEN 'Decrease'" .
                        "ELSE 'New Price'" .
                        "END)";
                } elseif ($filter->field == "mpq_status") {
                    $key = $mpq_status;
                } elseif ($filter->field == "moq_status") {
                    $key = $moq_status;
                } elseif ($filter->field == "leadtime_status") {
                    $key = $leadtime_status;
                } elseif ($filter->field == "currency_status") {
                    $key = $currency_status;
                } elseif ($filter->field == "price_status") {
                    $key = $price_status;
                } elseif ($filter->field == "suggestion") {
                    $key = $suggestion;
                }
                $likes[$key] = $filter->value;
            }
        }
        // die(var_dump($likes));
        //Select Query
        $this->db->distinct()->select(
            "a.*," .
                "b.name as supplier_name," .
                "c.name as maker_name," .
                "f.name as uom_name," .
                "COALESCE(d.mpq, 0) AS mpq_bf," .
                "COALESCE(d.moq, 0) AS moq_bf," .
                "COALESCE(d.leadtime, 0) AS leadtime_bf," .
                "COALESCE(d.price_last, 0) AS price_last," .
                "d.currency AS currency_bf," .
                "d.price_date AS price_date," .
                "CASE WHEN COALESCE(d.price_last, 0) = 0 THEN 0 ELSE (a.price - COALESCE(d.price_last, 0)) END AS balance_price," .
                "CASE " .
                "WHEN (CASE WHEN COALESCE(d.price_last, 0) = 0 THEN 0 ELSE (a.price - COALESCE(d.price_last, 0)) END) > 0 THEN 'Increase'" .
                "WHEN (CASE WHEN COALESCE(d.price_last, 0) = 0 THEN 0 ELSE (a.price - COALESCE(d.price_last, 0)) END) < 0 THEN 'Decrease'" .
                "ELSE 'New Price'" .
                "END AS status_price," .
                "$mpq_status AS mpq_status," .
                "$moq_status AS moq_status," .
                "$leadtime_status AS leadtime_status," .
                "$currency_status AS currency_status," .
                "$price_status AS price_status," .
                "$suggestion AS suggestion"
        )
            ->from("temp_vendor_contract a")
            ->join("suppliers b", "a.supplier_id = b.id")
            ->join("mst_maker c", "a.maker_code = c.number")
            ->join("(" .
                "SELECT DISTINCT x.*, aa.price AS price_last, aa.valid_date AS price_date " .
                "FROM supplier_items x " .
                "JOIN (SELECT supplier_item_id, MAX(valid_date) AS max FROM supplier_item_prices GROUP BY supplier_item_id) y ON x.id = y.supplier_item_id " .
                "JOIN (SELECT supplier_item_id, valid_date, MAX(start_date) AS max FROM supplier_item_prices GROUP BY supplier_item_id, valid_date) z ON x.id = z.supplier_item_id AND y.max = z.valid_date " .
                "JOIN supplier_item_prices aa ON x.id = aa.supplier_item_id AND y.max = aa.valid_date AND z.max = aa.start_date" .
                ") d", "a.item_id = d.item_id AND a.supplier_id = d.supplier_id", "LEFT")
            ->join("mst_item_raw e", "e.item_id = a.item_id", "LEFT")
            ->join("uom f", "e.um_id = f.number", "LEFT")
            ->where($wheres)->like($likes)->order_by($sort, $order);
        // die($this->db->get_compiled_select());

        if (empty($this->input->get("excel"))) {
            //Total Data
            $totalRows = $this->db->count_all_results("", false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            $sheet = 0;
            $excel = $this->spreadsheet_lib->newSpreadsheet();
            $spreadsheet = $excel->getActiveSheet($sheet);
            $excel->getProperties()->setCreator('Pixelphy')
                ->setLastModifiedBy('Pixelphy')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('PT ASKARA TEHNO INDONESIA');

            $style_row = [
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_THIN],
                    'left' => ['borderStyle' => Border::BORDER_THIN],
                    'right' => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ];
            $style_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
            $style_right = ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']];
            $style_row_center = array_merge($style_row, $style_center);
            $style_row_right = array_merge($style_row, $style_right);

            $style_col = array_merge($style_row, [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ]);

            // $drawing = new Drawing();
            // $drawing->setName('Logo');
            // $drawing->setDescription('Company Logo');
            // $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
            // $drawing->setWidth(50);
            // $drawing->setHeight(50);
            // $drawing->setCoordinates('A1');
            // $drawing->setWorksheet($spreadsheet);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $columns = [['No', 'Quotation No', 'Quotation Date', 'Plant', 'Vendor', 'Item No', 'Item Supplier', 'Maker', 'UOM', 'MPQ', '', '', 'MOQ', '', '', 'Leadtime', '', '', 'Currency', '', '', 'Last Price', '', 'New Price', '', 'Price Status', 'Suggestion', 'Balance Price', 'Price Status', 'Remarks'], ['', '', '', '', '', '', '', '', '', 'Before', 'After', 'Status', 'Before', 'After', 'Status', 'Before', 'After', 'Status', 'Before', 'After', 'Status', 'Valid Date', 'Price', 'Valid Date', 'Price', '', '', '', '', '']];
            $last_index = count($columns[0]) - 1;
            $title = 'APPROVAL VMS - PRICE QUOTATION';

            $merges = [];
            $row_no = 1;
            foreach ($columns as $index => $column) {
                $alfa = 'A';
                $bf_cell = '';
                foreach ($column as $key => $value) {
                    $spreadsheet->setCellValue($alfa . $row_no, $value);
                    $spreadsheet->getStyle($alfa . $row_no)->applyFromArray($style_col);

                    if ($index == 0) {
                        if (!empty($value)) {
                            $bf_cell = $alfa . $row_no;
                            $merges[$bf_cell] = '';
                        } else {
                            $merges[$bf_cell] = $alfa . $row_no;
                        }
                    } else {
                        if (empty($value)) {
                            $merges[$alfa . ($row_no - 1)] = $alfa . $row_no;
                        }
                    }

                    $alfa++;
                }
                $row_no++;
            }

            foreach ($merges as $key => $value) {
                if (!empty($value)) {
                    $spreadsheet->mergeCells($key . ':' . $value);
                }
            }

            $id = base64_decode($this->input->post('id'));
            $datas = [];
            foreach ($this->db->get()->result() as $index => $row) {
                $datas[] = (object) [
                    "quotation_number" => $row->quotation_number,
                    "quotation_date" => $row->quotation_date,
                    "plant" => $row->plant,
                    "supplier_name" => $row->supplier_name,
                    "item_id" => $row->item_id,
                    "item_supplier" => $row->item_supplier,
                    "maker_name" => $row->maker_name,
                    "uom_name" => $row->uom_name,
                    "mpq_bf" => $row->mpq_bf,
                    "mpq" => $row->mpq,
                    "mpq_status" => $row->mpq_status,
                    "moq_bf" => $row->moq_bf,
                    "moq" => $row->moq,
                    "moq_status" => $row->moq_status,
                    "leadtime_bf" => $row->leadtime_bf,
                    "leadtime" => $row->leadtime,
                    "leadtime_status" => $row->leadtime_status,
                    "currency_bf" => $row->currency_bf,
                    "currency" => $row->currency,
                    "currency_status" => $row->currency_status,
                    "price_date" => $row->price_date,
                    "price_last" => $row->price_last,
                    "valid_date" => $row->valid_date,
                    "price" => $row->price,
                    "price_status" => $row->price_status,
                    "suggestion" => $row->suggestion,
                    "balance_price" => $row->balance_price,
                    "status_price" => $row->status_price,
                    "remarks" => $row->remarks
                ];
            }

            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $spreadsheet->setCellValue($alfa . $row_no, $no);
                $spreadsheet->getStyle($alfa . $row_no)->applyFromArray($style_row_center);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row;
                    if (in_array($key, ['quotation_date', 'plant', 'price_status', 'suggestion', 'true', 'mpq_status', 'moq_status', 'leadtime_status', 'currency_bf', 'currency', 'currency_status', 'price_date', 'valid_date', 'status_price'])) {
                        $style = $style_row_center;
                    }
                    if (in_array($key, ['balance_price', 'mpq_bf', 'mpq', 'moq_bf', 'moq', 'leadtime_bf', 'leadtime', 'price_last', 'price'])) {
                        $style = $style_row_right;
                    }

                    if (in_array($key, ['balance_price', 'price_last', 'price'])) {
                        $style['font'] = ['bold' => true];
                        $value = rtrim(number_format($value, 10, '.', ','), '0');
                    }

                    if (in_array($key, ['mpq_status', 'moq_status', 'leadtime_status', 'currency_status', 'price_status', 'suggestion']) && $value == 'CHECK') {
                        $style['font'] = ['color' => ['rgb' => 'FF0000']];
                    }

                    if ($key == 'balance_price') {
                        $balance_price = floatval($value);
                    }
                    if (in_array($key, ['status_price'])) {
                        $style['fill'] = [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFFF00']
                        ];
                        if ($balance_price != 0) {
                            if ($balance_price > 0) {
                                $style['fill']['startColor'] = ['argb' => 'FFFF0000'];
                                $style['font'] = ['color' => ['rgb' => 'FFFFFF']];
                            } else {
                                $style['fill']['startColor'] = ['argb' => 'FF34EB46'];
                            }
                        }
                    }

                    if (in_array($key, ['item_id', 'item_supplier'])) {
                        $spreadsheet->setCellValueExplicit($alfa . $row_no, $value, DataType::TYPE_STRING);
                    } else {
                        $spreadsheet->setCellValue($alfa . $row_no, $value);
                    }
                    $spreadsheet->getStyle($alfa . $row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
            }

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $excel->setActiveSheetIndex(0);
            // die(json_encode($excel->getActiveSheet()->toArray()));

            if (ob_get_length()) {
                ob_end_clean();
            }

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $title . date(' (Y-m-d)') . '.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = IOFactory::createWriter($excel, 'Xlsx');
            $writer->save('php://output');
            exit;
        }
    }

    public function approvalNewPartPriceProcurements($approved_to, $created_by, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        $approved_by = base64_decode($approved_by);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = $this->input->post('sort');
        $order = $this->input->post('order');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        // Sort
        $sort   = isset($sort) ? strval($sort) : 'item_id';
        $order   = isset($order) ? strval($order) : 'ASC';
        if ($sort == 'item_id') { //pengecekan jika kolom alphanumeric contoh : 1, 1A, 1B
            $sort = 'CAST(' . $sort . ' as UNSIGNED) ' . $order;
        } else {
            $sort = $sort . ' ' . $order;
        }
        $result = array();
        //Select Query
        $this->db->select('*')->from('new_part_price_procurements')
            ->where(["approved_to" => $approved_to, "approved_by" => $approved_by])->order_by($sort);
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = [];
        foreach ($this->db->get()->result() as $index => $row) {
            // $row->status = 1;
            $records[] = $row;
        }
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }
}

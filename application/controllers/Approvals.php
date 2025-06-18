<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Approvals extends CI_Controller
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

    public function approveall()
    {
        $approved_to = $this->input->post('approved_to');
        $created_by = $this->input->post('created_by');
        $table_name = $this->input->post('table_name');

        $datas = $this->api->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

        foreach ($datas as $data) {
            $id = $data->id;
            $user = $this->api->read('user', [], ["username" => $data->created_by]);
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

            $send = $this->db->update($table_name, $values, ["id" => $id]);
        }

        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
    }

    public function approve()
    {
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

        $send = $this->db->update($tablename, $values, ["id" => $id]);
        if ($send) {
            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
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
                $send = $this->api->delete($table_name, ["id" => $id]);
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
        $read = $this->api->read($tablename, [], ["id" => $id]);
        $data = json_decode($read->approved_data, false);
        $merge = @array_merge((array) $read, array("disapprove" => $message));

        /* Default */
        if (empty($data)) {
            $send = $this->api->delete($tablename, ["id" => $id]);
        } else {
            $send = $this->db->update($tablename, $data, ["id" => $id]);
        }

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
        $balance_wip = $this->api->reads('balance_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $upload_gp = $this->api->reads('upload_gp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrp_finals = $this->api->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->api->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_order_npd = $this->api->reads('purchase_order_npd', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->api->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_items = $this->api->reads('supplier_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_item_prices = $this->api->reads('supplier_item_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $simulation_mps = $this->api->reads('simulation_mps', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $forecast_supplier = $this->api->reads('forecast_supplier', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (count($users) + count($generate_mrp_finals) + count($upload_gp) + count($balance_wip) + count($os_mpp) + count($os_vendor) + count($rm_stock) + count($mst_umh_havcp) + count($mst_umh) + count($mpp_os) + count($mst_wip) + count($mst_forecast) + count($fg_stock) + count($sales_order_os) + count($sales_order) + count($purchase_orders) + count($purchase_order_npd) + count($purchase_requests) + count($supplier_items) + count($supplier_item_prices) + count($simulation_mps) + count($forecast_supplier)); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
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
        $os_mpp = $this->api->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $balance_wip = $this->api->reads('balance_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $upload_gp = $this->api->reads('upload_gp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrp_finals = $this->api->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->api->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_order_npd = $this->api->reads('purchase_order_npd', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->api->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_items = $this->api->reads('supplier_items', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supplier_item_prices = $this->api->reads('supplier_item_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $simulation_mps = $this->api->reads('simulation_mps', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $forecast_supplier = $this->api->reads('forecast_supplier', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, $user->created_by, "user");
        }
        foreach ($supplier_items as $supplier_item) {
            $this->approvalMessage($supplier_item->approved_by, $supplier_item->approved_to, $supplier_item->created_by, "supplier_items");
        }
        foreach ($purchase_orders as $po) {
            $this->approvalMessage($po->approved_by, $po->approved_to, $po->created_by, "purchase_orders");
        }
        foreach ($purchase_order_npd as $npd) {
            $this->approvalMessage($npd->approved_by, $npd->approved_to, $npd->created_by, "purchase_order_npd");
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
    }

    public function approvalMessage($approved_by, $approved_to, $created_by, $table)
    {
        $user = $this->api->read('user', [], ["username" => $approved_by]);

        if (empty($user->avatar)) {
            $avatar = "default.png";
        } else {
            $avatar = $user->avatar;
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
                                    <b>' . $user->name . '</b><br>
                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $table)) . '</b></small>
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

    public function purchase_order_npd($approved_to, $created_by, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['created_by'] = base64_decode($created_by);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_order_npd";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_order_npd');
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

    public function approvalPurchaseOrderNpd($approved_to, $created_by, $approved_by)
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
        $this->db->from('purchase_order_npd a');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like("a." . $filter->field, $filter->value);
            }
        }
        $this->db->group_by('a.id');
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
        $this->db->select('a.*, b.name as supplier_name, c.name as maker_name');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('mst_maker c', 'a.maker_code = c.number');
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
        $this->db->select('a.item_id, b.name as supplier_name, c.name as maker_name, d.*');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('mst_maker c', 'a.maker_code = c.number');
        $this->db->join('supplier_item_prices d', 'a.id = d.supplier_item_id');
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
}

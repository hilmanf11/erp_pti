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
        $read = $this->api->read($tablename, [], ["id" => $id]);
        $data = json_decode($read->approved_data, false);

        /* Default */
        if(empty($data)){
            $send = $this->api->delete($tablename, ["id" => $id]);
        }else{
            $send = $this->db->update($tablename, $data, ["id" => $id]);
        }
        
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
        $purchase_requests = $this->api->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (count($users) + count($generate_mrp_finals) + count($upload_gp) + count($balance_wip) + count($os_mpp) + count($os_vendor) + count($rm_stock) + count($mst_umh_havcp) + count($mst_umh) + count($mpp_os) + count($mst_wip) + count($mst_forecast) + count($fg_stock) + count($sales_order_os) + count($sales_order) + count($purchase_orders) + count($purchase_requests)); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
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
        $purchase_requests = $this->api->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        
        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, $user->created_by, "users");
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
    }

    public function approvalMessage($approved_by, $approved_to, $created_by, $table)
    {
        $user = $this->api->read('user', [], ["username" => $approved_by]);

        if (empty($user->avatar)) {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $user->avatar;
        }

        $link = "approvalDetail('$table', '$approved_to', '$created_by')";
        echo '  <li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
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

    public function approvalUsers($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('user a');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSalesOrder($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('sales_order');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSalesOrderOs($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('sales_order_os');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalFgStock($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('fg_stock');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalForecast($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('mst_forecast');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalWip($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('mst_wip');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
    
    public function approvalMppOs($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('mpp_os');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalUmh($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('a.*, c.name as process_main_name, d.name as process_sub_name, e.name as process_name');
        $this->db->from('mst_umh a');
        $this->db->join('notification b', 'a.id = b.table_id');
        $this->db->join('mst_process_main c', 'a.process_main_number = c.number');
        $this->db->join('mst_process_sub d', 'a.process_sub_number = d.number');
        $this->db->join('mst_process e', 'a.process_number = e.number');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        //Get Data Array
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalUmhHavcp($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('a.*, c.name as process_main_name, d.name as process_sub_name, e.name as process_name');
        $this->db->from('mst_umh_havcp a');
        $this->db->join('notification b', 'a.id = b.table_id');
        $this->db->join('mst_process_main c', 'a.process_main_number = c.number');
        $this->db->join('mst_process_sub d', 'a.process_sub_number = d.number');
        $this->db->join('mst_process e', 'a.process_number = e.number');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        //Get Data Array
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalRmStock($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('rm_stock');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalOsVendor($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('os_vendor');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalOsMpp($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('os_mpp');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalBalanceWip($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('balance_wip');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalUploadGp($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('upload_gp');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalGenerateMrpFinals($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('generate_mrp_finals');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseOrders($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);

        $this->db->select('*');
        $this->db->from('purchase_orders');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseRequests($approved_to, $created_by)
    {
        $approved_to = base64_decode($approved_to);
        $created_by = base64_decode($created_by);
        
        $this->db->select('*');
        $this->db->from('purchase_requests');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}

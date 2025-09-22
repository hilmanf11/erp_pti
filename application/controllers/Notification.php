<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Notification extends CI_Controller
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

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->db->delete('notification', $data);
        echo $send;
    }

    public function notificationCount()
    {
        $totalRows = 0;
        $notificationUser = [];
        $menuUser = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-2021120004", "v_view" => 1]);
        if(!empty($menuUser)){
            $notificationUser = $this->api->reads('notification', [], ["table_name" => "user"], "", "", "", ["id_user", "table_name", "name"]);
        }

        $notificationPr = [];
        $menuPr = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202301000007", "v_view" => 1]);
        if(!empty($menuPr)){
            $notificationPr = $this->api->reads('notification', [], ["table_name" => "purchase_requests"], "", "", "", ["id_user", "table_name", "name"]);
        }

        $notificationPo = [];
        $menuPo = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202311000012", "v_view" => 1]);
        if(!empty($menuPo)){
            $notificationPo = $this->api->reads('notification', [], ["table_name" => "purchase_orders"], "", "", "", ["id_user", "table_name", "name"]);
        }

        $notificationPrice = [];
        $totalNotificationPrice = 0;
        $menuPrice = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202403000002", "v_view" => 1]);
        if(!empty($menuPrice)){
            $trans_date = date("Y-m-d", strtotime("+7 days"));
            $notificationPrice = $this->api->query("SELECT * FROM supplier_item_prices WHERE valid_date <= '$trans_date'");
            if(count($notificationPrice) > 0){
                $totalNotificationPrice = 1;
            }
        }
        
        $notificationDelivery = [];
        $totalNotificationDelivery = 0;
        $menuDelivery = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202301000011", "v_view" => 1]);
        if(!empty($menuDelivery)){
            $trans_date_1 = date("Y-m-d");
            $trans_date_2 = date("Y-m-d", strtotime("+14 days"));
            $notificationDelivery = $this->api->query("SELECT id FROM delivery_schedules 
                WHERE etd_1 between '$trans_date_1' and '$trans_date_2' 
                or etd_2 between '$trans_date_1' and '$trans_date_2'
                or etd_3 between '$trans_date_1' and '$trans_date_2'
                or etd_4 between '$trans_date_1' and '$trans_date_2'
                or etd_5 between '$trans_date_1' and '$trans_date_2'
                or etd_6 between '$trans_date_1' and '$trans_date_2'");

            if(count($notificationDelivery) > 0){
                $totalNotificationDelivery = 1;
            }
        }

        $totalRows = (count($notificationUser) + count($notificationPr) + count($notificationPo) + $totalNotificationPrice + $totalNotificationDelivery);
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function notificationList()
    {
        //Users
        $menuUser = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-2021120004", "v_view" => 1]);
        if(!empty($menuUser)){
            $notificationUser = $this->api->reads('notification', [], ["table_name" => "user"], "", "", "", ["id_user", "table_name", "name"]);

            foreach ($notificationUser as $user) {
                $this->notificationMessage($user->id_user, $user->table_name, $user->description, $user->name);
            }
        }

        $menuPr = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202301000007", "v_view" => 1]);
        if(!empty($menuPr)){
            $notificationPr = $this->api->reads('notification', [], ["table_name" => "purchase_requests"], "", "", "", ["id_user", "table_name", "name"]);

            foreach ($notificationPr as $pr) {
                $this->notificationMessage($pr->id_user, $pr->table_name, $pr->description, $pr->name);
            }
        }

        $menuPo = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202311000012", "v_view" => 1]);
        if(!empty($menuPo)){
            $notificationPo = $this->api->reads('notification', [], ["table_name" => "purchase_orders"], "", "", "", ["id_user", "table_name", "name"]);

            foreach ($notificationPo as $po) {
                $this->notificationMessage($po->id_user, $po->table_name, $po->description, $po->name);
            }
        }

        $notificationPrice = [];
        $menuPrice = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202403000002", "v_view" => 1]);
        if(!empty($menuPrice)){
            $trans_date = date("Y-m-d", strtotime("+7 days"));
            $notificationPrice = $this->api->query("SELECT * FROM supplier_item_prices WHERE valid_date <= '$trans_date'");
            if(count($notificationPrice) > 0){
                $link = "notificationDetailPrice()";
                echo '  <li class="list-isi">
                            <a onclick="' . $link . '">
                                <table style="width: 100%;">
                                    <tr>
                                        <td width="30">
                                            <div class="icon-container">
                                                <img src="assets/image/users/default.png" class="user-online" />
                                                <div class="status-circle"></div>
                                            </div>
                                        </td>
                                        <td style="padding-left: 10px;">
                                            <b>System Automatic</b><br>
                                            <small>There are several prices to expired in <b>VENDOR CONTRACT</b></small>
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </li>';
            }
        }

        $notificationDelivery = [];
        $menuDelivery = $this->api->read("user_setting", [], ["id_user" => $this->session->username, "id_menu" => "MNU-202301000011", "v_view" => 1]);
        if(!empty($menuDelivery)){
            $trans_date_1 = date("Y-m-d");
            $trans_date_2 = date("Y-m-d", strtotime("+14 days"));
            
            $notificationDelivery = $this->api->query("SELECT id FROM delivery_schedules 
                WHERE etd_1 between '$trans_date_1' and '$trans_date_2' 
                or etd_2 between '$trans_date_1' and '$trans_date_2'
                or etd_3 between '$trans_date_1' and '$trans_date_2'
                or etd_4 between '$trans_date_1' and '$trans_date_2'
                or etd_5 between '$trans_date_1' and '$trans_date_2'
                or etd_6 between '$trans_date_1' and '$trans_date_2'");

            if(count($notificationDelivery) > 0){
                $link = "notificationDelivery()";
                echo '  <li class="list-isi">
                            <a onclick="' . $link . '">
                                <table style="width: 100%;">
                                    <tr>
                                        <td width="30">
                                            <div class="icon-container">
                                                <img src="assets/image/users/default.png" class="user-online" />
                                                <div class="status-circle"></div>
                                            </div>
                                        </td>
                                        <td style="padding-left: 10px;">
                                            <b>System Automatic</b><br>
                                            <small>There are several delivery to expired in <b>SCHEDULE DELIVERY IMPORT</b></small>
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        </li>';
            }
        }
    }

    public function notificationMessage($id_user, $table, $description, $name)
    {
        $user = $this->api->read('user', [], ["username" => $id_user]);

        if (empty($user->avatar)) {
            $avatar = "default.png";
        } else {
            $avatar = $user->avatar;
        }

        $link = "notificationDetail('$id_user', '$table', '$name')";
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
                                    <b>' . @$user->name . '</b><br>
                                    <small>'.$description.'</small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function user($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "user";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/user');
        }
    }

    public function purchase_requests($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_requests";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/purchase_request');
        }
    }

    public function purchase_orders($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_orders";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/purchase_orders');
        }
    }

    public function supplier_item_prices(){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['table'] = "supplier_item_prices";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/supplier_item_prices');
        }
    }

    public function delivery_schedules(){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['table'] = "delivery_schedules";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/delivery_schedules');
        }
    }

    public function notificationData($table = "", $user = "", $name = "")
    {
        $user = base64_decode($user);
        $name = base64_decode($name);

        //Select Query
        $this->db->select('id, log, table_name');
        $this->db->from('notification');
        $this->db->where('table_name', $table);
        $this->db->where('id_user', $user);
        $this->db->where('name', $name);
        $this->db->order_by('created_date', 'desc');
        $totalRows = $this->db->count_all_results('', false);
        $records = $this->db->get()->result_array();

        $hasil = array();
        foreach ($records as $record) {
            $log = json_decode($record['log'], true);
            $id_notification = array("id_notification" => $record['id']);

            if($record['table_name'] == "purchase_orders"){
                $supplier = $this->api->read("suppliers", ["id" => $log['supplier_id']]);
                $hasil[] = ($id_notification + $log + array("supplier_name" => $supplier->name));
            }elseif($record['table_name'] == "purchase_requests"){
                $supplier = $this->api->read("suppliers", ["id" => @$log['supplier_id']]);
                $hasil[] = ($id_notification + $log + array("supplier_name" => $supplier->name));
            }else{
                $hasil[] = ($id_notification + $log);
            }
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $hasil]);
        echo json_encode($result);
    }

    public function notificationPrice()
    {
        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $trans_date = date("Y-m-d", strtotime("+7 days"));

        //Select Query
        $this->db->select('a.item_id, b.name as supplier_name, c.name as maker_name, d.*');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('mst_maker c', 'a.maker_code = c.number');
        $this->db->join('supplier_item_prices d', 'a.id = d.supplier_item_id');
        $this->db->where('d.valid_date <=', $trans_date);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if($filter->field == "supplier_name"){
                    $this->db->like("b.name", $filter->value);
                }elseif($filter->field == "maker_name"){
                    $this->db->like("c.name", $filter->value);
                }elseif($filter->field == "item_id"){
                    $this->db->like("a.item_id", $filter->value);
                }else{
                    $this->db->like("d.".$filter->field, $filter->value);
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

    public function notificationDelivery()
    {
        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $trans_date_1 = date("Y-m-d");
        $trans_date_2 = date("Y-m-d", strtotime("+14 days"));

        //Select Query
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('delivery_schedules a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->group_start();
        $this->db->where("a.etd_1 between '$trans_date_1' and '$trans_date_2'");
        $this->db->or_where("a.etd_2 between '$trans_date_1' and '$trans_date_2'");
        $this->db->or_where("a.etd_3 between '$trans_date_1' and '$trans_date_2'");
        $this->db->or_where("a.etd_4 between '$trans_date_1' and '$trans_date_2'");
        $this->db->or_where("a.etd_5 between '$trans_date_1' and '$trans_date_2'");
        $this->db->or_where("a.etd_6 between '$trans_date_1' and '$trans_date_2'");
        $this->db->group_end();
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if($filter->field == "supplier_name"){
                    $this->db->like("b.name", $filter->value);
                }else{
                    $this->db->like("a.".$filter->field, $filter->value);
                }
            }
        }
        $this->db->order_by('a.po_no', 'ASC');
        $records = $this->db->get()->result_array();

        foreach ($records as $data) {
            if($data['delivey_date_1'] == "0000-00-00"){ $eta_1 = "-"; }else{ $eta_1 = $data['delivey_date_1']; }
            if($data['delivey_date_2'] == "0000-00-00"){ $eta_2 = "-"; }else{ $eta_2 = $data['delivey_date_2']; }
            if($data['delivey_date_3'] == "0000-00-00"){ $eta_3 = "-"; }else{ $eta_3 = $data['delivey_date_3']; }
            if($data['delivey_date_4'] == "0000-00-00"){ $eta_4 = "-"; }else{ $eta_4 = $data['delivey_date_4']; }
            if($data['delivey_date_5'] == "0000-00-00"){ $eta_5 = "-"; }else{ $eta_5 = $data['delivey_date_5']; }
            if($data['delivey_date_6'] == "0000-00-00"){ $eta_6 = "-"; }else{ $eta_6 = $data['delivey_date_6']; }
            if($data['etd_1'] == "0000-00-00"){ $etd_1 = "-"; }else{ $etd_1 = $data['etd_1']; }
            if($data['etd_2'] == "0000-00-00"){ $etd_2 = "-"; }else{ $etd_2 = $data['etd_2']; }
            if($data['etd_3'] == "0000-00-00"){ $etd_3 = "-"; }else{ $etd_3 = $data['etd_3']; }
            if($data['etd_4'] == "0000-00-00"){ $etd_4 = "-"; }else{ $etd_4 = $data['etd_4']; }
            if($data['etd_5'] == "0000-00-00"){ $etd_5 = "-"; }else{ $etd_5 = $data['etd_5']; }
            if($data['etd_6'] == "0000-00-00"){ $etd_6 = "-"; }else{ $etd_6 = $data['etd_6']; }
            
            if($data['delivey_qty_1'] > 0){
                $recordFinal[] = array(
                    "po_no" => $data['po_no'],
                    "supplier_name" => $data['supplier_name'],
                    "product_family" => $data['product_family'],
                    "part_no" => $data['part_no'],
                    "part_name" => $data['part_name'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "qty" => $data['qty'],
                    "delivey_qty" => $data['delivey_qty_1'],
                    "etd" => $etd_1,
                    "eta" => $eta_1,
                );
            }

            if($data['delivey_qty_2'] > 0){
                $recordFinal[] = array(
                    "po_no" => $data['po_no'],
                    "supplier_name" => $data['supplier_name'],
                    "product_family" => $data['product_family'],
                    "part_no" => $data['part_no'],
                    "part_name" => $data['part_name'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "qty" => $data['qty'],
                    "delivey_qty" => $data['delivey_qty_2'],
                    "etd" => $etd_2,
                    "eta" => $eta_2,
                );
            }

            if($data['delivey_qty_3'] > 0){
                $recordFinal[] = array(
                    "po_no" => $data['po_no'],
                    "supplier_name" => $data['supplier_name'],
                    "product_family" => $data['product_family'],
                    "part_no" => $data['part_no'],
                    "part_name" => $data['part_name'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "qty" => $data['qty'],
                    "delivey_qty" => $data['delivey_qty_3'],
                    "etd" => $etd_3,
                    "eta" => $eta_3,
                );
            }

            if($data['delivey_qty_4'] > 0){
                $recordFinal[] = array(
                    "po_no" => $data['po_no'],
                    "supplier_name" => $data['supplier_name'],
                    "product_family" => $data['product_family'],
                    "part_no" => $data['part_no'],
                    "part_name" => $data['part_name'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "qty" => $data['qty'],
                    "delivey_qty" => $data['delivey_qty_4'],
                    "etd" => $etd_4,
                    "eta" => $eta_4,
                );
            }

            if($data['delivey_qty_5'] > 0){
                $recordFinal[] = array(
                    "po_no" => $data['po_no'],
                    "supplier_name" => $data['supplier_name'],
                    "product_family" => $data['product_family'],
                    "part_no" => $data['part_no'],
                    "part_name" => $data['part_name'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "qty" => $data['qty'],
                    "delivey_qty" => $data['delivey_qty_5'],
                    "etd" => $etd_5,
                    "eta" => $eta_5,
                );
            }

            if($data['delivey_qty_6'] > 0){
                $recordFinal[] = array(
                    "po_no" => $data['po_no'],
                    "supplier_name" => $data['supplier_name'],
                    "product_family" => $data['product_family'],
                    "part_no" => $data['part_no'],
                    "part_name" => $data['part_name'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "qty" => $data['qty'],
                    "delivey_qty" => $data['delivey_qty_6'],
                    "etd" => $etd_6,
                    "eta" => $eta_6,
                );
            }
        }

        //Mapping Data
        $result['total'] = $recordFinal;
        $result = array_merge($result, ['rows' => $recordFinal]);
        echo json_encode($result);
    }
}

<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->load->model('emails');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function purchaseRequests($month = "", $year = "")
    {
        $period = $year."-".$month;
        $querys = $this->api->query("SELECT product_family as name, coalesce(SUM(purchase_request), 0) as total FROM purchase_requests 
        WHERE purchase_request_date LIKE '%$period%'
        GROUP BY product_family
        ORDER BY coalesce(SUM(purchase_request), 0) DESC");

        die(json_encode($querys));
    }

    public function purchaseOrders($month = "", $year = "")
    {
        $period = $year."-".$month;
        $querys = $this->api->query("SELECT product_family_name as name, coalesce(SUM(qty), 0) as total FROM purchase_orders 
        WHERE purchase_order_date LIKE '%$period%'
        GROUP BY product_family_name
        ORDER BY coalesce(SUM(qty), 0) DESC");

        die(json_encode($querys));
    }

    public function SupplierPurchaseOrders($month = "", $year = "")
    {
        $period = $year."-".$month;
        $querys = $this->api->query("SELECT a.number, a.name, coalesce(SUM(b.qty), 0) as total_qty, coalesce(SUM(b.total), 0) as total_sub FROM suppliers a 
        LEFT JOIN purchase_orders b ON b.supplier_id = a.id and b.purchase_order_date LIKE '%$period%'
        GROUP BY b.supplier_id
        ORDER BY coalesce(SUM(b.qty), 0) desc
        LIMIT 10");

        die(json_encode($querys));
    }
}

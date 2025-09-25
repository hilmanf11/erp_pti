<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_data extends CI_Controller
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
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readRevisions()
    {
        $arr = array(
            ["id" => "0", "name" => "Revision 0"],
            ["id" => "1", "name" => "Revision 1"],
            ["id" => "2", "name" => "Revision 2"],
            ["id" => "3", "name" => "Revision 3"],
            ["id" => "4", "name" => "Revision 4"],
            ["id" => "5", "name" => "Revision 5"],
        );

        echo json_encode($arr);
    }

    public function readItemsTerminal()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $items = $this->api->reads('mst_item', ["item_id" => $post], ["pfm_id" => "02"], "", "item_id", "asc", "item_id", "item_id, item_name");

        echo json_encode($items);
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get('customer_id');
        $items = $this->api->reads('mst_item', ["item_id" => $post, "id_customer" => $customer_id], ["pfm_id" => '06', "stscode_id" => "01"], "", "item_id", "asc", "", "item_id, item_name");

        $arr = array();
        foreach ($items as $item) {
            $arr[] = array(
                "item_id" => $item->item_id,
                "item_name" => $item->item_name
            );
        }
        echo json_encode($arr);
    }

    public function readCircuits()
    {
        $productNo = base64_decode(trim($this->input->get('productNo')));
        $url = file_get_contents("http://erp.banshu-ind.com/mrp/api/param.php?db=pagaden&action=getcct&assy_no=" . base64_encode($productNo));
        $items = json_decode($url, TRUE);

        foreach ($items['data'] as $item) {
            $data[] = array(
                "mstwos_circuitno" => $item['cct']
            );
        }
        // $items = $this->banshu->reads('wip_mst_wos', [], ["mstwos_assyno" => $productNo], "", "", "", "mstwos_circuitno", "mstwos_circuitno");
        echo json_encode($data);
    }

    public function readCircuitTotal()
    {
        $productNo = trim($this->input->post('productNo'));
        $url = file_get_contents("http://erp.banshu-ind.com/mrp/api/param.php?db=pagaden&action=getcct&assy_no=" . base64_encode($productNo));
        $items = json_decode($url, TRUE);
        $data[] = array("total_cct" => count($items['data']));
        echo json_encode($data);
    }

    public function readCustomers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customers = $this->api->reads('mst_customer', ["name" => "PT", "name" => $post], [], "", "name", "asc");
        echo json_encode($customers);
    }
    
    public function readProductFamily(){
        $query = $this->db->query("SELECT number as pfm_id, name as pfm_name FROM mst_item_family WHERE number != '06' ORDER BY number ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readUom(){
        $query = $this->db->query("SELECT number, name FROM uom ORDER BY number ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }
}

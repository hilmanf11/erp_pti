<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sync extends CI_Controller
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

            $this->load->view('template/header', $data);
            $this->load->view('admin/sync');
        } else {
            redirect('error_access');
        }
    }

    public function getItems()
    {
        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("trim(item_id) as item_id, item_name, item_minoq, item_muloq, item_manldtm, item_prcldtm, stscode_id, pfm_id, type_id, um_id, cust_id as id_customer, ven_id as id_supplier, lotqty as lot, st_sparepart");
        $this->pg->from("mst_item");
        $this->pg->where("stscode_id", "01");
        $this->pg->where("pfm_id", "06");
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createItems()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("mst_item", [], ["item_id" => $data['item_id']]);
        if (empty($read)) {
            $create = $this->api->create('mst_item', $data);
            echo json_encode($create);
        } else {
            $update = $this->api->update('mst_item', ["item_id" => $data['item_id']], $data);
            echo json_encode($update);
        }
    }

    public function getItemsRw()
    {
        error_reporting(0);
        
        $items = $this->api->reads('mst_item_raw');
        $rowItem = array();
        foreach ($items as $item) {
            $rowItem[] = $item->item_id;
        }

        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("trim(item_id) as item_id, item_name, item_minoq, item_muloq, item_manldtm, item_prcldtm, stscode_id, pfm_id, type_id, um_id, cust_id as id_customer, ven_id as id_supplier, lotqty as lot, st_sparepart");
        $this->pg->from("mst_item");
        $this->pg->where("stscode_id", "01");
        $this->pg->where("pfm_id !=", "06");
        $this->pg->where_not_in("trim(item_id)", $rowItem);
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createItemsRw()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("mst_item_raw", [], ["item_id" => $data['item_id']]);
        if (empty($read)) {
            $create = $this->api->create('mst_item_raw', $data);
            echo json_encode($create);
        } else {
            echo json_encode(array("title" => "Good Job", "message" => "Data Updated Update Successfully", "theme" => "success"));
        }
    }

    public function getLines()
    {
        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("TRIM(cd_line) as line_no, mpp_line as remarks, nm_line as line_description, nick_line as line_nick, nick_line as customer_nick");
        $this->pg->from("wip_mst_line");
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createLines()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("mst_line", [], ["line_no" => trim($data['line_no'])]);
        if (empty($read)) {
            $create = $this->api->create('mst_line', $data, "LINE", "LINE");
            echo json_encode($create);
        } else {
            $update = $this->api->update('mst_line', ["line_no" => trim($data['line_no'])], $data);
            echo json_encode($update);
        }
    }

    public function getCustomer()
    {
        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("cust_id, cust_name, cust_address, cust_nikname");
        $this->pg->from("r_customer");
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createCustomer()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("mst_customer", [], ["number" => $data['cust_id']]);
        if (empty($read)) {
            $create = $this->api->create('mst_customer', [
                "number" => $data['cust_id'],
                "name" => $data['cust_name'],
                "address" => $data['cust_address'],
                "nickname" => $data['cust_nikname']
            ], "CST", "CST");
            echo json_encode($create);
        } else {
            $update = $this->api->update('mst_customer', ["number" => $data['cust_id']], [
                "name" => $data['cust_name'],
                "address" => $data['cust_address'],
                "nickname" => $data['cust_nikname']
            ]);
            echo json_encode($update);
        }
    }

    public function getCircuitNo()
    {
        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("b.item_id, COUNT(a.mstwos_circuitno) as circuit_no");
        $this->pg->from("mst_item b");
        $this->pg->join("wip_mst_wos a", "a.mstwos_assyno = b.item_id", "left");
        $this->pg->where("b.stscode_id", "01");
        $this->pg->where("b.pfm_id", "06");
        $this->pg->group_by("b.item_id");
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function getItemLines()
    {
        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("b.item_id, b.cd_line_1");
        $this->pg->from("mst_item a");
        $this->pg->join("mst_item_line b", "a.item_id = b.item_id", "left");
        $this->pg->where("a.stscode_id", "01");
        $this->pg->where("a.pfm_id", "06");
        $this->pg->group_by("b.item_id");
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createCct()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("wip_mst_wos_cct", [], ["mstwos_assyno" => trim($data['item_id'])]);
        $item_id = trim($data['item_id']);

        $crimping = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'MCR000' and cycle_time > 0 GROUP BY circuit_no) a");

        $joint = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'JNT000' and cycle_time > 0 GROUP BY circuit_no) a");

        $semiautocutting = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'SAC000' and cycle_time > 0 GROUP BY circuit_no) a");

        $stripping = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'STR000' and cycle_time > 0 GROUP BY circuit_no) a");

        $hfsealer = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'HFS000' and cycle_time > 0 GROUP BY circuit_no) a");

        $jointTapping = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'JTP000' and cycle_time > 0 GROUP BY circuit_no) a");

        $weldingjoint = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'WJT000' and cycle_time > 0 GROUP BY circuit_no) a");

        $spotWelding = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'SWL000' and cycle_time > 0 GROUP BY circuit_no) a");

        $solder = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'SLD000' and cycle_time > 0 GROUP BY circuit_no) a");

        $jst = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'JST000' and cycle_time > 0 GROUP BY circuit_no) a");

        $twist = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'TWS000' and cycle_time > 0 GROUP BY circuit_no) a");

        $hotstamp = $this->api->query("SELECT count(a.circuit_no) as total_cct FROM (SELECT circuit_no, COUNT(circuit_no) as cct  FROM `mst_umh` WHERE product_no = '$item_id' and process_sub_number = 'HST000' and cycle_time > 0 GROUP BY circuit_no) a");

        if (empty($read->mstwos_assyno)) {
            $create = $this->db->insert('wip_mst_wos_cct', [
                "mstwos_assyno" => trim($data['item_id']),
                "circuit_no" => @$data['circuit_no'],
                "circuit_crimping" => @$crimping[0]->total_cct,
                "circuit_joint" => @$joint[0]->total_cct,
                "circuit_semiautocutting" => @$semiautocutting[0]->total_cct,
                "circuit_stripping" => @$stripping[0]->total_cct,
                "circuit_hfsealer" => @$hfsealer[0]->total_cct,
                "circuit_jointtapping" => @$jointTapping[0]->total_cct,
                "circuit_weldingjoint" => @$weldingjoint[0]->total_cct,
                "circuit_spotwelding" => @$spotWelding[0]->total_cct,
                "circuit_solder" => @$solder[0]->total_cct,
                "circuit_jst" => @$jst[0]->total_cct,
                "circuit_twist" => @$twist[0]->total_cct,
                "circuit_hotstamp" => @$hotstamp[0]->total_cct
            ]);

            if ($create) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Data Saved Failed", "theme" => "error"));
            }
        } else {
            $update = $this->db->update('wip_mst_wos_cct', [
                "circuit_no" => $data['circuit_no'], 
                "circuit_crimping" => @$crimping[0]->total_cct,
                "circuit_joint" => @$joint[0]->total_cct, 
                "circuit_semiautocutting" => @$semiautocutting[0]->total_cct,
                "circuit_stripping" => @$stripping[0]->total_cct,
                "circuit_hfsealer" => @$hfsealer[0]->total_cct,
                "circuit_jointtapping" => @$jointTapping[0]->total_cct,
                "circuit_weldingjoint" => @$weldingjoint[0]->total_cct,
                "circuit_spotwelding" => @$spotWelding[0]->total_cct,
                "circuit_solder" => @$solder[0]->total_cct,
                "circuit_jst" => @$jst[0]->total_cct,
                "circuit_twist" => @$twist[0]->total_cct,
                "circuit_hotstamp" => @$hotstamp[0]->total_cct], ["mstwos_assyno" => @$data['item_id'],
            ]);

            if ($update) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Data Updated Failed", "theme" => "error"));
            }
        }
    }

    public function createItemLines()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("mst_item_line", [], ["item_id" => trim($data['item_id'])]);
        if (empty($read->item_id)) {
            $create = $this->db->insert('mst_item_line', [
                "item_id" => trim($data['item_id']),
                "line_no" => @$data['cd_line_1']
            ]);

            if ($create) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Data Saved Failed", "theme" => "error"));
            }
        } else {
            $update = $this->db->update('mst_item_line', ["line_no" => $data['cd_line_1']], [
                "item_id" => @$data['item_id']
            ]);

            if ($update) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Data Updated Failed", "theme" => "error"));
            }
        }
    }

    public function getBom()
    {   
        $this->pg = $this->load->database('pg', TRUE);
        
        $this->pg->select("b.*");
        $this->pg->from("mst_bom b");
        $this->pg->join("mst_item a", "a.item_id = b.bom_par_item");
        $this->pg->where("a.stscode_id", "01");
        $this->pg->where("a.pfm_id", "06");
        // $this->pg->where_in("b.bom_par_item", ["30ZYE002A-601A","26011-2500C"]);
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        $this->db->empty_table("mst_bom");

        die(json_encode($result));
    }

    public function createBom()
    {
        $data = $this->input->post('data');
        $create = $this->db->insert('mst_bom', [
            "item_id" => trim($data['bom_par_item']),
            "component_id" => trim($data['bom_com_item']),
            "eff_date" => $data['bom_effdate'],
            "rout_id" => $data['bom_routid'],
            "scrap_weight" => $data['scrap_weight'],
            "qty" => $data['bom_qty_perassy'],
            "revision" => $data['bom_revision'],
            "created_by" => $this->session->username,
            "created_date" => date("Y-m-d"),
            "document_no" => $data['bom_docno'],
            "status_dt" => $data['status_dt'],
            "maker" => $data['bom_maker']
        ]);

        if ($create) {
            echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
        } else {
            echo json_encode(array("title" => "Failed", "message" => "Data Saved Failed", "theme" => "error"));
        }
    }

    public function getWos()
    {
        $this->pg = $this->load->database('pg', TRUE);
        $this->pg->select("mstwos_idwos, mstwos_assyno, mstwos_circuitno, kind, size, color, length");
        $this->pg->from("wip_mst_wos");
        $totalRows = $this->pg->count_all_results('', false);
        $records = $this->pg->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }

    public function createWos()
    {
        $data = $this->input->post('data');
        $read = $this->api->read("wip_mst_wos", [], ["mstwos_assyno" => trim($data['mstwos_assyno']), "mstwos_circuitno" => trim($data['mstwos_circuitno'])]);

        if (empty($read->mstwos_assyno)) {
            $create = $this->db->insert('wip_mst_wos', [
                "mstwos_idwos" => trim($data['mstwos_idwos']),
                "mstwos_assyno" => trim($data['mstwos_assyno']),
                "mstwos_circuitno" => $data['mstwos_circuitno'],
                "kind" => $data['kind'],
                "size" => $data['size'],
                "color" => $data['color'],
                "length" => $data['length']
            ]);

            if ($create) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            } else {
                echo json_encode(array("title" => "Failed", "message" => "Data Saved Failed", "theme" => "error"));
            }
        } else {
            // $update = $this->db->update('wip_mst_wos', [
            //     "kind" => $data['kind'],
            //     "size" => $data['size'],
            //     "color" => $data['color'],
            //     "length" => $data['length']
            // ], ["mstwos_assyno" => trim($data['mstwos_assyno']), "mstwos_circuitno" => trim($data['mstwos_circuitno'])]);

            // if ($update) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            // } else {
            //     echo json_encode(array("title" => "Failed", "message" => "Data Updated Failed", "theme" => "error"));
            // }
        }
    }
}

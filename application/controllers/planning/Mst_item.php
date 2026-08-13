<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_item extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');

        //Validasi Form
        $this->form_validation->set_rules('line_no', 'Line No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_item');
        } else {
            redirect('error_access');
        }
    }

    public function readFamily()
    {
        $send = $this->api->reads('mst_item_family', [], [], "", "number", "asc");
        echo json_encode($send);
    }

    public function readUom()
    {
        $send = $this->api->reads('uom', [], [], "", "number", "asc");
        echo json_encode($send);
    }

    public function reads($item_family = "")
    {
        $page = $this->input->post('page');
        $rows = $this->input->post('rows'); //output 10
        // var_dump("damn66666".$rows);
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 50;
        $offset = ($page - 1) * $rows;
        $result = array();
        // var_dump($page."jaga".$rows."haha".$offset."chck");
        // die();
        //Select Query
        $this->db->select('*');
        $this->db->from('mst_item_raw');
        $this->db->like('item_id', $post);
        $this->db->like('pfm_id', $item_family);
        $this->db->order_by('item_id', 'ASC');
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


    public function readsparams()
    {
        $page = $this->input->post('page') ?: $this->input->get('page'); // Supports both POST and GET
        $rows = $this->input->post('rows') ?: $this->input->get('rows');
        $post = $this->input->post('q') ?: ""; // For filtering item_id
        $item_family = $this->input->get('product_family'); // Get the item_family parameter

        // Pagination: Default to page 1 and 50 rows per page if not set
        $page = isset($page) && is_numeric($page) ? intval($page) : 1;
        $rows = isset($rows) && is_numeric($rows) ? intval($rows) : 50;
        $offset = ($page - 1) * $rows;

        // Query
        $this->db->select('*');
        $this->db->from('mst_item_raw');

        // Add filter for `item_family` if provided
        if (!empty($item_family)) {
            $this->db->like('item_name', $item_family);
        }

        // Order by item_id
        $this->db->order_by('item_id', 'ASC');

        // Count total rows (ignoring limit)
        $totalRows = $this->db->count_all_results('', false);

        // Apply pagination
        $this->db->limit($rows, $offset);

        // Fetch paginated results
        $records = $this->db->get()->result_array();

        // Format result
        $result = [
            'total' => $totalRows,
            'rows' => $records
        ];

        // Return as JSON
        echo json_encode($result);
    }



    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as created_name, c.name as updated_name, d.name as product_family, e.name as uom, coalesce(f.total_assy, 0) as total_assy');
            $this->db->from('mst_item_raw a');
            $this->db->join('user b', 'a.created_by = b.username', 'left');
            $this->db->join('user c', 'a.updated_by = c.username', 'left');
            $this->db->join('mst_item_family d', 'a.pfm_id = d.number', 'left');
            $this->db->join('uom e', 'a.um_id = e.number', 'left');
            $this->db->join("(select component_id, COUNT(id) as total_assy from mst_bom GROUP BY component_id) f", 'f.component_id = a.item_id', 'left');
            // $this->db->where('d.name !=', null);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "created_by") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "updated_by") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "product_family") {
                        $this->db->like("d.name", $filter->value);
                    } elseif ($filter->field == "uom") {
                        $this->db->like("e.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('d.number', 'ASC');
            $this->db->order_by('a.item_id', 'ASC');
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
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $send = $this->api->create('mst_item_raw', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->api->update('mst_item_raw', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_item_raw', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'item_id' => $data->val($i, 2),
                'item_gp' => $data->val($i, 3),
                'item_supplier' => $data->val($i, 4),
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Line No
            $item = $this->api->reads('mst_item_raw', [], ["item_id" => $data['item_id']]);
            if (count($item) > 0) {
                $send = $this->api->update('mst_item_raw', ["item_id" => $data['item_id']], $data);
                echo $send;
            } else {
                $send   = $this->api->create('mst_item_raw', $data, "LINE", "LINE");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_item_raw_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as created_name, c.name as updated_name, d.name as product_family, e.name as uom, coalesce(f.total_assy, 0) as total_assy');
        $this->db->from('mst_item_raw a');
        $this->db->join('user b', 'a.created_by = b.username', 'left');
        $this->db->join('user c', 'a.updated_by = c.username', 'left');
        $this->db->join('mst_item_family d', 'a.pfm_id = d.number', 'left');
        $this->db->join('uom e', 'a.um_id = e.number', 'left');
        $this->db->join("(select component_id, COUNT(id) as total_assy from mst_bom GROUP BY component_id) f", 'f.component_id = a.item_id', 'left');
        $this->db->order_by('d.number', 'ASC');
        $this->db->order_by('a.item_id', 'ASC');
        $records = $this->db->get()->result_array();

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
                            <small>MASTER ITEM RAW MATERIAL</small>
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
                <th>Item ID</th>
                <th>Item GP</th>
                <th>Item Supplier</th>
                <th>Item Name</th>
                <th>Product Family</th>
                <th>Unit of Measure</th>
                <th>Total Assy</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td class="str">' . $data['item_id'] . '</td>
                            <td class="str">' . $data['item_gp'] . '</td>
                            <td class="str">' . $data['item_supplier'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['total_assy'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

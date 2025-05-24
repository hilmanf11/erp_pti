<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Plan_capacity extends CI_Controller
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
        $this->form_validation->set_rules('item_no', 'Product No', 'required|min_length[5]|max_length[30]|is_unique[plan_capacity.item_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/plan_capacity');
        } else {
            redirect('error_access');
        }
    }

    public function reads($name = "")
    {
        $send = $this->api->query("SELECT DISTINCT `number`, name, description FROM plan_capacity");
        echo json_encode($send);
    }

    public function readPlant(){
        $send = $this->api->query("SELECT DISTINCT plant FROM mst_line");
        echo json_encode($send);
    }

    public function readLine($plant){
        $send = $this->api->query("SELECT DISTINCT line_no, line_description FROM mst_line WHERE plant = '$plant' ORDER BY line_no");
        echo json_encode($send);
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $line_no = $this->input->get("line_no");

        $send = $this->api->query("SELECT DISTINCT a.item_id, a.item_name FROM mst_item a JOIN mst_item_line b ON a.item_id = b.item_id 
        WHERE a.pfm_id = '06' and a.stscode_id = '01' and b.line_no = '$line_no' and a.item_id LIKE '%$post%'
        ORDER BY a.item_id asc");

        echo json_encode($send);
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
            $this->db->select('a.*, b.name as created_name, c.name as updated_name');
            $this->db->from('plan_capacity a');
            $this->db->join('user b', 'a.created_by = b.username', 'left');
            $this->db->join('user c', 'a.updated_by = c.username', 'left');
            $this->db->where('a.deleted_is', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "number") {
                        $this->db->like("a.number", $filter->value);
                    } elseif ($filter->field == "name") {
                        $this->db->like("a.name", $filter->value);
                    } elseif ($filter->field == "description") {
                        $this->db->like("a.description", $filter->value);
                    } elseif ($filter->field == "trans_date") {
                        $this->db->like("a.trans_date", $filter->value);
                    } elseif ($filter->field == "created_by") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "created_date") {
                        $this->db->like("a.created_date", $filter->value);
                    } elseif ($filter->field == "updated_by") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "updated_date") {
                        $this->db->like("a.updated_date", $filter->value);
                    }
                }
            }
            $this->db->order_by('a.item_no', 'ASC');
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
            $post = $this->input->post();
            
            $plan_capacity = $this->api->read("plan_capacity", [], ["line_no" => $post['line_no'], "item_id" => $post['item_id']]);
            if (!empty($plan_capacity)) {
                $send = $this->api->update('plan_capacity', ["line_no" => $post['line_no'], "item_id" => $post['item_id']], $post);
            } else {
                $send = $this->api->create('plan_capacity', $post, "PC", "PC");
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->api->update('plan_capacity', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('plan_capacity', $data);
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
                'plant' => $data->val($i, 2),
                'line_no' => $data->val($i, 3),
                'line_name' => $data->val($i, 4),
                'item_no' => $data->val($i, 5),
                'item_name' => $data->val($i, 6),
                'capacity' => $data->val($i, 7),
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
            $mainProcess = $this->api->reads('plan_capacity', [], ["number" => $data['number']]);

            if (count($mainProcess) > 0) {
                echo json_encode(array("title" => "Duplicated", "message" => "Main Process Code " . $data['number'] . " has been Duplicated", "theme" => "error"));
            } else {
                $send = $this->api->create('plan_capacity', $data, "PC", "PC");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=plan_capacity_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('plan_capacity');
        $this->db->where('deleted_is', 0);
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
                            <small>MASTER PLAN CAPACITY</small>
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
                <th>Plant</th>
                <th>Line No</th>
                <th>Line Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Capacity</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['line_no'] . '</td>
                            <td>' . $data['line_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['capacity'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

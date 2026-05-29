<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_line extends CI_Controller
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
        $this->form_validation->set_rules('line_no', 'Customer ID', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_line');
        } else {
            redirect('error_access');
        }
    }

    public function reads($act = '')
    {
        if (!empty($act)) {
            $this->db->distinct()->select("TRIM(number) AS number")
            ->from("user")->where("username", $this->session->username);
            $user_number = $this->db->get()->row()->number;

            $this->db->distinct()->select("TRIM(customer) AS customers")
            ->from($act)->where("user_id", $user_number);
            $customers = explode(",", $this->db->get()->row()->customers);
        }
        $this->db->distinct()->select("*")->from("mst_line");
        if (!empty($act)) { $this->db->where_in("line_no", $customers); }

        echo json_encode($this->db->order_by("remarks")->get()->result());
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
            $this->db->from('mst_line a');
            $this->db->join('user b', 'a.created_by = b.username', 'left');
            $this->db->join('user c', 'a.updated_by = c.username', 'left');
            $this->db->where('a.deleted_is', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "plant") {
                        $this->db->like("a.plant", $filter->value);
                    } elseif ($filter->field == "line_business") {
                        $this->db->like("a.line_business", $filter->value);
                    } elseif ($filter->field == "line_nick") {
                        $this->db->like("a.line_nick", $filter->value);
                    } elseif ($filter->field == "line_description") {
                        $this->db->like("a.line_description", $filter->value);
                    } elseif ($filter->field == "customer_nick") {
                        $this->db->like("a.customer_nick", $filter->value);
                    } elseif ($filter->field == "customer_code") {
                        $this->db->like("a.customer_code", $filter->value);
                    } elseif ($filter->field == "remarks") {
                        $this->db->like("a.remarks", $filter->value);
                    } elseif ($filter->field == "line_position") {
                        $this->db->like("a.line_position", $filter->value);
                    } elseif ($filter->field == "created_by") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "created_date") {
                        $this->db->like("a.created_date", $filter->value);
                    } elseif ($filter->field == "updated_by") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "updated_date") {
                        $this->db->like("a.updated_date", $filter->value);
                    } else {
                        $this->db->like($filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('plant', 'ASC');
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
                $post   = $this->input->post();

                //Configs
                $config = $this->api->read('config');
                $configArr = [
                    "plant" => $config->number,
                    "line_business" => $config->description
                ];

                $merge = array_merge($post, $configArr);

                $send   = $this->api->create('mst_line', $merge, "LINE", "mst_line");
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
            $send = $this->api->update('mst_line', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_line', $data);
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
                'line_business' => $data->val($i, 3),
                'line_nick' => $data->val($i, 4),
                'line_description' => $data->val($i, 5),
                'customer_nick' => $data->val($i, 6),
                'remarks' => $data->val($i, 7),
                'line_position' => $data->val($i, 8)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            //Configs
            $config = $this->api->read('config');
            $configArr = [
                "plant" => $config->number,
                "line_business" => $config->description
            ];

            $data = $this->input->post('data');
            $merge = array_merge($data, $configArr);

            //Cek Line No
            $line = $this->api->reads('mst_line', [], ["line_no" => $data['line_no']]);
            if (count($line) > 0) {
                echo json_encode(array("title" => "Duplicate", "message" => "Line No " . $data['line_no'] . " has been Duplicated", "theme" => "error"));
            } else {
                $send   = $this->api->create('mst_line', $merge, "LINE", "LINE");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_line_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('mst_line');
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
                            <small>MASTER LINE PRODUCTION</small>
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
                <th>Line Business</th>
                <th>Customer ID</th>
                <th>Line Name</th>
                <th>Customer Nick</th>
                <th>Customer Name</th>
                <th>Line Position</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['plant'] . '</td>
                            <td>' . $data['line_business'] . '</td>
                            <td>' . $data['line_nick'] . '</td>
                            <td>' . $data['line_description'] . '</td>
                            <td>' . $data['customer_nick'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . $data['line_position'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

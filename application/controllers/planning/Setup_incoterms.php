<?php
defined('BASEPATH') or exit('No direct script access allowed');
class setup_incoterms extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[30]');
    }
    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/setup_incoterms');
        } else {
            redirect('error_access');
        }
    }
    public function autonumber()
    {
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM setup_incoterms");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $autoID = sprintf("%04s", $urutan + 1);
        }

        echo  "SHP-" . $autoID;
    }

    public function reads($name = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->api->query("SELECT * FROM setup_incoterms WHERE number like '%$post%' or name like '%$post%'");
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
            $this->db->select('a.*, b.name as incoterm_name, c.name as country_name');
            $this->db->from('setup_incoterms a');
            $this->db->join('incoterms b', 'a.incoterm_number = b.number', 'left');
            $this->db->join('country_of_origins c', 'a.country_number = c.number', 'left');
            $this->db->where('a.deleted_is', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "incoterm_number") {
                        $this->db->like("b.number", $filter->value);
                    } elseif ($filter->field == "country_name") {
                        $this->db->like("c.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.number', 'ASC');
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
                $setup_incoterms = $this->api->read('setup_incoterms', [], ["incoterm_number" => $post['incoterm_number'], "country_number" => $post['country_number']]);
                if (!$setup_incoterms) {
                    $send   = $this->api->create('setup_incoterms', $post, "SHP", "setup_incoterms");
                    echo $send;
                } else {
                    show_error("Duplicate Data");
                }
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
            $setup_incoterms = $this->api->read('setup_incoterms', [], ["incoterm_number" => $post['incoterm_number'], "country_number" => $post['country_number']]);
            if (!empty(@$setup_incoterms->id)) {
                $send = $this->api->update('setup_incoterms', ["id" => $id], $post);
                echo $send;
            } else {
                $send   = $this->api->create('setup_incoterms', $post, "SHP", "setup_incoterms");
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('setup_incoterms', $data);
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
                'row_number' => trim($data->val($i, 1)),
                'incoterm_number' => $data->val($i, 2),
                'country_number' => $data->val($i, 3),
                'insurance' => $data->val($i, 4),
                'transportation' => $data->val($i, 5),
                'import_duty' => $data->val($i, 6),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');
            if (
                !empty($data['incoterm_number']) &&
                !empty($data['country_number']) &&
                $data['insurance'] != '' &&
                $data['transportation'] != '' &&
                $data['import_duty'] != ''
            ) {
                //Cek Process Number
                $incoterm = $this->api->read('incoterms', [], ["number" => $data['incoterm_number']]);
                $country_of_origin = $this->api->read('country_of_origins', [], ["number" => $data['country_number']]);
                $setup_incoterms = $this->api->read('setup_incoterms', [], ["incoterm_number" => $data['incoterm_number'], "country_number" => $data['country_number']]);

                $total_ddp = $data['insurance'] + $data['transportation'] + $data['import_duty'];
                $postFinal = array(
                    "incoterm_number" => $data['incoterm_number'],
                    "country_number" => $data['country_number'],
                    "insurance" => $data['insurance'],
                    "transportation" => $data['transportation'],
                    "import_duty" => $data['import_duty'],
                    "total_ddp" => $total_ddp
                );
                if (empty($incoterm->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Incoterm " . $data['incoterm_number'] . " Not Found", "theme" => "error"));
                } elseif (empty($country_of_origin->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Country of Origin " . $data['country_number'] . " Not Found", "theme" => "error"));
                } elseif (!empty($setup_incoterms->number)) {
                    $send = $this->api->update('setup_incoterms', ["id" => $setup_incoterms->id], $postFinal);
                    echo $send;
                } else {
                    $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM setup_incoterms");
                    $rowID      = $sqlGetID->row();
                    $kode       = $rowID->kode;

                    if ($kode == NULL) {
                        $autoID = sprintf("%04s", $kode + 1);
                    } else {
                        $urutan = (int) substr($kode, -4);
                        $autoID = sprintf("%04s", $urutan + 1);
                    }
                    $postFinal['number'] = "SHP-" . $autoID;
                    $send   = $this->api->create('setup_incoterms', $postFinal, "SHP", "setup_incoterms");
                    echo $send;
                }
            } else {
                echo json_encode(array("title" => "Found Empty", "message" => " Mandatory Data Is Empty in No " . $data['row_number'], "theme" => "error"));
            }
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=setup_incoterms_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.name as incoterm_name, c.name as country_name');
        $this->db->from('setup_incoterms a');
        $this->db->join('incoterms b', 'a.incoterm_number = b.number', 'left');
        $this->db->join('country_of_origins c', 'a.country_number = c.number', 'left');
        $this->db->where('a.deleted_is', 0);
        $this->db->order_by('a.number', 'ASC');
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
                            <small>MASTER SETUP INCOTERMS</small>
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
                <th>Shipping Code</th>
                <th>Incoterm</th>
                <th>Country Of Origin</th>
                <th>Insurance (%)</th>
                <th>Transportation (%)</th>
                <th>Import Duty (%)</th>
                <th>Total DDP (%)</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['incoterm_name'] . '</td>
                            <td>' . $data['country_name'] . '</td>
                            <td align="right">' . number_format($data['insurance'], 4) . '</td>
                            <td align="right">' . number_format($data['transportation'], 4) . '</td>
                            <td align="right">' . number_format($data['import_duty'], 4) . '</td>
                            <td align="right">' . number_format($data['total_ddp'], 4) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}

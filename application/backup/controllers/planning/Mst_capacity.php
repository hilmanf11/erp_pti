<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_capacity extends CI_Controller
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
        $this->form_validation->set_rules('manpower', 'Man Power', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_capacity');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $send = $this->api->reads('mst_capacity');
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_process_main = base64_decode($this->input->get('filter_process_main'));
            $filter_process_sub = base64_decode($this->input->get('filter_process_sub'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.name as process_main_name, b.name as process_sub_name, d.*');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_capacity d', 'b.number = d.process_sub_number');
            $this->db->where('d.deleted_is', 0);
            $this->db->like('a.number', $filter_process_main);
            $this->db->like('b.number', $filter_process_sub);
            $this->db->order_by('b.flag', 'asc');
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
                $read = $this->api->read("mst_capacity", [], ["process_sub_number" => $post['process_sub_number']]);
                if ($read) {
                    show_error("Duplicated");
                } else {
                    $send   = $this->api->create('mst_capacity', $post, "PC", "mst_capacity");
                    echo $send;
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
            $send = $this->api->update('mst_capacity', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('mst_capacity', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_capacity_$format.xls");
        }

        $filter_process_main = base64_decode($this->input->get('filter_process_main'));
        $filter_process_sub = base64_decode($this->input->get('filter_process_sub'));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.name as process_main_name, b.name as process_sub_name, c.name as process_name, d.*');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->join('mst_capacity d', 'c.number = d.process_number');
        $this->db->where('d.deleted_is', 0);
        $this->db->like('a.number', $filter_process_main);
        $this->db->like('b.number', $filter_process_sub);
        $this->db->order_by('b.flag', 'asc');
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
                            <small>MASTER PARAMETER CAPACITY</small>
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
                <th>Process Main</th>
                <th>Process Sub</th>
                <th>Man Power</th>
                <th>Efficiency (%)</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['process_main_name'] . '</td>
                            <td>' . $data['process_sub_name'] . '</td>
                            <td>' . $data['manpower'] . '</td>
                            <td>' . $data['efficiency'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

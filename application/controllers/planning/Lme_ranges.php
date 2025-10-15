<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LME_ranges extends CI_Controller
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
        $this->form_validation->set_rules('lme_start', 'LME Range', 'required|integer|greater_than_equal_to[1]|less_than_equal_to[99999]');
        $this->form_validation->set_rules('lme_end', 'LME Range', 'required|integer|greater_than_equal_to[1]|less_than_equal_to[99999]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/lme_ranges');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $lme_ranges = $this->api->reads('lme_ranges');
        echo json_encode($lme_ranges);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;

            $this->db->select(
                 "a.*,"
                ."CONCAT(a.lme_start,'-',a.lme_end) AS lme_range,"
                ."TRIM(b.name) AS c_by,"
                ."TRIM(c.name) AS u_by"
            )->from('lme_ranges a')
                ->join('user b', 'a.created_by = b.username', 'left')
                ->join('user c', 'a.updated_by = c.username', 'left')
            ->order_by('id');
            $records = $this->db->get()->result_array();

            echo json_encode(array('total' => count($records), 'rows' => array_splice($records, $offset, $rows)));
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $exists = $this->db->where('lme_start', $post['lme_start'])->where('lme_end', $post['lme_end'])->get('lme_ranges')->row();
                if ($exists) { show_error("LME Range already available"); }
                else {
                    $send = $this->api->create('lme_ranges', $post, "LME", "lme_ranges");
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

            $exists = $this->db
                ->where('lme_start', $post['lme_start'])
                ->where('lme_end', $post['lme_end'])
                ->where('id !=', $id)
            ->get('lme_ranges')->row();
            if ($exists) { show_error("LME Range already available"); }
            else {
                $send = $this->api->update('lme_ranges', ["id" => $id], $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('lme_ranges', $data);
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
            $start = intval($data->val($i, 1));
            $end = intval($data->val($i, 2));
            if ($start != 0 && $end != 0) {
                $datas[] = array(
                    'lme_start' => $start,
                    'lme_end' => $end,
                    'remarks' => $data->val($i, 3)
                );
            }
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $result = ['title' => '', 'message' => '', 'theme' => 'error'];
            $data = $this->input->post('data');
            $start = intval($data['lme_start']);
            $end = intval($data['lme_end']);

            $datas = $this->api->read('lme_ranges', [], [
                "lme_start" => $start,
                "lme_end" => $end
            ]);

            if ($start <= 0) {
                $result['title'] = 'Less';
                $result['message'] = "LME Range Start $start less than 1";
            } elseif ($end > 99999) {
                $result['title'] = 'More';
                $result['message'] = "LME Range End $end more than 99999";
            } elseif (!empty($datas)) {
                $result['title'] = 'Duplicated';
                $result['message'] = "LME Range $start-$end has been Duplicated";
            }

            if (!empty($result['title'])) { echo json_encode($result); }
            else {
                $send = $this->api->create('lme_ranges', $data, "LME", "LME");
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Y-m-d H:i:s");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=LME Range $format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
        $fullname = $this->db->get()->row()->name;

        $this->db->select('*');
        $this->db->from('lme_ranges');
        $this->db->order_by('id', 'asc');

        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#lme_ranges {border-collapse: collapse;width: 100%;font-size: 12px;}#lme_ranges td, #lme_ranges th {border: 1px solid #ddd;padding: 2px;}#lme_ranges tr:nth-child(even){background-color: #f2f2f2;}#lme_ranges tr:hover {background-color: #ddd;}#lme_ranges th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>LME Range</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $fullname . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="lme_ranges" border="1">
            <tr>
                <th width="20">No</th>
                <th>LME Range</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                <td>' . $no . '</td>
                <td>' . $data['lme_start'].'-'.$data['lme_end'] . '</td>
                <td>' . $data['remarks'] . '</td>
            </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mst_umh_all extends CI_Controller
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

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            //Process Main Number
            $this->db->select('COUNT(a.number) as total, a.number, a.name');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->where('a.deleted_is', 0);
            $this->db->group_by('a.number');
            $this->db->order_by('b.flag', 'asc');
            $processMainNumber = $this->db->get()->result_array();

            //Process Sub Number
            $this->db->select('COUNT(b.number) as total, b.number, b.name, a.name as main');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->where('a.deleted_is', 0);
            $this->db->group_by('b.number');
            $this->db->order_by('b.flag', 'asc');
            $processSubNumber = $this->db->get()->result_array();

            //Process Main Number
            $this->db->select('c.number, c.name, a.name as main');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->where('a.deleted_is', 0);
            $this->db->group_by('c.number');
            $this->db->order_by('b.flag', 'asc');
            $processNumber = $this->db->get()->result_array();

            $no = 1;
            foreach ($processNumber as $process) {
                $arrProcess[] = array(
                    "main" => $process['main'],
                    "number" => $process['number'],
                    "name" => $process['name']
                );
                $no++;
            }

            $data['mainProcess'] = @$processMainNumber;
            $data['subProcess'] = @$processSubNumber;
            $data['process'] = !empty($arrProcess) ? $arrProcess : [];

            $this->load->view('template/header', $data);
            $this->load->view('planning/mst_umh_all');
        } else {
            redirect('error_access');
        }
    }

    public function datatables()
    {
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $id = isset($_POST['id']) ? $_POST['id'] : 0;
            $ex = explode("_", $id);
            $filter_line_no = base64_decode($this->input->get('filter_line_no'));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            if ($id == 0) {
                //Select Query
                $this->db->select('*, SUM(cycle_time) as total_umh');
                $this->db->from('mst_umh');
                $this->db->where('deleted_is', 0);
                $this->db->like('line_no', $filter_line_no);
                $this->db->like('product_no', $filter_product_no);
                $this->db->group_by('line_no');
                $this->db->order_by('line_no', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {
                    $line_no = $record['line_no'];
                    $line_name = $record['line_name'];
                    $product_no = $record['product_no'];

                    $this->db->select('*, SUM(cycle_time) as total_umh_havcp');
                    $this->db->from('mst_umh_havcp');
                    $this->db->where('deleted_is', 0);
                    $this->db->like('line_no', $filter_line_no);
                    $this->db->like('product_no', $filter_product_no);
                    $this->db->group_by('line_no');
                    $this->db->order_by('line_no', 'asc');
                    $havcp = $this->db->get()->row();

                    $arr = array(
                        "id" => "1" . "_" . $line_no,
                        "state" => "closed",
                        "line_no" => $line_no,
                        "line_name" => $line_name,
                        "circuit_no" => "-",
                        "total" => round($record['total_umh'] + @$havcp->total_umh_havcp, 3),
                        "total_umh" => round(($record['total_umh'] + @$havcp->total_umh_havcp) / 3600, 3),
                    );

                    //Process Number
                    $this->db->select('c.number, (SUM(coalesce(d.cycle_time,0))) as cycle_time');
                    $this->db->from('mst_process_main a');
                    $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                    $this->db->join('mst_process c', 'b.number = c.process_sub_number');
                    $this->db->join('mst_umh d', 'c.number = d.process_number');
                    $this->db->where('d.deleted_is', 0);
                    $this->db->where('d.line_no', $line_no);
                    $this->db->like('d.product_no', $filter_product_no);
                    $this->db->group_by('c.number');
                    $this->db->order_by('b.flag', 'asc');
                    $processNumber = $this->db->get()->result_array();

                    $this->db->select('c.number, (SUM(coalesce(d.cycle_time,0))) as cycle_time');
                    $this->db->from('mst_process_main a');
                    $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                    $this->db->join('mst_process c', 'b.number = c.process_sub_number');
                    $this->db->join('mst_umh_havcp d', 'c.number = d.process_number');
                    $this->db->where('d.deleted_is', 0);
                    $this->db->where('d.line_no', $line_no);
                    $this->db->like('d.product_no', $filter_product_no);
                    $this->db->group_by('c.number');
                    $this->db->order_by('b.flag', 'asc');
                    $processNumber2 = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($processNumber as $process) {
                        $arrProcess += array(
                            $process['number'] => $process['cycle_time']
                        );
                    }

                    $arrProcess2 = array();
                    foreach ($processNumber2 as $process2) {
                        $arrProcess2 += array(
                            $process2['number'] => $process2['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge(array_merge($arr, $arrProcess), $arrProcess2);
                }
            } else {
                $explode = explode("_", $id);
                //Select Query
                $this->db->select('*, SUM(cycle_time) as total_umh');
                $this->db->from('mst_umh');
                $this->db->where('deleted_is', 0);
                $this->db->where('line_no', $explode[1]);
                $this->db->like('product_no', $filter_product_no);
                $this->db->group_by('product_no');
                $this->db->order_by('product_no', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {
                    $line_no = $record['line_no'];
                    $product_no = $record['product_no'];

                    $this->db->select('*, SUM(cycle_time) as total_umh_havcp');
                    $this->db->from('mst_umh_havcp');
                    $this->db->where('deleted_is', 0);
                    $this->db->where('line_no', $line_no);
                    $this->db->where('product_no', $product_no);
                    $this->db->group_by('product_no');
                    $this->db->order_by('product_no', 'asc');
                    $havcp = $this->db->get()->row();

                    $arr = array(
                        "line_no" => $product_no,
                        "line_name" => $product_no,
                        "product_no" => $product_no,
                        "total" => round($record['total_umh'] + @$havcp->total_umh_havcp, 3),
                        "total_umh" => round(($record['total_umh'] + @$havcp->total_umh_havcp) / 3600, 3),
                    );

                    //Process Number
                    $this->db->select('c.number, (SUM(coalesce(d.cycle_time,0))) as cycle_time');
                    $this->db->from('mst_process_main a');
                    $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                    $this->db->join('mst_process c', 'b.number = c.process_sub_number');
                    $this->db->join('mst_umh d', 'c.number = d.process_number');
                    $this->db->where('d.deleted_is', 0);
                    $this->db->where('d.line_no', $line_no);
                    $this->db->where('d.product_no', $product_no);
                    $this->db->group_by('c.number');
                    $this->db->order_by('b.flag', 'asc');
                    $processNumber = $this->db->get()->result_array();

                    $this->db->select('c.number, (SUM(coalesce(d.cycle_time,0))) as cycle_time');
                    $this->db->from('mst_process_main a');
                    $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
                    $this->db->join('mst_process c', 'b.number = c.process_sub_number');
                    $this->db->join('mst_umh_havcp d', 'c.number = d.process_number');
                    $this->db->where('d.deleted_is', 0);
                    $this->db->where('d.line_no', $line_no);
                    $this->db->where('d.product_no', $product_no);
                    $this->db->group_by('c.number');
                    $this->db->order_by('b.flag', 'asc');
                    $processNumber2 = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($processNumber as $process) {
                        $arrProcess += array(
                            $process['number'] => $process['cycle_time']
                        );
                    }

                    $arrProcess2 = array();
                    foreach ($processNumber2 as $process2) {
                        $arrProcess2 += array(
                            $process2['number'] => $process2['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge(array_merge($arr, $arrProcess), $arrProcess2);
                }
            }

            //Mapping Data
            // $result['total'] = $totalRows;
            $result = !empty($arrFinal) ? $arrFinal : [];
            echo json_encode($result);
        }
    }

   public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=mst_umh_havcp_$format.xls");
        }

        $filter_line_no = base64_decode($this->input->get('filter_line_no'));
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Process Main Number
        $this->db->select('COUNT(a.number) as total, a.number, a.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->group_by('a.number');
        $this->db->order_by('b.flag', 'asc');
        $processMainNumber = $this->db->get()->result_array();

        //Process Sub Number
        $this->db->select('COUNT(b.number) as total, b.number, b.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->group_by('b.number');
        $this->db->order_by('b.flag', 'asc');
        $processSubNumber = $this->db->get()->result_array();

        //Process Main Number
        $this->db->select('c.number, c.name');
        $this->db->from('mst_process_main a');
        $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
        $this->db->join('mst_process c', 'b.number = c.process_sub_number');
        $this->db->where('a.deleted_is', 0);
        $this->db->group_by('c.number');
        $this->db->order_by('b.flag', 'asc');
        $processNumber = $this->db->get()->result_array();

        //Select Query
        $this->db->select('*, SUM(cycle_time) as total_umh');
        $this->db->from('mst_umh');
        $this->db->where('deleted_is', 0);
        $this->db->where('line_no', $filter_line_no);
        $this->db->like('product_no', $filter_product_no);
        $this->db->group_by('product_no');
        $this->db->order_by('product_no', 'asc');
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
                            <small>MASTER UNIT MAN HOUR (ALL)</small>
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
                <th rowspan="3" style="text-align:center;"width="20">No</th>
                <th rowspan="3" style="text-align:center;">Product No</th>';
        foreach ($processMainNumber as $main) {
            $html .= '<th style="text-align:center;" colspan="' . $main['total'] . '"> ' . $main['name'] . '</th>';
        }

        $html .= '<th style="text-align:center;" rowspan="3">Total HAVCP </th>
                </tr><tr>';

        foreach ($processSubNumber as $sub) {
            $html .= '<th style="text-align:center;" colspan="' . $sub['total'] . '"> ' . $sub['name'] . '</th>';
        }

        $html .= '</tr><tr>';

        foreach ($processNumber as $proces) {
            $html .= '<th style="text-align:center;"> ' . $proces['name'] . '</th>';
        }

        $html .= '</tr>';

        $no = 1;
        $arrFinal = array();
        foreach ($records as $record) {
            $line_no = $record['line_no'];
            $customer_id = $record['customer_id'];
            $customer_name = $record['customer_name'];
            $product_no = $record['product_no'];

            $this->db->select('id, created_by, created_date, updated_by, updated_date, approved, approved_to, approved_by, approved_date, approved_data, deleted_is, line_no, line_name, customer_id, customer_name, product_no, circuit_no, process_main_number, process_sub_number, process_number, status_is, SUM(cycle_time) as total_umh_havcp, COALESCE(remarks, "0") as remarks');
            $this->db->from('mst_umh_havcp');
            $this->db->where('deleted_is', 0);
            $this->db->where('line_no', $line_no);
            $this->db->where('product_no', $product_no);
            $this->db->group_by('product_no');
            $this->db->order_by('product_no', 'asc');
            $havcp = $this->db->get()->row();
            // var_dump("line_no: $line_no, customer_id: $customer_id, customer_name: $customer_name, product_no: $product_no");
            // var_dump("fell: " . $record['remarks']);
            if ($havcp === null) {
                $havcp = (object) ['total_umh_havcp' => 0]; // Default to 0 if no result
            }
            
            // var_dump("damn676: $havcp->total_umh_havcp");
            $arr = array(
                "line_no" => $line_no,
                "customer_id" => $customer_id,
                "customer_name" => $customer_name,
                "product_no" => $product_no,
                "remarks" => $record['remarks'],
                "total_umh" => round($record['total_umh'] + $havcp->total_umh_havcp, 3),
            );
            // var_dump("damn676xxxxxx");
            //Process Number
            $this->db->select('c.number, (SUM(coalesce(d.cycle_time,0))) as cycle_time');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->join('mst_umh d', 'c.number = d.process_number');
            $this->db->where('d.deleted_is', 0);
            $this->db->where('d.line_no', $line_no);
            $this->db->where('d.product_no', $product_no);
            $this->db->group_by('c.number');
            $this->db->order_by('b.flag', 'asc');
            $processNumber = $this->db->get()->result_array();

            $this->db->select('c.number, (SUM(coalesce(d.cycle_time,0))) as cycle_time');
            $this->db->from('mst_process_main a');
            $this->db->join('mst_process_sub b', 'a.number = b.process_main_number');
            $this->db->join('mst_process c', 'b.number = c.process_sub_number');
            $this->db->join('mst_umh_havcp d', 'c.number = d.process_number');
            $this->db->where('d.deleted_is', 0);
            $this->db->where('d.line_no', $line_no);
            $this->db->where('d.product_no', $product_no);
            $this->db->group_by('c.number');
            $this->db->order_by('b.flag', 'asc');
            $processNumber2 = $this->db->get()->result_array();
          
            $arrProcess = array();
            foreach ($processNumber as $process) {
                $arrProcess += array(
                    $process['number'] => $process['cycle_time']
                );
            }

            if (empty($processNumber2)) {
                // Array default yang ingin Anda gunakan
                $defaultNumbers = [
                    'ASM001', 'ASM002', 'ASM003', 'ASM004', 'ASM005', 
                    'ASM006', 'ASM007', 'ASM008', 'ASM009', 'ASM010','ASM011', 'ASM023', 
                    'CHK001', 'CHK002', 'CLP001', 'CLP002', 'CVL001', 
                    'HSG001', 'HSG002', 'HSG003', 'HSG004', 'HSG005', 
                    'HSG006', 'HSG007', 'HSG008','HSG009', 'HSG010', 'HSG011','PSC001',
                    'PSC002', 'PSC003', 'PSC004',
                    'PCK001', 'SAS001','SAS002',
                    'SAS003','SAS004','SET001','SET002','TBK001','TBK002','TET001','VIS001','VIS002'
                ];

                // Membuat array dengan cycle_time default
                foreach ($defaultNumbers as $number) {
                    $processNumber2[] = [
                        'number' => $number,
                        'cycle_time' => '0.000',
                    ];
                }
            }
            
            // Proses pengolahan data seperti biasa
            $arrProcess2 = [];
            foreach ($processNumber2 as $process2) {
                $arrProcess2 += [
                    $process2['number'] => $process2['cycle_time'],
                ];
            }
            
            $arrFinal[] = array_merge(array_merge($arr, $arrProcess), $arrProcess2);
        }

        foreach ($arrFinal as $final) {
            $html .= '  <tr>
                        <td style="text-align:center;">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $final['product_no'] . '</td>';
            foreach ($processNumber as $proses) {
                $number = $proses['number'];
                $html .= '<td style="text-align:center;">'. @$final["$number"] . '</td>';
            }

            if (!empty($processNumber2)) {
                foreach ($processNumber2 as $proses2) {
                    // var_dump('damn50505');
                    
                    $number = $proses2['number'];
                    $html .= '<td style="text-align:center;"> ' . @$final["$number"] . '</td>';
                }
            } else {
                // var_dump('processNumber2 is empty');
                $html .= '<td style="text-align:center;">' . @$final["$number"] . '</td>';
  
            }
            
            $html .= '  <td style="text-align:center;">' . $final['total_umh'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }   
}

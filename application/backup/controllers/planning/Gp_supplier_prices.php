<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gp_supplier_prices extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[fg_stock.product_no]');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/gp_supplier_prices');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('upload_gp', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function datatables()
    {
        $this->gp = $this->load->database('gp', TRUE);
        $filters = json_decode($this->input->post('filterRules'));
        $page   = $this->input->post('page');
        $rows   = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        $date_now = date('Y-m-d');

        //Select Query
        $this->gp->select("a.maker_part, 
            d.name as product_family_name, 
            c.code as supplier_code, 
            c.supplier_type,
            b.moq,
            b.mpq,
            c.shipping_lead_time_days,
            (CASE WHEN b.default_order = 'AI' THEN 'PT. ASKARA INTERNAL' ELSE c.name END) as supplier_name,
            e.name as maker,
            f.code as currency,
            b.buying_price,
            ((b.buying_price * COALESCE(g.ai_to_bei_margin, 0)) / 100) as bod_policy,
            ((CASE WHEN c.supplier_type = 'IMPORT' THEN 0.1 ELSE 0 END) + COALESCE(h.rate, 0)) as lcid,
            i.price_avg,
            ((b.buying_price + ((b.buying_price * COALESCE(g.ai_to_bei_margin, 0)) / 100) + ((CASE WHEN c.supplier_type = 'IMPORT' THEN 0.1 ELSE 0 END) + COALESCE(h.rate, 0))) * i.price_avg) as gpnol");
        $this->gp->from('database_parts a');
        $this->gp->join('database_prices b', 'a.id = b.database_part_id');
        $this->gp->join('suppliers c', 'b.supplier_id = c.id');
        $this->gp->join('commodities d', 'a.commodity_id = d.id');
        $this->gp->join('suppliers e', 'b.supplier_maker_id = e.id');
        $this->gp->join('currencies f', 'b.currency_id = f.id');
        $this->gp->join('bod_policies g', 'c.id = g.supplier_id', 'left');
        $this->gp->join('import_of_duties h', 'a.id = h.database_part_id and c.id = h.supplier_id', 'left');
        $this->gp->join("(SELECT *, ((sell + buy) / 2) as price_avg FROM exchange_rates WHERE `date` <= '$date_now' ORDER BY `date` DESC limit 25) i", "i.currency_code = f.code");
        $this->gp->where('a.deleted_by', null);
        //Filter Automatic
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                if($filter->field == "maker_part"){
                    $this->gp->like("a.maker_part", $filter->value);
                }elseif($filter->field == "product_family_name"){
                    $this->gp->like("d.name", $filter->value);
                }elseif($filter->field == "supplier_code"){
                    $this->gp->like("c.code", $filter->value);
                }elseif($filter->field == "supplier_name"){
                    $this->gp->like("c.name", $filter->value);
                }elseif($filter->field == "maker"){
                    $this->gp->like("e.name", $filter->value);
                }elseif($filter->field == "currency"){
                    $this->gp->like("f.code", $filter->value);
                }elseif($filter->field == "buying_price"){
                    $this->gp->like("b.buying_price", $filter->value);
                }elseif($filter->field == "buy"){
                    $this->gp->like("g.buy", $filter->value);
                }elseif($filter->field == "total_price"){
                    $this->gp->like("(b.buying_price * g.buy)", $filter->value);
                }
                
            }
        }
        $this->gp->group_by('a.id');
        $this->gp->group_by('c.id');
        $this->gp->order_by('c.name', 'ASC');
        $this->gp->order_by('a.maker_part', 'ASC');
        //Total Data
        $totalRows = $this->gp->count_all_results('', false);
        //Limit 1 - 10
        $this->gp->limit($rows, $offset);
        //Get Data Array
        $records = $this->gp->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function print($option = "")
    {
        // $this->gp = $this->load->database('gp', TRUE);
        // if ($option == "excel") {
        //     $format  = date("Ymd");
        //     header("Content-type: application/vnd-ms-excel");
        //     header("Content-Disposition: attachment; filename=supplier_gp_$format.xls");
        // }

        // //Config
        // $this->gp->select('*');
        // $this->gp->from('config');
        // $config = $this->gp->get()->row();

        // $this->gp->select('*');
        // $this->gp->from('suppliers');
        // $this->gp->where('deleted_by', null);
        // //Filter Automatic
        // if (@count($filters) > 0) {
        //     foreach ($filters as $filter) {
        //         $this->gp->like($filter->field, $filter->value);
        //     }
        // }
        // $this->gp->order_by('name', 'ASC');
        // $records = $this->gp->get()->result_array();

        // $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        // <center>
        //     <div style="float: left; font-size: 12px; text-align: left;">
        //         <table style="width: 100%;">
        //             <tr>
        //                 <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
        //                     <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
        //                 </td>
        //                 <td style="font-size: 14px; text-align: left; margin:2px;">
        //                     <b>' . $config->name . '</b><br>
        //                     <small>Master Supplier Global Purchase</small>
        //                 </td>
        //             </tr>
        //         </table>
        //     </div>
        //     <div style="float: right; font-size: 12px; text-align: right;">
        //         Print Date ' . date("d M Y H:m:s") . ' <br>
        //         Print By ' . $this->session->username . '  
        //     </div>
        // </center>
        // <br><br><br>
        // <table id="customers" border="1">
        //     <tr>
        //         <th width="20">No</th>
        //         <th>ID</th>
        //         <th>Code</th>
        //         <th>Name</th>
        //         <th>Ship Address</th>
        //         <th>Bill Address</th>
        //         <th>Origin</th>
        //         <th>Type</th>
        //         <th>Leadtime</th>
        //         <th>Payment Term</th>
        //         <th>Vat Status</th>
        //         <th>Tax Rate</th>
        //         <th>Email</th>
        //         <th>Contact Person</th>
        //     </tr>';
        // $no = 1;
        // foreach ($records as $data) {
        //     $html .= '  <tr>
        //                     <td>' . $no . '</td>
        //                     <td>' . $data['serial_code'] . '</td>
        //                     <td>' . $data['code'] . '</td>
        //                     <td>' . $data['name'] . '</td>
        //                     <td>' . $data['ship_address'] . '</td>
        //                     <td>' . $data['bill_address'] . '</td>
        //                     <td>' . $data['country_origin'] . '</td>
        //                     <td>' . $data['supplier_type'] . '</td>
        //                     <td>' . $data['shipping_lead_time_days'] . '</td>
        //                     <td>' . $data['payment_term_days'] . '</td>
        //                     <td>' . $data['vat_status'] . '</td>
        //                     <td>' . $data['tax_rates'] . '</td>
        //                     <td>' . $data['email'] . '</td>
        //                     <td>' . $data['contact_person'] . '</td>
        //                 </tr>';
        //     $no++;
        // }

        // $html .= '</table></body></html>';
        // echo $html;
    }
}

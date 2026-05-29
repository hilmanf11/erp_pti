<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Os_mpp extends CI_Controller
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
            $this->load->view('planning/os_mpp');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        if ($this->input->get()) {
            $number = $this->input->get('number');
            $processSub = $this->api->reads('os_mpp', [], ["number" => $number]);

            echo json_encode($processSub);
        }
    }

    public function readRevision(){
        $month = $this->input->get("month");
        $year = $this->input->get("year");

        $data = $this->api->query("SELECT DISTINCT revision FROM os_mpp WHERE p_month = '$month' and p_year='$year' ORDER BY revision ASC");
        echo json_encode($data);
    }

    public function readProductFamily(){
        $this->dummy = $this->load->database('dummy', TRUE);
        $query = $this->dummy->query("SELECT * FROM r_prodfam WHERE prod_group = 'Raw Material' and pfm_id != '06' ORDER BY pfm_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function readProducts($pfm_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->dummy = $this->load->database('pg', TRUE);
        $query = $this->dummy->query("SELECT * FROM mst_item WHERE pfm_id = '$pfm_id' and stscode_id = '01' and item_id LIKE '%$post%' ORDER BY item_id ASC");
        $records = $query->result_array();

        echo json_encode($records);
    }

    public function datatables()
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        if ($this->input->get()) {
            //Filter Data
            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_revision = base64_decode($this->input->get('filter_revision'));
            $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
            $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
            $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

            $filter_start = date("Y-m-d", strtotime($filter_cutoff . "+1 days"));
            $filter_end = date("Y-m-t", strtotime($filter_cutoff));
            $periode = $filter_year.$filter_month;

            // $qmpp = $this->pg->query("SELECT * FROM wip_trx_mpp WHERE wp_date BETWEEN '$filter_start' and '$filter_end'");
            // $dmpp = $qmpp->result_array();

            // $itemq = $this->dummy->query("SELECT b.bom_com_item, c.item_name, d.pfm_name, SUM(a.total * b.bom_qty_perassy) as total from
            //     (select assy_no, SUM(qty) as total from wip_trx_mpp WHERE wp_date between '$filter_start' and '$filter_end' GROUP BY assy_no ORDER BY SUM(qty) asc) a
            //     join mst_bom b on a.assy_no = b.bom_par_item
            //     join mst_item c on b.bom_com_item = c.item_id
            //     join r_prodfam d on c.pfm_id = d.pfm_id
            //     where d.pfm_id like '%$filter_product_family%' and b.bom_com_item like '%$filter_product_no%'
            //     group by b.bom_com_item, c.item_name, d.pfm_name
            //     order by d.pfm_name asc, SUM(a.total * b.bom_qty_perassy) desc
            // ");
            // $items = $itemq->result_array();

            $itemq = $this->dummy->query("SELECT trim(x.bom_com_item) as bom_com_item, c.item_name, d.pfm_name, SUM(x.os_wo) as total 
            FROM (
                    select a.assy_no, b.bom_com_item, a.prod_plan_1, coalesce(c.total, 0) as generate, (a.prod_plan_1 - coalesce(c.total, 0)) as ungenerate, b.bom_qty_perassy, ((a.prod_plan_1 - coalesce(c.total, 0)) * b.bom_qty_perassy) as os_wo
                    from ltpp_generate a
                    join mst_bom b on a.assy_no = b.bom_par_item
                    left join (select z.assy_no, SUM(z.total) as total 
                        from (
                            select a.serial_mpp, a.assy_no, SUM(a.qty) as total
                            from wip_trx_mpp a
                            join (SELECT serial_mpp, SUM(wds_qty) as qty FROM wip_trx_wds WHERE precut_sts = '1' GROUP BY serial_mpp) b ON a.serial_mpp = b.serial_mpp
                            where a.periode = '$periode' and a.wp_date between '$filter_start' and '$filter_end'
                            GROUP BY a.serial_mpp, a.assy_no
                            order by a.serial_mpp asc
                            ) z GROUP BY z.assy_no
                        ) c ON a.assy_no = c.assy_no
                    where a.period = '$periode' and a.prod_plan_1 > 0 and rev = (select max(rev) from ltpp_generate WHERE period = '$periode')
                ) x
            JOIN mst_item c on x.bom_com_item = c.item_id
            JOIN r_prodfam d on c.pfm_id = d.pfm_id
            GROUP BY x.bom_com_item, c.item_name, d.pfm_name
            HAVING SUM(x.os_wo) > 0
            ");
            $items = $itemq->result_array();

            $os_mpp = $this->api->reads('os_mpp', [], [
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            foreach ($os_mpp as $mpp) {
                $osVenId[$mpp->product_no] = $mpp->id;
                $osVenQty[$mpp->product_no] = $mpp->actual;
                $osVenCreatedby[$mpp->product_no] = $mpp->created_by;
                $osVenCreateddate[$mpp->product_no] = $mpp->created_date;
                $osVenUpdatedby[$mpp->product_no] = $mpp->updated_by;
                $osVenUpdateddate[$mpp->product_no] = $mpp->updated_date;
                $osVenStatus[$mpp->product_no] = $mpp->status;
            }

            $arr = array();
            foreach ($items as $item) {
                $item_id = trim($item['bom_com_item']);
                $item_name = $item['item_name'];
                $pfm_name = $item['pfm_name'];
                $qty = abs($item['total']);

                if (!empty($osVenId[$item_id])) {
                    if($osVenStatus[$item_id] == "EMPTY"){
                        $status = "GENERATE";
                    }else{
                        $status = $osVenStatus[$item_id];
                    }

                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $filter_cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($osVenQty[$item_id]),
                        "status" => $status,
                        "created_by" => $osVenCreatedby[$item_id],
                        "created_date" => $osVenCreateddate[$item_id],
                        "updated_by" => $osVenUpdatedby[$item_id],
                        "updated_date" => $osVenUpdateddate[$item_id],
                    );
                } else {
                    $arr[] = array(
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "cutoff" => $filter_cutoff,
                        "product_no" => $item_id,
                        "product_name" => $item_name,
                        "product_family" => $pfm_name,
                        "qty" => round($qty),
                        "actual" => round($qty),
                        "status" => "EMPTY",
                    );
                }
            }

            echo json_encode($arr);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $os_mpp = $this->api->reads('os_mpp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            if($post['product_no'] != ""){
                if (count($os_mpp) > 0) {
                    $send   = $this->api->update('os_mpp', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => trim($post['product_no'])
                    ], $post);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $send   = $this->api->create('os_mpp', $post, "RMS", "os_mpp");

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Saved Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Cannot Saved", "theme" => "error"));
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('os_mpp', $data);
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

        for ($i = 6; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $data->val(2, 3),
                'p_year' => $data->val(2, 4),
                'revision' => $data->val(3, 3),
                "cutoff" => $data->val(4, 3),
                'product_no' => $data->val($i, 2),
                'product_name' => $data->val($i, 3),
                'qty' => $data->val($i, 4),
                'status' => "UPLOAD"
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $p_month = $post['p_month'];
            $p_year = $post['p_year'];
            $revision = $post['revision'];
            $cutoff = $post['cutoff'];

            $os_mpp = $this->api->reads('os_mpp', [], [
                "p_month" => $post['p_month'],
                "p_year" => $post['p_year'],
                "revision" => $post['revision'],
                "product_no" => trim($post['product_no'])
            ]);

            $postFinal = array(
                "p_month" => $p_month,
                "p_year" => $p_year,
                "revision" => $revision,
                "product_no" => trim($post['product_no']),
                "product_name" => $post['product_name'],
                "product_family" => @$os_mpp[0]->product_family,
                "actual" => $post['qty'],
                "status" => $post['status']
            );

            if($post['product_no'] != ""){
                if (count($os_mpp) > 0) {
                    $send   = $this->api->update('os_mpp', [
                        "p_month" => $post['p_month'],
                        "p_year" => $post['p_year'],
                        "revision" => $post['revision'],
                        "product_no" => trim($post['product_no'])
                    ], $postFinal);

                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Updated Successfully", "theme" => "success"));
                } else {
                    $this->api->create('os_mpp', $postFinal, "RMS", "os_mpp");
                    echo json_encode(array("title" => "Good Job", "message" => "Product No " . $post['product_no'] . " Created Successfully", "theme" => "success"));
                }
            }else{
                echo json_encode(array("title" => "Error", "message" => "Product No " . $post['product_no'] . " Saved Failed", "theme" => "error"));
            }
        }
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/fg_stock.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/fg_stock.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/fg_stock.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function print($option = "")
    {
        $this->dummy = $this->load->database('dummy', TRUE);
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=os_wo_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_family = trim(base64_decode($this->input->get('filter_product_family')));
        $filter_product_no = trim(base64_decode($this->input->get('filter_product_no')));

        $filter_start = date("Y-m-d", strtotime($filter_cutoff . "+1 days"));
        $filter_end = date("Y-m-t", strtotime($filter_cutoff));
        $periode = $filter_year.$filter_month;

        $itemq = $this->dummy->query("SELECT trim(x.bom_com_item) as bom_com_item, c.item_name, d.pfm_name, SUM(x.os_wo) as total 
            FROM (
                    select a.assy_no, b.bom_com_item, a.prod_plan_1, coalesce(c.total, 0) as generate, (a.prod_plan_1 - coalesce(c.total, 0)) as ungenerate, b.bom_qty_perassy, ((a.prod_plan_1 - coalesce(c.total, 0)) * b.bom_qty_perassy) as os_wo
                    from ltpp_generate a
                    join mst_bom b on a.assy_no = b.bom_par_item
                    left join (select z.assy_no, SUM(z.total) as total 
                        from (
                            select a.serial_mpp, a.assy_no, SUM(a.qty) as total
                            from wip_trx_mpp a
                            join (SELECT serial_mpp, SUM(wds_qty) as qty FROM wip_trx_wds WHERE precut_sts = '1' GROUP BY serial_mpp) b ON a.serial_mpp = b.serial_mpp
                            where a.periode = '$periode' and a.wp_date between '$filter_start' and '$filter_end'
                            GROUP BY a.serial_mpp, a.assy_no
                            order by a.serial_mpp asc
                            ) z GROUP BY z.assy_no
                        ) c ON a.assy_no = c.assy_no
                    where a.period = '$periode' and a.prod_plan_1 > 0 and rev = (select max(rev) from ltpp_generate WHERE period = '$periode')
                ) x
            JOIN mst_item c on x.bom_com_item = c.item_id
            JOIN r_prodfam d on c.pfm_id = d.pfm_id
            GROUP BY x.bom_com_item, c.item_name, d.pfm_name
            HAVING SUM(x.os_wo) > 0
        ");
        $items = $itemq->result_array();
        $records = array();

        foreach ($items as $item) {
            $item_id = trim($item['bom_com_item']);
            $item_name = $item['item_name'];
            $pfm_name = $item['pfm_name'];
            $qty = abs($item['total']);

            $os_mpp = $this->api->read('os_mpp', [], [
                "product_no" => $item_id,
                "p_month" => $filter_month,
                "p_year" => $filter_year,
                "revision" => $filter_revision
            ]);

            if ($os_mpp) {
                if($os_mpp->status == "EMPTY"){
                    $status = "GENERATE";
                }else{
                    $status = $os_mpp->status;
                }

                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => @$os_mpp->actual,
                    "status" => $status,
                );
            } else {
                $records[] = array(
                    "p_month" => $filter_month,
                    "p_year" => $filter_year,
                    "revision" => $filter_revision,
                    "cutoff" => $filter_cutoff,
                    "product_no" => $item_id,
                    "product_name" => $item_name,
                    "product_family" => $pfm_name,
                    "qty" => $qty,
                    "actual" => $qty,
                    "status" => "EMPTY",
                );
            }
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <table style="font-size: 12px;">
            <tr>
                <td width="80">MONTH</td>
                <td width="10">:</td>
                <td>' . $filter_month . '<td>
            </tr>
            <tr>
                <td width="80">YEAR</td>
                <td width="10">:</td>
                <td>' . $filter_year . '<td>
            </tr>
            <tr>
                <td width="80">REVISION</td>
                <td width="10">:</td>
                <td>' . $filter_revision . '<td>
            </tr>
        </table>
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Product Family</th>
                <th>OS Workorder</th>
                <th>Actual</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['product_family'] . '</td>
                            <td>' . $data['qty'] . '</td>
                            <td>' . $data['actual'] . '</td>
                            <td>' . $data['status'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

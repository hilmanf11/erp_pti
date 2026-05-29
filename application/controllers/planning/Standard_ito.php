<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Standard_ito extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('spreadsheet_lib');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if (empty($this->session->username)) { redirect('error_session'); }
        elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['base_url'] = base_url('planning/standard_ito/');

            $this->load->view('template/header', $data);
            $this->load->view('planning/standard_ito');
        } else { redirect('error_access'); }
    }

    public function filterProdfam()
    {
        $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
        ->from("mst_item_family a")->join("standard_ito b", "a.number = b.pfm_id")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    public function filterMaker()
    {
        $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
        ->from("mst_maker a")->join("standard_ito b", "a.number = b.maker_number")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    public function datatables($act = '')
    {
        $prodfam = base64_decode($this->input->post("prodfam") ?? $this->input->get("prodfam"));
        $maker = base64_decode($this->input->post("maker") ?? $this->input->get("maker"));
        $where = [];

        if (!empty($prodfam)) { $where["a.pfm_id"] = $prodfam; }
        if (!empty($maker)) { $where["a.maker_number"] = $maker; }

        $this->db->select(
            "TRIM(a.id) AS id,".
            "TRIM(a.pfm_id) AS pfm_id,".
            "TRIM(a.maker_number) AS maker_number,".
            "COALESCE(TRIM(b.name), '') AS prodfam,".
            "COALESCE(TRIM(c.name), '') AS maker,".
            "COALESCE(a.std_min, 0) AS std_min,".
            "COALESCE(a.std_max, 0) AS std_max"
        )->from("standard_ito a")
            ->join("mst_item_family b", "a.pfm_id = b.number")
            ->join("mst_maker c", "a.maker_number = c.number")
        ->where($where)->order_by("prodfam, maker");
        // die($this->db->get_compiled_select());
        $datas = $this->db->get()->result();
        // die($this->db->last_query());

        if ($act == 'print') {
            $this->db->select("*")->from("config");
            $config = $this->db->get()->row();
            $logo = base_url('assets/image/config/' . $config->logo);

            $this->db->select("TRIM(name) AS name")->from("user")->where("username", $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $print_date = date("d M Y H:m:s");

            $html = <<<HTML
                <html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#datatables {border-collapse: collapse;width: 100%;font-size: 12px;}#datatables td, #datatables th {border: 1px solid #ddd;padding: 2px;}#datatables tr:nth-child(even){background-color: #f2f2f2;}#datatables tr:hover {background-color: #ddd;}#datatables th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="$logo" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>{$config->name}</b><br>
                                    <small></small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date $print_date<br>
                        Print By $fullname
                    </div>
                </center>
                <br><br><br>
                
                <table id="datatables" border="1">
                    <tr>
                        <th width="20" rowspan="2">No</th>
                        <th rowspan="2">Product Family</th>
                        <th rowspan="2">Maker Type</th>
                        <th colspan="2">Standard ITO</th>
                    </tr>
                    <tr>
                        <th>MIN</th>
                        <th>MAX</th>
                    </tr>
            HTML;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $html .= <<<HTML
                    <tr>
                        <td align="center">$no</td>
                        <td>{$row->prodfam}</td>
                        <td>{$row->maker}</td>
                        <td>{$row->std_min}</td>
                        <td>{$row->std_max}</td>
                    </tr>
                HTML;
            }

            echo $html.'</table></body></html>';
        } else { echo json_encode(array('total' => count($datas), 'rows' => $datas)); }
    }

    public function prodfam()
    {
        $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
        ->from("mst_item_family")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    public function maker()
    {
        $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
        ->from("mst_maker")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    public function excel()
    {
        if ($this->session->username != "") {
            $sheet = 0;
            $excel = $this->spreadsheet_lib->newSpreadsheet();
            $spreadsheet = $excel->getActiveSheet($sheet);
            $excel->getProperties()->setCreator('Pixelphy')
                ->setLastModifiedBy('Pixelphy')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('PT ASKARA TEHNO INDONESIA');

            $style_row = [
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                ]
            ];
            $style_row_center = array_merge($style_row, ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']]);
            $style_row_right = array_merge($style_row, ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']]);

            $style_col = array_merge($style_row, [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ]);

            // $drawing = new Drawing();
            // $drawing->setName('Logo');
            // $drawing->setDescription('Company Logo');
            // $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
            // $drawing->setWidth(50);
            // $drawing->setHeight(50);
            // $drawing->setCoordinates('A1');
            // $drawing->setWorksheet($spreadsheet);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $columns = [
                ['No', 'Product Family', 'Maker Type', 'Standard ITO',''],
                ['', '', '', 'MIN', 'MAX']
            ];
            $last_index = count($columns[0]) - 1;
            $title = 'Standard ITO';

            $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            foreach ($columns as $index => $arr) {
                foreach ($arr as $key => $value) {
                    if ($index == 1 && $key == $last_index) {
                        $spreadsheet->setCellValue('A1', $title);
                        $spreadsheet->mergeCells('A1:'.$alfa.'1');
                        $spreadsheet->getStyle('A1')->applyFromArray($style_row_center);
                        $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                        $spreadsheet->setCellValue($alfa.'3', 'Print Date : ' . date("d M Y H:m:s"));
                        $spreadsheet->setCellValue($alfa.'4', 'Print By : ' . $fullname);
                    }

                    $spreadsheet->setCellValue($alfa.'6', $value);
                    $spreadsheet->getStyle($alfa.'6')->applyFromArray($style_col);

                    $alfa++;
                }
            }

            $prodfam = base64_decode($this->input->get("prodfam"));
            $maker = base64_decode($this->input->get("maker"));
            $where = [];

            if (!empty($prodfam)) {
                $this->db->select('TRIM(name) AS name')->from('mst_item_family')->where('number', $prodfam);
                $filter_prodfam = $this->db->get()->row()->name;
                $where["a.pfm_id"] = $prodfam;
            }
            if (!empty($maker)) {
                $this->db->select('TRIM(name) AS name')->from('mst_maker')->where('number', $maker);
                $filter_maker = $this->db->get()->row()->name;
                $where["a.maker_number"] = $maker;
            }

            $spreadsheet->setCellValue('B3', $filter_prodfam);
            $spreadsheet->getStyle('B3')->applyFromArray($style_col);
            $spreadsheet->setCellValue('B4', $filter_maker);
            $spreadsheet->getStyle('B4')->applyFromArray($style_col);

            $this->db->select(
                "COALESCE(TRIM(b.name), '') AS prodfam,".
                "COALESCE(TRIM(c.name), '') AS maker,".
                "COALESCE(a.std_min, 0) AS std_min,".
                "COALESCE(a.std_max, 0) AS std_max"
            )->from("standard_ito a")
                ->join("mst_item_family b", "a.pfm_id = b.number")
                ->join("mst_maker c", "a.maker_number = c.number")
            ->where($where)->order_by("prodfam, maker");
            $datas = $this->db->get()->result();

            $row_no = 7;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row;
                    if (in_array($key, [''])) { $style = $style_row_center; }
                    if (in_array($key, ['std_min', 'std_max'])) { $style = $style_row_right; }

                    if (in_array($key, ['']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
            }

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $excel->setActiveSheetIndex(0);
            // die(json_encode($excel->getActiveSheet()->toArray()));

            if (ob_get_length()) { ob_end_clean(); }

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.date(' (Y-m-d)').'.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = IOFactory::createWriter($excel, 'Xlsx');
            $writer->save('php://output');
            exit;
        }
    }

    public function create()
    {
        $post = $this->input->post();
        $result = ['stat' => ''];
        $where = ["pfm_id" => base64_decode($post['prodfam']), "maker_number" => base64_decode($post['maker'])];
        $send_data = array_merge($where, ["std_min" => base64_decode($post['std_min']), "std_max" => base64_decode($post['std_max'])]);

        if ($this->db->where($where)->get('standard_ito')->num_rows() > 0) { $result['stat'] = 'Data is available!'; }
        else {
            $this->db->trans_start();
            $this->api->create('standard_ito', $send_data);
            $stat = $this->db->where($send_data)->get('standard_ito')->num_rows() > 0;

            if (!$stat) {
                $result['stat'] = $this->db->error()['message'];
                // $result['stat'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function update()
    {
        $post = $this->input->post();
        $result = ['stat' => ''];
        $send_data = ["std_min" => base64_decode($post['std_min']), "std_max" => base64_decode($post['std_max'])];
        $where = ["id" => base64_decode($post["id"])];

        $this->db->trans_start();
        $this->api->update('standard_ito', $where, $send_data);
        $stat = $this->db->where($where)->get('standard_ito')->num_rows() > 0;

        if (!$stat) {
            $result['stat'] = $this->db->error()['message'];
            // $result['stat'] = $this->db->last_query();
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function delete()
    {
        $result = ['stat' => ''];

        $post = $this->input->post();
        $where = ["id" => $post["id"]];

        $this->db->trans_start();
        $this->api->delete('standard_ito', $where);
        if ($this->db->where($where)->get('standard_ito')->num_rows() != 0) {
            $result['stat'] = $this->db->error()['message'];
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function uploadTemplate()
    {
        $result = ['stat' => 'ok', 'data' => []];

        try {
            $spreadsheet = IOFactory::load($_FILES['file_upload']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet(0)->toArray(null, true, true, true);
            $head_col = $sheetData[1];

            if ($head_col['A'] != '' || $head_col['B'] != '')
                { $result['stat'] = 'The template format does not match!'; }
            elseif ((count($sheetData) - 1) == 0)
                { $result['stat'] = 'No data!'; }
            else {
                unset($sheetData[1]);
                foreach ($sheetData as $key => $value) {
                    array_push($result['data'], [
                        '' => base64_encode($value['A']),
                        '' => base64_encode($value['B'])
                    ]);
                }
            }
        } catch (Exception $e) { echo json_encode(['stat' => $e->getMessage()]); }

        echo json_encode($result);
    }

    public function templateProcess()
    {
        $post = $this->input->post();
        $result = ["theme" => "success", "title" => "", "message" => ""];
        // $customer = base64_decode($post["customer"]);
        $user = base64_decode($post["user"]);

        if (empty(count($this->api->reads("user", [], ["number" => $user]))))
            { $result["message"] = "User ID $user not found"; }

        // if (empty($result["message"])) {
        //     $customers = explode(",", $customer);
        //     $numbers = [];

        //     $this->db->distinct()->select("TRIM(number) AS number")
        //     ->from("mst_item_family")->where_in("number", $customers)->order_by("number");
        //     foreach ($this->db->get()->result() as $index => $row) { array_push($numbers, $row->number); }

        //     $hasil = array_merge(array_diff($customers, $numbers), array_diff($numbers, $customers));
        //     if (!empty($hasil)) {
        //         $last_value = (count($hasil) > 1) ? " and ".array_pop($hasil) : "";
        //         $result["message"] = "This Customer ID ".implode(",", $hasil).$last_value." are not found";
        //     }
        // }

        if (empty($result["message"])) {
            $where = ["user_id" => $user];
            // if (empty(count($this->api->reads("", [], $where)))) {
            //     $send = $this->api->create("", ["user_id" => $user]);
            // } else {
            //     $send = $this->api->update("", [], $where);
            // }

            if (empty($send)) {
                $result = [
                    "theme" => "failed",
                    "title" => "Not Good",
                    "message" => "Data Saved unsuccessfully"
                ];
            } else { $result = json_decode($send); }
        } else { $result = array_merge($result, ["theme" => "failed", "title" => "Please Revise"]); }

        echo json_encode($result);
    }
}

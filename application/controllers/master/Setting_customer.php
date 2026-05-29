<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Setting_customer extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('master/setting_customer');
        } else { redirect('error_access'); }
    }

    public function users()
    {
        $this->db->distinct()->select("TRIM(a.number) AS number, TRIM(a.name) AS name")
        ->from("user a")->join("setting_customer b", "a.number = b.user_id", "LEFT")
        ->where("b.user_id IS NULL")->where("a.deleted_is", 0)->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    public function customer($user = '')
    {
        $this->db->distinct()->select("TRIM(customer) AS customer")
        ->from("setting_customer")->where("user_id", base64_decode($user));
        $get = $this->db->get()->row();
        $customers = (!empty($get)) ? explode(',', $get->customer) : [];
        // die(var_dump($customers));

        $this->db->distinct()->select("TRIM(line_no) AS number, TRIM(remarks) AS name")
        ->from("mst_line")->order_by("name");
        $data = [];
        foreach ($this->db->get()->result() as $key => $value) {
            if (in_array($value->number, $customers)) { $value->checked = true; }
            array_push($data, $value);
        }

        echo json_encode($data);
    }

    public function datatables($act = '')
    {
        $this->db->select(
            "TRIM(a.user_id) AS user, ".
            "COALESCE(TRIM(b.name), '') AS name, ".
            "GROUP_CONCAT(COALESCE(TRIM(c.remarks), '') ORDER BY c.remarks SEPARATOR '<br>') AS customer"
        )->from("setting_customer a")
            ->join("user b", "a.user_id = b.number", "LEFT")
            ->join("mst_line c", "FIND_IN_SET(TRIM(c.line_no), a.customer)", "LEFT")
        ->group_by("b.name")->order_by("name");
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
                        <th width="20">No</th>
                        <th>User Name</th>
                        <th>Customer</th>
                    </tr>
            HTML;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $html .= <<<HTML
                    <tr>
                        <td align="center">$no</td>
                        <td>{$row->name}</td>
                        <td>{$row->customer}</td>
                    </tr>
                HTML;
            }

            echo $html.'</table></body></html>';
        } else { echo json_encode(array('total' => count($datas), 'rows' => $datas)); }
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
            // $drawing->setPath(FCPATH.'assets/image/config/Group_2-removebg-preview_1671978002.png');
            // $drawing->setWidth(50);
            // $drawing->setHeight(50);
            // $drawing->setCoordinates('A1');
            // $drawing->setWorksheet($spreadsheet);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $columns = ['No', 'User Name', 'Customer'];
            $last_index = count($columns) - 1;
            $title = 'Setting Customer MPp';

            $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            foreach ($columns as $key => $value) {
                if ($key == $last_index) {
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

            $this->db->select(
                "COALESCE(TRIM(b.name), '') AS name, ".
                "GROUP_CONCAT(COALESCE(TRIM(c.remarks), '') ORDER BY c.remarks SEPARATOR '\n') AS customer"
            )->from("setting_customer a")
                ->join("user b", "a.user_id = b.number", "LEFT")
                ->join("mst_line c", "FIND_IN_SET(TRIM(c.line_no), a.customer)", "LEFT")
            ->group_by("b.name")->order_by("name");
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
                    if (in_array($key, [''])) { $style = $style_row_right; }

                    if (in_array($key, ['']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                    if (in_array($key, ['customer'])) { $spreadsheet->getStyle($alfa.$row_no)->getAlignment()->setWrapText(true); }
                }

                $row_no++;
            }

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $excel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.' ('.date('Y-m-d').').xls"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = IOFactory::createWriter($excel, 'Xls');
            $writer->save('php://output');
            exit;
        }
    }

    public function create()
    {
        $post = $this->input->post();
        $result = ['stat' => ''];
        $send_data = [
            'user_id' => base64_decode($post['user']),
            'customer' => base64_decode($post['customer'])
        ];

        $this->db->trans_start();
        $this->api->create('setting_customer', $send_data);
        $stat = $this->db->where($send_data)->get('setting_customer')->num_rows() > 0;

        if (!$stat) {
            $result['stat'] = $this->db->error()['message'];
            // $result['stat'] = $this->db->last_query();
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function update()
    {
        $post = $this->input->post();
        $result = ['stat' => ''];
        $send_data = ['customer' => base64_decode($post['customer'])];
        $where = ['user_id' => base64_decode($post['user'])];

        $this->db->trans_start();
        $this->api->update('setting_customer', $where, $send_data);
        $stat = $this->db->where($where)->get('setting_customer')->num_rows() > 0;

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

        $post = explode(',', base64_decode($this->input->post('user')));

        $this->db->trans_start();
        foreach ($post as $key => $value) {
            $this->api->delete('setting_customer', ['user_id' => $value]);
        }
        if ($this->db->where_in("user_id", $post)->get('setting_customer')->num_rows() != 0) {
            $result['stat'] = $this->db->error()['message'];
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function template()
    {
        $excel = $this->spreadsheet_lib->newSpreadsheet();
        $spreadsheet = $excel->getActiveSheet(0)->setTitle('Template');
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

        //SHEET 1 ------------------------------------------------------------------------------------------------------------
        $alfa = 'A';
        foreach (['User ID', 'Customer ID'] as $key => $value) {
            $spreadsheet->setCellValue($alfa.'1', $value);
            $spreadsheet->getStyle($alfa.'1')->applyFromArray($style_col);
            $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
            $comment = $spreadsheet->getComment($alfa.'1');
            $comment->getText()->createTextRun([
                "Enter the User ID in the User Data sheet",
                "Enter the Customer ID in the Customer Data sheet \n *note: if you select more than one, add a comma (example: 1,A)"
            ][$key]);
            $comment->setWidth(['200pt', '340pt'][$key]);
            $comment->setHeight(['20pt', '40pt'][$key]);
            // die(var_dump($spreadsheet->getComment($alfa.'1')->getText()));

            $alfa++;
        }

        //SHEET 2 ------------------------------------------------------------------------------------------------------------
        $spreadsheet2 = $excel->createSheet()->setTitle('Data User');
        $excel->setActiveSheetIndex(1);

        $alfa = 'A';
        foreach (['No', 'User ID', 'User Name'] as $key => $value) {
            $spreadsheet2->setCellValue($alfa.'1', $value);
            $spreadsheet2->getStyle($alfa.'1')->applyFromArray($style_col);

            $alfa++;
        }

        $this->db->distinct()->select("TRIM(a.number) AS number, TRIM(a.name) AS name")
        ->from("user a")->join("setting_customer b", "a.number = b.user_id", "LEFT")
        ->where("b.user_id IS NULL")->where("a.deleted_is", 0)->order_by("name");
        $datas = $this->db->get()->result();

        $row_no = 2;
        foreach ($datas as $index => $row) {
            $alfa = 'A';

            $spreadsheet2->setCellValue($alfa.$row_no, ($index + 1));
            $spreadsheet2->getStyle($alfa.$row_no)->applyFromArray($style_row_center);
            $spreadsheet2->getColumnDimension($alfa)->setAutoSize(true);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['number']))
                    { $spreadsheet2->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet2->setCellValue($alfa.$row_no, $value); }
                $spreadsheet2->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet2->getColumnDimension($alfa)->setAutoSize(true);
            }

            $row_no++;
        }

        //SHEET 3 ------------------------------------------------------------------------------------------------------------
        $spreadsheet2 = $excel->createSheet()->setTitle('Data Customer');
        $excel->setActiveSheetIndex(2);

        $alfa = 'A';
        foreach (['No', 'Customer ID', 'Customer Name'] as $key => $value) {
            $spreadsheet2->setCellValue($alfa.'1', $value);
            $spreadsheet2->getStyle($alfa.'1')->applyFromArray($style_col);

            $alfa++;
        }

        $this->db->distinct()->select("TRIM(line_no) AS number, TRIM(remarks) AS name")
        ->from("mst_line")->order_by("name");
        $datas = $this->db->get()->result();

        $row_no = 2;
        foreach ($datas as $index => $row) {
            $alfa = 'A';

            $spreadsheet2->setCellValue($alfa.$row_no, ($index + 1));
            $spreadsheet2->getStyle($alfa.$row_no)->applyFromArray($style_row_center);
            $spreadsheet2->getColumnDimension($alfa)->setAutoSize(true);

            foreach ($row as $key => $value) {
                $alfa++;

                $style = $style_row;
                if (in_array($key, [''])) { $style = $style_row_center; }
                if (in_array($key, [''])) { $style = $style_row_right; }

                if (in_array($key, ['number']))
                    { $spreadsheet2->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                else { $spreadsheet2->setCellValue($alfa.$row_no, $value); }
                $spreadsheet2->getStyle($alfa.$row_no)->applyFromArray($style);
                $spreadsheet2->getColumnDimension($alfa)->setAutoSize(true);
            }

            $row_no++;
        }

        $excel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template Setting Customer MPp ('.date('Y-m-d').').xlsx"');
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

    public function uploadTemplate()
    {
        $result = ['stat' => 'ok', 'data' => []];

        try {
            $spreadsheet = IOFactory::load($_FILES['file_upload']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet(0)->toArray(null, true, true, true);
            $head_col = $sheetData[1];

            if ($head_col['A'] != 'User ID' || $head_col['B'] != 'Customer ID')
                { $result['stat'] = 'The template format does not match!'; }
            elseif ((count($sheetData) - 1) == 0)
                { $result['stat'] = 'No data!'; }
            else {
                unset($sheetData[1]);
                foreach ($sheetData as $key => $value) {
                    array_push($result['data'], [
                        'user' => base64_encode($value['A']),
                        'customer' => base64_encode($value['B'])
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
        $customer = base64_decode($post["customer"]);
        $user = base64_decode($post["user"]);

        if (empty(count($this->api->reads("user", [], ["number" => $user]))))
            { $result["message"] = "User ID $user not found"; }

        if (empty($result["message"])) {
            $customers = explode(",", $customer);
            $numbers = [];

            $this->db->distinct()->select("TRIM(number) AS number")
            ->from("mst_item_family")->where_in("number", $customers)->order_by("number");
            foreach ($this->db->get()->result() as $index => $row) { array_push($numbers, $row->number); }

            $hasil = array_merge(array_diff($customers, $numbers), array_diff($numbers, $customers));
            if (!empty($hasil)) {
                $last_value = (count($hasil) > 1) ? " and ".array_pop($hasil) : "";
                $result["message"] = "This Customer ID ".implode(",", $hasil).$last_value." are not found";
            }
        }

        if (empty($result["message"])) {
            $where = ["user_id" => $user];
            if (empty(count($this->api->reads("setting_customer", [], $where)))) {
                $send = $this->api->create("setting_customer", ["customer" => $customer, "user_id" => $user]);
            } else {
                $send = $this->api->update("setting_customer", ["customer" => $customer], $where);
            }

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

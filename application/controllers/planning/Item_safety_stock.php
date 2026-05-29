<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Item_safety_stock extends CI_Controller
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
            $data['base_url'] = base_url('planning/item_safety_stock/');

            $this->load->view('template/header', $data);
            $this->load->view('planning/item_safety_stock');
        } else { redirect('error_access'); }
    }

    public function getItems()
    {
        $this->db->distinct()->select("TRIM(a.item_id) AS item")
        ->from("mst_item_raw a")->join("item_safety_stock b", "a.item_id = b.item_id", "LEFT")
        ->where(["a.item_id !=" => ""])->where("b.item_id IS NULL", NULL, FALSE)->order_by("item");
        echo json_encode($this->db->get()->result());
    }

    public function datatables($act = '')
    {
        $this->db->select("id, TRIM(item_id) AS item, safety_stock AS safety")
        ->from("item_safety_stock")->order_by("item");
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
                        <th style="text-align: center;" width="20">No</th>
                        <th style="text-align: center;">Item ID</th>
                        <th style="text-align: center;">Safety Stock</th>
                    </tr>
            HTML;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $html .= <<<HTML
                    <tr>
                        <td align="center">$no</td>
                        <td>{$row->item}</td>
                        <td align="center">{$row->safety}%</td>
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
            // $drawing->setPath(FCPATH.'assets/image/config/logo/1741863196.png');
            // $drawing->setWidth(50);
            // $drawing->setHeight(50);
            // $drawing->setCoordinates('A1');
            // $drawing->setWorksheet($spreadsheet);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $columns = ['No', 'Item ID', 'Safety Stock'];
            $last_index = count($columns) - 1;
            $title = 'Item Safety Stock';

            $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            foreach ($columns as $key => $value) {
                if ($key == ($last_index - 1)) {
                    $spreadsheet->setCellValue($alfa.'3', 'Print Date');
                    $spreadsheet->setCellValue($alfa.'4', 'Print By');
                } elseif ($key == $last_index) {
                    $spreadsheet->setCellValue('A1', $title);
                    $spreadsheet->mergeCells('A1:'.$alfa.'1');
                    $spreadsheet->getStyle('A1')->applyFromArray($style_row_center);
                    $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                    $spreadsheet->setCellValue($alfa.'3', ': ' . date("d M Y H:m:s"));
                    $spreadsheet->setCellValue($alfa.'4', ': ' . $fullname);
                }

                $spreadsheet->setCellValue($alfa.'6', $value);
                $spreadsheet->getStyle($alfa.'6')->applyFromArray($style_col);

                $alfa++;
            }

            $this->db->select("TRIM(item_id) AS item, safety_stock AS safety")
            ->from("item_safety_stock")->order_by("item");
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
                    if (in_array($key, ['safety'])) { $style = $style_row_center; }
                    if (in_array($key, [''])) { $style = $style_row_right; }

                    if ($key == 'safety') { $value = $value.'%'; }
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
        $send_data = [
            'item_id' => base64_decode($post['item']),
            'safety_stock' => base64_decode($post['safety'])
        ];

        $this->db->trans_start();
        $this->api->create('item_safety_stock', $send_data);
        $stat = $this->db->where($send_data)->get('item_safety_stock')->num_rows() > 0;

        if (!$stat) {
            $result['stat'] = 'Undefined';
            // $result['stat'] = $this->db->error()['message'];
            // $result['stat'] = $this->db->last_query();
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function update()
    {
        $post = $this->input->post();
        $result = ['stat' => ''];
        $send_data = [
            'item_id' => base64_decode($post['item']),
            'safety_stock' => base64_decode($post['safety'])
        ];
        $where = ['id' => base64_decode($post['id'])];

        $this->db->trans_start();
        $this->api->update('item_safety_stock', $where, $send_data);
        $stat = $this->db->where($send_data)->get('item_safety_stock')->num_rows() > 0;

        if (!$stat) {
            $result['stat'] = 'Undefined';
            // $result['stat'] = $this->db->error()['message'];
            // $result['stat'] = $this->db->last_query();
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function delete()
    {
        $result = ['stat' => ''];

        $post = $this->input->post();
        $where = ['id' => base64_decode($post['id'])];

        $this->db->trans_start();
        $this->api->delete('item_safety_stock', $where);
        if ($this->db->where($where)->get('item_safety_stock')->num_rows() != 0) {
            $result['stat'] = $this->db->error()['message'];
            $this->db->trans_rollback();
        } else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function template()
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
            $columns = ['Item ID', 'Safety Stock'];
            $last_index = count($columns) - 1;

            $alfa = 'A';
            foreach ($columns as $key => $value) {
                $spreadsheet->setCellValue($alfa.'1', $value);
                $spreadsheet->getStyle($alfa.'1')->applyFromArray($style_col);
                $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                if ($alfa == 'A') {
                    $comment = $spreadsheet->getComment($alfa.'1');
                    $comment->getText()->createTextRun("Enter the Item ID in the Data Item ID sheet");
                    $comment->setWidth('200pt');
                    $comment->setHeight('20pt');
                }

                $alfa++;
            }

            // Rename worksheet
            $spreadsheet->setTitle('template');

            //SHEET 2 ------------------------------------------------------------------------------------------------------------
            $spreadsheet2 = $excel->createSheet()->setTitle('Data Item ID');
            $excel->setActiveSheetIndex(1);
            $columns = ['No', 'Item ID'];
            $last_index = count($columns) - 1;

            $alfa = 'A';
            foreach ($columns as $key => $value) {
                $spreadsheet2->setCellValue($alfa.'1', $value);
                $spreadsheet2->getStyle($alfa.'1')->applyFromArray($style_col);

                $alfa++;
            }

            $this->db->distinct()->select("TRIM(item_id) AS item")->from("mst_item_raw")
            ->where(["item_id !=" => ""])->order_by("item");
            $datas = $this->db->get()->result();

            $row_no = 2;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $spreadsheet2->setCellValue($alfa.$row_no, $no);
                $spreadsheet2->getStyle($alfa.$row_no)->applyFromArray($style_row_center);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row;
                    if (in_array($key, [''])) { $style = $style_row_center; }
                    if (in_array($key, [''])) { $style = $style_row_right; }

                    if (in_array($key, ['']))
                        { $spreadsheet2->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet2->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet2->getStyle($alfa.$row_no)->applyFromArray($style);
                    $spreadsheet2->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
            }

            $excel->setActiveSheetIndex(0);
            // die(json_encode($excel->getActiveSheet()->toArray()));

            if (ob_get_length()) { ob_end_clean(); }

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Template Item Safety Stock '.date('(Y-m-d)').'.xlsx"');
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

    public function uploadTemplate()
    {
        $result = ['stat' => 'ok', 'data' => []];

        try {
            $spreadsheet = IOFactory::load($_FILES['file_upload']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet(0)->toArray(null, true, true, true);
            $head_col = $sheetData[1];

            if ($head_col['A'] != 'Item ID' || $head_col['B'] != 'Safety Stock')
                { $result['stat'] = 'The template format does not match!'; }
            elseif ((count($sheetData) - 1) == 0)
                { $result['stat'] = 'No data!'; }
            else {
                unset($sheetData[1]);
                foreach ($sheetData as $key => $value) {
                    array_push($result['data'], [
                        'item' => base64_encode($value['A']),
                        'safety' => base64_encode($value['B'])
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
        $safety = base64_decode($post["safety"]);
        $item = base64_decode($post["item"]);

        if (empty(count($this->api->reads("mst_item_raw", [], ["item_id" => $item]))))
            { $result["message"] = "Item ID $item not found"; }

        if (empty($result["message"])) {
            $where = ["item_id" => $item];
            $data = ["safety_stock" => $safety];
            if (empty(count($this->api->reads("item_safety_stock", [], $where)))) {
                $send = $this->api->create("item_safety_stock", array_merge($data,$where));
            } else {
                $send = $this->api->update("item_safety_stock", $where, $data);
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

<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Quotation extends CI_Controller
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
            $data['base_url'] = base_url('planning/quotation/');

            $this->load->view('template/header', $data);
            $this->load->view('planning/quotation');
        } else { redirect('error_access'); }
    }

    public function datatables($act = '')
    {
        $this->db->select("")->from("")->order_by("");
        // die($this->db->get_compiled_select());
        $datas = $this->db->get()->result();
        // die($this->db->last_query());

        if ($act == 'print') {
            $this->db->select("*")->from("config");
            $config = $this->db->get()->row();
            $logo = base_url('assets/image/config/' . $config->logo);

            $this->db->select("TRIM(name) AS name")->from("users")->where("username", $this->session->username);
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
                        <th></th>
                    </tr>
            HTML;
            foreach ($datas as $index => $row) {
                // $no = $index + 1;
                // $html .= <<<HTML
                //     <tr>
                //         <td align="center">$no</td>
                //         <td>{$row->}</td>
                //     </tr>
                // HTML;
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
                    'top' => ['borderStyle' => Border::BORDER_THIN],
                    'left' => ['borderStyle' => Border::BORDER_THIN],
                    'right' => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ];
            $style_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
            $style_right = ['alignment' => ['horizontal' => 'right', 'vertical' => 'center']];
            $style_row_center = array_merge($style_row, $style_center);
            $style_row_right = array_merge($style_row, $style_right);

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
            $columns = ['No', ''];
            $last_index = count($columns) - 1;
            $title = '';

            $this->db->select('TRIM(name) AS name')->from('users')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            foreach ($columns as $key => $value) {
                if ($key == $last_index) {
                    $spreadsheet->setCellValue('A1', $title);
                    $spreadsheet->mergeCells('A1:'.$alfa.'1');
                    $spreadsheet->getStyle('A1')->applyFromArray($style_center);
                    $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                    $spreadsheet->setCellValue($alfa.'3', 'Print Date : ' . date("d M Y H:m:s"));
                    $spreadsheet->setCellValue($alfa.'4', 'Print By : ' . $fullname);
                }

                $spreadsheet->setCellValue($alfa.'6', $value);
                $spreadsheet->getStyle($alfa.'6')->applyFromArray($style_col);

                $alfa++;
            }

            $id = base64_decode($this->input->post('id'));
            $datas = $this->db->select("*")->from("")->order_by("")->get()->result();

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
}

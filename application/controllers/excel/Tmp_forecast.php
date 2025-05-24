<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load library phpspreadsheet
require('./excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// End load library phpspreadsheet

class Tmp_forecast extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
    }

    public function index()
    {
        if ($this->session->username != "") {
            $excel = new Spreadsheet();
            $spreadsheet = $excel->getActiveSheet(0);
            $excel->getProperties()->setCreator('Pixelphy')
                ->setLastModifiedBy('Pixelphy')
                ->setTitle('Office 2007 XLSX Test Document')
                ->setSubject('Office 2007 XLSX Test Document')
                ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('PT BANSHU ELECTRIC INDONESIA');

            $style_col = array(
                'font' => array('bold' => true), // Set font nya jadi bold
                'alignment' => array(
                    'horizontal' => 'center', // Set text jadi ditengah secara horizontal (center)
                    'vertical' => 'center' // Set text jadi di tengah secara vertical (middle)
                ),
                'borders' => array(
                    'top' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN), // Set border top dengan garis tipis
                    'right' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),  // Set border right dengan garis tipis
                    'bottom' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN), // Set border bottom dengan garis tipis
                    'left' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN) // Set border left dengan garis tipis
                )
            );

            $styleRequired = array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'FF0000')
                )
            );

            $month = base64_decode($this->input->get('month'));
            $year = base64_decode($this->input->get('year'));
            $revision = base64_decode($this->input->get('revision'));

            $month_0 =  date('M Y', strtotime($year . "-" . $month . "-01"));
            $month_1 =  date('M Y', strtotime('+1 month', strtotime($year . "-" . $month . "-01")));
            $month_2 =  date('M Y', strtotime('+2 month', strtotime($year . "-" . $month . "-01")));
            $month_3 =  date('M Y', strtotime('+3 month', strtotime($year . "-" . $month . "-01")));
            $month_4 =  date('M Y', strtotime('+4 month', strtotime($year . "-" . $month . "-01")));
            $month_5 =  date('M Y', strtotime('+5 month', strtotime($year . "-" . $month . "-01")));
            $month_6 =  date('M Y', strtotime('+6 month', strtotime($year . "-" . $month . "-01")));
            $month_7 =  date('M Y', strtotime('+7 month', strtotime($year . "-" . $month . "-01")));
            $month_8 =  date('M Y', strtotime('+8 month', strtotime($year . "-" . $month . "-01")));
            $month_9 =  date('M Y', strtotime('+9 month', strtotime($year . "-" . $month . "-01")));
            $month_10 =  date('M Y', strtotime('+10 month', strtotime($year . "-" . $month . "-01")));
            $month_11 =  date('M Y', strtotime('+11 month', strtotime($year . "-" . $month . "-01")));

            // $spreadsheet = $excel->createSheet(0);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            // Header
            $spreadsheet->setCellValue('A1', 'TEMPLATE UPLOAD FORECAST');
            $spreadsheet->mergeCells('A1:N1');
            $spreadsheet->getStyle('A1')->getFont()->setBold(TRUE); // Set bold kolom A1
            $spreadsheet->getStyle('A1')->getFont()->setSize(14);
            $spreadsheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            $spreadsheet->setCellValue('A2', 'PERIOD');
            $spreadsheet->setCellValue('C2', $month);
            $spreadsheet->setCellValue('D2', $year);
            $spreadsheet->setCellValue('A3', 'REVISION');
            $spreadsheet->setCellValue('C3', $revision);

            //Coloumn
            $spreadsheet->setCellValue('A4', 'NO');
            $spreadsheet->setCellValue('B4', 'PRODUCT ID');
            $spreadsheet->setCellValue('C4', strtoupper($month_0));
            $spreadsheet->setCellValue('D4', strtoupper($month_1));
            $spreadsheet->setCellValue('E4', strtoupper($month_2));
            $spreadsheet->setCellValue('F4', strtoupper($month_3));
            $spreadsheet->setCellValue('G4', strtoupper($month_4));
            $spreadsheet->setCellValue('H4', strtoupper($month_5));
            $spreadsheet->setCellValue('I4', strtoupper($month_6));
            $spreadsheet->setCellValue('J4', strtoupper($month_7));
            $spreadsheet->setCellValue('K4', strtoupper($month_8));
            $spreadsheet->setCellValue('L4', strtoupper($month_9));
            $spreadsheet->setCellValue('M4', strtoupper($month_10));
            $spreadsheet->setCellValue('N4', strtoupper($month_11));
            $spreadsheet->getStyle('B4')->applyFromArray($styleRequired);
            $spreadsheet->getStyle('A4:N4')->applyFromArray($style_col);

            //Set Width
            $spreadsheet->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getColumnDimension('C')->setAutoSize(true);
            $spreadsheet->getColumnDimension('D')->setAutoSize(true);
            $spreadsheet->getColumnDimension('E')->setAutoSize(true);
            $spreadsheet->getColumnDimension('F')->setAutoSize(true);
            $spreadsheet->getColumnDimension('G')->setAutoSize(true);
            $spreadsheet->getColumnDimension('I')->setAutoSize(true);
            $spreadsheet->getColumnDimension('J')->setAutoSize(true);
            $spreadsheet->getColumnDimension('K')->setAutoSize(true);
            $spreadsheet->getColumnDimension('L')->setAutoSize(true);
            $spreadsheet->getColumnDimension('M')->setAutoSize(true);
            $spreadsheet->getColumnDimension('N')->setAutoSize(true);

            // Rename worksheet
            $spreadsheet->setTitle('Template');

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="tmp_forecast.xls"');
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
        } else {
            redirect('login');
        }
    }
}

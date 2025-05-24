<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load library phpspreadsheet
require('./excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// End load library phpspreadsheet

class Tmp_process_sub extends CI_Controller
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

            $spreadsheet = $excel->createSheet(0);
            $spreadsheet1 = $excel->createSheet(1);

            // Header
            $spreadsheet->setCellValue('A1', 'TEMPLATE UPLOAD MASTER PROCESS SUB');
            $spreadsheet->mergeCells('A1:F1');
            $spreadsheet->getStyle('A1')->getFont()->setBold(TRUE); // Set bold kolom A1
            $spreadsheet->getStyle('A1')->getFont()->setSize(14);
            $spreadsheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            //Coloumn
            $spreadsheet->setCellValue('A2', 'NO');
            $spreadsheet->setCellValue('B2', 'PROCESS MAIN CODE');
            $spreadsheet->setCellValue('C2', 'PROCESS SUB CODE');
            $spreadsheet->setCellValue('D2', 'PROCESS SUB NAME');
            $spreadsheet->setCellValue('E2', 'REMARKS');
            $spreadsheet->setCellValue('F2', 'INDEX');
            $spreadsheet->getStyle('B2')->applyFromArray($styleRequired);
            $spreadsheet->getStyle('A2:F2')->applyFromArray($style_col);

            //Set Width
            $spreadsheet->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getColumnDimension('C')->setAutoSize(true);
            $spreadsheet->getColumnDimension('D')->setAutoSize(true);
            $spreadsheet->getColumnDimension('E')->setAutoSize(true);
            $spreadsheet->getColumnDimension('F')->setAutoSize(true);

            // Rename worksheet
            $spreadsheet->setTitle('Template');

            //MASTER
            // Header
            $spreadsheet1->setCellValue('A1', 'MASTER MAIN PROCESS');
            $spreadsheet1->mergeCells('A1:D1');
            $spreadsheet1->getStyle('A1')->getFont()->setBold(TRUE); // Set bold kolom A1
            $spreadsheet1->getStyle('A1')->getFont()->setSize(14);
            $spreadsheet1->getStyle('A1')->getAlignment()->setHorizontal('center');

            //Coloumn
            $spreadsheet1->setCellValue('A2', 'NO');
            $spreadsheet1->setCellValue('B2', 'PROCESS MAIN CODE');
            $spreadsheet1->setCellValue('C2', 'PROCESS MAIN NAME');
            $spreadsheet1->setCellValue('D2', 'REMARKS');

            $no = 1;
            $rows = 3;
            $processMains = $this->api->reads('mst_process_main', [], [], "", "number", "asc");
            foreach ($processMains as $processMain) {
                $spreadsheet1->setCellValue('A' . $rows, $no);
                $spreadsheet1->setCellValue('B' . $rows, $processMain->number);
                $spreadsheet1->setCellValue('C' . $rows, $processMain->name);
                $spreadsheet1->setCellValue('D' . $rows, $processMain->description);
                $no++;
                $rows++;
            }

            $spreadsheet1->getStyle('A2:D2')->applyFromArray($style_col);

            //Set Width
            $spreadsheet1->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet1->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet1->getColumnDimension('C')->setAutoSize(true);
            $spreadsheet1->getColumnDimension('D')->setAutoSize(true);

            // Rename worksheet
            $spreadsheet1->setTitle('Master Main Process');

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="tmp_process_sub.xls"');
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

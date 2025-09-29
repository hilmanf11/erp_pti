<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load library phpspreadsheet
require('./excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// End load library phpspreadsheet

class Tmp_lme_range extends CI_Controller
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
                ),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('rgb' => 'FFC000')
                )
            );

            $styleRequired = array(
                'font'  => array(
                    'bold'  => true,
                    'color' => array('rgb' => 'FF0000')
                )
            );

            // $spreadsheet = $excel->createSheet(0);

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $spreadsheet->setCellValue('A1', 'LME');
            $spreadsheet->setCellValue('A2', 'Start');
            $spreadsheet->setCellValue('B2', 'End');
            $spreadsheet->setCellValue('C1', 'Remarks');
            $spreadsheet->mergeCells('A1:B1');
            $spreadsheet->mergeCells('C1:C2');
            $spreadsheet->getStyle('A1:B1')->applyFromArray($style_col);
            $spreadsheet->getStyle('A2')->applyFromArray($style_col);
            $spreadsheet->getStyle('B2')->applyFromArray($style_col);
            $spreadsheet->getStyle('C1:C2')->applyFromArray($style_col);
            $spreadsheet->getStyle('A1:B2')->applyFromArray($styleRequired);

            //Set Width
            $spreadsheet->getColumnDimension('A')->setAutoSize(true);
            $spreadsheet->getColumnDimension('B')->setAutoSize(true);
            $spreadsheet->getColumnDimension('C')->setAutoSize(true);

            // Rename worksheet
            $spreadsheet->setTitle('Template');

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Template LME Range.xls"');
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

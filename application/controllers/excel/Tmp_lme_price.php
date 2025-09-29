<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Load library phpspreadsheet
require('./excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// End load library phpspreadsheet

class Tmp_lme_price extends CI_Controller
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
            $sheet = 0;
            $excel = new Spreadsheet();
            $spreadsheet = $excel->getActiveSheet($sheet);
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

            $style_row = array(
                'alignment' => array(
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

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $alfa = 'A';
            $spreadsheet->setCellValue($alfa.'1', 'ITEM ID'); $alfa++;
            $spreadsheet->setCellValue($alfa.'1', 'MAKER CODE'); $alfa++;
            $spreadsheet->setCellValue($alfa.'1', 'SUPPLIER CODE'); $alfa++;
            $spreadsheet->setCellValue($alfa.'1', 'LME RANGE'); $alfa++;
            $spreadsheet->setCellValue($alfa.'1', 'LME PRICE');

            for ($i='A'; $i <= $alfa; $i++) {
                $spreadsheet->getStyle($i.'1')->applyFromArray($style_col);
                //Set Width
                $spreadsheet->getColumnDimension($i)->setAutoSize(true);
            }

            // Rename worksheet
            $spreadsheet->setTitle('Template');

            //SHEET 2 ------------------------------------------------------------------------------------------------------------
            $sheet++;
            $spreadsheet = $excel->createSheet($sheet);

            $spreadsheet->setCellValue('A1', 'ITEM ID');
            $spreadsheet->getStyle('A1')->applyFromArray($style_col);
            $spreadsheet->getColumnDimension('A')->setAutoSize(true);

            $this->db->select("DISTINCT TRIM(item_id) AS item")->from("supplier_items")->order_by('item');
            foreach ($this->db->get()->result_object() as $key => $value) {
                $row = $key + 2;
                $spreadsheet->setCellValue('A'.$row, $value->item);
                $spreadsheet->getStyle('A'.$row)->applyFromArray($style_row);
            }

            $spreadsheet->setTitle('ITEM ID');

            //SHEET 3 ------------------------------------------------------------------------------------------------------------
            $sheet++;
            $spreadsheet = $excel->createSheet($sheet);

            $alfa = 'A';
            $spreadsheet->setCellValue($alfa.'1', 'MAKER CODE'); $alfa++;
            $spreadsheet->setCellValue($alfa.'1', 'MAKER NAME');

            $get = $this->db->select("DISTINCT TRIM(a.maker_code) AS code, TRIM(b.name) AS maker")
            ->from("supplier_items a")->join("mst_maker b","a.maker_code = b.number")->order_by("maker");
            foreach ($this->db->get()->result_object() as $key => $value) {
                $row = $key + 2;
                $spreadsheet->setCellValue('A'.$row, $value->code);
                $spreadsheet->setCellValue('B'.$row, $value->maker);

                $spreadsheet->getStyle('A'.$row)->applyFromArray($style_row);
                $spreadsheet->getStyle('B'.$row)->applyFromArray($style_row);
            }

            for ($i='A'; $i <= $alfa; $i++) {
                $spreadsheet->getStyle($i.'1')->applyFromArray($style_col);
                $spreadsheet->getColumnDimension($i)->setAutoSize(true);
            }

            $spreadsheet->setTitle('MAKER CODE');

            //SHEET 4 ------------------------------------------------------------------------------------------------------------
            $sheet++;
            $spreadsheet = $excel->createSheet($sheet);

            $alfa = 'A';
            $spreadsheet->setCellValue($alfa.'1', 'SUPPLIER CODE'); $alfa++;
            $spreadsheet->setCellValue($alfa.'1', 'SUPPLIER NAME');

            $get = $this->db->select("DISTINCT TRIM(b.number) AS id, TRIM(b.name) AS name")
            ->from("supplier_items a")->join("suppliers b", "a.supplier_id = b.id")->order_by("name");
            foreach ($this->db->get()->result_object() as $key => $value) {
                $row = $key + 2;
                $spreadsheet->setCellValue('A'.$row, $value->id);
                $spreadsheet->setCellValue('B'.$row, $value->name);

                $spreadsheet->getStyle('A'.$row)->applyFromArray($style_row);
                $spreadsheet->getStyle('B'.$row)->applyFromArray($style_row);
            }

            for ($i='A'; $i <= $alfa; $i++) {
                $spreadsheet->getStyle($i.'1')->applyFromArray($style_col);
                $spreadsheet->getColumnDimension($i)->setAutoSize(true);
            }

            $spreadsheet->setTitle('SUPPLIER CODE');

            //SHEET 5 ------------------------------------------------------------------------------------------------------------
            $sheet++;
            $spreadsheet = $excel->createSheet($sheet);

            $spreadsheet->setCellValue('A1', 'LME RANGE');
            $spreadsheet->getStyle('A1')->applyFromArray($style_col);

            $this->db->select("id, CONCAT(lme_start,'-',lme_end) AS ranges")
                ->from('lme_ranges')->order_by('id');
            foreach ($this->db->get()->result_object() as $key => $value) {
                $row = $key + 2;
                $spreadsheet->setCellValue('A'.$row, $value->ranges);
                $spreadsheet->getStyle('A'.$row)->applyFromArray($style_row);
            }

            $spreadsheet->getColumnDimension('A')->setAutoSize(true);

            $spreadsheet->setTitle('LME RANGE');

            $excel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Template Setup Price LME.xls"');
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

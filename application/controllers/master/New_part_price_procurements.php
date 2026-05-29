<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class New_part_price_procurements extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        //VALIDASI FORM
        $this->form_validation->set_rules('item_id', 'Item ID', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/new_part_price_procurements');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $plant_id = $this->input->get('plant_id') ? $this->input->get('plant_id') : "";

        $send = $this->api->reads('new_part_price_procurements', ["item_id" => $post], ["plant_id" => $plant_id], "", "item_id", "asc", ['item_id']);
        echo json_encode($send);
    }

    public function currency()
    {
        $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
        ->from("currencies")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    public function incoterms()
    {
        $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
        ->from("incoterms")->order_by("name");
        echo json_encode($this->db->get()->result());
    }

    //GET DATATABLES
    public function datatables()
    {
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $sort = $this->input->post('sort');
        $order = $this->input->post('order');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;

        // Sort
        $sort   = isset($sort) ? strval($sort) : 'item_id';
        $order   = isset($order) ? strval($order) : 'ASC';
        //Select Query
        $this->db->select('*')->from('new_part_price_procurements')
        ->where(['deleted' => 0])->order_by(($sort == 'a.item_id') ? $sort . ' ' . $order : 'CAST(' . $sort . ' as UNSIGNED) ' . $order);
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        echo json_encode(['rows' => $records, 'total' => $totalRows]);
    }

    public function datatableHistorical()
    {
        $records = [];
        $this->db->select('*')->from('new_part_price_procurement_history')
        ->like(['descriptions' => '"id":"'.base64_decode($this->input->post('id')).'"'])
        ->order_by("created_date", "DESC");
        foreach ($this->db->get()->result() as $index => $row) { array_push($records, json_decode($row->description)); }

        echo json_encode(['rows' => $records, 'total' => count($records)]);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $new_part_price_procurements = $this->api->read('new_part_price_procurements', [], ["plant_id" => $post['plant_id'], "customers_product_id" => $post['customers_product_id'], "item_id" => $post['item_id']]);
                if (empty(@$new_part_price_procurements->item_id)) {
                    $send   = $this->api->create('new_part_price_procurements', $post);
                } else {
                    $send = json_encode(array("title" => "Duplicate", "message" => " Item Already Exist", "theme" => "error"));
                }
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $new_part_price_procurements = $this->api->read('new_part_price_procurements', [], ["plant_id" => $post['plant_id'], "customers_product_id" => $post['customers_product_id'], "item_id" => $post['item_id']]);
            if (!empty(@$new_part_price_procurements->item_id)) {
                $send = $this->api->update('new_part_price_procurements', ["id" => $id], $post);
            } else {
                $send   = $this->api->create('new_part_price_procurements', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function saveEdit()
    {
        $id = "";
        $data = ["status" => 0, "new_price" => 0, "percentage" => 0];
        foreach ($this->input->post() as $key => $value) {
            $val = base64_decode($value);
            if ($key == 'id') { $id = $val; }
            else { $data[$key] = $val; }
        }
        $send = $this->api->update("new_part_price_procurements", ["id" => $id], $data);
        echo json_encode(["status" => (isset($send["theme"])) ? "ok" : json_encode($send)]);
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->api->delete('new_part_price_procurements', $data);
        echo $send;
    }

    public function template()
    {
        require 'assets/vendors/phpspreadsheet/vendor/autoload.php';

        $spreadsheet = new Spreadsheet(); // cek di codingan paling atas file ini setelah define harus sudah di import USE library nya
        ob_end_clean(); //wajib pakai ini biar tidak corrupt saat file sudah di download
        $file_name = "tmp_new_part_price_procurements_" . date('Ymd');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
        header('Cache-Control: max-age=0');

        // load file from template (jika sudah ada template sebelumnya)
        $filename = 'template/tmp_new_part_price_procurements.xls';
        $spreadsheet = IOFactory::load($filename);

        // set data di sheet 1
        // $data_sheet_1 = $spreadsheet->getActiveSheet();
        // $data_sheet_1->setCellValue('A4', 'Hello World !');

        // $spreadsheet->getSheetByName("new_part_price_procurements")->getComment("A3")->getText()->createTextRun('Total amount on the current invoice, excluding VAT.');

        $styleHeader = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'BLACK'],
                ],
            ],
        ];

        $styleHeaderCode = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'RED'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'BLACK'],
                ],
            ],
        ];

        //------------ create dan setting data di sheet 2
        $data_sheet_2 = $spreadsheet->createSheet(1)->setTitle("Plant");
        //Select Query
        $this->db->select('*');
        $this->db->from('plant');
        $this->db->where_in('id', $arrayplant);
        $this->db->order_by('number', 'ASC');
        $record_sheet_2 = $this->db->get()->result_array();


        $data_sheet_2->setCellValue('A1', 'LIST PLANT')->mergeCells('A1:C1')->getStyle('A1:C1')->applyFromArray($styleHeader);
        $data_sheet_2->setCellValue('A2', 'No')->setAutoFilter('A2:B2')->getStyle('A2')->applyFromArray($styleHeader);
        $data_sheet_2->setCellValue('B2', 'Code')->getStyle('B2')->applyFromArray($styleHeaderCode);
        $data_sheet_2->setCellValue('C2', 'Name')->getStyle('C2')->applyFromArray($styleHeader);

        // setting agar auto width
        foreach ($data_sheet_2->getColumnIterator() as $column) {
            $data_sheet_2->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
        // Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
        $data_sheet_2->getDefaultRowDimension()->setRowHeight(-1);

        // setting freeze column
        $data_sheet_2->freezePane('A3');

        $no_urut_sheet_2 = 1;
        foreach ($record_sheet_2 as $recordsheet_2) {
            $nocell_2 = $no_urut_sheet_2 + 2; // karena dimulai dari baris ke 3
            $data_sheet_2->setCellValue("A" . $nocell_2, $no_urut_sheet_2);
            $data_sheet_2->setCellValue("B" . $nocell_2, $recordsheet_2['number']);
            $data_sheet_2->setCellValue("C" . $nocell_2, $recordsheet_2['name']);
            // $data_sheet_2->setCellValue("H" . $nocell_2, '=IF(F' . $nocell_2 . '=' . '"B71"' . ',"BENAR","SALAH")'); // jika dipasang formula
            $no_urut_sheet_2++;
        }

        //------------ create dan setting data di sheet 3
        $data_sheet_3 = $spreadsheet->createSheet(2)->setTitle("Item Similar MTK Price");
        //Select Query
        $this->db->select('a.*, b.name as plant_name');
        $this->db->from('price_gp a');
        $this->db->join('plant b', 'a.plant_id = b.id');
        $this->db->order_by('a.plant_id', 'ASC');
        $this->db->order_by('a.number', 'ASC');
        $record_sheet_3 = $this->db->get()->result_array();

        $data_sheet_3->setCellValue('A1', 'LIST ITEM')->mergeCells('A1:C1')->getStyle('A1:C1')->applyFromArray($styleHeader);
        $data_sheet_3->setCellValue('A2', 'No')->setAutoFilter('A2:D2')->getStyle('A2')->applyFromArray($styleHeader);
        $data_sheet_3->setCellValue('B2', 'Plant')->getStyle('B2')->applyFromArray($styleHeader);
        $data_sheet_3->setCellValue('C2', 'Item ID')->getStyle('C2')->applyFromArray($styleHeaderCode);

        foreach ($data_sheet_3->getColumnIterator() as $column) {
            $data_sheet_3->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
        // Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
        $data_sheet_3->getDefaultRowDimension()->setRowHeight(-1);

        $data_sheet_3->freezePane('A3');

        $no_urut_sheet_3 = 1;
        foreach ($record_sheet_3 as $recordsheet_3) {
            $nocell_3 = $no_urut_sheet_3 + 2; // karena dimulai dari baris ke 3
            $data_sheet_3->setCellValue("A" . $nocell_3, $no_urut_sheet_3);
            $data_sheet_3->setCellValue("B" . $nocell_3, $recordsheet_3['plant_name']);
            $data_sheet_3->setCellValueExplicit("C" . $nocell_3, $recordsheet_3['number'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $no_urut_sheet_3++;
        }

        // //------------ create dan setting data di sheet 6
        // $data_sheet_6 = $spreadsheet->createSheet(5)->setTitle("Currency");
        // //Select Query
        // $this->db->select('*');
        // $this->db->from('currencies');
        // $this->db->order_by('number', 'ASC');
        // $record_sheet_6 = $this->db->get()->result_array();

        // $data_sheet_6->setCellValue('A1', 'LIST CURRENCY')->mergeCells('A1:C1')->getStyle('A1:C1')->applyFromArray($styleHeader);
        // $data_sheet_6->setCellValue('A2', 'No')->setAutoFilter('A2:C2')->getStyle('A2')->applyFromArray($styleHeader);
        // $data_sheet_6->setCellValue('B2', 'Code')->getStyle('B2')->applyFromArray($styleHeaderCode);
        // $data_sheet_6->setCellValue('C2', 'Name')->getStyle('C2')->applyFromArray($styleHeader);

        // foreach ($data_sheet_6->getColumnIterator() as $column) {
        //     $data_sheet_6->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        // }
        // // Set height semua kolom menjadi auto (mengikuti height isi dari kolommnya, jadi otomatis)
        // $data_sheet_6->getDefaultRowDimension()->setRowHeight(-1);

        // $data_sheet_6->freezePane('A3');

        // $no_urut_sheet_6 = 1;
        // foreach ($record_sheet_6 as $recordsheet_6) {
        //     $nocell_6 = $no_urut_sheet_6 + 2; // karena dimulai dari baris ke 3
        //     $data_sheet_6->setCellValue("A" . $nocell_6, $no_urut_sheet_6);
        //     $data_sheet_6->setCellValueExplicit("B" . $nocell_6, $recordsheet_6['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        //     $data_sheet_6->setCellValue("C" . $nocell_6, $recordsheet_6['description']);
        //     $no_urut_sheet_6++;
        // }

        // setting sheet yang aktif saat dibuka oleh user
        $spreadsheet->setActiveSheetIndex(0);

        // create excel final (download)
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        // $writer = new Xlsx($spreadsheet); //untuk type xlsx
        $writer->save('php://output');
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=marketing_price_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_plant = $this->input->get('filter_plant');
        $filter_item_id = base64_decode($this->input->get('filter_item_id'));

        //Select Query
        $this->db->select('*')->from('new_part_price_procurements')->where('deleted', 0);
        if (!empty($filter_item_id)) { $this->db->like('item_id', trim($filter_item_id)); }
        if (!empty($filter_plant)) { $this->db->where('plant_id', $filter_plant); }
        $this->db->order_by('item_id');
        $records = $this->db->get()->result_array();
        $html = '
        <html>
        <head>
            <title>Print Data</title>
        </head>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;}
            .tbl-judul {font-weight:bold;text-align: center; margin:2px;border-collapse: collapse;width: 100%;font-size: 12px;border: 1px solid #ddd;padding: 2px;}
            .tbl-data {border-collapse: collapse;width: 100%;font-size: 12px; text-align:center;}
            .tbl-data td, .tbl-data th {border: 1px solid #ddd;padding: 2px;}
            .tbl-data tr:hover {background-color: #ddd;}
            .tbl-data th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black; background:#eaeaea;}
            .tbl-data-header {text-align:left; font-weight:bold; background:#eaeaea;}
            .number {text-align:center;}
            .numeric {text-align:right;}
            .tbl-footer {text-align:right; font-weight:bold;}
        </style>
        <body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>NEW PART PRICE PROCUREMENT</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table class="tbl-data" border="1">
        <tr>
            <th>No</th>
            <th>Request Date</th>
            <th>Due Date</th>
            <th>Plant Name</th>
            <th>Volume/Month</th>
            <th>Volume/Year</th>
            <th>Category</th>
            <th>Assy No</th>
            <th>Model</th>
            <th>Customer</th>
            <th>Business Line</th>
            <th>Application</th>
            <th>Masspro</th>
            <th>End User</th>
            <th>Item ID</th>
            <th>Item Name</th>
            <th>Maker</th>
            <th>Qty/Unit</th>
            <th>Qty/Month</th>
            <th>UoM</th>
            <th>Currency</th>
            <th>Price</th>
            <th>New Price</th>
            <th>Percentage</th>
            <th>MOQ</th>
            <th>MPQ</th>
            <th>SPQ</th>
            <th>Lead Time</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Remarks</th>
            <th>Remarks</th>
            <th>Procurement<br>Response Date</th>
            <th>Item ID Maker</th>
            <th>Quot Req</th>
            <th>Quotation</th>
            <th>Incoterms</th>
        </tr>
            ';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['request_date'] . '</td>
                    <td>' . $data['due_date'] . '</td>
                    <td>' . $data['plant_name'] . '</td>
                    <td>' . $data['volume_per_month'] . '</td>
                    <td>' . $data['volume_per_year'] . '</td>
                    <td>' . $data['project_category'] . '</td>
                    <td>' . $data['assy_no'] . '</td>
                    <td>' . $data['model_name'] . '</td>
                    <td>' . $data['customers_name'] . '</td>
                    <td>' . $data['business_line_name'] . '</td>
                    <td>' . $data['application'] . '</td>
                    <td>' . $data['masspro'] . '</td>
                    <td>' . $data['end_user'] . '</td>
                    <td style="mso-number-format:\@">' . $data['item_id'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . $data['maker_name'] . '</td>
                    <td>' . $data['qty_unit'] . '</td>
                    <td>' . $data['qty_month'] . '</td>
                    <td>' . $data['um_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['new_price'] . '</td>
                    <td>' . $data['percentage'] . '</td>
                    <td>' . $data['moq'] . '</td>
                    <td>' . $data['mpq'] . '</td>
                    <td>' . $data['spq'] . '</td>
                    <td>' . $data['leadtime'] . '</td>
                    <td>' . $data['status'] . '</td>
                    <td>' . $data['remarks1'] . '</td>
                    <td>' . $data['remarks2'] . '</td>
                    <td>' . $data['remarks3'] . '</td>
                    <td>' . $data['procurement_respons_date'] . '</td>
                    <td style="mso-number-format:\@">' . $data['item_id_maker'] . '</td>
                    <td>' . $data['quot_req'] . '</td>
                    <td>' . $data['quotation'] . '</td>
                    <td>' . $data['incoterms'] . '</td>
                    ';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    //UPLOAD DATA
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'plant_number' => trim($data->val($i, 2)),
                'item_id' => trim($data->val($i, 3)),
                'item_id_similar' => trim($data->val($i, 4)),
                'percent_adjustment' => trim($data->val($i, 5)),
                // 'currency' => trim($data->val($i, 5)),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/new_part_price_procurements.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/new_part_price_procurements.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/new_part_price_procurements.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        // if ($this->input->post()) {
        //     $data = $this->input->post('data');

        //     // validasi data kolom yang mandatory tidak boleh kosong (color merah)
        //     if (
        //         !empty($data['plant_number']) &&
        //         !empty($data['item_id']) &&
        //         !empty($data['item_id_similar']) &&
        //         // !empty($data['currency']) &&
        //         !empty($data['percent_adjustment'])
        //     ) {
        //         $plant = $this->api->read('plant', [], ["number" => $data['plant_number']]);
        //         $price_gp = $this->api->read('price_gp', [], ["number" => $data['item_id_similar'], "plant_id" => @$plant->id]);

        //         // $currency = $this->api->read('currencies', [], ["name" => $data['currency']]);
        //         $new_part_price_procurements = $this->api->read('new_part_price_procurements', [], ["plant_id" => $data['plant_number'], "item_id" => $data['item_id']]);

        //         if (empty($plant->number)) {
        //             echo json_encode(array("title" => "Not Found", "message" => "Plant Code " . $data['plant_number'] . " Not Found", "theme" => "error"));
        //         } else if (empty($price_gp->number)) {
        //             echo json_encode(array("title" => "Not Found", "message" => "Item Similar " . $data['item_id_similar'] . " Not Found", "theme" => "error"));
        //         }
        //         // else if (empty($currency->id)) {
        //         //     echo json_encode(array("title" => "Not Found", "message" => "Currency Code " . $data['currency'] . " Not Found", "theme" => "error"));
        //         // } 
        //         else {
        //             $dataFinal = array(
        //                 "plant_id" => @$plant->id,
        //                 "item_id" => $data['item_id'],
        //                 "item_id_similar" => $data['item_id_similar'],
        //                 // "currency_id" => @$currency->id,
        //                 "percent_adjustment" => $data['percent_adjustment'],
        //             );
        //             if (empty(@$new_part_price_procurements->id)) {
        //                 $send = $this->api->create('new_part_price_procurements', $dataFinal);
        //             } else {
        //                 $send = json_encode(array("title" => "Duplicate", "message" => " Item Already Exist", "theme" => "error"));
        //             }
        //             echo $send;
        //         }
        //     } else {
        //         echo json_encode(array("title" => "Found Empty", "message" => " Mandatory Data Is Empty", "theme" => "error"));
        //     }
        // }
    }
}

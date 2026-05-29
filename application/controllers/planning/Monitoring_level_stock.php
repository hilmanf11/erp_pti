<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Monitoring_level_stock extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '512M');
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
            $data["button"] = $this->getbutton($this->id_menu());

            $year = intval(date("Y"));
            $data["years"] = [];
            for ($i=($year + 1); $i >= ($year - 7); $i--) {
                $arr = ["year" => $i];
                if ($i == $year) { $arr["selected"] = true; }
                array_push($data["years"], $arr);
            }

            $data["months"] = [];
            for ($i=1; $i <= 12; $i++) {
                $month = str_pad($i, 2, "0", STR_PAD_LEFT);
                $arr = [
                    "name" => date("F", strtotime($year."-".$month."-01")),
                    "number" => $month
                ];

                if ($i == intval(date("m"))) { $arr["selected"] = true; }
                array_push($data["months"], $arr);
            }

            $data["revision"] = [
                ["number" => "0", "name" => "Revision 0", "selected" => true],
                ["number" => "1", "name" => "Revision 1"],
                ["number" => "2", "name" => "Revision 2"],
                ["number" => "3", "name" => "Revision 3"],
                ["number" => "4", "name" => "Revision 4"],
                ["number" => "5", "name" => "Revision 5"]
            ];

            // $this->db->distinct()->select("TRIM(number) AS number, TRIM(name) AS name")
            // ->from("mst_item_family")->where("number !=", "06")->order_by("name");
            $this->db->distinct()->select("TRIM(c.name) AS name")
            ->from("generate_mrp_finals a")
                ->join("mst_item_raw b", "a.part_no = b.item_id", "LEFT")
                ->join("mst_item_family c", "b.pfm_id = c.number", "LEFT")
            ->order_by("name");
            $data["prodfam"] = $this->db->get()->result();

            $data["status"] = [
                ["stat" => "POTENTIAL DEADSTOCK"],
                ["stat" => "OVER"],
                ["stat" => "OK"],
                ["stat" => "SH"]
            ];

            $data["type"] = [
                ["type" => "IMPORT"],
                ["type" => "LOCAL"]
            ];

            // die(json_encode($data));
            $this->load->view('template/header', $data);
            $this->load->view('planning/monitoring_level_stock');
        } else { redirect('error_access'); }
    }

    public function datatables($act = '')
    {
        // die(var_dump($this->input->post()));
        $year = base64_decode($this->input->post('year') ?? $this->input->get('year'));
        $month = base64_decode($this->input->post('month') ?? $this->input->get('month'));
        $revision = base64_decode($this->input->post('revision') ?? $this->input->get('revision'));
        $prodfam = base64_decode($this->input->post('prodfam') ?? $this->input->get('prodfam'));
        $status = base64_decode($this->input->post('status') ?? $this->input->get('status'));
        $type = base64_decode($this->input->get('type'));
        $datas = [];
        $datas = $this->getData([
            'year' => $year,
            'month' => $month,
            'revision' => $revision,
            'prodfam' => $prodfam,
            'status' => $status,
            'type' => $type,
        ]);
        // die(json_encode($datas));
        $columns = $this->getHoliday($year.$month);

        echo json_encode([
            'data' => ['total' => count($datas), 'rows' => $datas],
            'columns' => [
                'total' => intval(date('t', strtotime($year.'-'.$month.'-01'))),
                'format' => $year.'-'.$month.'-',
                'holiday' => $columns
            ]
        ]);
    }

    public function datatablesHistory()
    {
        // code...
    }

    public function excel()
    {
        if ($this->session->username != "") {
            $year = base64_decode($this->input->get('year'));
            $month = base64_decode($this->input->get('month'));
            $revision = base64_decode($this->input->get('revision'));
            $prodfam = base64_decode($this->input->get('prodfam'));
            $status = base64_decode($this->input->get('status'));
            $type = base64_decode($this->input->get('type'));

            $periode = date('F - Y', strtotime($year.'-'.$month.'-01'));
            $revisions = ["Revision 0", "Revision 1", "Revision 2", "Revision 3", "Revision 4", "Revision 5"];

            $prodfam_name = 'ALL';
            if (!empty($prodfam)) {
                $this->db->distinct()->select("TRIM(name) AS name")
                ->from("mst_item_family")->where("number", $prodfam);
                $prodfam_name = $this->db->get()->row()->name;
            }

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
            $style_center = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
            $style_row_center = array_merge($style_row, $style_center);
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
            $col1 = [];
            $col2 = [];
            $holidays = $this->getHoliday($year.$month);
            $strtotime = strtotime($year.'-'.$month.'-01');
            $month_name = date('M', $strtotime);
            $end_month = date('t', $strtotime);
            for ($i = 1; $i <= $end_month; $i++) {
                $date = str_pad($i, 2, "0", STR_PAD_LEFT);
                $format = $date.' '.$month_name;
                if (!in_array(intval(date('w', strtotime($year.'-'.$month.'-'.$date))), [0,6]) && !in_array($i, $holidays)) {
                    $format .= ' (W/P '.(count($col1) + 1).')';
                }

                $col1[] = $format;
                $col1[] = '';
                $col1[] = '';
                $col1[] = '';
                $col2[] = 'ETA';
                $col2[] = 'NEED';
                $col2[] = 'SUPPLY';
                $col2[] = 'BALANCE';
            }

            $col1[] = 'SAFETY STOCK';
            $col1[] = '';
            $col2[] = 'QTY';
            $col2[] = 'BALANCE';

            for ($i=1; $i <= 3; $i++) {
                for ($j=1; $j <= 4; $j++) {
                    $col1[] = date('M', strtotime('+'.$i.' month', $strtotime)).' Week '.$j;
                    $col1[] = '';
                    $col1[] = '';
                    $col2[] = 'ETA';
                    $col2[] = 'NEED';
                    $col2[] = 'BALANCE';
                }
            }

            $columns = [
                ['No', 'PART NO', 'SIMILAR PART', 'PART NAME', 'PRODUCT FAMILY', 'CLASS', 'PO L/T', 'MOQ', 'MPQ', 'SPQ', 'MAKER TYPE', 'STOCK WHS', 'OS SUPPLY', 'OS WO', 'ETA', 'BALANCE', 'STOCK AWAL', 'TOTAL ETA', 'TOTAL NEED', '', '', 'ITO (DAYS)', '', '', ''],
                ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'REV.00', 'REV.01', 'REV.02', 'STD (MIN)', 'STD (MAX)', 'ACT', 'STATUS']
            ];
            array_splice($columns[0], 21, 0, $col1);
            array_splice($columns[1], 21, 0, $col2);
            $last_index = count($columns[0]) - 1;
            $title = 'Monitoring Level Stock';
            // die(json_encode($columns));

            $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $row_no = 3;
            $spreadsheet->setCellValue('B'.$row_no, 'Periode');
            $spreadsheet->setCellValue('C'.$row_no, ': '.$periode); $row_no++;
            $spreadsheet->setCellValue('B'.$row_no, 'Revision');
            $spreadsheet->setCellValue('C'.$row_no, ': '.$revisions[intval($revision)]); $row_no++;
            $spreadsheet->setCellValue('B'.$row_no, 'Product Family');
            $spreadsheet->setCellValue('C'.$row_no, ': '.$prodfam_name); $row_no++;
            $spreadsheet->setCellValue('B'.$row_no, 'Status ITO');
            $spreadsheet->setCellValue('C'.$row_no, ': '.((empty($status)) ? 'ALL' : $status)); $row_no++;
            $spreadsheet->setCellValue('B'.$row_no, 'Maker Type');
            $spreadsheet->setCellValue('C'.$row_no, ': '.((empty($type)) ? 'ALL' : $type));

            $row_no += 2;
            $key_holiday = [];
            $alfa_holiday = [];
            foreach ($columns as $index => $row) {
                $alfa = 'A';
                $bf_alfa = '';
                $merge_cell = '';
                foreach ($row as $key => $value) {
                    if ($key == ($last_index - 2)) {
                        $spreadsheet->setCellValue($alfa.'4', 'Print By');
                        $spreadsheet->setCellValue($alfa.'3', 'Print Date');
                    } elseif ($key == ($last_index - 1)) {
                        $print_cell = $alfa;
                        $spreadsheet->setCellValue($alfa.'3', ': '.date("d M Y H:m:s"));
                        $spreadsheet->setCellValue($alfa.'4', ': '.$fullname);
                    } elseif ($key == $last_index) {
                        $spreadsheet->setCellValue('A1', $title);
                        $spreadsheet->mergeCells('A1:'.$alfa.'1');
                        $spreadsheet->getStyle('A1')->applyFromArray($style_center);
                        $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                        $spreadsheet->mergeCells($print_cell.'3:'.$alfa.'3');
                        $spreadsheet->mergeCells($print_cell.'4:'.$alfa.'4');
                    }

                    $style = $style_col;
                    $add_color = false;
                    if ($index == 0) {
                        if (gettype(strpos($value, " $month_name")) == 'integer' && gettype(strpos($value, " (W/P")) == 'boolean') {
                            $add_color = true;
                            $key_holiday[] = $key;
                            $key_holiday[] = $key + 1;
                            $key_holiday[] = $key + 2;
                            $key_holiday[] = $key + 3;
                        }
                    } else {
                        if (in_array($key, $key_holiday)) {
                            $add_color = true;
                            $alfa_holiday[] = $alfa;
                        }
                    }
                    if ($add_color) {
                        $style['fill'] = [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFB0B0']
                        ];
                    }

                    $spreadsheet->setCellValue($alfa.$row_no, $value);
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style);
                    if (empty($value)) {
                        if ($index == 0) {
                            if (!empty($merge_cell)) { $spreadsheet->unmergeCells($merge_cell); }
                            $merge_cell = $bf_alfa.$row_no.':'.$alfa.$row_no;
                            $spreadsheet->mergeCells($merge_cell);
                        } else { $spreadsheet->mergeCells($alfa.($row_no - 1).':'.$alfa.$row_no); }
                    } else { $bf_alfa = $alfa; $merge_cell = ''; }

                    $alfa++;
                }
                $row_no++;
            }

            $balances = ['balance'];
            for ($i=1; $i <= 31; $i++) { $balances[] = 'balance_'.$i; }
            $balances[] = 'stock_balance';
            for ($i=1; $i <= 3; $i++) { 
                for ($j=1; $j <= 4; $j++) { 
                    $balances[] = 'week_'.$i.'_'.$j.'_balance';
                }
            }

            $datas = $this->getData([
                'year' => $year,
                'month' => $month,
                'revision' => $revision,
                'prodfam' => $prodfam,
                'status' => $status,
                'type' => $type,
            ]);

            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                unset($row['stock']);
                unset($row['need_week_2']);
                unset($row['need_week_3']);
                unset($row['need_week_4']);
                unset($row['id']);

                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row_center);
                // die(json_encode($row));
                foreach ($row as $key => $value) {
                    $alfa++;

                    $style = $style_row_right;
                    if (in_array($key, ['class','maker','ito_status'])) { $style = $style_row_center; }
                    if (in_array($key, ['part_no','part_name','prodfam'])) { $style = $style_row; }

                    if (in_array($alfa, $alfa_holiday)) {
                        $style['fill'] = [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFB0B0']
                        ];
                    }
                    if (in_array($key, $balances) && floatval($value) < 0)
                        { $style['font'] = ['color' => ['argb' => 'FFFF0000']]; }

                    if (in_array($key, ['part_no']))
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

    public function getData($data)
    {
        $year = $data['year'];
        $month = $data['month'];
        $revision = $data['revision'];
        $prodfam = $data['prodfam'];
        $status = $data['status'];
        $type = $data['type'];

        $where = ["a.p_year" => $year, "a.p_month" => $month, "a.revision" => $revision];
        if (!empty($prodfam)) { $where['m.number'] = $prodfam; }
        if (!empty($type)) { $where['c.maker_code'] = $type; }

        $periode = $year.$month;
        $year_month = $year.'-'.$month;
        $w_date_start = $year_month.'-01';
        $w_date_end = date('Y-m-t', strtotime($w_date_start));
        $periode2 = date('Ym', strtotime("+1 month", strtotime($w_date_start)));
        $periode3 = date('Ym', strtotime("+2 month", strtotime($w_date_start)));
        $periode4 = date('Ym', strtotime("+3 month", strtotime($w_date_start)));
        $end_day = substr($w_date_end, -2);
        $dummy = $this->load->database('dummy', TRUE);
        $etas = [];
        $supplys = [];
        $result = [];
        $cols = [];
        $dates = [];

        for ($i=1; $i <= $end_day; $i++) {
            $eta_date = $year_month.'-'.str_pad($i, 2, "0", STR_PAD_LEFT);
            array_push($dates, "CAST(date_$i AS SIGNED) AS need_$i");
            array_push($etas, "CASE WHEN eta = '$eta_date' THEN delivery ELSE 0 END AS eta_$i");
            array_push($cols, "COALESCE(f.eta_$i, 0) AS eta_$i, COALESCE(e.need_$i, 0) AS need_$i, 0 AS supply_$i, 0 AS balance_$i");
        }
        $col_eta = implode(', ', $etas);
        $col_wp = implode(', ', $cols).',';
        $date_cols = implode(', ', $dates);

        $cols = [];
        foreach (['h','i','j'] as $key => $value) {
            $no = $key + 1;
            for ($i=1; $i <= 4; $i++) { 
                array_push($cols,
                    "COALESCE(".$value.".qty_eta_".$i.", 0) AS week_".$no."_".$i."_eta, ".
                    "0 AS week_".$no."_".$i."_need, ".
                    "0 AS week_".$no."_".$i."_balance, "
                );
            }
        }
        $col_periodes = implode('', $cols);

        // $q = "SELECT DISTINCT TO_CHAR(por_date, 'DD') AS date, TRIM(item_id) AS item, SUM(por_receiveqty) AS qty FROM por ".
        // "WHERE por_docno NOT LIKE '%PEMUTIHAN%' AND por_date BETWEEN '$w_date_start' AND '$w_date_end' ".
        // "GROUP BY por_date, item_id ORDER BY date, item";
        // $get = $dummy->query($q);
        // foreach ($get->result() as $index => $row) {
        //     if (!isset($etas[$row->date])) { $etas[$row->date] = []; }
        //     $etas[$row->date][$row->item] = floatval($row->qty);
        // }
        // die(json_encode($etas));

        $q = "SELECT TO_CHAR(ith_date, 'DD') AS date, TRIM(ith_item_id) AS item, SUM(ith_qty) AS qty FROM ith ".
        "WHERE ith_form LIKE '%OU%' AND ith_date BETWEEN '$w_date_start' AND '$w_date_end' ".
        "GROUP BY ith_date, ith_item_id ORDER BY date, item";
        $get = $dummy->query($q);
        foreach ($get->result() as $index => $row) {
            if (!isset($supplys[$row->date])) { $supplys[$row->date] = []; }
            $supplys[$row->date][$row->item] = floatval($row->qty);
        }
        // die(json_encode($supplys));

        $arr = [[],[],[]];
        for ($i=0; $i < 3; $i++) {
            $format = $year.'-'.str_pad(intval($month) + $i, 2, "0", STR_PAD_LEFT);
            $set_date = $format.'-01';
            $get_date = intval(date('d', strtotime('+'.(6 - intval(date('w', strtotime($set_date)))).' day', strtotime($set_date))));
            $date1 = $format.'-'.str_pad($get_date, 2, "0", STR_PAD_LEFT); $get_date++;
            $idx1 = $format.'-'.str_pad($get_date, 2, "0", STR_PAD_LEFT); $get_date += 6;
            $date2 = $format.'-'.str_pad($get_date, 2, "0", STR_PAD_LEFT); $get_date++;
            $idx2 = $format.'-'.str_pad($get_date, 2, "0", STR_PAD_LEFT); $get_date += 6;
            $date3 = $format.'-'.str_pad($get_date, 2, "0", STR_PAD_LEFT); $get_date++;
            $idx3 = $format.'-'.str_pad($get_date, 2, "0", STR_PAD_LEFT);
            $arr_eta = [
                $set_date => $date1,
                $idx1 => $date2,
                $idx2 => $date3,
                $idx3 => ''
            ];
            foreach ($arr_eta as $key => $value) {
                $search = (empty($value)) ? ">= '$key'" : "BETWEEN '$key' AND '$value'";
                array_push($arr[$i], "SUM(CASE WHEN eta $search THEN delivery ELSE 0 END) AS qty_eta_".(count($arr[$i]) + 1));
            }
        }
        $eta_cols1 = implode(', ', $arr[0]);
        $eta_cols2 = implode(', ', $arr[1]);
        $eta_cols3 = implode(', ', $arr[2]);

        $this->db->distinct()->select(
            "TRIM(a.part_no) AS part_no, ".
            "'' AS similar_part, ".
            "COALESCE(TRIM(a.part_name),'') AS part_name, ".
            "COALESCE(TRIM(m.name),'') AS prodfam, ".
            "COALESCE(TRIM(b.class),'') AS class, ".
            "a.leadtime AS po_lt, a.moq, a.mpq, ".
            "COALESCE(c.spq, 0) AS spq, ".
            "COALESCE(TRIM(d.type),'') AS maker, ".
            "COALESCE(a.qty_rm, 0) AS stock_whs, ".
            "COALESCE(e.os_supply,0) AS os_supply, ".
            "COALESCE(e.os_wo, 0) AS os_wo, ".
            "COALESCE(e.eta, 0) AS eta, ".
            "COALESCE(a.qty_rm, 0) - COALESCE(e.stock_awal, 0) AS balance, ".
            "COALESCE(e.stock_awal, 0) AS stock_awal, ".
            // "COALESCE(f.qty_eta, 0) AS total_eta, ".
            "0 AS total_eta, ".
            "COALESCE(a.issued_1, 0) AS rev_0, ".
            "COALESCE(a.issued_2, 0) AS rev_1, ".
            "COALESCE(a.issued_3, 0) AS rev_2, ".
            $col_wp.
            "COALESCE(g.safety_stock, 0) AS stock, ".
            "0 AS stock_qty, ".
            "0 AS stock_balance, ".
            "COALESCE(a.need_2, 0) AS need_week_2, ".
            "COALESCE(a.need_3, 0) AS need_week_3, ".
            "COALESCE(a.need_4, 0) AS need_week_4, ".
            $col_periodes.
            "COALESCE(l.std_min, 0) AS ito_std_min, ".
            "COALESCE(l.std_max, 0) AS ito_std_max, ".
            "0 AS ito_act, 'POTENTIAL DEADSTOCK' AS ito_status, ".
            "a.id"
        )->from("generate_mrp_finals a")
            ->join("(
                SELECT DISTINCT part_no, class FROM generate_mrp_abcclass
                WHERE p_year = '$year' AND p_month = '$month' AND revision = '$revision'
            ) b", "a.part_no = b.part_no", "LEFT")
            ->join("(
                SELECT DISTINCT item_id, spq, maker_code
                FROM supplier_items WHERE share_order > 0
            ) c", "a.part_no = c.item_id")
            ->join("mst_maker d", "c.maker_code = d.number")
            ->join("(
                SELECT DISTINCT product_no, os_supply, os_wo, eta, COALESCE(os_supply,0) - COALESCE(os_wo,0) AS stock_awal, $date_cols
                FROM generate_item_calculation WHERE p_year = '$year' AND p_month = '$month' AND revision = '$revision'
            ) e", "a.part_no = e.product_no", "LEFT")
            ->join("(
                SELECT DISTINCT item_id, $col_eta FROM purchase_confirm
                WHERE DATE_FORMAT(eta, '%Y%m') = '$periode' GROUP BY item_id
            ) f", "a.part_no = f.item_id", "LEFT")
            ->join("item_safety_stock g", "a.part_no = g.item_id", "LEFT")
            ->join("(
                SELECT DISTINCT item_id, $eta_cols1
                FROM purchase_confirm WHERE DATE_FORMAT(eta, '%Y%m') = '$periode2' GROUP BY item_id
            ) h", "a.part_no = h.item_id", "LEFT")
            ->join("(
                SELECT DISTINCT item_id, $eta_cols2
                FROM purchase_confirm WHERE DATE_FORMAT(eta, '%Y%m') = '$periode3' GROUP BY item_id
            ) i", "a.part_no = i.item_id", "LEFT")
            ->join("(
                SELECT DISTINCT item_id, $eta_cols3
                FROM purchase_confirm WHERE DATE_FORMAT(eta, '%Y%m') = '$periode4' GROUP BY item_id
            ) j", "a.part_no = j.item_id", "LEFT")
            ->join("mst_item_raw k", "a.part_no = k.item_id", "LEFT")
            ->join("standard_ito l", "c.maker_code = l.maker_number AND k.pfm_id = l.pfm_id", "LEFT")
            ->join("mst_item_family m", "k.pfm_id = m.number", "LEFT")
        ->where($where)->order_by("part_no");
        // die($this->db->get_compiled_select());
        // die($this->db->last_query());
        foreach ($this->db->get()->result_array() as $index => $row) {
            $balance = 0;
            $arr = [];

            for ($i=1; $i <= $end_day; $i++) {
                $day = str_pad($i, 2, "0", STR_PAD_LEFT);
                $supply_qty = 0;
                $eta_qty = 0;

                // if (isset($etas[$day])) { $eta_qty = floatval($etas[$day][$row['part_no']] ?? 0); }
                if (isset($supplys[$day])) { $supply_qty = floatval($supplys[$day][$row['part_no']] ?? 0); }
                $wp = floatval($row['need_'.$i]);
                $balance += round($eta_qty - $wp);

                // $row['eta_'.$i] = round($eta_qty);
                $row['supply_'.$i] = abs(round($supply_qty));
                $row['balance_'.$i] = round($balance);

                if (!empty($wp)) {
                    $row['total_eta'] += $row['eta_'.$i];
                    array_push($arr, $wp);
                }
            }

            $row['stock_qty'] = ($row['stock'] == '0') ? 0
                : floatval(number_format(floatval($row['need_week_2']) * (floatval($row['stock']) / 100), 2, '.', ''));
            $row['stock_balance'] = floatval(number_format($balance - $row['stock_qty'], 2, '.', ''));

            $balance = $row['stock_balance'];
            for ($i=1; $i <= 3; $i++) {
                $row['total_eta'] += $row['week_'.$i.'_1_eta'] + $row['week_'.$i.'_2_eta'] + $row['week_'.$i.'_3_eta'] + $row['week_'.$i.'_4_eta'];
                $week_split_need = round(floatval($row['need_week_'.($i + 1)]) / 4);

                $row['week_'.$i.'_1_need'] = $week_split_need;
                $row['week_'.$i.'_1_balance'] = floatval(number_format($balance + $row['week_'.$i.'_1_eta'] - $row['week_'.$i.'_1_need'], 2, '.', ''));
                $row['week_'.$i.'_2_need'] = $week_split_need;
                $row['week_'.$i.'_2_balance'] = floatval(number_format($row['week_'.$i.'_1_balance'] + $row['week_'.$i.'_2_eta'] - $row['week_'.$i.'_2_need'], 2, '.', ''));
                $row['week_'.$i.'_3_need'] = $week_split_need;
                $row['week_'.$i.'_3_balance'] = floatval(number_format($row['week_'.$i.'_2_balance'] + $row['week_'.$i.'_3_eta'] - $row['week_'.$i.'_3_need'], 2, '.', ''));
                $row['week_'.$i.'_4_need'] = $week_split_need;
                $row['week_'.$i.'_4_balance'] = floatval(number_format($row['week_'.$i.'_3_balance'] + $row['week_'.$i.'_4_eta'] - $row['week_'.$i.'_4_need'], 2, '.', ''));

                $balance = $row['week_'.$i.'_4_balance'];
            }

            $min = floatval($row['ito_std_min']);
            $max = floatval($row['ito_std_max']);
            $average_wp = (empty(array_sum($arr)) || empty(count($arr))) ? 0 : array_sum($arr) / count($arr);

            if (empty($average_wp)) {
                $row['ito_act'] = 0;

                if ($row['stock_qty'] > 0) { $row['ito_act'] = $row['stock_qty']; }
                else { $row['ito_status'] = "OK"; }
            } else {
                $row['ito_act'] = floatval(number_format($balance / $average_wp, 2, '.', ''));
                $row['ito_status'] = "OK";

                if ($row['ito_act'] > $max) { $row['ito_status'] = "OVER"; }
                elseif ($row['ito_act'] < $min) { $row['ito_status'] = "SH"; }
            }

            $push = true;
            if (!empty($status)) { $push = $status == $row['ito_status']; }
            if ($push) { array_push($result, $row); }
        }

        return $result;
    }

    public function getHoliday($periode)
    {
        $result = [];
        $this->db->distinct()->select("DATE_FORMAT(working_date, '%d') AS day")->from("working_calendar")
        ->where("DATE_FORMAT(working_date, '%Y%m') =", $periode)->order_by("working_date");
        foreach ($this->db->get()->result() as $index => $row) { array_push($result, intval($row->day)); }
        return $result;
    }
}

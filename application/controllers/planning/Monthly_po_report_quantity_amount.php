<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Monthly_po_report_quantity_amount extends CI_Controller
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
            $data["button"] = $this->getbutton($this->id_menu());

            $periodes = ["month" => [
                ["id" => "01", "name" => "January"],
                ["id" => "02", "name" => "February"],
                ["id" => "03", "name" => "March"],
                ["id" => "04", "name" => "April"],
                ["id" => "05", "name" => "May"],
                ["id" => "06", "name" => "June"],
                ["id" => "07", "name" => "July"],
                ["id" => "08", "name" => "August"],
                ["id" => "09", "name" => "September"],
                ["id" => "10", "name" => "October"],
                ["id" => "11", "name" => "November"],
                ["id" => "12", "name" => "December"]
            ], "year" => []];
            $tahun_before = date("Y", strtotime("-7 year", strtotime(date("Y"))));
            $tahun_next = date("Y", strtotime("+1 year", strtotime(date("Y"))));
            for ($i = $tahun_next; $i >= $tahun_before; $i--) {
                array_push($periodes["year"], ["id" => $i, "name" => $i]);
            }

            $this->db->distinct()->select("TRIM(number) AS id, TRIM(name) AS name")->from("plants")->order_by("name");
            $plants = $this->db->get()->result();

            $this->db->distinct()->select("TRIM(id) AS id, TRIM(name) AS name")->from("suppliers")->order_by("name");
            $vendors = $this->db->get()->result();

            $this->db->distinct()->select("TRIM(number) AS id, TRIM(name) AS name")->from("mst_maker")->order_by("name");
            $makers = $this->db->get()->result();

            $this->db->distinct()->select("TRIM(number) AS id, TRIM(name) AS name")->from("mst_item_family")->order_by("name");
            $prodfams = $this->db->get()->result();

            $this->load->view("template/header", $data);
            $this->load->view("planning/monthly_po_report_quantity_amount", [
                "periodes" => $periodes,
                "plants" => $plants,
                "vendors" => $vendors,
                "makers" => $makers,
                "prodfams" => $prodfams,
                "displays" => [["name" => "Recap", "selected" => true], ["name" => "Detail"]]
            ]);
        } else { redirect("error_access"); }
    }

    public function readItems()
    {
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $page = isset($page) ? intval($page) : 1;
        $rows = isset($rows) ? intval($rows) : 50;
        $offset = ($page - 1) * $rows;
        $pfm = base64_decode($this->input->post('pfm'));

        $this->db->distinct()->select("TRIM(item_id) AS id, TRIM(item_name) AS name")->from("mst_item_raw")->like('item_id', $post);
        if (!empty($pfm)) { $this->db->where('pfm_id', $pfm); }
        $this->db->order_by("name");
        $totalRows = $this->db->count_all_results('', false);
        $this->db->limit($rows, $offset);
        echo json_encode(['total' => $totalRows, 'rows' => $this->db->get()->result()]);
    }

    public function getData($arr, $display = 'RECAP')
    {
        $plant = $arr['plant'];
        $period = $arr['period'];
        $vendor = $arr['vendor'];
        $maker = $arr['maker'];
        $prodfam = $arr['prodfam'];
        $part_no = $arr['part_no'];

        $q_item = ($display == 'RECAP') ? "" : ", x.item_id";
        $q_qtys = "SELECT CONCAT(x.p_year, x.p_month) AS period, x.supplier_id$q_item, SUM(x.qty) AS qtys, SUM(x.qty * x.price * CASE ".
            "WHEN x.currency = 'IDR' THEN 1 ".
            "WHEN y.middle IS NOT NULL THEN y.middle ".
            "ELSE 0 ".
        "END) as total FROM purchase_orders x ".
        "LEFT JOIN exchange_rates y ON x.currency = y.currency AND x.purchase_order_date BETWEEN y.start_date AND y.end_date ".
        "WHERE CONCAT(x.p_year, x.p_month) IN ('".(implode("', '", $period))."') ".
        "GROUP BY x.p_year, x.p_month, x.supplier_id$q_item";
        // die($q_qtys);

        $this->db->select(
            "TRIM(b.name) AS supplier,".
            (($display == 'RECAP') ? "TRIM(e.name) AS maker," : "TRIM(a.item_id) AS item,".
            "TRIM(a.item_vendor) AS vendor,".
            "TRIM(a.item_name) AS name,".
            "TRIM(c.name) AS prodfam,".
            "TRIM(e.name) AS maker,".
            "TRIM(b.type) AS type,".
            "TRIM(a.uom) AS uom,").
            "'IDR' AS currency,".
            "GROUP_CONCAT(DISTINCT '\"', a.p_year, a.p_month, '\":[', COALESCE(f.qtys, 0), ',', COALESCE(f.total, 0), ']') AS qtys"
        )->from("purchase_orders a")
            ->join("suppliers b", "a.supplier_id = b.id")
            ->join("mst_item_family c", "a.product_family = c.number")
            ->join("supplier_items d", "a.supplier_id = d.supplier_id AND a.item_id = d.item_id", "left")
            ->join("mst_maker e", "e.number = d.maker_code", "left")
            ->join("(".$q_qtys.") f", "a.supplier_id = f.supplier_id ".(($display == 'RECAP') ? "" : "AND a.item_id = f.item_id")." AND CONCAT(a.p_year, a.p_month) = f.period")
        ->where("d.plant", $plant)->where_in("CONCAT(a.p_year, a.p_month)", $period);

        if (!empty($vendor)) { $this->db->where("a.supplier_id", $vendor); }
        if (!empty($maker)) { $this->db->where("d.maker_code", $maker); }
        if (!empty($prodfam)) { $this->db->where("a.product_family", $prodfam); }
        if (!empty($part_no)) { $this->db->where("a.item_id", $part_no); }

        $this->db->group_by("a.supplier_id".(($display == 'RECAP') ? "" : ", a.item_id"))->order_by(
            (($display == 'RECAP') ? "" : "prodfam, ")."supplier".(($display == 'RECAP') ? "" : ", item")
        );
        return $this->db->get()->result();
    }

    public function print()
    {
        $period_start = strtotime(base64_decode($this->input->get('period_start')).'-01');
        $period_end = strtotime(base64_decode($this->input->get('period_end')).'-01');
        $display = strtoupper(base64_decode($this->input->get('display')));
        $plant = base64_decode($this->input->get('plant'));
        $vendor = base64_decode($this->input->get('vendor'));
        $maker = base64_decode($this->input->get('maker'));
        $prodfam = base64_decode($this->input->get('prodfam'));
        $part_no = base64_decode($this->input->get('part_no'));

        $this->db->distinct()->select("TRIM(name) AS name")->from("plants")->where("number", $plant);
        $plant_name = $this->db->get()->row()->name;
        $periode_name = strtoupper(date('F Y', $period_start)).' - '.strtoupper(date('F Y', $period_end));

        $totals = [[0, 0], [0, 0]];
        $sub_header = null;
        $header = null;
        $period = [];
        $start = $period_start;
        while ($start <= $period_end) {
            array_push($period, date('Ym', $start));
            array_push($totals, [0, 0]);

            $month_name = strtoupper(date('M-y', $start));
            $header .= <<<HTML
                <th style="text-align: center;" colspan="2">$month_name</th>
            HTML;
            $sub_header .= <<<HTML
                <th style="text-align: center;">QTY</th>
                <th style="text-align: center;">AMOUNT</th>
            HTML;
            $start = strtotime("+1 month", $start);
        }

        $datas = $this->getData([
            'plant' => $plant,
            'period' => $period,
            'vendor' => $vendor,
            'maker' => $maker,
            'prodfam' => $prodfam,
            'part_no' => $part_no
        ], $display);
        // die(json_encode($datas));

        $this->db->select("*")->from("config");
        $config = $this->db->get()->row();
        $logo = base_url('assets/image/config/' . $config->logo);

        $this->db->select("TRIM(name) AS name")->from("user")->where("username", $this->session->username);
        $fullname = $this->db->get()->row()->name;

        $print_date = date("d M Y H:m:s");

        $def_column = ($display == 'DETAIL') ? <<<HTML
            <th style="text-align: center;" rowspan="2">Part No</th>
            <th style="text-align: center;" rowspan="2">Part Supplier</th>
            <th style="text-align: center;" rowspan="2">Part Name</th>
            <th style="text-align: center;" rowspan="2">Product Family</th>
            <th style="text-align: center;" rowspan="2">Maker Name</th>
            <th style="text-align: center;" rowspan="2">Vendor Type</th>
            <th style="text-align: center;" rowspan="2">Uom</th>
        HTML : <<<HTML
            <th style="text-align: center;" rowspan="2">Maker Name</th>
        HTML;

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
                                <small>MONTHLY REPORT QTY AND AMOUNT $display</small>
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
            <div style="float: left; font-size: 12px; text-align: left;">
                <table>
                    <tr>
                        <td>Plant</td>
                        <td>: $plant_name</td>
                    </tr>
                    <tr>
                        <td>Periode</td>
                        <td>: $periode_name</td>
                    </tr>
                </table>
            </div><br><br>
            
            <table id="datatables" border="1">
                <tr>
                    <th style="text-align: center;" rowspan="2" width="20">No</th>
                    <th style="text-align: center;" rowspan="2">Vendor Name</th>
                    $def_column
                    <th style="text-align: center;" rowspan="2">Currency</th>
                    $header
                    <th style="text-align: center;" colspan="2">Grand Total</th>
                    <th style="text-align: center;" colspan="2">AVG</th>
                </tr>
                <tr>
                    $sub_header
                    <th style="text-align: center;">QTY</th>
                    <th style="text-align: center;">AMOUNT</th>
                    <th style="text-align: center;">QTY</th>
                    <th style="text-align: center;">AMOUNT</th>
                </tr>
        HTML;
        foreach ($datas as $index => $row) {
            $no = $index + 1;
            $columns = null;
            $grand_total_qty = 0;
            $grand_total_amount = 0;
            $arr = array_values(array_replace(
                array_map(null, array_fill_keys($period, [0,0])),
                json_decode('{'.$row->qtys.'}', true)
            ));

            foreach ($arr as $key => $value) {
                $qty = $value[0];
                $amount = $value[1];

                $totals[$key][0] += $qty;
                $totals[$key][1] += $amount;

                $grand_total_qty += $qty;
                $grand_total_amount += $amount;

                $format_qty = number_format($qty, 2);
                $format_amount = number_format($amount, 2);
                $columns .= <<<HTML
                    <td style="text-align: right;">$format_qty</td>
                    <td style="text-align: right;">$format_amount</td>
                HTML;
            }
            $avg_qty = $grand_total_qty / count($period);
            $avg_amount = $grand_total_amount / count($period);

            $key_grand = count($totals) - 2;
            $totals[$key_grand][0] += $grand_total_qty;
            $totals[$key_grand][1] += $grand_total_amount;

            $key_avg = count($totals) - 1;
            $totals[$key_avg][0] += $avg_qty;
            $totals[$key_avg][1] += $avg_amount;

            $def_column = ($display == 'DETAIL') ? <<<HTML
                <td>{$row->item}</td>
                <td>{$row->vendor}</td>
                <td>{$row->name}</td>
                <td style="text-align: center;">{$row->prodfam}</td>
                <td>{$row->maker}</td>
                <td style="text-align: center;">{$row->type}</td>
                <td style="text-align: center;">{$row->uom}</td>
            HTML : <<<HTML
                <td>{$row->maker}</td>
            HTML;

            $format_grand_total_qty = number_format($grand_total_qty, 2);
            $format_grand_total_amount = number_format($grand_total_amount, 2);
            $format_avg_qty = number_format($avg_qty, 2);
            $format_avg_amount = number_format($avg_amount, 2);
            $html .= <<<HTML
                <tr>
                    <td style="text-align: center;">$no</td>
                    <td>{$row->supplier}</td>
                    $def_column
                    <td style="text-align: center;">{$row->currency}</td>
                    $columns
                    <td style="text-align: right;">$format_grand_total_qty</td>
                    <td style="text-align: right;">$format_grand_total_amount</td>
                    <td style="text-align: right;">$format_avg_qty</td>
                    <td style="text-align: right;">$format_avg_amount</td>
                </tr>
            HTML;
        }

        $total = null;
        foreach ($totals as $key => $value) {
            $qty = $value[0];
            $amount = $value[1];

            $format_qty = number_format($qty, 2);
            $format_amount = number_format($amount, 2);
            $total .= <<<HTML
                <th style="text-align: right;">$format_qty</th>
                <th style="text-align: right;">$format_amount</th>
            HTML;
        }

        $colspan = ($display == 'DETAIL') ? 9 : 3;
        $html .= <<<HTML
            <tr>
                <th></th>
                <th colspan="$colspan">Total</th>
                $total
            </tr>
        HTML;

        echo $html.'</table></body></html>';
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

            $style_total = array_merge(['font' => ['bold' => true]], $style_row);
            $style_col = $style_total; $style_col['alignment']['horizontal'] = 'center';

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $period_start = strtotime(base64_decode($this->input->get('period_start')).'-01');
            $period_end = strtotime(base64_decode($this->input->get('period_end')).'-01');
            $display = strtoupper(base64_decode($this->input->get('display')));
            $plant = base64_decode($this->input->get('plant'));
            $vendor = base64_decode($this->input->get('vendor'));
            $maker = base64_decode($this->input->get('maker'));
            $prodfam = base64_decode($this->input->get('prodfam'));
            $part_no = base64_decode($this->input->get('part_no'));

            $this->db->distinct()->select("TRIM(name) AS name")->from("plants")->where("number", $plant);
            $plant_name = $this->db->get()->row()->name;
            $periode_name = strtoupper(date('F Y', $period_start)).' - '.strtoupper(date('F Y', $period_end));

            $title = 'MONTHLY REPORT QTY AND AMOUNT '.$display;

            $columns = [['No', 'Vendor Name'],['', '']];
            $header = ($display == 'DETAIL') ? [
                'Part No',
                'Part Supplier',
                'Part Name',
                'Product Family',
                'Maker Name',
                'Vendor Type',
                'Uom',
                'Currency'
            ] : ['Maker Name', 'Currency'];
            foreach ($header as $key => $value)
                { array_push($columns[0], $value); }

            for ($i=0; $i < count($header); $i++)
                { array_push($columns[1], ''); }

            $totals = [[0, 0], [0, 0]];
            $period = [];
            $start = $period_start;
            while ($start <= $period_end) {
                array_push($period, date('Ym', $start));
                array_push($totals, [0, 0]);

                array_push($columns[0], strtoupper(date('M-y', $start)));
                array_push($columns[0], '');
                array_push($columns[1], 'QTY');
                array_push($columns[1], 'AMOUNT');

                $start = strtotime("+1 month", $start);
            }

            array_push($columns[0], 'Grand Total');
            array_push($columns[0], '');
            array_push($columns[0], 'AVG');
            array_push($columns[0], '');
            array_push($columns[1], 'QTY');
            array_push($columns[1], 'AMOUNT');
            array_push($columns[1], 'QTY');
            array_push($columns[1], 'AMOUNT');
            // die(json_encode($columns));

            $datas = $this->getData([
                'plant' => $plant,
                'period' => $period,
                'vendor' => $vendor,
                'maker' => $maker,
                'prodfam' => $prodfam,
                'part_no' => $part_no
            ], $display);
            // die(json_encode($datas));

            $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            $alfa_merge = '';
            $alfa_periode = '';
            $last_column = array_key_last($columns[0]);
            foreach ($columns[0] as $key => $value) {
                if ($key == 0) {
                    $spreadsheet->setCellValue($alfa.'3', 'Plant');
                    $spreadsheet->setCellValue($alfa.'4', 'Periode');
                } elseif ($key == 1) {
                    $spreadsheet->setCellValue($alfa.'3', ': '.$plant_name);
                    $spreadsheet->setCellValue($alfa.'4', ': '.$periode_name);
                } elseif ($key == ($last_column - 1)) {
                    $spreadsheet->setCellValue($alfa.'3', 'Print Date');
                    $spreadsheet->setCellValue($alfa.'4', 'Print By');
                } elseif ($key == $last_column) {
                    $style_row['alignment']['horizontal'] = 'center';

                    $spreadsheet->setCellValue('A1', $title);
                    $spreadsheet->mergeCells('A1:'.$alfa.'1');
                    $spreadsheet->getStyle('A1')->applyFromArray($style_row);
                    $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                    $spreadsheet->setCellValue($alfa.'3', ': '.date("d M Y H:m:s"));
                    $spreadsheet->setCellValue($alfa.'4', ': '.$fullname);
                }

                $spreadsheet->setCellValue($alfa.'6', $value);
                $spreadsheet->getStyle($alfa.'6')->applyFromArray($style_col);
                if (empty($value)) {
                    $spreadsheet->mergeCells($alfa_merge.'6:'.$alfa.'6');

                    if (empty($alfa_periode)) { $alfa_periode = $alfa_merge; }
                }

                $alfa_merge = $alfa;
                $alfa++;
            }

            $alfa = 'A';
            foreach ($columns[1] as $key => $value) {
                $spreadsheet->setCellValue($alfa.'7', $value);
                $spreadsheet->getStyle($alfa.'7')->applyFromArray($style_col);
                if (empty($value)) { $spreadsheet->mergeCells($alfa.'6:'.$alfa.'7'); }

                $alfa++;
            }

            $row_no = 8;
            foreach ($datas as $index => $row) {
                $row = (array) $row;
                $no = $index + 1;
                $alfa = 'A';
                $grand_qty = 0;
                $grand_amount = 0;
                $arr = array_values(array_replace(
                    array_map(null, array_fill_keys($period, [0,0])),
                    json_decode('{'.$row['qtys'].'}', true)
                ));
                unset($row['qtys']);

                $cols = [];
                foreach ($arr as $key => $value) {
                    $qty = $value[0];
                    $amount = $value[1];

                    $totals[$key][0] += $qty;
                    $totals[$key][1] += $amount;

                    $grand_qty += $qty;
                    $grand_amount += $amount;

                    $key_qty = $key.'_qty';
                    $key_amount = $key.'_amount';
                    array_push($cols, $key_qty);
                    array_push($cols, $key_amount);
                    $row[$key_qty] = number_format($qty, 2);
                    $row[$key_amount] = number_format($amount, 2);
                }
                array_push($cols, 'grand_qty');
                array_push($cols, 'grand_amount');
                $row['grand_qty'] = number_format($grand_qty, 2);
                $row['grand_amount'] = number_format($grand_amount, 2);

                $avg_qty = $grand_qty / count($period);
                $avg_amount = $grand_amount / count($period);
                array_push($cols, 'avg_qty');
                array_push($cols, 'avg_amount');
                $row['avg_qty'] = number_format($avg_qty, 2);
                $row['avg_amount'] = number_format($avg_amount, 2);

                $key_grand = count($totals) - 2;
                $totals[$key_grand][0] += $grand_qty;
                $totals[$key_grand][1] += $grand_amount;

                $key_avg = count($totals) - 1;
                $totals[$key_avg][0] += $avg_qty;
                $totals[$key_avg][1] += $avg_amount;

                $style_row['alignment']['horizontal'] = 'center';
                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style_row['alignment']['horizontal'] = 'left';
                    if (in_array($key, ['prodfam', 'type', 'uom', 'currency'])) { $style_row['alignment']['horizontal'] = 'center'; }
                    if (in_array($key, $cols)) { $style_row['alignment']['horizontal'] = 'right'; }

                    if (in_array($key, ['item', 'vendor']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
            }

            for ($i='A'; $i < $alfa_periode; $i++) {
                $alfa = $i;
                $spreadsheet->setCellValue($i.$row_no, ($i == 'B') ? 'Total' : '');
                $spreadsheet->getStyle($i.$row_no)->applyFromArray($style_total);
            }
            $spreadsheet->mergeCells('B'.$row_no.':'.$alfa.$row_no);

            $style_total['alignment']['horizontal'] = 'right';
            foreach ($totals as $index => $arr) {
                foreach ($arr as $key => $value) {
                    $spreadsheet->setCellValue($alfa_periode.$row_no, number_format($value, 2));
                    $spreadsheet->getStyle($alfa_periode.$row_no)->applyFromArray($style_total);

                    $alfa_periode++;
                }
            }

            // Rename worksheet
            $spreadsheet->setTitle('data table');

            $excel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$title.date(' (Y-m-d)').'.xls"');
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
}
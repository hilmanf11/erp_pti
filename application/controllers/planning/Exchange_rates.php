<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Exchange_rates extends CI_Controller
{
    public $json_currency = [['t' => 'USD'], ['t' => 'JPY']];
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
            $this->load->view('planning/exchange_rates', ['data_currency' => $this->json_currency]);
        } else { redirect('error_access'); }
    }

    public function datatables($act = '')
    {
        $datas = $this->db->select("*, 'IDR' AS currency_to")->from("exchange_rates")->order_by("start_date DESC")->get()->result();

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
                                    <small>Exchange Rates</small>
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
                        <th style="text-align: center;">Start Date</th>
                        <th style="text-align: center;">Ending Date</th>
                        <th style="text-align: center;">Currency From</th>
                        <th style="text-align: center;">Currency To</th>
                        <th style="text-align: center;">Selling</th>
                        <th style="text-align: center;">Buying</th>
                        <th style="text-align: center;">Middle</th>
                    </tr>
            HTML;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $selling = 'Rp '.number_format($row->selling, 2, ',', '.');
                $buying = 'Rp '.number_format($row->buying, 2, ',', '.');
                $middle = 'Rp '.number_format($row->middle, 2, ',', '.');

                $html .= <<<HTML
                    <tr>
                        <td style="text-align: center;">$no</td>
                        <td style="text-align: center;">{$row->start_date}</td>
                        <td style="text-align: center;">{$row->end_date}</td>
                        <td style="text-align: center;">{$row->currency}</td>
                        <td style="text-align: center;">{$row->currency_to}</td>
                        <td style="text-align: right; font-weight: bold;">$selling</td>
                        <td style="text-align: right; font-weight: bold;">$buying</td>
                        <td style="text-align: right; font-weight: bold;">$middle</td>
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

            $style_col = array_merge(['font' => ['bold' => true]], $style_row);
            $style_col['alignment']['horizontal'] = 'center';

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $columns = ['No', 'Start Date', 'Ending Date', 'Currency From', 'Currency To', 'Selling', 'Buying', 'Middle'];
            $last_index = count($columns) - 1;
            $title = 'Exchange Rates';

            $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $alfa = 'A';
            foreach ($columns as $key => $value) {
                if ($key == $last_index) {
                    $style_row['alignment']['horizontal'] = 'center';

                    $spreadsheet->setCellValue('A1', $title);
                    $spreadsheet->mergeCells('A1:'.$alfa.'1');
                    $spreadsheet->getStyle('A1')->applyFromArray($style_row);
                    $spreadsheet->getStyle('A1:'.$alfa.'1')->getFont()->setSize(20);

                    $spreadsheet->setCellValue($alfa.'3', 'Print Date : ' . date("d M Y H:m:s"));
                    $spreadsheet->setCellValue($alfa.'4', 'Print By : ' . $fullname);
                }

                $spreadsheet->setCellValue($alfa.'6', $value);
                $spreadsheet->getStyle($alfa.'6')->applyFromArray($style_col);

                $alfa++;
            }

            $id = base64_decode($this->input->post('id'));
            $datas = $this->db->select("start_date, end_date, currency, 'IDR' AS currency_to, selling, buying, middle")
            ->from("exchange_rates")->order_by("start_date DESC")->get()->result();

            $row_no = 7;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';
                $row->selling = 'Rp '.number_format($row->selling, 2, ',', '.');
                $row->buying = 'Rp '.number_format($row->buying, 2, ',', '.');
                $row->middle = 'Rp '.number_format($row->middle, 2, ',', '.');

                $style_row['alignment']['horizontal'] = 'center';
                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style_row['alignment']['horizontal'] = 'left';
                    if (in_array($key, ['start_date', 'end_date', 'currency', 'currency_to'])) { $style_row['alignment']['horizontal'] = 'center'; }
                    if (in_array($key, ['selling', 'buying', 'middle'])) { $style_row['alignment']['horizontal'] = 'right'; }

                    if (in_array($key, ['']))
                        { $spreadsheet->setCellValueExplicit($alfa.$row_no, $value, DataType::TYPE_STRING); }
                    else { $spreadsheet->setCellValue($alfa.$row_no, $value); }
                    $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row);
                    $spreadsheet->getColumnDimension($alfa)->setAutoSize(true);
                }

                $row_no++;
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

    public function create()
    {
        $result = ['stat' => ''];
        $send_data = array_map('base64_decode', $this->input->post());

        $stat = $this->db->where("currency", $send_data['currency'])
            ->group_start()
                ->where("'".$send_data['start_date']."' BETWEEN start_date AND end_date")
                ->or_where("'".$send_data['end_date']."' BETWEEN start_date AND end_date")
            ->group_end()
        ->get("exchange_rates")->num_rows() > 0;
        if ($stat) { $result['stat'] = 'Data is available'; }
        else {
            $this->db->trans_start();
            $this->api->create("exchange_rates", $send_data, 'ER');
            $stat = $this->db->where($send_data)->get('exchange_rates')->num_rows() > 0;
            if (!$stat) {
                $result['stat'] = 'Failed';
                $result['info'] = $this->db->error()['message'];
                // $result['stat'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function update()
    {
        $result = ['stat' => ''];
        $send_data = array_map('base64_decode', $this->input->post());
        $where = ['id' => $send_data['id']];
        unset($send_data['id']);

        $stat = $this->db->where("id !=", $where['id'])
            ->where("currency", $send_data['currency'])
            ->group_start()
                ->where("'".$send_data['start_date']."' BETWEEN start_date AND end_date")
                ->or_where("'".$send_data['end_date']."' BETWEEN start_date AND end_date")
            ->group_end()
        ->get("exchange_rates")->num_rows() > 0;
        if ($stat) { $result['stat'] = 'Data is available'; }
        else {
            $this->db->trans_start();
            $this->api->update('exchange_rates', $where, $send_data);
            $stat = $this->db->where($where)->get('exchange_rates')->num_rows() > 0;

            if (!$stat) {
                $result['stat'] = 'Failed';
                $result['info'] = $this->db->error()['message'];
                // $result['info'] = $this->db->last_query();
                $this->db->trans_rollback();
            } else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function delete()
    {
        $result = ['stat' => ''];
        $ids = explode(',', array_map('base64_decode', $this->input->post())['data']);

        $this->db->trans_start();

        foreach ($ids as $key => $value) {
            $this->api->delete('exchange_rates', ['id' => $value]);
            if ($this->db->where('id', $value)->get('exchange_rates')->num_rows() != 0) {
                $result['stat'] = 'Failed';
                $result['info'] = $this->db->error()['message'];
                break;
            }
        }

        if (empty($result['stat'])) { $this->db->trans_rollback(); }
        else { $this->db->trans_commit(); }

        echo json_encode($result);
    }
}

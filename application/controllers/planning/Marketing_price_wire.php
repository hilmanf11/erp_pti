<?php
defined('BASEPATH') or exit('No direct script access allowed');

require('./excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Marketing_price_wire extends CI_Controller
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
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);
            $this->load->view('planning/marketing_price_wire');
        } else {
            redirect('error_access');
        }
    }

    public function readRanges()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
    	$result = [];
    	$data = [];

        $this->db->select("DISTINCT TRIM(data_lme) AS data")->from("lme_prices");
        if (!empty($post)) { $this->db->like('data_lme', $post); }
        $this->db->order_by("data");
        $get = $this->db->get()->result_object();
        foreach ($get as $key => $value) {
        	$datas = json_decode($value->data);
        	foreach ($datas as $key2 => $value2) { array_push($data, $value2->range); }
        }

        foreach ($data as $key => $value) { array_push($result, ['range' => $value]); }

        echo json_encode($result);
    }

    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(a.ven_id) AS id, TRIM(b.name) AS supplier")
        ->from("lme_prices a")->join('suppliers b', 'a.ven_id = b.number');
        if (!empty($post)) { $this->db->like('a.ven_id', $post); }
        $this->db->order_by("supplier");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $this->db->select("DISTINCT TRIM(item_id) AS item")->from("lme_prices");
        if (!empty($post)) { $this->db->like('item_id', $post); }
        $this->db->order_by("item");
        $get = $this->db->get()->result_object();

        echo json_encode($get);
    }

    public function generate()
    {
        $supplier = base64_decode($this->input->post('supplier'));
        $range = base64_decode($this->input->post('range'));
        $item = base64_decode($this->input->post('item'));
        $result = ['stat' => 'ok'];
        $process = [];

        $this->db->distinct()->select(
        	 "TRIM(a.item_id) AS item,"
        	."TRIM(a.ven_id) AS supplier,"
        	."COALESCE(TRIM(b.type),'') AS type,"
        	."TRIM(a.maker_code) AS maker,"
        	."TRIM(a.data_lme) AS lme,"
        	."COALESCE(TRIM(c.percentage),0) AS duty,"
        	."COALESCE(TRIM(d.percentage),0) AS insurance,"
        	."COALESCE(TRIM(e.percentage),0) AS transport"
        )->from("lme_prices a")
			->join("suppliers b", "a.ven_id = b.number")
			->join("mst_import_duty c", "b.id = c.supplier_id AND a.item_id = c.item_id", "left")
			->join("mst_assurance d", "b.type = d.type")
			->join("mst_transportation e", "b.type = e.type");
		if (!empty($supplier)) { $this->db->where("a.ven_id", $supplier); }
		if (!empty($range)) { $this->db->like("a.data_lme", $range); }
		if (!empty($item)) { $this->db->where("a.item_id", $item); }
		$this->db->order_by('item');
		$get = $this->db->get()->result_object();
        // die($this->db->last_query());
		// die(var_dump($get));
		foreach ($get as $index => $row) {
			$ranges = json_decode($row->lme);
			foreach ($ranges as $key => $value) { array_push($process, [
				"item_id" => $row->item,
				"supp_id" => $row->supplier,
				"type" => $row->type,
				"maker_code" => $row->maker,
				"ranges" => $value->range,
				"price" => $value->price,
				"import_duty" => $row->duty,
				"insurance" => $row->insurance,
				"transportation" => $row->transport
			]); }
		}

		$this->db->trans_begin();
		$ids = $this->db->distinct()->select('TRIM(id) AS id')->from('marketing_price_wire')->get()->result_object();
		foreach ($ids as $key => $value) {
			$delete = json_decode($this->api->delete('marketing_price_wire', ['id' => $value->id]));
			if (!isset($delete->theme)) { $result['stat'] = 'Failed generate!'; break; }
		}

		if ($result['stat'] == 'ok') {
			foreach ($process as $key => $value) {
				$insert = json_decode($this->api->create('marketing_price_wire', $value, 'MPW', 'marketing_price_wire'));
				if (!isset($insert->theme)) { $result['stat'] = 'Failed generate!'; break; }
			}
		}
		if ($result['stat'] != 'ok') { $this->db->trans_rollback(); }
		else { $this->db->trans_commit(); }

        echo json_encode($result);
    }

    public function datatables($act = '')
    {
        $supplier = base64_decode($this->input->post('supplier'));
        $range = base64_decode($this->input->post('range'));
        $item = base64_decode($this->input->post('item'));
        $datas = [];

        $this->db->distinct()->select(
        	 "TRIM(a.item_id) AS item,"
        	."TRIM(a.supp_id) AS supp_id,"
        	."COALESCE(TRIM(b.name),'') AS supp_name,"
        	."TRIM(a.type) AS supp_type,"
        	."TRIM(a.maker_code) AS maker_id,"
        	."COALESCE(TRIM(c.name),'') AS maker_name,"
        	."TRIM(a.ranges) AS ranges,"
        	."a.price,"
        	."a.import_duty AS duty,"
        	."a.insurance AS insurance,"
        	."a.transportation AS transport,"
        	."0 AS total"
        )->from("marketing_price_wire a")
        	->join("suppliers b", "a.supp_id = b.number")
        	->join("mst_maker c", "a.maker_code = c.number");
		if (!empty($supplier)) { $this->db->where("a.supp_id", $supplier); }
		if (!empty($range)) { $this->db->where("a.ranges", $range); }
		if (!empty($item)) { $this->db->where("a.item_id", $item); }
        $this->db->order_by("item");
        $get = $this->db->get()->result_object();
        foreach ($get as $index => $row) {
            $row->price = floatval($row->price);
        	$row->duty = floatval(number_format(floatval($row->duty) * $row->price, 4));
        	$row->insurance = floatval(number_format(floatval($row->insurance) * $row->price, 4));
        	$row->transport = floatval(number_format(floatval($row->transport) * $row->price, 4));
        	$row->total = floatval(number_format($row->price + $row->duty + $row->insurance + $row->transport, 4));
        	array_push($datas, $row);
        }

        if ($act == 'print') {
            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $this->db->select('TRIM(name) AS name')->from('user')->where('username', $this->session->username);
            $fullname = $this->db->get()->row()->name;

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#datatables {border-collapse: collapse;width: 100%;font-size: 12px;}#datatables td, #datatables th {border: 1px solid #ddd;padding: 2px;}#datatables tr:nth-child(even){background-color: #f2f2f2;}#datatables tr:hover {background-color: #ddd;}#datatables th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <small>Marketing Price Wire</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $fullname . '  
                    </div>
                </center>
                <br><br><br>
                
                <table id="datatables" border="1">
                    <tr>
                        <th width="20">No</th>
                        <th>Item ID</th>
                        <th>Supplier ID</th>
                        <th>Supplier Name</th>
                        <th>Supplier Type</th>
                        <th>Maker Code</th>
                        <th>Wire Name Maker</th>
                        <th>LME Range</th>
                        <th>Price</th>
                        <th>Import Duty</th>
                        <th>Insurance</th>
                        <th>Transportation</th>
                        <th>Total Price</th>
                    </tr>';
            $no = 1;
            foreach ($datas as $key => $value) {
                $html .= '  <tr>
                    <td align="center">' . ($key + 1) . '</td>
                    <td>' . $value->item . '</td>
                    <td>' . $value->supp_id . '</td>
                    <td>' . $value->supp_name . '</td>
                    <td align="center">' . $value->supp_type . '</td>
                    <td>' . $value->maker_id . '</td>
                    <td>' . $value->maker_name . '</td>
                    <td>' . $value->ranges . '</td>
                    <td align="right">' . $value->price . '</td>
                    <td align="right">' . $value->duty . '</td>
                    <td align="right">' . $value->insurance . '</td>
                    <td align="right">' . $value->transport . '</td>
                    <td align="right">' . $value->total . '</td>
                </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
        } else {
            $result = array('total' => count($datas), 'rows' => $datas);
            echo json_encode($result);
        }
    }

    public function excel()
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
                ->setCategory('PT ASKARA TEHNO INDONESIA');

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
                    'horizontal' => 'left', // Set text jadi ditengah secara horizontal (center)
                    'vertical' => 'center' // Set text jadi di tengah secara vertical (middle)
                ),
                'borders' => array(
                    'top' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN), // Set border top dengan garis tipis
                    'right' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN),  // Set border right dengan garis tipis
                    'bottom' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN), // Set border bottom dengan garis tipis
                    'left' => array('borderStyle'  => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN) // Set border left dengan garis tipis
                )
            );

            //SHEET 1 ------------------------------------------------------------------------------------------------------------
            //Coloumn
            $columns = ['No', 'Item ID', 'Supplier ID', 'Supplier Name', 'Supplier Type', 'Maker Code', 'Wire Name Maker', 'LME Range', 'Price', 'Import Duty', 'Insurance', 'Transportation', 'Total Price'];
            $last_index = count($columns) - 1;
            $title = 'Marketing Price Wire';

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

            $supplier = base64_decode($this->input->post('supplier'));
            $range = base64_decode($this->input->post('range'));
            $item = base64_decode($this->input->post('item'));
            $datas = [];

            $this->db->distinct()->select(
                 "TRIM(a.item_id) AS item,"
                ."TRIM(a.supp_id) AS supp_id,"
                ."COALESCE(TRIM(b.name),'') AS supp_name,"
                ."TRIM(a.type) AS supp_type,"
                ."TRIM(a.maker_code) AS maker_id,"
                ."COALESCE(TRIM(c.name),'') AS maker_name,"
                ."TRIM(a.ranges) AS ranges,"
                ."a.price,"
                ."a.import_duty AS duty,"
                ."a.insurance AS insurance,"
                ."a.transportation AS transport,"
                ."0 AS total"
            )->from("marketing_price_wire a")
                ->join("suppliers b", "a.supp_id = b.number")
                ->join("mst_maker c", "a.maker_code = c.number");
            if (!empty($supplier)) { $this->db->where("a.supp_id", $supplier); }
            if (!empty($range)) { $this->db->where("a.ranges", $range); }
            if (!empty($item)) { $this->db->where("a.item_id", $item); }
            $this->db->order_by("item");
            $get = $this->db->get()->result_object();
            foreach ($get as $index => $row) {
                $row->duty = floatval($row->duty) * floatval($row->price);
                $row->insurance = floatval($row->insurance) * floatval($row->price);
                $row->transport = floatval($row->transport) * floatval($row->price);
                $row->total = $row->duty + $row->insurance + $row->transport;
                array_push($datas, $row);
            }

            $row_no = 7;
            foreach ($datas as $index => $row) {
                $no = $index + 1;
                $alfa = 'A';

                $style_row['alignment']['horizontal'] = 'center';
                $spreadsheet->setCellValue($alfa.$row_no, $no);
                $spreadsheet->getStyle($alfa.$row_no)->applyFromArray($style_row);

                foreach ($row as $key => $value) {
                    $alfa++;

                    $style_row['alignment']['horizontal'] = 'left';
                    if (in_array($key, ['supp_type'])) { $style_row['alignment']['horizontal'] = 'center'; }
                    if (in_array($key, ['price','duty','insurance','transport','total']))
                        { $style_row['alignment']['horizontal'] = 'right'; }

                    if (in_array($key, ['item_id', 'maker_id']))
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
            header('Content-Disposition: attachment;filename="'.$title.'.xls"');
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
?>
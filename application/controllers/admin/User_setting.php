<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_setting extends CI_Controller
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
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $this->load->view('template/header');
            $this->load->view('admin/user_setting');
        } else {
            redirect('error_access');
        }
    }

    public function getuser()
    {
        $post = isset($_POST['q']) ? array("name" => $_POST['q']) : $this->input->get();
        $send = $this->api->reads('user', $post, ["deleted_is" => 0, "actived_is" => 0]);
        echo json_encode($send);
    }

    function has_child($id)
    {
        $rs     = $this->db->query("SELECT count(*) as total FROM menu WHERE parent_id = '$id'");
        $row    = $rs->row();
        return $row->total > 0 ? true : false;
    }

    public function datatables()
    {
        if ($this->input->get('id_user')) {
            $id     = $_POST['id'];
            $nama   = $this->input->get('id_user');
            $result = array();

            if ($id === "0") {
                $rs     = $this->db->query("SELECT count(*) as total from menu where parent_id=''");
                $row    = $rs->row();
                $result['total'] = $row->total;

                $query  = $this->db->query("SELECT a.m_view,a.m_add,a.m_edit,a.m_delete,a.m_upload,a.m_download,a.m_print,a.m_excel,b.v_view, b.v_add,b.v_edit,b.v_delete,b.v_upload,b.v_download,b.v_print,b.v_excel, c.name, c.id, b.id as id_user_set 
                                        FROM menu_setting a 
                                        JOIN user_setting b on a.id_menu = b.id_menu 
                                        JOIN menu c on a.id_menu = c.id 
                                        WHERE b.id_user = '$nama' and c.parent_id='' ORDER BY c.sort asc");
                $items = array();
                foreach ($query->result_array() as $data) {
                    $data['state'] = $this->has_child($data['id']) ? 'closed' : 'open';
                    array_push($items, $data);
                }

                $result["rows"] = $items;
            } else {
                $query  = $this->db->query("SELECT a.m_view,a.m_add,a.m_edit,a.m_delete,a.m_upload,a.m_download,a.m_print,a.m_excel,b.v_view, b.v_add,b.v_edit,b.v_delete,b.v_upload,b.v_download,b.v_print,b.v_excel, c.name, c.id, b.id as id_user_set
                                        FROM menu_setting a 
                                        JOIN user_setting b on a.id_menu = b.id_menu 
                                        JOIN menu c on a.id_menu = c.id 
                                        WHERE b.id_user = '$nama' and c.parent_id='$id' ORDER BY c.sort asc");
                $items = array();
                foreach ($query->result_array() as $data) {
                    $data['state'] = $this->has_child($data['id']) ? 'closed' : 'open';
                    array_push($result, $data);
                }
            }

            echo json_encode($result);
        }
    }

    public function create()
    {
        if (!empty($this->session->username)) {
            if ($this->input->post()) {
                $post = $this->input->post();
                $sql = $this->db->query("SELECT * from menu_setting order by id_menu asc");
                foreach ($sql->result_array() as $data) {

                    //Ambil data yang belum terdaftar di setting user
                    $id_menu    = $data['id_menu'];
                    $sql_cek2   = $this->db->query("SELECT * FROM user_setting WHERE id_menu = '$id_menu' and id_user = '$post[id_user]'");
                    $row_cek2   = $sql_cek2->num_rows();

                    if ($row_cek2 > 0) {
                        //jika data sudah ada maka tidak ada proses
                    } else {
                        //Jika data belum ada maka terjadi insert data di setting user
                        $value  = array(
                            'id_user'       => $post['id_user'],
                            'id_menu'       => $id_menu
                        );

                        $send   = $this->api->create('user_setting', $value, "USRSET", "SETUSER");
                    }
                }
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Your Session has been Expired");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id     = base64_decode($this->input->get('id'));
            $post   = $this->input->post();
            $send   = $this->api->update('user_setting', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=user_setting_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $name = $this->input->get('name');
        $this->db->select('a.*, b.name as menu_name');
        $this->db->from('user_setting a');
        $this->db->join('menu b', 'a.id_menu = b.id', 'join');
        $this->db->where('a.deleted_is', 0);
        $this->db->like('b.name', $name);
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . base_url('assets/image/config/' . $config->logo) . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>USER SETTING</small>
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
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Menu Name</th>
                <th>View</th>
                <th>Add</th>
                <th>Edit</th>
                <th>Delete</th>
                <th>Upload</th>
                <th>Download</th>
                <th>Print</th>
                <th>Excel</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['menu_name'] . '</td>
                        <td>' . $data['m_view'] . '</td>
                        <td>' . $data['m_add'] . '</td>
                        <td>' . $data['m_edit'] . '</td>
                        <td>' . $data['m_delete'] . '</td>
                        <td>' . $data['m_upload'] . '</td>
                        <td>' . $data['m_download'] . '</td>
                        <td>' . $data['m_print'] . '</td>
                        <td>' . $data['m_excel'] . '</td>
                    </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}

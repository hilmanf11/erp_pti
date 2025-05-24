<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
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

    public function macAddress()
    {
        //Cara mudah dan sederhana mendapatkan mac address  
        // Turn on output buffering  
        ob_start();
        //mendapatkan detail ipconfing menggunakan CMD  
        system('ipconfig /all');
        // mendapatkan output kedalam variable  
        $mycom = ob_get_contents();
        // membersihkan output buffer  
        ob_clean();
        $findme = "Physical";
        // mencari perangkat fisik | menemukan posisi text perangkat fisik  
        //Search the "Physical" | Find the position of Physical text  
        $pmac = strpos($mycom, $findme);
        // mendapatkan alamat peragkat fisik  
        $mac = substr($mycom, ($pmac + 36), 17);
        //menampilkan Mac Address  
        return $mac;
    }

    public function index()
    {
        if ($this->session->username == "") {
            if ($this->input->post()) {
                $post = $this->input->post();
                $this->db->select('*');
                $this->db->from('user');
                $this->db->where('deleted_is', 0);
                $this->db->where($post);
                $data = $this->db->get()->result_object();

                if (count($data) > 0) {
                    if ($data[0]->actived_is == 1) {
                        $data['message'] = '<div style="background:red; color:white; padding:10px; font-size:10px;"><strong>Not Active</strong><br>Your account is not active, please check your email or contact the admin</div>';
                        $this->load->view('login', $data);
                    } else {
                        $session = array(
                            "id" => $data[0]->id,
                            "name" => $data[0]->name,
                            "username" => $data[0]->username,
                            "api_key" => $data[0]->api_key
                        );

                        $macAdress = $this->macAddress();
                        $ip_address = $this->input->ip_address();
                        $logUsername = $this->api->reads('log_user', ["trans_date" => date("Y-m-d")], ["username" => $data[0]->username]);
                        if (count($logUsername) > 0) {
                            // if ($logUsername[0]->ip_address == $ip_address) {
                                $this->api->log("Login", json_encode($session), "login", $data[0]->api_key);
                                $this->session->set_userdata($session);
                                redirect('home');
                            // } else {
                            //     $data['message'] = '<div style="background:orange; color:white; padding:10px; font-size:10px;"><strong>Already in Use</strong><br>Your account is already in use by another computer, if you want to switch your account, please logout first on your previous computer</div>';
                            //     $this->load->view('login', $data);
                            // }
                        } else {
                            $this->db->insert("log_user", [
                                "ip_address" => $ip_address,
                                "mac_address" => $macAdress,
                                "username" => $data[0]->username,
                                "trans_date" => date("Y-m-d H:i:s"),
                                "status" => "ACTIVE"
                            ]);

                            $this->api->log("Login", json_encode($session), "login", $data[0]->api_key);
                            $this->session->set_userdata($session);
                            redirect('home');
                        }
                    }
                } else {
                    $data['message'] = '<div style="background:red; color:white; padding:10px; font-size:10px;"><strong>Failed Login!</strong><br>Your Username and Password is wrong, please try again!!</div>';
                    $this->load->view('login', $data);
                }
            } else {
                $data['message'] = "";
                $this->load->view('login', $data);
            }
        } else {
            redirect('home');
        }
    }

    public function logout()
    {
        $username = $this->session->username;
        $this->db->delete('log_user', ["username" => $username]);
        $this->session->unset_userdata('id');
        $this->session->unset_userdata('name');
        $this->session->unset_userdata('username');
        $this->session->unset_userdata('api_key');
        redirect('login');
    }
}

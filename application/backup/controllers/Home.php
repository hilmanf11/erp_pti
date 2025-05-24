<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
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
            $username = $this->session->username;

            $this->db->select('b.*');
            $this->db->from('logins a');
            $this->db->join('user b', 'a.username = b.username');
            $this->db->where('b.status_is', 0);
            $this->db->where_not_in('b.username', $username);
            $this->db->like('a.created_date', date('Y-m-d'));
            $this->db->order_by('b.name', 'ASC');
            $logins = $this->db->get()->result_object();

            $config = $this->api->read('config');
            $profiles = $this->api->read("user", [], ["username" => $username]);

            if ($profiles->avatar != "") {
                $profile_img = $profiles->avatar;
            } else {
                $profile_img = base_url('assets/image/users/default.png');
            }

            $data['users'] = $logins;
            $data['profile'] = $profiles;
            $data['profile_img'] = $profile_img;
            $data['config'] = $config;

            $this->load->view('template/header');
            $this->load->view('home', $data);
        } else {
            redirect('login');
        }
    }

    public function menus()
    {
        if ($this->session->username != "") {
            $username = $this->session->username;
            $searchName = $this->input->get('name');
            
            $this->db->select('a.*');
            $this->db->from('menu a');
            $this->db->join('user_setting b', 'a.id = b.id_menu');
            $this->db->join('user c', 'b.id_user = c.username');
            $this->db->where('c.username', $username);
            $this->db->where('b.v_view', 1);
            $this->db->like('a.name', $searchName);
            $this->db->order_by('a.sort', 'ASC');
            $menus = $this->db->get()->result_array();

            die(json_encode($menus));
        } else {
            show_error("Session Expired");
        }
    }

    public function updateProfile()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $avatar = $this->api->upload('avatar', ["jpg", "png", "jpeg"], 'assets/image/users/', ["username" => $post['username']], "user", "avatar");

            if ($post['password'] == "") {
                $postFinal = array(
                    "name" => $post['name'],
                    "username" => $post['username'],
                    "email" => $post['email'],
                    "phone" => $post['phone'],
                    "position" => $post['position'],
                    "theme" => $post['theme'],
                    "avatar" => $avatar,
                );
            } else {
                $postFinal = array(
                    "name" => $post['name'],
                    "username" => $post['username'],
                    "email" => $post['email'],
                    "phone" => $post['phone'],
                    "position" => $post['position'],
                    "theme" => $post['theme'],
                    "password" => $post['password'],
                    "avatar" => $avatar,
                );
            }

            $send = $this->api->update('user', ["username" => $post['username']], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function chats()
    {
        if ($this->input->post()) {
            $to_users_id = $this->input->post('to_users_id');
            $from_users_id = $this->session->id;

            $this->api->update("chats", ["from_users_id" => $to_users_id, "to_users_id" => $from_users_id], ["status" => 1]);

            $chats = $this->api->query("SELECT * FROM chats WHERE (to_users_id='$to_users_id' and from_users_id = '$from_users_id') or (to_users_id='$from_users_id' and from_users_id = '$to_users_id')");
            foreach ($chats as $chat) {
                if ($chat->to_users_id == $to_users_id) {
                    echo '  <div class="alert alert-warning p-2" role="alert">
                                ' . $chat->messages . '
                            </div>';
                } else {
                    echo '  <div class="alert alert-info p-2" style="text-align: right;" role="alert">
                                ' . $chat->messages . '
                            </div>';
                }
            }
        }
    }

    public function createChats()
    {
        if ($this->input->post()) {
            $from_users_id = $this->session->id;
            $to_users_id = $this->input->post('to_users_id');
            $messages = $this->input->post('messages');

            $chats = $this->api->create("chats", ["from_users_id" => $from_users_id, "to_users_id" => $to_users_id, "messages" => $messages]);
            echo json_encode($chats);
        }
    }
}

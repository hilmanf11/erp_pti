<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Email_otp extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('email');
        $this->load->model('api');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function email($email, $password)
    {
        $result = ['stat' => ''];
        $email = base64_decode($email);
        $password = base64_decode($password);

        $this->db->distinct()->select("TRIM(a.password) AS pass, TRIM(a.name) AS user, COALESCE(TRIM(b.name), '') AS supplier")
        ->from("user a")->join("suppliers b", "a.supplier_id = b.id", "left")
        ->where("a.email", $email);
        $users = $this->db->get()->row();
        if (empty($users)) { $result['stat'] = 'Email not registered!'; }
        elseif ($users->pass != $password) { $result['stat'] = 'Wrong password!'; }
        elseif (empty($users->supplier)) { $result['stat'] = "You don't have access!"; }
        else {
            $minutes = 5;
            $table = 'otp';
            $otp = rand(100000, 999999);
            $expired = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
            $data = [
                "id" => $this->api->autonumber($table, 'OTP', $table),
                "created_date" => date('Y-m-d H:i:s'),
                'expired_at' => $expired,
                'email' => $email,
                'otp' => $otp
            ];

            $this->db->trans_begin();
            if (!$this->db->insert($table, $data)) {
                $result['stat'] = 'Failed create OTP!';
                $result['info'] = $this->db->error();
            } else {
                $result['otp'] = $otp;
                $result['email'] = $email;
                $result['user'] = $users->user;
                $result['minutes'] = $minutes;
                // $this->email->initialize([
                //     'protocol' => 'smtp', // 'mail', 'sendmail', or 'smtp'
                //     'smtp_host' => 'aeconsys.com',
                //     'smtp_port' => 465,
                //     'smtp_user' => 'no-reply@aeconsys.com',
                //     'smtp_pass' => 'Hilman1196',
                //     'smtp_crypto' => 'ssl', //can be 'ssl' or 'tls' for example
                //     'mailtype' => 'html', //plaintext 'text' mails or 'html'
                //     'smtp_timeout' => '4', //in seconds
                //     'charset' => 'iso-8859-1',
                //     'wordwrap' => TRUE
                // ]);
                // $this->email->from('no-reply@aeconsys.com', 'BANSHU ELECTRIC INDONESIA');
                // $this->email->to($email);
                // $this->email->subject("OTP Verification Code [$otp]");
                // $this->email->message(<<<HTML
                //     Hello {$users->user},<br><br>
                //     Your One-Time Password (OTP) is:<br><br>
                //     <b>$otp</b><br>
                //     This code is valid for $minutes minutes and is required to complete your verification.<br><br>
                //     For security reasons, do not share this OTP with anyone.<br><br>
                //     Thank you,<br>
                //     PT. BANSHU ELECTRIC INDONESIA
                // HTML);

                // if (!$this->email->send()) {
                //     error_reporting(E_ALL);
                //     ini_set('display_errors', 1);
                //     $result['stat'] = 'Failed send email';
                //     $result['info'] = $this->email->print_debugger();
                // }
            }

            if (!empty($result['stat'])) { $this->db->trans_rollback(); }
            else { $this->db->trans_commit(); }
        }

        echo json_encode($result);
    }

    public function auth($email, $otp)
    {
        $result = ['stat' => ''];
        $otp = base64_decode($otp);
        $email = base64_decode($email);

        $this->db->distinct()->select("expired_at AS expired, is_used, id")
        ->from("otp")->where("email", $email)->where("otp", $otp);
        $otps = $this->db->get()->row();
        if (empty($otps)) { $result['stat'] = "Wrong OTP!"; }
        elseif (strtotime($otps->expired) < strtotime(date("Y-m-d H:i:s")))
            { $result['stat'] = "OTP has expired!"; }
        elseif ($otps->is_used == '1') { $result['stat'] = "OTP has been used!"; }
        else {
            if (!$this->db->where('id', $otps->id)->update('otp', [
                "updated_date" => date('Y-m-d H:i:s'),
                'is_used' => 1
            ])) { $result['stat'] = "Failed update OTP!"; }
            else {
                $this->db->distinct()->select(
                    "a.*,".
                    "COALESCE(TRIM(b.number), '') AS supp_number,".
                    "COALESCE(TRIM(b.name), '') AS supp_name,".
                    "COALESCE(TRIM(b.type), '') AS type,".
                    "COALESCE(TRIM(b.origin), '') AS origin"
                )->from("user a")->join("suppliers b", "a.supplier_id = b.id", "LEFT")->where(["a.email" => $email]);
                $result['data'] = $this->db->get()->row();
            }
        }

        echo json_encode($result);
    }

}

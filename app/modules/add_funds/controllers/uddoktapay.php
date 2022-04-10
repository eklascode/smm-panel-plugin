<?php
defined('BASEPATH') or exit('No direct script access allowed');

class uddoktapay extends MX_Controller
{
    public $tb_users;
    public $tb_transaction_logs;
    public $payment_type;
    public $currency_code;
    public $charge_fee;

    public function __construct()
    {
        parent::__construct();
        $this->tb_users            = USERS;
        $this->tb_transaction_logs = TRANSACTION_LOGS;
        $this->payment_type           = "uddoktapay";
        $this->currency_code       = (get_option("currency_code", "USD") == "") ? 'USD' : get_option("currency_code", "");
        $this->api_key = get_option("uddoktapay_api_key");
        $this->api_url = get_option("uddoktapay_api_url");
        $this->convert = (get_option("uddoktapay_convert_rate") == "") ? '85' : get_option("uddoktapay_convert_rate");
    }

    public function index()
    {
        redirect(cn('add_funds'));
    }

    /**
     *
     * Create payment
     *
     */
    public function create_payment($data)
    {
        if (!isset($data['amount'])) {
            redirect(cn('statistics'));
        }

        $amount = $data['amount'];
        if (!empty($amount) && $amount > 0) {
            $pdata = array(
                "ids"                 => ids(),
                "uid"                 => session("uid"),
                "type"                => $this->payment_type,
                "transaction_id"      => NULL,
                "amount"              => $amount,
                "status"              => 0,
                "created"             => NOW,
            );
            $this->db->insert($this->tb_transaction_logs, $pdata);
            $transaction_id = $this->db->insert_id();
            set_session("transaction_id", $transaction_id);

            $users = session('user_current_info');
            $data = [
                "full_name"         => $users['first_name'] . " " . $users['last_name'],
                "email"             => $users['email'],
                "amount"            => $amount * $this->convert,
                "metadata"          => [
                    "transaction_id"  => $transaction_id,
                    "user_id"   => session("uid")
                ],
                "redirect_url"      => cn("add_funds/success"),
                "cancel_url"        => cn("add_funds/unsuccess"),
                "webhook_url"       => cn("api/uddoktapay"),
            ];
            $result = json_decode($this->send_request($data));
            if (isset($result) && $result->status == true) {
                $this->load->view($this->payment_type . '/redirect', ['redirect_url' => $result->payment_url]);
            } else {
                redirect(cn("add_funds/unsuccess"));
            }
        } else {
            redirect(cn());
        }
    }


    /**
     * Get the response from an API request.
     * @param  string $endpoint
     * @param  array  $params
     * @param  string $method
     * @return array
     */
    public function send_request($postfields = array())
    {

        // Setup request to send json via POST.
        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "RT-UDDOKTAPAY-API-KEY: $this->api_key";

        // Contact UuddoktaPay Gateway and get URL data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

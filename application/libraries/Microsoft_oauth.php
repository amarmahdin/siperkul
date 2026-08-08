<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Microsoft_oauth {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('microsoft', TRUE);
    }

    protected function cfg($key)
    {
        return $this->CI->config->item($key, 'microsoft');
    }

    public function is_configured()
    {
        $client_id = (string) $this->cfg('microsoft_client_id');
        $secret = (string) $this->cfg('microsoft_client_secret');
        return $client_id !== ''
            && $secret !== ''
            && $secret !== 'YOUR_CLIENT_SECRET'
            && strpos($client_id, 'YOUR_') === false;
    }

    protected function tenant_url($template)
    {
        return str_replace('{tenant}', $this->cfg('microsoft_tenant_id'), $template);
    }

    public function get_authorize_url($state)
    {
        $params = array(
            'client_id'     => $this->cfg('microsoft_client_id'),
            'response_type' => 'code',
            'redirect_uri'  => $this->cfg('microsoft_redirect_uri'),
            'response_mode' => 'query',
            'scope'         => $this->cfg('microsoft_scopes'),
            'state'         => $state,
        );

        return $this->tenant_url($this->cfg('microsoft_authorize_url')) . '?' . http_build_query($params);
    }

    public function exchange_code($code)
    {
        $body = http_build_query(array(
            'client_id'     => $this->cfg('microsoft_client_id'),
            'client_secret' => $this->cfg('microsoft_client_secret'),
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->cfg('microsoft_redirect_uri'),
            'scope'         => $this->cfg('microsoft_scopes'),
        ));

        $response = $this->curl_request('POST', $this->tenant_url($this->cfg('microsoft_token_url')), $body, array(
            'Content-Type: application/x-www-form-urlencoded',
        ));

        if (empty($response['access_token'])) {
            $msg = isset($response['error_description']) ? $response['error_description'] : 'Gagal menukar kode Microsoft.';
            throw new Exception($msg);
        }

        return $response;
    }

    public function get_profile($access_token)
    {
        $response = $this->curl_request('GET', $this->cfg('microsoft_graph_me_url'), null, array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ));

        if (empty($response) || (isset($response['error']))) {
            throw new Exception('Gagal mengambil profil Microsoft.');
        }

        return $response;
    }

    protected function curl_request($method, $url, $body = null, $headers = array())
    {
        $ch = curl_init($url);
        $opts = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
        );

        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new Exception('Koneksi ke Microsoft gagal: ' . $err);
        }

        $data = json_decode($raw, TRUE);
        return is_array($data) ? $data : array();
    }
}

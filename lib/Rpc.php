<?php

class Rpc
{
    private $curl;

    public function __construct($url, $username, $password)
    {
        $this->curl = curl_init();
        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_HEADER, false);
        $this->setOpt(CURLINFO_HEADER_OUT, false);

        if (!empty($username)) {
            $auth = ['Authorization: Basic ' . base64_encode($username . ':' . $password)];
            $this->setOpt(CURLOPT_HTTPHEADER, $auth);
        }
    }

    private function setOpt($key, $value)
    {
        curl_setopt($this->curl, $key, $value);
    }

    public function __call($method, $params = [])
    {
        $method = 'supervisor.' . $method;
        $data = xmlrpc_encode_request($method, $params);
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        $response = xmlrpc_decode(curl_exec($this->curl));

        if (!$response) {
            throw new Exception('Invalid response from', 53);
        }

        if (is_array($response) && xmlrpc_is_fault($response)) {
            throw new Exception($response['faultString'], $response['faultCode']);
        }

        return $response;
    }

    public function system($method, $params = [])
    {
        $method = 'system.' . $method;
        $data = xmlrpc_encode_request($method, $params);
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        $response = xmlrpc_decode(curl_exec($this->curl));

        return $response;
    }
}

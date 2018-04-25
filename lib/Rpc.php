<?php

class Rpc
{
    /**
     * CURL object
     * @var curl
     */
    private $curl;

    /**
     * Rpc 初始化
     * 
     * @param string $url
     * @param string $username
     * @param string $password
     */
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

    /**
     * 设置curl参数
     * 
     * @param string $key
     * @param void $value
     */
    private function setOpt($key, $value)
    {
        curl_setopt($this->curl, $key, $value);
    }

    /**
     * 魔术方法动态调用
     */
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

    /**
     * 调用supervisor 系统方法
     * 
     * @return array
     */
    public function system($method, $params = [])
    {
        $method = 'system.' . $method;
        $data = xmlrpc_encode_request($method, $params);
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        $response = xmlrpc_decode(curl_exec($this->curl));

        return $response;
    }
}

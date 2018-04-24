<?php

include 'lib/Rpc.php';

class Api
{
    private $rpc = null;

    private $config = [];

    const RPC_SERVER_URL = 'http://%s:%s/RPC2';

    public function __construct()
    {
        

        $this->config = include 'config.php';
        $url = vsprintf(self::RPC_SERVER_URL, [$this->config['host'], $this->config['port']]);
        $username = $this->config['username'];
        $password = $this->config['password'];         
        $this->rpc = new Rpc($url, $username, $password);
    }

    

    public function index()
    {
        $this->auth();
        $data = [];
        $data['supervisorVerison'] = $this->rpc->getSupervisorVersion();
        $data['pid'] = $this->rpc->getPID();
        $data['state'] = $this->rpc->getState();

        return $data;
    }

    public function getAllProcessInfo()
    {
        $this->auth();
        return $this->rpc->getAllProcessInfo();
    }

    public function start()
    {
        $this->auth();
        $input = $this->input();
        $name = $input['name'];

        $result = $this->rpc->startProcess($name);
        return $result;
    }

    public function restart()
    {
        $this->auth();
        $input = $this->input();
        $name = $input['name'];

        $this->rpc->stopProcess($name);
        $result = $this->rpc->startProcess($name);
        return $result;
    }

    public function stop()
    {
        $this->auth();
        $input = $this->input();
        $name = $input['name'];

        $result = $this->rpc->stopProcess($name);
        return $result;
    }

    public function clearProcessLogs()
    {
        $this->auth();
        $input = $this->input();
        $name = $input['name'];

        $result = $this->rpc->clearProcessLogs($name);

        return $result;
    }

    public function readProcessStdoutLog()
    {
        $this->auth();
        $input = $this->input();
        $name = $input['name'];
        $length = $input['length'];
        $start = $input['start'];

        $response = $this->rpc->readProcessStdoutLog($name, $start, $length);

        $result['data'] = $response;
        $result['length'] = strlen($response);

        return $result;
    }

    public function login()
    {
        $input = $this->input();
        $username = $input['account'];
        $password = $input['password'];

        if ($username === $this->config['username'] && $password === $this->config['password']) {
            session_start();
            $_SESSION['user'] = $username;
            return true;
        }

        throw new Exception('账号信息错误', 6);
    }

    private function input()
    {
        return $input = json_decode(file_get_contents('php://input'), true);
    }

    private function auth()
    {
        session_start();

        if (empty($_SESSION['user'])) {
            throw new Exception('未登录，无访问权限', 78);
        }
    }

    public function success($data)
    {
        return [
            'code' => 0,
            'message' => 'ok',
            'timestamp' => time(),
            'data' => $data
        ];
    }

    public function error($code = 500, $message)
    {
        return [
            'code' => $code,
            'message' => $message,
            'timestamp' => time()
        ];
    }
}

$api = new Api();

try {
    $method = isset($_GET['method']) ? $_GET['method'] : 'index';
    $response = $api->success($api->$method());
} catch (Exception $exception) {
    $response = $api->error($exception->getCode(), $exception->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);

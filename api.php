<?php

include 'lib/Rpc.php';
include 'lib/Helper.php';

class Api
{
    /**
     * RPC实例
     * @var Rpc
     */
    private $rpc = null;

    /**
     * 配置信息
     * @var array
     */
    private $config = [];

    /**
     * RPC url format
     */
    const RPC_SERVER_URL = 'http://%s:%s/RPC2';

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->config = include 'config.php';
        $url = vsprintf(self::RPC_SERVER_URL, [$this->config['host'], $this->config['port']]);
        $username = $this->config['username'];
        $password = $this->config['password'];         
        $this->rpc = new Rpc($url, $username, $password);
    }

    /**
     * 返回基本的Supervisor信息
     * 
     * @return array
     */
    public function index()
    {
        $this->auth();
        $data = [];
        $data['supervisorVerison'] = $this->rpc->getSupervisorVersion();
        $data['pid'] = $this->rpc->getPID();
        $data['state'] = $this->rpc->getState();

        return $data;
    }

    /**
     * 获取所有的进程信息
     * 
     * @return array
     */
    public function getAllProcessInfo()
    {
        $this->auth();
        return $this->rpc->getAllProcessInfo();
    }

    /**
     * 启动进程
     * 
     * @return bool
     */
    public function start()
    {
        $this->auth();
        $input = Helper::input();
        $name = $input['name'];

        $result = $this->rpc->startProcess($name);
        return $result;
    }

    /**
     * 重启进程
     * 
     * @return bool
     */
    public function restart()
    {
        $this->auth();
        $input = Helper::input();
        $name = $input['name'];

        $this->rpc->stopProcess($name);
        $result = $this->rpc->startProcess($name);
        return $result;
    }

    /**
     * 停止进程
     * 
     * @return bool
     */
    public function stop()
    {
        $this->auth();
        $input = Helper::input();
        $name = $input['name'];

        $result = $this->rpc->stopProcess($name);
        return $result;
    }

    /**
     * 清除进程下的日志
     * 
     * @return bool
     */
    public function clearProcessLogs()
    {
        $this->auth();
        $input = Helper::input();
        $name = $input['name'];

        $result = $this->rpc->clearProcessLogs($name);

        return $result;
    }

    /**
     * 读取标准输出日志
     * 
     * @return array
     */
    public function readProcessStdoutLog()
    {
        $this->auth();
        $input = Helper::input();
        $name = $input['name'];
        $length = $input['length'];
        $start = $input['start'];

        $response = $this->rpc->readProcessStdoutLog($name, $start, $length);

        $result['data'] = $response;
        $result['length'] = strlen($response);

        return $result;
    }

    /**
     * 登录接口
     * 
     * @return bool
     * @throws Exception
     */
    public function login()
    {
        $input = Helper::input();
        $username = $input['account'];
        $password = $input['password'];

        if ($username === $this->config['username'] && $password === $this->config['password']) {
            session_start();
            $_SESSION['user'] = $username;
            return true;
        }

        throw new Exception('账号信息错误', 6);
    }

    /**
     * 限制访问权限
     * 
     * @return bool
     * @throws Exception
     */
    private function auth()
    {
        session_start();

        if (empty($_SESSION['user'])) {
            throw new Exception('未登录，无访问权限', 78);
        }

        return true;
    }
}

try {
    $api = new Api();
    $method = isset($_GET['method']) ? $_GET['method'] : 'index';
    $response = Helper::success($api->$method());
} catch (Exception $exception) {
    $response = Helper::error($exception->getCode(), $exception->getMessage());
}

header('Content-Type: application/json');
exit(json_encode($response));

<?php

class Helper
{
    /**
     * 输出正确结果
     * 
     * @param array $data
     * @return array
     */
    public static function success($data)
    {
        return [
            'code' => 0,
            'message' => 'ok',
            'timestamp' => time(),
            'data' => $data
        ];
    }

    /**
     * 输出错误结果
     * 
     * @param int $code
     * @param string $message
     * @return array
     */
    public static function error($code = 500, $message) 
    {
        return [
            'code' => $code,
            'message' => $message,
            'timestamp' => time()
        ];
    }

    /**
     * 获取输入
     * 
     * @return array
     */
    private function input()
    {
        return $input = json_decode(file_get_contents('php://input'), true);
    }
}

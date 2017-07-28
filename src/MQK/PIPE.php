<?php
/**
 * Created by PhpStorm.
 * User: cj
 * Date: 2017/7/26
 * Time: 下午12:49
 */

namespace MQK;


class PIPE
{
    /**
     * stream_socket_pair 生成的PIPE
     * @var integer[]
     */
    private $pipe;

    public function __construct()
    {
        $this->pipe = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    }

    public function closeImFather()
    {
        fclose($this->pipe[0]);
    }

    public function closeImSon()
    {
        // 正常运行的代码是关掉0
        fclose($this->pipe[1]);
    }


    public function get($index)
    {
        return $this->pipe[$index];
    }

    /**
     * TODO: 不处理 select
     * @return bool|null|string
     */
    public function read()
    {
        $pipe = $this->pipe[1];
        $read = [$pipe];
        $write = [];
        $exception = [];

        @stream_select($read, $write, $exception, 1.00);
        if (in_array($pipe, $read)) {
            $buffer = fgets($pipe);
            if ($buffer) {
                return $buffer;
            }
        }

        return null;
    }

    /**
     * @param string $data
     */
    public function write($data)
    {
        fwrite($this->pipe[0], $data);
    }
}
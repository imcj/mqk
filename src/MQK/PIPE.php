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

    public $dispatchedSignalInt = false;

    public function __construct()
    {
        $this->pipe = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    }

    public function closeImFather()
    {
        @fclose($this->pipe[0]);
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

    public function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        $this->last_error = compact('errno', 'errstr', 'errfile', 'errline', 'errcontext');

        // fwrite notice that the stream isn't ready
        if (strstr($errstr, 'Resource temporarily unavailable')) {
            // it's allowed to retry
            return;
        }
        // stream_select warning that it has been interrupted by a signal
        if (strstr($errstr, 'Interrupted system call')) {
            throw new \Exception("Interrupted system call");
            // it's allowed while processing signals
            return;
        }
        // raise all other issues to exceptions
        throw new \Exception($errstr, 0, $errno, $errfile, $errline);
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

        if ($this->dispatchedSignalInt) {
            exit();
        }
        set_error_handler([$this, "error_handler"]);
        try {
            stream_select($read, $write, $exception, 1.00);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            restore_error_handler();
        }

        pcntl_signal_dispatch();
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
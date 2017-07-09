<?php

namespace MQK;

class Config
{
    public static $default;

    /**
     * @var Redis主机
     */
    private $host;

    /**
     * Redis 端口
     * @var int
     */
    private $port;

    /**
     * @var string Redis密码
     */
    private $password;

    /**
     * Worker的数量
     *
     * @var int
     */
    private $workers;

    /**
     * 队列最大重试
     * @var int
     */
    private $jobMaxRetries = 3;

    /**
     * Burst模式
     *
     * Burst模式下队列处理完后程序退出
     *
     * @var bool
     */
    private $burst;

    /**
     * 安静模式，安静模式下不输出任何内容。
     *
     * @var boolean
     */
    private $quite = false;

    /**
     * Redis 集群的服务器配置
     * @var string[]
     */
    private $cluster = array();

    /**
     * 极速模式，该模式下任务失败后丢失
     *
     * @var bool
     */
    private $fast = false;

    public function __construct(
        $host,
        $port,
        $password
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->burst = false;
    }

    public function host()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function port()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function workers()
    {
        if (!$this->workers) {
            $this->workers = 50;
        }
        return $this->workers;
    }

    public function setWorkers($workers)
    {
        $this->workers = $workers;
    }

    public function jobMaxRetries()
    {
        return $this->jobMaxRetries;
    }

    public function setJobMaxRetries($jobMaxRetries)
    {
        $this->jobMaxRetries = $jobMaxRetries;
    }

    public static function defaultConfig()
    {
        if (null == self::$default) {
            self::$default = new Config(
                "127.0.0.1",
                null,
                "",
                ""
            );
        }

        return self::$default;
    }

    public function burst()
    {
        return $this->burst;
    }

    public function setBurst($burst)
    {
        $this->burst = $burst;
    }

    public function quite()
    {
        return $this->quite;
    }

    public function beQuite()
    {
        $this->quite = true;
    }

    public function cluster()
    {
        return $this->cluster;
    }

    public function setCluster($cluster)
    {
        $this->cluster = $cluster;
    }

    public function fast()
    {
        return $this->fast;
    }

    public function enableFast()
    {
        $this->fast = true;
    }
}
<?php

namespace MQK;

class Config
{
    public static $default;

    private $host;
    private $port;
    private $username;
    private $password;
    private $queueName;
    private $workers;

    private $redis;

    public function __construct(
        $host,
        $port,
        $username,
        $password,
        $queueName = "queue"
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->queueName = $queueName;
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

    public function redis()
    {
        if (null == $this->redis) {
            $this->redis = new \Redis();
            $this->redis->connect($this->host);
        }
        return $this->redis;
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
}
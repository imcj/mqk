<?php

namespace MQK;

use AD7six\Dsn\Dsn;

class Config
{
    public static $default;

    /**
     * @var string
     */
    private $redis;

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
    private $burst = false;

    /**
     * 安静模式，安静模式下不输出任何内容。
     *
     * @var boolean
     */
    private $quite = false;

    /**
     * Redis 集群的服务器DSN配置
     * @var string[]
     */
    private $cluster = array();

    /**
     * 测试任务的最大次数，每一个进程。
     * @var int
     */
    private $testJobMax = 0;

    /**
     * 极速模式，该模式下任务失败后丢失
     *
     * @var bool
     */
    private $fast = false;

    /**
     * 启动脚本路径
     *
     * @var string
     */
    private $initScript = "";

    /**
     * @var string
     */
    private $sentry;

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
            self::$default = new Config();
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

    public function setQuite($yesOrNo)
    {
        $this->quite = $yesOrNo;
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

    /**
     * @return int
     */
    public function testJobMax()
    {
        return $this->testJobMax;
    }

    /**
     * @param int $testJobMax
     */
    public function setTestJobMax(int $testJobMax)
    {
        $this->testJobMax = $testJobMax;
    }

    public function initScript()
    {
        return $this->initScript;
    }

    public function setInitScript($initScript)
    {
        $this->initScript = $initScript;
    }

    public function redis()
    {
        return $this->redis;
    }

    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    public function sentry()
    {
        return $this->sentry;
    }

    public function setSentry($sentry)
    {
        $this->sentry = $sentry;
    }
}
<?php

namespace MQK;

use MQK\Error\ErrorHandler;

class Config
{
    public static $default;

    /**
     * @var string
     */
    private $redis = "redis://127.0.0.1";

    /**
     * Worker的数量
     *
     * @var int
     */
    private $concurrency;

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
    private $bootstrap = "";

    /**
     * @var string
     */
    private $sentry;

    /**
     * @var string[]
     */
    private $queues = [];

    /**
     * @var ErrorHandler[]
     */
    private $errorHandlers = [];

    private $queuePrefix = 'queue_';

    private $defaultQueue = 'default';

    public function __construct()
    {
    }

    public function concurrency()
    {
        if (!$this->concurrency) {
            $this->concurrency = 50;
        }
        return $this->concurrency;
    }

    public function setConcurrency($concurrency)
    {
        $this->concurrency = $concurrency;
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
            self::$default->setQueues(['default']);
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

    public function bootstrap()
    {
        return $this->bootstrap;
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
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

    /**
     * @return \string[]
     */
    public function queues()
    {
        return $this->queues;
    }

    /**
     * @param \string[] $queues
     */
    public function setQueues(array $queues)
    {
        $this->queues = $queues;
    }

    /**
     * @return ErrorHandler[]
     */
    public function errorHandlers()
    {
        return $this->errorHandlers;
    }

    /**
     * @param ErrorHandler[] $errorHandlers
     */
    public function setErrorHandlers($errorHandlers)
    {
        $this->errorHandlers = $errorHandlers;
    }

    public function addErrorHandler($handler)
    {
        $this->errorHandlers[] = $handler;
    }

    /**
     * @return string
     */
    public function queuePrefix(): string
    {
        return $this->queuePrefix;
    }

    /**
     * @param string $queuePrefix
     */
    public function setQueuePrefix(string $queuePrefix)
    {
        $this->queuePrefix = $queuePrefix;
    }

    /**
     * @return string
     */
    public function defaultQueue(): string
    {
        return $this->defaultQueue;
    }

    /**
     * @param string $defaultQueue
     */
    public function setDefaultQueue(string $defaultQueue)
    {
        $this->defaultQueue = $defaultQueue;
    }


}
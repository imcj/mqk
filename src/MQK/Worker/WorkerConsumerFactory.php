<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Queue\Queue;
use MQK\Process\WorkerFactory;

class WorkerConsumerFactory implements WorkerFactory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string[]
     */
    private $queueNameList;

    private $masterId;

    /**
     * TODO: 把 config 改成需要用到的属性
     *
     * WorkerConsumerFactory constructor.
     * @param $config Config
     * @param $queues string
     */
    public function __construct(Config $config, $queueNameList, $masterId)
    {
        $this->config = $config;
        $this->queueNameList = $queueNameList;
        $this->masterId = $masterId;
    }

    /**
     * @return Worker
     */
    function create()
    {
        $worker = new WorkerConsumer($this->config, $this->queueNameList, $this->masterId);

        return $worker;
    }
}
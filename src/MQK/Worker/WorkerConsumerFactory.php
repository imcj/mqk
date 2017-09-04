<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Queue\Queue;

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

    /**
     * TODO: 把 config 改成需要用到的属性
     *
     * WorkerConsumerFactory constructor.
     * @param $config Config
     * @param $queues string
     */
    public function __construct(Config $config, $queueNameList)
    {
        $this->config = $config;
        $this->queueNameList = $queueNameList;
    }

    /**
     * @param string $masterId
     * @return Worker
     */
    function create($masterId)
    {
        $worker = new WorkerConsumer($this->config, $this->queueNameList, $masterId);

        return $worker;
    }
}
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
     * @var Queue[]
     */
    private $queues;

    /**
     * TODO: 把 config 改成需要用到的属性
     *
     * WorkerConsumerFactory constructor.
     * @param $config Config
     * @param $queues Queue[]
     */
    public function __construct(Config $config, $queues)
    {
        $this->config = $config;
        $this->queues = $queues;
    }

    /**
     * @param string $masterId
     * @return Worker
     */
    function create($masterId)
    {
        $worker = new WorkerConsumer($this->config, $this->queues, $masterId);

        return $worker;
    }
}
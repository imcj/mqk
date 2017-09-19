<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Queue\Queue;
use MQK\Process\WorkerFactory;

class WorkerConsumerFactory implements WorkerFactory
{
    private $redisDsn;

    /**
     * @var string[]
     */
    private $queueNameList;

    private $masterId;

    private $initScript;

    private $burst;

    private $fast;

    /**
     * TODO: 把 config 改成需要用到的属性
     *
     * WorkerConsumerFactory constructor.
     * @param $config Config
     * @param $queues string
     */
    public function __construct(
        $redisDsn,
        $queueNameList,
        $masterId,
        $initScript,
        $burst,
        $fast) {

        $this->redisDsn = $redisDsn;
        $this->queueNameList = $queueNameList;
        $this->masterId = $masterId;
        $this->initScript = $initScript;
        $this->burst = $burst;
        $this->fast = $fast;
    }

    /**
     * @return Worker
     */
    function create()
    {
        $worker = new WorkerConsumer(
            $this->redisDsn,
            $this->queueNameList,
            $this->masterId,
            $this->initScript,
            $this->burst,
            $this->fast
        );

        return $worker;
    }
}
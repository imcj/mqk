<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Error\ErrorHandler;
use MQK\Process\WorkerFactory;

class WorkerConsumerFactory implements WorkerFactory
{
    /**
     * @var string
     */
    private $redisDsn;

    /**
     * @var string[]
     */
    private $queueNameList;

    /**
     * @var integer
     */
    private $masterId;

    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var bool
     */
    private $burst;

    /**
     * @var bool
     */
    private $fast;

    /**
     * @var ErrorHandler[]
     */
    private $errorHandlers;

    /**
     * WorkerConsumerFactory constructor.
     *
     * @param string $redisDsn
     * @param string[] $queueNameList
     * @param integer $masterId
     * @param string $bootstrap
     * @param boolean $burst
     * @param boolean $fast
     * @param ErrorHandler[] $errorHandlers
     */
    public function __construct(
        $redisDsn,
        $queueNameList,
        $masterId,
        $bootstrap,
        $burst,
        $fast,
        $errorHandlers) {

        $this->redisDsn = $redisDsn;
        $this->queueNameList = $queueNameList;
        $this->masterId = $masterId;
        $this->bootstrap = $bootstrap;
        $this->burst = $burst;
        $this->fast = $fast;
        $this->errorHandlers = $errorHandlers;
    }

    /**
     * Factory method of class
     * @return Worker
     */
    function create()
    {
        $worker = new WorkerConsumer(
            $this->redisDsn,
            $this->queueNameList,
            $this->masterId,
            $this->bootstrap,
            $this->burst,
            $this->fast,
            $this->errorHandlers
        );

        return $worker;
    }
}
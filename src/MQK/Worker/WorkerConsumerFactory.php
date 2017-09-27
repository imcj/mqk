<?php
namespace MQK\Worker;


use MQK\Error\ErrorHandler;
use MQK\Process\WorkerFactory;

class WorkerConsumerFactory implements WorkerFactory
{
    /**
     * @var WorkerConsumerExecutor
     */
    private $executor;

    /**
     * @var string
     */
    private $bootstrap;


    public function __construct($bootstrap, WorkerConsumerExecutor $executor) {
        $this->bootstrap = $bootstrap;
        $this->executor = $executor;
    }

    /**
     * Factory method of class
     *
     * @return Worker
     */
    function create()
    {
        $worker = new WorkerConsumer(
            $this->bootstrap,
            $this->executor
        );

        return $worker;
    }
}
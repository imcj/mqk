<?php
namespace MQK\Worker;


use MQK\Error\ErrorHandler;
use MQK\Process\WorkerFactory;

class ConsumerWorkerFactory implements WorkerFactory
{
    /**
     * @var ConsumerExecutorWorker
     */
    private $executor;

    /**
     * @var string
     */
    private $bootstrap;


    public function __construct($bootstrap, ConsumerExecutorWorker $executor) {
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
        $worker = new ConsumerWorker(
            $this->bootstrap,
            $this->executor
        );

        return $worker;
    }
}
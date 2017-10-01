<?php
namespace MQK\Worker;


use MQK\Process\WorkerFactory;
use MQK\RedisProxy;

class ConsumerWorkerFactory implements WorkerFactory
{
    /**
     * @var ConsumerExecutorWorkerFactory
     */
    private $consumerExecutorWorkerFactory;

    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var RedisProxy
     */
    protected $connection;

    public function __construct(
        $bootstrap,
        $connection,
        ConsumerExecutorWorkerFactory $consumerExecutorWorkerFactory) {

        $this->bootstrap = $bootstrap;
        $this->connection = $connection;
        $this->consumerExecutorWorkerFactory = $consumerExecutorWorkerFactory;
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
            $this->connection,
            $this->consumerExecutorWorkerFactory
        );

        return $worker;
    }
}
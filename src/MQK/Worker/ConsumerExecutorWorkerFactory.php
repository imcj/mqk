<?php
namespace MQK\Worker;


use MQK\Error\ErrorHandler;
use MQK\Health\HealthReporterRedis;
use MQK\Health\WorkerHealth;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\QueueCollection;
use MQK\RedisProxy;
use MQK\Registry;
use MQK\SearchExpiredMessage;
use MQK\SerializerFactory;

class ConsumerExecutorWorkerFactory
{
    /**
     * @var integer
     */
    protected $memoryLimit;

    /**
     * @var RedisProxy
     */
    protected $connection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var MessageInvokableSyncController
     */
    protected $messageController;

    /**
     * @var SearchExpiredMessage
     */
    protected $searchExpiredMessage;

    /**
     * @var boolean
     */
    protected $burst;

    /**
     * @var boolean
     */
    protected $fast;

    /**
     * @var QueueCollection
     */
    protected $queues;

    /**
     * @var ErrorHandler[]
     */
    protected $errorHandlers;

    public function __construct(
        $burst,
        $fast,
        $memoryLimit,
        $connection,
        $regsitry,
        $queues,
        $searchExpiredMessage,
        $messagController,
        $errorHandlers) {

        $this->burst = $burst;
        $this->fast = $fast;
        $this->memoryLimit = $memoryLimit;
        $this->connection = $connection;
        $this->registry = $regsitry;
        $this->queues = $queues;
        $this->searchExpiredMessage = $searchExpiredMessage;
        $this->messageController = $messagController;
        $this->errorHandlers = $errorHandlers;
    }

    public function create()
    {
        $this->connection->connect(true);
        $health = new WorkerHealth();
        $healthRepoter = new HealthReporterRedis(
            $health,
            $this->connection,
            SerializerFactory::shared()->serializer(),
            1
        );

        $executor = new ConsumerExecutorWorker(
            $this->burst,
            $this->fast,
            $this->queues,
            $this->memoryLimit,
            $this->registry,
            $this->searchExpiredMessage,
            $this->messageController,
            $health,
            $healthRepoter,
            $this->errorHandlers
        );

        return $executor;
    }
}
<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Error\ErrorHandler;
use MQK\SearchExpiredMessage;
use MQK\Health\HealthReporterRedis;
use MQK\Health\WorkerHealth;
use MQK\Queue\Message\MessageDAO;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisProxy;
use MQK\Registry;
use MQK\SerializerFactory;

class ConsumerExecutorWorkerFactory
{
    /**
     * @var string
     */
    protected $redisDsn;

    /**
     * @var boolean
     */
    protected $burst;

    /**
     * @var boolean
     */
    protected $fast;

    /**
     * @var string
     */
    protected $queuePrefix;

    /**
     * @var string[]
     */
    protected $queues;

    /**
     * @var integer
     */
    protected $maxRetries;

    /**
     * @var ErrorHandler[]
     */
    protected $errorHandlers;

    /**
     * @var boolean
     */
    protected $isSearchExpiredMessage;

    /**
     * ConsumerExecutorWorkerFactory constructor.
     * @param $burst
     * @param $fast
     * @param $redisDsn
     * @param $queuePrefix
     * @param $queues
     * @param $maxRetries
     * @param $isSearchExpiresdMessage
     * @param $errorHandlers
     */
    public function __construct(
        $burst,
        $fast,
        $redisDsn,
        $queuePrefix,
        $queues,
        $maxRetries,
        $isSearchExpiresdMessage,
        $errorHandlers) {

        $this->burst = $burst;
        $this->fast = $fast;
        $this->redisDsn = $redisDsn;
        $this->queuePrefix = $queuePrefix;
        $this->queues = $queues;
        $this->maxRetries = $maxRetries;
        $this->isSearchExpiredMessage = $isSearchExpiresdMessage;
        $this->errorHandlers = $errorHandlers;
    }

    public function create()
    {
        $connection = new RedisProxy($this->redisDsn);
        $connection->connect(true);

        $messageDAO = new MessageDAO($connection);
        $queue = new RedisQueue($connection, $this->queuePrefix);
        $registry = new Registry($connection);
        $queues = new RedisQueueCollection($connection, $this->queues);
        $messageController = new MessageInvokableSyncController($connection, $queue, $messageDAO);

        $expiredFinder = new SearchExpiredMessage($connection, $messageDAO, $registry, $queue, $this->maxRetries);

        $health = new WorkerHealth();
        $healthRepoter = new HealthReporterRedis($health, $connection, SerializerFactory::shared()->serializer(), 1);

        $executor = new ConsumerExecutorWorker(
            $this->burst,
            $this->fast,
            $queues,
            $registry,
            $expiredFinder,
            $messageController,
            $health,
            $healthRepoter,
            $this->errorHandlers,
            $this->isSearchExpiredMessage
        );

        return $executor;
    }
}
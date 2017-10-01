<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\ExpiredFinder;
use MQK\Health\HealthReporterRedis;
use MQK\Health\WorkerHealth;
use MQK\Queue\Message\MessageDAO;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisProxy;
use MQK\Registry;
use MQK\SerializerFactory;

class WorkerConsumerExecutorFactory
{
    public function __construct()
    {
    }

    public function create()
    {
        $config = Config::defaultConfig();

        $connection = new RedisProxy($config->redis());;
        $connection->connect(true);

        $messageDAO = new MessageDAO($connection);
        $queue = new RedisQueue($connection, $config->queuePrefix());
        $registry = new Registry($connection);
        $queues = new RedisQueueCollection($connection, $config->queues());
        $messageController = new MessageInvokableSyncController($connection, $queue, $messageDAO);

        $expiredFinder = new ExpiredFinder($connection, $messageDAO, $registry, $queue, $config->retry());

        $health = new WorkerHealth();
        $healthRepoter = new HealthReporterRedis($health, $connection, SerializerFactory::shared()->serializer(), 1);

        $executor = new WorkerConsumerExecutor(
            $config->burst(),
            $config->fast(),
            $queues,
            $registry,
            $expiredFinder,
            $messageController,
            $health,
            $healthRepoter,
            $config->errorHandlers()
        );

        return $executor;
    }
}
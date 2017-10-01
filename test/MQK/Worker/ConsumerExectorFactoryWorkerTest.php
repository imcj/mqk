<?php
namespace MQK\Worker;


use MQK\Queue\Message\MessageDAO;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisTestCase;
use MQK\Registry;
use MQK\SearchExpiredMessage;

class ConsumerExectorFactoryWorkerTest extends RedisTestCase
{
    public function testCreate()
    {
        $registry = new Registry($this->connection);
        $messageDAO = new MessageDAO($this->connection);
        $queue = new RedisQueue($this->connection, 'queue_');
        $searchExpiredMessage = new SearchExpiredMessage(
            $this->connection,
            $messageDAO,
            $registry,
            $queue,
            3
        );
        $queues = new RedisQueueCollection($this->connection, ['default']);
        $messageController = new MessageInvokableSyncController(
            $this->connection,
            $queue,
            $messageDAO
        );

        $workerConsumerExecutorFactory = new ConsumerExecutorWorkerFactory(
            false,
            false,
            $this->connection,
            $registry,
            $queues,
            $searchExpiredMessage,
            $messageController,
            []
        );
        $consumerExecutor = $workerConsumerExecutorFactory->create();
        $this->assertEquals(true, true);
    }
}
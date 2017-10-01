<?php

use PHPUnit\Framework\TestCase;
use MQK\SearchExpiredMessage;
use MQK\RedisFactory;
use MQK\Queue\Message\MessageDAO;
use MQK\Registry;
use MQK\Queue\RedisQueueCollection;
use MQK\Queue\QueueFactory;

class TimeoutFinderTest extends TestCase
{

    public function testProcess()
    {
        $queueName = 'unittest';
        $jobDictionary = array(
            "id" => "test_1",
            "func" => 'MQK\Test\Calculator::sum',
            'arguments' => array(1, 1),
            'ttl' => 500,
            'queue' => $queueName
        );
        $now = time();
        $timeoutJobTTLTime = $now - $jobDictionary['ttl'];

        /**
         * @var $connection Redis
         */
        $connection = $messageDAO = $registry = $queueCollection = null;

        $redisFactory = RedisFactory::shared();
        $connection = $redisFactory->createRedis();

        $queueFactory = new QueueFactory();
        $queueList = $queueFactory->createQueues([$queueName], $connection);

        $messageDAO = new MessageDAO($connection);
        $registry = new Registry($connection);
        $queueCollection = new RedisQueueCollection($connection, $queueList);

        $connection->set($jobDictionary['id'], json_encode($jobDictionary));
        $connection->zAdd('mqk:started', $timeoutJobTTLTime, $jobDictionary['id']);
        $connection->set("job:" . $jobDictionary['id'], json_encode($jobDictionary), 6000);

        $found = $connection->zRange("mqk:started", 0, $now);
        $this->assertEquals(1, count($found));

        // TODO: 超时任务列表的查询，一次只查询有限的数据量。
        $finder = new SearchExpiredMessage($connection, $messageDAO, $registry, $queueCollection);
        $finder->process();

        $job = $queueCollection->dequeue(false);

        $found = $connection->zRange("mqk:started", 0, $now);
        $this->assertEquals(0, count($found));
    }

    public function testRetryField()
    {

    }
}
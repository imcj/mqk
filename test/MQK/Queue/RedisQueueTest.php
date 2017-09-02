<?php
namespace MQK\Queue;


use PHPUnit\Framework\TestCase;

class RedisQueueTest extends TestCase
{
    /**
     * @var \Redis
     */
    private $connection;

    public function setUp()
    {
        $this->connection = new \Redis();
        $this->connection->connect('127.0.0.1');
        $this->connection->flushAll();
    }

    public function testEnqueueMessageInvokableSync()
    {
        $queue = new RedisQueue("default", $this->connection);

        $payload = new \stdClass();
        $payload->func = '\MQK\Test\sum';
        $payload->arguments = [1, 2];

        $groupId = uniqid();
        $messageId = uniqid();

        $message = new MessageInvokableSync($groupId, $messageId, 'default', 600, $payload);
        $queue->enqueue($message);

        list($queueKey, $messageValue) = $this->connection->blPop($queue->key(), 10);
        $messageJson = json_decode($messageValue);

        $this->assertEquals($groupId, $messageJson->groupId);
        $this->assertEquals($messageId, $messageJson->id);

        $this->assertEquals($messageJson->payload, $payload);

        $this->assertTrue(true);
    }
}
<?php
namespace MQK\Queue;


use PHPUnit\Framework\TestCase;

class MessageInvokableSyncTest extends TestCase
{
    /**
     * @var \Redis
     */
    private $connection;

    public function setUp()
    {
        $this->connection = new RedisProxy('127.0.0.1');
        $this->connection->connect();
        $this->connection->flushAll();
    }

    public function testMessage()
    {
        $queue = new RedisQueue("default", $this->connection);
        $payload = new \stdClass();
        $payload->func = '\MQK\Test\sum';
        $payload->arguments = [1, 2];

        $groupId = uniqid();
        $messageId = uniqid();

        $message = new MessageInvokableSync($groupId, 1, $messageId, 'default', 'default', 600, $payload);
        $queue->enqueue($message);


    }
}
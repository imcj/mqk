<?php
namespace MQK\Queue;

use MQK\RedisTestCase;

class MessageInvokableSyncTest extends RedisTestCase
{

    public function testMessage()
    {
        $queue = new RedisQueue($this->connection, "queue_");
        $payload = new \stdClass();
        $payload->func = '\MQK\Test\sum';
        $payload->arguments = [1, 2];

        $groupId = uniqid();
        $messageId = uniqid();

        $message = new MessageInvokableSync($groupId, 1, $messageId, 'default', 'default', 600, $payload);
        $queue->enqueue("default", $message);


    }
}
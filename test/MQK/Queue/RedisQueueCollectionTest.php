<?php
namespace MQK\Queue;

use PHPUnit\Framework\TestCase;

class RedisQueueCollectionTest extends TestCase
{
    /**
     * @var RedisQueue
     */
    private $queue;

    /**
     * @var \Redis
     */
    private $connection;

    public function setUp()
    {
        $this->connection = new \Redis();
        $this->connection->connect('127.0.0.1');

        $this->queue = new RedisQueue("default", $this->connection);
        $this->connection->flushAll();
    }

    public function testDequeue()
    {
        $message = new Message(uniqid());
        $this->queue->enqueue($message);
        $queues = new RedisQueueCollection($this->connection, [$this->queue]);
        $message = $queues->dequeue();

        $this->assertInstanceOf(Message::class, $message);
    }

    public function testEnqueueEvent()
    {
        $event = new ComplexEvent(1);
        $messageFactory = new MessageFactory();
        $message = $messageFactory->messageWithEvent($event);
        $this->queue->enqueue($message);

        $assertYes = false;
        MessageEventBus::shared()->addListener(ComplexEvent::NAME, function($event) use (&$assertYes) {
            $assertYes = true;
        });

        $queues = new RedisQueueCollection($this->connection, [$this->queue]);
        $messageEvent = $queues->dequeue();
        $messageEvent();

        $this->assertTrue($assertYes);
    }
}
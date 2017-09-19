<?php
namespace MQK\Queue;

use MQK\RedisFactory;
use MQK\RedisProxy;
use PHPUnit\Framework\TestCase;

class RedisQueueCollectionTest extends TestCase
{
    /**
     * @var RedisQueue
     */
    private $queue;

    /**
     * @var RedisProxy
     */
    private $connection;

    public function setUp()
    {
        $this->connection = new RedisProxy('redis://127.0.0.1');
        $this->connection->connect();

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
        $messageFactory = new MessageAbstractFactory();
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

    public function testDequeueMessageInvokableSync()
    {
        $queues = new RedisQueueCollection($this->connection, [$this->queue]);
        $message = $queues->dequeue();
        $this->assertInstanceOf(MessageInvokableSync::class, $message);
    }

    public function testMessageInvokableSync()
    {
        $sync = K::invokeSync(
            array(
                'MQK\Test\Sum::oneSecond',
                'MQK\Test\Sum::oneSecond'
            )
        );

        $sync->then(function($arg) {
            var_dump($arg);
        });
    }
}
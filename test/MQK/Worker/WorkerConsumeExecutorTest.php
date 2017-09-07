<?php
namespace MQK\Worker;
use MQK\Config;
use MQK\Job\MessageDAO;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\MessageInvokableSync;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisFactory;
use MQK\RedisProxy;
use MQK\Registry;
use PHPUnit\Framework\TestCase;

class WorkerConsumeExecutorTest extends TestCase
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
        RedisFactory::shared()->createRedis();
        $this->connection = new RedisProxy('127.0.0.1');
        $this->connection->connect();

        $messageFactory = new MessageAbstractFactory();
        $this->queue = new RedisQueue("default", $this->connection, $messageFactory);
        $this->connection->flushAll();
    }

    public function testExecute()
    {
        $queue = new RedisQueue("default", $this->connection);

        $payload = new \stdClass();
        $payload->func = '\MQK\Test\Calculator::sum';
        $payload->arguments = [1, 2];

        $groupId = uniqid();
        $messageId = uniqid();

        $message = new MessageInvokableSync($groupId, $messageId, "invokable_sync", 'default', 600, $payload);
        $queue->enqueue($message);

        $executor = new WorkerConsumerExector(Config::defaultConfig(), ["default"]);
        $executor->initialize();

        $success = $executor->execute();

        // 单元测试的代码在同一个进程内，所以等待返回必须在最后
        $message->watch(function($invokes) {
            $invoke = $invokes[0];

            $this->assertEquals(3, $invoke->returns);
        });
    }

    public function testExecuteWithMultiInvokes()
    {
        $messageFactory = new MessageAbstractFactory();
        $payload = new \stdClass();
        $payload->func = '\MQK\Test\Calculator::sum';
        $payload->arguments = [1, 2];

        $groupId = uniqid();
        $messageId = uniqid();
        $message1 = new MessageInvokableSync($groupId, 2, $messageId, 'invokable_sync', 'default', 600, $payload);
        $message2 = new MessageInvokableSync($groupId, 2, uniqid(), 'invokable_sync', 'default', 600, $payload);

        $this->queue->enqueue($message1);
        $this->queue->enqueue($message2);

        $registry = new Registry($this->connection);
        $queues = new RedisQueueCollection($this->connection, [new RedisQueue("default", $this->connection, $messageFactory)]);

        $messageDAO = new MessageDAO($this->connection);
        $controller = new MessageInvokableSyncController($this->connection, new RedisQueue("", $this->connection, $messageFactory), $messageDAO);
        $executor = new WorkerConsumerExector(false, false, $queues, $registry, $controller);

        $success = $executor->execute();
        $success = $executor->execute();

        $value = $this->connection->blPop(["queue_{$groupId}"], 1);
        $message = $messageDAO->find($groupId);

        $invokedMessage1 = $messageDAO->find($message1->id());
        $invokedMessage2 = $messageDAO->find($message2->id());

        $this->assertEquals(3, $invokedMessage1->returns());
        $this->assertEquals(3, $invokedMessage2->returns());
        assert(true);
    }

}
<?php
namespace MQK\Worker;
use MQK\Config;
use MQK\Queue\MessageInvokableSync;
use MQK\Queue\RedisQueue;
use MQK\RedisFactory;
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
        $this->connection = new \Redis();
        $this->connection->connect('127.0.0.1');

        $this->queue = new RedisQueue("default", $this->connection);
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
//        $message;
//        $message->promise()->then(function($invokes) {
//
//        });
    }

}
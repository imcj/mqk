<?php

use MQK\Worker\WorkerConsumer;
use MQK\Config;
use PHPUnit\Framework\TestCase;
use MQK\Queue\QueueFactory;
use MQK\Worker\Worker;
use MQK\Queue\Queue;
use MQK\RedisProxy;

class WorkerTest extends TestCase
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * @var Queue
     */
    private $queue;

    public function setUp()
    {
        $this->queue = (new QueueFactory())->createQueue("default");
        $this->redis = new RedisProxy('redis://127.0.0.1')
        $this->redis->flushAll();
    }

    public function testJobToFinished()
    {
        $job = K::invoke("\\MQK\\Test\\Calculator::sum", 1, 1);
        $queue = (new QueueFactory())->createQueue();
        $worker = new WorkerConsumer(Config::defaultConfig(), $queue);
        $worker->execute();
        $startedQueueCount =  $this->redis->zCard("mqk:started");
        $finishedQueueCount =  $this->redis->zCard("mqk:finished");
        $result = (int)$job->result();
        $this->assertEquals(0, $startedQueueCount);
        $this->assertEquals(1, $finishedQueueCount);
        $this->assertEquals(2, $result);
    }

    /**
     * 移除过期的状态是 STARTED 的项目
     * 把过期的任务重新加入到队列
     */
    public function testJobFailure()
    {
        $job = new Job(null, "\\MQK\\Test\\Calculator::sumCrash", [1, 1]);
        $job->setTtl(0);
        $this->queue->enqueue($job);
        $worker = new WorkerConsumer(Config::defaultConfig(), [$this->queue]);
        $worker->execute();

        $startedQueueCount =  $this->redis->zCard("mqk:started");
        $this->assertEquals(1, $startedQueueCount);

        // first retry
        $worker->execute();

        // second retry
        $worker->execute();

        // third retry
        $worker->execute();


        $count = $this->redis->zCard("mqk:started");

        // TODO: 完成断言
    }
}
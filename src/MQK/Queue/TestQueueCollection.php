<?php
namespace MQK\Queue;
use MQK\Exception\EmptyQueueException;
use MQK\Job;
use MQK\RedisFactory;

/**
 * TestQueueCollection类的目的是为了进行性能测试，测试在没有任何对Redis出列的开销的情况下代码的性能
 * @package MQK\Queue
 */
class TestQueueCollection implements QueueCollection
{
    /**
     * 用于测试json反序列化的性能
     * @var string
     */
    private $job = '{"id":"6718b3b00d429e825c87db4e022b2fab","func":"\\\\MQK\\\\Test\\\\Calculator::sum","arguments":["1","1"],"ttl":500,"queue":"default"}';

    /**
     * 计数器
     * @var int
     */
    private $index = 0;

    /**
     * 最大执行次数
     * @var int
     */
    private $max;

    public function __construct($max)
    {
        $this->max = $max;
        $this->redis = RedisFactory::shared()->createRedis();
    }

    /**
     * 出队列
     *
     * @param Queue[] $queues
     *
     * @return Job
     */
    public function dequeue($block = true)
    {
        return $this->testQPS();
        $this->index += 1;
        if ($this->index >= $this->max)
            throw new EmptyQueueException("");
        $jsonObject = json_decode($this->job);
        return Job::job($jsonObject);
    }

    function testQPS()
    {
        try {
            $this->redis->get("a");
        } catch (\RedisException $e) {
            printf("%s\n", $e->getMessage());
        }
    }
}
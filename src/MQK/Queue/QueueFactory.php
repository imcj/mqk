<?php
namespace MQK\Queue;

use MQK\RedisFactory;

class QueueFactory
{
    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct()
    {
        $this->redisFactory = new RedisFactory();
    }

    /**
     * @return Queue
     */
    public function createQueue($name)
    {
        return new RedisQueue($name, $this->redisFactory->createRedis());
    }

    /**
     * 创建一个队列的列表
     *
     * @param $nameList ['default', 'fast']
     * @param $redis Redis connection
     * @return Queue
     */
    public function createQueues($nameList, $redis)
    {
        $queues = [];
        foreach ($nameList as $name) {
            $queues[] = new RedisQueue($name, $redis);
        }
        return $queues;
    }
}
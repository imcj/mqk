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
}
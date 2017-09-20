<?php
namespace MQK\Queue;

use MQK\RedisProxy;

class QueueFactory
{
    /**
     * @var RedisProxy
     */
    private $connection;

    /**
     * @var MessageAbstractFactory
     */
    private $factory;

    /**
     * QueueFactory constructor.
     *
     * @param RedisProxy $connection
     * @param MessageAbstractFactory $factory
     */
    public function __construct(RedisProxy $connection, MessageAbstractFactory $factory)
    {
        $this->connection = $connection;
        $this->factory = $factory;
    }

    /**
     * @param string $name
     * @return Queue
     */
    public function createQueue($name)
    {
        return new RedisQueue($name, $this->connection, $this->factory);
    }

    /**
     * 创建一个队列的列表
     *
     * @param $connection
     * @param $queues
     * @param $messageFactory
     * @return Queue[]
     */
    public function createQueues($queues)
    {
        $returns = [];
        foreach ($queues as $queue) {
            $returns[] = $this->createQueue($queue);
        }

        return $returns;
    }
}
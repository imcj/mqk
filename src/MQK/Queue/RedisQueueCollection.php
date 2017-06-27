<?php
namespace MQK\Queue;

use MQK\Job;

class RedisQueueCollection implements QueueCollection
{
    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var array
     */
    private $queues;

    private $queueKeys = [];

    public function __construct(\Redis $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Queue[] $queues
     */
    public function register(array $queues)
    {
        foreach ($queues as $queue)
        {
            $this->queues[$queue->name()] = $queue;
            $this->queueKeys[] = $queue->key();
        }
    }

    public function get($name)
    {
        return $this->queues[$name];
    }

    public function dequeue()
    {
        $raw = $this->connection->blPop($this->queueKeys, 60);
        if (empty($raw))
            return null;
        $raw = $this->connection->hget('job', $raw[1]);

        return Job::job(json_decode($raw));
    }

    public function queueNames()
    {
        return $this->nameList;
    }
}
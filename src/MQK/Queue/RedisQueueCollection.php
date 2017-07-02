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
        for ($i = 0; $i < 3; $i++) {
            try {
                $raw = $this->connection->blPop($this->queueKeys, 10);
                break;
            } catch (\RedisException $e) {
//                var_dump($e);
            }
        }
        if (empty($raw))
            return null;
        if (!isset($raw[1])) {
            var_dump($raw);
        }
        $raw = $this->connection->hget('job', $raw[0]);

        return Job::job(json_decode($raw));
    }

    public function queueNames()
    {
        return $this->nameList;
    }
}
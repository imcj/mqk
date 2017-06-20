<?php
namespace MQK\Queue;

use MQK\Job;

class RedisQueue implements Queue
{
    
    public function __construct()
    {
        $this->connection = new \Redis();
        $this->connection->connect('127.0.0.1');
    }

    public function connection()
    {
        return $this->connection;
    }

    public function enqueue(Job $queue)
    {
        $this->connection->hset('job', $queue->id(), json_encode($queue->jsonSerialize()));
        $this->connection->lpush('queue', $queue->id());
    }

    public function dequeue()
    {
        $raw = $this->connection->blPop('queue', 60);
        if (empty($raw))
            return null;
        $raw = $this->connection->hget('job', $raw[1]);

        return Job::job(json_decode($raw));
    }
}
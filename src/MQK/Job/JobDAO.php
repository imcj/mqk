<?php
namespace MQK\Job;

use Connection\RedisConnectionProxy;
use MQK\Job;

class JobDAO
{
    /**
     * @var \Redis
     */
    private $connection;

    public function __construct(\Redis $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $id
     * @return Job
     */
    public function find($id)
    {
        $jsonObject = json_decode($this->connection->hGet("job", $id));
        return Job::job($jsonObject);
    }

    public function store(Job $job)
    {
        $this->connection->hSet("job", $job->id(), $job->jsonSerialize());
    }

    public function clear($job)
    {
        $this->connection->hDel("job", $job->id());
    }
}
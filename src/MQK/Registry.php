<?php
namespace MQK;

use MQK\Job\JobDAO;

class Registry
{
    /**
     * @var \Redis
     */
    private $connection;

    public function __construct(\Redis $connection)
    {
        $this->connection = $connection;
        $this->jobDAO = new JobDAO($this->connection);
    }

    public function setConnection(\Redis $connection)
    {
        $this->connection = $connection;
    }

    public function start(Job $job)
    {
        $ttl = time() + $job->ttl();
        $this->connection->zAdd("mqk:started", $ttl, $job->id());
    }

    public function fail(Job $job)
    {
        $ttl = time() + $job->ttl();
        $this->connection->zAdd("mqk:fail", $ttl, $job->id());
    }

    public function finish(Job $job)
    {
        $ttl = time() + $job->ttl();
        $this->connection->zAdd("mqk:finished", $ttl, $job->id());
        $this->connection->zRem("mqk:started", $job->id());
    }

    public function clear($queueName, $id)
    {
        $this->connection->zDelete($queueName, $id);
    }


    public function getExpiredJob($queueName)
    {
        $id = $this->connection->zRangeByScore(
            $queueName,
            0,
            time(),
            array("limit" => array(0, 1)));

        if (empty($id))
            return null;
        return $id[0];
    }

}
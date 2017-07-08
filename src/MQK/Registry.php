<?php
namespace MQK;

use Monolog\Logger;
use MQK\Job\JobDAO;

class Registry
{
    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->jobDAO = new JobDAO($this->connection);
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function start(Job $job)
    {
        if (strpos($job->id(), "_") > -1) {
            $this->logger->debug("Name of job is invalid.");
            return;
        }
        $ttl = time() + $job->ttl();
        $this->connection->zAdd("mqk:started", $ttl, $job->id());
        $this->logger->info("{$job->id()} will at $ttl timeout.");
    }

    public function fail(Job $job)
    {
        $ttl = time() + $job->ttl();
        $this->connection->zAdd("mqk:fail", $ttl, $job->id());
    }

    public function finish(Job $job)
    {
        $ttl = time() + $job->ttl();
        // TODO: 后续在Slow模式加入成功的任务保存
//        $this->connection->zAdd("mqk:finished", $ttl, $job->id());
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
        if (is_array($id)) {
            if (empty($id))
                return null;
            $id = $id[0];
        }

        if (empty($id))
            return null;

        $this->logger->debug("Found expire job {$id}.");
        return $id;
    }

}
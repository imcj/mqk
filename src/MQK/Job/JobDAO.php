<?php
namespace MQK\Job;

use Connection\RedisConnectionProxy;
use Monolog\Logger;
use MQK\Job;
use MQK\LoggerFactory;

class JobDAO
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Redis
     */
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    /**
     * @param $id
     * @return Job
     */
    public function find($id)
    {
        $this->logger->info("JobDAO find {$id}");
        $raw = $this->connection->get("job:{$id}");
        if (null == $raw || false === $raw) {
            $this->logger->error("Job {$id} not found.");
            throw new \Exception("Job {$id} not found.");
        }
        $jsonObject = json_decode($raw);
        $this->logger->debug($raw);
        return Job::job($jsonObject);
    }

    public function store(Job $job)
    {
//        $raw = json_encode($job->jsonSerialize());
//        $this->logger->debug("Store job");
//        $this->logger->debug($raw);
//        $this->connection->hSet("job", $job->id(), $raw);
    }

    public function clear($job)
    {
        $this->connection->hDel("job", $job->id());
    }
}
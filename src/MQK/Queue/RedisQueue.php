<?php
namespace MQK\Queue;

use Connection\Connection;
use Monolog\Logger;
use MQK\Job;
use MQK\LoggerFactory;

class RedisQueue implements Queue
{
    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $name;

    public function __construct($name, $connection)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->name = $name;
    }

    public function connection()
    {
        return $this->connection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function key()
    {
        return "queue_{$this->name}";
    }

    public function enqueue(Job $job)
    {
        if (strpos($job->id(), "_")) {
            $this->logger->error("[enqueue] {$job->id()} contains _", debug_backtrace());
        }
        $job->setQueue($this->name);
        $jobJson = $job->jsonSerialize();
        if ($job->retries()) {
            $jobJson['retries'] = $job->retries();
        }
        $encoded = json_encode($jobJson);
//        $this->logger->debug("[enqueue] {$job->id()}");
//        $this->logger->debug($encoded);
        $this->connection->lpush("{$this->key()}", $encoded);
    }

    public function name()
    {
        return $this->name;
    }
}
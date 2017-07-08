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

    public function enqueue(Job $queue)
    {
        if (strpos($queue->id(), "_")) {
            $this->logger->error("[enqueue] {$queue->id()} contains _", debug_backtrace());
        }
        $this->logger->info("Enqueue job function is {$queue->func()}");
        $this->logger->debug("ttl {$queue->ttl()} second\n");
        $queue->setQueue($this->name);
        $this->connection->hset(
            'job',
            $queue->id(),
            json_encode($queue->jsonSerialize())
        );
        $this->connection->lpush("{$this->key()}", $queue->id());
        $this->logger->debug("enqueue job {$queue->id()}");
    }

    public function name()
    {
        return $this->name;
    }
}
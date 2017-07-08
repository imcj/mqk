<?php
namespace MQK\Queue;

use Monolog\Logger;
use MQK\Exception\BlockPopException;
use MQK\Job;
use MQK\LoggerFactory;
use MQK\RedisFactory;

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

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->redisFactory = RedisFactory::shared();
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

    /**
     * @param $name
     * @return Queue
     * @throws \Exception
     */
    public function get($name)
    {
        if (!isset($this->queues[$name])) {
            throw new \Exception("Queue {$name} not found.");
        }
        return $this->queues[$name];
    }

    public function dequeue($block=true)
    {
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->logger->debug("[dequeue] queues", $this->queueKeys);
                if ($block) {
                    $raw = $this->connection->blPop($this->queueKeys, 10);
                    if (!$raw)
                        return null;
                } else {
                    foreach ($this->queueKeys as $queueKey) {
                        $raw = $this->connection->lPop($queueKey);
                        if ($raw) {
                            $raw = array($queueKey, $raw);
                            break;
                        } else {
                            throw new BlockPopException("");
                        }
                    }
                }
                break;
            } catch (\RedisException $e) {
                // e 0
                // read error on connection
                $this->logger->error($e->getCode());
                $this->logger->error($e->getMessage());
                if ("read error on connection" == $e->getMessage()) {
                    $this->redisFactory->reconnect(3);
                    continue;
                }

                throw $e;
            }
        }
        if (count($raw) < 2) {
            throw new \Exception("queue data count less 2.");
        }
        list($queueKey, $jobJson) = $raw;

        if (empty($jobJson))
            return null;

        try {
            $job = Job::job(json_decode($jobJson));
            $this->logger->debug("[dequeue] Job id is {$job->id()}");
        } catch (\Exception $e) {
            $job = null;
        }
        if (null == $job) {
            $this->logger("Make job object error.", $raw);
            throw \Exception("Make job object error");
        }
        return $job;
    }

    public function queueNames()
    {
        return $this->nameList;
    }
}
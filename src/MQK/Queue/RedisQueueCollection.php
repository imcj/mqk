<?php
namespace MQK\Queue;

use Monolog\Logger;
use MQK\Exception\QueueIsEmptyException;
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
     * @var Queue[]
     */
    private $queues;

    /**
     * Redis队列的名字列表
     *
     * @var string[]
     */
    private $queueKeys = [];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RedisFactory
     */
    private $redisFactory;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * RedisQueueCollection constructor.
     * @param $connection \Redis
     * @param $queues Queue[]
     */
    public function __construct($connection, $queues)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->redisFactory = RedisFactory::shared();
        $this->register($queues);
        $this->messageFactory = new MessageFactory();
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
                            throw new QueueIsEmptyException(null);
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
        list($queueKey, $messageJson) = $raw;

        if (empty($messageJson))
            return null;

        try {
            $messageJsonObject = json_decode($messageJson);
//            $this->logger->debug("[dequeue] {$jsonObject->id}");
//            $this->logger->debug($messageJson);
            // 100k 对象创建大概300ms，考虑是否可以利用对象池提高效率

            $message = $this->messageFactory->messageWithJson($messageJsonObject);
        } catch (\Exception $e) {
            $message = null;
        }
//        if (null == $job) {
//            $this->logger("Make job object error.", $raw);
//            throw \Exception("Make job object error");
//        }
        return $message;
    }
}
<?php
namespace MQK\Queue;

use Monolog\Logger;
use MQK\Exception\EmptyQueueException;
use MQK\Job;
use MQK\LoggerFactory;
use MQK\RedisFactory;
use MQK\RedisProxy;

class RedisQueueCollection implements QueueCollection
{
    /**
     * @var RedisProxy
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
     * @var MessageAbstractFactory
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
        $this->register($queues);
        $this->messageFactory = new MessageAbstractFactory();
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
        $messageJsonObject = $this->connection->listPop($this->queueKeys, $block, 1);

        if (null == $messageJsonObject)
            return null;

        try {
            $messageJsonObject = json_decode($messageJsonObject);
//            $this->logger->debug("[dequeue] {$jsonObject->id}");
//            $this->logger->debug($messageJsonObject);
            // 100k 对象创建大概300ms，考虑是否可以利用对象池提高效率

            $message = $this->messageFactory->messageWithJson($messageJsonObject);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $message = null;
        }
//        if (null == $job) {
//            $this->logger("Make job object error.", $raw);
//            throw \Exception("Make job object error");
//        }
        return $message;
    }
}
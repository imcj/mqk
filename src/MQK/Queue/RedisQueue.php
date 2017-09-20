<?php
namespace MQK\Queue;

use Monolog\Logger;
use MQK\LoggerFactory;
use MQK\RedisProxy;

class RedisQueue implements Queue
{
    /**
     * @var RedisProxy
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

    /**
     * @var MessageAbstractFactory
     */
    private $messageFactory;

    /**
     * RedisQueue constructor.
     *
     * @param string $name
     * @param RedisProxy $connection
     * @param MessageAbstractFactory $messageFactory
     */
    public function __construct($name, RedisProxy $connection, $messageFactory)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->name = $name;
        $this->messageFactory = $messageFactory;
    }

    public function connection()
    {
        return $this->connection;
    }

    public function setConnection(RedisProxy $connection)
    {
        $this->connection = $connection;
    }

    public function key($queue=null)
    {
        if ($queue) {
            return "queue_{$queue}";

        } else {
            return "queue_{$this->name}";
        }
    }

    /**
     * @param Message $message
     * @throws \Exception
     * @return void
     */
    public function enqueue(Message $message)
    {
        if (strpos($message->id(), "_")) {
            $this->logger->error("[enqueue] {$message->id()} contains _", debug_backtrace());
        }
        $messageJsonObject = $message->jsonSerialize();
        if ($message->retries()) {
            $messageJsonObject['retries'] = $message->retries();
        }
        $messageJson = json_encode($messageJsonObject);
        $this->logger->debug("enqueue {$message->id()} to {$message->queue()}");
        $this->logger->debug($messageJson);

        $queueKey = $this->key($message->queue());
        $success = $this->connection->lpush($queueKey, $messageJson);

        if (!$success) {
            $error = $this->connection->getLastError();
            $this->connection->clearLastError();
            throw new \Exception($error);
        }
    }

    public function enqueueBatch($messages)
    {
        $this->connection->multi();
        foreach ($messages as $message) {
            $this->enqueue($message);
        }
        $this->connection->exec();
    }

    public function name()
    {
        return $this->name;
    }

    public function dequeue($block=true)
    {
        $messageJsonObject = $this->connection->listPop($this->key, $block, 1);

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

    /**
     * 设置队列名
     *
     * @param $name
     * @return void
     */
    function setName($name)
    {
        $this->name = $name;
    }

    public static function create($connection, $queues, $messageFactory)
    {
        $returns = [];
        foreach ($queues as $queue) {
            $returns[] = new RedisQueue($queue, $connection, $messageFactory);
        }

        return $returns;
    }
}
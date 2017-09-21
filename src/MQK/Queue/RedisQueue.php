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
    private $prefix;

    /**
     * RedisQueue constructor.
     *
     * @param RedisProxy $connection
     */
    public function __construct(RedisProxy $connection, $queuePrefix)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->prefix = $queuePrefix;
    }

    /**
     * @param string $name
     * @param Message $message
     * @throws \Exception
     * @return void
     */
    public function enqueue($name, Message $message)
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

        $queueKey = $this->prefix . $message->queue();
        $success = $this->connection->lpush($queueKey, $messageJson);

        if (!$success) {
            $error = $this->connection->getLastError();
            $this->connection->clearLastError();
            throw new \Exception($error);
        }
    }
}
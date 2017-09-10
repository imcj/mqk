<?php
namespace MQK\Job;

use Monolog\Logger;
use MQK\LoggerFactory;
use MQK\Queue\Message;
use MQK\Queue\MessageAbstractFactory;

class MessageDAO
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var MessageAbstractFactory
     */
    private $messageFactory;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->messageFactory = MessageAbstractFactory::shared();
    }

    /**
     * @param $id
     * @return Message
     */
    public function find($id)
    {
        assert($id != null);
        $raw = $this->connection->get("mqk:message:{$id}");
        if (null == $raw || false === $raw) {
            $this->logger->error("Message {$id} not found.");
            throw new \Exception("Message {$id} not found.");
        }
        $jsonObject = json_decode($raw);
        $message = $this->messageFactory->messageWithJson($jsonObject);

        return $message;
    }

    /**
     * 持久化存储Message
     *
     * @param Message $message
     */
    public function store(Message $message)
    {
        $messageId = $message->id();
        $this->logger->debug("Store message {$messageId}");
        $raw = json_encode($message->jsonSerialize());
        $this->connection->set("mqk:message:$messageId", $raw);
    }

    public function clear($job)
    {
        $this->connection->hDel("mqk:message:", $job->id());
    }
}
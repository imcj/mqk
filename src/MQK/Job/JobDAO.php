<?php
namespace MQK\Job;

use Monolog\Logger;
use MQK\LoggerFactory;
use MQK\Queue\Message;
use MQK\Queue\MessageAbstractFactory;

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
        $raw = $this->connection->get("job:{$id}");
        if (null == $raw || false === $raw) {
            $this->logger->error("Job {$id} not found.");
            throw new \Exception("Job {$id} not found.");
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
        $raw = json_encode($message->jsonSerialize());
        $this->connection->set("job:", $message->id(), $raw);
    }

    public function clear($job)
    {
        $this->connection->hDel("job", $job->id());
    }
}
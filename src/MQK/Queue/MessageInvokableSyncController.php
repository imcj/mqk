<?php
namespace MQK\Queue;


use Monolog\Logger;
use MQK\LoggerFactory;
use MQK\Queue\Message\MessageDAO;
use MQK\RedisProxy;

class MessageInvokableSyncController
{
    /**
     * @var RedisProxy
     */
    private $connection;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var MessageDAO
     */
    private $messageDAO;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(RedisProxy $connection, Queue $queue, MessageDAO $messageDAO)
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->messageDAO = $messageDAO;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    public function invoke(MessageInvokableSync $message)
    {
        $this->messageDAO->store($message);

        if ($message->numberOfInvoke() == 1) {
            $message = new MessageInvokableSyncReply([], $message->groupId());
        } else {
            $invoked = $this->connection->incr("mqk:invoked:{$message->groupId()}");

            if ($invoked >= $message->numberOfInvoke()) {
                $message = new MessageInvokableSyncReply([], $message->groupId());
                $this->messageDAO->store($message);
            } else {
                return;
            }
        }

        $this->logger->debug("Paralles message completed");
        $this->queue->enqueue($message->groupId(), $message);

    }
}
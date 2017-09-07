<?php
namespace MQK\Queue;


use MQK\Job\MessageDAO;
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

    public function __construct(RedisProxy $connection, Queue $queue, MessageDAO $messageDAO)
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->messageDAO = $messageDAO;
    }

    public function invoke(MessageInvokableSync $message)
    {
        $this->messageDAO->store($message);
        if ($message->numberOfInvoke() == 1) {
            $message = new MessageInvokableSyncReply([], $message->groupId());
            $this->queue->setName($message->groupId());
            $this->queue->enqueue($message);
        } else {
            $invoked = $this->connection->incr("mqk:invoked:{$message->groupId()}");

            if ($invoked >= $message->numberOfInvoke()) {
                $message = new MessageInvokableSyncReply([], $message->groupId());
                $this->queue->setName($message->groupId());
                $this->messageDAO->store($message);
                $this->queue->enqueue($message);
            }
        }

    }
}
<?php
namespace MQK\Queue;


use MQK\Job\JobDAO;
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
     * @var JobDAO
     */
    private $messageDAO;

    public function __construct(RedisProxy $connection, Queue $queue, JobDAO $messageDAO)
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->messageDAO = $messageDAO;
    }

    public function invoke(MessageInvokableSync $message)
    {
        if ($message->numberOfInvoke() == 1) {
            $message = new MessageInvokableSyncReply([$message->id()], $message->groupId());
            $this->queue->setName($message->groupId());
            $this->messageDAO->store($message);
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
<?php
namespace MQK\Queue;


class MessageInvokableSyncWacther
{
    public function wait($handler)
    {
        $message = $this->queue->dequeue($this->groupId());
        $handler($message);
    }
}
<?php
namespace MQK\Queue\MessageFactory;


use MQK\Queue\Message;
use MQK\Queue\MessageInvokableSync;

class MessageInvokableSyncFactory implements MessageFactory
{

    /**
     * 创建Message对象
     *
     * @param \stdClass $jsonObject
     * @return Message
     */
    public function withJsonObject($jsonObject)
    {
        $message = new MessageInvokableSync(
            $jsonObject->groupId,
            $jsonObject->id,
            $jsonObject->discriminator,
            $jsonObject->queue,
            $jsonObject->ttl,
            $jsonObject->payload
        );

        return $message;
    }
}
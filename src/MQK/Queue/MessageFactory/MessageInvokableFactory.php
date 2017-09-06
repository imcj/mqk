<?php
namespace MQK\Queue\MessageFactory;


use MQK\Queue\Message;
use MQK\Queue\MessageInvokable;
use MQK\Queue\Nothing;

class MessageInvokableFactory implements MessageFactory
{

    /**
     * 创建Message对象
     *
     * @param \stdClass $jsonObject
     * @return Message
     */
    public function withJsonObject($jsonObject)
    {
        if (property_exists($jsonObject, "payload"))
            $payload = $jsonObject->payload;
        else {
            $payload = new \stdClass();
            $payload->func = Nothing::class;
            $payload->arguments = [];
        }

        $message = new MessageInvokable(
            $jsonObject->id,
            $jsonObject->discriminator,
            $jsonObject->queue,
            $jsonObject->ttl,
            $payload
        );

        return $message;
    }
}
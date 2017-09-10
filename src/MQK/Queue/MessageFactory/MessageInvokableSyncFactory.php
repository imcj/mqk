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
        $returns = null;
        if (property_exists($jsonObject, 'returns'))
            $returns = $jsonObject->returns;

        $message = new MessageInvokableSync(
            $jsonObject->groupId,
            $jsonObject->numberOfInvoke,
            $jsonObject->id,
            $jsonObject->discriminator,
            $jsonObject->queue,
            $jsonObject->ttl,
            $jsonObject->payload
        );

        if (null != $returns)
            $message->setReturns($returns);

        return $message;
    }
}
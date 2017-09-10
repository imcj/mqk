<?php
namespace MQK\Queue\MessageFactory;


use MQK\Queue\Message;
use MQK\Queue\MessageInvokableSync;
use MQK\Queue\MessageInvokableSyncReply;

class MessageInvokableSyncReplyFactory implements MessageFactory
{

    /**
     * 创建Message对象
     *
     * @param \stdClass $jsonObject
     * @return Message
     */
    public function withJsonObject($jsonObject)
    {
        $numberOfInvoke = property_exists($jsonObject, 'numberOfInvoke') ? $jsonObject->numberOfInvoke : 0;
        $message = new MessageInvokableSyncReply($jsonObject->invokes, $jsonObject->groupId, $numberOfInvoke);
        return $message;
    }
}
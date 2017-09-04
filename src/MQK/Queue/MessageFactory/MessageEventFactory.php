<?php
namespace MQK\Queue\MessageFactory;

use MQK\Queue\Message;

class MessageEventFactory implements MessageFactory
{

    /**
     * 创建Message对象
     *
     * @param \stdClass $jsonObject
     * @return Message
     */
    public function withJsonObject($jsonObject)
    {
    }
}
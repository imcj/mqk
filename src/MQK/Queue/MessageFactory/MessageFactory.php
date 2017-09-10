<?php
namespace MQK\Queue\MessageFactory;
use MQK\Queue\Message;

/**
 * Message工厂方法的接口抽象
 *
 * @package MQK\Queue\MessageFactory
 */
interface MessageFactory
{
    /**
     * 创建Message对象
     *
     * @param \stdClass $jsonObject
     * @return Message
     */
    public function withJsonObject($jsonObject);
}
<?php
namespace MQK\Queue;

/**
 * 队列接口
 * 
 * 默认使用Redis实现队列，将来可能会增加RabbitMQ和SQS
 */
interface Queue
{
    /**
     * 进入队列
     *
     * @param string $name
     * @param Message $message
     * @return void
     */
    function enqueue($name, Message $message);
}
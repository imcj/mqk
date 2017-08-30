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
     * @param Message $message
     * @return void
     */
    function enqueue(Message $message);

    /**
     * 队列名
     *
     * @return string
     */
    function name();

    /**
     * 队列的键名
     *
     * @return string
     */
    function key();
}
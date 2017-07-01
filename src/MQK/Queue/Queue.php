<?php
namespace MQK\Queue;

use MQK\Job;

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
     * @param Job $job
     * @return void
     */
    function enqueue(Job $job);

    function name();

    function key();
}
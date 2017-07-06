<?php
namespace MQK\Queue;


interface QueueCollection
{
    /**
     * 出队列
     *
     * @param Queue[] $queues
     *
     * @return Job
     */
    function dequeue($block=true);

    /**
     * @return stringp[
     */
    function queueNames();
}
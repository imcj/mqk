<?php
namespace MQK;

class K
{
    public static function setup($config)
    {
        $this->config = $config;
    }

    public static function invoke($func, ...$args)
    {
        $queue = new \MQK\Queue\RedisQueue();
        $job = new Job(null, $func, $args);
        $job->setConnection($queue->connection());
        $queue->enqueue($job);

        return $job;
    }
}
<?php
namespace MQK\Worker;

use MQK\Job;
use MQK\Config;
use MQK\Queue\Queue;

class WorkerImpl implements Worker
{
    private $config;
    private $queue;
    private $id;

    public function __construct($id, Config $config, Queue $queue)
    {
        $this->id = $id;
        $this->config = $config;
        $this->queue = $queue;
    }

    public function start()
    {
        while (true) {
            $job = $this->queue->dequeue();
            if (null == $job) {
                continue;
            }
            $this->execute($job);
            $memory1 = memory_get_usage(false);
            if ($memory1 * 1024 * 1024 * 200) {
                exit(0);
            }
        }
    }

    function execute($job)
    {
        $result = call_user_func_array($job->func(), $job->arguments());
        $this->config->redis()->hset('result', $job->id(), $result);
    }

    public function stop()
    {

    }

    public function pause()
    {

    }

    public function id()
    {
        return $this->id;
    }
}
<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Queue\Queue;

class WorkerConsumer extends AbstractWorker implements Worker
{
    protected $config;
    protected $queue;

    public function __construct(Config $config, Queue $queue)
    {
        parent::__construct();
        $this->config = $config;
        $this->queue = $queue;
    }

    public function run()
    {
        echo "Process {$this->id} started.\n";
        while (true) {
            $this->execute();
            $memoryUsage = $this->memoryGetUsage();
            if ($memoryUsage > self::M * 10) {
                exit(0);
            }
        }
    }

    protected function memoryGetUsage()
    {
        return memory_get_usage(false);
    }

    function execute()
    {
        $job = $this->queue->dequeue();
        if (null == $job) {
            return;
        }
        $result = call_user_func_array($job->func(), $job->arguments());
        $this->config->redis()->hset('result', $job->id(), $result);
        $this->config->redis()->expire($this->id, 500);
    }
}
<?php
namespace MQK;
use MQK\Queue\RedisQueue;
declare(ticks=1);

class Runner
{
    private $config;
    private $workerId = [];

    public function __construct()
    {
        $this->config = Config::defaultConfig();

        pcntl_signal(SIGCHLD, array(&$this, "signal"));
    }

    function signal($status)
    {
        $pid = posix_getpid();
        echo "Signal pid is {$pid}\n";
        $workerId = pcntl_waitpid(-1, $status, WNOHANG);
        $status = $status >> 8;
    }

    public function run()
    {
        echo "Master work on " . posix_getpid() . "\n";

        for ($i = 0; $i < $this->config->workers(); $i++) {
            $this->fork();
        }

        while (true) {
            sleep(0.1);
        }
    }

    function fork()
    {
        $pid = pcntl_fork();
        $queue = new RedisQueue();
        $worker = new \MQK\Worker\WorkerImpl($pid, $this->config, $queue);

        if (-1 == $pid) {
            exit(1);
        } else if ($pid) {
            $this->workerId[] = $pid;
            return $pid;
        }
    }
}
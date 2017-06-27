<?php
namespace MQK;
use MQK\Queue\Queue;
use MQK\Queue\QueueFactory;
use MQK\Queue\RedisQueue;
declare(ticks=1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Runner
{
    private $config;
    private $logger;
    private $workers = [];

    /**
     * @var Queue
     */
    private $queue;

    public function __construct()
    {
        $this->logger = new Logger(__CLASS__);
        $queueFactory = new QueueFactory();
        $this->queue = $queueFactory->createQueue();

//        $this->logger->pushHandler(new StreamHandler("php://stdout"));
        $this->config = Config::defaultConfig();

        pcntl_signal(SIGCHLD, array(&$this, "signal"));
    }

    function signal($status)
    {
        switch ($status) {
            case SIGCHLD:
                $this->signalChld($status);
                break;
            case STDIN:
                $this->signalIncrement($status);
                break;
        }

    }

    function signalChld($status)
    {
        $pid = posix_getpid();
        $this->logger->debug("Signal pid is {$pid}\n");
        $workerId = pcntl_waitpid(-1, $status, WNOHANG);
        $status = $status >> 8;

        $worker = $this->workers[$workerId];
        unset($this->workers[$workerId]);
        $this->logger->debug("Child {$workerId} quit.");

        if (time() - $worker->createdAt() < 2) {
            $this->logger->debug("Child quit too fast sleep.");
            sleep(2);
        }
        $this->spawn();
    }

    function signalIncrement($status)
    {
        $this->spawn();
    }

    public function run()
    {
        echo "Master work on " . posix_getpid() . "\n";

        for ($i = 0; $i < $this->config->workers(); $i++) {
            $worker = $this->spawn();
            $this->workers[] = $worker;
        }

        while (true) {
            sleep(0.1);
        }
    }

    function spawn()
    {
        $worker = new \MQK\Worker\WorkerConsumer($this->config, $this->queue);
        $worker->start();
        return $worker;
    }

    function fork()
    {

    }
}
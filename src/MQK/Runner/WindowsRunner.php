<?php
namespace MQK\Runner;

use MQK\Process\Master;
use MQK\Queue\QueueFactory;
use MQK\Worker\ConsumerWorker;
use MQK\Worker\ConsumerWorkerFactory;
use MQK\Worker\Worker;
use MQK\Worker\WorkerFactory;
use MQK\LoggerFactory;

class WindowsRunner implements Runner, Master
{
    use RunnerTrait;

    public function __construct(
        $burst,
        $fast,
        $processIdFile,
        $daemonize,
        $concurrency,
        $workerFactory,
        $connection,
        $searchExpiredMessage
    ) {
        $this->workerClassOrFactory = $workerFactory;
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->masterId = uniqid();
        $this->searchExpiredMessage = $searchExpiredMessage;
        $this->fast = $fast;
        $this->processIdFile = $processIdFile;
        $this->daemonize = $daemonize;
    }

    function heartbeat()
    {
    }

    /**
     * 启动主进程
     *
     * @return void
     */
    function run()
    {
        /**
         * @var ConsumerWorker $consumer
         */
        $consumer = $this->workerClassOrFactory->create();
        $consumer
            ->consumerWorkerExecutor()
            ->setSearchExpiredMessage($this->searchExpiredMessage);

        $consumer->run();
    }
}
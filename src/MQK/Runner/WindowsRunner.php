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

    /**
     * @var RedisProxy
     */
    private $connection;

    /**
     * @var string
     */
    protected $masterId;

    /**
     * @var integer
     */
    protected $concurrency;

    /**
     * @var OSDetect
     */
    protected $osDetect;

    /**
     * @var SearchExpiredMessage
     */
    protected $searchExpiredMessage;

    /**
     * @var ConsumerWorkerFactory
     */
    protected $workerClassOrFactory;

    /**
     * @var bool
     */
    protected $fast = false;

    public function __construct(
        $burst,
        $fast,
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
<?php
namespace MQK\Runner;

declare(ticks=1);
use MQK\Process\MasterProcess as Master;
use MQK\Queue\QueueFactory;
use MQK\Worker\Worker;
use MQK\Worker\WorkerFactory;
use MQK\LoggerFactory;

class PosixRunner extends Master implements Runner
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
    protected $searchExpiredMessage = true;

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

        parent::__construct(
            $this->workerClassOrFactory,
            $concurrency,
            $burst,
            $this->logger
        );
    }

    public function run()
    {
        $this->logger->notice("MasterProcess ({$this->masterId}) work on process" . posix_getpid());
        parent::run();
        $this->spawn();
    }

    protected function didSelect()
    {
        $this->heartbeat();
    }

    public function heartbeat()
    {
        $this->updateHealth();
        if (!$this->fast && $this->searchExpiredMessage) {
            $this->searchExpiredMessage->process();
        }
    }

    public function updateHealth()
    {
        $key = "mqk:{$this->masterId}";
        $this->connection->multi();
        $this->connection->hSet($key, "updated_at", time());
        $this->connection->expire($key, 5);
        $this->connection->exec();
    }

}
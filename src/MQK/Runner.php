<?php
namespace MQK;

declare(ticks=1);
use MQK\Process\AbstractWorker;
use MQK\Queue\Message\MessageDAO;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\QueueCollection;
use MQK\Queue\QueueFactory;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\Worker\Worker;
use MQK\Worker\ConsumerWorkerFactory;
use MQK\Worker\WorkerFactory;
use MQK\Process\MasterProcess as Master;


class Runner extends Master
{
    /**
     * @var string
     */
    private $redisDsn;

    /**
     * @var integer
     */
    private $maxRetries;

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

    /**
     * Runner constructor.
     *
     * @param integer $retry
     * @param string[] $queues
     */
    public function __construct(
        $burst,
        $fast,
        $concurrency,
        $workerFactory,
        $connection,
        $maxRetries,
        OSDetect $osDetect,
        $searchExpiredMessage
    ) {
        $this->workerClassOrFactory = $workerFactory;
        $this->connection = $connection;
        $this->maxRetries = $maxRetries;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->masterId = uniqid();
        $this->osDetect = $osDetect;
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

        if ($this->osDetect->isPosix()) {
            parent::run();
        }

        $this->spawn();
    }

    protected function didSpawnWorker(AbstractWorker $worker, $index)
    {
        // Windows 维护负责过期任务的进程是一个，如果进程出现意外退出将全部退出
        if ($index == 0 && $this->osDetect->isPosix()) {
            $this->logger->debug("Windows系统下，由Worker负责查找过期消息。");
            $worker->setIsSearchExpiredMessage(true);
        }
    }

    protected function didSelect()
    {
        $this->heartbeat();
    }

    public function heartbeat()
    {
        $this->updateHealth();
        if (!$this->fast && $this->isSearchExpiredMessage) {
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
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
    private $config;

    /**
     * @var RedisProxy
     */
    private $connection;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var MessageDAO
     */
    private $messageDAO;

    /**
     * @var string[]
     */
    private $queues;

    protected $findExpiredJob = true;

    /**
     * @var SearchExpiredMessage
     */
    protected $expiredFinder;

    /**
     * @var string
     */
    protected $masterId;

    /**
     * Runner constructor.
     *
     * @param integer $retry
     * @param string[] $queues
     */
    public function __construct($queues, $retry)
    {
        $config = Config::defaultConfig();
        $dsn = $config->redis();
        try {
            $this->connection = new RedisProxy($dsn);
            $this->connection->connect();
        } catch (\RedisException $e) {
            if ("Failed to AUTH connection" == $e->getMessage()) {
                $this->logger->error($e->getMessage());
                exit(1);
            }
        }

        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);

        $this->config = $config;
        $this->registry = new Registry($this->connection);
        $this->messageDAO = new MessageDAO($this->connection);

        $this->queues = $queues;

        $queue = new RedisQueue($this->connection, $config->queuePrefix());
        $this->workerClassOrFactory = new ConsumerWorkerFactory(
            $config->redis(),
            $queues,
            $this->masterId,
            $config->bootstrap(),
            $config->burst(),
            $config->fast(),
            $config->errorHandlers(),
            $config->queuePrefix(),
            $retry
        );
        $this->expiredFinder = new SearchExpiredMessage(
            $this->connection,
            $this->messageDAO,
            $this->registry,
            $queue,
            null,
            $retry
        );

        $this->masterId = uniqid();

        parent::__construct($this->workerClassOrFactory, $this->config->concurrency(), $this->config->burst(), $this->logger );
    }

    public function run()
    {
        if (!$this->isWin())
            parent::run();
        else
            $this->spawn();
        $this->logger->notice("MasterProcess ({$this->masterId}) work on " . posix_getpid());
    }

    protected function didSpawnWorker(AbstractWorker $worker, $index)
    {
        // Windows 维护负责过期任务的进程是一个，如果进程出现意外退出将全部退出
        if ($index == 0 && $this->isWin()) {
            $this->logger->debug("Windows系统下，由Worker负责查找过期消息。");
            $worker->enableSearchExpiredMessage();
        }
    }

    protected function isWin()
    {
        return true;
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    protected function didSelect()
    {
        $fast = $this->config->fast();
        $findExpiredJob = $this->findExpiredJob;

        $this->updateHealth();
        if (!$fast && $findExpiredJob && !$this->isWin()) {
            $this->expiredFinder->process();
        }
    }

    public function workerFactory()
    {
        return $this->workerFactory;
    }

    public function setWorkerFactory($workerFactory)
    {
        $this->workerFactory = $workerFactory;
    }

    protected function updateHealth()
    {
        $key = "mqk:{$this->masterId}";
        $this->connection->multi();
        $this->connection->hSet($key, "updated_at", time());
        $this->connection->expire($key, 5);
        $this->connection->exec();
    }

}
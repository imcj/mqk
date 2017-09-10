<?php
namespace MQK;
use MQK\Exception\JobMaxRetriesException;
use MQK\Job\JobDAO;
use MQK\MasterProcess\MasterProcess;
use MQK\Queue\Queue;
use MQK\Queue\QueueCollection;
use MQK\Queue\QueueFactory;
use MQK\Queue\RedisQueue;
declare(ticks=1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use MQK\Queue\RedisQueueCollection;
use MQK\Worker\Worker;
use MQK\Worker\WorkerConsumer;
use MQK\Worker\WorkerConsumerFactory;
use MQK\Worker\WorkerFactory;
use MQK\Process\MasterProcess as Master;


class Runner extends Master
{
    private $config;

    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var JobDAO
     */
    private $jobDAO;

    /**
     * @var Logger
     */
    private $cliLogger;

    /**
     * @var QueueCollection
     */
    private $queues;

    private $nameList = ['default'];

    protected $findExpiredJob = true;

    /**
     * @var ExpiredFinder
     */
    protected $expiredFinder;

    /**
     * @var string
     */
    protected $masterId;

    public function __construct()
    {
        $queueFactory = new QueueFactory();
        $redisFactory = RedisFactory::shared();
        $config = Config::defaultConfig();
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->cliLogger = LoggerFactory::shared()->cliLogger();

        try {
            $connection = $redisFactory->createRedis();
        } catch (\RedisException $e) {
            if ("Failed to AUTH connection" == $e->getMessage()) {
                $this->cliLogger->error($e->getMessage());
                exit(1);
            }
        }

        $this->config = $config;
        $this->connection = $connection;
        $this->registry = new Registry($connection);
        $this->jobDAO = new JobDAO($connection);

        $this->queues = new RedisQueueCollection(
            $this->connection,
            $queueFactory->createQueues($this->nameList, $connection)
        );
        $queues = ["default"];

        $this->workerClassOrFactory = new WorkerConsumerFactory($config, $queues, $this->masterId);
        $this->expiredFinder = new ExpiredFinder($connection, $this->jobDAO, $this->registry, $this->queues);
    }

    protected function didSelect()
    {
        $this->masterId = uniqid();
        $this->cliLogger->notice("MasterProcess ({$this->masterId}) work on " . posix_getpid());
        $this->logger->debug("Starting {$this->config->workers()} workers.");

        $fast = $this->config->fast();
        $findExpiredJob = $this->findExpiredJob;


        $this->updateHealth();
        if (!$fast && $findExpiredJob) {
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
<?php
namespace MQK;

declare(ticks=1);
use MQK\Queue\Message\MessageDAO;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\QueueCollection;
use MQK\Queue\QueueFactory;
use MQK\Queue\RedisQueueCollection;
use MQK\Worker\Worker;
use MQK\Worker\WorkerConsumerFactory;
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

        $queueFactory = new QueueFactory($this->connection, new MessageAbstractFactory());
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);

        $this->config = $config;
        $this->registry = new Registry($this->connection);
        $this->messageDAO = new MessageDAO($this->connection);

        $this->queues = new RedisQueueCollection(
            $this->connection,
            $queueFactory->createQueues($this->nameList, $this->connection)
        );
        $queues = ["default"];
        $this->workerClassOrFactory = new WorkerConsumerFactory(
            $config->redis(),
            $queues,
            $this->masterId,
            $config->initScript(),
            $config->burst(),
            $config->fast()
        );
        $this->expiredFinder = new ExpiredFinder($this->connection, $this->messageDAO, $this->registry, $this->queues);

        parent::__construct($this->workerClassOrFactory, $this->config->workers(), $this->config->burst(), $this->logger );
    }

    public function run()
    {
        parent::run();
        $this->logger->notice("MasterProcess ({$this->masterId}) work on " . posix_getpid());
    }

    protected function didSelect()
    {
        $this->masterId = uniqid();

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
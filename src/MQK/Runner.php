<?php
namespace MQK;
use MQK\Exception\JobMaxRetriesException;
use MQK\Job\JobDAO;
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


class Runner
{
    private $config;
    private $workers = [];

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
    private $logger;

    /**
     * @var Logger
     */
    private $cliLogger;

    /**
     * @var QueueCollection
     */
    private $queues;

    private $alive = true;

    private $exists = 0;

    private $nameList = ['default'];

    /**
     * @var WorkerFactory
     */
    protected $workerFactory;

    protected $findExpiredJob = true;

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
        $queueFactory = new QueueFactory();
        $queues = [$queueFactory->createQueue("default")];
        $this->workerFactory = new WorkerConsumerFactory($config, $queues);

        pcntl_signal(SIGCHLD, array(&$this, "signal"));
        pcntl_signal(SIGINT, array(&$this, "sigintHandler"));
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

    function sigintHandler($signo)
    {
        $this->halt();
    }

    function signalChld($status)
    {
        while (-1 != pcntl_waitpid(0, $status)) {
            pcntl_wexitstatus($status);
            $this->exists += 1;
        }

        if ($this->config->workers() == $this->exists and $this->config->burst()) {
            $this->alive = false;
            return;
        }

        if (!$this->config->burst())
            $this->spawn();
    }

    function signalIncrement($status)
    {
        $this->spawn();
    }

    public function run()
    {
        $this->cliLogger->notice("MasterProcess work on " . posix_getpid());
        $this->logger->debug("Starting {$this->config->workers()}.");

        for ($i = 0; $i < $this->config->workers(); $i++) {
            $worker = $this->spawn();
        }

        while ($this->alive) {
            if ($this->config->fast()) {
                sleep(100);
            } else {
                try {
                    if ($this->findExpiredJob) {
                        $this->reassignExpiredJob();
                    }
                    sleep(1);
                } catch (JobMaxRetriesException $e) {
                    $job = $e->job();
                    $this->logger->warning("超过最大重试次数 {$job->id()}");
                    $this->registry->clear("mqk:started", $job->id());
                    $this->jobDAO->clear($e->job());
                };
            }

        }
        $this->logger->info("MasterProcess process quit.");
    }

    function spawn()
    {
        $worker = $this->workerFactory->create();
        $pid = $worker->start();
        $worker->setId($pid);
        $this->workers[$worker->id()] = $worker;
        return $worker;
    }

    function halt()
    {
        // kill all process
        /**
         * @var $worker Worker
         */
        foreach ($this->workers as $worker) {
            $this->cliLogger->info("Kiling process {$worker->id()}");
            if (!posix_kill($worker->id(), SIGKILL)) {
                $this->cliLogger->error("Kill process failure {$worker->id()}");
            }
        }

        exit(0);
    }

    function reassignExpiredJob()
    {
        $id = $this->registry->getExpiredJob("mqk:started");
        if (null == $id)
            return;
        else {
            $this->logger->info("Remove expired job {$id} from started queue");
        }

        $this->registry->clear("mqk:started", $id);
        try {
             $job = $this->jobDAO->find($id);
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
            return;
        }
        $this->logger->debug("Renew enqueue Job in {$job->queue()}");
        try {
            $queue = $this->queues->get($job->queue());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error(json_encode($job->jsonSerialize()));
            return;
        }

        if (3 <= $job->retries()) {
            throw new JobMaxRetriesException($job);
        }
        if (null == $job) {
            $this->logger->error("[reassignExpredJob] Job is null");
            exit(1);
        }
        $job->increaseRetries();
        $this->jobDAO->store($job);
        $queue->enqueue($job);
    }

    public function workerFactory()
    {
        return $this->workerFactory;
    }

    public function setWorkerFactory($workerFactory)
    {
        $this->workerFactory = $workerFactory;
    }
}
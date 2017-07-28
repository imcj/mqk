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


class Runner implements MasterProcess
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

    /**
     * @var ExpiredFinder
     */
    protected $expiredFinder;

    /**
     * @var PIPE
     */
    protected $pipe;

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

        $this->pipe = new PIPE();
        $this->workerFactory = new WorkerConsumerFactory($config, $queues, $this->pipe);

        $this->expiredFinder = new ExpiredFinder($connection, $this->jobDAO, $this->registry, $this->queues);

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
        $this->logger->debug("Pressed ctrl+C");
        $this->halt();
    }

    function signalChld($status)
    {
        while (-1 != pcntl_waitpid(0, $status)) {
            pcntl_wexitstatus($status);
            $this->exists += 1;
        }

        $allChildrenProcessQuited = $this->config->workers() == $this->exists;
        if ($allChildrenProcessQuited and $this->config->burst()) {
            $this->alive = false;
            return;
        }

        if (!$this->config->burst()) {
            $this->spawn();
        }
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
        $fast = $this->config->fast();
        $findExpiredJob = $this->findExpiredJob;

        while ($this->alive) {
            $buffer = $this->pipe->read();
            if ($buffer) {
                list($action, $data) = explode(":", $buffer);
                if ($action == "Q") {
                    $this->cliLogger->info("[run] Received quit command.");
                    unset($this->workers[(int)$data]);
                }
            }

            if ($fast) {
            } else {
                if ($findExpiredJob) {
//                    $this->logger->debug("Search expired job");
                    $this->expiredFinder->process();
                }
//                sleep(1);
            }

        }
        $this->logger->info("MasterProcess process quit.");
    }

    function spawn()
    {
        $worker = $this->workerFactory->create();
        $pid = $worker->start();

        $this->pipe->closeImFather();

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
            $this->cliLogger->info("Killing process {$worker->id()}");
            if (!posix_kill($worker->id(), SIGUSR1)) {
                $this->cliLogger->error("Kill process failure {$worker->id()}");
            }
        }
//        sleep(2);
        exit(0);
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
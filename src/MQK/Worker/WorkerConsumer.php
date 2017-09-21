<?php
declare(ticks=1);
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Error\ErrorHandler;
use MQK\Exception\EmptyQueueException;
use MQK\Health\HealthReporter;
use MQK\Health\HealthReporterRedis;
use MQK\Health\WorkerHealth;
use MQK\Queue\Message\MessageDAO;
use MQK\LoggerFactory;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\QueueFactory;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisProxy;
use MQK\Registry;
use MQK\SerializerFactory;
use MQK\Time;
use MQK\Process\AbstractWorker;

/**
 * Woker的具体实现，在进程内调度Queue和Job完成具体任务
 *
 * Class WorkerConsumer
 * @package MQK\Worker
 */
class WorkerConsumer extends AbstractWorker
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $cliLogger;

    /**
     * @var float
     */
    protected $workerStartTime;

    /**
     * @var float
     */
    protected $workerEndTime;

    /**
     * @var int
     */
    protected $success = 0;

    /**
     * @var int
     */
    protected $failure = 0;

    /**
     * @var string
     */
    protected $masterId;

    /**
     * @var string
     */
    protected $workerId;

    /**
     * @var WorkerConsumerExecutor
     */
    protected $executor;

    const M = 1024 * 1024;

    protected $queueNameList;

    protected $bootstrap = false;

    protected $burst = false;

    protected $fast = false;

    /**
     * @var HealthReporter
     */
    protected $healthRepoter;

    protected $redisDsn;

    /**
     * @var ErrorHandler[]
     */
    protected $errorHandlers;

    /**
     * @var string
     */
    protected $queuePrefix;

    public function __construct($redisDsn, $queueNameList, $masterId, $bootstrap, $burst, $fast, $errorHandlers, $queuePrefix)
    {
        $this->redisDsn = $redisDsn;
        $this->masterId = $masterId;
        $this->workerId = uniqid();
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->queueNameList = $queueNameList;
        $this->bootstrap = $bootstrap;
        $this->burst = $burst;
        $this->fast = $fast;
        $this->errorHandlers = $errorHandlers;
        $this->queuePrefix = $queuePrefix;

        $this->loadUserInitializeScript();


    }

    public function run()
    {
        parent::run();

        $health = new WorkerHealth();
        $health->setId($this->workerId);
        $health->setProcessId($this->id);

        $connection = new RedisProxy($this->redisDsn);
        $connection->connect();

        $this->healthRepoter = new HealthReporterRedis(
            $health,
            $connection,
            SerializerFactory::shared()->serializer(),
            1
        );
        $this->healthRepoter->report(WorkerHealth::STARTED);

        $this->executor = $this->createExecutor($connection);
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);

        $this->logger->debug("Process ({$this->workerId}) {$this->id} started.");
        $this->workerStartTime = Time::micro();
        while ($this->alive) {
            try {
                $this->healthRepoter->report(WorkerHealth::EXECUTING);
                $success = $this->executor->execute();
                $this->healthRepoter->report(WorkerHealth::EXECUTED);

                if ($success)
                    $this->success += 1;

            } catch (EmptyQueueException $e) {
                $this->alive = false;
                $this->logger->info("When the burst, queue is empty worker {$this->id} will quitting.");
            }

            $memoryUsage = $this->memoryGetUsage();
            if ($memoryUsage > self::M * 1024) {
                break;
            }

            $health->setDuration(Time::micro() - $this->workerStartTime);
        }


        $this->workerEndTime = Time::micro();
        $this->didQuit();
        exit(0);
    }

    protected function didQuit()
    {
        if (0 == $this->workerEndTime)
            $this->workerEndTime = time();
        $duration = $this->workerEndTime - $this->workerStartTime;
        $this->logger->notice("[run] duration {$duration} second");
        $this->logger->notice("Success {$this->success} failure {$this->failure}");
    }

    protected function memoryGetUsage()
    {
        return memory_get_usage(false);
    }

    protected function loadUserInitializeScript()
    {
        if (!empty($this->bootstrap)) {
            if (file_exists($this->bootstrap)) {
                include_once $this->bootstrap;
                return;
            } else {
                $this->logger->warning("You specify bootstrap script [{$this->bootstrap}], but file not exists.");
            }
        }
        $cwd = getcwd();
        $initFilePath = "{$cwd}/init.php";

        if (file_exists($initFilePath)) {
            include_once $initFilePath;
        } else {
            $this->logger->warning("{$initFilePath} not found, all event will miss.");
        }
    }

    protected function createExecutor($connection)
    {
        assert($connection instanceof  RedisProxy);

        $notifyQueue = new RedisQueue($connection, $this->queuePrefix);

        $queues = new RedisQueueCollection(
            $connection,
            $this->queueNameList
        );
        $registry = new Registry($connection);

        $messageDAO = new MessageDAO($connection);
        $controller = new MessageInvokableSyncController(
            $connection,
            $notifyQueue,
            $messageDAO
        );

        $exector = new WorkerConsumerExecutor(
            $this->burst,
            $this->fast,
            $queues,
            $registry,
            $controller,
            $this->healthRepoter,
            $this->errorHandlers
        );

        return $exector;
    }

    protected function updateHealth()
    {
        $key = "mqk:{$this->masterId}:{$this->workerId}";
        $masterKey = "mqk:{$this->masterId}";
        $this->connection->multi();
        $this->connection->hSet($key, "updated_at", time());
        $this->connection->hSet($key, 'success', (int)$this->success);
        $this->connection->hSet($key, 'failure', (int)$this->failure);
        $this->connection->expire($key, 5);
        $this->connection->exec();
    }
}
<?php
declare(ticks=1);
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Config;
use MQK\Exception\QueueIsEmptyException;
use MQK\Job\MessageDAO;
use MQK\LoggerFactory;
use MQK\PIPE;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\QueueFactory;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\Queue\TestQueueCollection;
use MQK\RedisFactory;
use MQK\RedisProxy;
use MQK\Registry;
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
    protected $executor;

    protected $config;

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
     * @var WorkerConsumerExector
     */
    protected $exector;

    /**
     * @var RedisProxy
     */
    protected $connection;

    const M = 1024 * 1024;

    protected $queueNameList;

    public function __construct(Config $config, $queueNameList, $masterId)
    {
        $this->masterId = $masterId;
        $this->workerId = uniqid();
        $this->config = $config;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->queueNameList = $queueNameList;

        $this->loadUserInitializeScript();


    }

    public function run()
    {
        parent::run();

        $this->exector = $this->createExector();
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);

        $this->logger->debug("Process ({$this->workerId}) {$this->id} started.");
        $this->workerStartTime = Time::micro();

        while ($this->alive) {
            try {
                $success = $this->exector->execute();
            } catch (QueueIsEmptyException $e) {
                $this->alive = false;
                $this->cliLogger->info("When the burst, queue is empty worker {$this->id} will quitting.");
            }

            if ($success)
                $this->success += 1;
            else
                $this->failure += 1;

            $memoryUsage = $this->memoryGetUsage();
            if ($memoryUsage > self::M * 1024) {
                break;
            }
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
        if ($this->config->initScript()) {
            if (file_exists($this->config->initScript())) {
                include_once $this->config->initScript();
                return;
            } else {
//                $this->cliLogger->warning("You specify init script [{$this->config->initScript()}], but file not exists.");
            }
        }
        $cwd = getcwd();
        $initFilePath = "{$cwd}/init.php";

        if (file_exists($initFilePath)) {
            include_once $initFilePath;
        } else {
//            $this->cliLogger->warning("{$initFilePath} not found, all event will miss.");
        }
    }

    protected function createConnection()
    {
        $connection = RedisFactory::shared()->createConnection();
        return $connection;
    }

    protected function createExector()
    {
        $this->connection = $connection = $this->createConnection();
        $messageFactory = new MessageAbstractFactory();
        assert($connection instanceof  RedisProxy);

        $queueFactory = new QueueFactory($connection, $messageFactory);

        $queues = new RedisQueueCollection(
            $connection,
            RedisQueue::create(
                $connection,
                $this->queueNameList,
                $messageFactory
            )
        );
        $registry = new Registry($connection);

        $notifyQueue = $queueFactory->createQueue("");
        $messageDAO = new MessageDAO($connection);
        $controller = new MessageInvokableSyncController(
            $connection,
            $notifyQueue,
            $messageDAO
        );

        $exector = new WorkerConsumerExector(
            $this->config->burst(),
            $this->config->fast(),
            $queues,
            $registry,
            $controller
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
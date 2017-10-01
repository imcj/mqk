<?php
declare(ticks=1);
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Error\ErrorHandler;
use MQK\Exception\EmptyQueueException;
use MQK\ExpiredFinder;
use MQK\Health\HealthReporter;
use MQK\Health\HealthReporterRedis;
use MQK\Health\WorkerHealth;
use MQK\Queue\Message;
use MQK\Queue\Message\MessageDAO;
use MQK\LoggerFactory;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\Queue;
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
     * @var float
     */
    protected $workerStartTime;

    /**
     * @var float
     */
    protected $workerEndTime;

    /**
     * @var string
     */
    protected $workerId;

    /**
     * @var WorkerConsumerExecutor
     */
    protected $executor;
    
    public function __construct(
        $bootstrap,
        WorkerConsumerExecutor $executor) {
        $this->executor = $executor;
        $this->bootstrap = $bootstrap;
    }

    public function run()
    {
        parent::run();
        $this->logger->debug("Process ({$this->workerId}) {$this->id} started.");

        $this->loadUserInitializeScript($this->bootstrap);
        $this->executor->execute();
    }

    public function loadUserInitializeScript($bootstrap)
    {
        if (!empty($bootstrap)) {
            if (file_exists($bootstrap)) {
                $this->logger->debug("Loaded bootstrap {$bootstrap}");
                include_once $bootstrap;
                return;
            } else {
                $this->logger->warning("You specify bootstrap script [{$bootstrap}], but file not exists.");
            }
        }
        $cwd = getcwd();
        $bootstrap = "{$cwd}/bootstrap.php";

        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        } else {
            $this->logger->warning("{$bootstrap} not found, all event will miss.");
        }
    }
}
<?php
declare(ticks=1);
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Config;
use MQK\Exception\BlockPopException;
use MQK\Exception\JobMaxRetriesException;
use MQK\Exception\TestTimeoutException;
use MQK\Job\JobDAO;
use MQK\LoggerFactory;
use MQK\Queue\Queue;
use MQK\Queue\QueueCollection;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisFactory;
use MQK\Registry;

/**
 * Woker的具体实现，在进程内调度Queue和Job完成具体任务
 *
 * Class WorkerConsumer
 * @package MQK\Worker
 */
class WorkerConsumer extends AbstractWorker implements Worker
{
    protected $config;
    protected $queue;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Logger
     */
    private $cliLogger;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var JobDAO
     */
    private $jobDAO;

    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var QueueCollection
     */
    private $queues;

    /**
     * @var string[]
     */
    private $queueNameList;

    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct(Config $config, $queues)
    {
        parent::__construct();

        $this->config = $config;
        $this->queueNameList = $queues;
    }

    public function run()
    {
        $this->redisFactory = RedisFactory::shared();
        $this->connection = $this->redisFactory->reconnect();

        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->cliLogger = LoggerFactory::shared()->cliLogger();
        $this->registry = new Registry($this->connection);
        $this->jobDAO = new JobDAO($this->connection);

        $this->queues = new RedisQueueCollection($this->connection);
        $this->queues->register($this->queueNameList);

        $this->logger->debug("Process {$this->id} started.");
        while ($this->alive) {
            $this->execute();
            $memoryUsage = $this->memoryGetUsage();
            if ($memoryUsage > self::M * 10) {
                exit(0);
            }
        }
        $this->logger->debug("[run] quiting");
        exit(0);
    }

    protected function memoryGetUsage()
    {
        return memory_get_usage(false);
    }

    function execute()
    {
        while (true) {
            try {
                $job = $this->queues->dequeue(!$this->config->burst());
                break;
            } catch (\RedisException $e) {
                $this->logger->error($e);
                $this->redisFactory->reconnect();
            } catch (BlockPopException $e) {
                $this->alive = false;
                $this->cliLogger->info("Worker {$this->id} is quitting.");
                return;
            }
        }
        if (null == $job) {
            $this->logger->debug("[execute] Job is null.");
            return;
        }

        if (!$this->config->fast()) {
            $this->registry->start($job);
            $this->logger->info("Job {$job->id()} is started");
        }
        try {
            $this->logger->info("Job call function {$job->func()}");
            $this->logger->info("retries {$job->retries()}");
            $arguments = $job->arguments();
            $beforeExecute = time();
            $result = @call_user_func_array($job->func(), $arguments);

            $error = error_get_last();
            error_clear_last();

            if (!empty($error)) {
                $this->logger->error($error['message']);
                $this->logger->error($job->func());
                $this->logger->error(json_encode($job->arguments()));

                throw new \Exception($error['message']);
            }

            $afterExecute = time();
            $duration = $afterExecute - $beforeExecute;
            $this->logger->debug("Function execute duration {$duration}");
            $this->cliLogger->info(sprintf("Job finished and result is %s", json_encode($result)));
            if ($afterExecute - $beforeExecute >= $job->ttl()) {
                $this->logger->warn(sprintf("Job %s is timeout", $job->id()));
            }

            if (!$this->config->fast())
                $this->registry->finish($job);
        } catch (\Exception $exception) {

            if ($exception instanceof TestTimeoutException)
                $result = null;
            else {
                $this->logger->error($exception->getMessage());
                $this->registry->fail($job);
            }
        }
    }
}
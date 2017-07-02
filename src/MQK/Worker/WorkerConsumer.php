<?php
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Config;
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

class WorkerConsumer extends AbstractWorker implements Worker
{
    protected $config;
    protected $queue;

    /**
     * @var Logger
     */
    private $logger;

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

    public function __construct(Config $config, $queues)
    {
        parent::__construct();
        $this->config = $config;
        $this->connection = (new RedisFactory())->createRedis();
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->logger->debug("here");
        $this->registry = new Registry($this->connection);

        $this->jobDAO = new JobDAO($this->connection);

        $this->queues = new RedisQueueCollection($this->connection);
        $this->queues->register($queues);
    }

    public function run()
    {
        echo "Process {$this->id} started.\n";
        while (true) {
            $this->execute();
            $memoryUsage = $this->memoryGetUsage();
            if ($memoryUsage > self::M * 10) {
                exit(0);
            }
        }
    }

    protected function memoryGetUsage()
    {
        return memory_get_usage(false);
    }

    function execute()
    {
        try {
            $this->reassignExpredJob();
        } catch (JobMaxRetriesException $e) {
            $this->logger->warning("超过最大重试次数 {$e->job()->id()}");
            $this->registry->clear("mqk:started", $e->job()->id());
            $this->jobDAO->clear($e->job());
        }
        $job = $this->queues->dequeue();
        if (null == $job) {
            return;
        }

        $this->registry->start($job);
        try {
            $this->logger->info("Job {$job->id()} is started");
            $this->logger->info("Job call function {$job->func()}");
            $this->logger->info("retries {$job->retries()}");
            $arguments = $job->arguments();
            $beforeExecute = time();
            $result = @call_user_func_array($job->func(), $arguments);
            $afterExecute = time();
            $duration = $afterExecute - $beforeExecute;
            $this->logger->info("Execute duration {$duration}");
            $this->logger->info(sprintf("Job finished %s", $result));
            if ($afterExecute - $beforeExecute >= $job->ttl()) {
                $this->logger->warn(sprintf("Job %d is timeout", $job->id()));
            }

            $error = error_get_last();
            if (!empty($error)) {
                printf("%s\n", $error['message']);
                printf("%s\n", $job->func());
                printf("%s\n", json_encode($job->arguments()));

                throw new \Exception($error['message']);
            }
            $this->registry->finish($job);
        } catch (\Exception $exception) {

            if ($exception instanceof TestTimeoutException)
                $result = null;
            else
                $this->registry->fail($job);
        }

        $this->connection->hset('result', $job->id(), $result);
        $this->connection->expire($this->id, 500);
    }

    function reassignExpredJob()
    {
        $id = $this->registry->getExpiredJob("mqk:started");
        if (null == $id)
            return;
        else {
            $this->logger->info("Remove timeout job {$id}");
        }
        $this->logger->debug("Renew enqueue Job in {$job->queue()}");
        $job = $this->jobDAO->find($id);
        $queue = $this->queues->get($job->queue());

        if (3 <= $job->retries()) {
            throw new JobMaxRetriesException($job);
        }
        if (null == $job) {
            var_dump($id);
            exit(1);
        }
        $job->increaseRetries();
        $this->jobDAO->store($job);
        $queue->enqueue($job);
        $this->registry->clear("mqk:started", $id);
    }
}
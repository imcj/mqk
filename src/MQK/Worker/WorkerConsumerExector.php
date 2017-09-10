<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Exception\TestTimeoutException;
use MQK\LoggerFactory;
use MQK\Queue\RedisQueue;
use MQK\Queue\RedisQueueCollection;
use MQK\RedisFactory;
use MQK\Registry;

class WorkerConsumerExector
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var QueueCollection
     */
    protected $queues;

    /**
     * @var \Redis
     */
    protected $connection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * 队列的名字列表
     *
     * @var string
     */
    protected $queueNameList;

    /**
     * @var RedisFactory
     */
    protected $redisFactory;

    /**
     * WorkerConsumerExector constructor.
     * @param Config $config
     * @param string[] $queueNameList
     */
    public function __construct($config, $queueNameList)
    {
        $this->config = $config;
        $this->queueNameList = $queueNameList;
        $this->redisFactory = RedisFactory::shared();
    }

    protected function run()
    {
        $this->initialize();
    }

    public function initialize()
    {
//        LoggerFactory::renewSingleInstance();
        $loggerFactory = LoggerFactory::shared();
        $this->logger = $loggerFactory->getLogger("WorkerConsume");
        $this->cliLogger = $loggerFactory->cliLogger();

        $this->logger->debug("Start new redis connection.");
        $this->connection = $this->redisFactory->createNewConnection();
        $this->registry = new Registry($this->connection);
        $this->queues = $this->buildQueues();
    }


    /**
     * @return boolean 执行成功
     */
    public function execute()
    {
        $now  = time();
        while (true) {
            try {
                $message = $this->queues->dequeue(!$this->config->burst());
                $this->updateHealth();
                break;
            } catch (\RedisException $e) {
                $this->logger->error($e);
                $this->connection = $this->redisFactory->reconnect();
            } catch (QueueIsEmptyException $e) {
                $this->alive = false;
                $this->cliLogger->info("When the burst, queue is empty worker {$this->id} will quitting.");
                return;
            }
        }
        // 可能出列的数据是空
        if (null == $message) {
//            $this->logger->debug("[execute] Job is null.");
            return;
        }
        $this->logger->debug("Pop a message {$message->id()} at {$now}.");
        if (!$this->config->fast()) {
            $this->registry->start($message);
//            $this->logger->info("Job {$job->id()} is started");
        }
        $success = true;
        try {
            $beforeExecute = time();
            $message();
            $success = true;

            $afterExecute = time();
            $duration = $afterExecute - $beforeExecute;
//            $this->cliLogger->notice("Function execute duration {$duration}");
            $messageClass = (string)get_class($message);
            $this->cliLogger->info("{$messageClass} {$message->id()} is finished");
            if ($afterExecute - $beforeExecute >= $message->ttl()) {
                $this->logger->warn(sprintf("The message %s timed out for %d seconds.", $message->id(), $message->ttl()));
//                return;
            }

            if (!$this->config->fast())
                $this->registry->finish($message);
        } catch (\Exception $exception) {
            $this->logger->error("Got an exception");
            $this->logger->error($exception->getMessage());
            $success = false;
            if ($exception instanceof TestTimeoutException) {
                $this->logger->debug("Catch timeout exception.");
            } else {
                $this->logger->error($exception->getMessage());
                $this->registry->fail($message);
            }
        }

        return $success;
    }

    protected function updateHealth()
    {

    }

    protected function buildQueues()
    {
        if ($this->config->testJobMax() > 0 ) {
            return new TestQueueCollection($this->config->testJobMax());
        } else {
            $queues = [];
            foreach ($this->queueNameList as $name) {
                $queues[] = new RedisQueue($name, $this->connection);
            }
            return new RedisQueueCollection($this->connection, $queues);
        }
    }

}
<?php
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Exception\TestTimeoutException;
use MQK\Health\HealthReporter;
use MQK\Health\WorkerHealth;
use MQK\LoggerFactory;
use MQK\Queue\MessageInvokableSync;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\RedisQueueCollection;
use MQK\Registry;

class WorkerConsumerExecutor
{
    /**
     * @var QueueCollection
     */
    protected $queues;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $burst = false;

    /**
     * @var bool
     */
    protected $fast = false;

    /**
     * @var MessageInvokableSyncController
     */
    protected $messageInvokableSyncController;

    /**
     * @var HealthReporter
     */
    protected $healthRepoter;

    /**
     * @var int
     */
    protected $consumed = 0;

    /**
     * WorkerConsumerExector constructor.
     */
    public function __construct(
        $burst,
        $fast,
        RedisQueueCollection $queues,
        Registry $registry,
        MessageInvokableSyncController $messageInvokableSyncController,
        HealthReporter $healthReporter) {

        $this->burst = $burst;
        $this->fast = $fast;
        $this->queues = $queues;
        $this->registry = $registry;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->messageInvokableSyncController = $messageInvokableSyncController;
        $this->healthRepoter = $healthReporter;
    }


    /**
     * @return boolean 执行成功
     */
    public function execute()
    {
        $now  = time();

        $message = $this->queues->dequeue(!$this->burst);

        // 可能出列的数据是空
        if (null == $message) {
            return false;
        }
        $this->consumed += 1;
        $this->healthRepoter->health()->setConsumed($this->consumed);
        $this->healthRepoter->report(WorkerHealth::DID_DEQUEUE);
        $this->logger->debug("Pop a message {$message->id()} at {$now}.");
        if (!$this->fast) {
            $this->registry->start($message);
        }

        $success = true;
        try {
            $beforeExecute = time();
            $this->healthRepoter->report(WorkerHealth::EXECUTING);
            $message();
            $this->healthRepoter->report(WorkerHealth::EXECUTED);
            if ($message instanceof MessageInvokableSync) {
                $this->messageInvokableSyncController->invoke($message);
            }

            $success = true;

            $afterExecute = time();
            $duration = $afterExecute - $beforeExecute;
            $this->logger->info("Message execute duration {$duration}");
            $messageClass = (string)get_class($message);
            $this->logger->debug("{$messageClass} {$message->id()} is finished");
            if ($afterExecute - $beforeExecute >= $message->ttl()) {
                $this->logger->warn(sprintf("The message %s timed out for %d seconds.", $message->id(), $message->ttl()));
            }

            if (!$this->fast)
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
}
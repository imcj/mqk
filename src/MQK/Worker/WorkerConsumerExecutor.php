<?php
namespace MQK\Worker;


use Monolog\Logger;
use MQK\Exception\TestTimeoutException;
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
     * WorkerConsumerExector constructor.
     */
    public function __construct(
        $burst,
        $fast,
        RedisQueueCollection $queues,
        Registry $registry,
        MessageInvokableSyncController $messageInvokableSyncController
        ) {
        $this->burst = $burst;
        $this->fast = $fast;
        $this->queues = $queues;
        $this->registry = $registry;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->messageInvokableSyncController = $messageInvokableSyncController;
    }


    /**
     * @return boolean 执行成功
     */
    public function execute()
    {
        $now  = time();
        while (true) {
            $message = $this->queues->dequeue(!$this->burst);
            break;
        }
        // 可能出列的数据是空
        if (null == $message) {
            return;
        }
        $this->logger->debug("Pop a message {$message->id()} at {$now}.");
        if (!$this->fast) {
            $this->registry->start($message);
        }

        $success = true;
        try {
            $beforeExecute = time();

            $message();
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
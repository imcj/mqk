<?php
namespace MQK\Worker;

use Monolog\Logger;
use MQK\Error\ErrorHandler;
use MQK\Exception\SkipFailureRegistryException;
use MQK\Helper\ByteSize;
use MQK\Queue\Message;
use MQK\Queue\MessageNormal;
use MQK\Queue\NotImplementedInvoke;
use MQK\Queue\Outbound\NotificationCenter;
use MQK\Queue\Outbound\OutboundService;
use MQK\SearchExpiredMessage;
use MQK\Health\HealthReporter;
use MQK\Health\WorkerHealth;
use MQK\LoggerFactory;
use MQK\Queue\MessageInvokableSync;
use MQK\Queue\MessageInvokableSyncController;
use MQK\Queue\RedisQueueCollection;
use MQK\Registry;
use MQK\Time;

class ConsumerExecutorWorker
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
     * @var SearchExpiredMessage
     */
    protected $expiredFindder;

    /**
     * @var MessageInvokableSyncController
     */
    protected $messageInvokableSyncController;

    /**
     * @var WorkerHealth
     */
    protected $health;

    /**
     * @var HealthReporter
     */
    protected $healthRepoter;

    /**
     * @var int
     */
    protected $consumed = 0;

    /**
     * @var ErrorHandler[]
     */
    protected $errorHandlers = [];

    /**
     * @var boolean
     */
    protected $isSearchExpiredMessage = false;

    /**
     * @var SearchExpiredMessage
     */
    protected $searchExpiredMessage;

    protected $failure = 0;

    protected $success = 0;

    protected $alive = true;

    const M = 1024 * 1024;

    protected $memoryLimit;

    /**
     * @var ByteSize
     */
    private $byteSize;

    /**
     * @var OutboundService
     */
    protected $outboundService;

    /**
     * ConsumerExecutorWorker constructor.
     *
     * @param boolean $burst
     * @param boolean $fast
     * @param RedisQueueCollection $queues
     * @param Registry $registry
     * @param MessageInvokableSyncController $messageInvokableSyncController
     * @param HealthReporter $healthReporter
     * @param ErrorHandler[] $errorHandlers
     */
    public function __construct(
        $burst,
        $fast,
        $queues,
        $memoryLimit,
        Registry $registry,
        $expiredFinder,
        MessageInvokableSyncController $messageInvokableSyncController,
        WorkerHealth $workerHealth,
        HealthReporter $healthReporter,
        $errorHandlers,
        OutboundService $outboundService) {

        $this->burst = $burst;
        $this->fast = $fast;
        $this->queues = $queues;
        $this->memoryLimit = $memoryLimit;
        $this->registry = $registry;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->expiredFindder = $expiredFinder;
        $this->messageInvokableSyncController = $messageInvokableSyncController;
        $this->healthRepoter = $healthReporter;
        $this->errorHandlers = $errorHandlers;
        $this->byteSize = new ByteSize();
        $this->outboundService = $outboundService;
    }

    public function execute()
    {
        $this->healthRepoter->report(WorkerHealth::STARTED);
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);

        $formatNameList = join(", ", $this->queues->nameList());
        $this->logger->debug("Watch queue list {$formatNameList}");
        $memoryLimit = $this->memoryLimit / 1024 / 1024;
        $this->logger->debug("Memory limit {$memoryLimit}m");
        $this->workerStartTime = Time::micro();
        while ($this->alive) {
            if ($this->searchExpiredMessage) {
                $this->searchExpiredMessage->process();
            }

            try {
                $this->healthRepoter->report(WorkerHealth::EXECUTING);
                $success = $this->consumeOneMessage();
                $this->healthRepoter->report(WorkerHealth::EXECUTED);

                if ($success)
                    $this->success += 1;

            } catch (EmptyQueueException $e) {
                $this->alive = false;
                $this->logger->info("When the burst, queue is empty worker {$this->id} will quitting.");
            }

            $memoryUsage = $this->memoryGetUsage();
            // TODO: Fixed memory leak
//            $this->logger->debug("memory usage: \n{$this->byteSize->m($memoryUsage)} / {$this->memoryLimit}");
            if ($memoryUsage > $this->memoryLimit) {
                $this->logger->info("Restart process, out of memory {{$this->byteSize->m($memoryUsage)} / {{$this->byteSize->m($this->memoryLimit)}");
                break;
            }
            pcntl_signal_dispatch();
//            $health->setDuration(Time::micro() - $this->workerStartTime);
        }
        $this->workerEndTime = Time::micro();
        $this->didQuit();
        exit(0);
    }

    function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }


    /**
     * @return boolean 执行成功
     */
    public function consumeOneMessage()
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
        $this->logger->debug("Did dequeue a message {$message->id()} at {$now}.");
        if (!$this->fast) {
            $this->registry->start($message);
        }

        $success = true;
        try {
            $beforeExecute = $this->microtimeFloat();
            $this->logger->debug('Message will execute');
            $this->healthRepoter->report(WorkerHealth::EXECUTING);

            try {
                $messageReturns = $message();
            } catch (NotImplementedInvoke $e) {
                if ($message instanceof MessageNormal) {
                    /**
                     * @var MessageNormal
                     */
                    $messageNormal = $message;

                    $messageReturns = $this->outboundService->launch(
                        $messageNormal->routerKey(),
                        $messageNormal
                    );
                } else {
                    throw $e;
                }
            }

            $this->healthRepoter->report(WorkerHealth::EXECUTED);
            if ($message instanceof MessageInvokableSync) {
                $this->messageInvokableSyncController->invoke($message);
            }

            $success = true;

            $afterExecute = $this->microtimeFloat();
            $duration = $afterExecute - $beforeExecute;

            if (is_array($messageReturns)) {
                $messageReturnsString = var_export($messageReturns, true);
            } else {
                try {
                    $messageReturnsString = (string)$messageReturns;
                } catch (\Exception $e) {
                    if (is_object($messageReturns))
                        $messageReturnsString = "Object";
                    else
                        $messageReturnsString = "Unknow";
                }
            }

            $this->logger->info("Task {$message->id()} in {$duration}s: {$messageReturnsString}");
            if ($afterExecute - $beforeExecute >= $message->ttl()) {
                $this->logger->warn(sprintf("The message %s timed out for %d seconds.", $message->id(), $message->ttl()));
            }

            if (!$this->fast)
                $this->registry->finish($message);

            if ($this->isSearchExpiredMessage) {
                $this->expiredFindder->process();
            }
        } catch (\Exception $exception) {
            $success = false;
            if ($exception instanceof SkipFailureRegistryException) {
                $this->logger->debug("Catch skip failure of registry exception.");
                return;
            }
            foreach ($this->errorHandlers as $errorHandler)
                $errorHandler->got($exception);

            if (!$this->fast)
                $this->registry->fail($message);
        }

        return $success;
    }

    public function quit()
    {
        $this->alive = false;
        $this->logger->debug('set alive to false');
    }

    public function graceFullQuit()
    {
        $this->alive = false;
        $this->logger->debug('will grace full quit');
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

    public function searchExpiredMessage()
    {
        return $this->searchExpiredMessage;
    }

    public function setSearchExpiredMessage($searchExpiredMessage)
    {
        $this->searchExpiredMessage = $searchExpiredMessage;
    }

    public function setIsSearchExpiredMessage($isSearchExpiredMessage)
    {
        $this->isSearchExpiredMessage = $isSearchExpiredMessage;
    }

    public function goingToDie()
    {
        $this->alive = false;
    }
}
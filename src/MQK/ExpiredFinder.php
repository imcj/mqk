<?php
namespace MQK;


use MQK\Job\JobDAO;
use MQK\Queue\Queue;
use MQK\Queue\QueueCollection;

/**
 * 处理执行过期的任务
 * @package MQK
 */
class ExpiredFinder
{
    /**
     * @var \Redis
     */
    private $connection;

    /**
     * @var JobDAO
     */
    private $jobDAO;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var QueueCollection
     */
    private $queues;

    /**
     * @var bool
     */
    private $cluster;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * ExpiredFinder constructor.
     * @param \Redis $connection
     * @param JobDAO $jobDAO
     * @param Registry $registry
     * @param QueueCollection $queues
     * @param bool $cluster
     */
    public function __construct(
        $connection,
        JobDAO $jobDAO,
        Registry $registry,
        QueueCollection $queues,
        $cluster = false) {

        $this->connection = $connection;
        $this->jobDAO = $jobDAO;
        $this->registry = $registry;
        $this->queues = $queues;
        $this->cluster = $cluster;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    /**
     * 超着过期的任务并处理
     * @return void
     */
    public function process()
    {
        /**
         * @var $queue Queue
         */
        $id = $this->registry->queryExpiredMessage("mqk:started");
        if (null == $id)
            return;

        $this->registry->clear("mqk:started", $id);
        try {
            $message = $this->jobDAO->find($id);
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
            return;
        }
        $this->logger->debug("Find expired message {$message->id()}");
        $this->logger->debug(json_encode($message->jsonSerialize()));
        try {
            $queue = $this->queues->get($message->queue());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error(json_encode($message->jsonSerialize()));
            return;
        }

//        $this->logger->debug("The message {$message->id()} retries {$message->retries()}");
        if ($message->retries() > 2) {
            $this->clearRetryFailed($message);
            return;
        }
        if (null == $message) {
            $this->logger->error("[reassignExpredJob] Job is null");
        }

        if (!$this->cluster)
            $this->connection->multi();

        $message->increaseRetries();
//        $this->logger->debug(json_encode($message->jsonSerialize()));
        $this->jobDAO->store($message);
        $queue->enqueue($message);

        if (!$this->cluster)
            $this->connection->exec();
    }

    /**
     * 清理重试失败的任务
     *
     * @param $job
     * @return void
     */
    function clearRetryFailed($job)
    {
        $this->logger->warning("超过最大重试次数 {$job->id()}");
        $this->registry->clear("mqk:started", $job->id());
        $this->jobDAO->clear($job);
    }
}
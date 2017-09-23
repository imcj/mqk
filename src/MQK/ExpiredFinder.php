<?php
namespace MQK;


use MQK\Queue\Message\MessageDAO;
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
     * @var MessageDAO
     */
    private $messageDAO;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var bool
     */
    private $cluster;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var integer
     */
    private $retry;

    /**
     * ExpiredFinder constructor.
     * @param \Redis $connection
     * @param MessageDAO $messageDAO
     * @param Registry $registry
     * @param QueueCollection $queues
     * @param bool $cluster
     * @param integer $retry
     */
    public function __construct(
        $connection,
        MessageDAO $messageDAO,
        Registry $registry,
        Queue $queue,
        $cluster = false,
        $retry) {

        $this->connection = $connection;
        $this->messageDAO = $messageDAO;
        $this->registry = $registry;
        $this->queue = $queue;
        $this->cluster = $cluster;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->retry = $retry;
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
            $message = $this->messageDAO->find($id);
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
            return;
        }
        $this->logger->debug("Find expired message {$message->id()}");
        $this->logger->debug(json_encode($message->jsonSerialize()));

//        $this->logger->debug("The message {$message->id()} retries {$message->retries()}");
        if (null != $message && $message->maxRetry() > 0) {
            $retry = $message->maxRetry();
            $this->logger->debug("Message setting retry {$message->maxRetry()}");
        } else
            $retry = $this->retry;

        var_dump($retry);

        if ($message->retries() > $retry) {
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
        $this->messageDAO->store($message);
        $this->queue->enqueue($message->queue(), $message);

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
        $this->messageDAO->clear($job);
    }
}
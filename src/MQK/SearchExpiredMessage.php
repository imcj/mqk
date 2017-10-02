<?php
namespace MQK;

use MQK\Queue\Message\MessageDAO;
use MQK\Queue\Queue;
use MQK\Queue\QueueCollection;

/**
 * 处理执行过期的任务
 * @package MQK
 */
class SearchExpiredMessage
{
    /**
     * @var \RedisProxy
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
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var integer
     */
    private $maxRetries;

    private $lastPrintTime;

    /**
     * SearchExpiredMessage constructor.
     * @param \Redis $connection
     * @param MessageDAO $messageDAO
     * @param Registry $registry
     * @param QueueCollection $queues
     * @param integer $maxRetries
     */
    public function __construct(
        $connection,
        MessageDAO $messageDAO,
        Registry $registry,
        Queue $queue,
        $maxRetries) {

        $this->connection = $connection;
        $this->messageDAO = $messageDAO;
        $this->registry = $registry;
        $this->queue = $queue;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->maxRetries = $maxRetries;
    }

    /**
     * 超着过期的任务并处理
     * @return void
     */
    public function process()
    {
        if ($this->lastPrintTime < time() - 10) {
            $this->logger->debug("开始搜索超时消息");
            $this->lastPrintTime = time();
        }

        /**
         * @var $queue Queue
         */
        $pid = getmypid();
        $id = $this->registry->queryExpiredMessage("mqk:started");
        if (null == $id) {
            return;
        }

        $this->registry->clear("mqk:started", $id);
        try {
            $message = $this->messageDAO->find($id);
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
            return;
        }
        $this->logger->debug("Find expired message {$message->id()}");
        $this->logger->debug(json_encode($message->jsonSerialize()));

        if (null !== $message && is_integer($message->maxRetry())) {
            $retry = $message->maxRetry();
            $this->logger->debug(
                "Message retry times {$message->maxRetry()}"
            );
        } else
            $retry = $this->maxRetries;

        if ($message->retries() >= $retry - 1) {
            $this->clearRetryFailed($message);
            return;
        }
        if (null == $message) {
            $this->logger->error("Message is null");
        }

        $this->connection->multi();

        $message->increaseRetries();
        $this->messageDAO->store($message);
        $this->queue->enqueue($message->queue(), $message);

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
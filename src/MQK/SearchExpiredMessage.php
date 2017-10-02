<?php
namespace MQK;

use MQK\Queue\Message\MessageDAO;
use MQK\Queue\Queue;

/**
 * 处理执行过期的任务
 * @package MQK
 */
class SearchExpiredMessage
{
    /**
     * @var RedisProxy
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

    /**
     * 上次打印信息的时间
     *
     * @var integer
     */
    private $lastPrintTime;

    /**
     * SearchExpiredMessage constructor.
     * @param RedisProxy $connection
     * @param MessageDAO $messageDAO
     * @param Registry $registry
     * @param Queue $queue
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
            $this->logger->debug("Search timeout message");
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
        $this->logger->debug(
            "Find expired message {$message->id()}",
            $message->jsonSerialize()
        );

        if (null !== $message && is_integer($message->maxRetry())) {
            $maxRetries = $message->maxRetry();
        } else
            $maxRetries = $this->maxRetries;

        $retries = $message->retries() + 1;
        $this->logger->debug(
            "Max retries {$maxRetries} current {$retries}"
        );

        if ($message->retries() >= $maxRetries - 1) {
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
     * @param $message
     * @return void
     */
    function clearRetryFailed($message)
    {
        $this->logger->warning(
            "Exceeds the maximum number of retries {$message->id()}"
        );
        $this->registry->clear("mqk:started", $message->id());
        $this->messageDAO->clear($message);
    }
}
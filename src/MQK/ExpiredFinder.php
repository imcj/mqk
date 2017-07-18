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
        $id = $this->registry->getExpiredJob("mqk:started");
        if (null == $id)
            return;

        $this->registry->clear("mqk:started", $id);
        try {
            $job = $this->jobDAO->find($id);
        } catch(\Exception $e) {
            $this->logger->error($e->getMessage());
            return;
        }
//        $this->logger->debug("[process] Find job {$job->id()}");
//        $this->logger->debug(json_encode($job->jsonSerialize()));
        try {
            $queue = $this->queues->get($job->queue());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error(json_encode($job->jsonSerialize()));
            return;
        }

//        $this->logger->debug("The job {$job->id()} retries {$job->retries()}");
        if ($job->retries() > 2) {
            $this->clearRetryFailed($job);
            return;
        }
        if (null == $job) {
            $this->logger->error("[reassignExpredJob] Job is null");
        }

        if (!$this->cluster)
            $this->connection->multi();

        $job->increaseRetries();
//        $this->logger->debug(json_encode($job->jsonSerialize()));
        $this->jobDAO->store($job);
        $queue->enqueue($job);

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
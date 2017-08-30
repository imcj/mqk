<?php
namespace MQK\Worker;


class WorkerConsumerExector extends AbstractWorker
{
    protected function initialize()
    {

    }

    protected function execute()
    {
        while (true) {
            try {
                $message = $this->queues->dequeue(!$this->config->burst());
                break;
            } catch (\RedisException $e) {
                $this->logger->error($e);
                $this->redisFactory->reconnect();
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

        if (!$this->config->fast()) {
            $this->registry->start($message);
//            $this->logger->info("Job {$job->id()} is started");
        }
        try {
            $beforeExecute = time();
            $message();
            $this->success += 1;

            $afterExecute = time();
            $duration = $afterExecute - $beforeExecute;
//            $this->cliLogger->notice("Function execute duration {$duration}");
            $messageClass = (string)get_class($message);
            $this->cliLogger->info("{$messageClass} {$message->id()} is finished");
            if ($afterExecute - $beforeExecute >= $message->ttl()) {
                $this->logger->warn(sprintf("The job %s timed out for %d seconds.", $message->id(), $message->ttl()));
//                return;
            }

            if (!$this->config->fast())
                $this->registry->finish($message);
        } catch (\Exception $exception) {

            if ($exception instanceof TestTimeoutException)
                $result = null;
            else {
                $this->failure += 1;

                $this->logger->error($exception->getMessage());
                $this->registry->fail($message);
            }
        }
    }
}
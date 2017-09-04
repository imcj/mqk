<?php
namespace MQK\Worker;


use MQK\Exception\TestTimeoutException;

class WorkerConsumerExector extends AbstractWorker
{
    protected function initialize()
    {

    }

    protected function execute()
    {
        $now  = time();
        while (true) {
            try {
                $message = $this->queues->dequeue(!$this->config->burst());
                $this->updateHealth();
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
        $this->logger->debug("Pop a message {$message->id()} at {$now}.");
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
                $this->logger->warn(sprintf("The message %s timed out for %d seconds.", $message->id(), $message->ttl()));
//                return;
            }

            if (!$this->config->fast())
                $this->registry->finish($message);
        } catch (\Exception $exception) {
            $this->logger->error("Got an exception");
            $this->logger->error($exception->getMessage());
            if ($exception instanceof TestTimeoutException) {
                $this->logger->debug("Catch timeout exception.");
            } else {
                $this->failure += 1;

                $this->logger->error($exception->getMessage());
                $this->registry->fail($message);
            }
        }
    }

    protected function updateHealth()
    {
    }
}
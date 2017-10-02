<?php
namespace MQK\Command\InvokeCommand;


class Produce
{
    protected $numbers;

    protected $queues;

    protected $queue;

    protected $funcName;

    protected $arguments;

    public function __construct(
        $funcName,
        $arguments,
        $numbers,
        $queue,
        $queues,
        $ttl = null) {

        $this->funcName = $funcName;
        $this->arguments = $arguments;
        $this->numbers = $numbers;
        $this->queue = $queue;
        $this->queues = $queues;
        $this->ttl = $ttl;
    }

    public function run()
    {
        for ($i = 0; $i < $this->numbers; $i++) {
            $payload = new \stdClass();
            $payload->func = $this->funcName;
            $payload->arguments = $this->arguments;

            foreach ($this->queues as $queueName) {
                $message = new \MQK\Queue\MessageInvokable(
                    uniqid(),
                    "invokable",
                    $queueName,
                    $this->ttl ? $this->ttl : 600,
                    $payload
                );
                $this->queue->enqueue($queueName, $message);
            }
        }
    }
}
<?php
namespace MQK\Queue;


class MessageInvokable extends Message
{
    private $func;
    private $arguments;

    public function __construct($id, $queue = null, $ttl = 600, $payload = null)
    {
        parent::__construct($id, $queue, $ttl, $payload);

        $this->func = $payload->func;
        $this->arguments = $payload->arguments;
    }

    public function __invoke()
    {
        $this->logger->info("Job call function {$this->func()}");
        $this->logger->info("retries {$this->retries()}");
        $arguments = $this-->arguments();
        $result = @call_user_func_array($this->func(), $arguments);

        $error = error_get_last();
        error_clear_last();

        if (!empty($error)) {
            $this->logger->error($error['message']);
            $this->logger->error($this->func());
            $this->logger->error(json_encode($this->arguments()));

            throw new \Exception($error['message']);
        }
    }
}
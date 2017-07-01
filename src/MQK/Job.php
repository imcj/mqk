<?php
namespace MQK;

use MQK\Queue\Queue;

class Job implements \JsonSerializable
{
    /**
     * id
     *
     * @var string
     */
    private $id;

    private $func;
    private $arguments;
    private $connection;
    private $ttl = 500;

    /**
     * Job 的重试次数
     * @var int
     */
    private $retries = 0;

    /**
     * @var int
     */
    private $delay;

    /**
     * @var string
     */
    private $queue;

    public function __construct($id, $func, $arguments)
    {
        $this->id = $id == null ? uniqid() : $id;
        $this->func = $func;
        $this->arguments = $arguments;
        $this->result = null;
    }

    public function id()
    {
        return $this->id;
    }

    public function func()
    {
        return $this->func;
    }

    public function arguments()
    {
        return $this->arguments;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function result()
    {
        return $this->connection->hget('result', $this->id());
    }

    public function delay()
    {
        return $this->delay;
    }

    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'func' => $this->func,
            'arguments' => $this->arguments,
            'delay' => $this->delay,
            'ttl' => $this->ttl,
            'queue' => $this->queue(),
            'retries' => $this->retries
        );
    }

    public static function job($json)
    {
        $job = new Job($json->id, $json->func, $json->arguments);
        $job->setTtl($json->ttl);
        $job->setQueue($json->queue);
        $job->setRetries((int)$json->retries);
        return $job;
    }

    public function ttl()
    {
        return $this->ttl;
    }

    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    public function retries()
    {
        return $this->retries;
    }

    public function increaseRetries()
    {
        $this->retries += 1;
    }

    public function setRetries($retries)
    {
        $this->retries = $retries;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    public function queue()
    {
        return $this->queue;
    }
}
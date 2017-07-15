<?php
namespace MQK\Exception;


use MQK\Queue\Queue;
use Throwable;

class QueueIsEmptyException extends \Exception
{
    public function __construct($queue, $code = 0, Throwable $previous = null)
    {
        $name = $queue ? $queue->name() : "?";
        parent::__construct("Queue {$name} is empty.", $code, $previous);
    }
}
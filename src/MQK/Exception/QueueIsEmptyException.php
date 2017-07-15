<?php
namespace MQK\Exception;


use MQK\Queue\Queue;
use Throwable;

class QueueIsEmptyException extends \Exception
{
    public function __construct(Queue $queue, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Queue {$queue->name()} is empty.", $code, $previous);
    }
}
<?php
namespace MQK\Exception;


use MQK\Job;
use Throwable;

class JobMaxRetriesException extends \Exception
{
    /**
     * @var Job
     */
    private $job;

    public function __construct(Job $job, Throwable $previous = null)
    {
        $this->job = $job;
        parent::__construct("", 0, $previous);
    }

    public function job()
    {
        return $this->job;
    }
}
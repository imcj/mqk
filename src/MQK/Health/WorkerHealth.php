<?php
namespace MQK\Health;

class WorkerHealth
{
    const STARTED = "started";
    const DEQUEUED = "dequeued";
    const EXECUTING = "executing";
    const EXECUTED = "executed";
    const QUITtING = "quitting";
    const QUITED = "quited";

    /**
     * Uniqid of worker
     *
     * @var string
     */
    private $id;

    /**
     * Unix timestamp of worker
     * @var integer
     */
    private $lastUpdatedAt;

    /**
     * Readable of last updated datetime of worker
     * like 2017-01-01 01:01
     *
     * @var string
     */
    private $lastUpdatedAtHuman;

    /**
     * Status of worker
     *
     * @var string
     */
    private $status;

    /**
     * Number of consumed of worker
     * @var integer
     */
    private $consumed;

    /**
     * Duration time of worker, second
     * @var integer
     */
    private $duration;

    /**
     * Process id of worker
     * @var integer
     */
    private $processId;

    public function id()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function lastUpdatedAt()
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt($lastUpdatedAt)
    {
        $this->lastUpdatedAt = $lastUpdatedAt;
    }

    public function lastUpdatedAtHuman()
    {
        return $this->lastUpdatedAtHuman;
    }

    public function setLastUpdatedAtHuman($lastUpdatedAtHuman)
    {
        $this->lastUpdatedAtHuman = $lastUpdatedAtHuman;
    }

    public function status()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function consumed()
    {
        return $this->consumed;
    }

    /**
     * @param int $consumed
     */
    public function setConsumed(int $consumed)
    {
        $this->consumed = $consumed;
    }

    public function duration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration)
    {
        $this->duration = $duration;
    }

    public function processId()
    {
        return $this->processId;
    }

    /**
     * @param int $processId
     */
    public function setProcessId(int $processId)
    {
        $this->processId = $processId;
    }
}
<?php
namespace MQK\Health;


use Symfony\Component\Serializer\Serializer;

class HealthReporterRedis implements HealthReporter
{
    private $redis;

    /**
     * @var WorkerHealth
     */
    private $health;

    private $interval = 5;

    /**
     * @var integer
     */
    private $lastReportAt;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(WorkerHealth $health, $redis, $serializer, $interval = 5)
    {
        $this->health = $health;
        $this->redis = $redis;
        $this->serializer = $serializer;
        $this->interval = $interval;
    }

    /**
     * Report worker process for health
     *
     * @return void
     */
    public function report($status)
    {
        $this->health->setStatus($status);
        if ($this->interval + $this->lastReportAt > time())
            return;

        $normalized = $this->serializer->normalize($this->health);
        $this->redis->multi();

        $key = "mqk:worker:status:{$this->health->id()}";

        foreach ($normalized as $propertyName => $propertyValue) {
            $this->redis->hSet($key, $propertyName, $propertyValue);
        }
        $this->redis->expire($key, $this->interval * 2);
        $this->redis->exec();

        $this->lastReportAt = time();
    }

    /**
     * Health reporter report frien
     *
     * @return integer
     */
    function interval()
    {
        return $this->interval;
    }

    function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return WorkerHealth
     */
    public function health()
    {
        return $this->health;
    }

    /**
     * @param $health
     * @return WorkerHealth
     */
    function setHealth($health)
    {
        $this->health = $health;
    }
}
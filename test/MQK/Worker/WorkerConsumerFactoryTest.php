<?php
namespace MQK\Worker;


use PHPUnit\Framework\TestCase;

class WorkerConsumerFactoryTest extends TestCase
{
    public function testCreate()
    {
        $workerConsumerFactory = new ConsumerWorkerFactory("", null);
        $workerConsumer = $workerConsumerFactory->create();
        $this->assertEquals(true, true);
    }
}
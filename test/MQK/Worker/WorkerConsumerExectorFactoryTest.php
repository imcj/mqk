<?php
namespace MQK\Worker;


use PHPUnit\Framework\TestCase;

class WorkerConsumerExectorFactoryTest extends TestCase
{
    public function testCreate()
    {
        $workerConsumerExecutorFactory = new WorkerConsumerExecutorFactory();
        $exector = $workerConsumerExecutorFactory->create();
        $this->assertEquals(true, true);
    }
}
<?php
namespace MQK\Worker;


use PHPUnit\Framework\TestCase;

class WorkerConsumerExectorFactoryTest extends TestCase
{
    public function testCreate()
    {
        $workerConsumerExecutorFactory = new WorkerConsumerExecutorFactory(
            false,
            false,
            'redis://127.0.0.1',
            'queue_',
            ['default'],
            3,
            false,
            []
        );
        $consumerExecutor = $workerConsumerExecutorFactory->create();
        $this->assertEquals(true, true);
    }
}
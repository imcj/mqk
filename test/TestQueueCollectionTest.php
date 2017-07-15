<?php

class TestQueueCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testDequeue()
    {
        $queues = new \MQK\Queue\TestQueueCollection(10);
        $job = $queues->dequeue();
        $this->assertEquals("6718b3b00d429e825c87db4e022b2fab", $job->id());
    }
}
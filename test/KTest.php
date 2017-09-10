<?php
use MQK\Queue\MessageInvokableSync;
use PHPUnit\Framework\TestCase;

class KTest extends TestCase
{
    public function testInvoke()
    {
        \K::invoke('MQK\Test\Calculator::sum', 1, 1);
    }

    public function testSync()
    {
        $invokeSync = new InvokeGroup(
            'sum1', MessageInvokableSync::invoke('\MQK\Test\Calculator::sum', 1, 1),
            'sum2', MessageInvokableSync::invoke('\MQK\Test\Calculator::sum', 2, 2)
        );
        $invoke = K::invokeSync($invokeSync);

        $this->assertArrayHasKey('sum1', $invoke);
        $this->assertArrayHasKey('sum2', $invoke);

        $sum1 = $invoke['sum1'];
        $sum2 = $invoke['sum2'];

        $this->assertInstanceOf(MessageInvokableSync::class, $sum1);
        $this->assertInstanceOf(MessageInvokableSync::class, $sum2);

        $this->assertEquals(2, $sum1);
        $this->assertEquals(4, $sum2);
    }
}
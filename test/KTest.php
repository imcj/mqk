<?php
use MQK\Queue\MessageInvokableSync;
use PHPUnit\Framework\TestCase;

class KTest extends TestCase
{
    public function testInvoke()
    {
        \K::invoke('MQK\Test\Calculator::sum', 1, 1);
    }

    public function testSyncMulti()
    {
        $invoke = K::invokeSync(array(
            "sum1" => array(
                "method" => '\MQK\Test\Calculator::sum',
                "arguments" => array(1, 1)
            ),
            'sum2' => array(
                "method" => '\MQK\Test\Calculator::sum',
                "arguments" => array(2, 2)
            ),
        ));

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
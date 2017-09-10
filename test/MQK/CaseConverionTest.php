<?php
namespace MQK;

use PHPUnit\Framework\TestCase;

class CaseConverionTest extends TestCase
{
    public function testSnakeToCamel()
    {
        $this->assertEquals("MessageInvokableSync", CaseConverion::snakeToCamel("message_invokable_sync"));
    }
}
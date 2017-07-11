<?php

use PHPUnit\Framework\TestCase;

class KTest extends TestCase
{
    public function testInvoke()
    {
        \K::invoke('MQK\Test\Calculator::sum', 1, 1);
    }
}
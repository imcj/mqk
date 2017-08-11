<?php

class MQKHook implements \MQK\Hook\Hook
{
    /**
     * @var Closure
     */
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function boot()
    {
        $this->callback();
    }
}

class HookTest extends \PHPUnit\Framework\TestCase
{
    public function testBoot()
    {
        $success = false;
        $hookNotification = new \MQK\Hook\HookNotification(function () use (&$success) {
            $success = true;
        });

        $hookNotification->boot();

        $this->assertTrue($success);
    }
}
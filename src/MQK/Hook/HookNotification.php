<?php
namespace MQK\Hook;


class HookNotification
{
    /**
     * @var Hook
     */
    private $hook;

    public function __construct()
    {
        if (class_exists("MQKHook")) {
//            $this->hook = new "MQKHook"();
        }
    }

    public function boot()
    {
//        $this->hook->boot();
    }
}
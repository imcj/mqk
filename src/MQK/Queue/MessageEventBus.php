<?php
namespace MQK\Queue;


use Symfony\Component\EventDispatcher\EventDispatcher;

class MessageEventBus
{
    protected static $instance;

    protected $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function addListener($name, $callback)
    {
        $this->dispatcher->addListener($name, $callback);
    }

    public function dispatch($eventName, $event = null)
    {
        $this->dispatcher->dispatch($eventName, $event);
    }

    public static function shared()
    {
        if (self::$instance == null)
            self::$instance = new self;
        return self::$instance;
    }
}
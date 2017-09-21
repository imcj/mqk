<?php
namespace MQK\Queue;


use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageEventBus
{
    protected static $instance;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function addListener($name, $callback)
    {
        $this->dispatcher->addListener($name, $callback);
    }

    /**
     * Add event subscriber
     *
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function addSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        $this->dispatcher->addSubscriber($eventSubscriber);
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
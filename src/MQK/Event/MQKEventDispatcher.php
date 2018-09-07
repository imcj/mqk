<?php
namespace MQK\Event;


use Symfony\Component\EventDispatcher\EventDispatcher;

class MQKEventDispatcher extends EventDispatcher
{
    /**
     * @var MQKEventDispatcher
     */
    protected static $shared;

    private function __construct()
    {
    }

    public static function shared()
    {
        if (null == self::$shared) {
            self::$shared = new MQKEventDispatcher();
        }

        return self::$shared;
    }
}
<?php
namespace MQK;


trait SingletonTrait
{
    protected static $instance;

    public static function shared()
    {
        if (self::$instance == null)
            self::$instance = new self;
        return self::$instance;
    }
}
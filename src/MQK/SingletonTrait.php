<?php
namespace MQK;


trait SingletonTrait
{
    protected static $instance;

    public static function renewSingleInstance()
    {
        self::$instance = null;
        return self::shared();
    }

    public static function shared()
    {
        if (self::$instance == null)
            self::$instance = new self;
        return self::$instance;
    }
}
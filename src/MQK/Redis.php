<?php
namespace MQK;


class Redis extends \Redis
{
    /**
     * @var Redis
     */
    private $defaultConnection;

    static public function defaultConnection()
    {
        return self::$defaultConnection;
    }

    static public function setDefaultConnection(Redis $redis)
    {
        self::$defaultConnection = $redis;
    }
}
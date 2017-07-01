<?php
namespace MQK;


class RedisFactory
{
    private static $connection;

    public function createRedis()
    {
        if (null != self::$connection) {
            return self::$connection;
        }

        $config = Config::defaultConfig();
        $redis = new \Redis();
        $redis->connect($config->host());

        self::$connection = $redis;

        return $redis;
    }


}
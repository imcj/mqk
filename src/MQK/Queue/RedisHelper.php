<?php
namespace MQK\Queue;


use AD7six\Dsn\Dsn;

class RedisHelper
{
    /**
     * @param string $dsn
     * @return array
     */
    public function dsnToRedis($dsn)
    {
        $dsn = Dsn::parse($dsn);
        $port = $dsn->port ? $dsn->port : 6379;
        $options = [];
        if ($dsn->pass) {
            $options['password'] = $dsn->pass;
        }
        $options['port'] = $port;
        $options['host'] = $dsn->host;

        if ($dsn->path) {
            $database = substr($dsn->path, 1);
            $options['database'] = $database;
        }
        return $options;
    }

    /**
     * @param $host
     * @param $port
     * @param $password
     * @param $database
     * @return string
     */
    public function toDsn($host, $port, $password, $database)
    {

    }
}
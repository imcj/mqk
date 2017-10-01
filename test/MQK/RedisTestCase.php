<?php
namespace MQK;


use PHPUnit\Framework\TestCase;

class RedisTestCase extends TestCase
{
    /**
     * @var \Redis
     */
    protected $connection;

    public function setUp()
    {
        $this->connection = new RedisProxy('redis://127.0.0.1');
        $this->connection->connect();
        $this->connection->flushAll();
    }
}
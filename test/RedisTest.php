<?php

use PHPUnit\Framework\TestCase;
use MQK\RedisFactory;

class RedisTest extends TestCase
{
    /**
     * @var Redis
     */
    private $redis;

    public function setUp()
    {
        $this->redis = (RedisFactory::shared())->createRedis();
        $this->redis->flushAll();
    }

    public function testSet()
    {
        $redis = RedisFactory::shared()->createRedis();
        $redis->set("test", 1);
        $test = $redis->get("test");
        $this->assertEquals("1", $test);
    }

    public function testZRangeByScore()
    {
        $this->redis->zAdd("test", 1, "test");
        $z = $this->redis->zRangeByScore("test", 0, 1);
        $this->assertTrue(in_array("test", $z));
    }

    public function testZRangeByScoreWithPipeline()
    {
        $pipeline = $this->redis->multi();
        $pipeline->zAdd("test", 1, "test");
        $z = $pipeline->zRangeByScore("test", 0, 1)->exec();
        $this->assertTrue(in_array("test", $z[1]));
    }

    public function testPipeline()
    {
        $this->redis->multi();
        $this->redis->set("a", 1);
        $this->redis->set("b", 2);
        $this->redis->get("a");
        $this->redis->get("b");
        $result = $this->redis->exec();
        $this->assertEquals(array(true, true, "1", "2"), $result);
    }
}
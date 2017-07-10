<?php
namespace MQK\Test;

use MQK\Exception\TestTimeoutException;
use MQK\RedisFactory;

class Calculator
{
    public static function sum($a, $b)
    {
//        echo $a + $b . "\n";
        return $a + $b;
    }

    public static function sumCrash($a, $b)
    {
        throw new TestTimeoutException("Test");
    }

    public static function sumTimeout($a, $b)
    {
        echo "Sum test";
        $redis = RedisFactory::shared()->createRedis();
        $idx = (int)$redis->get("test_sum_timeout");
        if ($idx == 2) {
            $idx = 0;
            $redis->del("test_sum_timeout");
        }

        if ($idx == 0) {
            echo "sleep 2 will timeout\n";
            sleep(2);
            $redis->set("test_sum_timeout", 1);
        } else {
            echo "Result " . $a + $b;
            $redis->set("test_sum_timeout", 2);
            return $a + $b;
        }
    }

    public static function sumTimeoutForever($a, $b)
    {
        echo "Exit\n";
        exit();
//        sleep(2);
        return $a + $b;
    }
}
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
        $redis = (new RedisFactory())->createRedis();
        $idx = (int)$redis->get("test_sum_timeout");
        if ($idx == 2) {
            $idx = 0;
            $redis->del("test_sum_timeout");
        }

        printf("Index %s\n", $idx);

        if ($idx == 0) {
            sleep(2);
            $redis->set("test_sum_timeout", 1);
            echo "sleep 2 will timeout\n";
            exit();
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
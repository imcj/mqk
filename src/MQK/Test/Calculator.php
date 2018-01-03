<?php
namespace MQK\Test;

use MQK\Exception\SkipFailureRegistryException;
use MQK\Time;

class Calculator
{
    public static function sum($a, $b)
    {
//        echo $a + $b . "\n";
        return new \stdClass();
        return 1.1;
        return $a + $b;
    }

    public static function sumSleep($a, $b, $second)
    {
        usleep($second * 1000000);
        return self::sum($a, $b);
    }

    public static function sumSleepThenQuit($a, $b, $second)
    {
        self::sumSleep($a, $b, $second);
        exit(0);
    }

    public static function sumCrash($a, $b)
    {
        throw new SkipFailureRegistryException("Test");
    }

    public static function sumFailure($a, $b)
    {
        throw new \Exception("Got an exception");
    }

    public static function sumTimeout($session, $a, $b)
    {
        echo "Sum test\n";
        $redis = RedisFactory::shared()->createRedis();
        $idx = (int)$redis->exists($session);

        if (!$idx) {
            echo "sleep 2 will timeout\n";
            $s = Time::micro();
            sleep(2);
            $e = Time::micro() - $s;
            echo "Duration {$e}.\n";
            $redis->set($session, 1);
        } else {
            echo "Result " . $a + $b . "\n";
            $redis->del($session);
            return $a + $b;
        }
    }

    public static function sumTimeoutForever($a, $b)
    {
        $s = Time::micro();
        sleep(2);
        $e = Time::micro() - $s;
        exit(0);
//        return $a + $b;
    }
}
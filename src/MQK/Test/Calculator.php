<?php
namespace MQK\Test;

use MQK\Exception\TestTimeoutException;

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
        sleep(2);
    }
}
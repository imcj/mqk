<?php

include __DIR__ . "/../vendor/autoload.php";

class Calculator
{
    use \MQK\Queue\Traits\InvokeTrait;

    public $a = 1;
//    public function sum($a, $b)
//    {
//        return $a + $b;
//    }
}

//Calculator::sum(1, 1);

$c = new Calculator();
var_dump(Calculator::sum(1, 2));
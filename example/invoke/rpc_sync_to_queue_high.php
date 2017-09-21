<?php
include __DIR__ . "/../../vendor/autoload.php";

use \MQK\Queue\Invoke;
use MQK\Queue\Invokes;

$sum = 'MQK\Test\Calculator::sumSleep';

$invokes = new Invokes(
    new Invoke('a', $sum, 1, 1, 1),
    new Invoke('b', $sum, 2, 2, 1),
    new Invoke('c', $sum, 2, 3, 1),
    new Invoke('d', $sum, 2, 4, 1)
);
$invokes->setQueue('high');
$invoke = K::invokeAsync($invokes);
$start = \MQK\Time::micro();

echo "group id {$invoke->id()}\n";
$invoke->wait();
extract($invoke->returns());

$end = \MQK\Time::micro();
echo "A is {$a}\n";
echo "B is {$b}\n";
echo "C is {$c}\n";
echo "D is {$d}\n";

$diff = $end - $start;
echo "time is {$diff}";
echo "Completed\n";
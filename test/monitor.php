<?php

$now = new DateTime();
$redis = new Redis();
$redis->connect('127.0.0.1');

$previous = 0;
while (true) {
    echo $now->format("Y-m-d H:i:s");
    $len = (int)$redis->llen("queue");
    if (!$previous) {
        $previous = $len;
    }
    $range = $previous - $len;
    $previous = $len;
    echo ",{$range}\n";
    sleep(1);
}
<?php

include __DIR__ . "/../../vendor/autoload.php";

$message = \K::invokeLate('MQK\Test\Calculator::sumSleep', 1, 2, 10);
$message->setTtl(1);
$message->setQueue('default');
\K::invokeMessage($message);
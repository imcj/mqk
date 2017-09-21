<?php

include __DIR__ . "/../../vendor/autoload.php";

use MQK\Example\ExampleSubscriber;

$subscriber = new ExampleSubscriber();

K::addSubscriber($subscriber);
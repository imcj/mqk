<?php

include __DIR__ . "/../../vendor/autoload.php";

use MQK\Example\ExampleSubscriber;

$subscriber = new ExampleSubscriber();

K::addListener("example", function(\MQK\Example\ExampleEvent $event) {
    echo "{$event->hello}\n";
});
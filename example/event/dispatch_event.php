<?php
include __DIR__ . "/../../vendor/autoload.php";

use MQK\Example\ExampleEvent;
\K::dispatch("example", new ExampleEvent());
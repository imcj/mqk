<?php
require './vendor/autoload.php';


use MQK\Queue\Outbound\OutboundRouter;

$app = new \Slim\App;
$outboundRouter = new OutboundRouter($app);
$outboundRouter->boot();

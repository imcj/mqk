<?php
namespace MQK;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    public function getLogger($name)
    {
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler("php://stdout"));
        return $logger;
    }
}
<?php
namespace MQK;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    private $level = Logger::WARNING;

    /**
     * @var LoggerFactory
     */
    private static $shared;

    public function level()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLogger($name, $level=null)
    {
        $logger = new Logger($name);
        $handler = new StreamHandler("php://stdout");
        if ($level)
            $handler->setLevel($level);
        else
            $handler->setLevel($this->level);
        $logger->pushHandler($handler);
        return $logger;
    }

    public static function shared()
    {
        if (null == self::$shared) {
            self::$shared = new LoggerFactory();
        }

        return self::$shared;
    }
}
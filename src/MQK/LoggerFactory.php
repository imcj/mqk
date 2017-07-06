<?php
namespace MQK;


use Monolog\Formatter\LineFormatter;
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

    /**
     * Logger的工厂方法
     *
     * @param $name
     * @param integer $level Logger level
     * @return Logger
     */
    public function getLogger($name, $level=null)
    {
        $logger = new Logger($name);
        $handler = new StreamHandler("php://stdout");
        $pid = posix_getpid();
        $output = "[%datetime%] {$pid} %channel%.%level_name%: %message% %context% %extra%\n";

        $formatter = new LineFormatter($output);
        $handler->setFormatter($formatter);

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
<?php
namespace MQK;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    private $defaultLevel = Logger::WARNING;

    /**
     * @var LoggerFactory
     */
    private static $shared;

    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    public function defaultLevel()
    {
        return $this->defaultLevel;
    }

    public function setDefaultLevel($level)
    {
        $this->defaultLevel = $level;
    }

    public function pushHandler($handler)
    {
        $this->handlers[] = $handler;
    }

    public function setHandlers($handlers)
    {
        $this->handlers = $handlers;
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
        if (method_exists("posix_getpid"))
            $pid = posix_getpid();
        else
            $pid = getmypid();
        $output = "[%datetime%] {$pid} %channel%.%level_name%: %message% %context% %extra%\n";

        $formatter = new LineFormatter($output);
        $handler->setFormatter($formatter);

        if ($level)
            $handler->setLevel($level);
        else
            $handler->setLevel($this->defaultLevel);
        $logger->pushHandler($handler);

        foreach ($this->handlers as $handler)
            $logger->pushHandler($handler);

        return $logger;
    }

    public function cliLogger()
    {
        $config = Config::defaultConfig();
        if ($config->quite()) {
            $level = Logger::NOTICE;
        } else {
            $level = Logger::INFO;
        }

        return $this->getLogger("", $level);
    }

    public static function shared()
    {
        if (null == self::$shared) {
            self::$shared = new LoggerFactory();
        }

        return self::$shared;
    }
}
<?php
namespace MQK;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    use SingletonTrait;

    private $defaultLevel = Logger::WARNING;

    /**
     * @var LoggerFactory
     */
    private static $shared;

    /**
     * @var HandlerInterface[]
     */
    private $handlers = [];

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

        foreach ($this->handlers as $handler) {
            $logger->pushHandler($handler);
        }

        if (empty($this->handlers)) {
            $handler = new StreamHandler("php://stdout", $this->defaultLevel);
            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
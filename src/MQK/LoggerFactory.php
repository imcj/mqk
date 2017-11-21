<?php
namespace MQK;

use Monolog\Handler\AbstractHandler;
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
     * @var AbstractHandler[]
     */
    private $handlers = [];

    /**
     * @var Logger[]
     */
    private $loggers = [];

    public function __construct()
    {
        $this->handlers = [new StreamHandler("php://stdout")];
    }

    public function defaultLevel()
    {
        return $this->defaultLevel;
    }

    public function setDefaultLevel($level)
    {
        $this->defaultLevel = $level;

        $this->setAllHandlerLevel($level);
    }

    private function setAllHandlerLevel($level)
    {
        foreach ($this->handlers as $handler) {
            $handler->setLevel($level);
        }
    }

    public function getHandlers()
    {
        return $this->handlers;
    }

    public function pushHandler($handler)
    {
        $this->handlers[] = $handler;
        $handler->setLevel($this->defaultLevel);
    }

    public function setHandlers($handlers)
    {
        $this->handlers = $handlers;
        $this->setAllHandlerLevel($this->defaultLevel);

        foreach ($this->loggers as $logger) {
            $logger->setHandlers([]);

            foreach ($this->handlers as $handler) {
                $logger->pushHandler($handler);
            }
        }
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
        if (isset($this->loggers[$name])) {
            return $this->loggers[$name];
        }
        $logger = new Logger($name);
        $this->loggers[$name] = $logger;

        foreach ($this->handlers as $handler) {
            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
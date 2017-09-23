<?php
namespace MQK;

use Monolog\Logger;
use MQK\Logging\Handlers\StreamHandler;

class YamlConfigProcessor
{
    /**
     * @var mixed
     */
    private $yaml;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct($yaml, Config $config)
    {
        $this->yaml = $yaml;
        $this->config = $config;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    public function process()
    {
        $yaml = $this->yaml;
        $levels = Logger::getLevels();
        if (isset($yaml['logging']) && isset($yaml['logging']['level'])) {
            $level = $yaml['logging']['level'];

            if (!empty($level)) {
                $this->validateLoggingLevel($level);
                if (array_key_exists($level, $levels)) {
                    LoggerFactory::shared()->setDefaultLevel($levels[$level]);
                }
            }
        }
        if (isset($yaml['logging'])) {
            $handlers = [];

            /**
             * @var AbstractProcessingHandler $handler
             */
            $handler = null;
            if (isset($yaml['logging']['handlers'])) {
                foreach ($yaml['logging']['handlers'] as $handlerListItem) {
                    $namespace = "\\MQK\\Logging\\Handlers\\";

                    if (is_array($handlerListItem)) {
                        $key = current(array_keys($handlerListItem));
                        $value = current(array_values($handlerListItem));

                        if (!isset($handlerListItem['class'])) {
                            throw new \Exception("Does not exists handler class name");
                            continue;
                        }
                        $className = $namespace . $handlerListItem['class'];

                        if (class_exists($className)) {
                            // 12345 54321 5211314 from kiki
                            //
                            // This code from my wife, not me.

                            if (isset($handlerListItem['arguments'])) {
                                $arguments = $handlerListItem['arguments'];
                                if (is_string($arguments))
                                    $arguments = [$arguments];
                            } else {
                                $arguments = [];
                            }

                            $handlerClass = new \ReflectionClass($className);
                            if (1 <= count($arguments))
                                $handler = $handlerClass->newInstanceArgs($arguments);
                            else
                                $handler = $handlerClass->newInstance();


                            if (isset($handlerListItem['level'])) {
                                $level = $handlerListItem['level'];
                                $this->validateLoggingLevel($level);
                                $handler->setLevel($levels[$level]);
                            } else {
                                $handler->setLevel(LoggerFactory::shared()->defaultLevel());
                            }

                        } else {
                            throw new \Exception("Does not exists handler class");
                        }
                    } else {

                        $className = $namespace . $handlerListItem;

                        if (class_exists($className))
                            $handler = new $className;
                        else
                            throw new \Exception("Does not exists handler class");
                    }

                    if (null !== $handler) {
                        $handlers[] = $handler;
                    }
                }
            } else {
                $handler = new StreamHandler();
                $handler->setLevel(LoggerFactory::shared()->defaultLevel());
                $handlers[] = $handler;
            }

            LoggerFactory::shared()->setHandlers($handlers);
        }

        if (isset($yaml['concurrency'])) {
            $concurrency = $yaml['concurrency'];
            if (is_integer($concurrency)) {
                $this->config->setConcurrency($concurrency);
            } else {
                throw new \Exception("Concurrency must be integer");
            }
        }

        if (isset($yaml['queue'])) {
            if (isset($yaml['queue']['default'])) {
                $defaultQueue = $yaml['queue']['default'];
                if (!empty($defaultQueue)) {
                    $this->config->setDefaultQueue($defaultQueue);
                }
            }

            if (isset($yaml['queue']['prefix'])) {
                $queuePrefix = $yaml['queue']['prefix'];
                if (!empty($queuePrefix)) {
                    $this->config->setQueuePrefix($queuePrefix);
                }
            }
        }

        if (isset($yaml['queues'])) {
            $queues = $yaml['queues'];

            $queuesFilter = [];
            if (!empty($queues)) {
                foreach ($queues as $queue) {
                    if (is_string($queue)) {
                        $queuesFilter[] = $queue;
                    } else {
                        throw new \Exception("Queue name is not a string", 5);
                    }
                }
            }

            if (!empty($queuesFilter))
                $this->config->setQueues($queuesFilter);
        }

        if (isset($yaml['error_handlers'])) {
            $errorHandlers = $yaml['error_handlers'];
            foreach ($errorHandlers as $errorHandler) {
                try {
                    $handler = new $errorHandler();
                    $this->config->addErrorHandler($handler);
                } catch (\Exception $e) {

                }
            }
        }

        if (isset($yaml['bootstrap'])) {
            $bootstrap = $yaml['bootstrap'];

            if (!empty($bootstrap) && file_exists($bootstrap)) {
                $this->config->setBootstrap($bootstrap);
            }
        }

        if (isset($yaml['retry'])) {
            $retry = $yaml['retry'];
            if (!empty($retry) && is_integer($retry)) {
                $this->logger->debug("Set default max retry times {$retry}");
                $this->config->setRetry($retry);
            }
        }
    }

    /**
     * @param int $level
     * @throws \Exception
     */
    function validateLoggingLevel($level)
    {
        $levels = Logger::getLevels();
        if (!isset($levels[$level])) {
            throw new \Exception("{$level} not in mono levels");
        }
    }
}
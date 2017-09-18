<?php
namespace MQK;


use Monolog\Logger;

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

    public function __construct($yaml, Config $config)
    {
        $this->yaml = $yaml;
        $this->config = $config;
    }

    public function process()
    {
        $yaml = $this->yaml;
        if (isset($yaml['logging']) && isset($yaml['logging']['level'])) {
            $level = $yaml['logging']['level'];

            if (!empty($level)) {
                $levelMap = Logger::getLevels();
                if (array_key_exists($level, $levelMap)) {
                    LoggerFactory::shared()->setDefaultLevel($levelMap[$level]);
                }
            }
        }

        $handlers = [];

        /**
         * @var AbstractProcessingHandler $handler
         */
        $handler = null;
        foreach ($yaml['logging']['handlers'] as $handlerListItem) {
            $namespace = "\\MQK\\Logging\\Handlers\\";

            if (is_array($handlerListItem)) {
                $key = current(array_keys($handlerListItem));
                $value = current(array_values($handlerListItem));

                $className = $namespace . $key;

                if (class_exists($className)) {
                    // 12345 54321 5211314 from kiki
                    //
                    // This code from my wife, not me.

                    $arguments = $value;

                    $handlerClass = new \ReflectionClass($className);
                    $handler = $handlerClass->newInstance();


                    if (null == $handlerListItem['level'])
                        $handler->setLevel($level);

                    $handlers[] = $handler;
                } else {
                    $handlerClass = new \ReflectionClass($className);
                    $handler = $handlerClass->newInstance();
                }
            } else {

                $className = $namespace . $handlerListItem;

                if (class_exists($className))
                    $handler = new $className;
            }

            if (null !== $handler) {
                $handlers[] = $handler;
            }
        }

        LoggerFactory::shared()->setHandlers($handlers);
    }
}
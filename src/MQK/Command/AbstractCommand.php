<?php
namespace MQK\Command;

use AD7six\Dsn\Dsn;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use MQK\Config;
use MQK\IniConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MQK\LoggerFactory;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    protected $logger;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::defaultConfig();
        $verbose = $input->getOption("verbose");
        if ($verbose) {
            LoggerFactory::shared()->setDefaultLevel(Logger::DEBUG);
        } else {
            LoggerFactory::shared()->setDefaultLevel(Logger::INFO);
        }
        $dsn = $input->getOption("redis-dsn");
        if (!empty($dsn)) {
            $config->setDsn($dsn);
        }
    }

    protected function loadIniConfig($yamlPath)
    {
        $yaml = Yaml::parse(file_get_contents($yamlPath));
        $level = $yaml['logging']['level'];

        if (!empty($level)) {
            $levelMap = Logger::getLevels();
            if (array_key_exists($level, $levelMap)) {
                LoggerFactory::shared()->setDefaultLevel($levelMap[$level]);
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

            if (nul !== $handler) {
                $handlers[] = $handler;
            }
        }

        LoggerFactory::shared()->setHandlers($handlers);

        $conf = Config::defaultConfig();
        if (!empty($config['init_script'])) {
            $conf->setInitScript($config['init_script']);
        }

        if (!empty($config['workers'])) {
            $conf->setWorkers((int)$config['workers']);
        }

        if (!empty($config['dsn'])) {
            $conf->setDsn($config['dsn']);
        }
    }

}
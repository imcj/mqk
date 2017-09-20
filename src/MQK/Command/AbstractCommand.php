<?php
namespace MQK\Command;

use AD7six\Dsn\Dsn;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use MQK\Config;
use MQK\IniConfig;
use MQK\YamlConfigProcessor;
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
        $dsn = $input->getOption("redis");
        if (!empty($dsn)) {
            $config->setRedis($dsn);
        } else {
            $config->setRedis('redis://127.0.0.1');
        }
    }

    protected function loadIniConfig($yamlPath)
    {
        $conf = Config::defaultConfig();
        $parseProcessor = new YamlConfigProcessor(
            Yaml::parse(file_get_contents($yamlPath)),
            $conf
        );
        $parseProcessor->process();
    }

}
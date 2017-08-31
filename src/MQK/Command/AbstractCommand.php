<?php
namespace MQK\Command;

use AD7six\Dsn\Dsn;
use Monolog\Logger;
use MQK\Config;
use MQK\IniConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MQK\LoggerFactory;

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
        }
        $dsn = $input->getOption("redis-dsn");
        if (!empty($dsn)) {
            $config->setDsn($dsn);
        }
    }

    protected function loadIniConfig($iniFile)
    {
        $config = include $iniFile;

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
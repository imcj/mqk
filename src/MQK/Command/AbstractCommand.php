<?php
namespace MQK\Command;

use AD7six\Dsn\Dsn;
use Monolog\Logger;
use MQK\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MQK\LoggerFactory;

abstract class AbstractCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption("verbose");
        if ($verbose) {
            LoggerFactory::shared()->setDefaultLevel(Logger::DEBUG);
        }
        $dsn = $input->getOption("redis-dsn");
        if (!empty($dsn)) {
            $this->setupRedisConfig($dsn);
        }
    }

    protected function setupRedisConfig($dsn)
    {
        if (!empty($dsn)) {
            $dsn = Dsn::parse($dsn);
            $host = $dsn->host;
            $port = $dsn->port;
            $config = Config::defaultConfig();
            $config->setHost($host);
            $config->setPort($port);
            $config->setPassword($dsn->pass);
        }
    }
}
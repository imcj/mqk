<?php
namespace MQK\Command;

use Monolog\Logger;
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
            LoggerFactory::shared()->setLevel(Logger::DEBUG);
        }
    }
}
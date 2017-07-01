<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\LoggerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RunCommand extends Command
{
    protected function configure()
    {
        $this->setName("run")
            ->addOption("workers", "w", InputOption::VALUE_OPTIONAL, "", 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workers = (int)$input->getOption("workers");

        $config = \MQK\Config::defaultConfig();
        if (0 == $workers)
            $workers = 1;
        $config->setWorkers($workers);
        $runner = new \MQK\Runner();
        $runner->run();
    }
}
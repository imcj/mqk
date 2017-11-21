<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\Config;
use MQK\LoggerFactory;
use MQK\YamlConfigProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends Command
{
    protected function configure()
    {
        $this->setName("stop")
            ->addOption("config", '', InputOption::VALUE_OPTIONAL, "", "")
            ->addOption('pid', 'p', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::defaultConfig();
        $yaml = YamlConfigProcessor::loadFromFile($input->getOption('config'), $config);

        $yaml->process();

        $processIdFile = $input->getOption('pid');

        if (!file_exists($processIdFile)) {
            echo("pid file not exists\n");
            return;
        }

        $processId = file_get_contents($processIdFile);
        posix_kill($processId, SIGTERM);
    }
}
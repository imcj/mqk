<?php
namespace MQK\Command;

use MQK\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName("run")
            ->addOption("workers", "w", InputOption::VALUE_OPTIONAL, "", 1)
            ->addOption("redis-dsn", "s", InputOption::VALUE_OPTIONAL)
            ->addOption("burst", 'b', InputOption::VALUE_NONE)
            ->addOption("quite", '', InputOption::VALUE_NONE)
            ->addOption("cluster", 'c', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $workers = (int)$input->getOption("workers");
        $burst = $input->getOption("burst");

        $config = Config::defaultConfig();
        $config->setBurst($burst);
        if (0 == $workers)
            $workers = 1;

        $config->setWorkers($workers);

        $quite = $input->getOption("quite");
        if ($quite)
            $config->beQuite();

        $cluster = $input->getOption("cluster");
        if (!empty($cluster))
            $config->setCluster($cluster);

        $runner = new \MQK\Runner();
        $runner->run();
    }
}
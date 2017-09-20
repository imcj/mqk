<?php
namespace MQK\Command;

use MQK\Config;
use MQK\Runner;
use MQK\Worker\EmptyWorkerFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractCommand
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName("run")
            ->addOption("concurrency", 'c', InputOption::VALUE_OPTIONAL, "", 1)
            ->addOption("redis", "s", InputOption::VALUE_OPTIONAL)
            ->addOption("burst", 'b', InputOption::VALUE_NONE)
            ->addOption("quite", '', InputOption::VALUE_NONE)
            ->addOption("fast", 'f', InputOption::VALUE_NONE)
            ->addOption("test", 't', InputOption::VALUE_OPTIONAL)
            ->addOption("empty-worker", '', InputOption::VALUE_NONE)
            ->addOption("config", '', InputOption::VALUE_OPTIONAL, "", "")
            ->addOption("sentry", '', InputOption::VALUE_OPTIONAL, '', '')
            ->addOption('queue', '', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $burst = $input->getOption("burst");

        $config = Config::defaultConfig();
        $config->setBurst($burst);

        $fast = $input->getOption("fast");
        if ($fast)
            $config->enableFast();

        $max = (int)$input->getOption("test");
        if ($max > 0)
            $config->setTestJobMax($max);

        parent::execute($input, $output);

        $runner = new Runner($config->queues());

        if ((boolean)$input->getOption("empty-worker")) {
            $workerFactory = new EmptyWorkerFactory();
            $runner->setWorkerFactory($workerFactory);
        }

        $runner->run();
    }
}
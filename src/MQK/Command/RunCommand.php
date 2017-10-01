<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\Config;
use MQK\LoggerFactory;
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
            ->addOption('bootstrap', '', InputOption::VALUE_OPTIONAL)
            ->addOption('queue', '', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED)
            ->addOption('retry', 'r', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $burst = $input->getOption("burst");

        LoggerFactory::shared()->setDefaultLevel(Logger::NOTICE);

        $config = Config::defaultConfig();
        $config->setBurst($burst);

        $fast = $input->getOption("fast");
        if ($fast)
            $config->enableFast();

        $max = (int)$input->getOption("test");
        if ($max > 0)
            $config->setTestJobMax($max);

        $retry = $input->getOption('retry');
        if (!empty($retry) && is_integer($retry)) {
            $retry = (int)$retry;
            $config->setRetry($retry);
        }

        $bootstrap = $input->getOption('bootstrap');
        if (!empty($bootstrap))
            $config->setBootstrap($bootstrap);

        parent::execute($input, $output);
        $this->start($config);
    }

    public function start(Config $config)
    {
        // Objects

        $runner = new Runner($config->queues(), $config);
        $runner->run();
    }
}
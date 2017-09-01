<?php
namespace MQK\Command;

use MQK\Config;
use MQK\Hook\HookNotification;
use MQK\MasterProcess\MasterProcessFactory;
use MQK\MasterProcess\MQKMasterProcessFactory;
use MQK\Runner;
use MQK\Worker\EmptyWorkerFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractCommand
{
    /**
     * @var MasterProcessFactory
     */
    protected $masterProcessFactory;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->masterProcessFactory = new MQKMasterProcessFactory();
    }

    protected function configure()
    {
        $this->setName("run")
            ->addOption("workers", "w", InputOption::VALUE_OPTIONAL, "", 1)
            ->addOption("redis-dsn", "s", InputOption::VALUE_OPTIONAL)
            ->addOption("burst", 'b', InputOption::VALUE_NONE)
            ->addOption("quite", '', InputOption::VALUE_NONE)
            ->addOption("cluster", 'c', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED)
            ->addOption("fast", 'f', InputOption::VALUE_NONE)
            ->addOption("test", 't', InputOption::VALUE_OPTIONAL)
            ->addOption("empty-worker", '', InputOption::VALUE_NONE)
            ->addOption("config", '', InputOption::VALUE_OPTIONAL, "", "")
            ->addOption("sentry", '', InputOption::VALUE_OPTIONAL, '', '');
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

        $fast = $input->getOption("fast");
        if ($fast)
            $config->enableFast();

        $max = (int)$input->getOption("test");
        if ($max > 0)
            $config->setTestJobMax($max);

        $configFilePath = $input->getOption("config");
        if (!empty($configFilePath)) {
            if (!file_exists($configFilePath)) {
                $this->logger->warning("You specify config file, but not found");
            } else {
                $this->loadIniConfig($configFilePath);
            }
        }

        $sentry = $input->getOption("sentry");
        if (!empty($sentry)) {
            $config->setSentry($sentry);

            $client = new \Raven_Client($sentry);
            $error_handler = new \Raven_ErrorHandler($client);
            $error_handler->registerExceptionHandler();
            $error_handler->registerErrorHandler();
            $error_handler->registerShutdownFunction();
        }

        $runner = $this->masterProcessFactory->create();

        if ((boolean)$input->getOption("empty-worker")) {
            $workerFactory = new EmptyWorkerFactory();
            $runner->setWorkerFactory($workerFactory);
        }

        $hookNotification = new HookNotification();
        $hookNotification->boot();

        $runner->run();
    }

    public function masterProcessFactory()
    {
        return $this->masterProcessFactory;
    }

    public function setMasterProcessFactory($masterProcessFactory)
    {
        $this->masterProcessFactory = $masterProcessFactory;
    }
}
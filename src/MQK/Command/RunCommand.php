<?php
namespace MQK\Command;

use MQK\Config;
use MQK\MasterProcess\MasterProcessFactory;
use MQK\MasterProcess\MQKMasterProcessFactory;
use MQK\Runner;
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
            ->addOption("fast", 'f', InputOption::VALUE_NONE);
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

        $runner = $this->masterProcessFactory->create();
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
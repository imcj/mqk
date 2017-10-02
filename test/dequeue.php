<?php
include __DIR__ . "/../vendor/autoload.php";

class DequeueWorkerFactory implements \MQK\Worker\WorkerFactory
{
    public function create()
    {
        return new \MQK\Worker\DequeueWorker();
    }
}

class MasterProcess extends \MQK\PosixRunner
{
    protected $findExpiredJob = false;

    public function __construct()
    {
        parent::__construct();
    }
}

class LoopCommand extends \Symfony\Component\Console\Command\Command
{
    public function configure()
    {
        $this->setName("loop")
            ->addArgument("numbers", \Symfony\Component\Console\Input\InputArgument::REQUIRED, "");
    }

    public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $size = (int)$input->getArgument("numbers");
        $s = \MQK\Time::micro();
        for ($i = 0; $i < $size; $i++) {

        }
        printf("Left %f seconds.\n", \MQK\Time::micro() - $s);
    }
}

class DequeueMasterProcessFactory implements \MQK\MasterProcess\MasterProcessFactory
{
    public function create()
    {
        $workerFactory = new DequeueWorkerFactory();

        $master = new MasterProcess();
        $master->setWorkerFactory($workerFactory);

        return $master;
    }
}

$masterProcessFactory = new DequeueMasterProcessFactory();

$command = new \MQK\Command\RunCommand();
$command->setMasterProcessFactory($masterProcessFactory);

$app = new \Symfony\Component\Console\Application();
$app->add($command);
$app->add(new LoopCommand());
$app->run();
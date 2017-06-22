<?php
require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use MQK\K;



class ProduceWorker extends \MQK\Worker\AbstractWorker
{
    /**
     * @var int
     */
    private $numbers;

    public function __construct($numbers)
    {
        parent::__construct();

        $this->numbers = $numbers;
    }

    public function run()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');

        echo "Start process {$this->id}.\n";
        for ($i = 0; $i < $this->numbers; $i++) {
            try {
                $job = K::invoke('MQK\\Test\\Calculator::sum', 1, 2);
            } catch (RedisException $e) {
                var_dump($e);
            }
        }
    }
}

class TestCommand extends Command
{
    protected function configure()
    {
        $this->setName("test");
        $this->addArgument("numbers", InputArgument::REQUIRED);
        $this->addOption("workers", "w", \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'workers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workers = (int)$input->getOption('workers');
        if (!$workers)
            $workers = 1;
        $numbers = (int)$input->getArgument("numbers");

        $processes = [];
        $a = $numbers / $workers;
        for ($i = 0; $i < $workers; $i++) {
            $worker = new ProduceWorker($numbers / $workers);
            $worker->start();
            $processes[] = $worker;
        }

        foreach ($processes as $worker) {
            $worker->join();
        }
    }
}

$application = new Application();
$application->add(new TestCommand());
$application->run();


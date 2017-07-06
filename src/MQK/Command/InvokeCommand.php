<?php
namespace MQK\Command;

use MQK\Job;
use MQK\Queue\QueueFactory;
use MQK\RedisFactory;
use MQK\Worker\AbstractWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class InvokeCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName("invoke")
            ->addArgument("funcAndArguments", InputArgument::IS_ARRAY)
            ->addOption("ttl", "t", InputOption::VALUE_OPTIONAL)
            ->addOption("workers", "w", InputOption::VALUE_OPTIONAL)
            ->addOption("invokes", "i", InputOption::VALUE_OPTIONAL)
            ->addOption("redis-dsn", "s", InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $funcAndArguments = $input->getArgument("funcAndArguments");
        $functionName = array_shift($funcAndArguments);

        $ttl = $input->getOption("ttl");
        $workers = $input->getOption("workers");
        if (!$workers) {
            $workers = 1;
        }

        $invokes = $input->getOption("invokes");
        if (!$invokes) {
            $invokes = 1;
        }

        $processes = [];
        $block = $invokes / $workers;
        for ($i = 0; $i < $workers; $i++) {
            $worker = new ProduceWorker($functionName, $funcAndArguments, $block, $ttl);
            $worker->start();
            $processes[] = $worker;
        }

        foreach ($processes as $worker) {
            $worker->join();
        }


    }
}

class ProduceWorker extends AbstractWorker
{
    /**
     * @var int
     */
    private $numbers;

    /**
     * @var string
     */
    private $funcName;

    /**
     * @var string[]
     */
    private $arguments;

    /**
     * @var int
     */
    private $ttl;

    public function __construct($funcName, $arguments, $numbers, $ttl = null)
    {
        parent::__construct();

        $this->funcName = $funcName;
        $this->arguments = $arguments;
        $this->numbers = $numbers;
        $this->ttl = $ttl;
    }

    public function run()
    {
        echo "Start process {$this->id}.\n";

        $queueFactory = new QueueFactory();
        $redis = RedisFactory::shared()->createRedis();
        $queue = $queueFactory->createQueue("default");

        for ($i = 0; $i < $this->numbers; $i++) {

            $job = new Job(null, $this->funcName, $this->arguments);
            $job->setConnection($redis);
            if (null != $this->ttl)
                $job->setTtl($this->ttl);


            $queue->enqueue($job);
        }
    }
}
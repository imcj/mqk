<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\Config;
use MQK\Job;
use MQK\LoggerFactory;
use MQK\Queue\MessageAbstractFactory;
use MQK\Queue\QueueFactory;
use MQK\RedisFactory;
use MQK\RedisProxy;
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
            ->addOption("redis", "s", InputOption::VALUE_OPTIONAL)
            ->addOption("cluster", 'c', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $funcAndArguments = $input->getArgument("funcAndArguments");
        $functionName = array_shift($funcAndArguments);

        $config = Config::defaultConfig();

        $cluster = $input->getOption("cluster");
        if (!empty($cluster))
            $config->setCluster($cluster);

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
            $worker = new ProduceWorker($config->redis(), $functionName, $funcAndArguments, $block, $ttl);
            $worker->start();
            $processes[] = $worker;
        }

        foreach ($processes as $worker) {
            $worker->join();
        }
    }
}

class ProduceWorker extends \MQK\Process\AbstractWorker
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

    private $redisDsn;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct($redisDsn, $funcName, $arguments, $numbers, $ttl = null)
    {
        $this->redisDsn = $redisDsn;
        $this->funcName = $funcName;
        $this->arguments = $arguments;
        $this->numbers = $numbers;
        $this->ttl = $ttl;
    }

    public function run()
    {
        echo "Start process {$this->id}.\n";

        $redis = new RedisProxy($this->redisDsn);

        try {
            $redis->connect();
        } catch (\RedisException $e) {
            if ("Failed to AUTH connection" == $e->getMessage()) {
                $this->logger->error($e->getMessage());
                exit(1);
            }
        }
        $messageAbstractFactory = new MessageAbstractFactory();
        $queueFactory = new QueueFactory($redis, $messageAbstractFactory);
        $queue = $queueFactory->createQueue("default");
        $this->logger = LoggerFactory::shared()->cliLogger();

        for ($i = 0; $i < $this->numbers; $i++) {
            $payload = new \stdClass();
            $payload->func = $this->funcName;
            $payload->arguments = $this->arguments;

            $message = new \MQK\Queue\MessageInvokable(uniqid(), "invokable", "default", $this->ttl ? $this->ttl : 600, $payload);
            $queue->enqueue($message);
        }
    }
}
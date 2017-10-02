<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\Config;
use MQK\LoggerFactory;
use MQK\Queue\RedisQueue;
use MQK\RedisProxy;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InvokeCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName("invoke")
            ->addArgument("funcAndArguments", InputArgument::IS_ARRAY)
            ->addOption("ttl", "t", InputOption::VALUE_OPTIONAL)
            ->addOption("quite", '', InputOption::VALUE_NONE)
            ->addOption("config", '', InputOption::VALUE_OPTIONAL, "", "")
            ->addOption("sentry", '', InputOption::VALUE_OPTIONAL)
            ->addOption('concurrency', 'c', InputOption::VALUE_OPTIONAL)
            ->addOption("invokes", "i", InputOption::VALUE_OPTIONAL)
            ->addOption("redis", "s", InputOption::VALUE_OPTIONAL)
            ->addOption('queue', '', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED);
//            ->addOption("cluster", 'c', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $funcAndArguments = $input->getArgument("funcAndArguments");
        $functionName = array_shift($funcAndArguments);

        $config = Config::defaultConfig();

        $ttl = $input->getOption("ttl");

        $concurrency = $input->getOption("concurrency");
        if (!$concurrency) {
            $concurrency = 1;
        }

        $invokes = $input->getOption("invokes");
        if (!$invokes) {
            $invokes = 1;
        }

        $processes = [];
        $block = $invokes / $concurrency;
        $queues = $input->getOption("queue");

        if (empty($queues)) {
            $queues = ['default'];
        }
        for ($i = 0; $i < $concurrency; $i++) {
            $worker = new ProduceWorker($config->redis(), $functionName, $funcAndArguments, $block, $queues, $ttl);
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

    private $queues;

    /**
     * @var Config
     */
    private $config;

    public function __construct($redisDsn, $funcName, $arguments, $numbers, $queues, $ttl = null)
    {
        $this->redisDsn = $redisDsn;
        $this->funcName = $funcName;
        $this->arguments = $arguments;
        $this->numbers = $numbers;
        $this->queues = $queues;
        $this->ttl = $ttl;
        $this->config = Config::defaultConfig();
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

        $queue = new RedisQueue($redis, $this->config->queuePrefix());

        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);

        for ($i = 0; $i < $this->numbers; $i++) {
            $payload = new \stdClass();
            $payload->func = $this->funcName;
            $payload->arguments = $this->arguments;

            foreach ($this->queues as $queueName) {
                $message = new \MQK\Queue\MessageInvokable(uniqid(), "invokable", $queueName, $this->ttl ? $this->ttl : 600, $payload);
                $message->setMaxRetry(0);
                $queue->enqueue($queueName, $message);
            }
        }
    }

    protected  function quit()
    {
    }
}
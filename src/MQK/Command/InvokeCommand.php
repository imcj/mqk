<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\Command\InvokeCommand\PosixProduceWorker;
use MQK\Command\InvokeCommand\Produce;
use MQK\Command\InvokeCommand\WindowsProduceWorker;
use MQK\Config;
use MQK\LoggerFactory;
use MQK\OSDetect;
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

        $queues = $input->getOption("queue");

        if (empty($queues)) {
            $queues = ['default'];
        }

        $connection = new RedisProxy($config->redis());
        $connection->connect();
        $queue = new RedisQueue($connection, $config->queuePrefix());


        $osDetect = new OSDetect();
        if ($osDetect->isPosix()) {
            $produceWorkerClass = PosixProduceWorker::class;
            $block = $invokes / $concurrency;
        } else {
            $produceWorkerClass = WindowsProduceWorker::class;
            $block = $invokes;
            $concurrency = 1;
            $this->logger->warning("Windows 系统无法使用多进程并发，将在单一进程执行");
        }

        $produce = new Produce(
            $functionName,
            $funcAndArguments,
            $block,
            $queue,
            $queues,
            $ttl
        );

        for ($i = 0; $i < $concurrency; $i++) {
            $produceWorker = new $produceWorkerClass($connection, $produce);
            $produceWorker->start();
            $processes[] = $produceWorker;
        }

        if ($osDetect->isPosix()) {
            foreach ($processes as $worker) {
                $worker->join();
            }
        }
    }
}
<?php
namespace MQK\Command;

use MQK\Config;
use MQK\LoggerFactory;
use MQK\RedisProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MonitorCommand extends Command
{

    public function configure()
    {
        $this->setName("monitor")
            ->addOption("redis-dsn", "s", InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dsn = $input->getOption("redis-dsn");
        if (empty($dsn))
            $dsn = 'redis://127.0.0.1';
        $previous = 0;

        $config = Config::defaultConfig();

        $redis = new RedisProxy($dsn);
        $redis->connect();

        while (true) {
            $now = new \DateTime();
            echo $now->format("Y-m-d H:i:s");
            $len = (int)$redis->llen("queue_default");
            if (!$previous) {
                $previous = $len;
            }
            $range = $previous - $len;
            $previous = $len;
            echo ",{$len}\n";
            sleep(1);
        }
    }
}
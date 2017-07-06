<?php
namespace MQK\Command;


use MQK\RedisFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \AD7six\Dsn\Dsn;

class MonitorCommand extends AbstractCommand
{
    public function configure()
    {
        parent::configure();
        $this->setName("monitor")
            ->addOption("redis-dsn", "s", InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $dsn = $input->getOption("redis-dsn");
        $previous = 0;
        $redis = RedisFactory::shared()->createRedis($dsn);
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
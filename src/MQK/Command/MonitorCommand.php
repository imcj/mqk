<?php
namespace MQK\Command;


use MQK\RedisFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MonitorCommand extends AbstractCommand
{
    public function configure()
    {
        parent::configure();
        $this->setName("monitor");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $previous = 0;
        $redis = (new RedisFactory())->createRedis();
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
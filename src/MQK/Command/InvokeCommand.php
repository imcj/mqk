<?php
namespace MQK\Command;

use MQK\Job;
use MQK\Queue\QueueFactory;
use MQK\RedisFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class InvokeCommand extends Command
{
    protected function configure()
    {
        $this->setName("invoke")
            ->addArgument("funcAndArguments", InputArgument::IS_ARRAY)
            ->addOption("ttl", "t", InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $funcAndArguments = $input->getArgument("funcAndArguments");
        $functionName = array_shift($funcAndArguments);

        $ttl = $input->getOption("ttl");

        $queueFactory = new QueueFactory();
        $redis = (new RedisFactory())->createRedis();
        $job = new Job(null, $functionName, $funcAndArguments);
        $job->setConnection($redis);
        if (null != $ttl)
            $job->setTtl($ttl);

        $queue = $queueFactory->createQueue("default");
        $queue->enqueue($job);
    }
}
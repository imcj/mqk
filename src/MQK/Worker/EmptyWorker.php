<?php
namespace MQK\Worker;


use MQK\Config;
use MQK\Queue\TestQueueCollection;
use MQK\Time;

function dequeue()
{
    return null;
}

class EmptyWorker extends AbstractWorker implements Worker
{

    function run()
    {
        $max = Config::defaultConfig()->testJobMax();
        $queues = new TestQueueCollection($max);
        $i = 0;
        $start = Time::micro();
        while (true) {
            try {
                $job = $queues->dequeue();
//                $job = $this->dequeue();
//                $job = $this->dequeue();
            } catch (\Exception $e) {
                break;
            }

            $i += 1;

            if ($i >= $max) {
                break;
            }
        }
        $duration = Time::micro() - $start;
        printf("duration {$duration} second\n");
    }

    function dequeue()
    {
        return null;
    }
}
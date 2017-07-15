<?php
namespace MQK\Worker;

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


class DequeueWorker extends AbstractWorker implements Worker
{
    public function __construct()
    {
        $this->connection = new \Redis();
        $this->connection->connect('127.0.0.1');
    }

    function run()
    {
        echo "Run";
        $i = 0;
        $pre = microtime_float();

        while (true) {

//            $pop = null;
//            foreach (["queue_default"] as $queue) {
//                $pop = $this->connection->lPop($queue);
//                if ($pop)
//                    break;
//            }
            $pop = $this->connection->lPop("queue_default");
            if (!$pop)
                break;
            $json = json_decode($pop, true);
            $obj = new \stdClass();

            foreach ($json as $k => $v)
                $obj->$k = $v;
            $i += 1;

            $now = time();
            if ($now - $pre > 0) {
                $pre = $now;
                echo "$i\n";
            }
        }

        $now = microtime_float();

        echo $now - $pre . "\n";
    }
}
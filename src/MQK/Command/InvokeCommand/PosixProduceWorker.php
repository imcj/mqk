<?php
namespace MQK\Command\InvokeCommand;

use MQK\RedisProxy;

class PosixProduceWorker extends \MQK\Process\AbstractWorker
{
    /**
     * @var RedisProxy
     */
    protected $connection;

    /**
     * @var Produce
     */
    protected $produce;

    public function __construct($connection, $produce)
    {
        $this->connection = $connection;
        $this->produce = $produce;
    }

    public function run()
    {
        echo "Start process {$this->id}.\n";
        $this->connection->connect(true);

        $this->produce->run();
    }

    protected  function quit()
    {
    }
}
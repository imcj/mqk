<?php
namespace MQK\Command\InvokeCommand;

use MQK\Process\Worker;

class WindowsProduceWorker implements Worker
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

    /**
     * 启动工作者进程
     *
     * @return void
     */
    function start()
    {
        $this->run();
    }

    /**
     * 工作者进程的运行方法，子类应该重写这个方法，而且方法本身是一个抽象方法。
     *
     * @return void
     */
    function run()
    {
        $this->produce->run();
    }
}
<?php
namespace MQK\Worker;

abstract class AbstractWorker
{
    protected $id;
    protected $createdAt;

    const M = 1024 * 1024;

    public function __construct()
    {
        $this->createdAt = time();
    }

    public function start()
    {
        $pid = pcntl_fork();

        if (-1 == $pid) {
            exit(1);
        } else if ($pid) {
            return $pid;
        }
        $this->id = posix_getpid();
        $this->run();
        exit();
    }


    public function stop()
    {

    }

    public function join()
    {
        $status = null;
        pcntl_waitpid($this->id, $status);
    }

    public function pause()
    {

    }

    public function id()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function createdAt()
    {
        return $this->createdAt;
    }
}
<?php
namespace MQK\Worker;
declare(ticks=1);

use MQK\LoggerFactory;

abstract class AbstractWorker
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var int
     */
    protected $createdAt;
    protected $alive = true;
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
//        pcntl_signal(SIGQUIT, array(&$this, "signalQuitHandler"));
        pcntl_signal(SIGTERM, array(&$this, "signalTerminalHandler"));
//        pcntl_signal(SIGUSR1, array(&$this, "signalUsr1Handler"));
        $this->id = posix_getpid();

        // TODO: 进程退出后通知
        $this->run();
        exit();
    }

    protected function run()
    {

    }

    protected function signalUsr1Handler($signo)
    {
    }

    protected function signalTerminalHandler($signo)
    {
        $this->logger->debug("Signal terminal.");
        $this->willExit();
        $this->alive = false;
    }

    protected function signalQuitHandler($signo)
    {
        $this->willQuit();
        $this->alive = false;
        exit(0);
    }

    protected function willExit()
    {

    }

    protected function willQuit()
    {

    }

    protected function didQuit()
    {

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
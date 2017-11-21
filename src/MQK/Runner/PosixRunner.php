<?php
namespace MQK\Runner;

declare(ticks=1);
use Monolog\Handler\StreamHandler;
use MQK\Process\MasterProcess as Master;
use MQK\Queue\QueueFactory;
use MQK\Worker\Worker;
use MQK\Worker\WorkerFactory;
use MQK\LoggerFactory;

class PosixRunner extends Master implements Runner
{
    use RunnerTrait;

    public function __construct(
        $burst,
        $fast,
        $processIdFile,
        $daemonize,
        $concurrency,
        $workerFactory,
        $connection,
        $searchExpiredMessage
    ) {
        $this->workerClassOrFactory = $workerFactory;
        $this->connection = $connection;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $this->masterId = uniqid();
        $this->searchExpiredMessage = $searchExpiredMessage;
        $this->fast = $fast;
        $this->processIdFile = $processIdFile;
        $this->daemonize = $daemonize;

        parent::__construct(
            $this->workerClassOrFactory,
            $concurrency,
            $burst,
            $this->logger
        );
    }

    public function run()
    {
        if (!$this->daemonize) {
            if ($this->processIdFile) {
                file_put_contents($this->processIdFile, getmypid());
            }
        } else
            $this->daemonize();
        $this->logger->notice("MasterProcess ({$this->masterId}) work on process" . posix_getpid());
        parent::run();
        $this->spawn();
    }

    protected function daemonize()
    {
        $pid = pcntl_fork();
        if ($pid != 0)
            exit(0);

        $pid = pcntl_fork();
        if ($pid != 0)
            exit(0);

        if ($this->daemonize)
            file_put_contents($this->processIdFile, getmypid());
        posix_setsid();
        umask(022);
        if (is_resource(STDOUT))
            fclose(STDOUT);

        if (is_resource(STDERR))
            fclose(STDERR);

        $factory = LoggerFactory::shared();
        $hander = new StreamHandler("/tmp/mqk.log");
        $factory->pushHandler($hander);
        $this->logger = $factory->getLogger(__CLASS__);
    }

    protected function didSelect()
    {
        $this->heartbeat();
    }

    public function heartbeat()
    {
        $this->updateHealth();
        if (!$this->fast && $this->searchExpiredMessage) {
            $this->searchExpiredMessage->process();
        }
    }

    public function updateHealth()
    {
        $key = "mqk:{$this->masterId}";
        $this->connection->multi();
        $this->connection->hset($key, "updated_at", time());
        $this->connection->expire($key, 5);
        $this->connection->exec();
    }

    protected function willQuit()
    {
        if ($this->processIdFile) {
            $this->logger->debug("delete process id file {$this->processIdFile}");
            unlink($this->processIdFile);
        }
    }
}
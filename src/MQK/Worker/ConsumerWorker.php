<?php
declare(ticks=1);
namespace MQK\Worker;

use Monolog\Logger;
use MQK\LoggerFactory;
use MQK\Process\AbstractWorker;
use MQK\RedisProxy;

/**
 * Woker的具体实现，在进程内调度Queue和Job完成具体任务
 *
 * Class ConsumerWorker
 * @package MQK\Worker
 */
class ConsumerWorker extends AbstractWorker
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var float
     */
    protected $workerStartTime;

    /**
     * @var float
     */
    protected $workerEndTime;

    /**
     * @var string
     */
    protected $workerId;

    /**
     * @var RedisProxy
     */
    protected $connection;

    /**
     * @var ConsumerExecutorWorker
     */
    protected $executor;

    private $alive = true;

    public function __construct(
        $bootstrap,
        $connection,
        ConsumerExecutorWorkerFactory $consumerExecutorWorkerFactory) {

        $this->workerId = uniqid();
        $this->connection = $connection;
        $this->executor = $consumerExecutorWorkerFactory->create();
        $this->bootstrap = $bootstrap;
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    public function run()
    {
        parent::run();
        $this->connection->connect(true);

        $this->logger->debug("Process ({$this->workerId}) {$this->id} started.");
        $this->loadUserInitializeScript($this->bootstrap);
        $this->executor->execute();
    }

    protected function quit()
    {
        $this->executor->quit();
    }

    protected function graceFullQuit()
    {
        $this->logger->debug("Grace full quit");
        $this->executor->goingToDie();
    }

    public function consumerWorkerExecutor()
    {
        return $this->executor;
    }

    public function loadUserInitializeScript($bootstrap)
    {
        if (!empty($bootstrap)) {
            if (file_exists($bootstrap)) {
                $this->logger->debug("Loaded bootstrap {$bootstrap}");
                include_once $bootstrap;
                return;
            } else {
                $this->logger->warning("You specify bootstrap script [{$bootstrap}], but file not exists.");
            }
        }
        $cwd = getcwd();
        $bootstrap = "{$cwd}/bootstrap.php";

        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        } else {
            $this->logger->warning("{$bootstrap} not found, all event will miss.");
        }
    }
}
<?php
namespace MQK\Command;

use Monolog\Logger;
use MQK\Config;
use MQK\Error\DefaultErrorHandler;
use MQK\IniConfig;
use MQK\LoggerFactory;
use MQK\YamlConfigProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::defaultConfig();

        $defaultErrorHandler = new DefaultErrorHandler();
        $config->addErrorHandler($defaultErrorHandler);

        $workers = (int)$input->getOption("concurrency");
        if (0 == $workers)
            $workers = 1;

        $config->setConcurrency($workers);

        $quite = $input->getOption("quite");
        if ($quite)
            $config->beQuite();

        $verbose = $input->getOption("verbose");
        if ($verbose) {
            LoggerFactory::shared()->setDefaultLevel(Logger::DEBUG);
        } else {
            LoggerFactory::shared()->setDefaultLevel(Logger::INFO);
        }
        $dsn = $input->getOption("redis");
        if (!empty($dsn)) {
            $config->setRedis($dsn);
        } else {
            $config->setRedis('redis://127.0.0.1');
        }

        $sentry = $input->getOption("sentry");
        if (!empty($sentry)) {
            $config->setSentry($sentry);

            $client = new \Raven_Client($sentry);
            $error_handler = new \Raven_ErrorHandler($client);
            $error_handler->registerExceptionHandler();
            $error_handler->registerErrorHandler();
            $error_handler->registerShutdownFunction();
        }

        $queues = $input->getOption('queue');

        $this->loadIniConfig($input->getOption('config'));

        if (empty($config->queues())) {
            $config->setQueues(['default']);
        }
    }

    protected function loadIniConfig($yamlPath)
    {
        if (!empty($yamlPath)) {
            if (!file_exists($yamlPath)) {
                $this->logger->warning("You specify config file, but not found");
                return;
            }
        } else {
            return;
        }

        $conf = Config::defaultConfig();
        $parseProcessor = new YamlConfigProcessor(
            Yaml::parse(file_get_contents($yamlPath)),
            $conf
        );
        try {
            $parseProcessor->process();
        } catch (\Exception $e) {
            if ($e->getMessage() == 5) {
                $this->logger->warning($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

}
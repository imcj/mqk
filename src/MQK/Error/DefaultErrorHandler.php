<?php
namespace MQK\Error;


use Monolog\Logger;
use MQK\Config;
use MQK\LoggerFactory;

class DefaultErrorHandler implements ErrorHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct()
    {
        $this->config = Config::defaultConfig();
        $this->logger = LoggerFactory::shared()->getLogger(__CLASS__);
    }

    /**
     * 捕获到错误
     *
     * @param \Exception $exception
     * @return void
     */
    public function got(\Exception $exception)
    {
        $this->logger->error($exception->getMessage());

        if (!empty($this->config->sentry())) {
            $client = new \Raven_Client($this->config->sentry());
            $client->captureException($exception);
        }
    }
}
<?php
namespace MQK;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use MQK\Logging\Handlers\StreamHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

const LOGGING_DEFAULT_TEST_CONFIG_YAML = "logging:
  handlers:
    - StreamHandler";

const LOGGING_STREAM_TEST_CONFIG_YAML = "logging:
  logging: INFO
  handlers:
    -
      handler:
        class: StreamHandler
        stream: app.log
        level: INFO";

class YamlConfigProcessorTest extends TestCase
{
    public function testDefault()
    {
        // 重构 LoggerFactory的状态由Logger配置和LoggerFactory共同完成
        // 而不是在YamlCOnfigProcessorTest内进行修改
        $yaml = Yaml::parse(LOGGING_DEFAULT_TEST_CONFIG_YAML);
        $config = new Config(null, null, null);
        $yamlConfigProcessor = new YamlConfigProcessor($yaml, $config);
        $yamlConfigProcessor->process();

        /**
         * @var Logger $logger
         */
        $logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);

        /**
         * @var StreamHandler $defaultHandler
         */
        $defaultHandler = $handlers[0];
        $this->assertEquals("php://stdout", $defaultHandler->getUrl());
        $this->assertEquals(Logger::DEBUG, $defaultHandler->getLevel());
        $this->assertInstanceOf(StreamHandler::class, $defaultHandler);

        assert(true);
    }

    public function testStreamHandler()
    {
        // 重构 LoggerFactory的状态由Logger配置和LoggerFactory共同完成
        // 而不是在YamlCOnfigProcessorTest内进行修改
        $yaml = Yaml::parse(LOGGING_STREAM_TEST_CONFIG_YAML);
        $config = new Config(null, null, null);
        $yamlConfigProcessor = new YamlConfigProcessor($yaml, $config);
        $yamlConfigProcessor->process();

        /**
         * @var Logger $logger
         */
        $logger = LoggerFactory::shared()->getLogger(__CLASS__);
        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);

        /**
         * @var StreamHandler $defaultHandler
         */
        $defaultHandler = $handlers[0];
        $this->assertEquals("app.log", $defaultHandler->getUrl());
        $this->assertEquals(Logger::INFO, $defaultHandler->getLevel());
        $this->assertInstanceOf(StreamHandler::class, $defaultHandler);

        assert(true);
    }
}
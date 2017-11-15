<?php
namespace MQK;


use PHPUnit\Framework\TestCase;

class YamlConfigProcessTest extends TestCase
{
    public function testMemoryLimit100M()
    {
        $config = Config::defaultConfig();
        $yaml = [
            'memory_limit' => "100m"
        ];
        $configProcess = new YamlConfigProcessor($yaml, $config);
        $configProcess->process();
        $this->assertEquals(1024 * 1024 * 100, $config->memoryLimit());
    }

    public function testMemoryLimit100MUpper()
    {
        $config = Config::defaultConfig();
        $yaml = [
            'memory_limit' => "100M"
        ];
        $configProcess = new YamlConfigProcessor($yaml, $config);
        $configProcess->process();
        $this->assertEquals(1024 * 1024 * 100, $config->memoryLimit());
    }

    public function testMemoryLimit100G()
    {
        $config = Config::defaultConfig();
        $yaml = [
            'memory_limit' => "100G"
        ];
        $configProcess = new YamlConfigProcessor($yaml, $config);
        $configProcess->process();
        $this->assertEquals(1024 * 1024 * 1024 * 100, $config->memoryLimit());
    }

    public function testMemoryLimitDefault()
    {
        $config = Config::defaultConfig();
        $yaml = [
        ];
        $configProcess = new YamlConfigProcessor($yaml, $config);
        $configProcess->process();
        $this->assertEquals(1024 * 1024 * 1024, $config->memoryLimit());
    }
}
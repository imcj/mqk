<?php
namespace MQK\Event;

use MQK\Config;
use Symfony\Component\EventDispatcher\Event;

class ConfigEvent extends Event
{
    const CONFIG_LOAD = 'config.load';

    const CONFIG_LOADED = 'config.loaded';

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config = null)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function config()
    {
        return $this->config;
    }
}
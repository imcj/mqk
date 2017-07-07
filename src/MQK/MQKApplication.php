<?php
namespace MQK;

use Symfony\Component\Console\Application;
use MQK\Command\RunCommand;
use MQK\Command\InvokeCommand;
use MQK\Command\MonitorCommand;

class MQKApplication extends Application
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->add(new RunCommand());
        $this->add(new InvokeCommand());
        $this->add(new MonitorCommand());
    }
}
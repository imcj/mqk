<?php
namespace MQK\MasterProcess;

use MQK\Process\Process;
use MQK\Runner;

class MQKMasterProcessFactory implements MasterProcessFactory
{
    /**
     * @return Process
     */
    public function create()
    {
        return new Runner();
    }
}
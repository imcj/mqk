<?php
namespace MQK\MasterProcess;

use MQK\Process\Process;

interface MasterProcessFactory
{
    /**
     * @return Process
     */
    function create();
}
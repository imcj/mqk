<?php
namespace MQK\MasterProcess;


use MQK\Process\Process;

interface MasterProcess
{
    public function workerFactory();

    public function setWorkerFactory($workerFactory);
}
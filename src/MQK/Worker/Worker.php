<?php
namespace MQK\Worker;

use MQK\Process\Process;

interface Worker extends Process
{
    function id();
}
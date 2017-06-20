<?php
namespace MQK\Worker;

interface Worker
{
    function start();
    // function execute();
    function stop();
    function pause();
}
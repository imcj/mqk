<?php
namespace MQK\Process;

interface Process
{
    function start();
    function run();
    function stop();
    function pause();
    function join();
}
<?php
namespace MQK\Worker;

interface Worker
{
    function start();
    function run();
    function stop();
    function pause();
    function join();
}
<?php
namespace MQK\Worker;

interface Worker
{
    function id();

    function start();
    function run();
    function stop();
    function pause();
    function join();
}
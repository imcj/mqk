<?php
namespace MQK\Worker;

/**
 * Interface WorkerFactory
 * @package MQK\Worker
 */
interface WorkerFactory
{
    /**
     * @return Worker
     */
    function create();
}
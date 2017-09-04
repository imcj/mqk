<?php
namespace MQK\Worker;

/**
 * Interface WorkerFactory
 * @package MQK\Worker
 */
interface WorkerFactory
{
    /**
     * @param  string $masterId
     * @return Worker
     */
    function create($masterId);
}
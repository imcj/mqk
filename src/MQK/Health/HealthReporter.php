<?php
namespace MQK\Health;


interface HealthReporter
{
    /**
     * Report worker process for health
     *
     * @param string
     * @return void
     */
    function report($status);

    /**
     * Health reporter report frien
     *
     * @return integer
     */
    function interval();

    function setInterval($interval);

    /**
     * @return WorkerHealth
     */
    function health();

    /**
     * @param $health
     * @return WorkerHealth
     */
    function setHealth($health);
}
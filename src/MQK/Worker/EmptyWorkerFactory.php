<?php
namespace MQK\Worker;


class EmptyWorkerFactory implements WorkerFactory
{

    /**
     * @return Worker
     */
    function create()
    {
        return new EmptyWorker();
    }
}
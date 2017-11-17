<?php
namespace MQK\Runner;


trait RunnerTrait
{
    /**
     * @var RedisProxy
     */
    private $connection;

    /**
     * @var string
     */
    protected $masterId;

    /**
     * @var integer
     */
    protected $concurrency;

    /**
     * @var OSDetect
     */
    protected $osDetect;

    /**
     * @var SearchExpiredMessage
     */
    protected $searchExpiredMessage;

    /**
     * @var bool
     */
    protected $fast = false;

    /**
     * @var string|null
     */
    protected $processIdFile;

    /**
     * @var boolean
     */
    protected $daemonize;
}
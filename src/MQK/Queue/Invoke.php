<?php
namespace MQK\Queue;


class Invoke
{
    /**
     * @var string
     */
    private $func;

    /**
     * @var array
     */
    private $arguments;

    public function __construct($func, $arguments)
    {
        $this->func = $func;
        $this->arguments = $arguments;
    }

    public function func()
    {
        return $this->func;
    }

    public function arguments()
    {
        return $this->arguments;
    }
}
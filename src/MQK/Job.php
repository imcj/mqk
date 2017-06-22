<?php
namespace MQK;

class Job implements \JsonSerializable
{
    /**
     * id
     *
     * @var string
     */
    private $id;

    private $func;
    private $arguments;
    private $connection;

    /**
     * @var int
     */
    private $delay;

    public function __construct($id, $func, $arguments)
    {
        $this->id = $id == null ? uniqid() : $id;
        $this->func = $func;
        $this->arguments = $arguments;
        $this->result = null;
    }

    public function id()
    {
        return $this->id;
    }

    public function func()
    {
        return $this->func;
    }

    public function arguments()
    {
        return $this->arguments;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function result()
    {
        return $this->connection->hget('result', $this->id());
    }

    public function delay()
    {
        return $this->delay;
    }

    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'func' => $this->func,
            'arguments' => $this->arguments,
            'delay' => $this->delay
        );
    }

    public static function job($json)
    {
        return new Job($json->id, $json->func, $json->arguments);
    }
}
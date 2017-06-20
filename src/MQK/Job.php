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

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'func' => $this->func,
            'arguments' => $this->arguments
        );
    }

    public static function job($json)
    {
        return new Job($json->id, $json->func, $json->arguments);
    }
}
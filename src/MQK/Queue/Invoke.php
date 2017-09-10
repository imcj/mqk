<?php
namespace MQK\Queue;


class Invoke
{
    private $key;
    /**
     * @var string
     */
    private $func;

    /**
     * @var array
     */
    private $arguments;

    private $id;

    private $invokes;

    /**
     * @var MessageInvokableSync
     */
    private $message;

    public function __construct($key, $func, ...$arguments)
    {
        $this->key = $key;
        $this->func = $func;
        $this->arguments = $arguments;
        $this->id = uniqid();
    }

    public function func()
    {
        return $this->func;
    }

    public function arguments()
    {
        return $this->arguments;
    }

    public function id()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function invokes()
    {
        return $this->invokes;
    }

    public function setInvokes($invokes)
    {
        $this->invokes = $invokes;
    }

    public function key()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function message()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function createMessage()
    {
        $message = new MessageInvokableSync($this->invokes->id(), $this->invokes->length(), $this->id);
        $payload = new \stdClass();
        $payload->func = $this->func;
        $payload->arguments = $this->arguments;
        $message->setPayload($payload);
        return $message;
    }
}
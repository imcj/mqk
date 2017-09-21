<?php
namespace MQK\Example;

use Symfony\Component\EventDispatcher\Event;

class ExampleEvent extends Event
{
    public $hello;

    public function __construct()
    {
        $this->hello = "hello world!!!";
    }
}
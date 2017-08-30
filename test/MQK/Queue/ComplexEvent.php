<?php
namespace MQK\Queue;


use Symfony\Component\EventDispatcher\Event;

class ComplexEvent extends Event
{
    const NAME = "complex.event";

    public $id;

    public $child;

    public $parent;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
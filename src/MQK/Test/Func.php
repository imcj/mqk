<?php
namespace MQK\Test;


class Func
{
    public static function arr()
    {
        return [1, 2];
    }

    public static function obj()
    {
        $a = new \stdClass();
        $a->name = "CJ";

        $b = new \stdClass();
        $b->email = "weicongju@gmail.com";

        $a->b = $b;
        $b->a = $a;

        return $a;
    }
}
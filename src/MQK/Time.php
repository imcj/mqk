<?php
namespace MQK;


class Time
{
    public static function micro()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}
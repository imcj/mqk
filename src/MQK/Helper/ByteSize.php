<?php
namespace MQK\Helper;

class ByteSize
{
    public function k($bytes)
    {
        return sprintf("%.4f", $bytes / 1024.0);
    }

    public function m($bytes)
    {
        return sprintf("%.4f", floatval($this->k($bytes)) / 1024.0);
    }
}
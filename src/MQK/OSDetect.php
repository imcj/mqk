<?php
namespace MQK;


class OSDetect
{
    public function isWin()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public function isPosix()
    {
        return !$this->isWin();
    }
}
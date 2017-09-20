<?php
namespace MQK\Error;


interface ErrorHandler
{
    /**
     * 捕获到错误
     *
     * @param \Exception $exception
     * @return void
     */
    public function got(\Exception $exception);
}
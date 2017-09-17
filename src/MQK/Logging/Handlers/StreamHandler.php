<?php
namespace MQK\Logging\Handlers;

use Monolog\Logger;

class StreamHandler extends \Monolog\Handler\StreamHandler
{
    public function __construct(
        $stream = null,
        $level = Logger::DEBUG,
        $bubble = true,
        $filePermission = null,
        $useLocking = false) {

        if (null == $stream)
            $stream = "php://stdout";
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
    }

    public function setStream($stream)
    {
        if (is_resource($stream)) {
            $this->stream = $stream;
        } elseif (is_string($stream)) {
            $this->url = $stream;
        } else {
            throw new \InvalidArgumentException('A stream must either be a resource or a string.');
        }
    }
}
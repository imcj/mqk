<?php
namespace MQK\Queue\Outbound;


interface NotificationCenter
{
    public function notify($routerEntry, $message);
}
<?php
namespace MQK\Queue\Outbound;


use MQK\Queue\MessageNormal;

interface NotificationCenter
{
    /**
     * @param RouterEntry $routerEntry
     * @param MessageNormal $message
     * @return void
     */
    public function notify(
        RouterEntry $routerEntry,
        MessageNormal $message
    );
}
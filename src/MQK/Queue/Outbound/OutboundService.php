<?php
namespace MQK\Queue\Outbound;


interface OutboundService
{
    public function launch($routerKey, $message);

    public function addNewEndpoint(RouterEntry $entry);

    public function listEndpoint();
}
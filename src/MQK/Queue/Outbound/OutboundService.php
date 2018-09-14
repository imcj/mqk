<?php
namespace MQK\Queue\Outbound;


interface OutboundService
{
    public function launch($routerKey, $message);

    /**
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function addNewRouterEntry(RouterEntry $routerEntry);

    public function listEndpoint();

    /**
     * @param string $routerKey
     * @param int $page
     * @return RouterEntry
     */
    public function listRouterEntry($routerKey, $page);
}
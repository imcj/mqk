<?php
namespace MQK\Queue\Outbound;


use MQK\Queue\Outbound\Command\CreateRouterEntryCommand;
use MQK\Queue\Outbound\DTO\RouterEntryDTO;

interface OutboundServiceFacade
{
    public function launch($routerKey, $message);

    /**
     * @param string $routerKey
     * @param int $page
     * @return RouterEntryDTO[]
     */
    public function listRouterEntry($routerKey, $page);

    /**
     * @param CreateRouterEntryCommand $command
     * @return RouterEntry
     */
    public function addNewRouterEntry(CreateRouterEntryCommand $command);

    /**
     * @param RouterEntry $routerEntry
     * @return RouterEntry
     */
    public function updateRouterEntry(RouterEntry $routerEntry);

    /**
     * @param int $id
     * @return RouterEntry
     */
    public function removeRouterEntry($id);
}